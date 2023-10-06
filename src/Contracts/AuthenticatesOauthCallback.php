<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Socialite\Contracts\User;

interface AuthenticatesOauthCallback
{
    /**
     * Authenticates users returning from an OAuth flow.
     */
    public function authenticate(string $provider, User $providerAccount): Response|RedirectResponse|LoginResponse;
}
