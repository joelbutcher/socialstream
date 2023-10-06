<?php

namespace JoelButcher\Socialstream\Contracts;

interface Credentials extends RefreshedCredentials
{
    /**
     * Get the ID for the credentials.
     *
     * @return string
     */
    public function getId(): string;
}
