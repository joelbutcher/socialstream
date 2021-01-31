<?php

namespace JoelButcher\Socialstream;

class Features
{
    /**
     * Determine if the given feature is enabled.
     *
     * @param  string  $feature
     * @return bool
     */
    public static function enabled(string $feature)
    {
        return in_array($feature, config('socialstream.features', []));
    }

    /**
     * Determine if the application supports creating accounts
     * when logging in for the first time via a provider.
     *
     * @return bool
     */
    public static function hasCreateAccountOnFirstLoginFeatures()
    {
        return static::enabled(static::createAccountOnFirstLogin());
    }

    /**
     * Determine if the application should use provider avatars when registering.
     * 
     * @return bool
     */
    public static function hasProviderAvatarsFeature()
    {
        return static::enabled(static::providerAvatars());
    }

    /**
     * Determine if the application should remember the users session om login.
     *
     * @return bool
     */
    public static function hasRememberSessionFeatures()
    {
        return static::enabled(static::rememberSession());
    }

    /**
     * Enable the create account on first login feature.
     *
     * @return string
     */
    public static function createAccountOnFirstLogin()
    {
        return 'create-account-on-first-login';
    }

    /**
     * Enable the provider avatars feature.
     * 
     * @return string
     */
    public static function providerAvatars()
    {
        return 'provider-avatars';
    }

    /**
     * Enable the remember session feature for logging in.
     *
     * @return string
     */
    public static function rememberSession()
    {
        return 'remember-session';
    }
}
