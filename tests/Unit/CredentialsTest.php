<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use App\Models\User;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use JoelButcher\Socialstream\Credentials;
use JoelButcher\Socialstream\Tests\TestCase;

uses(RefreshDatabase::class);

it('can be created from a connected account instance', function (): void
{
    Carbon::setTestNow(DateTime::createFromFormat('Y-m-d H:i:s', '2023-04-13 00:00:00'));

    $credentials = new Credentials(
        User::create([
            'name' => 'Joel Butcher',
            'email' => 'joel@socialstream.dev',
        ])->connectedAccounts()->create([
            'provider' => 'github',
            'provider_id' => 12345678,
            'token' => 'some-token',
            'secret' => 'some-token-secret',
            'refresh_token' => 'some-refresh-token',
            'expires_at' => Carbon::now()->addHour(),
        ])
    );

    $this->assertEquals('12345678', $credentials->getId());
    $this->assertEquals('some-token', $credentials->getToken());
    $this->assertEquals('some-token-secret', $credentials->getTokenSecret());
    $this->assertEquals('some-refresh-token', $credentials->getRefreshToken());
    $this->assertEquals('2023-04-13 01:00:00', $credentials->getExpiry());
});

it('can be cast to an array', function (): void
{
    Carbon::setTestNow(DateTime::createFromFormat('Y-m-d H:i:s', '2023-04-13 00:00:00'));

    $credentials = new Credentials(
        User::create([
            'name' => 'Joel Butcher',
            'email' => 'joel@socialstream.dev',
        ])->connectedAccounts()->create([
            'provider' => 'github',
            'provider_id' => 12345678,
            'token' => 'some-token',
            'secret' => 'some-token-secret',
            'refresh_token' => 'some-refresh-token',
            'expires_at' => Carbon::now()->addHour(),
        ])
    );

    $this->assertEquals([
        'id' => '12345678',
        'token' => 'some-token',
        'token_secret' => 'some-token-secret',
        'refresh_token' => 'some-refresh-token',
        'expiry' => Carbon::createFromFormat('Y-m-d H:i:s', '2023-04-13 01:00:00'),
    ], $credentials->toArray());
});

it('can be json encoded', function (): void
{
    Carbon::setTestNow(DateTime::createFromFormat('Y-m-d H:i:s', '2023-04-13 00:00:00'));

    $credentials = new Credentials(
        User::create([
            'name' => 'Joel Butcher',
            'email' => 'joel@socialstream.dev',
        ])->connectedAccounts()->create([
            'provider' => 'github',
            'provider_id' => 12345678,
            'token' => 'some-token',
            'secret' => 'some-token-secret',
            'refresh_token' => 'some-refresh-token',
            'expires_at' => Carbon::now()->addHour(),
        ])
    );

    $expected = '{"id":"12345678","token":"some-token","token_secret":"some-token-secret","refresh_token":"some-refresh-token","expiry":"2023-04-13T01:00:00.000000Z"}';

    $this->assertEquals($expected, json_encode($credentials));
});
