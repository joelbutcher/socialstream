<?php

namespace JoelButcher\Socialstream;

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
     * Get user of the connected account.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(Jetstream::userModel(), 'user_id');
    }
}
