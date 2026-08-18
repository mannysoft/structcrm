<?php

declare(strict_types=1);

namespace App\Domains\Sales\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\Sales\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
    ];
}
