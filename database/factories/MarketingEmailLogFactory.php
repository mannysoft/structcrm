<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Domains\Marketing\Models\MarketingEmailLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MarketingEmailLog>
 */
class MarketingEmailLogFactory extends Factory
{
    protected $model = MarketingEmailLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'campaign_id' => MarketingCampaign::factory(),
            'campaign_step_id' => MarketingCampaignStep::factory(),
            'subject' => fake()->sentence(),
            'sent_at' => now(),
        ];
    }
}
