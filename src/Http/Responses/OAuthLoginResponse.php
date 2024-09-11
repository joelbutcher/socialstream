<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JoelButcher\Socialstream\Concerns\ConfirmsFilament;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthLoginResponse as LoginResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\LoginResponse as FortifyLoginResponse;
use Laravel\Fortify\Fortify;

class OAuthLoginResponse implements LoginResponseContract
{
    use ConfirmsFilament;
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse|FortifyLoginResponse
    {
        return match (true) {
            $this->cameFromFilamentAuthRoute() && $this->usesFilament() && $this->hasFilamentAuthRoutes() => redirect()->route(
                config('socialstream.filament-route', 'filament.admin.pages.dashboard')
            ),
            $this->hasComposerPackage('laravel/jetstream') => $this->fortifyResponse($request),
            $this->hasComposerPackage('laravel/breeze') => redirect()->route('dashboard'),
            default => $this->defaultResponse(),
        };
    }

    private function fortifyResponse(Request $request): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended(Fortify::redirects('login'));
    }

    private function defaultResponse(): RedirectResponse
    {
        return Socialstream::redirects('login')
            ? redirect()->intended(Socialstream::redirects('login'))
            : redirect()->to(route('dashboard', absolute: false));
    }
}
