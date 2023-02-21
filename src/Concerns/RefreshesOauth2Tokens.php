<?php

namespace JoelButcher\Socialstream\Concerns;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use JoelButcher\Socialstream\ConnectedAccount;
use JoelButcher\Socialstream\RefreshedCredentials;
use Laravel\Socialite\Two\AbstractProvider;

/**
 * @mixin AbstractProvider&RefreshesOauth2Tokens
 */
trait RefreshesOauth2Tokens
{
    /**
     * Refresh the token for the current provider.
     *
     * @throws GuzzleException
     */
    public function refreshToken(ConnectedAccount $connectedAccount): RefreshedCredentials
    {
        if (is_null($connectedAccount->refresh_token)) {
            throw new \RuntimeException('A valid refresh token is required.');
        }

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => $this->getRefreshTokenHeaders(),
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
     * @return array<string, string>
     */
    protected function getRefreshTokenHeaders(): array
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * Get the POST fields for the refresh token request.
     *
     * @return array<string, string>
     */
    protected function getRefreshTokenFields(string $refreshToken): array
    {
        return [
            'grant_type' => 'refresh_token',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $refreshToken,
        ];
    }
}
