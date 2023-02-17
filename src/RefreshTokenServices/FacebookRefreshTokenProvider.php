<?php

namespace JoelButcher\Socialstream\RefreshTokenServices;

use JoelButcher\Socialstream\Concerns\RefreshesOauth2Tokens;
use JoelButcher\Socialstream\Contracts\RefreshTokenProvider;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookRefreshTokenProvider extends FacebookProvider implements RefreshTokenProvider
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
            clientId: config('services.facebook.client_id'),
            clientSecret: config('services.facebook.client_secret'),
            redirectUrl: '',
        );
    }
}
