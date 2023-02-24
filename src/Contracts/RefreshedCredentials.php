<?php

namespace JoelButcher\Socialstream\Contracts;

use DateTimeInterface;

interface RefreshedCredentials
{
    /**
     * Get token for the credentials.
     *
     * @return string
     */
    public function getToken();

    /**
     * Get the token secret for the credentials.
     *
     * @return string|null
     */
    public function getTokenSecret();

    /**
     * Get the refresh token for the credentials.
     *
     * @return string|null
     */
    public function getRefreshToken();

    /**
     * Get the expiry date for the credentials.
     *
     * @return DateTimeInterface|null
     */
    public function getExpiry();
}
