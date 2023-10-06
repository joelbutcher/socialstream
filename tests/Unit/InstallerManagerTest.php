<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use JoelButcher\Socialstream\Installer\Drivers\Breeze\BladeDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\InertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\LivewireDriver;
use JoelButcher\Socialstream\Installer\InstallManager;

it('resolves the inertia driver for Laravel Jetstream', function () {
    expect(app(InstallManager::class)->driver('inertia-jetstream'))
        ->toBeInstanceOf(InertiaDriver::class);
});

it('resolves the livewire driver for Laravel Jetstream', function () {
    expect(app(InstallManager::class)->driver('livewire-jetstream'))
        ->toBeInstanceOf(LivewireDriver::class);
});

it('resolves the blade driver for Laravel Breeze', function () {
    expect(app(InstallManager::class)->driver('blade-breeze'))
        ->toBeInstanceOf(BladeDriver::class);
});

it('throws if no provider is given', function () {
    expect(fn () => app(InstallManager::class)->driver())
        ->toThrow(\InvalidArgumentException::class, sprintf('Unable to resolve NULL driver for [%s].', InstallManager::class));
});
