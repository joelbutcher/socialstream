<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use Illuminate\Support\Facades\Config;
use JoelButcher\Socialstream\Providers;

it('supports the \'bitbucket\' provider', function (): void {
    Config::set('socialstream.providers', [Providers::bitbucket()]);

    $this->assertTrue(Providers::hasBitbucketSupport());
});

it('supports the \'facebook\' provider', function (): void {
    Config::set('socialstream.providers', [Providers::facebook()]);

    $this->assertTrue(Providers::hasFacebookSupport());
});

it('supports the \'github\' provider', function (): void {
    Config::set('socialstream.providers', [Providers::github()]);

    $this->assertTrue(Providers::hasGithubSupport());
});

it('supports the \'gitlab\' provider', function (): void {
    Config::set('socialstream.providers', [Providers::gitlab()]);

    $this->assertTrue(Providers::hasGitlabSupport());
});

it('supports the \'google\' provider', function (): void {
    Config::set('socialstream.providers', [Providers::google()]);

    $this->assertTrue(Providers::hasGoogleSupport());
});

it('supports the \'linked\' in_provider', function (): void {
    Config::set('socialstream.providers', [Providers::linkedin()]);

    $this->assertTrue(Providers::hasLinkedInSupport());
});

it('supports the \'linked-openid\' in_provider', function (): void {
    Config::set('socialstream.providers', [Providers::linkedinOpenId()]);

    $this->assertTrue(Providers::hasLinkedInOpenIdSupport());
});

it('supports the \'twitter\' provider', function (): void {
    Config::set('socialstream.providers', [Providers::twitter()]);

    $this->assertTrue(Providers::hasTwitterSupport());
});

it('supports the \'twitter\' OAuth 1.0 provider', function (): void {
    Config::set('socialstream.providers', [Providers::twitterOAuth1()]);

    $this->assertTrue(Providers::hasTwitterOAuth1Support());
});

it('supports the \'twitter\' OAuth 2.0 provider', function (): void {
    Config::set('socialstream.providers', [Providers::twitterOAuth2()]);

    $this->assertTrue(Providers::hasTwitterOAuth2Support());
});

it('supports custom providers', function (): void {
    Config::set('socialstream.providers', ['my-custom-provider']);

    $this->assertTrue(Providers::enabled('my-custom-provider'));
});

it('supports dynamic calls for custom providers', function (): void {
    Config::set('socialstream.providers', ['a-custom-provider', 'another-provider', 'and-another']);

    $this->assertTrue(Providers::hasACustomProviderSupport());
    $this->assertTrue(Providers::hasAnotherProviderSupport());
    $this->assertTrue(Providers::hasAndAnotherSupport());
});

it('can set a label for a provider', function () {
    Providers::google('The Google Provider');

    expect(Providers::buttonLabel(Providers::google()))
        ->toEqual('The Google Provider');
});
