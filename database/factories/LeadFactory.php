<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Sales\Enums\LeadStatus;
use App\Domains\Sales\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'status' => fake()->randomElement(LeadStatus::cases()),
            'notes' => fake()->sentence(),
        ];
    }
}
