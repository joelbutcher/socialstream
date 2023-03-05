<?php

namespace JoelButcher\Socialstream\Actions\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;
use JoelButcher\Socialstream\Contracts\HandlesOauthCallbackErrors;
use Laravel\Fortify\Features as FortifyFeatures;

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

        return Auth::check()
            ? redirect(config('fortify.home'))->dangerBanner($request->get('error_description'))
            : redirect()->route(
                FortifyFeatures::enabled(FortifyFeatures::registration()) ? 'register' : 'login'
            )->withErrors($messageBag);
    }
}
