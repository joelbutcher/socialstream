<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Jetstream;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\JetstreamInstallStack;
use Symfony\Component\Finder\Finder;

class InertiaDriver extends JetstreamDriver
{
    /**
     * Specify the stack used by this installer.
     */
    protected static function stack(): JetstreamInstallStack
    {
        return JetstreamInstallStack::Inertia;
    }

    /**
     * Define the resource directories that should be checked for existence for the stack.
     */
    protected static function directoriesToCreateForStack(): array
    {
        return [
            app_path('Actions/Jetstream'),
            resource_path('js/Components'),
            resource_path('js/Pages/Auth'),
            resource_path('js/Pages/Profile'),
        ];
    }

    /**
     * Copy the auth views to the app "resources" directory for the given stack.
     */
    protected function copyAuthViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Auth/Login.vue', resource_path('js/Pages/Auth/Login.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Auth/Register.vue', resource_path('js/Pages/Auth/Register.vue'));

        return $this;
    }

    /**
     * Copy the profile views to the app "resources" directory for the given stack.
     */
    protected function copyProfileViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Profile/Partials/ConnectedAccountsForm.vue', resource_path('js/Pages/Profile/Partials/ConnectedAccountsForm.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Profile/Partials/SetPasswordForm.vue', resource_path('js/Pages/Profile/Partials/SetPasswordForm.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Profile/Show.vue', resource_path('js/Pages/Profile/Show.vue'));

        return $this;
    }

    /**
     * Copy the Socialstream components to the app "resources" directory for the given stack.
     */
    protected function copySocialstreamComponents(InstallOptions ...$options): static
    {
        (new Filesystem)->copyDirectory(__DIR__ . '/../../../../stubs/jetstream/inertia/resources/js/Components/SocialstreamIcons', resource_path('js/Components/SocialstreamIcons'));
        copy(__DIR__ . '/../../../../stubs/jetstream/inertia/resources/js/Components/ActionLink.vue', resource_path('js/Components/ActionLink.vue'));
        copy(__DIR__ . '/../../../../stubs/jetstream/inertia/resources/js/Components/ConnectedAccount.vue', resource_path('js/Components/ConnectedAccount.vue'));
        copy(__DIR__ . '/../../../../stubs/jetstream/inertia/resources/js/Components/Socialstream.vue', resource_path('js/Components/Socialstream.vue'));

        return $this;
    }
}
