<?php

namespace JoelButcher\Socialstream\Resolvers\OAuth;

use JoelButcher\Socialstream\Concerns\RefreshesOAuth2Tokens;
use JoelButcher\Socialstream\Contracts\OAuth2RefreshResolver;
use Laravel\Socialite\Two\SlackProvider;

class SlackOAuth2RefreshResolver extends SlackProvider implements OAuth2RefreshResolver
{
    use RefreshesOAuth2Tokens;

    /**
     * Create a new provider instance.
     */
    public function __construct()
    {
        parent::__construct(
            request: request(),
            clientId: config('services.slack.client_id'),
            clientSecret: config('services.slack.client_secret'),
            redirectUrl: '',
        );
    }
}
