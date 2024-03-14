<?php

namespace JoelButcher\Socialstream\Http\Responses;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthRegisterResponse as RegisterResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\RegisterResponse as FortifyRegisterResponse;

class OAuthRegisterResponse implements RegisterResponseContract
{
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse
    {
        return Socialstream::redirects('register')
            ? redirect()->intended(Socialstream::redirects('register'))
            : $this->defaultResponse();
    }

    private function defaultResponse(): RedirectResponse|FortifyRegisterResponse
    {
        $previousUrl = session()->pull('socialstream.previous_url');

        return match (true) {
            Route::has('filament.auth.login') && $previousUrl === route('filament.auth.login') => redirect()
                ->route('admin'),
            $this->hasComposerPackage('laravel/breeze') => redirect()
                ->route('dashboard'),
            $this->hasComposerPackage('laravel/jetstream') => app(FortifyRegisterResponse::class),
            default => redirect()
                ->to(route('dashboard', absolute: false)),
        };
    }
}
