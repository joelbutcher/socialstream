<?php

namespace JoelButcher\Socialstream\Data;

use JoelButcher\Socialstream\Enums\ProviderEnum;
use JoelButcher\Socialstream\Providers;
use Webmozart\Assert\Assert;

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
        Assert::stringNotEmpty($id);
        Assert::stringNotEmpty($name);
    }

    public static function from(ProviderEnum|string|array $provider): self
    {
        if (is_array($provider)) {
            Assert::keyExists($provider, 'id');
            Assert::keyExists($provider, 'name');
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
            'buttonLabel' => $this->buttonLabel,
        ];
    }
}
