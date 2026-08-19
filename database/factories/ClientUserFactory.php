<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\ClientUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<ClientUser>
 */
class ClientUserFactory extends Factory
{
    protected $model = ClientUser::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory()->for(Company::factory()),
            'company_id' => fn (array $attributes) => Client::findOrFail($attributes['client_id'])->company_id,
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the client user has not yet accepted their invitation.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
