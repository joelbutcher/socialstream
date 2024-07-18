<?php

declare(strict_types=1);

namespace JoelButcher\Socialstream\Concerns;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

trait ConfirmsFilament
{
    use InteractsWithComposer;

    public function usesFilament(): bool
    {
        return $this->hasComposerPackage('filament/filament');
    }

    public function hasFilamentAuthRoutes(): bool
    {
        return (Route::has('filament.auth.login') && Session::get('socialstream.previous_url') === route('filament.auth.login')) ||
            (Route::has('filament.admin.auth.login') && Session::get('socialstream.previous_url') === route('filament.admin.auth.login')) ||
            (Route::has('filament.auth.register') && Session::get('socialstream.previous_url') === route('filament.auth.register')) ||
            (Route::has('filament.admin.auth.register') && Session::get('socialstream.previous_url') === route('filament.admin.auth.register'));
    }
}
