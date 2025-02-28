@php use \JoelButcher\Socialstream\Enums\Provider; @endphp
@props([
    'provider' => null,
    'class' => null,
])

@switch($provider)
    @case(Provider::Bitbucket)
        <x-socialstream-icons.bitbucket class="{{ $class }}" />
        @break
    @case(Provider::Facebook)
        <x-socialstream-icons.facebook class="{{ $class }}" />
        @break
    @case(Provider::Github)
        <x-socialstream-icons.github class="{{ $class }}" />
        @break
    @case(Provider::Gitlab)
        <x-socialstream-icons.gitlab class="{{ $class }}" />
        @break
    @case(Provider::Google)
        <x-socialstream-icons.google class="{{ $class }}" />
        @break
    @case(Provider::LinkedIn)
    @case(Provider::LinkedInOpenId)
        <x-socialstream-icons.linkedin class="{{ $class }}" />
        @break
    @case(Provider::Slack)
    @case(Provider::SlackOpenId)
        <x-socialstream-icons.slack class="{{ $class }}" />
        @break
    @case(Provider::Twitch)
        <x-socialstream-icons.twitch class="{{ $class }}" />
        @break
    @case(Provider::Twitter)
    @case(Provider::TwitterOAuth2)
        <x-socialstream-icons.twitter class="{{ $class }}" />
        @break
    @case(Provider::X)
        <x-socialstream-icons.x class="{{ $class }}" />
        @break
    @default
        <div>{{ $provider->name }}</div>
@endswitch
