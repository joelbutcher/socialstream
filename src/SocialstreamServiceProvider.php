<?php

namespace JoelButcher\Socialstream;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use JoelButcher\Socialstream\Http\Livewire\ConnectedAccountsForm;
use JoelButcher\Socialstream\Http\Livewire\SetPasswordForm;
use JoelButcher\Socialstream\Http\Middleware\ShareInertiaData;
use JoelButcher\Socialstream\RefreshTokenProviders\BitbucketRefreshTokenProvider;
use JoelButcher\Socialstream\RefreshTokenProviders\FacebookRefreshTokenProvider;
use JoelButcher\Socialstream\RefreshTokenProviders\GithubRefreshTokenProvider;
use JoelButcher\Socialstream\RefreshTokenProviders\GitlabRefreshTokenProvider;
use JoelButcher\Socialstream\RefreshTokenProviders\GoogleRefreshTokenProvider;
use JoelButcher\Socialstream\RefreshTokenProviders\LinkedInRefreshTokenProvider;
use JoelButcher\Socialstream\RefreshTokenProviders\TwitterRefreshTokenProvider;
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
        $this->configureRefreshTokenProviders();

        if (config('jetstream.stack') === 'inertia') {
            $this->bootInertia();
        }
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
     * Configure the refresh token providers as defaults.
     */
    protected function configureRefreshTokenProviders(): void
    {
        Socialstream::refreshesProviderTokenWith(Providers::google(), GoogleRefreshTokenProvider::class);
        Socialstream::refreshesProviderTokenWith(Providers::facebook(), FacebookRefreshTokenProvider::class);
        Socialstream::refreshesProviderTokenWith(Providers::linkedin(), LinkedInRefreshTokenProvider::class);
        Socialstream::refreshesProviderTokenWith(Providers::bitbucket(), BitbucketRefreshTokenProvider::class);
        Socialstream::refreshesProviderTokenWith(Providers::github(), GithubRefreshTokenProvider::class);
        Socialstream::refreshesProviderTokenWith(Providers::gitlab(), GitlabRefreshTokenProvider::class);
        Socialstream::refreshesProviderTokenWith(Providers::twitter(), TwitterRefreshTokenProvider::class);
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
