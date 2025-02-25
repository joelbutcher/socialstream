<?php

namespace JoelButcher\Socialstream\Tests\Feature;

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

test('new users can register from login page', function (): void {
    Config::set('socialstream.features', [
        Features::createAccountOnFirstLogin(),
    ]);

    $this->assertDatabaseEmpty('users');
    $this->assertDatabaseEmpty('connected_accounts');

    $user = (new SocialiteUser)
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

    Session::put('socialstream.previous_url', route('login'));

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'joel@socialstream.dev']);
    $this->assertDatabaseHas('connected_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialstream.dev',
    ]);
});

test('new users can register from random page', function (): void {
    Config::set('socialstream.features', [
        Features::globalLogin(),
        Features::createAccountOnFirstLogin(),
    ]);

    $this->assertDatabaseEmpty('users');
    $this->assertDatabaseEmpty('connected_accounts');

    $user = (new SocialiteUser)
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

    Session::put('socialstream.previous_url', '/random');

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    get('http://localhost/oauth/github/callback')
        ->assertRedirect('/dashboard');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', ['email' => 'joel@socialstream.dev']);
    $this->assertDatabaseHas('connected_accounts', [
        'provider' => 'github',
        'provider_id' => $githubId,
        'email' => 'joel@socialstream.dev',
    ]);
});

test('new users cannot register from login page without feature enabled', function (): void {
    $this->assertDatabaseEmpty('users');
    $this->assertDatabaseEmpty('connected_accounts');

    $user = (new SocialiteUser)
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

    Session::put('socialstream.previous_url', route('login'));

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    $response = get('http://localhost/oauth/github/callback');

    $this->assertGuest();
    $response->assertRedirect(route('login'));
    $response->assertSessionHasErrors();
});

test('new users cannot register from random page without feature enabled', function (): void {
    $this->assertDatabaseEmpty('users');
    $this->assertDatabaseEmpty('connected_accounts');

    $user = (new SocialiteUser)
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

    Session::put('socialstream.previous_url', '/random');

    Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

    get('http://localhost/oauth/github/callback')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('socialstream');

    $this->assertGuest();
});
