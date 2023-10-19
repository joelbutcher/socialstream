<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Socialite\Contracts\User;

interface AuthenticatesOAuthCallback
{
    /**
     * Authenticates users returning from an OAuth flow.
     */
    public function authenticate(string $provider, User $providerAccount): Response|RedirectResponse|LoginResponse;
}
