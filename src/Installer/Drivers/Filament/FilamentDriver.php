<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Filament;

use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\TestRunner;
use Symfony\Component\Console\Output\BufferedOutput;
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

    /**
     * Copy the Socialstream test files to the apps "tests" directory for the given test runner.
     */
    protected function copyTests(TestRunner $testRunner): static
    {
        copy(from: match ($testRunner) {
            TestRunner::Pest => __DIR__.'/../../../../stubs/filament/pest-tests/SocialstreamRegistrationTest.php',
            TestRunner::PhpUnit => __DIR__.'/../../../../stubs/filament/tests/SocialstreamRegistrationTest.php',
        }, to: base_path('tests/Feature/SocialstreamRegistrationTest.php'));

        return $this;
    }
}
