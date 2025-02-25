<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Connected Accounts') }}
        </h2>

        <p class="max-w-xl mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('You are free to connect any social accounts to your profile and may remove any connected accounts at any time. If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.') }}
        </p>
    </header>

    <x-input-error :messages="$errors->get('callback')" class="mt-2" />

    <div class="mt-5 space-y-6">
        @foreach (JoelButcher\Socialstream\Socialstream::providers() as $provider)
            @php
                $account = null;
                $account = $user->connectedAccounts->where('provider', $provider['id'])->first();
            @endphp

            <x-connected-account :provider="$provider" created-at="{{ $account?->created_at->diffForHumans() ?? null }}">
                <x-slot name="action">
                    @if (! is_null($account))
                        <div class="flex items-center space-x-6">
                            @if (($user->connectedAccounts->count() > 1 || ! is_null($user->getAuthPassword())))
                                <x-danger-button
                                        x-data=""
                                        x-on:click.prevent="$dispatch('open-modal', 'confirm-connected-account-deletion')"
                                >{{ __('Remove') }}</x-danger-button>
                            @endif
                        </div>
                    @else
                        <x-action-link href="{{ route('oauth.redirect', ['provider' => $provider['id']]) }}">
                            {{ __('Connect') }}
                        </x-action-link>
                    @endif
                </x-slot>
            </x-connected-account>

            @if($account)
                <x-modal name="confirm-connected-account-deletion" :show="$errors->connectedAccountDeletion->isNotEmpty()" focusable>
                    <form method="post" action="{{ route('connected-accounts.destroy', $account) }}" class="p-6">
                        @csrf
                        @method('delete')

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                            {{ __('Are you sure you want to remove this account?') }}
                        </h2>

                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('Please enter your password to confirm you would like to remove this account.') }}
                        </p>

                        <div class="mt-6">
                            <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                            <x-text-input
                                    id="password"
                                    name="password"
                                    type="password"
                                    class="mt-1 block w-3/4"
                                    placeholder="{{ __('Password') }}"
                            />

                            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-secondary-button x-on:click="$dispatch('close')">
                                {{ __('Cancel') }}
                            </x-secondary-button>

                            <x-danger-button class="ms-3">
                                {{ __('Remove Account') }}
                            </x-danger-button>
                        </div>
                    </form>
                </x-modal>
            @endif
        @endforeach
    </div>

    @if (session('status') === 'connected-account-added')
        <p
                x-data="{ show: true }"
                x-show="show"
                x-transition
                x-init="setTimeout(() => show = false, 2000)"
                class="text-sm text-gray-600 dark:text-gray-400"
        >{{ __('Account Connected!') }}</p>
    @endif
</section>
