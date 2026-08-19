<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Database\Factories\TimeEntryFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(TimeEntryFactory::class)]
class TimeEntry extends Model
{
    /** @use HasFactory<TimeEntryFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'user_id',
        'clock_in_at',
        'clock_out_at',
        'break_minutes',
        'notes',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'clock_in_at' => 'immutable_datetime',
        'clock_out_at' => 'immutable_datetime',
        'break_minutes' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
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
     * Whether the entry is still running (no clock-out recorded).
     */
    public function isRunning(): bool
    {
        return $this->clock_out_at === null;
    }

    /**
     * Total worked minutes (clock-out minus clock-in, less breaks).
     */
    public function durationMinutes(): ?int
    {
        if ($this->clock_out_at === null) {
            return null;
        }

        return max(0, (int) $this->clock_in_at->diffInMinutes($this->clock_out_at) - $this->break_minutes);
    }

    /**
     * Get the company that owns the time entry.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the time entry.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that logged the time entry.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
