<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Jetstream;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\JetstreamInstallStack;

class InertiaDriver extends JetstreamDriver
{
    protected static function stack(): JetstreamInstallStack
    {
        return JetstreamInstallStack::Inertia;
    }

    protected static function directoriesToCreateForStack(): array
    {
        return [
            app_path('Actions/Jetstream'),
            resource_path('js/Components'),
            resource_path('js/Pages/Auth'),
            resource_path('js/Pages/Profile'),
        ];
    }

    public function copyAuthViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Auth/Login.vue', resource_path('js/Pages/Auth/Login.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Auth/Register.vue', resource_path('js/Pages/Auth/Register.vue'));

        return $this;
    }

    public function copyProfileViews(InstallOptions ...$options): static
    {
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Profile/Partials/ConnectedAccountsForm.vue', resource_path('js/Pages/Profile/Partials/ConnectedAccountsForm.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Profile/Partials/SetPasswordForm.vue', resource_path('js/Pages/Profile/Partials/SetPasswordForm.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Pages/Profile/Show.vue', resource_path('js/Pages/Profile/Show.vue'));

        return $this;
    }

    public function copySocialstreamComponents(InstallOptions ...$options): static
    {
        (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Components/SocialstreamIcons', resource_path('js/Components/SocialstreamIcons'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Components/ActionLink.vue', resource_path('js/Components/ActionLink.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Components/ConnectedAccount.vue', resource_path('js/Components/ConnectedAccount.vue'));
        copy(__DIR__.'/../../../../stubs/jetstream/inertia/resources/js/Components/Socialstream.vue', resource_path('js/Components/Socialstream.vue'));

        return $this;
    }
}
