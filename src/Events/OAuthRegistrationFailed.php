<?php

namespace JoelButcher\Socialstream\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

class OAuthRegistrationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $provider,
        public mixed $connectedAccount,
        public ProviderUser $providerAccount,
    ) {
        //
    }
}
