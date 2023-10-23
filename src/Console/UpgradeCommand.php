<?php

namespace JoelButcher\Socialstream\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\InertiaDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\JetstreamDriver;
use JoelButcher\Socialstream\Installer\Drivers\Jetstream\LivewireDriver;
use JoelButcher\Socialstream\Socialstream;

use function Laravel\Prompts\alert;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\spin;

class UpgradeCommand extends Command implements PromptsForMissingInput
{
    use InteractsWithComposer;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialstream:upgrade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically upgrade your Socialstream install.';

    public function handle(): int
    {
        if (! $this->isSocialstreamInstalled()) {
            alert('Socialstream is not installed, please run the `socialstream:install` command to setup Socialstream');

            return self::INVALID;
        }

        intro('Let\'s upgrade your Socialstream installation!');

        if (confirm(
            label: 'Have you made any changes to your 4.x Socialstream installation?',
            default: false,
        )) {
            alert('Upgrading Socialstream via this command is only possible for unaltered 4.x installs.');
            \Laravel\Prompts\info('Please visit https://docs.socialstream.dev/prologue/upgrade-guide/upgrading-to-v5-from-4.x to upgrade manually');

            return self::FAILURE;
        }

        if (! $this->hasComposerPackage('laravel/jetstream')) {
            \Laravel\Prompts\info('First, we need to add Laravel Jetstream to your projects composer dependencies.');

            if (! confirm('Install laravel/jetstream?')) {
                return self::INVALID;
            }

            spin(function () {
                $this->requireComposerPackages(packages: ['laravel/jetstream']);
            }, 'Adding Jetstream to composer dependencies...');
        }

        $this->copyActions()
            ->copyAuthViews()
            ->copyProfileViews()
            ->copySocialstreamComponents();

        /** @var JetstreamDriver $driver */
        $driver = match (config('jetstream.stack')) {
            'livewire' => $this->laravel->make(LivewireDriver::class),
            'inertia' => $this->laravel->make(InertiaDriver::class),
        };

        spin(fn () => $driver->build(), message: 'Scaffolding frontend...');

        outro('You\'ve successfully upgraded to Socialstream '.Socialstream::VERSION.'!');

        return self::SUCCESS;
    }

    private function isSocialstreamInstalled(): bool
    {
        return file_exists(config_path('socialstream.php'));
    }

    private function copyActions(): self
    {
        $options = [
            __DIR__.'/../../stubs/app/Actions/Jetstream/DeleteUser.php' => 'app/Actions/Jetstream/DeleteUser.php',
            __DIR__.'/../../stubs/app/Actions/Socialstream/ResolveSocialiteUser.php' => 'app/Actions/Socialstream/ResolveSocialiteUser.php',
            __DIR__.'/../../stubs/app/Actions/Socialstream/CreateConnectedAccount.php' => 'app/Actions/Socialstream/CreateConnectedAccount.php',
            __DIR__.'/../../stubs/app/Actions/Socialstream/GenerateRedirectForProvider.php' => 'app/Actions/Socialstream/GenerateRedirectForProvider.php',
            __DIR__.'/../../stubs/app/Actions/Socialstream/UpdateConnectedAccount.php' => 'app/Actions/Socialstream/UpdateConnectedAccount.php',
            __DIR__.'/../../stubs/app/Actions/Socialstream/CreateUserFromProvider.php' => 'app/Actions/Socialstream/CreateUserFromProvider.php',
            __DIR__.'/../../stubs/app/Actions/Socialstream/HandleInvalidState.php' => 'app/Actions/Socialstream/HandleInvalidState.php',
            __DIR__.'/../../stubs/app/Actions/Socialstream/SetUserPassword.php' => 'app/Actions/Socialstream/SetUserPassword.php',
        ];

        $selected = multiselect(
            label: 'Next, we\'ll replace any existing actions and make sure they\'re up-to-date',
            options: $options,
            scroll: 10,
            hint: 'This will overwrite any changes you may have made, please make sure to back up an changes before they\'re lost.'
        );

        $this->copyFiles(
            files: array_filter(
                array: $options,
                callback: fn (string $option) => in_array($option, $selected),
                mode: ARRAY_FILTER_USE_KEY,
            ),
        );

        return $this;
    }

    public function copyAuthViews(): self
    {
        $views = match (config('jetstream.stack')) {
            'livewire' => [
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/auth/login.blade.php' => 'resources/views/auth/login.blade.php',
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/auth/register.blade.php' => 'resources/views/auth/register.blade.php',
            ],
            'inertia' => [
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Pages/Auth/Login.vue' => 'resources/js/Pages/Auth/Login.vue',
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Pages/Auth/Register.vue' => 'resources/js/Pages/Auth/Register.vue',
            ],
        };

        $selected = multiselect(
            label: 'Next, we\'ll update the auth views and make sure they\'re up-to-date',
            options: $views,
            default: $views,
            scroll: 10,
            hint: 'This will overwrite any changes you may have made, please make sure to back up an changes before they\'re lost.'
        );

        $this->copyFiles(
            files: array_filter(
                array: $views,
                callback: fn (string $option) => in_array($option, $selected),
                mode: ARRAY_FILTER_USE_KEY,
            ),
        );

        return $this;
    }

    public function copyProfileViews(): self
    {
        $views = match (config('jetstream.stack')) {
            'livewire' => [
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/profile/connected-accounts-form.blade.php' => 'resources/views/profile/connected-accounts-form.blade.php',
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/profile/set-password-form.blade.php' => 'resources/views/profile/set-password-form.blade.php',
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/profile/show.blade.php' => 'resources/views/profile/show.blade.php',
            ],
            'inertia' => [
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Pages/Profile/Partials/ConnectedAccountsForm.vue' => 'resources/js/Pages/Profile/Partials/ConnectedAccountsForm.vue',
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Pages/Profile/Partials/SetPasswordForm.vue' => 'resources/js/Pages/Profile/Partials/SetPasswordForm.vue',
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Pages/Profile/Show.vue' => 'resources/js/Pages/Profile/Show.vue',
            ],
        };

        $selected = multiselect(
            label: 'Next, we\'ll update the profile views and make sure they\'re up-to-date',
            options: $views,
            default: $views,
            scroll: 10,
            hint: 'This will overwrite any changes you may have made, please make sure to back up an changes before they\'re lost.'
        );

        $this->copyFiles(
            files: array_filter(
                array: $views,
                callback: fn (string $option) => in_array($option, $selected),
                mode: ARRAY_FILTER_USE_KEY,
            ),
        );

        return $this;
    }

    private function copySocialstreamComponents(): self
    {
        $components = match (config('jetstream.stack')) {
            'livewire' => [
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/components/action-link.blade.php' => 'resources/views/components/action-link.blade.php',
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/components/connected-account.blade.php' => 'resources/views/components/connected-account.blade.php',
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/components/socialstream.blade.php' => 'resources/views/components/socialstream.blade.php',
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/components/validation-errors.blade.php' => 'resources/views/components/validation-errors.blade.php',
                __DIR__.'/../../stubs/jetstream/livewire/resources/views/components/socialstream-icons/provider-icon.blade.php' => 'resources/views/components/socialstream-icons/provider-icon.blade.php',
            ],
            'inertia' => [
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Components/ActionLink.vue' => 'resources/js/Components/ActionLink.vue',
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Components/ConnectedAccount.vue' => 'resources/js/Components/ConnectedAccount.vue',
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Components/Socialstream.vue' => 'resources/js/Components/Socialstream.vue',
                __DIR__.'/../../stubs/jetstream/inertia/resources/js/Components/SocialstreamIcons/ProviderIcon.vue' => 'resources/js/Components/SocialstreamIcons/ProviderIcon.vue',
            ],
        };

        $selected = multiselect(
            label: 'Next, we\'ll update the socialstream components and make sure they\'re up-to-date',
            options: $components,
            default: $components,
            scroll: 10,
            hint: 'This will overwrite any changes you may have made, please make sure to back up an changes before they\'re lost.'
        );

        $this->copyFiles(
            files: array_filter(
                array: $components,
                callback: fn (string $option) => in_array($option, $selected),
                mode: ARRAY_FILTER_USE_KEY,
            ),
        );

        return $this;
    }

    private function copyFiles(array $files): void
    {
        foreach ($files as $source => $destination) {
            \Laravel\Prompts\info("Copying file [$destination]");

            copy($source, base_path($destination));
        }
    }
}
