<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Requests;

class UpdateProjectRequest extends StoreProjectRequest
{
    public function authorize(): bool
    {
        $project = $this->route('project');

        return $this->user()?->can('update', $project) ?? false;
    }
}
