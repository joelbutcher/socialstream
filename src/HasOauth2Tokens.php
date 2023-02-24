<?php

namespace JoelButcher\Socialstream;

use App\Actions\Socialstream\UpdateConnectedAccount;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property string $token
 * @property string|null $secret
 * @property string|null $refresh_token
 * @property DateTimeInterface|null $expires_at
 */
trait HasOauth2Tokens
{
    /**
     * Intercepts and refreshes the "token" attribute if it has expired.
     */
    protected function token(): Attribute
    {
        return Attribute::make(
            get: function ($token) {
                if (! Socialstream::refresesOauthTokens()) {
                    return $token;
                }

                if (! $token) {
                    return $token;
                }

                if ($this->canRefreshToken()) {
                    app(UpdateConnectedAccount::class)->updateRefreshToken($this);

                    return $this->getAttribute('token');
                }

                return $token;
            },
        );
    }

    /**
     * Determines if the "token" attribute can be refreshed.
     */
    public function canRefreshToken(): bool
    {
        return $this->hasExpiredToken() && $this->hasRefreshToken();
    }

    /**
     * Determines if the token has expired.
     */
    public function hasExpiredToken(): bool
    {
        return $this->expires_at && Carbon::parse($this->expires_at)->lte(now());
    }

    /**
     * Determines if the model has a valid token.
     */
    public function hasRefreshToken(): bool
    {
        return ! is_null($this->refresh_token);
    }
}
