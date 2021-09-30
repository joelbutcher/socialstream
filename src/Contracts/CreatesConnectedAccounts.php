<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as ProviderUser;

interface CreatesConnectedAccounts
{
    /**
     * Create a connected account for a given user.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \JoelButcher\Socialstream\ConnectedAccount
     */
    public function create(Authenticatable $user, string $provider, ProviderUser $providerUser);
}
