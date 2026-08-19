<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\UpdateTaskAction;
use App\Domains\ProjectManagement\DTOs\TaskData;
use App\Domains\ProjectManagement\Enums\TaskStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Models\Client;

test('it updates a task', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $task = Task::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
    ]);

    $updated = app(UpdateTaskAction::class)->execute($task, new TaskData(
        title: 'Updated Task',
        projectId: $project->id,
        status: TaskStatus::Done,
    ));

    $this->assertSame('Updated Task', $updated->title);
    $this->assertSame(TaskStatus::Done, $updated->status);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task',
    ]);
});
