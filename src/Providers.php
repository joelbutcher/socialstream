<?php

namespace JoelButcher\Socialstream;

use BadMethodCallException;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Enums\Provider;

class Providers
{
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
            static::slackOpenId(),
            static::twitch(),
            static::twitterOAuth1(),
            static::twitterOAuth2(),
            static::x(),
        ];
    }

    /**
     * Get the name for a given provider.
     */
    public static function name(Provider|string $provider): string
    {
        $provider = match (true) {
            $provider instanceof Provider => $provider->value,
            default => $provider,
        };

        return match ($provider) {
            static::github() => 'GitHub',
            static::twitterOAuth1(), static::twitterOAuth2() => 'Twitter',
            static::linkedin(), static::linkedinOpenId() => 'LinkedIn',
            default => Str::of($provider)->replace(['-', '_'], ' ')->lower()->headline()->toString(),
        };
    }

    /**
     * Enable the Bitbucket provider.
     */
    public static function bitbucket(): string
    {
        return Provider::Bitbucket->value;
    }

    /**
     * Enable the Facebook provider.
     */
    public static function facebook(): string
    {
        return Provider::Facebook->value;
    }

    /**
     * Enable the GitHub provider.
     */
    public static function github(): string
    {
        return Provider::Github->value;
    }

    /**
     * Enable the GitLab provider.
     */
    public static function gitlab(): string
    {
        return Provider::Gitlab->value;
    }

    /**
     * Enable the Google provider.
     */
    public static function google(): string
    {
        return Provider::Google->value;
    }

    /**
     * Enable the LinkedIn provider.
     */
    public static function linkedin(): string
    {
        return Provider::LinkedIn->value;
    }

    /**
     * Enable the LinkedIn OpenID provider.
     */
    public static function linkedinOpenId(): string
    {
        return Provider::LinkedInOpenId->value;
    }

    /**
     * Enable the Slack provider.
     */
    public static function slack(): string
    {
        return Provider::Slack->value;
    }

    /**
     * Enable the Slack provider.
     */
    public static function slackOpenId(): string
    {
        return Provider::SlackOpenId->value;
    }

    /**
     * Enable the Twitch provider.
     */
    public static function twitch(): string
    {
        return Provider::Twitch->value;
    }

    /**
     * Enable the Twitter OAuth 1.0 provider.
     */
    public static function twitterOAuth1(): string
    {
        return Provider::Twitter->value;
    }

    /**
     * Enable the Twitter OAuth 2.0 provider.
     */
    public static function twitterOAuth2(): string
    {
        return Provider::TwitterOAuth2->value;
    }

    /**
     * Enable the X provider.
     */
    public static function x(): string
    {
        return Provider::X->value;
    }
}
