<?php

namespace JoelButcher\Socialstream\Data;

use JoelButcher\Socialstream\Enums\ProviderEnum;
use JoelButcher\Socialstream\Providers;

/**
 * @internal
 */
final class ProviderData
{
    private function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $buttonLabel = null,
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('Expected a different value than \'\'');
        }
        if ($name === '') {
            throw new \InvalidArgumentException('Expected a different value than \'\'');
        }
    }

    public static function from(ProviderEnum|string|array $provider): self
    {
        if (is_array($provider)) {
            if (! array_key_exists('id', $provider)) {
                throw new \InvalidArgumentException('Expected the key \'id\' to exist');
            }
            if (! array_key_exists('name', $provider)) {
                throw new \InvalidArgumentException('Expected the key \'name\' to exist');
            }
        }

        return match (true) {
            is_array($provider) => new self(
                $provider['id'],
                Providers::name($provider['name']),
                $provider['buttonLabel'] ?? $provider['label'] ?? $provider['button'] ?? Providers::buttonLabel($provider['id']),
            ),
            $provider instanceof ProviderEnum => new self(
                id: $provider->value,
                name: $provider->name(),
                buttonLabel: Providers::buttonLabel($provider->value)
            ),
            is_string($provider) => new self(
                id: $provider,
                name: Providers::name($provider),
                buttonLabel: Providers::buttonLabel($provider)
            )
        };
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'buttonLabel' => $this->buttonLabel ?: $this->name,
        ];
    }
}
