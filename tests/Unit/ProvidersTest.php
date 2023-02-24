<?php

namespace JoelButcher\Socialstream\Tests\Unit;

use Illuminate\Support\Facades\Config;
use JoelButcher\Socialstream\Providers;
use JoelButcher\Socialstream\Tests\TestCase;

class ProvidersTest extends TestCase
{
    public function test_it_supports_bitbucket_provider()
    {
        Config::set('socialstream.providers', [Providers::bitbucket()]);

        $this->assertTrue(Providers::hasBitbucketSupport());
    }

    public function test_it_supports_facebook_provider()
    {
        Config::set('socialstream.providers', [Providers::facebook()]);

        $this->assertTrue(Providers::hasFacebookSupport());
    }

    public function test_it_supports_github_provider()
    {
        Config::set('socialstream.providers', [Providers::github()]);

        $this->assertTrue(Providers::hasGithubSupport());
    }

    public function test_it_supports_gitlab_provider()
    {
        Config::set('socialstream.providers', [Providers::gitlab()]);

        $this->assertTrue(Providers::hasGitlabSupport());
    }

    public function test_it_supports_google_provider()
    {
        Config::set('socialstream.providers', [Providers::google()]);

        $this->assertTrue(Providers::hasGoogleSupport());
    }

    public function test_it_supports_linked_in_provider()
    {
        Config::set('socialstream.providers', [Providers::linkedin()]);

        $this->assertTrue(Providers::hasLinkedInSupport());
    }

    public function test_it_supports_twitter_provider()
    {
        Config::set('socialstream.providers', [Providers::twitter()]);

        $this->assertTrue(Providers::hasTwitterSupport());
    }

    public function test_it_supports_twitter_o_auth_1_provider()
    {
        Config::set('socialstream.providers', [Providers::twitterOAuth1()]);

        $this->assertTrue(Providers::hasTwitterOAuth1Support());
    }

    public function test_it_supports_twitter_o_auth_2_provider()
    {
        Config::set('socialstream.providers', [Providers::twitterOAuth2()]);

        $this->assertTrue(Providers::hasTwitterOAuth2Support());
    }

    public function test_it_supports_custom_providers()
    {
        Config::set('socialstream.providers', ['my-custom-provider']);

        $this->assertTrue(Providers::enabled('my-custom-provider'));
    }

    public function test_it_supports_dynamic_calls_for_custom_providers()
    {
        Config::set('socialstream.providers', ['a-custom-provider', 'another-provider', 'and-another']);

        $this->assertTrue(Providers::hasACustomProviderSupport());
        $this->assertTrue(Providers::hasAnotherProviderSupport());
        $this->assertTrue(Providers::hasAndAnotherSupport());
    }
}
