<?php

namespace JoelButcher\Socialstream;

use BadMethodCallException;
use Illuminate\Support\Str;

class Providers
{
    /**
     * Determine if the given privider is enabled.
     *
     * @param  string  $provider
     * @return bool
     */
    public static function enabled(string $provider)
    {
        return in_array($provider, config('socialstream.providers', []));
    }

    /**
     * Determine if the application has support for the Bitbucket provider.
     *
     * @return bool
     */
    public static function hasBitbucketSupport()
    {
        return static::enabled(static::bitbucket());
    }

    /**
     * Determine if the application has support for the Facebook provider.
     *
     * @return bool
     */
    public static function hasFacebookSupport()
    {
        return static::enabled(static::facebook());
    }

    /**
     * Determine if the application has support for the GitLab provider.
     *
     * @return bool
     */
    public static function hasGitlabSupport()
    {
        return static::enabled(static::gitlab());
    }

    /**
     * Determine if the application has support for the GitHub provider.
     *
     * @return bool
     */
    public static function hasGithubSupport()
    {
        return static::enabled(static::github());
    }

    /**
     * Determine if the application has support for the Google provider.
     *
     * @return bool
     */
    public static function hasGoogleSupport()
    {
        return static::enabled(static::google());
    }

    /**
     * Determine if the application has support for the LinkedIn provider.
     *
     * @return bool
     */
    public static function hasLinkedInSupport()
    {
        return static::enabled(static::linkedin());
    }

    /**
     * Determine if the application has support for the LinkedIn provider.
     *
     * @return bool
     */
    public static function hasTwitterSupport()
    {
        return static::enabled(static::twitter());
    }

    /**
     * Enable the bitbucket provider.
     *
     * @return string
     */
    public static function bitbucket()
    {
        return 'bitbucket';
    }

    /**
     * Enable the Facebook provider.
     *
     * @return string
     */
    public static function facebook()
    {
        return 'facebook';
    }

    /**
     * Enable the github provider.
     *
     * @return string
     */
    public static function github()
    {
        return 'github';
    }

    /**
     * Enable the gitlab provider.
     *
     * @return string
     */
    public static function gitlab()
    {
        return 'gitlab';
    }

    /**
     * Enable the google provider.
     *
     * @return string
     */
    public static function google()
    {
        return 'google';
    }

    /**
     * Enable the linkedin provider.
     *
     * @return string
     */
    public static function linkedin()
    {
        return 'linkedin';
    }

    /**
     * Enable the twitter provider.
     *
     * @return string
     */
    public static function twitter()
    {
        return 'twitter';
    }

    /**
     * Dynamically handle static calls.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        // If the method exists on the class, call it. Otherwise, attempt to
        // determine the provider from the method name being called.
        if (method_exists(static::class, $name)) {
            return static::$name(...$arguments);
        }

        /** @example $name = "HasMyCustomProviderSupport" */
        if (preg_match('/^has.*Support$/', $name)) {
            $provider = Str::remove('Support', Str::remove('has', $name));

            return static::enabled(Str::kebab($provider)) || static::enabled(Str::lower($provider));
        }

        static::throwBadMethodCallException($name);
    }

    /**
     * Throw a bad method call exception for the given method.
     *
     * @param  string  $method
     * @return void
     *
     * @throws \BadMethodCallException
     */
    protected static function throwBadMethodCallException($method)
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}
