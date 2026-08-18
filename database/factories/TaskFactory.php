<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(TaskStatus::cases()),
            'priority' => $this->faker->randomElement(TaskPriority::cases()),
            'due_date' => $this->faker->dateTimeBetween('now', '+2 months')->format('Y-m-d'),
        ];
    }
}
