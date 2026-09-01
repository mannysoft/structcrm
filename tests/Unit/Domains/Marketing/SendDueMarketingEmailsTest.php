<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Marketing;

use App\Domains\Marketing\Mail\MarketingEmail;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->travelTo(now()->startOfDay());
    Mail::fake();
});

test('sends due marketing emails to opted-in users', function () {

    $campaign = MarketingCampaign::factory()->create();
    $step = MarketingCampaignStep::factory()->create([
        'campaign_id' => $campaign->id,
        'delay_days' => 3,
        'subject' => 'Welcome aboard',
    ]);

    $user = User::factory()->marketingOptIn(3)->create();

    $this->artisan('marketing:send-due-emails')->assertSuccessful();

    Mail::assertQueued(MarketingEmail::class, fn ($mail) => $mail->hasTo($user->email));

    $this->assertDatabaseHas('marketing_email_logs', [
        'user_id' => $user->id,
        'campaign_id' => $campaign->id,
        'campaign_step_id' => $step->id,
    ]);
});

test('does not send emails before the step is due', function () {

    $campaign = MarketingCampaign::factory()->create();
    MarketingCampaignStep::factory()->create([
        'campaign_id' => $campaign->id,
        'delay_days' => 7,
    ]);

    User::factory()->marketingOptIn(3)->create();

    $this->artisan('marketing:send-due-emails')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('does not send to opted-out users', function () {

    $campaign = MarketingCampaign::factory()->create();
    MarketingCampaignStep::factory()->create([
        'campaign_id' => $campaign->id,
        'delay_days' => 0,
    ]);

    User::factory()->create([
        'marketing_opt_in' => false,
        'marketing_consented_at' => now()->subDays(10),
    ]);

    $this->artisan('marketing:send-due-emails')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('does not resend emails already sent', function () {

    $campaign = MarketingCampaign::factory()->create();
    MarketingCampaignStep::factory()->create([
        'campaign_id' => $campaign->id,
        'delay_days' => 0,
    ]);

    User::factory()->marketingOptIn(0)->create();

    $this->artisan('marketing:send-due-emails')->assertSuccessful();
    Mail::assertQueued(MarketingEmail::class, 1);

    $this->artisan('marketing:send-due-emails')->assertSuccessful();
    Mail::assertQueued(MarketingEmail::class, 1);
});

test('skips inactive campaigns', function () {

    $campaign = MarketingCampaign::factory()->inactive()->create();
    MarketingCampaignStep::factory()->create([
        'campaign_id' => $campaign->id,
        'delay_days' => 0,
    ]);

    User::factory()->marketingOptIn(0)->create();

    $this->artisan('marketing:send-due-emails')->assertSuccessful();

    Mail::assertNothingQueued();
});
