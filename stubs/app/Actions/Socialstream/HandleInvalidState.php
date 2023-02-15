<?php

namespace App\Actions\Socialstream;

use JoelButcher\Socialstream\Contracts\HandlesInvalidState;
use Laravel\Socialite\Two\InvalidStateException;

class HandleInvalidState implements HandlesInvalidState
{
    /**
     * Handle an invalid state exception from a Socialite provider.
     */
    public function handle(InvalidStateException $exception, callable $callback = null): mixed
    {
        if ($callback) {
            return $callback($exception);
        }

        throw $exception;
    }
}
