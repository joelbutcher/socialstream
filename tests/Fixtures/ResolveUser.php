<?php

namespace JoelButcher\Socialstream\Tests\Fixtures;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use Laravel\Socialite\Contracts\User;

class ResolveUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     */
    public function resolve(string $provider): User
    {
        //
    }
}
