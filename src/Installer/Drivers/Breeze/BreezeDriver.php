<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Breeze;

use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Enums\BreezeInstallStack;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\TestRunner;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

abstract class BreezeDriver extends Driver
{
    /**
     * Specify the stack used by this installer.
     */
    abstract protected static function stack(): BreezeInstallStack;

    protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
    {
        if (class_exists('App\Http\Controllers\ProfileController') ||
            class_exists('App\Providers\VoltServiceProvider') ||
            class_exists('App\Http\Middleware\HandleInertiaRequests')
        ) {
            return;
        }

        warning('Laravel Breeze is not installed.');

        spin(function () use ($options, $composerBinary) {
            if (! $this->hasComposerPackage('laravel/breeze')) {
                $this->requireComposerPackages(['laravel/breeze:^1.26'], $composerBinary);
            }

            (new Process([
                $this->phpBinary(),
                'artisan',
                'breeze:install',
                static::stack()->value,
                "--composer=$composerBinary",
                ...collect($options)->reject(
                    fn (InstallOptions $option) => ! in_array($option, InstallOptions::breezeOptions()),
                )->map(
                    fn (InstallOptions $option) => "--$option->value",
                ),
                '--quiet',
            ], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    (new BufferedOutput)->write($output);
                });
        }, message: 'Installing Laravel Breeze: '.static::stack()->label().'...');

        \Laravel\Prompts\info('Laravel Breeze has been installed successfully!');
    }

    /**
     * Copy all the app files required for the stack.
     */
    protected function copyAppFiles(): static
    {
        copy(__DIR__.'/../../../../stubs/breeze/default/app/Http/Controllers/Auth/ConnectedAccountController.php', app_path('Http/Controllers/Auth/ConnectedAccountController.php'));
        copy(__DIR__.'/../../../../stubs/breeze/default/app/Http/Controllers/Auth/PasswordController.php', app_path('Http/Controllers/Auth/PasswordController.php'));
        copy(__DIR__.'/../../../../stubs/breeze/default/app/Http/Controllers/ProfileController.php', app_path('Http/Controllers/ProfileController.php'));

        return $this;
    }

    /**
     * Copy the Socialstream models and their factories to the base "app" directory.
     */
    protected function copyModelsAndFactories(): static
    {
        parent::copyModelsAndFactories();

        copy(__DIR__.'/../../../../stubs/breeze/default/app/Models/User.php', app_path('Models/User.php'));
        copy(__DIR__.'/../../../../stubs/breeze/database/factories/UserFactory.php', database_path('factories/UserFactory.php'));

        return $this;
    }

    /**
     * Copy the Socialstream test files to the apps "tests" directory for the given test runner.
     */
    protected function copyTests(TestRunner $testRunner): static
    {
        copy(from: match ($testRunner) {
            TestRunner::Pest => __DIR__.'/../../../../stubs/breeze/pest-tests/SocialstreamRegistrationTest.php',
            TestRunner::PhpUnit => __DIR__.'/../../../../stubs/breeze/tests/SocialstreamRegistrationTest.php',
        }, to: base_path('tests/Feature/SocialstreamRegistrationTest.php'));

        return $this;
    }
}
