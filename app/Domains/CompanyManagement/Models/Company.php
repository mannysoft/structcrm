<?php

declare(strict_types=1);

namespace App\Domains\CompanyManagement\Models;

use App\Domains\CompanyManagement\Enums\Currency;
use App\Domains\CompanyManagement\Observers\CompanyObserver;
use App\Models\User;
use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([CompanyObserver::class])]
#[UseFactory(CompanyFactory::class)]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'email',
        'phone',
        'address',
        'tax_number',
        'timezone',
        'currency',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'currency' => Currency::class,
        ];
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
     * Get the users that belong to the company.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
