<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Common\Traits\BelongsToTenant;
use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\BillingStatus;
use App\Domains\Finance\Observers\BillingObserver;
use App\Domains\ProjectManagement\Models\Project;
use Database\Factories\BillingFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([BillingObserver::class])]
#[UseFactory(BillingFactory::class)]
class Billing extends Model
{
    /** @use HasFactory<BillingFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'progress_report_id',
        'billing_number',
        'period_start',
        'period_end',
        'previous_billing_amount',
        'current_accomplishment_amount',
        'gross_amount',
        'retention_rate',
        'retention_amount',
        'other_deductions_amount',
        'net_amount',
        'currency',
        'status',
        'certified_at',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'status' => BillingStatus::class,
        'period_start' => 'immutable_date',
        'period_end' => 'immutable_date',
        'due_date' => 'immutable_date',
        'certified_at' => 'datetime',
        'previous_billing_amount' => 'integer',
        'current_accomplishment_amount' => 'integer',
        'gross_amount' => 'integer',
        'retention_rate' => 'float',
        'retention_amount' => 'integer',
        'other_deductions_amount' => 'integer',
        'net_amount' => 'integer',
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

    /**
     * Get the gross billing amount.
     */
    public function grossAmount(): Money
    {
        return new Money($this->gross_amount, $this->currency);
    }

    /**
     * Get the net billing amount.
     */
    public function netAmount(): Money
    {
        return new Money($this->net_amount, $this->currency);
    }

    /**
     * Get the total amount paid against this billing in cents.
     */
    public function paidAmount(): int
    {
        return (int) $this->payments->sum('amount');
    }

    /**
     * Get the outstanding balance in cents.
     */
    public function balanceAmount(): int
    {
        return $this->net_amount - $this->paidAmount();
    }

    /**
     * Get the company that owns the billing.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the billing.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the progress report the billing is based on.
     *
     * @return BelongsTo<ProgressReport, $this>
     */
    public function progressReport(): BelongsTo
    {
        return $this->belongsTo(ProgressReport::class);
    }

    /**
     * Get the line items for the billing.
     *
     * @return HasMany<BillingItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BillingItem::class);
    }

    /**
     * Get the payments recorded against the billing.
     *
     * @return HasMany<BillingPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BillingPayment::class);
    }
}
