<?php

namespace JoelButcher\Socialstream\Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GithubProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;

use function Illuminate\Filesystem\join_paths;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $files = (new Filesystem());
    $files->cleanDirectory($this->app->basePath('routes'));
    if($files->exists($this->app->bootstrapPath(join_paths('cache', 'routes-v7.php')))) {
        $files->delete($this->app->bootstrapPath(join_paths('cache', 'routes-v7.php')));
    }

    $this->defineCacheRoutes(file_get_contents(
        __DIR__ . '/../../routes/inertia.php',
    ));
});

it('caches routes and redirects to provider', function () {
    get('/oauth/github')
        ->assertRedirect();
});

it('caches routes and authenticates via GET', function () {
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

    Session::put('socialstream.previous_url', route('register'));

    get('oauth/github/callback')->assertRedirect('/dashboard');
});

it('caches routes and authenticates via POST', function () {
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

    Session::put('socialstream.previous_url', route('register'));

    post('oauth/github/callback')->assertRedirect('/dashboard');
});
