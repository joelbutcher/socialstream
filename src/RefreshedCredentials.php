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
     * The credentials token.
     *
     * @var string
     */
    protected string $token;

    /**
     * The credentials token secret.
     *
     * @var string|null
     */
    protected string|null $tokenSecret;

    /**
     * The credentials refresh token.
     *
     * @var string|null
     */
    protected string|null $refreshToken;

    /**
     * The credentials expiry.
     *
     * @var DateTimeInterface|null
     */
    protected DateTimeInterface|null $expiry;

    /**
     * Create a new credentials instance.
     *
     * @param string|null $token
     * @param string|null $tokenSecret
     * @param string|null $refreshToken
     * @param string|null $expiry
     */
    public function __construct(
        ?string $token = null,
        ?string $tokenSecret = null,
        ?string $refreshToken = null,
        ?string $expiry = null,
    ) {
        $this->token = $token;
        $this->tokenSecret = $tokenSecret;
        $this->refreshToken = $refreshToken;
        $this->expiry = $expiry;
    }

    /**
     * Get token for the credentials.
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Get the token secret for the credentials.
     *
     * @return string|null
     */
    public function getTokenSecret(): ?string
    {
        return $this->tokenSecret;
    }

    /**
     * Get the refresh token for the credentials.
     *
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * Get the expiry date for the credentials.
     *
     * @return DateTimeInterface|null
     * @throws Exception
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
     * @return array
     */
    public function toArray()
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
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return $this->toArray();
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return mixed
     */
    public function jsonSerialize()
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
        return json_encode($this->toJson());
    }
}
