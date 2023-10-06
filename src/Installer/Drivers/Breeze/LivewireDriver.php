<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Breeze;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Enums\BreezeInstallStack;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use Symfony\Component\Finder\Finder;

class LivewireDriver extends BreezeDriver
{
    /**
     * Specify the stack used by this installer.
     */
    protected static function stack(): BreezeInstallStack
    {
        return BreezeInstallStack::Livewire;
    }

    protected static function directoriesToCreateForStack(): array
    {
        return [
            resource_path('views/livewire/pages/auth/auth'),
            resource_path('views/livewire/profile'),
        ];
    }

    /**
     * Copy all the app files required for the stack.
     */
    protected function copyAppFiles(): static
    {
        return $this;
    }

    /**
     * Copy the auth views to the app "resources" directory for the given stack.
     */
    protected function copyAuthViews(InstallOptions ...$options): static
    {
        copy(__DIR__ . '/../../../../stubs/breeze/livewire/resources/views/livewire/pages/auth/login.blade.php', resource_path('views/livewire/pages/auth/login.blade.php'));
        copy(__DIR__ . '/../../../../stubs/breeze/livewire/resources/views/livewire/pages/auth/register.blade.php', resource_path('views/livewire/pages/auth/register.blade.php'));

        return $this;
    }

    /**
     * Copy the profile views to the app "resources" directory for the given stack.
     */
    protected function copyProfileViews(InstallOptions ...$options): static
    {
        copy(__DIR__ . '/../../../../stubs/breeze/livewire/resources/views/profile.blade.php', resource_path('views/profile.blade.php'));
        copy(__DIR__ . '/../../../../stubs/breeze/livewire/resources/views/livewire/profile/delete-user-form.blade.php', resource_path('views/livewire/profile/delete-user-form.blade.php'));
        copy(__DIR__ . '/../../../../stubs/breeze/livewire/resources/views/livewire/profile/set-password-form.blade.php', resource_path('views/livewire/profile/set-password-form.blade.php'));
        copy(__DIR__ . '/../../../../stubs/breeze/livewire/resources/views/livewire/profile/connected-accounts-form.blade.php', resource_path('views/livewire/profile/connected-accounts-form.blade.php'));

        return $this;
    }

    /**
     * Copy the Socialstream components to the app "resources" directory for the given stack.
     */
    protected function copySocialstreamComponents(InstallOptions ...$options): static
    {
        (new Filesystem)->copyDirectory(__DIR__ . '/../../../../stubs/breeze/default/resources/views/components/socialstream-icons', resource_path('views/components/socialstream-icons'));

        copy(__DIR__ . '/../../../../stubs/breeze/default/resources/views/components/action-link.blade.php', resource_path('views/components/action-link.blade.php'));
        copy(__DIR__ . '/../../../../stubs/breeze/default/resources/views/components/connected-account.blade.php', resource_path('views/components/connected-account.blade.php'));
        copy(__DIR__ . '/../../../../stubs/breeze/livewire/resources/views/components/socialstream.blade.php', resource_path('views/components/socialstream.blade.php'));

        return $this;
    }
}
