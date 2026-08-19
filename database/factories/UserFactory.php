<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

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
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'type' => User::TYPE_STAFF,
        ];
    }

    /**
     * Give the created user full access by default so existing tests that
     * don't specify a role retain all permissions. Restrictive tests use the
     * role() state below.
     */
    public function configure(): static
    {
        return $this->afterCreating(fn (User $user) => $user->assignRole('owner'));
    }

    /**
     * Assign the user a specific staff role.
     */
    public function role(string $role): static
    {
        return $this->afterCreating(fn (User $user) => $user->syncRoles($role));
    }

    /**
     * Indicate that the user is an external client portal user.
     */
    public function client(): static
    {
        return $this
            ->afterCreating(fn (User $user) => $user->syncRoles([]))
            ->state(fn (array $attributes) => [
                'type' => User::TYPE_CLIENT,
            ]);
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
}
