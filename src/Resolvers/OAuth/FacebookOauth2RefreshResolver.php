<?php

namespace JoelButcher\Socialstream\Resolvers\OAuth;

use JoelButcher\Socialstream\Concerns\RefreshesOauth2Tokens;
use JoelButcher\Socialstream\Contracts\Oauth2RefreshResolver;
use Laravel\Socialite\Two\FacebookProvider;

class FacebookOauth2RefreshResolver extends FacebookProvider implements Oauth2RefreshResolver
{
    use RefreshesOauth2Tokens;

    /**
     * Create a new provider instance.
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
