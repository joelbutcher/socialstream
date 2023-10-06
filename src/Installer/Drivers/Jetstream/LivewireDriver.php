<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Jetstream;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\JetstreamInstallStack;

class LivewireDriver extends JetstreamDriver
{
    /**
     * Specify the stack used by this installer.
     */
    protected static function stack(): JetstreamInstallStack
    {
        return JetstreamInstallStack::Livewire;
    }

    /**
     * Define the resource directories that should be checked for existence for the stack.
     */
    protected static function directoriesToCreateForStack(): array
    {
        return [
            app_path('Actions/Jetstream'),
            resource_path('views/auth'),
            resource_path('views/components'),
            resource_path('views/profile'),
        ];
    }

    /**
     * Copy the auth views to the app "resources" directory for the given stack.
     */
    protected function copyAuthViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/auth/login.blade.php', resource_path('views/auth/login.blade.php'));
        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/auth/register.blade.php', resource_path('views/auth/register.blade.php'));

        return $this;
    }

    /**
     * Copy the profile views to the app "resources" directory for the given stack.
     */
    protected function copyProfileViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/profile/connected-accounts-form.blade.php', resource_path('views/profile/connected-accounts-form.blade.php'));
        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/profile/set-password-form.blade.php', resource_path('views/profile/set-password-form.blade.php'));
        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/profile/show.blade.php', resource_path('views/profile/show.blade.php'));

        return $this;
    }

    /**
     * Copy the Socialstream components to the app "resources" directory for the given stack.
     */
    protected function copySocialstreamComponents(InstallOptions ...$options): static
    {
        (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/components/socialstream-icons', resource_path('views/components/socialstream-icons'));

        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/components/action-link.blade.php', resource_path('views/components/action-link.blade.php'));
        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/components/connected-account.blade.php', resource_path('views/components/connected-account.blade.php'));
        copy(__DIR__.'/../../../../stubs/jetstream/livewire/resources/views/components/socialstream.blade.php', resource_path('views/components/socialstream.blade.php'));

        return $this;
    }
}
