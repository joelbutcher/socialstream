<?php

namespace JoelButcher\Socialstream\Http\Responses;

use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Concerns\ConfirmsFilament;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse as LoginResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\LoginResponse as FortifyLoginResponse;

class OAuthLoginResponse implements LoginResponseContract
{
    use ConfirmsFilament;
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse
    {
        return Socialstream::redirects('login')
            ? redirect()->intended(Socialstream::redirects('login'))
            : $this->defaultResponse();
    }

    private function defaultResponse(): RedirectResponse|FortifyLoginResponse
    {
        return match (true) {
            $this->usesFilament() && $this->hasFilamentAuthRoutes() => redirect()->route('filament.home'),
            $this->hasComposerPackage('laravel/breeze') => redirect()
                ->route('dashboard'),
            $this->hasComposerPackage('laravel/jetstream') => app(FortifyLoginResponse::class),
            default => redirect()
                ->to(route('dashboard', absolute: false)),
        };
    }

}
