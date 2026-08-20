<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Common\Traits\BelongsToTenant;
use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\BudgetStatus;
use App\Domains\Finance\Enums\BudgetType;
use App\Domains\ProjectManagement\Models\Project;
use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(BudgetFactory::class)]
class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'type',
        'version',
        'is_current',
        'status',
        'currency',
        'forecast_amount',
    ];

    protected $casts = [
        'type' => BudgetType::class,
        'status' => BudgetStatus::class,
        'version' => 'integer',
        'is_current' => 'boolean',
        'forecast_amount' => 'integer',
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
     * Get the total value of all budget line items in cents.
     */
    public function totalAmount(): int
    {
        return (int) $this->items->sum('total');
    }

    /**
     * Get the forecast cost to complete, if set.
     */
    public function forecast(): ?Money
    {
        if ($this->forecast_amount === null) {
            return null;
        }

        return new Money($this->forecast_amount, $this->currency);
    }

    /**
     * Get the company that owns the budget.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the budget.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the line items for the budget.
     *
     * @return HasMany<BudgetItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }
}
