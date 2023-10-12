<?php

namespace JoelButcher\Socialstream\Enums;

use Illuminate\Support\Str;
use JoelButcher\Socialstream\Providers;

/**
 * @internal
 */
enum ProviderEnum: string
{
    case Bitbucket = 'bitbucket';
    case Facebook = 'facebook';
    case Github = 'github';
    case Gitlab = 'gitlab';
    case Google = 'google';
    case LinkedIn = 'linkedin';
    case LinkedInOpenId = 'linkedin-openid';
    case Slack = 'slack';
    case Twitter = 'twitter';
    case TwitterOauth2 = 'twitter-oauth-2';

    public function name(): string
    {
        return match ($this) {
            self::Github => 'GitHub',
            self::Twitter, self::TwitterOauth2 => 'Twitter',
            self::LinkedIn, self::LinkedInOpenId => 'LinkedIn',
            default => Str::of($this->value)->headline(),
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->value,
            'name' => $this->name(),
            'buttonLabel' => Providers::buttonLabel($this->value),
        ];
    }
}
