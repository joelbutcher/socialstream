<?php

namespace JoelButcher\Socialstream\Contracts;

use Laravel\Socialite\Contracts\User as ProviderUserContract;

interface CreatesUserFromProvider
{
    /**
     * Create a new user from a social provider user.
     */
    public function create(string $provider, ProviderUserContract $providerUser): mixed;
}
