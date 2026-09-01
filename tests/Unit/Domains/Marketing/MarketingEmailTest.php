<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Marketing;

use App\Domains\Marketing\Mail\MarketingEmail;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Models\User;

test('marketing email renders the step body and replaces tokens', function () {

    $user = User::factory()->create(['name' => 'Tony Stark']);
    $campaign = MarketingCampaign::factory()->create();
    $step = MarketingCampaignStep::factory()->create([
        'campaign_id' => $campaign->id,
        'subject' => 'Welcome aboard',
        'body' => '<p>Hello {name} from {company_name}</p>',
    ]);

    $mailable = new MarketingEmail(
        user: $user,
        campaign: $campaign,
        step: $step,
        unsubscribeUrl: 'https://example.com/unsubscribe',
    );

    $mailable
        ->assertSeeInHtml('Welcome aboard')
        ->assertSeeInHtml('Hello Tony Stark')
        ->assertSeeInHtml('https://example.com/unsubscribe');
});
