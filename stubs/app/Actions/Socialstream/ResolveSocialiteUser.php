<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Facades\Socialite;

class ResolveSocialiteUser implements ResolvesSocialiteUsers
{
    /**
     * Resolve the user for a given provider.
     *
     * @param  string  $provider
     * @return \Laravel\Socialite\AbstractUser
     */
    public function resolve($provider)
    {
        $user = Socialite::driver($provider)->user();

        if (Socialstream::generatesMissingEmails()) {
            $user->email = $user->getEmail() ?? "{$user->id}@{$provider}".config('app.domain');
        }

        return $user;
    }
}
