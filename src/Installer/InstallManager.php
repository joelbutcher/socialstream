<?php

namespace JoelButcher\Socialstream\Installer;

use Illuminate\Support\Manager;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\BladeDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\FunctionalLivewireDriver as BreezeLivewireFunctionalDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\LivewireDriver as BreezeLivewireDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\ReactInertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Breeze\VueInertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Drivers\Filament\FilamentDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\InertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\LivewireDriver as JetstreamLivewireDriver;

/**
 * @method Driver driver($driver = null)
 */
class InstallManager extends Manager
{
    public function getDefaultDriver(): void
    {

    }

    public function createBreezeBladeDriver(): BladeDriver
    {
        return $this->container->make(BladeDriver::class);
    }

    public function createBreezeLivewireDriver(): BreezeLivewireDriver
    {
        return $this->container->make(BreezeLivewireDriver::class);
    }

    public function createBreezeLivewireFunctionalDriver(): BreezeLivewireFunctionalDriver
    {
        return $this->container->make(BreezeLivewireFunctionalDriver::class);
    }

    public function createBreezeReactDriver(): ReactInertiaDriver
    {
        return $this->container->make(ReactInertiaDriver::class);
    }

    public function createBreezeVueDriver(): VueInertiaDriver
    {
        return $this->container->make(VueInertiaDriver::class);
    }

    public function createJetstreamInertiaDriver(): InertiaDriver
    {
        return $this->container->make(InertiaDriver::class);
    }

    public function createJetstreamLivewireDriver(): JetstreamLivewireDriver
    {
        return $this->container->make(JetstreamLivewireDriver::class);
    }

    public function createFilamentDriver(): FilamentDriver
    {
        return $this->container->make(FilamentDriver::class);
    }
}
