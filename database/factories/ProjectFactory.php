<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(ProjectStatus::cases()),
            'budget_amount' => $this->faker->numberBetween(1000000, 50000000),
            'budget_currency' => 'USD',
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+6 months')->format('Y-m-d'),
        ];
    }
}
