<?php

namespace JoelButcher\Socialstream;

use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Contracts\GeneratesProviderRedirect;
use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use JoelButcher\Socialstream\Contracts\ResolvesSocialiteUsers;
use JoelButcher\Socialstream\Contracts\SetsUserPasswords;
use JoelButcher\Socialstream\Contracts\UpdatesConnectedAccounts;

class Socialstream
{
    /**
     * Determines if the application is using Socialstream.
     *
     * @var bool
     */
    public static $enabled = true;

    /**
     * Indicates if Socialstream routes will be registered.
     *
     * @var bool
     */
    public static $registersRoutes = true;

    /**
     * The user model that should be used by Jetstream.
     *
     * @var string
     */
    public static $connectedAccountModel = 'App\\Models\\ConnectedAccount';

    /**
     * Determine whether or not Socialstream is enabled in the application.
     *
     * @param  callable|bool  $callback
     * @return bool
     */
    public static function enabled($callback = null)
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
     * Determine whether or not to show Socialstream components on login or registration.
     *
     * @return bool
     */
    public static function show()
    {
        return static::$enabled;
    }

    /**
     * Determine which providers the application supports.
     *
     * @return array
     */
    public static function providers()
    {
        return config('socialstream.providers');
    }

    /**
     * Determine if Socialistream supports a specific Socialite provider.
     *
     * @return bool
     */
    public static function hasSupportFor(string $provider)
    {
        return Providers::enabled($provider);
    }

    /**
     * Determine if the application has support for the Bitbucket provider..
     *
     * @return bool
     */
    public static function hasBitbucketSupport()
    {
        return Providers::hasBitbucketSupport();
    }

    /**
     * Determine if the application has support for the Facebook provider..
     *
     * @return bool
     */
    public static function hasFacebookSupport()
    {
        return Providers::hasFacebookSupport();
    }

    /**
     * Determine if the application has support for the Gitlab provider..
     *
     * @return bool
     */
    public static function hasGitlabSupport()
    {
        return Providers::hasGitlabSupport();
    }

    /**
     * Determine if the application has support for the Github provider..
     *
     * @return bool
     */
    public static function hasGithubSupport()
    {
        return Providers::hasGithubSupport();
    }

    /**
     * Determine if the application has support for the Google provider..
     *
     * @return bool
     */
    public static function hasGoogleSupport()
    {
        return Providers::hasGoogleSupport();
    }

    /**
     * Determine if the application has support for the LinkedIn provider..
     *
     * @return bool
     */
    public static function hasLinkedInSupport()
    {
        return Providers::hasLinkedInSupport();
    }

    /**
     * Determine if the application has support for the Twitter provider..
     *
     * @return bool
     */
    public static function hasTwitterSupport()
    {
        return Providers::hasTwitterSupport();
    }

    /**
     * Determine if the application has the generates missing emails feature enabled.
     *
     * @return bool
     */
    public static function generatesMissingEmails()
    {
        return Features::generatesMissingEmails();
    }

    /**
     * Determine if the application has the create account on first login feature.
     *
     * @return bool
     */
    public static function hasCreateAccountOnFirstLoginFeatures()
    {
        return Features::hasCreateAccountOnFirstLoginFeatures();
    }

    /**
     * Determine if the application should use provider avatars when registering.
     *
     * @return bool
     */
    public static function hasProviderAvatarsFeature()
    {
        return Features::hasProviderAvatarsFeature();
    }

    /**
     * Determine if the application should remember the users session om login.
     *
     * @return bool
     */
    public static function hasRememberSessionFeatures()
    {
        return Features::hasRememberSessionFeatures();
    }

    /**
     * Find a connected account instance fot a given provider and provider ID.
     *
     * @param  string  $provider
     * @param  string  $providerId
     * @return mixed
     */
    public static function findConnectedAccountForProviderAndId(string $provider, string $providerId)
    {
        return static::newConnectedAccountModel()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * Get the name of the connected account model used by the application.
     *
     * @return string
     */
    public static function connectedAccountModel()
    {
        return static::$connectedAccountModel;
    }

    /**
     * Get a new instance of the connected account model.
     *
     * @return mixed
     */
    public static function newConnectedAccountModel()
    {
        $model = static::connectedAccountModel();

        return new $model;
    }

    /**
     * Specify the connected account model that should be used by Jetstream.
     *
     * @param  string  $model
     * @return static
     */
    public static function useConnectedAccountModel(string $model)
    {
        static::$connectedAccountModel = $model;

        return new static;
    }

    /**
     * Register a class / callback that should be used to resolve the user for a Socialite Provider.
     *
     * @param  string  $c;ass
     * @return void
     */
    public static function resolvesSocialiteUsersUsing($class)
    {
        return app()->singleton(ResolvesSocialiteUsers::class, $class);
    }

    /**
     * Register a class / callback that should be used to create users from social providers.
     *
     * @param  string  $class
     * @return void
     */
    public static function createUsersFromProviderUsing(string $class)
    {
        return app()->singleton(CreatesUserFromProvider::class, $class);
    }

    /**
     * Register a class / callback that should be used to create connected accounts.
     *
     * @param  string  $class
     * @return void
     */
    public static function createConnectedAccountsUsing(string $class)
    {
        return app()->singleton(CreatesConnectedAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to update connected accounts.
     *
     * @param  string  $class
     * @return void
     */
    public static function updateConnectedAccountsUsing(string $class)
    {
        return app()->singleton(UpdatesConnectedAccounts::class, $class);
    }

    /**
     * Register a class / callback that should be used to set user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function setUserPasswordsUsing(string $callback)
    {
        return app()->singleton(SetsUserPasswords::class, $callback);
    }

    /**
     * Register a class / callback that should be used to set user passwords.
     *
     * @param  string  $callback
     * @return void
     */
    public static function handlesInvalidStateUsing(string $callback)
    {
        return app()->singleton(HandlesInvalidState::class, $callback);
    }

    /**
     * Register a class / callback that should be used for generating provider redirects.
     *
     * @param  string  $callback
     * @return void
     */
    public static function generatesProvidersRedirectsUsing(string $callback)
    {
        return app()->singleton(GeneratesProviderRedirect::class, $callback);
    }
}
