<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\PhotoCategory;
use App\Models\User;
use Database\Factories\ProjectPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(ProjectPhotoFactory::class)]
class ProjectPhoto extends Model
{
    /** @use HasFactory<ProjectPhotoFactory> */
    use HasFactory, HasUuids, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'company_id',
        'project_id',
        'user_id',
        'category',
        'file_path',
        'caption',
    ];

    protected $casts = [
        'category' => PhotoCategory::class,
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
     * Get the company that owns the photo.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the photo.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user that uploaded the photo.
     *
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
