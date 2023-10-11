<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use JoelButcher\Socialstream\Installer\Drivers\Breeze\BladeDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\FunctionalLivewireDriver as BreezeLivewireFunctionalDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\LivewireDriver as BreezeLivewireDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\ReactInertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\VueInertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Filament\FilamentDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\InertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\LivewireDriver;
use JoelButcher\Socialstream\Installer\InstallManager;

it('resolves the inertia driver for Laravel Jetstream', function () {
    expect(app(InstallManager::class)->driver('jetstream-inertia'))
        ->toBeInstanceOf(InertiaDriver::class);
});

it('resolves the livewire driver for Laravel Jetstream', function () {
    expect(app(InstallManager::class)->driver('jetstream-livewire'))
        ->toBeInstanceOf(LivewireDriver::class);
});

it('resolves the blade driver for Laravel Breeze', function () {
    expect(app(InstallManager::class)->driver('breeze-blade'))
        ->toBeInstanceOf(BladeDriver::class);
});

it('resolves the livewire driver for Laravel Breeze', function () {
    expect(app(InstallManager::class)->driver('breeze-livewire'))
        ->toBeInstanceOf(BreezeLivewireDriver::class);
});

it('resolves the functional livewire driver for Laravel Breeze', function () {
    expect(app(InstallManager::class)->driver('breeze-livewire-functional'))
        ->toBeInstanceOf(BreezeLivewireFunctionalDriver::class);
});

it('resolves the react driver for Laravel Breeze', function () {
    expect(app(InstallManager::class)->driver('breeze-react'))
        ->toBeInstanceOf(ReactInertiaDriver::class);
});

it('resolves the vue driver for Laravel Breeze', function () {
    expect(app(InstallManager::class)->driver('breeze-vue'))
        ->toBeInstanceOf(VueInertiaDriver::class);
});

it('resolves the  driver for Filament', function () {
    expect(app(InstallManager::class)->driver('filament'))
        ->toBeInstanceOf(FilamentDriver::class);
});

it('throws if no provider is given', function () {
    expect(fn () => app(InstallManager::class)->driver())
        ->toThrow(\InvalidArgumentException::class, sprintf('Unable to resolve NULL driver for [%s].', InstallManager::class));
});
