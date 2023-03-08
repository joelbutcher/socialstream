<?php

namespace JoelButcher\Socialstream\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\Socialstream;

class ShareInertiaData
{
    /**
     * Handle the incoming request.
     */
    public function handle(Request $request, Closure $next): \Symfony\Component\HttpFoundation\Response
    {
        Inertia::share(array_filter([
            'socialstream' => function () use ($request) {
                return [
                    'show' => Socialstream::show(),
                    'providers' => Socialstream::providers(),
                    'hasPassword' => $request->user() && ! is_null($request->user()->password),
                    'connectedAccounts' => $request->user() ? $request->user()->connectedAccounts
                        ->map(function (ConnectedAccount $account) {
                            return (object) $account->getSharedInertiaData();
                        }) : [],
                ];
            },
        ]));

        return $next($request);
    }
}
