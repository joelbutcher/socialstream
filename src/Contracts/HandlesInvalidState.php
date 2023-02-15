<?php

namespace JoelButcher\Socialstream\Contracts;

use Laravel\Socialite\Two\InvalidStateException;

interface HandlesInvalidState
{
    /**
     * Handle an invalid state exception from a Socialite provider.
     */
    public function handle(InvalidStateException $exception, callable $callback = null): mixed;
}
