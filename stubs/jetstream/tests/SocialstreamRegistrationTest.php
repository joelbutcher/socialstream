<?php

namespace tests;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JoelButcher\Socialstream\Providers;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User;
use Mockery;
use Tests\TestCase;

class SocialstreamRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @dataProvider socialiteProvidersDataProvider
     */
    public function test_users_get_redirected_correctly(string $provider): void
    {
        if (! Providers::enabled($provider)) {
            $this->markTestSkipped("Registration support with the $provider provider is not enabled.");
        }

        config()->set("services.$provider", [
            'client_id' => 'client-id',
            'client_secret' => 'client-secret',
            'redirect' => "http://localhost/oauth/$provider/callback",
        ]);

        $response = $this->get("/oauth/$provider");
        $response->assertRedirectContains($provider);
    }

    /**
     * @dataProvider socialiteProvidersDataProvider
     */
    public function test_users_can_register_using_socialite_providers(string $socialiteProvider)
    {
        if (! FortifyFeatures::enabled(FortifyFeatures::registration())) {
            $this->markTestSkipped('Registration support is not enabled.');
        }

        if (! Providers::enabled($socialiteProvider)) {
            $this->markTestSkipped("Registration support with the $socialiteProvider provider is not enabled.");
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

        $provider = Mockery::mock('Laravel\\Socialite\\Two\\'.$socialiteProvider.'Provider');
        $provider->shouldReceive('user')->once()->andReturn($user);

        Socialite::shouldReceive('driver')->once()->with($socialiteProvider)->andReturn($provider);

        session()->put('socialstream.previous_url', route('register'));

        $response = $this->get("/oauth/$socialiteProvider/callback");

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function socialiteProvidersDataProvider(): array
    {
        return [
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
        ];
    }
}
