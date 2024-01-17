<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthLoginFailedResponse as OAuthLoginFailedResponseContract;
use JoelButcher\Socialstream\Socialstream;

class OAuthLoginFailedResponse implements OAuthLoginFailedResponseContract
{
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse
    {
        return Socialstream::redirects('login-failed')
            ? redirect()->intended(Socialstream::redirects('login-failed'))
            : $this->defaultResponse();
    }

    private function defaultResponse(): RedirectResponse
    {
        $previousUrl = session()->pull('socialstream.previous_url');

        return redirect()->to(match (true) {
            Route::has('filament.auth.login') && $previousUrl === route('filament.auth.login') => 'filament.auth.login',
            Route::has('login') && $previousUrl === route('login') => 'login',
            Route::has('register') && $previousUrl === route('register') => 'register',
            default => 'login',
        });
    }
}
