<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Common\Traits\BelongsToTenant;
use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\ContractStatus;
use App\Domains\Finance\Observers\ContractObserver;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use Database\Factories\ContractFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ContractObserver::class])]
#[UseFactory(ContractFactory::class)]
class Contract extends Model
{
    /** @use HasFactory<ContractFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'contract_number',
        'title',
        'contract_value_amount',
        'contract_value_currency',
        'signed_at',
        'status',
    ];

    protected $casts = [
        'status' => ContractStatus::class,
        'signed_at' => 'immutable_date',
        'contract_value_amount' => 'integer',
    ];

    // PHP 8.4 Property Hooks for Value Object hydration
    public Money $contractValue {
        get => new Money($this->contract_value_amount, $this->contract_value_currency);
        set(Money $money) {
            $this->contract_value_amount = $money->amountInCents;
            $this->contract_value_currency = $money->currency;
        }
    }

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
     * Get the contract value including approved change orders.
     */
    public function revisedValue(): Money
    {
        $approved = $this->project->changeOrders
            ->where('status', ChangeOrderStatus::Approved)
            ->sum('amount');

        return new Money($this->contract_value_amount + (int) $approved, $this->contract_value_currency);
    }

    /**
     * Get the company that owns the contract.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the contract.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
