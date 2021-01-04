<?php

namespace App\Actions\Socialstream;

use App\Models\User;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use Laravel\Socialite\Contracts\User as ProviderUser;

class CreateConnectedAccount implements CreatesConnectedAccounts
{
    /**
     * Create a connected account for a given user.
     *
     * @param  \App\Models\User  $user
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \JoelButcher\Socialstream\ConnectedAccount
     */
    public function create(User $user, string $provider, ProviderUser $providerUser)
    {
        if ($user->hasTokenFor($provider)) {
            $connectedAccount = $user->getTokenFor($provider);

            $connectedAccount->forceFill([
                'provider' => strtolower($provider),
                'id' => $providerUser->getId(),
                'name' => $providerUser->getName(),
                'nickname' => $providerUser->getNickname(),
                'email' => $providerUser->getEmail(),
                'avatar_path' => $providerUser->getAvatar(),
                'token' => $providerUser->token,
                'secret' => $providerUser->tokenSecret ?? null,
                'refresh_token' => $providerUser->refreshToken ?? null,
                'expires_at' => $providerUser->expiresAt ?? null,
            ])->save();

            return $connectedAccount;
        }

        return $user->connectedAccounts()->create([
            'provider' => strtolower($provider),
            'id' => $providerUser->getId(),
            'name' => $providerUser->getName(),
            'nickname' => $providerUser->getNickname(),
            'email' => $providerUser->getEmail(),
            'avatar_path' => $providerUser->getAvatar(),
            'token' => $providerUser->token,
            'secret' => $providerUser->tokenSecret ?? null,
            'refresh_token' => $providerUser->refreshToken ?? null,
            'expires_at' => $providerUser->expiresAt ?? null,
        ]);
    }
}
