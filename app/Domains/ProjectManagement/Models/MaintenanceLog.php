<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Models;

use App\Common\Traits\BelongsToTenant;
use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use Database\Factories\MaintenanceLogFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(MaintenanceLogFactory::class)]
class MaintenanceLog extends Model
{
    /** @use HasFactory<MaintenanceLogFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'equipment_id',
        'service_date',
        'description',
        'cost_amount',
        'cost_currency',
    ];

    protected $casts = [
        'service_date' => 'immutable_date',
        'cost_amount' => 'integer',
    ];

    // PHP 8.4 Property Hooks for Value Object hydration
    public Money $cost {
        get => new Money($this->cost_amount, $this->cost_currency);
        set(Money $money) {
            $this->cost_amount = $money->amountInCents;
            $this->cost_currency = $money->currency;
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
     * Get the company that owns the maintenance log.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the equipment that owns the maintenance log.
     *
     * @return BelongsTo<Equipment, $this>
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
