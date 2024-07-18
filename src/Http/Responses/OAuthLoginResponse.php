<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\RedirectResponse;
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
        return match (true) {
            $this->usesFilament() && $this->hasFilamentAuthRoutes() => redirect()->route(
                config('socialstream.filament-route', 'filament.admin.pages.dashboard')
            ),
            $this->hasComposerPackage('laravel/jetstream') => app(FortifyLoginResponse::class),
            $this->hasComposerPackage('laravel/breeze') => redirect()->route('dashboard'),
            default => $this->defaultResponse(),
        };
    }

    private function defaultResponse(): RedirectResponse
    {
        return Socialstream::redirects('login')
            ? redirect()->intended(Socialstream::redirects('login'))
            : redirect()->to(route('dashboard', absolute: false));
    }
}
