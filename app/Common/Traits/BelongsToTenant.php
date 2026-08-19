<?php

declare(strict_types=1);

namespace App\Common\Traits;

use App\Common\Scopes\TenantScope;

trait BelongsToTenant
{
    /**
     * Boot the BelongsToTenant trait for a model.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (auth()->check() && ! $model->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }
}
