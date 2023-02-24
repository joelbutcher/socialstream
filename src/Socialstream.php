<?php

namespace JoelButcher\Socialstream;

use Closure;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;
use JoelButcher\Socialstream\Contracts\AuthenticatesOauthCallback;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use JoelButcher\Socialstream\Contracts\HandlesOauthCallbackErrors;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use RuntimeException;

class Socialstream
{
    /**
     * Determines if the application is using Socialstream.
     */
    public static bool $enabled = true;

    /**
     * Indicates if Socialstream routes will be registered.
     */
    public static bool $registersRoutes = true;

    /**
     * The user model that should be used by Jetstream.
     */
    public static string $connectedAccountModel = 'App\\Models\\ConnectedAccount';

    /**
     * The list of resolvers for the refresh tokens,
     * keyed by the provider name.
     *
     * @var array<string, Closure|string>
     */
    public static array $refreshTokenResolvers = [];

    /**
     * Determine whether Socialstream is enabled in the application.
     */
    public static function enabled(callable|bool $callback = null): bool
    {
        if (is_callable($callback)) {
            static::$enabled = $callback();
        }

        if (is_bool($callback)) {
            static::$enabled = $callback;
        }

        return static::$enabled;
    }

    /**
     * Determine whether to show Socialstream components on login or registration.
     */
    public static function show(): bool
    {
        return static::$enabled;
    }

    /**
     * Determine which providers the application supports.
     */
    public static function providers(): array
    {
        return config('socialstream.providers');
    }

    /**
     * Determine if the application has support for the Bitbucket provider..
     */
    public static function hasBitbucketSupport(): bool
    {
        return Providers::hasBitbucketSupport();
    }

    /**
     * Determine if the application has support for the Facebook provider..
     */
    public static function hasFacebookSupport(): bool
    {
        return Providers::hasFacebookSupport();
    }

    /**
     * Determine if the application has support for the Gitlab provider..
     */
    public static function hasGitlabSupport(): bool
    {
        return Providers::hasGitlabSupport();
    }

    /**
     * Determine if the application has support for the Github provider..
     */
    public static function hasGithubSupport(): bool
    {
        return Providers::hasGithubSupport();
    }

    /**
     * Determine if the application has support for the Google provider..
     */
    public static function hasGoogleSupport(): bool
    {
        return Providers::hasGoogleSupport();
    }

    /**
     * Determine if the application has support for the LinkedIn provider..
     */
    public static function hasLinkedInSupport(): bool
    {
        return Providers::hasLinkedInSupport();
    }

    /**
     * Determine if the application has support for the Twitter provider.
     */
    public static function hasTwitterSupport(): bool
    {
        return Providers::hasTwitterSupport();
    }

    /**
     * Determine if the application has support for the Twitter OAuth 1.0 provider..
     */
    public static function hasTwitterOAuth1Support(): bool
    {
        return Providers::hasTwitterOAuth1Support();
    }

    /**
     * Determine if the application has support for the Twitter OAuth 2.0 provider..
     */
    public static function hasTwitterOAuth2Support(): bool
    {
        return Providers::hasTwitterOAuth2Support();
    }

    /**
     * Determine if the application has the generates missing emails feature enabled.
     */
    public static function generatesMissingEmails(): bool
    {
        return Features::generatesMissingEmails();
    }

    /**
     * Determine if the application has the create account on first login feature.
     */
    public static function hasCreateAccountOnFirstLoginFeatures(): bool
    {
        return Features::hasCreateAccountOnFirstLoginFeatures();
    }

    /**
     * Determine if the application should use provider avatars when registering.
     */
    public static function hasProviderAvatarsFeature(): bool
    {
        return Features::hasProviderAvatarsFeature();
    }

    /**
     * Determine if the application should remember the users session om login.
     */
    public static function hasRememberSessionFeatures(): bool
    {
        return Features::hasRememberSessionFeatures();
    }

    /**
     * Determine if the application should refresh the tokens on retrieval.
     */
    public static function refresesOauthTokens(): bool
    {
        return Features::refreshesOauthTokens();
    }

    /**
     * Find a connected account instance fot a given provider and provider ID.
     */
    public static function findConnectedAccountForProviderAndId(string $provider, string $providerId): mixed
    {
        return static::newConnectedAccountModel()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * Get the name of the connected account model used by the application.
     */
    public static function connectedAccountModel(): string
    {
        return static::$connectedAccountModel;
    }

    /**
     * Get a new instance of the connected account model.
     */
    public static function newConnectedAccountModel(): mixed
    {
        $model = static::connectedAccountModel();

        return new $model;
    }

    /**
     * Specify the connected account model that should be used by Jetstream.
     */
    public static function useConnectedAccountModel(string $model): void
    {
        static::$connectedAccountModel = $model;
    }

    /**
     * Register a class / callback that should be used to resolve the user for a Socialite Provider.
     */
    public static function resolvesSocialiteUsersUsing(string $class): void
    {
        app()->singleton(ResolvesSocialiteUsers::class, $class);
    }

    /**
     * Register a class / callback that should be used to create users from social providers.
     */
    public static function createUsersFromProviderUsing(string $class): void
    {
        app()->singleton(CreatesUserFromProvider::class, $class);
    }

    /**
     * Register a class / callback that should be used to create connected accounts.
     */
    public static function createConnectedAccountsUsing(string $class): void
    {
        app()->singleton(CreatesConnectedAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to update connected accounts.
     */
    public static function updateConnectedAccountsUsing(string $class): void
    {
        app()->singleton(UpdatesConnectedAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to set user passwords.
     */
    public static function setUserPasswordsUsing(callable|string $callback): void
    {
        app()->singleton(SetsUserPasswords::class, $callback);
    }

    /**
     * Register a class / callback that should be used to set user passwords.
     */
    public static function handlesInvalidStateUsing(callable|string $callback): void
    {
        app()->singleton(HandlesInvalidState::class, $callback);
    }

    public static function authenticatesOauthCallbackUsing(callable|string $callback): void
    {
        app()->singleton(AuthenticatesOauthCallback::class, $callback);
    }

    public static function handlesOAuthCallbackErrorsUsing(callable|string $callback): void
    {
        app()->singleton(HandlesOauthCallbackErrors::class, $callback);
    }

    /**
     * Register a class / callback that should be used for generating provider redirects.
     */
    public static function generatesProvidersRedirectsUsing(callable|string $callback): void
    {
        app()->singleton(GeneratesProviderRedirect::class, $callback);
    }

    /**
     * Register a class / callback that should be used for refreshing tokens for the given OAuth 2.0 provider.
     */
    public static function refreshesTokensForProviderUsing(string $provider, callable|string $callback): void
    {
        static::$refreshTokenResolvers[Str::lower($provider)] = $callback;
    }

    /**
     * Refresh the given connected account token.
     */
    public static function refreshConnectedAccountToken(ConnectedAccount $connectedAccount): RefreshedCredentials
    {
        $provider = Str::lower($connectedAccount->provider);

        $callback = static::$refreshTokenResolvers[$provider];

        if (! $callback) {
            throw new RuntimeException("Failed to refresh token. Could not find the associated resolver for the '$provider' provider.");
        }

        if (is_callable($callback)) {
            return $callback($connectedAccount);
        }

        return (new $callback)->refreshToken($connectedAccount);
    }
}
