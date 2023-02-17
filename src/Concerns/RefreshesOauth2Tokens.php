<?php

namespace JoelButcher\Socialstream\Concerns;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\RefreshedCredentials;
use Laravel\Socialite\Two\AbstractProvider;

trait RefreshesOauth2Tokens
{
    /**
     * Refresh the token for the current provider.
     *
     * @param  \JoelButcher\Socialstream\ConnectedAccount  $connectedAccount
     * @return \JoelButcher\Socialstream\RefreshedCredentials
     */
    public function refreshToken(ConnectedAccount $connectedAccount): RefreshedCredentials
    {
        /** @var AbstractProvider&RefreshesOauth2Tokens $this */
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => $this->getRefreshTokenHeaders($connectedAccount->refresh_token),
            RequestOptions::FORM_PARAMS => $this->getRefreshTokenFields($connectedAccount->refresh_token),
        ]);

        $response = json_decode($response->getBody(), true);

        return new RefreshedCredentials(
            token: Arr::get($response, 'access_token'),
            refreshToken: Arr::get($response, 'refresh_token'),
            expiry: now()->addSeconds(Arr::get($response, 'expires_in')),
        );
    }

    /**
     * Get the headers for the refresh token request.
     *
     * @param  string  $refreshToken
     * @return array
     */
    protected function getRefreshTokenHeaders($refreshToken)
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * Get the POST fields for the refresh token request.
     *
     * @param  string  $refreshToken
     * @return array
     */
    protected function getRefreshTokenFields($refreshToken)
    {
        return [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ];
    }
}