<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Breeze;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Enums\BreezeInstallStack;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;

class ReactInertiaDriver extends BreezeDriver
{
    protected static function stack(): BreezeInstallStack
    {
        return BreezeInstallStack::React;
    }

    protected static function directoriesToCreateForStack(): array
    {
        return [
            app_path('Http/Controllers/Auth'),
            resource_path('js/Components'),
            resource_path('js/Pages/Auth'),
            resource_path('js/Pages/Profile'),
            resource_path('js/Pages/Profile/Partials'),
            resource_path('js/types'),
        ];
    }

    protected function copyAppFiles(): static
    {
        copy(__DIR__.'/../../../../stubs/breeze/default/app/Http/Controllers/Auth/ConnectedAccountController.php', app_path('Http/Controllers/Auth/ConnectedAccountController.php'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia/app/Http/Controllers/Auth/PasswordController.php', app_path('Http/Controllers/Auth/PasswordController.php'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia/app/Http/Controllers/ProfileController.php', app_path('Http/Controllers/ProfileController.php'));

        return $this;
    }

    public function copyAuthViews(InstallOptions ...$options): static
    {
        if (in_array(InstallOptions::TypeScript, $options)) {
            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Pages/Auth/Login.tsx', resource_path('js/Pages/Auth/Login.tsx'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Pages/Auth/Register.tsx', resource_path('js/Pages/Auth/Register.tsx'));

            return $this;
        }

        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Pages/Auth/Login.jsx', resource_path('js/Pages/Auth/Login.jsx'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Pages/Auth/Register.jsx', resource_path('js/Pages/Auth/Register.jsx'));

        return $this;
    }

    public function copyProfileViews(InstallOptions ...$options): static
    {
        if (in_array(InstallOptions::TypeScript, $options)) {
            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Pages/Profile/Edit.tsx', resource_path('js/Pages/Profile/Edit.tsx'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Pages/Profile/Partials/SetPasswordForm.tsx', resource_path('js/Pages/Profile/Partials/SetPasswordForm.tsx'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Pages/Profile/Partials/ConnectedAccountsForm.tsx', resource_path('js/Pages/Profile/Partials/ConnectedAccountsForm.tsx'));

            return $this;
        }

        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Pages/Profile/Edit.jsx', resource_path('js/Pages/Profile/Edit.jsx'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Pages/Profile/Partials/SetPasswordForm.jsx', resource_path('js/Pages/Profile/Partials/SetPasswordForm.jsx'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Pages/Profile/Partials/ConnectedAccountsForm.jsx', resource_path('js/Pages/Profile/Partials/ConnectedAccountsForm.jsx'));

        return $this;
    }

    public function copySocialstreamComponents(InstallOptions ...$options): static
    {
        if (in_array(InstallOptions::TypeScript, $options)) {
            (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Components/SocialstreamIcons', resource_path('js/Components/SocialstreamIcons'));

            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Components/ActionLink.tsx', resource_path('js/Components/ActionLink.tsx'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Components/ConnectedAccount.tsx', resource_path('js/Components/ConnectedAccount.tsx'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/Components/Socialstream.tsx', resource_path('js/Components/Socialstream.tsx'));

            copy(__DIR__.'/../../../../stubs/breeze/inertia-react-ts/resources/js/types/index.d.ts', resource_path('js/types/index.d.ts'));

            return $this;
        }

        (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Components/SocialstreamIcons', resource_path('js/Components/SocialstreamIcons'));

        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Components/ActionLink.jsx', resource_path('js/Components/ActionLink.jsx'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Components/ConnectedAccount.jsx', resource_path('js/Components/ConnectedAccount.jsx'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-react/resources/js/Components/Socialstream.jsx', resource_path('js/Components/Socialstream.jsx'));

        return $this;
    }
}
