<?php

namespace JoelButcher\Socialstream;

use App\Models\ConnectedAccount;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use JoelButcher\Socialstream\Actions\AuthenticateOAuthCallback;
use JoelButcher\Socialstream\Actions\CreateConnectedAccount;
use JoelButcher\Socialstream\Actions\CreateUserFromProvider;
use JoelButcher\Socialstream\Actions\GenerateRedirectForProvider;
use JoelButcher\Socialstream\Actions\HandleInvalidState;
use JoelButcher\Socialstream\Actions\HandleOAuthCallbackErrors;
use JoelButcher\Socialstream\Actions\ResolveSocialiteUser;
use JoelButcher\Socialstream\Actions\UpdateConnectedAccount;
use JoelButcher\Socialstream\Concerns\InteractsWithComposer;
use JoelButcher\Socialstream\Resolvers\OAuth\BitbucketOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\FacebookOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GithubOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GitlabOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\GoogleOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\LinkedInOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\SlackOAuth2RefreshResolver;
use JoelButcher\Socialstream\Resolvers\OAuth\TwitterOAuth2RefreshResolver;

class SocialstreamServiceProvider extends ServiceProvider
{
    use InteractsWithComposer;

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/socialstream.php', 'socialstream');

        // if there's no fortify, we need to bind a stateful guard to the container
        if (! config('fortify.guard')) {
            $this->app->bind(StatefulGuard::class, function () {
                return Auth::guard(config('socialstream.guard'));
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRoutes();
        $this->configureCommands();
        $this->configureRefreshTokenResolvers();
    }

    /**
     * Sets sensible package defaults.
     */
    private function configureDefaults(): void
    {
        $this->publishes([
            __DIR__.'/../config/socialstream.php' => config_path('socialstream.php'),
        ], 'socialstream-config');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations/2025_02_27_000000_update_users_table.php' => database_path('migrations/2025_02_27_000000_update_users_table.php'),
            __DIR__.'/../database/migrations/2025_02_27_000001_create_connected_accounts_table.php' => database_path('migrations/2025_02_27_000001_create_connected_accounts_table.php'),
        ], 'socialstream-migrations');

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
     * Configure the routes offered by the application.
     */
    private function configureRoutes(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../routes/web.php' => base_path('routes/socialstream.php'),
            ], 'socialstream-routes');
        }

        $this->loadRoutesFrom(path: __DIR__.'/../routes/web.php');
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
}
