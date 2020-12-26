<?php

namespace App\Actions\Socialstream;

use App\Models\User;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use Laravel\Socialite\Contracts\User as ProviderUserContract;

class CreateUserFromProvider implements CreatesUserFromProvider
{
    /**
     * Create a new user from a social provider user.
     *
     * @param  string  $provider
     * @param  \Laravel\Socialite\Contracts\User  $providerUser
     * @return \App\Models\User
     */
    public function create(string $provider, ProviderUserContract $providerUser)
    {
        $user = User::create([
            'name' => $providerUser->getName(),
            'email' => $providerUser->getEmail(),
        ]);

        $user->markEmailAsVerified();

        return $user;
    }
}
