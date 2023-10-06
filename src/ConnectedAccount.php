<?php

namespace JoelButcher\Socialstream;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class ConnectedAccount extends Model
{
    use HasOauth2Tokens;

    /**
     * Get the credentials used for authenticating services.
     */
    public function getCredentials(): Credentials
    {
        return new Credentials($this);
    }

    /**
     * Get user of the connected account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(Socialstream::userModel(), 'user_id', Socialstream::newUserModel()->getAuthIdentifierName());
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
}
