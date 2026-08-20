<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Common\Traits\BelongsToTenant;
use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use Database\Factories\BoqItemFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(BoqItemFactory::class)]
class BoqItem extends Model
{
    /** @use HasFactory<BoqItemFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $table = 'boq_items';

    protected $fillable = [
        'company_id',
        'project_id',
        'cost_code_id',
        'item_code',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'total',
        'currency',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'integer',
        'total' => 'integer',
    ];

    // PHP 8.4 Property Hooks for Value Object hydration
    public Money $unitPrice {
        get => new Money($this->unit_price, $this->currency);
        set(Money $money) {
            $this->unit_price = $money->amountInCents;
            $this->currency = $money->currency;
        }
    }

    public Money $totalAmount {
        get => new Money($this->total, $this->currency);
        set(Money $money) {
            $this->total = $money->amountInCents;
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
     * Get the company that owns the BOQ item.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the BOQ item.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the cost code assigned to the BOQ item.
     *
     * @return BelongsTo<CostCode, $this>
     */
    public function costCode(): BelongsTo
    {
        return $this->belongsTo(CostCode::class);
    }
}
