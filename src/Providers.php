<?php

namespace JoelButcher\Socialstream;

use BadMethodCallException;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Enums\ProviderEnum;

class Providers
{
    public static array $buttonLabels = [];

    /**
     * Determine if the given provider is enabled.
     */
    public static function enabled(string $provider): bool
    {
        return in_array($provider, config('socialstream.providers', []));
    }

    /**
     * Gets an array of all providers.
     */
    public static function all(): array
    {
        return [
            static::bitbucket(),
            static::facebook(),
            static::github(),
            static::gitlab(),
            static::google(),
            static::linkedin(),
            static::linkedinOpenId(),
            static::slack(),
            static::twitterOAuth1(),
            static::twitterOAuth2(),
        ];
    }

    /**
     * Get the label for a given provider.
     */
    public static function buttonLabel(string $provider): ?string
    {
        return static::$buttonLabels[$provider] ?? null;
    }

    /**
     * Get the name for a given provider.
     */
    public static function name(string $provider): string
    {
        return match ($provider) {
            static::github() => 'GitHub',
            static::twitterOAuth1(), static::twitterOAuth2() => 'Twitter',
            static::linkedin(), static::linkedinOpenId() => 'LinkedIn',
            default => Str::of($provider)->replace(['-', '_'], ' ')->lower()->headline()->toString(),
        };
    }

    /**
     * Determine if the application has support for the Bitbucket provider.
     */
    public static function hasBitbucketSupport(): bool
    {
        return static::enabled(static::bitbucket());
    }

    /**
     * Determine if the application has support for the Facebook provider.
     */
    public static function hasFacebookSupport(): bool
    {
        return static::enabled(static::facebook());
    }

    /**
     * Determine if the application has support for the GitLab provider.
     */
    public static function hasGitlabSupport(): bool
    {
        return static::enabled(static::gitlab());
    }

    /**
     * Determine if the application has support for the GitHub provider.
     */
    public static function hasGithubSupport(): bool
    {
        return static::enabled(static::github());
    }

    /**
     * Determine if the application has support for the Google provider.
     */
    public static function hasGoogleSupport(): bool
    {
        return static::enabled(static::google());
    }

    /**
     * Determine if the application has support for the LinkedIn provider.
     */
    public static function hasLinkedInSupport(): bool
    {
        return static::enabled(static::linkedin());
    }

    /**
     * Determine if the application has support for the LinkedIn OpenID provider.
     */
    public static function hasLinkedInOpenIdSupport(): bool
    {
        return static::enabled(static::linkedinOpenId());
    }

    /**
     * Determine if the application has support for the Slack provider.
     */
    public static function hasSlackSupport(): bool
    {
        return static::enabled(static::slack());
    }

    /**
     * Determine if the application has support for the Twitter provider.
     *
     * @deprecated use `hasTwitterOAuth1Support` instead
     */
    public static function hasTwitterSupport(): bool
    {
        return static::enabled(static::twitterOAuth1())
            || static::enabled(static::twitterOAuth2());
    }

    /**
     * Determine if the application has support for the Twitter OAuth 1.0 provider.
     */
    public static function hasTwitterOAuth1Support(): bool
    {
        return static::enabled(static::twitterOAuth1());
    }

    /**
     * Determine if the application has support for the Twitter OAuth 2.0 provider.
     */
    public static function hasTwitterOAuth2Support(): bool
    {
        return static::enabled(static::twitterOAuth2());
    }

    /**
     * Enable the Bitbucket provider.
     */
    public static function bitbucket(string $label = null): string
    {
        return tap(ProviderEnum::Bitbucket->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the Facebook provider.
     */
    public static function facebook(string $label = null): string
    {
        return tap(ProviderEnum::Facebook->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the GitHub provider.
     */
    public static function github(string $label = null): string
    {
        return tap(ProviderEnum::Github->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the GitLab provider.
     */
    public static function gitlab(string $label = null): string
    {
        return tap(ProviderEnum::Gitlab->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the Google provider.
     */
    public static function google(string $label = null): string
    {
        return tap(ProviderEnum::Google->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the LinkedIn provider.
     */
    public static function linkedin(string $label = null): string
    {
        return tap(ProviderEnum::LinkedIn->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the LinkedIn OpenID provider.
     */
    public static function linkedinOpenId(string $label = null): string
    {
        return tap(ProviderEnum::LinkedInOpenId->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the Slack provider.
     */
    public static function slack(string $label = null): string
    {
        return tap(ProviderEnum::Slack->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the Twitter provider.
     *
     * @deprecated use `twitterOAuth1` instead.
     */
    public static function twitter(string $label = null): string
    {
        return tap(ProviderEnum::Twitter->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the Twitter OAuth 1.0 provider.
     */
    public static function twitterOAuth1(string $label = null): string
    {
        return tap(ProviderEnum::Twitter->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    /**
     * Enable the Twitter OAuth 2.0 provider.
     */
    public static function twitterOAuth2(string $label = null): string
    {
        return tap(ProviderEnum::TwitterOauth2->value, fn (string $provider) => static::addLabelFor($provider, $label));
    }

    public static function addLabelFor(ProviderEnum|string $provider, string $label = null): void
    {
        if (! $label) {
            return;
        }

        $key = match (true) {
            $provider instanceof ProviderEnum => $provider->value,
            default => $provider,
        };

        static::$buttonLabels[$key] = $label;
    }

    /**
     * Dynamically handle static calls.
     *
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
     * @throws BadMethodCallException
     */
    protected static function throwBadMethodCallException(string $method): void
    {
        throw new BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
}
