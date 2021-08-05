<?php

namespace JoelButcher\Socialstream\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Laravel\Jetstream\Jetstream;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialstream:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Socialstream components and resources';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Check if Jetstream has been installed.
        if (! file_exists(config_path('jetstream.php'))) {
            $this->warn('Jetstream hasn\'t been installed. This package requires Jetstream to be installed.');

            if ($this->ask('Do you want to install Jetstream? (yes/no)', 'no') !== 'yes') {
                return 0;
            }

            $stack = $this->choice('Which Jetstream stack do you prefer', ['livewire', 'inertia']);

            $useTeams = $this->ask('Will your application use teams? (yes/no)', 'no') === 'yes';

            $this->callSilent('jetstream:install', ['stack' => $stack, '--teams' => $useTeams]);
        } else {
            $stack = config('jetstream.stack');

            $useTeams = Jetstream::hasTeamFeatures();
        }

        // Publish...
        $this->callSilent('vendor:publish', ['--tag' => 'socialstream-config', '--force' => true]);
        $this->callSilent('vendor:publish', ['--tag' => 'socialstream-migrations', '--force' => true]);

        if ($stack === 'livewire') {
            $this->installLivewireStack();
        } elseif ($stack === 'inertia') {
            $this->installInertiaStack();
        }

        if ($useTeams) {
            $this->ensureTeamsCompatibility();
        }

        $this->line('');
        $this->info('Socialstream installed successfully.');
        $this->comment('Please execute "npm install && npm run dev" to build your assets.');

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

        // Requuired components...
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

        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js/Socialstream', resource_path('js/Socialstream'));

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
