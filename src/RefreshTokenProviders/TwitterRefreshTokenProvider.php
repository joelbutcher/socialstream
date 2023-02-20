<?php

namespace JoelButcher\Socialstream\RefreshTokenProviders;

use JoelButcher\Socialstream\Concerns\RefreshesOauth2Tokens;
use JoelButcher\Socialstream\Contracts\RefreshTokenProvider;
use Laravel\Socialite\Two\TwitterProvider;

class TwitterRefreshTokenProvider extends TwitterProvider implements RefreshTokenProvider
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
            clientId: config('services.twitter.client_id'),
            clientSecret: config('services.twitter.client_secret'),
            redirectUrl: '',
        );
    }
}
