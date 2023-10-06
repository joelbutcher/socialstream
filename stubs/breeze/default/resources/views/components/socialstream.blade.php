@if(! empty(JoelButcher\Socialstream\Socialstream::providers()))
    <div class="flex flex-row items-center justify-between py-4 text-gray-600 dark:text-gray-400">
        <hr class="w-full mr-2">
        {{ __('Or') }}
        <hr class="w-full ml-2">
    </div>
@endif

<x-input-error :messages="$errors->get('socialstream')" class="text-center mb-2" />

<div class="flex items-center justify-center gap-x-2">
    @foreach (\JoelButcher\Socialstream\Socialstream::providers() as $provider)
        <a href="{{ route('oauth.redirect', ['provider' => $provider]) }}">
            <x-socialstream-icons.provider-icon :provider="$provider" class="h-6 w-6" />
            <span class="sr-only">{{ $provider }}</span>
        </a>
    @endforeach
</div>
