<?php

namespace JoelButcher\Socialstream\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialstream:install 
                            {--stack= : Indicates the desired stack to be installed (Livewire, Inertia)}
                            {--teams : Indicates if team support should be installed}
                            {--api : Indicates if API support should be installed}
                            {--verification : Indicates if email verification support should be installed}
                            {--pest : Indicates if Pest should be installed}
                            {--ssr : Indicates if Inertia SSR support should be installed}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Socialstream components and resources';

    /**
     * Execute the console command.
     *
     * @return int|null
     */
    public function handle()
    {
        // Check if Jetstream has been installed.
        if (! file_exists(config_path('jetstream.php'))) {
            $this->components->warn('Jetstream hasn\'t been installed. Installing now...');

            $stack = $this->option('stack') ?: $this->components->choice('Which stack would you like to use [inertia] or [livewire]?', ['inertia', 'livewire']);

            if (! in_array($stack, ['inertia', 'livewire'])) {
                $this->components->error('Invalid stack. Supported stacks are [inertia] and [livewire].');

                return 1;
            }

            $this->callSilent('jetstream:install', [
                'stack' => $stack,
                '--teams' => $this->option('teams'),
                '--api' => $this->option('api'),
                '--verification' => $this->option('verification'),
                '--pest' => $this->option('pest'),
                '--ssr' => $this->option('ssr'),
                '--composer' => $this->option('composer'),
            ]);
        } else {
            $stack = config('jetstream.stack');
        }

        // Publish...
        $this->callSilent('vendor:publish', ['--tag' => 'socialstream-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'socialstream-migrations', '--force' => true]);

        if ($stack === 'livewire') {
            $this->installLivewireStack();
        } elseif ($stack === 'inertia') {
            $this->installInertiaStack();
        }

        if ($this->option('teams')) {
            $this->ensureTeamsCompatibility();
        }

        // Tests...
        $stubs = $this->getTestStubsPath();
        copy($stubs.'/SocialstreamRegistrationTest.php', base_path('tests/Feature/SocialstreamRegistrationTest.php'));

        $this->line('');
        $this->components->info('Socialstream installed successfully.');
        $this->components->info('Running [npm install && npm run build]...');

        $this->installNodeDependenciesAndBuild();

        return 0;
    }

    /**
     * Install the Livewire stack into the application.
     *
     * @return void
     */
    protected function installLivewireStack()
    {
        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Jetstream'));
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Socialstream'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/auth'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/profile'));
        (new Filesystem)->ensureDirectoryExists(resource_path('views/components'));

        // Service Providers...
        copy(__DIR__.'/../../stubs/app/Providers/AuthServiceProvider.php', app_path('Providers/AuthServiceProvider.php'));
        copy(__DIR__.'/../../stubs/app/Providers/SocialstreamServiceProvider.php', app_path('Providers/SocialstreamServiceProvider.php'));
        $this->installServiceProviderAfter('JetstreamServiceProvider', 'SocialstreamServiceProvider');

        // Models...
        copy(__DIR__.'/../../stubs/app/Models/User.php', app_path('Models/User.php'));
        copy(__DIR__.'/../../stubs/app/Models/ConnectedAccount.php', app_path('Models/ConnectedAccount.php'));

        // Policies
        (new Filesystem)->ensureDirectoryExists(app_path('Policies'));
        copy(__DIR__.'/../../stubs/app/Policies/ConnectedAccountPolicy.php', app_path('Policies/ConnectedAccountPolicy.php'));

        // Jetstream Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUser.php', app_path('Actions/Jetstream/DeleteUser.php'));

        // Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/ResolveSocialiteUser.php', app_path('Actions/Socialstream/ResolveSocialiteUser.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/CreateConnectedAccount.php', app_path('Actions/Socialstream/CreateConnectedAccount.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/UpdateConnectedAccount.php', app_path('Actions/Socialstream/UpdateConnectedAccount.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/CreateUserFromProvider.php', app_path('Actions/Socialstream/CreateUserFromProvider.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/HandleInvalidState.php', app_path('Actions/Socialstream/HandleInvalidState.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/SetUserPassword.php', app_path('Actions/Socialstream/SetUserPassword.php'));

        // Auth views...
        copy(__DIR__.'/../../stubs/livewire/resources/views/auth/login.blade.php', resource_path('views/auth/login.blade.php'));
        copy(__DIR__.'/../../stubs/livewire/resources/views/auth/register.blade.php', resource_path('views/auth/register.blade.php'));

        // Custom components...
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/livewire/resources/views/components', resource_path('views/components'));

        // Profile views...
        copy(__DIR__.'/../../stubs/livewire/resources/views/profile/connected-accounts-form.blade.php', resource_path('views/profile/connected-accounts-form.blade.php'));
        copy(__DIR__.'/../../stubs/livewire/resources/views/profile/set-password-form.blade.php', resource_path('views/profile/set-password-form.blade.php'));
        copy(__DIR__.'/../../stubs/livewire/resources/views/profile/show.blade.php', resource_path('views/profile/show.blade.php'));

        $this->replaceInFile('// Providers::github(),', 'Providers::github(),', config_path('socialstream.php'));
    }

    /**
     * Install the Inertia stack into the application.
     *
     * @return void
     */
    protected function installInertiaStack()
    {
        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Jetstream'));
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/Socialstream'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Socialstream'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages/Auth'));
        (new Filesystem)->ensureDirectoryExists(resource_path('js/Pages/Profile'));

        // Service Providers...
        copy(__DIR__.'/../../stubs/app/Providers/AuthServiceProvider.php', app_path('Providers/AuthServiceProvider.php'));
        copy(__DIR__.'/../../stubs/app/Providers/SocialstreamServiceProvider.php', app_path('Providers/SocialstreamServiceProvider.php'));
        $this->installServiceProviderAfter('JetstreamServiceProvider', 'SocialstreamServiceProvider');

        // Models...
        copy(__DIR__.'/../../stubs/app/Models/User.php', app_path('Models/User.php'));
        copy(__DIR__.'/../../stubs/app/Models/ConnectedAccount.php', app_path('Models/ConnectedAccount.php'));

        // Policies
        (new Filesystem)->ensureDirectoryExists(app_path('Policies'));
        copy(__DIR__.'/../../stubs/app/Policies/ConnectedAccountPolicy.php', app_path('Policies/ConnectedAccountPolicy.php'));

        // Jetstream Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUser.php', app_path('Actions/Jetstream/DeleteUser.php'));

        // Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/ResolveSocialiteUser.php', app_path('Actions/Socialstream/ResolveSocialiteUser.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/CreateConnectedAccount.php', app_path('Actions/Socialstream/CreateConnectedAccount.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/UpdateConnectedAccount.php', app_path('Actions/Socialstream/UpdateConnectedAccount.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/CreateUserFromProvider.php', app_path('Actions/Socialstream/CreateUserFromProvider.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/HandleInvalidState.php', app_path('Actions/Socialstream/HandleInvalidState.php'));
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/SetUserPassword.php', app_path('Actions/Socialstream/SetUserPassword.php'));

        // Auth views...
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/Auth/Login.vue', resource_path('js/Pages/Auth/Login.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/Auth/Register.vue', resource_path('js/Pages/Auth/Register.vue'));

        // Profile views...
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/Profile/ConnectedAccountsForm.vue', resource_path('js/Pages/Profile/Partials/ConnectedAccountsForm.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/Profile/SetPasswordForm.vue', resource_path('js/Pages/Profile/Partials/SetPasswordForm.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Pages/Profile/Show.vue', resource_path('js/Pages/Profile/Show.vue'));

        // Socialstream components
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Components/SocialstreamIcons', resource_path('js/Components/SocialstreamIcons'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Components/ActionLink.vue', resource_path('js/Components/ActionLink.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Components/ConnectedAccount.vue', resource_path('js/Components/ConnectedAccount.vue'));
        copy(__DIR__.'/../../stubs/inertia/resources/js/Components/Socialstream.vue', resource_path('js/Components/Socialstream.vue'));

        $this->replaceInFile('// Providers::github(),', 'Providers::github(),', config_path('socialstream.php'));
    }

    /**
     * Ensure the application is ready for Jetstream's "teams" feature.
     *
     * @return void
     */
    protected function ensureTeamsCompatibility()
    {
        // Service Provider...
        copy(__DIR__.'/../../stubs/app/Providers/TeamsAuthServiceProvider.php', app_path('Providers/AuthServiceProvider.php'));

        // User Model...
        copy(__DIR__.'/../../stubs/app/Models/UserWithTeams.php', app_path('Models/User.php'));

        // Jetstream Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUserWithTeams.php', app_path('Actions/Jetstream/DeleteUser.php'));

        // Actions...
        copy(__DIR__.'/../../stubs/app/Actions/Socialstream/CreateUserWithTeamsFromProvider.php', app_path('Actions/Socialstream/CreateUserFromProvider.php'));
    }

    /**
     * Install the Jetstream service providers in the application configuration file.
     *
     * @param  string  $after
     * @param  string  $name
     * @return void
     */
    protected function installServiceProviderAfter($after, $name)
    {
        if (! Str::contains($appConfig = file_get_contents(config_path('app.php')), 'App\\Providers\\'.$name.'::class')) {
            file_put_contents(config_path('app.php'), str_replace(
                'App\\Providers\\'.$after.'::class,',
                'App\\Providers\\'.$after.'::class,'.PHP_EOL.'        App\\Providers\\'.$name.'::class,',
                $appConfig
            ));
        }
    }

    /**
     * Returns the path to the correct test stubs.
     *
     * @return string
     */
    protected function getTestStubsPath()
    {
        return $this->option('pest')
            ? __DIR__.'/../../stubs/pest-tests'
            : __DIR__.'/../../stubs/tests';
    }

    /**
     * Install the given Composer Packages as "dev" dependencies.
     *
     * @param  mixed  $packages
     * @return void
     */
    protected function requireComposerDevPackages($packages)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = [$this->phpBinary(), $composer, 'require', '--dev'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require', '--dev'],
            is_array($packages) ? $packages : func_get_args()
        );

        (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            });
    }

    /**
     * @return void
     */
    protected function installNodeDependenciesAndBuild()
    {
        $commands = ['npm install', 'npm run build'];

        $this->runCommands($commands);
    }

    /**
     * @param  array  $commands
     * @param  array  $env
     * @return Process
     */
    protected function runCommands(array $commands, array $env = [])
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, $env, null, null);

        $process->run(function ($type, $line) {
            $this->output->write('    '.$line);
        });

        return $process;
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
