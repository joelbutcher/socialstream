<?php

namespace JoelButcher\Socialstream\Installer\Drivers\Jetstream;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Installer\Drivers\Driver;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\JetstreamInstallStack;
use JoelButcher\Socialstream\Installer\Enums\TestRunner;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

abstract class JetstreamDriver extends Driver
{
    /**
     * Specify the stack used by this installer.
     */
    abstract protected static function stack(): JetstreamInstallStack;

    /**
     * Copy the Socialstream routes.
     */
    protected function installRoutes(): static
    {
        $stack = static::stack()->value;

        copy(__DIR__.'/../../../../routes/'.$stack.'.php', base_path('routes/socialstream.php'));

        File::append(base_path('routes/web.php'), data: "require __DIR__.'/socialstream.php';");

        return $this;
    }

    /**
     * Check for, and install Laravel Jetstream, if required.
     */
    protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void
    {
        if (file_exists(config_path('jetstream.php'))) {
            return;
        }

        warning('Laravel Jetstream is not installed.');

        spin(function () use ($options, $composerBinary) {
            if (! $this->hasComposerPackage('laravel/jetstream')) {
                $this->requireComposerPackages(['laravel/jetstream'], $composerBinary);
            }

            (new Process([
                $this->phpBinary(),
                'artisan',
                'jetstream:install',
                static::stack()->value,
                "--composer=$composerBinary",
                ...collect($options)->reject(
                    fn (InstallOptions $option) => ! in_array($option, InstallOptions::jetstreamOptions()),
                )->map(
                    fn (InstallOptions $option) => "--$option->value",
                ),
                '--quiet',
            ], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    (new BufferedOutput)->write($output);
                });
        }, message: 'Installing Laravel Jetstream: '.static::stack()->label().'...');

        \Laravel\Prompts\info('Laravel Jetstream has been installed successfully!');
    }

    /**
     * Copy the Socialstream models to the base "app" directory.
     */
    protected function copyModelsAndFactories(): static
    {
        parent::copyModelsAndFactories();

        copy(__DIR__.'/../../../../stubs/jetstream/app/Models/User.php', app_path('Models/User.php'));
        copy(__DIR__.'/../../../../stubs/jetstream/database/factories/UserFactory.php', database_path('factories/UserFactory.php'));

        return $this;
    }

    /**
     * Copy the Socialstream test files to the apps "tests" directory for the stacks given test runner.
     */
    protected function copyTests(TestRunner $testRunner): static
    {
        copy(from: match ($testRunner) {
            TestRunner::Pest => __DIR__.'/../../../../stubs/jetstream/pest-tests/SocialstreamRegistrationTest.php',
            TestRunner::PhpUnit => __DIR__.'/../../../../stubs/jetstream/tests/SocialstreamRegistrationTest.php',
        }, to: base_path('tests/Feature/SocialstreamRegistrationTest.php'));

        return $this;
    }

    /**
     * Copy versions of previous files that are altered to be compatible with Jetstreams' teams feature.
     */
    protected function ensureTeamsCompatibility(InstallOptions ...$options): static
    {
        if (! collect($options)->contains(InstallOptions::Teams)) {
            return $this;
        }

        note('Making Socialstream compatible with teams');

        copy(__DIR__.'/../../../../stubs/app/Actions/Jetstream/DeleteUserWithTeams.php', app_path('Actions/Jetstream/DeleteUser.php'));
        copy(__DIR__.'/../../../../stubs/app/Actions/Socialstream/CreateUserWithTeamsFromProvider.php', app_path('Actions/Socialstream/CreateUserFromProvider.php'));
        copy(__DIR__.'/../../../../stubs/jetstream/app/Models/UserWithTeams.php', app_path('Models/User.php'));
        copy(__DIR__.'/../../../../stubs/app/Providers/TeamsAuthServiceProvider.php', app_path('Providers/AuthServiceProvider.php'));

        return $this;
    }

    /**
     * Execute a script to be run post-installation of the stack.
     */
    protected function postInstall(string $composerBinary, InstallOptions ...$options): void
    {
        $this->ensureTeamsCompatibility(...$options);

        // This action is added when actions are published, this needs to be removed
        (new Filesystem())->delete(app_path('Actions/Socialstream/CreateUserWithTeamsFromProvider.php'));
    }
}
