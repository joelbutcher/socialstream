<?php

namespace JoelButcher\Socialstream\Contracts;

use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\RefreshedCredentials;

interface RefreshTokenProvider
{
    /**
     * Refresh the token for the current provider.
     *
     * @param  \JoelButcher\Socialstream\ConnectedAccount  $connectedAccount
     * @return \JoelButcher\Socialstream\RefreshedCredentials
     */
    public function refreshToken(ConnectedAccount $connectedAccount): RefreshedCredentials;
}