<?php

declare(strict_types=1);

namespace JoelButcher\Socialstream\Actions;

use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\StatefulGuard;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Actions\AttemptToAuthenticate as BaseAction;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class AttemptToAuthenticate extends BaseAction
{
    public function __construct(
        StatefulGuard $guard,
        LoginRateLimiter $limiter,
        protected ResolvesSocialiteUsers $resolver
    ) {
        parent::__construct($guard, $limiter);
    }

    public function handle($request, $next, $authIdentifier = null)
    {
        if (Fortify::$authenticateUsingCallback) {
            return $this->handleUsingCustomCallback($request, $next);
        }

        // Fallback to Laravel Fortify
        if (! $request->route('provider') && $request->route(Fortify::username())) {
            return parent::handle($request, $next);
        }

        if ($authIdentifier) {
            $this->guard->loginUsingId($authIdentifier, Socialstream::hasRememberSessionFeatures());

            return $next($request);
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

        $this->guard->login($connectedAccount->user, Socialstream::hasRememberSessionFeatures());

        return $next($request);
    }
}
