<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\User;

interface AuthenticatesOAuthCallback
{
    /**
     * Authenticates users returning from an OAuth flow.
     */
    public function authenticate(string $provider, User $providerAccount): SocialstreamResponse|RedirectResponse;
}
