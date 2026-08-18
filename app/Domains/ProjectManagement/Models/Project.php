<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Common\ValueObjects\Money;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'status',
        'budget_amount',
        'budget_currency',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'start_date' => 'immutable_date',
        'end_date' => 'immutable_date',
        'budget_amount' => 'integer',
    ];

    // PHP 8.4 Property Hooks for Value Object hydration
    public Money $budget {
        get => new Money($this->budget_amount, $this->budget_currency);
        set (Money $money) {
            $this->budget_amount = $money->amountInCents;
            $this->budget_currency = $money->currency;
        }
    }
}
