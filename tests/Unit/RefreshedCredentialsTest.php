<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use ArgumentCountError;
use DateTime;
use Illuminate\Support\Carbon;
use JoelButcher\Socialstream\RefreshedCredentials;
use JoelButcher\Socialstream\Tests\TestCase;

class RefreshedCredentialsTest extends TestCase
{
    public function test_token_cannot_be_empty(): void
    {
        $credentials = new RefreshedCredentials('token');
        $this->assertEquals('token', $credentials->getToken());

        $this->expectException(ArgumentCountError::class);
        new RefreshedCredentials();
    }

    public function test_token_secret_can_be_nullable(): void
    {
        $credentials = new RefreshedCredentials(
            'token',
            'token-secret',
        );

        $this->assertEquals('token-secret', $credentials->getTokenSecret());

        $credentials = new RefreshedCredentials(
            'token',
        );

        $this->assertNull($credentials->getTokenSecret());
    }

    public function test_refresh_token_can_be_nullable(): void
    {
        $credentials = new RefreshedCredentials(
            'token',
            'token-secret',
            'refresh-token'
        );

        $this->assertEquals('refresh-token', $credentials->getRefreshToken());

        $credentials = new RefreshedCredentials(
            'token',
        );

        $this->assertNull($credentials->getRefreshToken());
    }

    public function test_expiry_can_be_nullable(): void
    {
        $credentials = new RefreshedCredentials(
            'some-token',
            'some-token-secret',
            'some-refresh-token',
            Carbon::now()->addHour(),
        );

        $this->assertEquals(
            Carbon::now()->addHour()->format('Y-m-d H:i:s'),
            $credentials->getExpiry()->format('Y-m-d H:i:s')
        );

        $credentials = new RefreshedCredentials(
            'token',
        );

        $this->assertNull($credentials->getExpiry());
    }

    public function test_it_can_be_cast_to_an_array()
    {
        Carbon::setTestNow(DateTime::createFromFormat('Y-m-d H:i:s', '2023-04-13 00:00:00'));

        $credentials = new RefreshedCredentials(
            'some-token',
            'some-token-secret',
            'some-refresh-token',
            Carbon::now()->addHour(),
        );

        $this->assertEquals([
            'token' => 'some-token',
            'token_secret' => 'some-token-secret',
            'refresh_token' => 'some-refresh-token',
            'expiry' => Carbon::createFromFormat('Y-m-d H:i:s', '2023-04-13 01:00:00'),
        ], $credentials->toArray());
    }

    public function test_it_can_be_json_encoded()
    {
        Carbon::setTestNow(DateTime::createFromFormat('Y-m-d H:i:s', '2023-04-13 00:00:00'));

        $credentials = new RefreshedCredentials(
            'some-token',
            'some-token-secret',
            'some-refresh-token',
            Carbon::now()->addHour(),
        );

        $expected = '{"token":"some-token","token_secret":"some-token-secret","refresh_token":"some-refresh-token","expiry":"2023-04-13T01:00:00.000000Z"}';

        $this->assertEquals($expected, json_encode($credentials));
    }
}
