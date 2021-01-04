<?php

namespace JoelButcher\Socialstream;

use DateTime;
use DateTimeInterface;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JoelButcher\Socialstream\Contracts\Credentials as CredentialsContract;
use JsonSerializable;

class Credentials implements CredentialsContract, Arrayable, Jsonable, JsonSerializable
{
    /**
     * The credentials user ID.
     *
     * @var string
     */
    protected $id;

    /**
     * The credentials token.
     *
     * @var string
     */
    protected $token;

    /**
     * The credentials token secret.
     *
     * @var string|null
     */
    protected $tokenSecret;

    /**
     * The credentials refresh token.
     *
     * @var string|null
     */
    protected $refreshToken;

    /**
     * The credentials expiry.
     *
     * @var DateTimeInterface|null
     */
    protected $expiry;

    /**
     * Create a new credentials instance.
     *
     * @param  \JoelButcher\Socialstream\ConnectedAccount  $connectedAccount
     */
    public function __construct(ConnectedAccount $connectedAccount)
    {
        $this->id = $connectedAccount->provider_id;
        $this->token = $connectedAccount->token;
        $this->tokenSecret = $connectedAccount->secret;
        $this->refreshToken = $connectedAccount->refresh_token;
        $this->expiry = $connectedAccount->expires_at;
    }

    /**
     * Get the ID for the credentials.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get token for the credentials.
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get the token secret for the credentials.
     *
     * @return string|null
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * Get the refresh token for the credentials.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get the expiry date for the credentials.
     *
     * @return DateTimeInterface|null
     */
    public function getExpiry()
    {
        if (is_null($this->expiry)) {
            return null;
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
            'id' => $this->getId(),
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
