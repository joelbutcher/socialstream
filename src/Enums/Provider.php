<?php

namespace JoelButcher\Socialstream\Enums;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Str;
use JsonSerializable;

enum Provider: string implements Arrayable, JsonSerializable
{
    case Bitbucket = 'bitbucket';
    case Facebook = 'facebook';
    case Github = 'github';
    case Gitlab = 'gitlab';
    case Google = 'google';
    case LinkedIn = 'linkedin';
    case LinkedInOpenId = 'linkedin-openid';
    case Slack = 'slack';
    case SlackOpenId = 'slack-openid';
    case Twitch = 'twitch';
    case Twitter = 'twitter';
    case TwitterOAuth2 = 'twitter-oauth-2';
    case X = 'x';

    /**
     * Get the name of the provider.
     */
    public function name(): string
    {
        return match ($this) {
            self::Github => 'GitHub',
            self::Twitter => 'Twitter (OAuth 1.0)',
            self::TwitterOAuth2 => 'Twitter (OAuth 2.0)',
            self::LinkedIn => 'LinkedIn',
            self::LinkedInOpenId => 'LinkedIn (OpenID)',
            self::Slack => 'Slack',
            self::SlackOpenId => 'Slack (OpenID)',
            default => Str::of($this->value)->headline(),
        };
    }

    /**
     * Get the name of the provider.
     */
    public function buttonLabel(): string
    {
        return match ($this) {
            self::Github => 'GitHub',
            self::Twitter, self::TwitterOAuth2 => 'Twitter',
            self::LinkedIn, self::LinkedInOpenId => 'LinkedIn',
            self::Slack, self::SlackOpenId => 'Slack',
            default => Str::of($this->value)->headline(),
        };
    }

    /**
     * Get the provider's details as an array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->name(),
            'buttonLabel' => $this->buttonLabel(),
        ];
    }

    /**
     * Convert the object into something JSON serializable.
     */
    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
