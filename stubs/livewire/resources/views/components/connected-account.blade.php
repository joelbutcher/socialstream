@props(['provider', 'createdAt' => null])

<div>
    <div class="pl-3 flex items-center justify-between">
        <div class="flex items-center">
            @switch($provider)
                @case(JoelButcher\Socialstream\Providers::facebook())
                    <x-socialstream-icons.facebook class="h-6 w-6 mr-2" />
                    @break
                @case(JoelButcher\Socialstream\Providers::google())
                    <x-socialstream-icons.google class="h-6 w-6 mr-2" />
                    @break
                @case(JoelButcher\Socialstream\Providers::twitter())
                @case(JoelButcher\Socialstream\Providers::twitterOAuth1())
                @case(JoelButcher\Socialstream\Providers::twitterOAuth2())
                    <x-socialstream-icons.twitter class="h-6 w-6 mr-2" />
                    @break
                @case(JoelButcher\Socialstream\Providers::linkedin())
                    <x-socialstream-icons.linkedin class="h-6 w-6 mr-2" />
                    @break
                @case(JoelButcher\Socialstream\Providers::github())
                    <x-socialstream-icons.github class="h-6 w-6 mr-2" />
                    @break
                @case(JoelButcher\Socialstream\Providers::gitlab())
                    <x-socialstream-icons.gitlab class="h-6 w-6 mr-2" />
                    @break
                @case(JoelButcher\Socialstream\Providers::bitbucket())
                    <x-socialstream-icons.bitbucket class="h-6 w-6 mr-2" />
                    @break
                @default
            @endswitch

            <div>
                <div class="text-sm font-semibold text-gray-600">
                    {{ __(ucfirst($provider)) }}
                </div>

                @if (! empty($createdAt))
                    <div class="text-xs text-gray-500">
                        Connected {{ $createdAt }}
                    </div>
                @else
                    <div class="text-xs text-gray-500">
                        {{ __('Not connected.') }}
                    </div>
                @endif
            </div>
        </div>

        <div>
            {{ $action }}
        </div>
    </div>

    @error($provider.'_connect_error')
    <div class="text-sm font-semibold text-red-500 px-3 mt-2">
        {{ $message }}
    </div>
    @enderror
</div>
