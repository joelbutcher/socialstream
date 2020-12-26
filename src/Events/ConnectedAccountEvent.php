<?php

namespace JoelButcher\Socialstream\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class ConnectedAccountEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The connected account instance.
     *
     * @var \App\Models\ConnectedAccount
     */
    public $connectedAccount;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\ConnectedAccount  $connectedAccount
     * @return void
     */
    public function __construct($connectedAccount)
    {
        $this->connectedAccount = $connectedAccount;
    }
}
