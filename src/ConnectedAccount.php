<?php

namespace JoelButcher\Socialstream;

use App\Actions\Socialstream\UpdateConnectedAccount;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Laravel\Jetstream\Jetstream;

abstract class ConnectedAccount extends Model
{
    /**
     * Get the credentials used for authenticating services.
     *
     * @return \JoelButcher\Socialstream\Credentials
     */
    public function getCredentials()
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Jetstream::userModel(), 'user_id', (Jetstream::newUserModel())->getAuthIdentifierName());
    }

    /**
     * Get the data that should be shared with Inertia.
     *
     * @return array
     */
    public function getSharedInertiaData()
    {
        return $this->getSharedData();
    }

    /**
     * Get the data that should be shared.
     *
     * @return array
     */
    public function getSharedData()
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
