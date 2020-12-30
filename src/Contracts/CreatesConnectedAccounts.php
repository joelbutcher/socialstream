<?php

namespace JoelButcher\Socialstream\Contracts;

use App\Models\User;
use Laravel\Socialite\Contracts\User as ProviderUser;

interface CreatesConnectedAccounts
{
    /**
     * Create a connected account for a given user.
     * 
     * @param  \App\Models\User  $user
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \JoelButcher\Socialstream\ConnectedAccount
     */
    public function create(User $user, string $provider, ProviderUser $providerUser);
}