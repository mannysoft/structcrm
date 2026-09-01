<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Marketing\Mail\MarketingEmail;
use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Domains\Marketing\Models\MarketingEmailLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendDueMarketingEmails extends Command
{
    protected $signature = 'marketing:send-due-emails';

    protected $description = 'Send due marketing campaign emails to opted-in users';

    public function handle(): int
    {
        $campaigns = MarketingCampaign::query()
            ->where('is_active', true)
            ->with('steps')
            ->get();

        $sent = 0;

        foreach ($campaigns as $campaign) {
            foreach ($campaign->steps as $step) {
                $sent += $this->sendDueStepEmails($campaign, $step);
            }
        }

        $this->info("Marketing emails sent: {$sent}");

        return self::SUCCESS;
    }

    /**
     * Send the given step to every opted-in user who is due and hasn't received it yet.
     */
    private function sendDueStepEmails(MarketingCampaign $campaign, MarketingCampaignStep $step): int
    {
        $alreadySentIds = MarketingEmailLog::query()
            ->where('campaign_step_id', $step->id)
            ->pluck('user_id');

        $users = User::query()
            ->where('marketing_opt_in', true)
            ->where('marketing_consented_at', '<=', now()->subDays($step->delay_days))
            ->whereNotIn('id', $alreadySentIds)
            ->get();

        $sent = 0;

        foreach ($users as $user) {
            Mail::to($user->email)->send(new MarketingEmail(
                user: $user,
                campaign: $campaign,
                step: $step,
                unsubscribeUrl: URL::signedRoute('marketing.unsubscribe', ['user' => $user->getKey()]),
            ));

            MarketingEmailLog::create([
                'user_id' => $user->id,
                'campaign_id' => $campaign->id,
                'campaign_step_id' => $step->id,
                'subject' => $step->subject,
                'sent_at' => now(),
            ]);

            $sent++;
        }

        return $sent;
    }
}
