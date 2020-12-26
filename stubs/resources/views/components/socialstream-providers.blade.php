<div class="flex flex-row items-center justify-between py-4 text-gray-500">
    <hr class="w-full mr-2">
        {{ __('Or') }}
    <hr class="w-full ml-2">
</div>

<div class="flex items-center justify-center">
    @if (JoelButcher\Socialstream\Socialstream::hasSupportFor('facebook'))
        <a href="{{ route('oauth.redirect', ['provider' => 'facebook']) }}">
            <x-facebook-icon class="h-6 w-6 mx-2" />
        </a>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasSupportFor('google'))
        <a href="{{ route('oauth.redirect', ['provider' => 'google']) }}" >
            <x-google-icon class="h-6 w-6 mx-2" />
        </a>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasSupportFor('twitter'))
        <a href="{{ route('oauth.redirect', ['provider' => 'twitter']) }}">
            <x-twitter-icon class="h-6 w-6 mx-2" />
        </a>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasSupportFor('linkedin'))
        <a href="{{ route('oauth.redirect', ['provider' => 'linkedin']) }}">
            <x-linked-in-icon class="h-6 w-6 mx-2" />
        </a>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasSupportFor('github'))
        <a href="{{ route('oauth.redirect', ['provider' => 'github']) }}">
            <x-github-icon class="h-6 w-6 mx-2" />
        </a>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasSupportFor('gitlab'))
        <a href="{{ route('oauth.redirect', ['provider' => 'gitlab']) }}">
            <x-gitlab-icon class="h-6 w-6 mx-2" />
        </a>
    @endif

    @if (JoelButcher\Socialstream\Socialstream::hasSupportFor('bitbucket'))
        <a href="{{ route('oauth.redirect', ['provider' => 'bitbucket']) }}">
            <x-bitbucket-icon />
        </a>
    @endif
</div>
