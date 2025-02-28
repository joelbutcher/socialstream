<?php

use App\Models\ConnectedAccount;
use Livewire\Volt\Component;

new class extends Component {
    public ConnectedAccount $account;

    public function submit(): void
    {
        auth()->user()->update([
            'avatar' => $this->account->avatar,
        ]);

        $this->dispatch('avatar-updated');
    }
}; ?>

<form wire:submit="submit">
    <flux:link variant="ghost" type="submit" class="cursor-pointer">
        <span class="text-sm">{{ __('Use Avatar') }}</span>
    </flux:link>
</form>
