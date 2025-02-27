<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Contracts\User;

interface AuthenticatesOAuthCallback
{
    /**
     * Authenticates users returning from an OAuth flow.
     */
    public function authenticate(Request $request, string $provider, User $providerAccount): RedirectResponse;
}
