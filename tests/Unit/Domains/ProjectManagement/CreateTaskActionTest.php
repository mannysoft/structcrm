<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateTaskAction;
use App\Domains\ProjectManagement\DTOs\TaskData;
use App\Domains\ProjectManagement\Enums\TaskPriority;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

test('it creates a task scoped to a company', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $task = app(CreateTaskAction::class)->execute(new TaskData(
        title: 'Pour foundation',
        projectId: $project->id,
        status: TaskStatus::InProgress,
        priority: TaskPriority::High,
    ), $company->id);

    $this->assertSame('Pour foundation', $task->title);
    $this->assertSame($project->id, $task->project_id);
    $this->assertSame(TaskStatus::InProgress, $task->status);
    $this->assertSame(TaskPriority::High, $task->priority);
    $this->assertNotNull($task->uuid);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Pour foundation',
    ]);
});
