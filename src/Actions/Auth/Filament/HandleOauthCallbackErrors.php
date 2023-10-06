<?php

namespace JoelButcher\Socialstream\Actions\Auth\Filament;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        return Auth::check()
            ? redirect()->route('filament.home')->withErrors($messageBag)
            : redirect()->route(
                'filament.admin.auth.login'
            )->withErrors($messageBag);
    }
}
