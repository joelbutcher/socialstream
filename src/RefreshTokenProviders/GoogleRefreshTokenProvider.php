<?php

namespace JoelButcher\Socialstream\RefreshTokenProviders;

use JoelButcher\Socialstream\Concerns\RefreshesOauth2Tokens;
use JoelButcher\Socialstream\Contracts\RefreshTokenProvider;
use Laravel\Socialite\Two\GoogleProvider;

class GoogleRefreshTokenProvider extends GoogleProvider implements RefreshTokenProvider
{
    use RefreshesOauth2Tokens;

    /**
     * Create a new provider instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct(
            request: request(),
            clientId: config('services.google.client_id'),
            clientSecret: config('services.google.client_secret'),
            redirectUrl: '',
        );
    }
}
