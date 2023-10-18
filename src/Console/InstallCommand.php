<?php

namespace JoelButcher\Socialstream\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Concerns\InteractsWithNode;
use JoelButcher\Socialstream\Installer\Enums\BreezeInstallStack;
use JoelButcher\Socialstream\Installer\Enums\InstallOptions;
use JoelButcher\Socialstream\Installer\Enums\InstallStarterKit;
use JoelButcher\Socialstream\Installer\Enums\JetstreamInstallStack;
use JoelButcher\Socialstream\Installer\InstallManager;
use Laravel\Fortify\Features as FortifyFeatures;
use Laravel\Jetstream\Jetstream;
use Pest\TestSuite;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

use function Laravel\Prompts\alert;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;

class InstallCommand extends Command implements PromptsForMissingInput
{
    use InteractsWithComposer;
    use InteractsWithNode;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socialstream:install {starter-kit : The development starter kit that should be installed (jetstream,breeze,filament)}
                            {stack : The development stack that should be used for the chosen starter kit (e.g. blade,livewire,inertia,javascript)}
                            {--dark : Indicate that dark mode support should be installed}
                            {--teams : Indicates if team support should be installed}
                            {--api : Indicates if API support should be installed}
                            {--verification : Indicates if email verification support should be installed}
                            {--ssr : Indicates if Inertia SSR support should be installed}
                            {--typescript : Indicates if TypeScript is preferred for the Inertia stack (Experimental) when using Laravel Breeze}
                            {--pest : Indicates if Pest should be installed}
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
    public function handle(InstallManager $installManager): ?int
    {
        $installManager->driver(match (true) {
            $this->getStarterKit() === InstallStarterKit::Filament => 'filament',
            ($this->getStarterKit() === InstallStarterKit::Breeze &&
            $this->getStack() === BreezeInstallStack::Livewire) => 'livewire-breeze',
            default => $this->getStarterKit()->value.'-'.$this->getStack()->value,
        })->install(
            $this->option('composer'),
            ...collect($this->options())
                ->only(['teams', 'api', 'verification', 'ssr', 'typescript', 'dark', 'pest'])
                ->filter()
                ->keys()
                ->map(
                    fn (string $option) => InstallOptions::from($option),
                ),
        );

        outro(match ($this->getStarterKit()) {
            InstallStarterKit::Filament => 'Installed Socialstream for Filament.',
            InstallStarterKit::Jetstream => "Installed Socialstream for Laravel Jetstream ({$this->getStack()->label()})",
            InstallStarterKit::Breeze => "Installed Socialstream for Laravel Breeze ({$this->getStack()->label()})",
        });

        return self::SUCCESS;
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'starter-kit' => function () {
                $callback = match (true) {
                    $this->isLaravelBreezeInstalled() => function () {
                        alert('We\'ve detected that Laravel Breeze is installed.');

                        return InstallStarterKit::Breeze;
                    },
                    $this->isLaravelJetstreamInstalled() => function () {
                        alert('We\'ve detected that Laravel Jetstream is installed.');

                        return InstallStarterKit::Jetstream;
                    },
                    default => function () {
                        if ($this->isFilamentInstalled()) {
                            alert('We\'ve detected that Filament is installed.');

                            if (confirm(
                                label: 'Would you like to install Socialstream for Filament?',
                                default: 'no',
                                hint: 'If you are also using Laravel Jetstream or Breeze, this will not affect those installations.'
                            )) {
                                return InstallStarterKit::Filament;
                            }
                        }

                        \Laravel\Prompts\info('Socialstream supports Laravel Breeze, Laravel Jetstream, and Filament.');

                        return InstallStarterKit::from(select(
                            label: 'Which development starter kit would you like to use?',
                            options: array_merge(
                                [
                                    'breeze' => 'Laravel Breeze',
                                    'jetstream' => 'Laravel Jetstream',
                                ],
                                // If filament is installed, the user has already told us they don't want to install for that starter kit.
                                $this->isFilamentInstalled() ? [] : ['filament' => 'Filament Admin Panel'],
                            ),
                            scroll: 10,
                        ));
                    }
                };

                return $callback();
            },
            'stack' => fn () => match ($this->getStarterKit()) {
                InstallStarterKit::Filament => null,
                InstallStarterKit::Breeze => $this->getBreezeStack(),
                InstallStarterKit::Jetstream => $this->getJetstreamStack(),
            },
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output): void
    {
        if ($this->isUsingFilament()) {
            return;
        }

        if ($this->isLaravelJetstreamInstalled()) {
            \Laravel\Prompts\info('Laravel Jetstream is already installed, configuring Socialstream based on enabled features...');

            $input->setOption('teams', Jetstream::hasTeamFeatures());
            $input->setOption('api', Jetstream::hasApiFeatures());
            $input->setOption('verification', FortifyFeatures::enabled(FortifyFeatures::emailVerification()));
            $input->setOption('ssr', file_exists(resource_path('js/ssr.js')));
            $input->setOption('pest', $this->isUsingPest());
            $input->setOption('dark', $this->hasFilesWithDarkMode());
        } elseif ($this->isLaravelBreezeInstalled()) {
            \Laravel\Prompts\info('Laravel Breeze is already installed, configuring Socialstream based on enabled features...');

            $input->setOption('ssr', file_exists(resource_path('js/ssr.js')));
            $input->setOption('typescript', $this->hasNodePackage('typescript'));
            $input->setOption('pest', $this->isUsingPest());
            $input->setOption('dark', $this->hasFilesWithDarkMode());
        } else {
            if ($this->isUsingJetstream()) {
                collect(multiselect(
                    label: 'Would you like any optional features?',
                    options: collect([
                        'teams' => 'Team support',
                        'api' => 'API support',
                        'verification' => 'Email verification',
                        'dark' => 'Dark mode',
                    ])
                        ->when(
                            $this->getStack() === JetstreamInstallStack::Inertia,
                            fn ($options) => $options->put('ssr', 'Inertia SSR')
                        )->sort(),
                ))->each(fn ($option) => $input->setOption($option, true));
            } elseif ($this->isUsingBreeze() && in_array($this->getStack(), [BreezeInstallStack::React, BreezeInstallStack::Vue])) {
                collect(multiselect(
                    label: 'Would you like any optional features?',
                    options: [
                        'dark' => 'Dark mode',
                        'ssr' => 'Inertia SSR',
                        'typescript' => 'TypeScript (experimental)',
                    ]
                ))->each(fn ($option) => $input->setOption($option, true));
            } else {
                $input->setOption('dark', confirm(
                    label: 'Would you like dark mode support?',
                    default: false
                ));
            }

            $input->setOption('pest', select(
                label: 'Which testing framework do you prefer?',
                options: ['PHPUnit', 'Pest'],
            ) === 'Pest');
        }
    }

    /**
     * Determine the starter pack that should be used.
     */
    private function getStarterKit(): InstallStarterKit
    {
        /** @var InstallStarterKit|string $kit */
        $kit = $this->argument('starter-kit');

        if (is_string($kit)) {
            $kit = InstallStarterKit::from($kit);
        }

        return $kit;
    }

    /**
     * Determine the stack that should be used for the selected starter pack.
     */
    private function getStack(): JetstreamInstallStack|BreezeInstallStack|null
    {
        if ($this->isUsingFilament()) {
            return null;
        }

        /** @var JetstreamInstallStack|BreezeInstallStack|string $stack */
        $stack = $this->argument('stack');

        if (! is_string($stack)) {
            return $stack;
        }

        /** @var JetstreamInstallStack|BreezeInstallStack|null  $stack */
        $stack = (JetstreamInstallStack::tryFrom($stack) ?? BreezeInstallStack::tryFrom($stack));

        if (! $stack) {
            throw new \InvalidArgumentException(
                "Invalid stack [{$this->argument('stack')}] for starter-kit '{$this->getStarterKit()->value}'"
            );
        }

        return $stack;
    }

    /**
     * Determine what Laravel Breeze stack should be used.
     */
    private function getBreezeStack(): BreezeInstallStack
    {
        if (
            class_exists('\App\Http\Middleware\HandleInertiaRequests') ||
            class_exists('\App\Http\Middleware\HandleInertiaRequests') ||
            class_exists('\App\Providers\VoltServiceProvider') ||
            class_exists('\App\Http\Controllers\ProfileController')
        ) {
            warning('Installing Socialstream will overwrite some key files already published by Laravel Breeze.');

            $decision = select(
                label: 'Do you wish to proceed?',
                options: [
                    'yes' => 'Yes',
                    'no' => 'No (exit)',
                ],
                default: 'yes',
                hint: 'This will overwrite any changes you have made to any previously published files.'
            );

            if ($decision === 'no') {
                throw new RuntimeException(message: 'An error occurred installing Socialstream', code: self::INVALID);
            }
        }

        return match (true) {
            class_exists('\App\Http\Middleware\HandleInertiaRequests') && $this->hasNodePackage('react') => BreezeInstallStack::React,
            class_exists('\App\Http\Middleware\HandleInertiaRequests') && $this->hasNodePackage('vue') => BreezeInstallStack::Vue,
            class_exists('\App\Providers\VoltServiceProvider') => match (true) {
                str_contains(file_get_contents(resource_path('views/livewire/pages/auth/login.blade.php')), '$login = function () {') => BreezeInstallStack::FunctionalLivewire,
                default => BreezeInstallStack::Livewire,
            },
            class_exists('\App\Http\Controllers\ProfileController') => BreezeInstallStack::Blade,
            default => BreezeInstallStack::from(select(
                label: 'Which stack would you like to use?',
                options: collect(BreezeInstallStack::cases())->mapWithKeys(
                    fn (BreezeInstallStack $stack) => [$stack->value => $stack->label()]
                )->all(),
            ))
        };
    }

    /**
     * Determine what Laravel Jetstream stack should be used.
     */
    private function getJetstreamStack(): JetstreamInstallStack
    {
        if (file_exists(config_path('jetstream.php'))) {
            warning('Installing Socialstream will overwrite some key files already published by Laravel Jetstream.');

            $decision = select(
                label: 'Do you wish to proceed?',
                options: [
                    'yes' => 'Yes',
                    'no' => 'No (exit)',
                ],
                default: 'yes',
                hint: 'This will overwrite any changes you have made to any published files.'
            );

            if ($decision === 'no') {
                throw new RuntimeException(message: 'An error occurred installing Socialstream', code: self::INVALID);
            }

            return JetstreamInstallStack::from(config('jetstream.stack'));
        }

        return JetstreamInstallStack::from(select(
            label: 'Which stack would you like to use?',
            options: collect(JetstreamInstallStack::cases())->mapWithKeys(
                fn (JetstreamInstallStack $stack) => [$stack->value => $stack->label()],
            ),
            default: 'inertia',
        ));
    }

    /**
     * Determine if Laravel Breeze was selected as the starter pack.
     */
    private function isUsingBreeze(): bool
    {
        return $this->getStarterKit() === InstallStarterKit::Breeze;
    }

    /**
     * Determine if Laravel Jetstream was selected as the starter pack.
     */
    private function isUsingJetstream(): bool
    {
        return $this->getStarterKit() === InstallStarterKit::Jetstream;
    }

    /**
     * Determine if Filament was selected as the starter pack.
     */
    private function isUsingFilament(): bool
    {
        return $this->getStarterKit() === InstallStarterKit::Filament;
    }

    /**
     * Determine if Laravel Breeze is installed.
     */
    private function isLaravelBreezeInstalled(): bool
    {
        return $this->hasComposerPackage('laravel/breeze') && (
            class_exists('\App\Http\Middleware\HandleInertiaRequests') || // Vue / React with Inertia
            class_exists('\App\Http\Controllers\ProfileController') || // Blade with Alpine
            class_exists('\App\Providers\VoltServiceProvider') // Livewire / Volt with Alpine
        );
    }

    /**
     * Determine if Laravel Jetstream is installed.
     */
    private function isLaravelJetstreamInstalled(): bool
    {
        return $this->hasComposerPackage('laravel/jetstream') && class_exists(Jetstream::class) && file_exists(config_path('jetstream.php'));
    }

    /**
     * Determine if Filament is installed.
     */
    private function isFilamentInstalled(): bool
    {
        return $this->hasComposerPackage('filament/filament') && class_exists('\App\Providers\Filament\AdminPanelProvider');
    }

    /**
     * Determine whether the project is already using Pest.
     */
    protected function isUsingPest(): bool
    {
        return class_exists(TestSuite::class);
    }

    private function hasFilesWithDarkMode(): bool
    {
        // Find all the files published by the starter kit that have dark mode class utilities,
        // ignoring any and all files that will have been overwritten by Socialstream
        $files = (new Finder)
            ->in([resource_path('views'), resource_path('js')])
            ->name(['*.blade.php', '*.vue', '*.jsx', '*.tsx'])
            ->notPath(['Pages/Welcome.vue', 'Pages/Welcome.vue', 'Pages/Welcome.jsx', 'Pages/Welcome.tsx'])
            ->notPath(['Pages/Auth/Login.vue', 'Pages/Auth/Login.jsx', 'Pages/Auth/Login.tsx'])
            ->notPath(['Pages/Auth/Register.vue', 'Pages/Auth/Register.jsx', 'Pages/Auth/Register.tsx'])
            ->notPath(['Pages/Profile/Partials/ConnectedAccountsForm.vue', 'Pages/Profile/Partials/ConnectedAccountsForm.jsx', 'Pages/Profile/Partials/ConnectedAccountsForm.tsx'])
            ->notPath(['Pages/Profile/Partials/SetPasswordForm.vue', 'Pages/Profile/Partials/SetPasswordForm.jsx', 'Pages/Profile/Partials/SetPasswordForm.tsx'])
            ->notPath(['Pages/Profile/Edit.vue', 'Pages/Profile/Edit.jsx', 'Pages/Profile/Edit.tsx'])
            ->notPath(['Components/ActionLink.vue', 'Components/ActionLink.jsx', 'Components/ActionLink.tsx'])
            ->notPath(['Components/ConnectedAccount.vue', 'Components/ConnectedAccount.jsx', 'Components/ConnectedAccount.tsx'])
            ->notPath(['Components/SocialstreamIcons/ProviderIcon.vue', 'Components/SocialstreamIcons/ProviderIcon.jsx', 'Components/SocialstreamIcons/ProviderIcon.tsx'])
            ->notPath(['Components/Socialstream.vue', 'Components/Socialstream.jsx', 'Components/Socialstream.tsx'])
            ->notPath(['components/socialstream.blade.php', 'components/socialstream-icons/provider-icon.blade.php'])
            ->notPath(['livewire/pages/auth/login.blade.php', 'livewire/pages/auth/register.blade.php'])
            ->notPath(['livewire/profile/connected-accounts-form.blade.php', 'livewire/profile/delete-user-form.blade.php', 'livewire/profile/set-password-form.blade.php'])
            ->notPath(['auth/login.blade.php', 'auth/register.blade.php', 'profile/edit.blade.php'])
            ->notName(['welcome.blade.php', 'profile.blade.php'])
            ->contains('/\sdark:[^\s"\']+/');

        return $files->count() > 0;
    }
}
