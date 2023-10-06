<?php

namespace JoelButcher\Socialstream\Installer\Enums;

enum BreezeInstallStack: string
{
    case Blade = 'blade';
    case Livewire = 'livewire';
    case React = 'react';
    case Vue = 'vue';

    public function label(): string
    {
        return match ($this) {
            self::Blade => 'Blade with Alpine',
            self::Livewire => 'Livewire with Alpine',
            self::React => 'React with Inertia',
            self::Vue => 'Vue with Inertia',
        };
    }
}
