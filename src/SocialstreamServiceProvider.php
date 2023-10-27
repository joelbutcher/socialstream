<?php

namespace JoelButcher\Socialstream;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use JoelButcher\Socialstream\Actions\AuthenticateOAuthCallback;
use JoelButcher\Socialstream\Actions\CreateConnectedAccount;
use JoelButcher\Socialstream\Actions\CreateUserFromProvider;
use JoelButcher\Socialstream\Actions\CreateUserWithTeamsFromProvider;
use JoelButcher\Socialstream\Actions\GenerateRedirectForProvider;
use JoelButcher\Socialstream\Actions\HandleInvalidState;
use JoelButcher\Socialstream\Actions\HandleOAuthCallbackErrors;
use JoelButcher\Socialstream\Actions\ResolveSocialiteUser;
use JoelButcher\Socialstream\Actions\SetUserPassword;
use JoelButcher\Socialstream\Actions\UpdateConnectedAccount;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Http\Livewire\ConnectedAccountsForm;
use JoelButcher\Socialstream\Http\Livewire\SetPasswordForm;
use JoelButcher\Socialstream\Http\Middleware\ShareInertiaData;
use JoelButcher\Socialstream\Resolvers\OAuth\BitbucketOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\FacebookOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GithubOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GitlabOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GoogleOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\LinkedInOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\SlackOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\TwitterOAuth2RefreshResolver;
use Laravel\Jetstream\Jetstream;
use Livewire\Livewire;

class SocialstreamServiceProvider extends ServiceProvider
{
    use InteractsWithComposer;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/socialstream.php', 'socialstream');

        $this->app->afterResolving(BladeCompiler::class, function () {
            if (config('jetstream.stack') === 'livewire' && class_exists(Livewire::class)) {
                Livewire::component('profile.set-password-form', SetPasswordForm::class);
                Livewire::component('profile.connected-accounts-form', ConnectedAccountsForm::class);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Socialstream::authenticatesOAuthCallbackUsing(AuthenticateOAuthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(HandleOAuthCallbackErrors::class);

        $this->configureRoutes();
        $this->configureCommands();
        $this->configureActions();
        $this->configureRefreshTokenResolvers();

        match (true) {
            $this->hasComposerPackage('laravel/breeze') => $this->bootLaravelBreeze(),
            $this->hasComposerPackage('laravel/jetstream') => $this->bootLaravelJetstream(),
            default => null,
        };

        if ($this->hasComposerPackage('filament/filament')) {
            $this->bootFilament();
        }

        if ($this->hasComposerPackage('inertiajs/inertia-laravel')) {
            $this->bootInertia();
        }
    }

    protected function configureRoutes(): void
    {
        if (Socialstream::$registersRoutes) {
            Route::group([
                'namespace' => 'JoelButcher\Socialstream\Http\Controllers',
                'domain' => config('socialstream.domain', null),
                'prefix' => config('socialstream.prefix', config('socialstream.path')),
            ], function () {
                $this->loadRoutesFrom(path: match (config('jetstream.stack')) {
                    'inertia' => __DIR__.'/../routes/socialstream-inertia.php',
                    default => __DIR__.'/../routes/socialstream.php'
                });
            });
        }

        match (config('jetstream.stack')) {
            'inertia' => $this->publishes([
                __DIR__.'/../routes/socialstream-inertia.php' => base_path('routes/socialstream.php'),
            ], 'socialstream-routes'),
            default => $this->publishes([
                __DIR__.'/../routes/socialstream.php' => base_path('routes/socialstream.php'),
            ], 'socialstream-routes')
        };
    }

    /**
     * Configure the commands offered by the application.
     */
    protected function configureCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
            Console\UpgradeCommand::class,
            Console\CreateProviderCommand::class,
        ]);
    }

    /**
     * Configure the default actions used by Socialstream.
     */
    public function configureActions(): void
    {
        Socialstream::resolvesSocialiteUsersUsing(ResolveSocialiteUser::class);
        Socialstream::createUsersFromProviderUsing(match (true) {
            $this->hasComposerPackage('laravel/jetstream') => Jetstream::hasTeamFeatures()
                ? CreateUserWithTeamsFromProvider::class
                : CreateUserFromProvider::class,
            default => CreateUserFromProvider::class,
        });
        Socialstream::createConnectedAccountsUsing(CreateConnectedAccount::class);
        Socialstream::updateConnectedAccountsUsing(UpdateConnectedAccount::class);
        Socialstream::handlesInvalidStateUsing(HandleInvalidState::class);
        Socialstream::generatesProvidersRedirectsUsing(GenerateRedirectForProvider::class);
    }

    /**
     * Configure the refresh token resolvers as defaults.
     */
    protected function configureRefreshTokenResolvers(): void
    {
        Socialstream::refreshesTokensForProviderUsing(Providers::bitbucket(), BitbucketOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::facebook(), FacebookOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::github(), GithubOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::gitlab(), GitlabOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::google(), GoogleOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::linkedin(), LinkedInOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::linkedinOpenId(), LinkedInOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::slack(), SlackOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::twitterOAuth1(), TwitterOAuth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::twitterOAuth2(), TwitterOAuth2RefreshResolver::class);
    }

    /**
     * Boot the services required for Laravel Jetstream.
     */
    protected function bootLaravelJetstream(): void
    {
        Socialstream::setUserPasswordsUsing(SetUserPassword::class);
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Config
        $this->publishes([
            __DIR__.'/../config/socialstream.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        // Actions
        $this->publishes(array_merge([
            __DIR__.'/../stubs/app/Actions/Socialstream/' => app_path('Actions/Socialstream/'),
            __DIR__.'/../stubs/app/Actions/Jetstream/DeleteUser.php' => app_path('Actions/Jetstream/DeleteUser.php'),
        ], Jetstream::hasTeamFeatures() ? [
            __DIR__.'/../stubs/app/Actions/Socialstream/CreateUserWithTeamsFromProvider.php' => app_path('Actions/Socialstream/CreateUserFromProvider.php'),
        ] : []), 'socialstream-actions');

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');
    }

    /**
     * Boot the services required for Laravel Breeze.
     */
    protected function bootLaravelBreeze(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        // Config
        $this->publishes([
            __DIR__.'/../config/socialstream.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        // Actions
        $this->publishes([
            __DIR__.'/../stubs/app/Actions/Socialstream/' => app_path('Actions/Socialstream/'),
        ], 'socialstream-actions');

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_breeze_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');

        // Breeze's Livewire stack uses Laravel Volt with PHP classes in the blade files...
        if (class_exists('\App\Providers\VoltServiceProvider')) {
            return;
        } elseif (class_exists('\App\Http\Middleware\HandleInertiaRequests')) {
            $this->publishes(paths: [
                __DIR__.'/../stubs/breeze/inertia-common/routes/auth.php' => base_path('routes/auth.php'),
                __DIR__.'/../stubs/breeze/inertia-common/routes/web.php' => base_path('routes/web.php'),
            ], groups: 'socialstream-routes');
        } elseif (class_exists('\App\Http\Controllers\ProfileController')) {
            $this->publishes(paths: [
                __DIR__.'/../stubs/breeze/default/routes/auth.php' => base_path('routes/auth.php'),
                __DIR__.'/../stubs/breeze/default/routes/web.php' => base_path('routes/web.php'),
            ], groups: 'socialstream-routes');
        }
    }

    /**
     * Boot the services required for Filament.
     */
    protected function bootFilament(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/filament/views', 'socialstream');

        if (! $this->app->runningInConsole()) {
            return;
        }

        // Config
        $this->publishes([
            __DIR__.'/../config/filament.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        // Actions
        $this->publishes([
            __DIR__.'/../stubs/app/Actions/Socialstream' => app_path('Actions/Socialstream'),
        ], 'socialstream-actions');

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');

        // Views
        $this->publishes([
            __DIR__.'/../resources/filament/views' => base_path('resources/views/vendor/socialstream'),
        ], 'socialstream-views');
    }

    /**
     * Boot any Inertia related services.
     */
    protected function bootInertia(): void
    {
        $kernel = $this->app->make(Kernel::class);

        $kernel->appendMiddlewareToGroup('web', ShareInertiaData::class);
        $kernel->appendToMiddlewarePriority(ShareInertiaData::class);
    }
}
