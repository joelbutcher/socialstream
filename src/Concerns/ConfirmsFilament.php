<?php

declare(strict_types=1);

namespace JoelButcher\Socialstream\Concerns;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Features;

trait ConfirmsFilament
{
    use InteractsWithComposer;

    public function usesFilament(): bool
    {
        return $this->hasComposerPackage('filament/filament');
    }

    public function hasFilamentAuthRoutes(): bool
    {
        return $this->hasFilamentLoginRoutes() || $this->hasFilamentRegistrationRoutes();
    }

    public function canRegisterUsingFilament(): bool
    {
        $filamentRegistrationEnabled = $this->hasFilamentRegistrationRoutes() ||
            $this->hasFilamentLoginRoutes() && Features::hasCreateAccountOnFirstLoginFeatures();

        if (! $filamentRegistrationEnabled) {
            return false;
        }

        return $this->cameFromFilamentAuthRoute();
    }

    /** Assumes static::canRegisterUsingFilament() returns TRUE. */
    public function cameFromFilamentAuthRoute(): bool
    {
        $previousRoute = Session::get('socialstream.previous_url');

        return in_array($previousRoute, array_filter([
            Route::has('filament.auth.login') ? route('filament.auth.login') : null,
            Route::has('filament.admin.auth.login') ? route('filament.admin.auth.login') : null,
            Route::has('filament.auth.register') ? route('filament.auth.register') : null,
            Route::has('filament.admin.auth.register') ? route('filament.admin.auth.register') : null,
        ]));
    }

    public function hasFilamentLoginRoutes(): bool
    {
        return Route::has('filament.auth.login') || Route::has('filament.admin.auth.login');
    }

    public function hasFilamentRegistrationRoutes(): bool
    {
        return Route::has('filament.auth.register') || Route::has('filament.admin.auth.register');
    }
}
