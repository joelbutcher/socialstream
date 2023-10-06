<?php

namespace JoelButcher\Socialstream\Installer\Enums;

enum JetstreamInstallStack: string
{
    case Livewire = 'livewire';
    case Inertia = 'inertia';

    public function label(): string
    {
        return match ($this) {
            self::Livewire => 'Livewire',
            self::Inertia => 'Vue with Inertia',
        };
    }
}
