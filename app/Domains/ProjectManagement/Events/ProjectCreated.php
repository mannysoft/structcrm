<?php

declare(strict_types=1);

namespace App\Domains\ProjectManagement\Events;

use App\Domains\ProjectManagement\Models\Project;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProjectCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Project $project
    ) {}
}
