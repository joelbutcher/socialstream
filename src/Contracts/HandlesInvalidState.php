<?php

namespace JoelButcher\Socialstream\Contracts;

use Laravel\Socialite\Two\InvalidStateException;

interface HandlesInvalidState
{
    /**
     * Handle an invalid state exception from a Socialite provider.
     *
     * @param  \Laravel\Socialite\Two\InvalidStateException  $exception
     * @param  callable  $callback
     */
    public function handle(InvalidStateException $exception, callable $callback = null);
}
