<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Enums\ProgressReportStatus;
use App\Domains\Finance\Observers\ProgressReportObserver;
use App\Domains\ProjectManagement\Models\Project;
use Database\Factories\ProgressReportFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([ProgressReportObserver::class])]
#[UseFactory(ProgressReportFactory::class)]
class ProgressReport extends Model
{
    /** @use HasFactory<ProgressReportFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'report_number',
        'report_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => ProgressReportStatus::class,
        'report_date' => 'immutable_date',
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
     * Get the total accomplished amount in cents.
     */
    public function totalAccomplished(): int
    {
        return (int) $this->items->sum('accomplished_amount');
    }

    /**
     * Get the company that owns the progress report.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the progress report.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the line items for the progress report.
     *
     * @return HasMany<ProgressItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProgressItem::class);
    }
}
