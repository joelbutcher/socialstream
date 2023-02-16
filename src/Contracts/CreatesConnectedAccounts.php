<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use JoelButcher\Socialstream\ConnectedAccount;
use Laravel\Socialite\Contracts\User as ProviderUser;

interface CreatesConnectedAccounts
{
    /**
     * Create a connected account for a given user.
     */
    public function create(Authenticatable $user, string $provider, ProviderUser $providerUser): mixed;
}
