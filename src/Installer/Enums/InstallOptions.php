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

    public static function breezeOptions(BreezeInstallStack $stack): array
    {
        $default = [
            self::DarkMode,
            self::Pest,
        ];

        return match ($stack) {
            BreezeInstallStack::Vue,
            BreezeInstallStack::React => array_merge([
                self::ServerSideRendering,
                self::TypeScript,
            ], $default),
            default => $default,
        };
    }
}
