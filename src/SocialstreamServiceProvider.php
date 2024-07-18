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
use JoelButcher\Socialstream\Http\Responses\OAuthLoginResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthProviderLinkedResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthProviderLinkFailedResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthFailedResponse;
use JoelButcher\Socialstream\Http\Responses\OAuthRegisterResponse;
use JoelButcher\Socialstream\Resolvers\OAuth\BitbucketOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\FacebookOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GithubOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GitlabOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GoogleOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\LinkedInOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\SlackOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\TwitterOAuth2RefreshResolver;
use Laravel\Fortify\Fortify;
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
        $this->app->singleton(Contracts\OAuthProviderLinkedResponse::class, OAuthProviderLinkedResponse::class);
        $this->app->singleton(Contracts\OAuthProviderLinkFailedResponse::class, OAuthProviderLinkFailedResponse::class);
        $this->app->singleton(Contracts\OAuthRegisterResponse::class, OAuthRegisterResponse::class);
        $this->app->singleton(Contracts\OAuthFailedResponse::class, OAuthFailedResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configurePublishing();
        $this->configureRoutes();
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
     * Sets sensible package defaults.
     */
    private function configureDefaults(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'socialstream');

        Socialstream::useConnectedAccountModel(ConnectedAccount::class);
        Gate::policy(Socialstream::connectedAccountModel(), Policies\ConnectedAccountPolicy::class);

        Socialstream::authenticatesOAuthCallbackUsing(AuthenticateOAuthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(HandleOAuthCallbackErrors::class);
        Socialstream::resolvesSocialiteUsersUsing(ResolveSocialiteUser::class);
        Socialstream::createUsersFromProviderUsing(CreateUserFromProvider::class);
        Socialstream::createConnectedAccountsUsing(CreateConnectedAccount::class);
        Socialstream::updateConnectedAccountsUsing(UpdateConnectedAccount::class);
        Socialstream::handlesInvalidStateUsing(HandleInvalidState::class);
        Socialstream::generatesProvidersRedirectsUsing(GenerateRedirectForProvider::class);
    }

    /**
     * Configure publishing for the package.
     */
    private function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/socialstream.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        $this->publishes([
            __DIR__.'/../routes/socialstream.php' => base_path('routes/socialstream.php'),
        ], 'socialstream-routes');

        $this->publishes([
            __DIR__.'/../stubs/app/Actions/Socialstream/' => app_path('Actions/Socialstream/'),
        ], 'socialstream-actions');
    }

    /**
     * Configure the routes offered by the application.
     */
    private function configureRoutes(): void
    {
        if (! Socialstream::$registersRoutes) {
            return;
        }

        Route::group([
            'namespace' => 'JoelButcher\Socialstream\Http\Controllers',
            'domain' => config('socialstream.domain'),
            'prefix' => config('socialstream.prefix', config('socialstream.path')),
        ], function () {
            $this->loadRoutesFrom(path: __DIR__.'/../routes/'.config('jetstream.stack', 'livewire').'.php');
        });
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

        if (class_exists('\App\Providers\VoltServiceProvider')) {
            return;
        }

        if ($this->hasComposerPackage('inertiajs/inertia-laravel')) {
            $this->publishes(paths: [
                __DIR__.'/../stubs/breeze/inertia/routes/socialstream.php' => base_path('routes/socialstream.php'),
            ], groups: 'socialstream-routes');
        } else {
            $this->publishes(paths: [
                __DIR__.'/../stubs/breeze/default/routes/socialstream.php' => base_path('routes/socialstream.php'),
            ], groups: 'socialstream-routes');
        }

        $this->publishesMigrations([
            __DIR__.'/../database/migrations/0001_01_01_000000_create_breeze_users_table.php' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/0001_01_01_000001_make_password_nullable_on_users_table.php' => database_path('migrations/0001_01_01_000001_make_password_nullable_on_users_table.php'),
            __DIR__.'/../database/migrations/0001_01_01_000002_create_connected_accounts_table.php' => database_path('migrations/0001_01_01_000002_create_connected_accounts_table.php'),
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
                'domain' => config('socialstream.domain'),
                'prefix' => config('socialstream.prefix', config('socialstream.path')),
            ], function () {
                $this->loadRoutesFrom(path: __DIR__.'/../routes/'.config('jetstream.stack').'.php');
            });
        }

        if (! $this->app->runningInConsole()) {
            return;
        }

        if (config('jetstream.stack') === 'inertia') {
            $this->publishes([
                __DIR__.'/../routes/inertia.php' => base_path('routes/socialstream.php'),
            ], 'socialstream-routes');
        }

        $this->publishes(array_merge([
            __DIR__.'/../stubs/app/Actions/Socialstream/' => app_path('Actions/Socialstream/'),
            __DIR__.'/../stubs/app/Actions/Jetstream/DeleteUser.php' => app_path('Actions/Jetstream/DeleteUser.php'),
        ], Jetstream::hasTeamFeatures() ? [
            __DIR__.'/../stubs/app/Actions/Socialstream/CreateUserWithTeamsFromProvider.php' => app_path('Actions/Socialstream/CreateUserFromProvider.php'),
        ] : []), 'socialstream-actions');

        $this->publishes([
            __DIR__.'/../database/migrations/0001_01_01_000000_create_users_table.php' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/0001_01_01_000001_make_password_nullable_on_users_table.php' => database_path('migrations/0001_01_01_000001_make_password_nullable_on_users_table.php'),
            __DIR__.'/../database/migrations/0001_01_01_000002_create_connected_accounts_table.php' => database_path('migrations/0001_01_01_000002_create_connected_accounts_table.php'),
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

        $this->publishes([
            __DIR__.'/../config/filament.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        $this->publishes([
            __DIR__.'/../stubs/app/Actions/Socialstream' => app_path('Actions/Socialstream'),
        ], 'socialstream-actions');

        $this->publishes([
            __DIR__.'/../database/migrations/0001_01_01_000000_create_users_table.php' => database_path('migrations/0001_01_01_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/0001_01_01_000001_make_password_nullable_on_users_table.php' => database_path('migrations/0001_01_01_000001_make_password_nullable_on_users_table.php'),
            __DIR__.'/../database/migrations/0001_01_01_000002_create_connected_accounts_table.php' => database_path('migrations/0001_01_01_000002_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');

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
