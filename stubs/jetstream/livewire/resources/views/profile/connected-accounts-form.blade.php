<x-action-section>
    <x-slot name="title">
        {{ __('Connected Accounts') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Connect your social media accounts to enable Sign In with OAuth.') }}
    </x-slot>

    <x-slot name="content">
        <div class="p-4 bg-red-500/10 dark:bg-red-500/5 text-red-500 border-l-4 border-red-600 dark:border-red-700 rounded font-medium text-sm">
            {{ __('If you feel any of your connected accounts have been compromised, you should disconnect them immediately and change your password.') }}
        </div>

        <div class="space-y-6 mt-6">
            @foreach ($this->providers as $provider)
                @php
                    $account = null;
                    $account = $this->accounts->where('provider', $provider['id'])->first();
                @endphp

                <x-connected-account :provider="$provider" created-at="{{ $account?->created_at }}">
                    <x-slot name="action">
                        @if (! is_null($account))
                            <div class="flex items-center space-x-6">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos() && ! is_null($account->avatar_path))
                                    <button class="cursor-pointer ms-6 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none" wire:click="setAvatarAsProfilePhoto({{ $account->id }})">
                                        {{ __('Use Avatar as Profile Photo') }}
                                    </button>
                                @endif

                                @if (($this->accounts->count() > 1 || ! is_null(auth()->user()->getAuthPassword())))
                                    <x-danger-button wire:click="confirmRemoveAccount({{ $account->id }})" wire:loading.attr="disabled">
                                        {{ __('Remove') }}
                                    </x-danger-button>
                                @endif
                            </div>
                        @else
                            <x-action-link href="{{ route('oauth.redirect', ['provider' => $provider['id']]) }}">
                                {{ __('Connect') }}
                            </x-action-link>
                        @endif
                    </x-slot>

                </x-connected-account>
            @endforeach
        </div>

        <!-- Logout Other Devices Confirmation Modal -->
        <x-dialog-modal wire:model="confirmingAccountRemoval">
            <x-slot name="title">
                {{ __('Are you sure you want to remove this account?') }}
            </x-slot>

            <x-slot name="content">
                {{ __('Please enter your password to confirm you would like to remove this account.') }}

                <div class="mt-4" x-data="{}" x-on:confirming-delete-user.window="setTimeout(() => $refs.password.focus(), 250)">
                    <x-input type="password" class="mt-1 block w-3/4"
                             autocomplete="current-password"
                             placeholder="{{ __('Password') }}"
                             x-ref="password"
                             wire:model="password"
                             wire:keydown.enter="removeConnectedAccount" />

                    <x-input-error for="password" class="mt-2" />
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('confirmingRemove')" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ml-2" wire:click="removeConnectedAccount" wire:loading.attr="disabled">
                    {{ __('Remove Account') }}
                </x-danger-button>
            </x-slot>
        </x-dialog-modal>
    </x-slot>
</x-action-section>
