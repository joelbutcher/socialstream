<div class="space-y-6 mt-6 mb-2">
    @if(! empty(\JoelButcher\Socialstream\Socialstream::providers()))
        <div class="relative flex items-center">
            <div class="flex-grow border-t border-gray-400 dark:border-gray-500"></div>
            <span class="flex-shrink text-gray-400 dark:text-gray-500 px-6">
                {{ config('socialstream.prompt', 'Or Login Via') }}
            </span>
            <div class="flex-grow border-t border-gray-400 dark:border-gray-500"></div>
        </div>
    @endif

    <x-input-error :for="'socialstream'" class="text-center"/>

    <div class="grid gap-4">
        @foreach (\JoelButcher\Socialstream\Socialstream::providers() as $provider)
            <a class="flex gap-2 items-center justify-center transition duration-200 border border-gray-400 w-full py-2.5 rounded-lg text-sm shadow-sm hover:shadow-md"
               href='{{ route('oauth.redirect', $provider['id']) }}'>
                <x-socialstream-icons.provider-icon :provider="$provider['id']" class="h-6 w-6"/>
                <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ $provider['buttonLabel'] }}</span>
            </a>
        @endforeach
    </div>
</div>
