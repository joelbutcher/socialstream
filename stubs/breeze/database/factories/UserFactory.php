<?php

namespace Database\Factories;

use App\Models\ConnectedAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use JoelButcher\Socialstream\Providers;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$12$Z/vhVO3e.UXKaG11EWgxc.EL7uej3Pi1M0Pq0orF5cbFGtyVh0V3C', // password
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have a connected account for the given provider.
     */
    public function withConnectedAccount(string $provider, callable $callback = null): static
    {
        if (! Providers::enabled($provider)) {
            return $this->state([]);
        }

        return $this->has(
            ConnectedAccount::factory()
                ->state(fn (array $attributes, User $user) => [
                    'provider' => $provider,
                    'user_id' => $user->id,
                ])
                ->when(is_callable($callback), $callback),
            'ownedTeams'
        );
    }
}
