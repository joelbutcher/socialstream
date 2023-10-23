<?php

namespace Tests\Feature;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JoelButcher\Socialstream\Providers;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Mockery as m;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('users can register using socialite providers', function (string $socialiteProvider) {
    if (! FortifyFeatures::enabled(FortifyFeatures::registration())) {
        return $this->markTestSkipped('Registration support is not enabled.');
    }

    if (! Providers::enabled($socialiteProvider)) {
        return $this->markTestSkipped("Registration support with the $socialiteProvider provider is not enabled.");
    }

    $user = (new User())
        ->map([
            'id' => 'abcdefgh',
            'nickname' => 'Jane',
            'name' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'avatar' => null,
            'avatar_original' => null,
        ])
        ->setToken('user-token')
        ->setRefreshToken('refresh-token')
        ->setExpiresIn(3600);

    $provider = m::mock('Laravel\\Socialite\\Two\\'.$socialiteProvider.'Provider');
    $provider->shouldReceive('user')->once()->andReturn($user);

    Socialite::shouldReceive('driver')->once()->with($socialiteProvider)->andReturn($provider);

    session()->put('socialstream.previous_url', route('register'));

    $response = get("/oauth/$socialiteProvider/callback");

    $this->assertAuthenticated();
    $response->assertRedirect(RouteServiceProvider::HOME);
})->with([
    [Providers::bitbucket()],
    [Providers::facebook()],
    [Providers::github()],
    [Providers::gitlab()],
    [Providers::google()],
    [Providers::linkedin()],
    [Providers::linkedinOpenId()],
    [Providers::slack()],
    [Providers::twitterOAuth1()],
    [Providers::twitterOAuth2()],
]);
