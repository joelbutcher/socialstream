<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Breeze;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Enums\BreezeInstallStack;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;

class BladeDriver extends BreezeDriver
{
    protected static function stack(): BreezeInstallStack
    {
        return BreezeInstallStack::Blade;
    }

    protected static function directoriesToCreateForStack(): array
    {
        return [
            app_path('Http/Controllers/Auth'),
            resource_path('views/auth'),
            resource_path('views/profile/partials'),
        ];
    }

    public function copyAuthViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/auth/login.blade.php', resource_path('views/auth/login.blade.php'));
        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/auth/register.blade.php', resource_path('views/auth/register.blade.php'));

        return $this;
    }

    public function copyProfileViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/profile/edit.blade.php', resource_path('views/profile/edit.blade.php'));
        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/profile/partials/set-password-form.blade.php', resource_path('views/profile/partials/set-password-form.blade.php'));
        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/profile/partials/connected-accounts-form.blade.php', resource_path('views/profile/partials/connected-accounts-form.blade.php'));

        return $this;
    }

    public function copySocialstreamComponents(InstallOptions ...$options): static
    {
        (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/breeze/default/resources/views/components/socialstream-icons', resource_path('views/components/socialstream-icons'));

        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/components/action-link.blade.php', resource_path('views/components/action-link.blade.php'));
        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/components/connected-account.blade.php', resource_path('views/components/connected-account.blade.php'));
        copy(__DIR__.'/../../../../stubs/breeze/default/resources/views/components/socialstream.blade.php', resource_path('views/components/socialstream.blade.php'));

        return $this;
    }
}
