<?php

namespace JoelButcher\Socialstream\Actions\Auth\Breeze;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use JoelButcher\Socialstream\Contracts\HandlesOauthCallbackErrors;

class HandleOauthCallbackErrors implements HandlesOauthCallbackErrors
{
    /**
     * Handles the request if the "errors" key is present.
     */
    public function handle(Request $request): ?RedirectResponse
    {
        if (! $request->has('error')) {
            return null;
        }

        $messageBag = new MessageBag;
        $messageBag->add('socialstream', $request->get('error_description'));

        return auth()->check()
            ? redirect(RouteServiceProvider::HOME)->withErrors(['callback' => $request->get('error_description')])
            : redirect()->route('login')->withErrors($messageBag);
    }
}
