<?php

declare(strict_types=1);

namespace App\Domains\Sales\Models;

use App\Common\Traits\BelongsToTenant;
use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Enums\ExpenseCategory;
use Database\Factories\ExpenseFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(ExpenseFactory::class)]
class Expense extends Model
{
    /** @use HasFactory<ExpenseFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'category',
        'amount',
        'currency',
        'date',
        'receipt_path',
        'notes',
    ];

    protected $casts = [
        'category' => ExpenseCategory::class,
        'date' => 'immutable_date',
        'amount' => 'integer',
    ];

    // PHP 8.4 Property Hooks for Value Object hydration
    public Money $expenseAmount {
        get => new Money($this->amount, $this->currency);
        set(Money $money) {
            $this->amount = $money->amountInCents;
            $this->currency = $money->currency;
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
     * Get the company that owns the expense.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the expense.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
