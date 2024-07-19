<?php

declare(strict_types=1);

namespace JoelButcher\Socialstream\Actions;

use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable as BaseAction;
use Laravel\Fortify\Fortify;

class RedirectIfTwoFactorAuthenticatable extends BaseAction
{
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

        $socialUser = app(ResolvesSocialiteUsers::class)
            ->resolve($request->route('provider'));

        return tap(Socialstream::$userModel::where('email', $socialUser->getEmail())->first(), function ($user) use ($request, $socialUser) {
            if (! $user || ! Socialstream::$connectedAccountModel::where('email', $socialUser->getEmail())->first()) {
                $this->fireFailedEvent($request, $user);

                $this->throwFailedAuthenticationException($request);
            }
        });
    }
}
