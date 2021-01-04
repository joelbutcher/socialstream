<?php

namespace JoelButcher\Socialstream;

use Illuminate\Support\Str;

trait HasConnectedAccounts
{
    /**
     * Determine if the given connected account is the current connected account.
     *
     * @param  mixed  $connectedAccount
     * @return bool
     */
    public function isCurrentConnectedAccount($connectedAccount)
    {
        return $connectedAccount->id === $this->currentConnectedAccount->id;
    }

    /**
     * Get the current connected account of the user's context.
     */
    public function currentConnectedAccount()
    {
        if (is_null($this->current_connected_account_id) && $this->id) {
            $this->switchConnectedAccount(
                $this->connectedAccounts()->orderBy('created_at')->first()
            );
        }

        return $this->belongsTo(Socialstream::connectedAccountModel(), 'current_connected_account_id');
    }

    /**
     * Switch the user's context to the given connected account.
     *
     * @param  mixed  $connectedAccount
     * @return bool
     */
    public function switchConnectedAccount($connectedAccount)
    {
        if (! $this->ownsConnectedAccount($connectedAccount)) {
            return false;
        }

        $this->forceFill([
            'current_connected_account_id' => $connectedAccount->id,
        ])->save();

        $this->setRelation('currentConnectedAccount', $connectedAccount);

        return true;
    }

    /**
     * Determine if the user owns the given connected account.
     *
     * @param  mixed  $connectedAccount
     * @return bool
     */
    public function ownsConnectedAccount($connectedAccount)
    {
        return $this->id == $connectedAccount->user_id;
    }

    /**
     * Determine if the user has a specific account type.
     *
     * @param  string  $accountType
     * @return bool
     */
    public function hasTokenFor(string $provider)
    {
        return $this->connectedAccounts->contains('provider_name', Str::lower($provider));
    }

    /**
     * Attempt to retrieve the token for a given provider.
     *
     * @param  string  $provider
     * @return mixed
     */
    public function getTokenFor(string $provider, $default = null)
    {
        if ($this->hasTokenFor($provider)) {
            return $this->connectedAccounts
                ->where('provider_name', Str::lower($provider))
                ->first()
                ->token;
        }

        return $default;
    }

    /**
     * Attempt to find a connected account that belongs to the user,
     * for the given provider and provider id.
     *
     * @param  string  $provider
     * @param  string  $providerId
     * @return \Laravel\Jetstream\ConnectedAccount
     */
    public function getConnectedAccountFor(string $provider, string $providerId)
    {
        return $this->connectedAccounts
            ->where('provider_name', $provider)
            ->where('provider_id', $providerId)
            ->first();
    }

    /**
     * Get all of the connected accounts belonging to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function connectedAccounts()
    {
        return $this->hasMany(Socialstream::connectedAccountModel(), 'user_id');
    }
}
