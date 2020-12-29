<?php

namespace JoelButcher\Socialstream\Tests;

use App\Actions\Socialstream\CreateUserFromProvider;
use Laravel\Socialite\One\User as OAuth1User;
use Laravel\Socialite\Two\User as OAuth2User;
use Illuminate\Support\Str;

class CreateUserFromProviderTest extends TestCase
{
    public function test_user_can_be_created_from_o_auth_1_provider()
    {
        $this->migrate();

        $providerUser = new OAuth1User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);

        $action = new CreateUserFromProvider;

        $user = $action->create('github', $providerUser);

        $this->assertEquals($providerUser->email, $user->email);
        $this->assertCount(1, $user->connectedAccounts);
    }

    public function test_user_can_be_created_from_o_auth_2_provider()
    {
        $this->migrate();
        
        $providerUser = new OAuth2User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);

        $action = new CreateUserFromProvider;

        $user = $action->create('github', $providerUser);

        $this->assertEquals($providerUser->email, $user->email);
        $this->assertCount(1, $user->connectedAccounts);
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}