<?php

namespace JoelButcher\Socialstream\Contracts;

use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\RefreshedCredentials;

interface Oauth2RefreshResolver
{
    /**
     * Refreshes the token for the current provider.
     */
    public function refreshToken(ConnectedAccount $connectedAccount): RefreshedCredentials;
}
