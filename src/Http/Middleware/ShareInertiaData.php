<?php

namespace JoelButcher\Socialstream\Http\Middleware;

use Inertia\Inertia;
use JoelButcher\Socialstream\Socialstream;

class ShareInertiaData
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  callable  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    {
        $user = $request->user();
        if ($user) {
            Inertia::share(array_filter([
                'socialstream' => function () use ($user) {
                    return [
                        'show' => Socialstream::show(),
                        'providers' => Socialstream::providers(),
                        'hasPassword' =>  ! is_null($user->password),
                        'connectedAccounts' => $user->connectedAccounts,
                    ];
                },
            ]));
        }

        return $next($request);
    }
}
