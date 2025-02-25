<?php

namespace JoelButcher\Socialstream;

class Features
{
    /**
     * Determine if the given feature is enabled.
     */
    public static function enabled(string $feature): bool
    {
        return in_array($feature, config('socialstream.features', []));
    }

    /**
     * Determine if the application has the generates missing emails feature enabled.
     */
    public static function generatesMissingEmails(): bool
    {
        return static::enabled(static::generateMissingEmails());
    }

    /**
     * Determine if the application supports creating accounts
     * when logging in for the first time via a provider.
     */
    public static function hasCreateAccountOnFirstLoginFeatures(): bool
    {
        return static::enabled(static::createAccountOnFirstLogin());
    }

    /**
     * Determine if the application supports authenticating users from any page.
     */
    public static function hasGlobalLoginFeatures(): bool
    {
        return static::enabled(static::globalLogin());
    }

    /**
     * Determine if the application supports logging into existing
     * accounts when registering with a provider whose email address
     * is already registered.
     *
     * @deprecated in v7, use static::hasLoginOnRegistrationFeatures() instead.
     */
    public static function hasLoginOnRegistrationFeatures(): bool
    {
        return static::enabled(static::loginOnRegistration());
    }

    /**
     * Determine if the application supports authenticating an existing
     * user to a provider who has not yet been linked to a user.
     */
    public static function authenticatesExistingUnlinkedUsers(): bool
    {
        return static::hasLoginOnRegistrationFeatures() ||
            static::enabled(static::authExistingUnlinkedUsers());
    }

    /**
     * Determine if the application should use provider avatars when registering.
     */
    public static function hasProviderAvatarsFeature(): bool
    {
        return static::enabled(static::providerAvatars());
    }

    /**
     * Determine if the application should remember the users session om login.
     */
    public static function hasRememberSessionFeatures(): bool
    {
        return static::enabled(static::rememberSession());
    }

    /**
     * Determine if the application should refresh the tokens on retrieval.
     */
    public static function refreshesOAuthTokens(): bool
    {
        return static::enabled(static::refreshOAuthTokens());
    }

    /**
     * Enabled the generate missing emails feature.
     */
    public static function generateMissingEmails(): string
    {
        return 'generate-missing-emails';
    }

    /**
     * Enable the create account on first login feature.
     */
    public static function createAccountOnFirstLogin(): string
    {
        return 'create-account-on-first-login';
    }

    /**
     * Allows users to be authenticated from any page.
     */
    public static function globalLogin(): string
    {
        return 'global-login';
    }

    /**
     * Enable the login on registration feature.
     *
     * @deprecated in v7, use self::authUnlinkedUsers() instead.
     */
    public static function loginOnRegistration(): string
    {
        return 'login-on-registration';
    }

    /**
     * Enable the ability to auth an existing user who
     * is not yet associated with a new provider.
     */
    public static function authExistingUnlinkedUsers(): string
    {
        return 'auth-existing-unlinked-users';
    }

    /**
     * Enable the provider avatars feature.
     */
    public static function providerAvatars(): string
    {
        return 'provider-avatars';
    }

    /**
     * Enable the remember session feature for logging in.
     */
    public static function rememberSession(): string
    {
        return 'remember-session';
    }

    /**
     * Enable the automatic refresh token update on token retrieval.
     */
    public static function refreshOAuthTokens(): string
    {
        return 'refresh-oauth-tokens';
    }
}
