<?php

namespace JoelButcher\Socialstream\Installer\Enums;

enum BreezeInstallStack: string
{
    case Blade = 'blade';
    case Livewire = 'livewire';
    case FunctionalLivewire = 'livewire-functional';
    case React = 'react';
    case Vue = 'vue';

    public function label(): string
    {
        return match ($this) {
            self::Blade => 'Blade with Alpine',
            self::Livewire => 'Livewire (Volt Class API) with Alpine',
            self::FunctionalLivewire => 'Livewire (Volt Functional API) with Alpine',
            self::React => 'React with Inertia',
            self::Vue => 'Vue with Inertia',
        };
    }
}
