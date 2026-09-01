<?php

declare(strict_types=1);

namespace App\Domains\Marketing\Models;

use App\Models\User;
use Database\Factories\MarketingEmailLogFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(MarketingEmailLogFactory::class)]
class MarketingEmailLog extends Model
{
    /** @use HasFactory<MarketingEmailLogFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'campaign_id',
        'campaign_step_id',
        'subject',
        'sent_at',
    ];

    /**
     * The columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Get the user that received the email.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the campaign the email belongs to.
     *
     * @return BelongsTo<MarketingCampaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaign::class, 'campaign_id');
    }

    /**
     * Get the campaign step the email belongs to.
     *
     * @return BelongsTo<MarketingCampaignStep, $this>
     */
    public function campaignStep(): BelongsTo
    {
        return $this->belongsTo(MarketingCampaignStep::class, 'campaign_step_id');
    }
}
