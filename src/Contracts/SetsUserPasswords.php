<?php

namespace JoelButcher\Socialstream\Contracts;

interface SetsUserPasswords
{
    /**
     * Validate and sets the user's password.
     */
    public function set(mixed $user, array $input): void;
}
