<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Models;

use App\Common\Traits\BelongsToTenant;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Enums\DocumentCategory;
use Database\Factories\DocumentFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(DocumentFactory::class)]
class Document extends Model
{
    /** @use HasFactory<DocumentFactory> */
    use BelongsToTenant, HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'category',
        'file_path',
    ];

    protected $casts = [
        'category' => DocumentCategory::class,
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
     * Get the company that owns the document.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the project that owns the document.
     *
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
