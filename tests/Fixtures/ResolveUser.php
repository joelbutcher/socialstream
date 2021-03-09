<?php

namespace JoelButcher\Socialstream\Tests\Fixtures;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;

class ResolveUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     *
     * @param  string  $provider
     * @return \Laravel\Socialite\AbstractUser
     */
    public function resolve($provider)
    {
        //
    }
}
