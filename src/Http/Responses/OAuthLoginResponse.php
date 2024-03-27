<?php

namespace JoelButcher\Socialstream\Http\Responses;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse as LoginResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\LoginResponse as FortifyLoginResponse;

class OAuthLoginResponse implements LoginResponseContract
{
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse
    {
        return Socialstream::redirects('login')
            ? redirect()->intended(Socialstream::redirects('login'))
            : $this->defaultResponse();
    }

    private function defaultResponse(): RedirectResponse|FortifyLoginResponse
    {
        $previousUrl = Session::pull('socialstream.previous_url');

        return match (true) {
            Route::has('filament.auth.login') && $previousUrl === route('filament.auth.login') => redirect()
                ->route('admin'),
            $this->hasComposerPackage('laravel/breeze') => redirect()
                ->route('dashboard'),
            $this->hasComposerPackage('laravel/jetstream') => app(FortifyLoginResponse::class),
            default => redirect()
                ->to(route('dashboard', absolute: false)),
        };
    }

}
