<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use JoelButcher\Socialstream\ConnectedAccount as SocialstreamConnectedAccount;
use JoelButcher\Socialstream\Events\ConnectedAccountCreated;
use JoelButcher\Socialstream\Events\ConnectedAccountDeleted;
use JoelButcher\Socialstream\Events\ConnectedAccountUpdated;

class ConnectedAccount extends SocialstreamConnectedAccount
{
    use HasFactory;
    use HasTimestamps;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'provider_id',
        'name',
        'nickname',
        'email',
        'avatar_path',
        'token',
        'refresh_token',
        'expires_at',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'created' => ConnectedAccountCreated::class,
        'updated' => ConnectedAccountUpdated::class,
        'deleted' => ConnectedAccountDeleted::class,
    ];
}
