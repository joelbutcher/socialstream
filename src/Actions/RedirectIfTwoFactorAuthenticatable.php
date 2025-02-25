<?php

declare(strict_types=1);

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as BaseAction;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class RedirectIfTwoFactorAuthenticatable extends BaseAction
{
    public function __construct(
        StatefulGuard $guard,
        LoginRateLimiter $limiter,
        protected ResolvesSocialiteUsers $resolver
    ) {
        parent::__construct($guard, $limiter);

    }

    protected function validateCredentials($request)
    {
        if (Fortify::$authenticateUsingCallback) {
            return tap(call_user_func(Fortify::$authenticateUsingCallback, $request), function ($user) use ($request) {
                if (! $user) {
                    $this->fireFailedEvent($request);

                    $this->throwFailedAuthenticationException($request);
                }
            });
        }

        // Fallback to Laravel Fortify
        if (! $request->route('provider') && $request->route(Fortify::username())) {
            return parent::validateCredentials($request);
        }

        $socialUser = $this->resolver->resolve($request->route('provider'));

        $connectedAccount = tap(Socialstream::$connectedAccountModel::where('email', $socialUser->getEmail())->first(), function ($connectedAccount) use ($request) {
            if (! $connectedAccount) {
                event(new Failed($this->guard?->name ?? config('fortify.guard'), user: null, credentials: [
                    'provider' => $request->route('provider'),
                ]));

                $this->throwFailedAuthenticationException($request);
            }
        });

        return $connectedAccount->user;
    }
}
