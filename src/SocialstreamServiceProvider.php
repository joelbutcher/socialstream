<?php

namespace JoelButcher\Socialstream;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use JoelButcher\Socialstream\Actions\Auth\AuthenticateOauthCallback;
use JoelButcher\Socialstream\Actions\Auth\HandleOauthCallbackErrors;
use JoelButcher\Socialstream\Http\Livewire\ConnectedAccountsForm;
use JoelButcher\Socialstream\Http\Livewire\SetPasswordForm;
use JoelButcher\Socialstream\Http\Middleware\ShareInertiaData;
use JoelButcher\Socialstream\Resolvers\OAuth\BitbucketOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\FacebookOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GithubOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GitlabOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GoogleOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\LinkedInOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\TwitterOauth2RefreshResolver;
use Livewire\Livewire;

class SocialstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
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
        $this->configurePublishing();
        $this->configureRoutes();
        $this->configureCommands();
        $this->configureRefreshTokenResolvers();

        if (config('jetstream.stack') === 'inertia') {
            $this->bootInertia();
        }

        Socialstream::authenticatesOauthCallbackUsing(AuthenticateOauthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(HandleOauthCallbackErrors::class);
    }

    /**
     * Configure publishing for the package.
     */
    protected function configurePublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/socialstream.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');

        $this->publishes([
            __DIR__.'/../routes/socialstream.php' => base_path('routes/socialstream.php'),
        ], 'socialstream-routes');
    }

    /**
     * Configure the routes offered by the application.
     */
    protected function configureRoutes(): void
    {
        if (Socialstream::$registersRoutes) {
            Route::group([
                'namespace' => 'JoelButcher\Socialstream\Http\Controllers',
                'domain' => config('socialstream.domain', null),
                'prefix' => config('socialstream.prefix', config('socialstream.path')),
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/socialstream.php');
            });
        }
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
        ]);
    }

    /**
     * Configure the refresh token resolvers as defaults.
     */
    protected function configureRefreshTokenResolvers(): void
    {
        Socialstream::refreshesTokensForProviderUsing(Providers::google(), GoogleOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::facebook(), FacebookOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::linkedin(), LinkedInOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::bitbucket(), BitbucketOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::github(), GithubOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::gitlab(), GitlabOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::twitter(), TwitterOauth2RefreshResolver::class);
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
