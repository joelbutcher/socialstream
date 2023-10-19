<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;

it('can build a composer require command for an array of packages', function () {
    $driver = new class($this->app->make(Kernel::class), $this->app->make(Repository::class)) extends Driver
    {
        protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
        {
        }
    };

    $reflection = new \ReflectionClass($driver);
    $method = $reflection->getMethod('buildBaseComposerCommand');
    $method->setAccessible(true);

    expect($method->invoke($driver, 'require', ['laravel/breeze']))
        ->toEqual([
            'composer', 'require', 'laravel/breeze',
        ]);
});

it('can build a composer require dev command for an array of packages', function () {
    $driver = new class($this->app->make(Kernel::class), $this->app->make(Repository::class)) extends Driver
    {
        protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
        {
        }
    };

    $reflection = new \ReflectionClass($driver);
    $method = $reflection->getMethod('buildBaseComposerCommand');
    $method->setAccessible(true);

    expect($method->invoke($driver, 'require', ['laravel/breeze'], dev: true))
        ->toEqual([
            'composer', 'require', '--dev', 'laravel/breeze',
        ]);
});

it('can build a composer remove dev command for an array of packages', function () {
    $driver = new class($this->app->make(Kernel::class), $this->app->make(Repository::class)) extends Driver
    {
        protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
        {
        }
    };

    $reflection = new \ReflectionClass($driver);
    $method = $reflection->getMethod('buildBaseComposerCommand');
    $method->setAccessible(true);

    expect($method->invoke($driver, 'remove', ['laravel/breeze'], dev: true))
        ->toEqual([
            'composer', 'remove', '--dev', 'laravel/breeze',
        ]);
});
