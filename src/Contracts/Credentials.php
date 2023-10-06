<?php

namespace JoelButcher\Socialstream\Contracts;

interface Credentials extends RefreshedCredentials
{
    /**
     * Get the ID for the credentials.
     */
    public function getId(): string;
}
