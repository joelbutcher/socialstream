<?php

use App\Models\ConnectedAccount;
use App\Livewire\Actions\Logout;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use JoelButcher\Socialstream\Events\ConnectedAccountDeleted;
use JoelButcher\Socialstream\Providers;
use Livewire\Volt\Component;

new class extends Component {
    public ConnectedAccount $account;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function unlinkAccount(): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        $this->account->delete();

        event(new ConnectedAccountDeleted($this->account));

        Session::flash('status', __(':provider account unlinked.', ['provider' => Providers::name($this->account->provider)]));

        $this->redirect(route('linked-accounts'), navigate: true);
    }
}; ?>

<div class="px-6 py-4 gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700">
    <div class="grid w-full md:grid-cols-2 items-center space-y-6 md:space-y-0">
        <div class="flex flex-row-reverse md:flex-row justify-between md:justify-start items-center gap-3">
            <x-socialstream-icon :provider="$account->provider" class="h-8 w-8"/>

            <div class="flex items-center justify-center gap-2">
                <flux:profile
                        :name="$account->name"
                        :avatar="$account->avatar"
                        :chevron="false"
                />
            </div>
        </div>

        <div class="flex flex-col-reverse md:flex-row items-center justify-between md:justify-end gap-3 md:gap-2">
            <livewire:settings.update-avatar :account="$account"/>

            @if(! auth()->user()->getAuthPassword())
                <div class="gap-3 md:gap-2">
                    <p class="md:hidden text-xs text-red-500/80 dark:text-red-500/80 text-center my-4">
                        {{ __('Set a password to unlink this account') }}
                    </p>
                    <flux:tooltip content="Set a password to unlink this account" class="w-full md:w-auto">
                        <flux:button variant="danger" :disabled="! auth()->user()->getAuthPassword()" class="w-full">
                            {{ __('Unlink') }}
                        </flux:button>
                    </flux:tooltip>
                </div>
            @else
                <flux:modal.trigger name="confirm-unlink-account-{{$account->id}}">
                    <flux:button variant="danger" x-data=""
                                 x-on:click.prevent="$dispatch('open-modal', 'confirm-unlink-account-{{$account->id}}')">
                        {{ __('Unlink') }}
                    </flux:button>
                </flux:modal.trigger>
            @endif
        </div>
    </div>

    @if(! auth()->user()->getAuthPassword())
        <p class="hidden md:block text-xs text-red-500/80 dark:text-red-500/80 text-right not-[hidden]:mt-2">
            {{ __('Set a password to unlink this account') }}
        </p>
    @endif


    <flux:modal name="confirm-unlink-account-{{$account->id}}" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form wire:submit="unlinkAccount" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Are you sure you want to unlink this account?') }}</flux:heading>

                <flux:subheading>
                    {{ __('Once unlinked, you will no longer be able to sign in with this account.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" id="password" label="{{ __('Password') }}" type="password"
                        name="password"/>

            <div class="flex justify-end space-x-2">
                <flux:modal.close>
                    <flux:button variant="filled">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Unlink account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</div>
