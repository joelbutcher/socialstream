<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Breeze;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Enums\BreezeInstallStack;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;

class VueInertiaDriver extends BreezeDriver
{
    protected static function stack(): BreezeInstallStack
    {
        return BreezeInstallStack::Vue;
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
            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Pages/Auth/Login.vue', resource_path('js/Pages/Auth/Login.vue'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Pages/Auth/Register.vue', resource_path('js/Pages/Auth/Register.vue'));

            return $this;
        }

        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Pages/Auth/Login.vue', resource_path('js/Pages/Auth/Login.vue'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Pages/Auth/Register.vue', resource_path('js/Pages/Auth/Register.vue'));

        return $this;
    }

    public function copyProfileViews(InstallOptions ...$options): static
    {
        if (in_array(InstallOptions::TypeScript, $options)) {
            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Pages/Profile/Edit.vue', resource_path('js/Pages/Profile/Edit.vue'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Pages/Profile/Partials/SetPasswordForm.vue', resource_path('js/Pages/Profile/Partials/SetPasswordForm.vue'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Pages/Profile/Partials/ConnectedAccountsForm.vue', resource_path('js/Pages/Profile/Partials/ConnectedAccountsForm.vue'));

            return $this;
        }

        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Pages/Profile/Edit.vue', resource_path('js/Pages/Profile/Edit.vue'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Pages/Profile/Partials/SetPasswordForm.vue', resource_path('js/Pages/Profile/Partials/SetPasswordForm.vue'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Pages/Profile/Partials/ConnectedAccountsForm.vue', resource_path('js/Pages/Profile/Partials/ConnectedAccountsForm.vue'));

        return $this;
    }

    public function copySocialstreamComponents(InstallOptions ...$options): static
    {
        if (in_array(InstallOptions::TypeScript, $options)) {
            (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Components/SocialstreamIcons', resource_path('js/Components/SocialstreamIcons'));

            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Components/ActionLink.vue', resource_path('js/Components/ActionLink.vue'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Components/ConnectedAccount.vue', resource_path('js/Components/ConnectedAccount.vue'));
            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/Components/Socialstream.vue', resource_path('js/Components/Socialstream.vue'));

            copy(__DIR__.'/../../../../stubs/breeze/inertia-vue-ts/resources/js/types/index.d.ts', resource_path('js/types/index.d.ts'));

            return $this;
        }

        (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Components/SocialstreamIcons', resource_path('js/Components/SocialstreamIcons'));

        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Components/ActionLink.vue', resource_path('js/Components/ActionLink.vue'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Components/ConnectedAccount.vue', resource_path('js/Components/ConnectedAccount.vue'));
        copy(__DIR__.'/../../../../stubs/breeze/inertia-vue/resources/js/Components/Socialstream.vue', resource_path('js/Components/Socialstream.vue'));

        return $this;
    }
}
