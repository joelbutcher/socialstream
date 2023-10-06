<?php

namespace JoelButcher\Socialstream\Installer\Drivers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\TestRunner;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\spin;

abstract class Driver
{
    use InteractsWithComposer;

    public function __construct(
        protected readonly Kernel $artisan,
        protected readonly Repository $config
    ) {
        //
    }

    /**
     * Execute a script to be run pre-installation of the stack.
     */
    protected function preInstall(string $composerBinary, InstallOptions ...$options): static
    {
        return $this;
    }

    /**
     * Execute a script to be run post-installation of the stack.
     */
    protected function postInstall(string $composerBinary, InstallOptions ...$options): void
    {
        //
    }

    public function install(string $composerBinary = 'global', InstallOptions ...$options): void
    {
        $this->ensureDependenciesAreInstalled($composerBinary, ...$options);

        spin(fn () => $this
            ->preInstall($composerBinary, ...$options)
            ->publishFiles()
            ->ensureDirectoriesExist(array_merge(static::directoriesToCreateForStack(), [
                app_path('Actions/Socialstream'),
                app_path('Policies'),
            ]))
            ->installServiceProviders()
            ->copyAppFiles()
            ->copyModelsAndFactories()
            ->copyPolicies()
            ->copyActions()
            ->copyResourceFiles(...$options)
            ->copySocialstreamComponents(...$options)
            ->copyTests(collect($options)->contains(InstallOptions::Pest) ? TestRunner::Pest : TestRunner::PhpUnit)
            ->postInstall($composerBinary, ...$options), message: 'Installing...');

        spin(fn () => $this
            ->optionallyRemoveDarkMode(...$options)
            ->build(), message: 'Scaffolding frontend...');
    }

    /**
     * Check the required dependencies are installed and install them if not
     */
    abstract protected function ensureDependenciesAreInstalled(string $composerBinary, InstallOptions ...$options): void;

    /**
     * Call the underlying vendor:publish command to publish the Socialstream config and required migrations.
     */
    private function publishFiles(): static
    {
        spin(callback: function () {
            $outputStyle = new BufferedOutput;

            (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=socialstream-config', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) use ($outputStyle) {
                    $outputStyle->write($output);
                });

            (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=socialstream-migrations', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) use ($outputStyle) {
                    $outputStyle->write($output);
                });

            (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=socialstream-routes', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) use ($outputStyle) {
                    $outputStyle->write($output);
                });
        }, message: 'Publishing config, migration and route files');

        return $this;
    }

    /**
     * Define the resource directories that should be checked for existence for the stack.
     */
    protected static function directoriesToCreateForStack(): array
    {
        return [];
    }

    /**
     * Makes sure the given directories exist within the application.
     */
    protected function ensureDirectoriesExist(array $directories): static
    {
        foreach ($directories as $directory) {
            (new Filesystem)->ensureDirectoryExists($directory);
        }

        return $this;
    }

    /**
     * Copy the Socialstream service providers.
     */
    protected function installServiceProviders(): static
    {
        copy(__DIR__ . '/../../../stubs/app/Providers/AuthServiceProvider.php', app_path('Providers/AuthServiceProvider.php'));
        copy(__DIR__ . '/../../../stubs/app/Providers/SocialstreamServiceProvider.php', app_path('Providers/SocialstreamServiceProvider.php'));

        $this->installServiceProviderAfter('RouteServiceProvider', 'SocialstreamServiceProvider');

        return $this;
    }

    /**
     * Copy all the app files required for the stack.
     */
    protected function copyAppFiles(): static
    {
        return $this;
    }

    /**
     * Copy all the resource files required for the stack.
     */
    protected function copyResourceFiles(InstallOptions ...$options): static
    {
        $this
            ->copyAuthViews(...$options)
            ->copyProfileViews(...$options);

        return $this;
    }

    /**
     * Copy the Socialstream models to the base "app" directory.
     */
    protected function copyModelsAndFactories(): static
    {
        copy(__DIR__ . '/../../../stubs/app/Models/ConnectedAccount.php', app_path('Models/ConnectedAccount.php'));
        copy(__DIR__ . '/../../../stubs/database/factories/ConnectedAccountFactory.php', database_path('factories/ConnectedAccountFactory.php'));

        return $this;
    }

    /**
     * Copy the Socialstream policies to the base "app" directory.
     */
    protected function copyPolicies(): static
    {
        copy(__DIR__ . '/../../../stubs/app/Policies/ConnectedAccountPolicy.php', app_path('Policies/ConnectedAccountPolicy.php'));

        return $this;
    }

    /**
     * Copy the actions to the base "app" directory.
     */
    protected function copyActions(): static
    {
        copy(__DIR__ . '/../../../stubs/app/Actions/Socialstream/ResolveSocialiteUser.php', app_path('Actions/Socialstream/ResolveSocialiteUser.php'));
        copy(__DIR__ . '/../../../stubs/app/Actions/Socialstream/CreateConnectedAccount.php', app_path('Actions/Socialstream/CreateConnectedAccount.php'));
        copy(__DIR__ . '/../../../stubs/app/Actions/Socialstream/GenerateRedirectForProvider.php', app_path('Actions/Socialstream/GenerateRedirectForProvider.php'));
        copy(__DIR__ . '/../../../stubs/app/Actions/Socialstream/UpdateConnectedAccount.php', app_path('Actions/Socialstream/UpdateConnectedAccount.php'));
        copy(__DIR__ . '/../../../stubs/app/Actions/Socialstream/CreateUserFromProvider.php', app_path('Actions/Socialstream/CreateUserFromProvider.php'));
        copy(__DIR__ . '/../../../stubs/app/Actions/Socialstream/HandleInvalidState.php', app_path('Actions/Socialstream/HandleInvalidState.php'));
        copy(__DIR__ . '/../../../stubs/app/Actions/Socialstream/SetUserPassword.php', app_path('Actions/Socialstream/SetUserPassword.php'));

        return $this;
    }

    /**
     * Copy the auth views to the app "resources" directory for the given stack.
     */
    protected function copyAuthViews(InstallOptions ...$options): static
    {
        return $this;
    }

    /**
     * Copy the profile views to the app "resources" directory for the given stack.
     */
    protected function copyProfileViews(InstallOptions ...$options): static
    {
        return $this;
    }

    /**
     * Copy the Socialstream components to the app "resources" directory for the given stack.
     */
    protected function copySocialstreamComponents(InstallOptions ...$options): static
    {
        return $this;
    }

    /**
     * Copy the Socialstream test files to the apps "tests" directory for the given test runner.
     */
    protected function copyTests(TestRunner $testRunner): static
    {
        return $this;
    }

    /**
     * Remove dark mode classes from the stack, if requested.
     */
    protected function optionallyRemoveDarkMode(InstallOptions ...$options): static
    {
        if (collect($options)->contains(InstallOptions::DarkMode)) {
            return $this;
        }

        $this->removeDarkClasses((new Finder)
            ->in([resource_path('views'), resource_path('js')])
            ->name(['*.blade.php', '*.vue', '*.jsx', '*.tsx'])
            ->notPath(['views/welcome.blade.php', 'Pages/Welcome.vue', 'Pages/Welcome.jsx', 'Pages/Welcome.tsx'])
            ->notName('welcome.blade.php'));

        return $this;
    }

    /**
     * Build the Socialstream frontend.
     */
    private function build(): void
    {
        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }
    }

    /**
     * Replace a given string within a given file.
     */
    protected function replaceInFile(string $search, string $replace, string $path): void
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Remove any dark classes, if dark mode has not been specified
     */
    protected function removeDarkClasses(Finder $finder): void
    {
        foreach ($finder as $file) {
            file_put_contents($file->getPathname(), preg_replace('/\sdark:[^\s"\']+/', '', $file->getContents()));
        }
    }

    /**
     * Execute the given commands using the given environment.
     */
    protected function runCommands(array $commands, array $env = []): Process
    {
        $output = new BufferedOutput();
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, $env, null, null);

        $process->run(function ($type, $line) use ($output) {
            $output->write('    ' . $line);
        });

        return $process;
    }

    /**
     * Install the Socialstream service providers in the application configuration file.
     */
    protected function installServiceProviderAfter(string $after, string $name): void
    {
        if (!Str::contains($appConfig = file_get_contents(config_path('app.php')), 'App\\Providers\\' . $name . '::class')) {
            file_put_contents(config_path('app.php'), str_replace(
                'App\\Providers\\' . $after . '::class,',
                'App\\Providers\\' . $after . '::class,' . PHP_EOL . '        App\\Providers\\' . $name . '::class,',
                $appConfig
            ));
        }
    }

    /**
     * Get the path to the appropriate PHP binary.
     */
    protected function phpBinary(): string
    {
        return (new PhpExecutableFinder())->find(false) ?: 'php';
    }
}
