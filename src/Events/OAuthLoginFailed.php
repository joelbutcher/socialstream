<?php

namespace JoelButcher\Socialstream\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * @deprecated in v7, use OAuthFailed instead.
 */
class OAuthLoginFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $provider,
        public ProviderUser $providerAccount,
    ) {
        //
    }
}
