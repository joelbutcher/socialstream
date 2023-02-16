<?php

namespace JoelButcher\Socialstream;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JoelButcher\Socialstream\Contracts\Credentials as CredentialsContract;
use JsonSerializable;

class Credentials extends RefreshedCredentials implements CredentialsContract, Arrayable, Jsonable, JsonSerializable
{
    /**
     * The credentials user ID.
     *
     * @var string
     */
    protected string $id;

    /**
     * Create a new credentials instance.
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
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get the instance as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'id' => $this->getId(),
        ]);
    }
}
