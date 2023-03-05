<?php

namespace JoelButcher\Socialstream\Contracts;

use DateTimeInterface;

interface Credentials extends RefreshedCredentials
{
    /**
     * Get the ID for the credentials.
     *
     * @return string
     */
    public function getId(): string;
}
