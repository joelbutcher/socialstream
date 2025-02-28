<?php

namespace JoelButcher\Socialstream\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

#[AsCommand(name: 'socialstream:install')]
class InstallCommand extends Command implements PromptsForMissingInput
{
    use InteractsWithComposer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialstream:install
                            {--react : Install Socialstream for the React Starter Kit}
                            {--vue : Install Socialstream for the Vue Starter Kit}
                            {--livewire : Install Socialstream for the Livewire Starter Kit}
                            {--pest : Use pest as the preferred test runner}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Socialstream components and resources';

    /**
     * Execute the console command.
     */
    public function handle(): ?int
    {
        if ($this->alreadyInstalled()) {
            warning('Socialstream is already installed.');

            return self::FAILURE;
        }

        if ($this->conflictsWithWorkOS()) {
            warning('Socialstream conflicts with WorkOS. Please uninstall WorkOS before installing Socialstream.');

            return self::FAILURE;
        }

        $this->installFor(
            $stack = $this->stack()
        );

        $this->runDatabaseMigrations();

        $this->runCommands(['npm install', 'npm run build']);
        $this->line('');

        $this->components->info("Socialstream has been installed for the $stack starter kit.");

        return self::SUCCESS;
    }

    /**
     * Install Socialstream for the given stack.
     */
    protected function installFor(string $stack): void
    {
        $this->ensureDirectoriesExist([
            app_path('Models'),
            app_path('Models/Providers'),
            database_path('factories'),
        ]);

        $this->publishConfigAndMigrations();

        ServiceProvider::addProviderToBootstrapFile('JoelButcher\Socialstream\SocialstreamServiceProvider');

        // Models
        $this->copyFiles([
            __DIR__.'/../../stubs/app/Models/User.php' => app_path('Models/User.php'),
            __DIR__.'/../../stubs/app/Models/ConnectedAccount.php' => app_path('Models/ConnectedAccount.php'),
            __DIR__.'/../../stubs/database/Factories/ConnectedAccountFactory.php' => database_path('factories/ConnectedAccountFactory.php'),
        ]);

        // Stack specific service provider
        $this->copyFiles([
            __DIR__."/../../stubs/$stack/app/Providers/SocialstreamServiceProvider.php" => app_path('Providers/SocialstreamServiceProvider.php'),
        ]);
        ServiceProvider::addProviderToBootstrapFile('App\Providers\SocialstreamServiceProvider');

        if (in_array($stack, ['react', 'vue'])) {
            $this->installInertia();

            match ($stack) {
                'react' => $this->installReact(),
                'vue' => $this->installVue(),
            };

            return;
        }

        $this->installLivewire();
    }

    /**
     * Install Socialstream for either the React, or Vue starter kit.
     */
    protected function installInertia(): void
    {
        $this->ensureDirectoriesExist([
            app_path('Http/Middleware'),
            resource_path('js/types'),
        ]);

        // Middleware
        $this->copyFiles([
            __DIR__.'/../../stubs/inertia/app/Http/Middleware/HandleInertiaRequests.php' => app_path('Http/Middleware/HandleInertiaRequests.php'),
        ]);

        // Types
        $this->copyFiles([
            __DIR__.'/../../stubs/inertia/resources/js/types/index.ts' => resource_path('js/types/index.ts'),
            __DIR__.'/../../stubs/inertia/resources/js/types/socialstream.ts' => resource_path('js/types/socialstream.ts'),
        ]);
    }

    /**
     * Install Socialstream for the React starter kit.
     */
    protected function installReact(): void
    {
        $this->ensureDirectoriesExist([
            app_path('Http/Controllers/Settings'),
            resource_path('js/components/SocialstreamIcons'),
            resource_path('js/components'),
            resource_path('js/layouts/settings'),
            resource_path('js/pages/auth'),
            resource_path('js/pages/settings'),
        ]);

        // Controllers
        $this->copyFiles([
            __DIR__.'/../../stubs/react/app/Http/Controllers/Settings/AvatarController.php' => app_path('Http/Controllers/Settings/AvatarController.php'),
            __DIR__.'/../../stubs/react/app/Http/Controllers/Settings/LinkedAccountController.php' => app_path('Http/Controllers/Settings/LinkedAccountController.php'),
            __DIR__.'/../../stubs/react/app/Http/Controllers/Settings/PasswordController.php' => app_path('Http/Controllers/Settings/PasswordController.php'),
            __DIR__.'/../../stubs/react/app/Http/Controllers/Settings/ProfileController.php' => app_path('Http/Controllers/Settings/ProfileController.php'),
        ]);

        // Components
        $this->copyFiles([
            __DIR__.'/../../stubs/react/resources/js/components/linked-account.tsx' => resource_path('js/components/linked-account.tsx'),
            __DIR__.'/../../stubs/react/resources/js/components/socialstream.tsx' => resource_path('js/components/socialstream.tsx'),
            __DIR__.'/../../stubs/react/resources/js/components/socialstream-icon.tsx' => resource_path('js/components/socialstream-icon.tsx'),
            __DIR__.'/../../stubs/react/resources/js/components/user-info.tsx' => resource_path('js/components/user-info.tsx'),
        ]);

        // Layouts
        $this->copyFiles([
            __DIR__.'/../../stubs/react/resources/js/layouts/settings/layout.tsx' => resource_path('js/layouts/settings/layout.tsx'),
        ]);

        // Auth Pages
        $this->copyFiles([
            __DIR__.'/../../stubs/react/resources/js/pages/auth/confirm-link-account.tsx' => resource_path('js/pages/auth/confirm-link-account.tsx'),
            __DIR__.'/../../stubs/react/resources/js/pages/auth/login.tsx' => resource_path('js/pages/auth/login.tsx'),
            __DIR__.'/../../stubs/react/resources/js/pages/auth/register.tsx' => resource_path('js/pages/auth/register.tsx'),
        ]);

        // Settings Pages
        $this->copyFiles([
            __DIR__.'/../../stubs/react/resources/js/pages/settings/linked-accounts.tsx' => resource_path('js/pages/settings/linked-accounts.tsx'),
            __DIR__.'/../../stubs/react/resources/js/pages/settings/password.tsx' => resource_path('js/pages/settings/password.tsx'),
        ]);

        // Routes
        $this->copyFiles([
            __DIR__.'/../../stubs/react/routes/settings.php' => base_path('routes/settings.php'),
        ]);
    }

    /**
     * Install Socialstream for the Vue starter kit.
     */
    protected function installVue(): void
    {
        $this->ensureDirectoriesExist([
            app_path('Http/Controllers/Settings'),
            resource_path('js/components/SocialstreamIcons'),
            resource_path('js/components'),
            resource_path('js/layouts/settings'),
            resource_path('js/pages/auth'),
            resource_path('js/pages/settings'),
        ]);

        // Controllers
        $this->copyFiles([
            __DIR__.'/../../stubs/vue/app/Http/Controllers/Settings/AvatarController.php' => app_path('Http/Controllers/Settings/AvatarController.php'),
            __DIR__.'/../../stubs/vue/app/Http/Controllers/Settings/LinkedAccountController.php' => app_path('Http/Controllers/Settings/LinkedAccountController.php'),
            __DIR__.'/../../stubs/vue/app/Http/Controllers/Settings/PasswordController.php' => app_path('Http/Controllers/Settings/PasswordController.php'),
            __DIR__.'/../../stubs/vue/app/Http/Controllers/Settings/ProfileController.php' => app_path('Http/Controllers/Settings/ProfileController.php'),
        ]);

        // Icons
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/vue/resources/js/components/SocialstreamIcons', resource_path('js/components/SocialstreamIcons'));

        // Components
        $this->copyFiles([
            __DIR__.'/../../stubs/vue/resources/js/components/LinkedAccount.vue' => resource_path('js/components/LinkedAccount.vue'),
            __DIR__.'/../../stubs/vue/resources/js/components/Socialstream.vue' => resource_path('js/components/Socialstream.vue'),
            __DIR__.'/../../stubs/vue/resources/js/components/SocialstreamIcon.vue' => resource_path('js/components/SocialstreamIcon.vue'),
            __DIR__.'/../../stubs/vue/resources/js/components/UserInfo.vue' => resource_path('js/components/UserInfo.vue'),
        ]);

        // Layouts
        $this->copyFiles([
            __DIR__.'/../../stubs/vue/resources/js/layouts/settings/Layout.vue' => resource_path('js/layouts/settings/Layout.vue'),
        ]);

        // Auth Pages
        $this->copyFiles([
            __DIR__.'/../../stubs/vue/resources/js/pages/auth/ConfirmLinkAccount.vue' => resource_path('js/pages/auth/ConfirmLinkAccount.vue'),
            __DIR__.'/../../stubs/vue/resources/js/pages/auth/Login.vue' => resource_path('js/pages/auth/Login.vue'),
            __DIR__.'/../../stubs/vue/resources/js/pages/auth/Register.vue' => resource_path('js/pages/auth/Register.vue'),
        ]);

        // Settings Pages
        $this->copyFiles([
            __DIR__.'/../../stubs/vue/resources/js/pages/settings/LinkedAccounts.vue' => resource_path('js/pages/settings/LinkedAccounts.vue'),
            __DIR__.'/../../stubs/vue/resources/js/pages/settings/Password.vue' => resource_path('js/pages/settings/Password.vue'),
        ]);

        // Routes
        $this->copyFiles([
            __DIR__.'/../../stubs/vue/routes/settings.php' => base_path('routes/settings.php'),
        ]);
    }

    /**
     * Install Socialstream for the Livewire starter kit.
     */
    protected function installLivewire(): void
    {
        $this->ensureDirectoriesExist([
            resource_path('views/components/layouts/app'),
            resource_path('views/components/layouts/settings'),
            resource_path('views/components/socialstream-icons'),
            resource_path('views/livewire/auth'),
            resource_path('views/livewire/settings'),
        ]);

        // Layouts
        $this->copyFiles([
            __DIR__.'/../../stubs/livewire/resources/views/components/layouts/app/sidebar.blade.php' => resource_path('views/components/layouts/app/sidebar.blade.php'),
            __DIR__.'/../../stubs/livewire/resources/views/components/layouts/settings/layout.blade.php' => resource_path('views/components/layouts/settings/layout..blade.php'),
        ]);

        // Icons
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/components/socialstream-icons', resource_path('views/components/socialstream-icons'));

        // Components
        $this->copyFiles([
            __DIR__.'/../../stubs/livewire/resources/views/components/socialstream.blade.php' => resource_path('views/components/socialstream.blade.php'),
            __DIR__.'/../../stubs/livewire/resources/views/components/socialstream-icon.blade.php' => resource_path('views/components/socialstream-icon.blade.php'),
        ]);

        // Auth Views
        $this->copyFiles([
            __DIR__.'/../../stubs/livewire/resources/views/livewire/auth/confirm-link-account.blade.php' => resource_path('views/livewire/auth/confirm-link-account.blade.php'),
            __DIR__.'/../../stubs/livewire/resources/views/livewire/auth/login.blade.php' => resource_path('views/livewire/auth/login.blade.php'),
            __DIR__.'/../../stubs/livewire/resources/views/livewire/auth/register.blade.php' => resource_path('views/livewire/auth/register.blade.php'),
        ]);

        // Settings Views
        $this->copyFiles([
            __DIR__.'/../../stubs/livewire/resources/views/livewire/settings/linked-account.blade.php' => resource_path('views/livewire/settings/linked-account.blade.php'),
            __DIR__.'/../../stubs/livewire/resources/views/livewire/settings/linked-accounts.blade.php' => resource_path('views/livewire/settings/linked-accounts.blade.php'),
            __DIR__.'/../../stubs/livewire/resources/views/livewire/settings/password.blade.php' => resource_path('views/livewire/settings/password.blade.php'),
            __DIR__.'/../../stubs/livewire/resources/views/livewire/settings/update-avatar.blade.php' => resource_path('views/livewire/settings/update-avatar.blade.php'),
        ]);

        // Routes
        $this->copyFiles([
            __DIR__.'/../../stubs/livewire/routes/web.php' => base_path('routes/web.php'),
        ]);
    }

    /**
     * Call the underlying vendor:publish commands to publish the Socialstream config and migrations.
     */
    protected function publishConfigAndMigrations(): static
    {
        spin(callback: function () {
            $outputStyle = new BufferedOutput;

            (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=socialstream-config'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) use ($outputStyle) {
                    $outputStyle->write($output);
                });

            if (! $this->migrationsExist()) {
                (new Process([$this->phpBinary(), 'artisan', 'vendor:publish', '--tag=socialstream-migrations'], base_path()))
                    ->setTimeout(null)
                    ->run(function ($type, $output) use ($outputStyle) {
                        $outputStyle->write($output);
                    });
            }
        }, message: 'Publishing config and migration');

        return $this;
    }

    /**
     * Determine which stack to install Socialstream for.
     */
    protected function stack(): string
    {
        $stack = $this->detectStack();

        if (
            ! $stack &&
            ! $this->option('react') &&
            ! $this->option('vue') &&
            ! $this->option('livewire')
        ) {

            return select(
                label: 'Which starter kit are you using?',
                options: [
                    'react' => 'React Starter Kit',
                    'vue' => 'Vue Starter Kit',
                    'livewire' => 'Livewire Starter Kit',
                ],
            );
        }

        return $stack ?? collect([
            'react',
            'vue',
            'livewire',
        ])->filter(fn ($stack) => $this->option($stack))->first();
    }

    /**
     * Detect the stack that is installed.
     */
    protected function detectStack(): ?string
    {
        if (class_exists('\App\Providers\VoltServiceProvider')) {
            return 'livewire';
        }

        if (file_exists(resource_path('js/app.tsx'))) {
            return 'react';
        }

        if (file_exists(resource_path('js/app.ts'))) {
            return 'vue';
        }

        return null;
    }

    /**
     * Determine if Socialstream is already installed.
     */
    protected function alreadyInstalled(): bool
    {
        return file_exists(config_path('socialstream.php'));
    }

    /**
     * Determine if Socialstream conflicts with WorkOS.
     */
    protected function conflictsWithWorkOS(): bool
    {
        return $this->hasComposerPackage('laravel/workos');
    }

    /**
     * Determine if the migrations already exist.
     */
    protected function migrationsExist(): bool
    {
        $stubs = ['update_users_table.php', 'create_connected_accounts_table.php'];
        $path = database_path('migrations/');

        $files = (new Filesystem)->files($path);

        foreach ($files as $file) {
            $migrationName = $file->getFilename();
            $migrationName = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $migrationName);

            if (in_array($migrationName, $stubs)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Makes sure the given directories exist within the application.
     */
    protected function ensureDirectoriesExist(array $directories): void
    {
        foreach ($directories as $directory) {
            (new Filesystem)->ensureDirectoryExists($directory);
        }
    }

    /**
     * Copy the given files to the application.
     */
    protected function copyFiles(array $files): void
    {
        $filesystem = (new Filesystem);

        foreach ($files as $location => $destination) {
            $filesystem->ensureDirectoryExists(dirname($destination));
            $filesystem->copy($location, $destination);
        }
    }

    /**
     * Run the database migrations.
     */
    protected function runDatabaseMigrations(): void
    {
        if (confirm('New database migrations were added. Would you like to re-run your migrations?', true)) {
            (new Process([$this->phpBinary(), 'artisan', 'migrate:fresh', '--force'], base_path()))
                ->setTimeout(null)
                ->run(function ($type, $output) {
                    $this->output->write($output);
                });
        }
    }

    /**
     * Run the given commands.
     */
    protected function runCommands(array $commands): void
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> '.$e->getMessage().PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });
    }
}
