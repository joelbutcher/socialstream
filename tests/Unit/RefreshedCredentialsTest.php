<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use ArgumentCountError;
use DateTime;
use Illuminate\Support\Carbon;
use JoelButcher\Socialstream\RefreshedCredentials;

test('the \'$token\' property cannot be empty', function (): void {
    $credentials = new RefreshedCredentials('token');
    $this->assertEquals('token', $credentials->getToken());

    $this->expectException(ArgumentCountError::class);
    new RefreshedCredentials;
});

test('the \'$tokenSecret\' property can be nullable', function (): void {
    $credentials = new RefreshedCredentials(
        'token',
        'token-secret',
    );

    $this->assertEquals('token-secret', $credentials->getTokenSecret());

    $credentials = new RefreshedCredentials(
        'token',
    );

    $this->assertNull($credentials->getTokenSecret());
});

test('the \'$refreshToken\' property can be nullable', function (): void {
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
});

test('the \'$expiry\' property can be nullable', function (): void {
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
});

it('can be cast to an array', function (): void {
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
});

it('can be json encoded', function (): void {
    Carbon::setTestNow(DateTime::createFromFormat('Y-m-d H:i:s', '2023-04-13 00:00:00'));

    $credentials = new RefreshedCredentials(
        'some-token',
        'some-token-secret',
        'some-refresh-token',
        Carbon::now()->addHour(),
    );

    $expected = '{"token":"some-token","token_secret":"some-token-secret","refresh_token":"some-refresh-token","expiry":"2023-04-13T01:00:00.000000Z"}';

    $this->assertEquals($expected, json_encode($credentials));
});
