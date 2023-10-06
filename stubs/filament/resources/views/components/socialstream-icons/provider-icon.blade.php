<div class="text-gray-900 dark:text-gray-100">
    @switch($provider)
        @case(\JoelButcher\Socialstream\Providers::bitbucket())
            <x-socialstream-icons.bitbucket {{ $attributes }} />
            @break

        @case (JoelButcher\Socialstream\Providers::facebook())
            <x-socialstream-icons.facebook {{$attributes}} />
            @break

        @case (JoelButcher\Socialstream\Providers::github())
            <x-socialstream-icons.github {{$attributes}} />
            @break

        @case (JoelButcher\Socialstream\Providers::gitlab())
            <x-socialstream-icons.gitlab {{$attributes}} />
            @break

        @case (JoelButcher\Socialstream\Providers::google())
            <x-socialstream-icons.google {{$attributes}} />
            @break

        @case (JoelButcher\Socialstream\Providers::linkedin())
        @case (JoelButcher\Socialstream\Providers::linkedinOpenId())
            <x-socialstream-icons.linkedin {{$attributes}} />
            @break

        @case (JoelButcher\Socialstream\Providers::slack())
            <x-socialstream-icons.slack {{$attributes}} />
            @break

        @case (JoelButcher\Socialstream\Providers::twitterOAuth1())
        @case (JoelButcher\Socialstream\Providers::twitterOAuth2())
        @case (JoelButcher\Socialstream\Providers::twitter())
            <x-socialstream-icons.twitter {{$attributes}} />
            @break
    @endswitch
</div>
