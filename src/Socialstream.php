<?php

namespace JoelButcher\Socialstream;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use JoelButcher\Socialstream\Contracts\AuthenticatesOAuthCallback;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use JoelButcher\Socialstream\Contracts\HandlesOAuthCallbackErrors;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;
use JoelButcher\Socialstream\Enums\Provider;
use RuntimeException;

class Socialstream
{
    public const VERSION = '6.0.0';

    /**
     * Determines if the application is using Socialstream.
     */
    public static bool $enabled = true;

    /**
     * The user model that should be used by Socialstream.
     *
     * @var class-string
     */
    public static string $userModel = 'App\\Models\\User';

    /**
     * The user model that should be used by Socialstream.
     *
     * @var class-string
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
     * The callback that should be used to prompt the user to confirm their OAuth authorization.
     *
     * @var ?(Closure(Provider): (Response|RedirectResponse|View))
     */
    public static ?Closure $oAuthConfirmationPrompt = null;

    /**
     * Get the name of the user model used by the application.
     */
    public static function userModel(): string
    {
        return static::$userModel;
    }

    /**
     * Get a new instance of the user model.
     */
    public static function newUserModel(): mixed
    {
        $model = static::userModel();

        return new $model;
    }

    /**
     * Specify the user model that should be used by Socialstream.
     */
    public static function useUserModel(string $model): static
    {
        static::$userModel = $model;

        return new static;
    }

    /**
     * Determine whether Socialstream is enabled in the application.
     */
    public static function enabled(callable|bool|null $callback = null): bool
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

    public static function divideText(): string
    {
        return config('socialstream.divide_text');
    }

    /**
     * Attemp to get a provider for the given input.
     */
    public static function provider(Provider|string $provider): Provider
    {
        return is_string($provider) ? Provider::from($provider) : $provider;
    }

    /**
     * Determine which providers the application supports.
     *
     * @return Collection<int, Provider>
     */
    public static function providers(): Collection
    {
        return collect(config('socialstream.providers'))
            ->map(fn ($provider) => Provider::from($provider));
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
     * Determine if the application should remember the users session om login.
     */
    public static function hasRememberSessionFeatures(): bool
    {
        return Features::hasRememberSessionFeatures();
    }

    /**
     * Determine if the application should refresh the tokens on retrieval.
     */
    public static function refreshesOAuthTokens(): bool
    {
        return Features::refreshesOAuthTokens();
    }

    /**
     * Find a connected account instance for a given provider and provider ID.
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
     * Specify the connected account model that should be used by Socialstream.
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
    public static function handlesInvalidStateUsing(callable|string $callback): void
    {
        app()->singleton(HandlesInvalidState::class, $callback);
    }

    public static function authenticatesOAuthCallbackUsing(callable|string $callback): void
    {
        app()->singleton(AuthenticatesOAuthCallback::class, $callback);
    }

    public static function handlesOAuthCallbackErrorsUsing(callable|string $callback): void
    {
        app()->singleton(HandlesOAuthCallbackErrors::class, $callback);
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
    public static function refreshConnectedAccountToken($connectedAccount): RefreshedCredentials
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

    /**
     * Register a callback that should be used to prompt the user to confirm their OAuth.
     *
     * @param ?(Closure(Provider): (Response|RedirectResponse|View)) $callback
     */
    public static function promptOAuthLinkUsing(?Closure $callback = null): void
    {
        self::$oAuthConfirmationPrompt = $callback;
    }

    public static function getOAuthConfirmationPrompt(): Closure
    {
        return self::$oAuthConfirmationPrompt ?? function (Provider $provider): View|Response {
            return Inertia::render('auth/confirm-link-account', [
                'provider' => $provider->toArray(),
            ]);
        };
    }
}
