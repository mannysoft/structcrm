<?php

declare(strict_types=1);

namespace App\Domains\Sales\Models;

use App\Common\Traits\BelongsToTenant;
use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\EstimateStatus;
use App\Domains\Sales\Observers\EstimateObserver;
use Database\Factories\EstimateFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([EstimateObserver::class])]
#[UseFactory(EstimateFactory::class)]
class Estimate extends Model
{
    /** @use HasFactory<EstimateFactory> */
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'lead_id',
        'project_id',
        'estimate_number',
        'title',
        'subtotal',
        'tax_amount',
        'amount',
        'currency',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'status' => EstimateStatus::class,
        'valid_until' => 'immutable_date',
        'amount' => 'integer',
        'subtotal' => 'integer',
        'tax_amount' => 'integer',
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
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public Money $estimateAmount {
        get => new Money($this->amount, $this->currency);
        set (Money $money) {
            $this->amount = $money->amountInCents;
            $this->currency = $money->currency;
        }
    }

    /**
     * Get the company that owns the estimate.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the lead the estimate belongs to.
     *
     * @return BelongsTo<Lead, $this>
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the project the estimate belongs to.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the line items for the estimate.
     *
     * @return HasMany<EstimateItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class);
    }
}
