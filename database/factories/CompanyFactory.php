<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'tax_number' => fake()->bothify('??#########'),
            'timezone' => 'Asia/Manila',
            'currency' => Currency::PHP,
        ];
    }
}
