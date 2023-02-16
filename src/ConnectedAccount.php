<?php

namespace JoelButcher\Socialstream;

use App\Actions\Socialstream\UpdateConnectedAccount;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Jetstream\Jetstream;

/**
 * @property int $id
 * @property int $user_id
 * @property string $provider_id
 * @property string $token
 * @property string|null $secret
 * @property string|null $refresh_token
 * @property DateTimeInterface|null $expires_at
 */
abstract class ConnectedAccount extends Model
{
    /**
     * Get the credentials used for authenticating services.
     */
    public function getCredentials(): Credentials
    {
        return new Credentials($this);
    }

    /**
     * Intercept the "token" attribute to refresh it automatically
     * if the current configuration allows.
     *
     * @return Attribute
     */
    protected function token(): Attribute
    {
        return Attribute::make(
            get: function ($token) {
                if (! Socialstream::refreshesTokensOnRetrieveFeature()) {
                    return $token;
                }

                if (! $token) {
                    return $token;
                }

                if ($this->tokenIsExpired() && $this->hasRefreshToken()) {
                    app(UpdateConnectedAccount::class)->updateRefreshToken($this);

                    return $this->getAttribute('token');
                }

                return $token;
            },
        );
    }

    /**
     * Get user of the connected account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Jetstream::userModel(), 'user_id', (Jetstream::newUserModel())->getAuthIdentifierName());
    }

    /**
     * Get the data that should be shared with Inertia.
     *
     * @return array<string, mixed>
     */
    public function getSharedInertiaData(): array
    {
        return $this->getSharedData();
    }

    /**
     * Get the data that should be shared.
     *
     * @return array<string, mixed>
     */
    public function getSharedData(): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'avatar_path' => $this->avatar_path,
            'created_at' => optional($this->created_at)->diffForHumans(),
        ];
    }

    /**
     * Check if the token is expired.
     *
     * @return bool
     */
    public function tokenIsExpired()
    {
        return $this->expires_at && Carbon::parse($this->expires_at)->lte(now());
    }

    /**
     * Check if the token can be refreshed.
     *
     * @return bool
     */
    public function hasRefreshToken()
    {
        return ! is_null($this->refresh_token);
    }
}
