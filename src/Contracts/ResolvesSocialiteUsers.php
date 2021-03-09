<?php

namespace JoelButcher\Socialstream\Contracts;

interface ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     *
     * @param  string  $provider
     * @return \Laravel\Socialite\AbstractUser
     */
    public function resolve($provider);
}
