<?php

namespace JoelButcher\Socialstream\Resolvers\OAuth;

use JoelButcher\Socialstream\Concerns\RefreshesOAuth2Tokens;
use JoelButcher\Socialstream\Contracts\OAuth2RefreshResolver;
use Laravel\Socialite\Two\GithubProvider;

class GithubOAuth2RefreshResolver extends GithubProvider implements OAuth2RefreshResolver
{
    use RefreshesOAuth2Tokens;

    /**
     * Create a new provider instance.
     */
    public function __construct()
    {
        parent::__construct(
            request: request(),
            clientId: config('services.github.client_id'),
            clientSecret: config('services.github.client_secret'),
            redirectUrl: '',
        );
    }
}
