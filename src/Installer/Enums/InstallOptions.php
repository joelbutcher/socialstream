<?php

namespace JoelButcher\Socialstream\Installer\Enums;

enum InstallOptions: string
{
    case DarkMode = 'dark';
    case Teams = 'teams';
    case Api = 'api';
    case Verification = 'verification';
    case ServerSideRendering = 'ssr';
    case Pest = 'pest';
    case TypeScript = 'typescript';

    public static function jetstreamOptions(): array
    {
        return [
            self::Teams,
            self::DarkMode,
            self::Api,
            self::Verification,
            self::Pest,
            self::ServerSideRendering,
        ];
    }

    public static function breezeOptions(): array
    {
        return [
            self::DarkMode,
            self::Pest,
            self::ServerSideRendering,
            self::TypeScript,
        ];
    }
}
