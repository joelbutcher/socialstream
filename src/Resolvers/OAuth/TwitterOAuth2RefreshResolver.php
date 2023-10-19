<?php

namespace JoelButcher\Socialstream\Resolvers\OAuth;

use JoelButcher\Socialstream\Concerns\RefreshesOAuth2Tokens;
use JoelButcher\Socialstream\Contracts\OAuth2RefreshResolver;
use Laravel\Socialite\Two\TwitterProvider;

class TwitterOAuth2RefreshResolver extends TwitterProvider implements OAuth2RefreshResolver
{
    use RefreshesOAuth2Tokens;

    /**
     * Create a new provider instance.
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
