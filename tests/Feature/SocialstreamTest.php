<?php

namespace JoelButcher\Socialstream\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Symfony\Component\HttpFoundation\RedirectResponse;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('redirects users', function (): void {
    $response = get('http://localhost/oauth/github');

    $response->assertRedirect()
        ->assertRedirectContains('github.com');
});

it('generates a redirect using an overriding closure', function (bool $manageRepos): void {
    Config::set('services.github.manage_repos', $manageRepos);

    Socialstream::generatesProvidersRedirectsUsing(
        callback: fn () => new class() implements GeneratesProviderRedirect
        {
            public function generate(string $provider): RedirectResponse
            {
                ['provider' => $provider] = Route::current()->parameters();

                $scopes = ['*'];

                $scopes = match ($provider) {
                    'github' => array_merge($scopes, [
                        'repos.manage',
                    ]),
                    default => $scopes,
                };

                return Socialite::driver($provider)
                    ->scopes($scopes)
                    ->with(['response_type' => 'token', 'mobileminimal' => 1])
                    ->redirect();
            }
        }
    );

    $response = get('http://localhost/oauth/github');

    $response->assertRedirect()
        ->assertRedirectContains('github.com')
        ->assertRedirectContains('mobileminimal=1')
        ->assertRedirectContains('response_type=token');

    if ($manageRepos) {
        $response->assertRedirectContains('repos.manage');
    }
})->with([
    'manage repos' => [true],
    'do not manage repos' => [false],
]);

test('users can register', function (): void {
    $user = (new SocialiteUser())
        ->map([
            'id' => $githubId = fake()->numerify('########'),
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialstream.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->once()->andReturn($user);

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    Session::put('socialstream.previous_url', route('register'));

    $response = get('http://localhost/oauth/github/callback');

    $response->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'joel@socialstream.dev']);
    $this->assertDatabaseHas('connected_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialstream.dev',
    ]);
});

test('existing users can login', function (): void {
    $user = User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialstream.dev',
        'password' => Hash::make('password'),
    ]);

    $user->connectedAccounts()->create([
        'provider' => 'github',
        'provider_id' => $githubId = fake()->numerify('########'),
        'email' => 'joel@socialstream.dev',
        'token' => Str::random(64),
    ]);

    $this->assertDatabaseHas('users', ['email' => 'joel@socialstream.dev']);
    $this->assertDatabaseHas('connected_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialstream.dev',
    ]);

    $user = (new SocialiteUser())
        ->map([
            'id' => $githubId,
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialstream.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->once()->andReturn($user);

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    Session::put('socialstream.previous_url', route('login'));

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});

test('authenticated users can link to provider', function (): void {
    $this->actingAs(User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialstream.dev',
        'password' => Hash::make('password'),
    ]));

    $this->assertDatabaseHas('users', ['email' => 'joel@socialstream.dev']);
    $this->assertDatabaseEmpty('connected_accounts');

    $user = (new SocialiteUser())
        ->map([
            'id' => $githubId = fake()->numerify('########'),
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialstream.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->once()->andReturn($user);

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('/user/profile');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('connected_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialstream.dev',
    ]);
});

test('users can be authenticated with the same provider if they change the email associated with their user', function () {
    $user = User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialstream.com',
        'password' => Hash::make('password'),
    ]);

    $user->connectedAccounts()->create([
        'provider' => 'github',
        'provider_id' => $githubId = fake()->numerify('########'),
        'name' => 'Joel',
        'email' => 'joel@socialstream.dev',
        'token' => Str::random(64),
    ]);

    $user = (new SocialiteUser())
        ->map([
            'id' => $githubId,
            'nickname' => 'joel',
            'name' => 'Joel',
            'email' => 'joel@socialstream.dev',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = Mockery::mock(GithubProvider::class);
    $provider->shouldReceive('user')->once()->andReturn($user);

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    Session::put('socialstream.previous_url', route('login'));

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticated();
});
