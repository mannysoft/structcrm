<?php

declare(strict_types=1);

namespace App\Domains\Sales\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estimate extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'lead_id',
        'project_id',
        'title',
        'amount',
        'currency',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'status' => EstimateStatus::class,
        'valid_until' => 'immutable_date',
        'amount' => 'integer',
    ];

    public Money $estimateAmount {
        get => new Money($this->amount, $this->currency);
        set (Money $money) {
            $this->amount = $money->amountInCents;
            $this->currency = $money->currency;
        }
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
