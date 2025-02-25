<?php

namespace JoelButcher\Socialstream\Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Features;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;

use Orchestra\Testbench\Concerns\WithWorkbench;

use function Pest\Laravel\get;

uses(RefreshDatabase::class, WithWorkbench::class);

it('generates missing emails', function (): void {
    Config::set('socialstream.features', [
        Features::generateMissingEmails(),
    ]);

    $user = (new SocialiteUser())
        ->map([
            'id' => $githubId = fake()->numerify('########'),
            'nickname' => 'joel',
            'name' => 'Joel',
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

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('/dashboard');

    $user = User::first();

    $this->assertAuthenticated();
    $this->assertEquals("$githubId@github", $user->email);
    $this->assertDatabaseHas('connected_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => $user->email,
    ]);
});
