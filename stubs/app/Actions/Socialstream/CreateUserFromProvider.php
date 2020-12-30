<?php

namespace App\Actions\Socialstream;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use Laravel\Socialite\Contracts\User as ProviderUser;

class CreateUserFromProvider implements CreatesUserFromProvider
{
    /**
     * Create a new user from a social provider user.
     *
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \App\Models\User
     */
    public function create(string $provider, ProviderUser $providerUser)
    {
        return DB::transaction(function () use ($provider, $providerUser) {
            return tap(User::create([
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
            ]), function (User $user) use ($provider, $providerUser) {
                $user->markEmailAsVerified();

                $user->switchConnectedAccount(
                    $this->createConnectedAccount($user, $provider, $providerUser)
                );
            });
        });
    }

    /**
     * Create a connected account for the user.
     *
     * @param  \App\Models\User  $user
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \JoelButcher\Socialstream\ConnectedAccount
     */
    protected function createConnectedAccount(User $user, string $provider, ProviderUser $providerUser)
    {
        return $user->connectedAccounts()->create([
            'provider_name' => strtolower($provider),
            'provider_id' => $providerUser->getId(),
            'token' => $providerUser->token,
            'secret' => $providerUser->tokenSecret ?? null,
            'refresh_token' => $providerUser->refreshToken ?? null,
            'expires_at' => $providerUser->expiresAt ?? null,
        ]);
    }
}
