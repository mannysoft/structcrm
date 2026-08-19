<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\EquipmentStatus;
use App\Domains\ProjectManagement\Enums\EquipmentType;
use Database\Factories\EquipmentFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(EquipmentFactory::class)]
class Equipment extends Model
{
    /** @use HasFactory<EquipmentFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'status',
        'service_due_at',
        'notes',
    ];

    protected $casts = [
        'type' => EquipmentType::class,
        'status' => EquipmentStatus::class,
        'service_due_at' => 'immutable_date',
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
     * Get the company that owns the equipment.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the maintenance logs for the equipment.
     *
     * @return HasMany<MaintenanceLog, $this>
     */
    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(MaintenanceLog::class);
    }
}
