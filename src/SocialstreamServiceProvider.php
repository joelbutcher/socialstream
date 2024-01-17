<?php

namespace JoelButcher\Socialstream;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
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
use JoelButcher\Socialstream\Http\Responses\OAuthLoginFailedResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthLoginResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthProviderLinkedResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthProviderLinkFailedResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthRegisterFailedResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthRegisterResponse;
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

        $this->registerResponseBindings();
    }

    /**
     * Register the response bindings.
     */
    protected function registerResponseBindings(): void
    {
        $this->app->singleton(Contracts\OAuthLoginResponse::class, OAuthLoginResponse::class);
        $this->app->singleton(Contracts\OAuthLoginFailedResponse::class, OAuthLoginFailedResponse::class);
        $this->app->singleton(Contracts\OAuthProviderLinkedResponse::class, OAuthProviderLinkedResponse::class);
        $this->app->singleton(Contracts\OAuthProviderLinkFailedResponse::class, OAuthProviderLinkFailedResponse::class);
        $this->app->singleton(Contracts\OAuthLoginFailedResponse::class, OAuthLoginFailedResponse::class);
        $this->app->singleton(Contracts\OAuthRegisterResponse::class, OAuthRegisterResponse::class);
        $this->app->singleton(Contracts\OAuthRegisterFailedResponse::class, OAuthRegisterFailedResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureCommands();
        $this->configureRefreshTokenResolvers();
        $this->bootLaravelBreeze();
        $this->bootLaravelJetstream();
        $this->bootFilament();
        $this->bootInertia();

        if(config('jetstream.stack') === 'livewire' && class_exists(Livewire::class)) {
            Livewire::component('profile.set-password-form', SetPasswordForm::class);
            Livewire::component('profile.connected-accounts-form', ConnectedAccountsForm::class);
        }
    }

    /**
     * Sets sensible package defaults if not installed alongside Jetstream, Breeze, or Filament.
     */
    private function configureDefaults(): void
    {
        // Blade views / components
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'socialstream');

        // Views
        if (Socialstream::$registersRoutes) {
            Route::group([
                'namespace' => 'JoelButcher\Socialstream\Http\Controllers',
                'domain' => config('socialstream.domain', null),
                'prefix' => config('socialstream.prefix', config('socialstream.path')),
            ], function () {
                $this->loadRoutesFrom(path: __DIR__.'/../routes/socialstream.php');
            });
        }

        // Models & Policies
        Socialstream::useConnectedAccountModel(ConnectedAccount::class);
        Gate::policy(Socialstream::connectedAccountModel(), Policies\ConnectedAccountPolicy::class);

        // Actions
        Socialstream::authenticatesOAuthCallbackUsing(AuthenticateOAuthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(HandleOAuthCallbackErrors::class);
        Socialstream::resolvesSocialiteUsersUsing(ResolveSocialiteUser::class);
        Socialstream::createUsersFromProviderUsing(CreateUserFromProvider::class);
        Socialstream::createConnectedAccountsUsing(CreateConnectedAccount::class);
        Socialstream::updateConnectedAccountsUsing(UpdateConnectedAccount::class);
        Socialstream::handlesInvalidStateUsing(HandleInvalidState::class);
        Socialstream::generatesProvidersRedirectsUsing(GenerateRedirectForProvider::class);

        if (! $this->app->runningInConsole()) {
            return;
        }

        // Config
        $this->publishes([
            __DIR__.'/../config/socialstream.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations/2022_12_21_000000_make_password_nullable_on_users_table.php' => database_path('migrations/2022_12_21_000000_make_password_nullable_on_users_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');

        // Routes
        $this->publishes([
            __DIR__.'/../routes/socialstream.php' => base_path('routes/socialstream.php'),
        ], 'socialstream-routes');

        // Actions
        $this->publishes([
            __DIR__.'/../stubs/app/Actions/Socialstream/' => app_path('Actions/Socialstream/'),
        ], 'socialstream-actions');
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
     * Boot the services required for Laravel Breeze.
     */
    protected function bootLaravelBreeze(): void
    {
        if (! $this->hasComposerPackage('laravel/breeze') || ! $this->app->runningInConsole()) {
            return;
        }

        // Routes
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

        // Migrations
        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_breeze_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');
    }

    /**
     * Boot the services required for Laravel Jetstream.
     */
    protected function bootLaravelJetstream(): void
    {
        if (! $this->hasComposerPackage('laravel/jetstream') || ! class_exists(Jetstream::class)) {
            return;
        }

        Socialstream::setUserPasswordsUsing(SetUserPassword::class);
        Socialstream::createUsersFromProviderUsing(match (Jetstream::hasTeamFeatures()) {
            true => CreateUserWithTeamsFromProvider::class,
            false => CreateUserFromProvider::class,
        });

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

        if (! $this->app->runningInConsole()) {
            return;
        }

        // Config
        if (config('jetstream.stack') === 'inertia') {
            $this->publishes([
                __DIR__.'/../routes/socialstream-inertia.php' => base_path('routes/socialstream.php'),
            ], 'socialstream-routes');
        }

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
     * Boot the services required for Filament.
     */
    protected function bootFilament(): void
    {
        if (! $this->hasComposerPackage('filament/filament') || ! $this->app->runningInConsole()) {
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
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/socialstream'),
        ], 'socialstream-views');
    }

    /**
     * Boot any Inertia related services.
     */
    protected function bootInertia(): void
    {
        if (! $this->hasComposerPackage('inertiajs/inertia-laravel')) {
            return;
        }

        $kernel = $this->app->make(Kernel::class);

        $kernel->appendMiddlewareToGroup('web', ShareInertiaData::class);
        $kernel->appendToMiddlewarePriority(ShareInertiaData::class);
    }
}
