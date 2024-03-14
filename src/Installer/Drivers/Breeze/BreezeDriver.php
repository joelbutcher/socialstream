<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Breeze;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
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
                $this->requireComposerPackages(['laravel/breeze:^2.0'], $composerBinary);
            }

            (new Process([
                $this->phpBinary(),
                'artisan',
                'breeze:install',
                static::stack()->value,
                "--composer=$composerBinary",
                ...collect($options)->reject(
                    fn (InstallOptions $option) => ! in_array($option, InstallOptions::breezeOptions(static::stack())),
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
     * Copy the Socialstream routes.
     */
    protected function installRoutes(): static
    {
        $folder = Str::of(match(static::stack()) {
            BreezeInstallStack::Blade,
            BreezeInstallStack::Livewire,
            BreezeInstallStack::FunctionalLivewire => 'livewire',
            BreezeInstallStack::Vue,
            BreezeInstallStack::React, => 'inertia',
        })->lower()->toString();

        copy(__DIR__.'/../../../../stubs/breeze/default/routes/socialstream.php', base_path('routes/socialstream.php'));

        File::append(base_path('routes/web.php'), data: "require __DIR__.'/socialstream.php';");

        return $this;
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
