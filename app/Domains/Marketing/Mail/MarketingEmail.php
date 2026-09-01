<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Mail;

use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MarketingEmail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly User $user,
        public readonly MarketingCampaign $campaign,
        public readonly MarketingCampaignStep $step,
        public readonly string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->step->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.marketing.message',
            with: [
                'user' => $this->user,
                'campaign' => $this->campaign,
                'step' => $this->step,
                'bodyHtml' => $this->renderBody(),
                'unsubscribeUrl' => $this->unsubscribeUrl,
            ],
        );
    }

    /**
     * Replace the {name} and {company_name} tokens in the step body.
     */
    private function renderBody(): string
    {
        return strtr($this->step->body, [
            '{name}' => e($this->user->name),
            '{company_name}' => e($this->user->company?->name ?? ''),
        ]);
    }
}
