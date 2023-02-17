<?php

namespace JoelButcher\Socialstream\RefreshTokenServices;

use JoelButcher\Socialstream\Concerns\RefreshesOauth2Tokens;
use JoelButcher\Socialstream\Contracts\RefreshTokenProvider;
use Laravel\Socialite\Two\BitbucketProvider;

class BitbucketRefreshTokenProvider extends BitbucketProvider implements RefreshTokenProvider
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
            clientId: config('services.bitbucket.client_id'),
            clientSecret: config('services.bitbucket.client_secret'),
            redirectUrl: '',
        );
    }
}
