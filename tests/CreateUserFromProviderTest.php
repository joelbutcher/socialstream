<?php

namespace JoelButcher\Socialstream\Tests;

use App\Actions\Socialstream\CreateConnectedAccount;
use App\Actions\Socialstream\CreateUserFromProvider;
use App\Models\ConnectedAccount;
use DateTimeInterface;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Contracts\Credentials;
use Laravel\Socialite\One\User as OAuth1User;
use Laravel\Socialite\Two\User as OAuth2User;

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

        $action = new CreateUserFromProvider(new CreateConnectedAccount);

        $user = $action->create('github', $providerUser);

        $this->assertEquals($providerUser->email, $user->email);

        $connectedAccount = $user->currentConnectedAccount;
        $this->assertInstanceOf(ConnectedAccount::class, $connectedAccount);
        $this->assertEquals($providerUser->id, $connectedAccount->provider_id);

        $credentials = $connectedAccount->getCredentials();
        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertEquals($providerUser->id, $credentials->getId());
        $this->assertEquals($providerUser->token, $credentials->getToken());
        $this->assertNull($credentials->getRefreshToken());
        $this->assertNull($credentials->getExpiry());
    }

    public function test_user_can_be_created_from_o_auth_2_provider()
    {
        $this->migrate();

        $providerUser = new OAuth2User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);
        $providerUser->expiresIn = 3600;

        $action = new CreateUserFromProvider(new CreateConnectedAccount);

        $user = $action->create('github', $providerUser);

        $this->assertEquals($providerUser->email, $user->email);

        $connectedAccount = $user->currentConnectedAccount;
        $this->assertInstanceOf(ConnectedAccount::class, $connectedAccount);
        $this->assertEquals($providerUser->id, $connectedAccount->provider_id);

        $credentials = $connectedAccount->getCredentials();
        $this->assertInstanceOf(Credentials::class, $credentials);
        $this->assertEquals($providerUser->id, $credentials->getId());
        $this->assertEquals($providerUser->token, $credentials->getToken());
        $this->assertNull($credentials->getTokenSecret());
        $this->assertInstanceOf(DateTimeInterface::class, $credentials->getExpiry());
    }
}
