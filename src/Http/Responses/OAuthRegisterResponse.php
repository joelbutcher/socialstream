<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\RedirectResponse;
use JoelButcher\Socialstream\Concerns\ConfirmsFilament;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthRegisterResponse as RegisterResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\RegisterResponse as FortifyRegisterResponse;

class OAuthRegisterResponse implements RegisterResponseContract
{
    use ConfirmsFilament;
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse
    {
        return match (true) {
            $this->usesFilament() && $this->hasFilamentAuthRoutes() => redirect()->route(
                config('socialstream.filament-route', 'filament.admin.pages.dashboard')
            ),
            $this->hasComposerPackage('laravel/jetstream') => app(FortifyRegisterResponse::class),
            $this->hasComposerPackage('laravel/breeze') => redirect()->route('dashboard'),
            default => $this->defaultResponse(),
        };
    }

    private function defaultResponse(): RedirectResponse
    {
        return Socialstream::redirects('register')
        ? redirect()->intended(Socialstream::redirects('register'))
        : redirect()->to(route('dashboard', absolute: false));
    }
}
