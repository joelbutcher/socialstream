<?php

use App\Models\User;
use App\Models\ConnectedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

it('can configure a login redirect', function () {
    Config::set('socialstream.redirects.login', '/foo');
    Route::get('foo', fn () => 'ok');

    $user = User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialstream.dev',
        'password' => Hash::make('password'),
    ]);

    ConnectedAccount::query()->forceCreate([
        'user_id' => $user->id,
        'provider' => 'github',
        'provider_id' => $githubId = fake()->numerify('########'),
        'name' => 'Joel',
        'nickname' => 'joel',
        'email' => 'joel@socialstream.dev',
        'token' => 'user-token',
        'refresh_token' => 'refresh-token',
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

    session()->put('socialstream.previous_url', route('login'));

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('foo');
});

it('can configure a register redirect', function () {
    Config::set('socialstream.redirects.register', '/foo');
    Route::get('foo', fn () => 'ok');

    $user = (new SocialiteUser())
        ->map([
            'id' => fake()->numerify('########'),
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

    session()->put('socialstream.previous_url', route('register'));

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('foo');
});

it('can configure a login failed redirect', function () {
    Config::set('socialstream.redirects.login-failed', '/foo');
    Route::get('foo', fn () => throw ValidationException::withMessages([
        'foo' => 'failed',
    ]));

    $user = (new SocialiteUser())
        ->map([
            'id' => fake()->numerify('########'),
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

    session()->put('socialstream.previous_url', route('login'));

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('foo')
        ->assertSessionHasErrors(['socialstream' => 'We could not find your account. Please register to create an account.']);
});

it('can configure a register failed redirect', function () {
    Config::set('socialstream.redirects.registration-failed', '/foo');
    Route::get('foo', fn () => throw ValidationException::withMessages([
        'foo' => 'failed',
    ]));

    User::create([
        'name' => 'Joel Butcher',
        'email' => 'joel@socialstream.dev',
        'password' => Hash::make('password'),
    ]);

    $user = (new SocialiteUser())
        ->map([
            'id' => fake()->numerify('########'),
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

    session()->put('socialstream.previous_url', route('register'));

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('foo')
        ->assertSessionHasErrors(['socialstream' => 'An account already exists for that email address. Please login to connect your GitHub account.']);
});
