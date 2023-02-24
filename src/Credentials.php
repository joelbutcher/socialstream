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
     */
    protected string $id;

    /**
     * Create a new credentials instance.
     */
    public function __construct(ConnectedAccount $connectedAccount)
    {
        $this->id = $connectedAccount->provider_id;

        parent::__construct(
            $connectedAccount->token,
            $connectedAccount->secret,
            $connectedAccount->refresh_token,
            $connectedAccount->expires_at,
        );
    }

    /**
     * Get the ID for the credentials.
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
        return array_merge([
            'id' => $this->getId(),
        ], parent::toArray());
    }
}
