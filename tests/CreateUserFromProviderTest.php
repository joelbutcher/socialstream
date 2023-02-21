<?php

namespace JoelButcher\Socialstream\Tests;

use App\Actions\Socialstream\CreateConnectedAccount;
use App\Actions\Socialstream\CreateUserFromProvider;
use App\Models\ConnectedAccount;
use DateTimeInterface;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Contracts\Credentials;
use JoelButcher\Socialstream\Features;
use JoelButcher\Socialstream\RefreshedCredentials;
use JoelButcher\Socialstream\Socialstream;
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

    public function test_user_can_refresh_token()
    {
        $this->migrate();

        Config::set('socialstream.features', [
            Features::refreshTokensOnRetrieve(),
        ]);

        Socialstream::refreshesTokensForProviderUsing('github', function () {
            return new RefreshedCredentials(
                'new-token',
                null,
                'new-refresh-token',
                now()->addSeconds(3600),
            );
        });

        $providerUser = new OAuth2User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);
        $providerUser->refreshToken = Str::random(64);
        $providerUser->expiresIn = 0;

        sleep(1);

        $createAction = new CreateUserFromProvider(new CreateConnectedAccount);
        $user = $createAction->create('github', $providerUser);
        $connectedAccount = $user->currentConnectedAccount;

        $this->assertEquals('new-token', $connectedAccount->token);
        $this->assertEquals('new-refresh-token', $connectedAccount->refresh_token);
        $this->assertEquals(null, $connectedAccount->secret);
    }

    public function test_user_token_does_not_get_refreshed_in_case_it_is_not_expired()
    {
        $this->migrate();

        Config::set('socialstream.features', [
            Features::refreshTokensOnRetrieve(),
        ]);

        Socialstream::refreshesTokensForProviderUsing('github', function () {
            return new RefreshedCredentials(
                'new-token',
                null,
                'new-refresh-token',
                now()->addSeconds(3600),
            );
        });

        $providerUser = new OAuth2User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);
        $providerUser->refreshToken = Str::random(64);
        $providerUser->expiresIn = 3600;

        sleep(1);

        $createAction = new CreateUserFromProvider(new CreateConnectedAccount);
        $user = $createAction->create('github', $providerUser);

        /** @var ConnectedAccount $connectedAccount */
        $connectedAccount = $user->currentConnectedAccount;

        $this->assertNotEquals('new-token', $connectedAccount->token);
        $this->assertNotEquals('new-refresh-token', $connectedAccount->refresh_token);
        $this->assertEquals(null, $connectedAccount->secret);
    }

    public function test_user_token_does_not_get_refreshed_in_case_feature_is_not_enabled()
    {
        $this->migrate();

        $newTime = now()->addSeconds(3600);

        Socialstream::refreshesTokensForProviderUsing('github', function (ConnectedAccount $account) use ($newTime) {
            return new RefreshedCredentials(
                'new-token',
                null,
                'new-refresh-token',
                $newTime,
            );
        });

        $providerUser = new OAuth2User;
        $providerUser->id = '1234567890';
        $providerUser->name = 'Joel Butcher';
        $providerUser->email = 'joel@socialstream.com';
        $providerUser->token = Str::random(64);
        $providerUser->refreshToken = Str::random(64);
        $providerUser->expiresIn = 0;

        sleep(1);

        $createAction = new CreateUserFromProvider(new CreateConnectedAccount);
        $user = $createAction->create('github', $providerUser);
        $connectedAccount = $user->currentConnectedAccount;

        $this->assertNotEquals('new-token', $connectedAccount->token);
        $this->assertNotEquals('new-refresh-token', $connectedAccount->refresh_token);
        $this->assertEquals(null, $connectedAccount->secret);
    }

    protected function migrate()
    {
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
    }
}
