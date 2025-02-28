<?php

use JoelButcher\Socialstream\Socialstream;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')]
class extends Component {
    public string $password = '';
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
            :title="__('Link :provider', ['provider' => Socialstream::provider(request()->string('provider'))->name])"
            :description="__('Please confirm your password before linking your :provider account.', ['provider' => Socialstream::provider(request()->string('provider'))->name])"
    />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form method="post" action="{{ route('oauth.confirm', ['provider' => request()->input('provider')]) }}" class="flex flex-col gap-6">
        @csrf

        <!-- Password -->
        <flux:input
            wire:model="password"
            id="password"
            label="{{ __('Password') }}"
            type="password"
            name="password"
            required
            autocomplete="new-password"
            placeholder="Password"
        />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('Confirm') }}</flux:button>
    </form>
</div>
