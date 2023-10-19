<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Filament;

use Illuminate\Filesystem\Filesystem;
use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class FilamentDriver extends Driver
{
    protected function postInstall(string $composerBinary, InstallOptions ...$options): void
    {
        $appConfig = file_get_contents(config_path('app.php'));

        file_put_contents(config_path('app.php'), str_replace(<<<'PHP'
        /*
         * Package Service Providers...
         */
PHP.PHP_EOL, <<<PHP
        /*
         * Package Service Providers...
         */
        JoelButcher\Socialstream\Filament\SocialstreamPanelProvider::class,
PHP.PHP_EOL, $appConfig));
    }

    protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
    {
        if (class_exists('App\Providers\Filament\AdminPanelProvider')) {
            return;
        }

        warning('Filament Admin Panel is not installed.');

        spin(function () use ($composerBinary) {
            if (! $this->hasComposerPackage('filament/filament')) {
                $this->requireComposerPackages(['filament/filament'], $composerBinary);
            }

            (new Process([
                $this->phpBinary(),
                'artisan',
                'filament:install',
                '--panels',
                '--force',
                '--quiet',
            ], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    (new BufferedOutput)->write($output);
                });
        }, message: 'Installing Filament Admin Panel...');

        \Laravel\Prompts\info('Filament Admin Panel has been installed successfully!');
    }

    /**
     * Copy the Socialstream models and their factories to the base "app" directory.
     */
    protected function copyModelsAndFactories(): static
    {
        parent::copyModelsAndFactories();

        copy(__DIR__.'/../../../../stubs/filament/app/Models/User.php', app_path('Models/User.php'));
        copy(__DIR__.'/../../../../stubs/filament/database/factories/UserFactory.php', database_path('factories/UserFactory.php'));

        return $this;
    }

    public function copyAuthViews(InstallOptions ...$options): static
    {
        return $this;
    }

    public function copyProfileViews(InstallOptions ...$options): static
    {
        return $this;
    }

    /**
     * Copy the Socialstream components to the app "resources" directory for the given stack.
     */
    public function copySocialstreamComponents(InstallOptions ...$options): static
    {
        (new Filesystem)->copyDirectory(__DIR__.'/../../../../stubs/filament/resources/views/components/socialstream-icons', resource_path('views/components/socialstream-icons'));

        copy(__DIR__.'/../../../../stubs/filament/resources/views/components/action-link.blade.php', resource_path('views/components/action-link.blade.php'));
        copy(__DIR__.'/../../../../stubs/filament/resources/views/components/input-error.blade.php', resource_path('views/components/input-error.blade.php'));
        copy(__DIR__.'/../../../../stubs/filament/resources/views/components/socialstream.blade.php', resource_path('views/components/socialstream.blade.php'));

        return $this;
    }

    protected function optionallyRemoveDarkMode(InstallOptions ...$options): static
    {
        $this->removeDarkClasses((new Finder)
            ->in([resource_path('views')])
            ->name(['*.blade.php'])
            ->notName('welcome.blade.php'));

        return $this;
    }
}
