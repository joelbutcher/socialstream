<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use App\Actions\Socialstream\CreateConnectedAccount;
use App\Actions\Socialstream\CreateUserFromProvider;
use App\Models\ConnectedAccount;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\RefreshedCredentials;
use JoelButcher\Socialstream\Socialstream;
use JoelButcher\Socialstream\Tests\TestCase;
use Laravel\Socialite\Two\User as OAuth2User;

it('can refresh expired tokens', function (): void
{
    $this->migrate();

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
});

it('does not refresh active tokens', function (): void
{
    $this->migrate();

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
});

it('does not refresh tokens if the feature is disabled', function (): void
{
    $this->migrate();

    Config::set('socialstream.features', []);

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
});
