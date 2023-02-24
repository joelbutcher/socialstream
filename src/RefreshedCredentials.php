<?php

namespace JoelButcher\Socialstream;

use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JoelButcher\Socialstream\Contracts\RefreshedCredentials as RefreshedCredentialsContract;
use JsonSerializable;

class RefreshedCredentials implements RefreshedCredentialsContract, Arrayable, Jsonable, JsonSerializable
{
    /**
     * Create a new credentials instance.
     */
    public function __construct(
        protected string $token,
        protected ?string $tokenSecret = null,
        protected ?string $refreshToken = null,
        protected ?DateTimeInterface $expiry = null,
    ) {
    }

    /**
     * Get token for the credentials.
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get the token secret for the credentials.
     */
    public function getTokenSecret(): ?string
    {
        return $this->tokenSecret;
    }

    /**
     * Get the refresh token for the credentials.
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Get the expiry date for the credentials.
     */
    public function getExpiry(): ?DateTimeInterface
    {
        if (is_null($this->expiry)) {
            return null;
        }

        if ($this->expiry instanceof DateTimeInterface) {
            return $this->expiry;
        }

        return new DateTime($this->expiry);
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'token' => $this->getToken(),
            'token_secret' => $this->getTokenSecret(),
            'refresh_token' => $this->getRefreshToken(),
            'expiry' => $this->getExpiry(),
        ];
    }

    /**
     * Convert the object to its JSON representation.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this, $options);
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array<string, string|DateTimeInterface>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Convert the object instance to a string.
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
