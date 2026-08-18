<?php

namespace Database\Factories;

use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'company_id' => null, // Should be passed by the user
            'name' => $this->faker->words(3, true),
            'status' => $this->faker->randomElement(ProjectStatus::cases()),
            'budget_amount' => $this->faker->numberBetween(1000000, 50000000), // 10k to 500k in cents
            'budget_currency' => 'USD',
            'start_date' => clone $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => clone $this->faker->dateTimeBetween('+2 months', '+6 months'),
        ];
    }
}
