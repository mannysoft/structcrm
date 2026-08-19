<?php

declare(strict_types=1);

namespace App\Domains\Sales\Models;

use App\Domains\CompanyManagement\Models\Company;
use Database\Factories\ClientUserFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[UseFactory(ClientUserFactory::class)]
class ClientUser extends Authenticatable
{
    /** @use HasFactory<ClientUserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'email',
        'password',
        'invited_at',
        'email_verified_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'email_verified_at' => 'datetime',
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
     * Get the client that this portal user belongs to.
     *
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the company that this portal user belongs to.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
