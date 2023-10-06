<?php

namespace JoelButcher\Socialstream\Installer;

use Illuminate\Support\Manager;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\BladeDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\LivewireDriver as BreezeLivewireDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\ReactInertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\VueInertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Filament\FilamentDriver;
use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\InertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\LivewireDriver as JetstreamLivewireDriver;

/**
 * @method Driver driver($driver = null)
 */
class InstallManager extends Manager
{
    public function getDefaultDriver(): void
    {
        return;
    }

    public function createInertiaJetstreamDriver(): InertiaDriver
    {
        return $this->container->make(InertiaDriver::class);
    }

    public function createLivewireJetstreamDriver(): JetstreamLivewireDriver
    {
        return $this->container->make(JetstreamLivewireDriver::class);
    }

    public function createFilamentDriver(): FilamentDriver
    {
        return $this->container->make(FilamentDriver::class);
    }

    public function createBladeBreezeDriver(): BladeDriver
    {
        return $this->container->make(BladeDriver::class);
    }

    public function createLivewireBreezeDriver(): BreezeLivewireDriver
    {
        return $this->container->make(BreezeLivewireDriver::class);
    }

    public function createReactBreezeDriver(): ReactInertiaDriver
    {
        return $this->container->make(ReactInertiaDriver::class);
    }

    public function createVueBreezeDriver(): VueInertiaDriver
    {
        return $this->container->make(VueInertiaDriver::class);
    }
}
