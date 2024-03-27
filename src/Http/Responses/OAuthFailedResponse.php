<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthLoginFailedResponse as OAuthFailedResponseContract;
use JoelButcher\Socialstream\Socialstream;

class OAuthFailedResponse implements OAuthFailedResponseContract
{
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse
    {
        if (Route::has('register') && Session::get(key: 'socialstream.previous_url') === route('register')) {
            Session::forget(keys: 'socialstream.previous_url');

            return redirect()->to('register');
        }

        return Socialstream::redirects('login-failed')
            ? redirect()->intended(Socialstream::redirects('login-failed'))
            : $this->defaultResponse();
    }

    private function defaultResponse(): RedirectResponse
    {
        $previousUrl = Session::pull('socialstream.previous_url');

        return redirect()->to(match (true) {
            Route::has('filament.auth.login') && $previousUrl === route('filament.auth.login') => 'filament.auth.login',
            Route::has('login') && $previousUrl === route('login') => 'login',
            Route::has('register') && $previousUrl === route('register') => 'register',
            default => 'login',
        });
    }
}
