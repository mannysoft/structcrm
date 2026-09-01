<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingCampaignStep>
 */
class MarketingCampaignStepFactory extends Factory
{
    protected $model = MarketingCampaignStep::class;

    public function definition(): array
    {
        return [
            'campaign_id' => MarketingCampaign::factory(),
            'subject' => fake()->sentence(),
            'delay_days' => fake()->numberBetween(0, 30),
            'body' => fake()->paragraph(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
