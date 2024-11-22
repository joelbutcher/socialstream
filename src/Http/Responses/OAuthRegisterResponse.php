<?php

namespace JoelButcher\Socialstream\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JoelButcher\Socialstream\Concerns\ConfirmsFilament;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Contracts\OAuthRegisterResponse as RegisterResponseContract;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Fortify\Contracts\RegisterResponse as FortifyRegisterResponse;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Responses\RegisterResponse;

class OAuthRegisterResponse implements RegisterResponseContract
{
    use ConfirmsFilament;
    use InteractsWithComposer;

    public function toResponse($request): RedirectResponse|RegisterResponse
    {
        return match (true) {
            $this->cameFromFilamentAuthRoute() && $this->usesFilament() && $this->hasFilamentAuthRoutes() => redirect()->route(
                config('socialstream.filament-route', 'filament.admin.pages.dashboard')
            ),
            $this->hasComposerPackage('laravel/jetstream') => $this->fortifyResponse($request),
            default => $this->defaultResponse(),
        };
    }

    private function fortifyResponse(Request $request): JsonResponse|RedirectResponse
    {
        return $request->wantsJson()
            ? new JsonResponse('', 201)
            : redirect()->intended(Fortify::redirects('register'));
    }

    private function defaultResponse(): RedirectResponse
    {
        return Socialstream::redirects('register')
        ? redirect()->intended(Socialstream::redirects('register'))
        : redirect()->to(config('socialstream.home'));
    }
}
