<?php

namespace JoelButcher\Socialstream\Contracts;

use JoelButcher\Socialstream\ConnectedAccount;
use Laravel\Socialite\Contracts\User;

interface UpdatesConnectedAccounts
{
    /**
     * Update a given connected account.
     *
     * @param  mixed  $user
     * @param  \JoelButcher\Socialstream\ConnectedAccount  $connectedAccount
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \JoelButcher\Socialstream\ConnectedAccount
     */
    public function update($user, ConnectedAccount $connectedAccount, string $provider, User $providerUser);
}
