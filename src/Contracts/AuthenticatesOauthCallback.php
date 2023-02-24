<?php

namespace JoelButcher\Socialstream\Contracts;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Socialite\Contracts\User;

interface AuthenticatesOauthCallback
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        StatefulGuard $guard,
        CreatesUserFromProvider $createsUser,
        CreatesConnectedAccounts $createsConnectedAccounts,
        UpdatesConnectedAccounts $updatesConnectedAccounts
    );

    /**
     * Authenticates users returning from an OAuth flow.
     */
    public function authenticate(string $provider, User $providerAccount): Response|RedirectResponse|LoginResponse;
}
