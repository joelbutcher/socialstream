<?php

namespace JoelButcher\Socialstream\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

class OAuthProviderLinkFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public mixed $user,
        public string $provider,
        public mixed $connectedAccount,
        public ProviderUser $providerAccount,
    ) {
        //
    }
}
