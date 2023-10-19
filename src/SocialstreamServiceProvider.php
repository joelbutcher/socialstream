<?php

namespace JoelButcher\Socialstream;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use JoelButcher\Socialstream\Actions\Auth\Breeze\Blade\AuthenticateOauthCallback as BreezeBladeAuthenticateOauthCallback;
use JoelButcher\Socialstream\Actions\Auth\Breeze\HandleOauthCallbackErrors as BreezeHandleOauthCallbackErrors;
use JoelButcher\Socialstream\Actions\Auth\Breeze\Livewire\AuthenticateOauthCallback as BreezeLivewireAuthenticateOauthCallback;
use JoelButcher\Socialstream\Actions\Auth\Filament\AuthenticateOauthCallback as FilamentAuthenticateOauthCallback;
use JoelButcher\Socialstream\Actions\Auth\Filament\HandleOauthCallbackErrors as FilamentHandleOauthCallbackErrors;
use JoelButcher\Socialstream\Actions\Auth\Jetstream\AuthenticateOauthCallback as JetstreamAuthenticateOauthCallback;
use JoelButcher\Socialstream\Actions\Auth\Jetstream\HandleOauthCallbackErrors as JetstreamHandleOauthCallbackErrors;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Http\Livewire\ConnectedAccountsForm;
use JoelButcher\Socialstream\Http\Livewire\SetPasswordForm;
use JoelButcher\Socialstream\Http\Middleware\ShareInertiaData;
use JoelButcher\Socialstream\Resolvers\OAuth\BitbucketOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\FacebookOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GithubOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GitlabOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GoogleOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\LinkedInOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\SlackOauth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\TwitterOauth2RefreshResolver;
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
        $this->configureRoutes();
        $this->configureCommands();
        $this->configureRefreshTokenResolvers();

        match (true) {
            $this->hasComposerPackage('laravel/breeze') => $this->bootLaravelBreeze(),
            $this->hasComposerPackage('laravel/jetstream') => $this->bootLaravelJetstream(),
            $this->hasComposerPackage('filament/filament') => $this->bootFilament(),
            default => null,
        };

        if ($this->hasComposerPackage('inertiajs/inertia-laravel')) {
            $this->bootInertia();
        }

        if ($this->app->runningInConsole()) {
            match (true) {
                $this->hasComposerPackage('filament/filament') => $this->publishes([
                    __DIR__.'/../config/filament.php' => config_path('socialstream.php'),
                ], 'socialstream-config'),
                default => $this->publishes([
                    __DIR__.'/../config/socialstream.php' => config_path('socialstream.php'),
                ], 'socialstream-config')
            };
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
     * Configure the refresh token resolvers as defaults.
     */
    protected function configureRefreshTokenResolvers(): void
    {
        Socialstream::refreshesTokensForProviderUsing(Providers::bitbucket(), BitbucketOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::facebook(), FacebookOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::github(), GithubOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::gitlab(), GitlabOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::google(), GoogleOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::linkedin(), LinkedInOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::linkedinOpenId(), LinkedInOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::slack(), SlackOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::twitterOAuth1(), TwitterOauth2RefreshResolver::class);
        Socialstream::refreshesTokensForProviderUsing(Providers::twitterOAuth2(), TwitterOauth2RefreshResolver::class);
    }

    /**
     * Boot the services required for Laravel Jetstream.
     */
    protected function bootLaravelJetstream(): void
    {
        Socialstream::authenticatesOauthCallbackUsing(JetstreamAuthenticateOauthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(JetstreamHandleOauthCallbackErrors::class);

        if (! $this->app->runningInConsole()) {
            return;
        }

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
        Socialstream::handlesOAuthCallbackErrorsUsing(BreezeHandleOauthCallbackErrors::class);
        Socialstream::authenticatesOauthCallbackUsing(match (true) {
            class_exists('\App\Providers\VoltServiceProvider') => BreezeLivewireAuthenticateOauthCallback::class,
            default => BreezeBladeAuthenticateOauthCallback::class,
        });

        if (! $this->app->runningInConsole()) {
            return;
        }

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
        Socialstream::authenticatesOauthCallbackUsing(FilamentAuthenticateOauthCallback::class);
        Socialstream::handlesOAuthCallbackErrorsUsing(FilamentHandleOauthCallbackErrors::class);

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../database/migrations/2014_10_12_000000_create_users_table.php' => database_path('migrations/2014_10_12_000000_create_users_table.php'),
            __DIR__.'/../database/migrations/2020_12_22_000000_create_connected_accounts_table.php' => database_path('migrations/2020_12_22_000000_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');

        $this->publishes([
            __DIR__.'/../routes/socialstream.php' => base_path('routes/socialstream.php'),
        ], 'socialstream-routes');
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
