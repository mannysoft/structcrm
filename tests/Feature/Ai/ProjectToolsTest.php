<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Projects\CreateDailyLog;
use App\Ai\Tools\Projects\CreateProject;
use App\Ai\Tools\Projects\CreateTask;
use App\Ai\Tools\Projects\GetProject;
use App\Ai\Tools\Projects\ListTasks;
use App\Ai\Tools\Projects\UpdateTask;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Laravel\Ai\Tools\Request;

test('the assistant can create a project and a task on it', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $client = Client::factory()->create(['company_id' => $company->id, 'name' => 'Acme Build']);
    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $project = json_decode(
        (string) (new CreateProject($user))->handle(new Request([
            'title' => 'Harbour Tower',
            'client' => $client->uuid,
            'budget' => 2500000,
            'status' => 'active',
            'start_date' => '2026-11-01',
        ])),
        true,
    );

    expect($project['ok'])->toBeTrue()
        ->and($project['data']['project']['budget'])->toEqual(2500000)
        ->and($project['data']['project']['status'])->toBe('active');

    $projectUuid = $project['data']['project']['uuid'];

    $task = json_decode(
        (string) (new CreateTask($user))->handle(new Request([
            'title' => 'Pour foundation',
            'project' => $projectUuid,
            'priority' => 'high',
            'assignee' => $user->email,
            'due_date' => '2026-11-15',
        ])),
        true,
    );

    expect($task['ok'])->toBeTrue()
        ->and($task['data']['task']['priority'])->toBe('high')
        ->and($task['data']['task']['assignee'])->toBe($user->name);

    $updated = json_decode(
        (string) (new UpdateTask($user))->handle(new Request([
            'task' => $task['data']['task']['uuid'],
            'status' => 'done',
        ])),
        true,
    );

    expect($updated['ok'])->toBeTrue()
        ->and($updated['data']['task']['status'])->toBe('done');

    $listed = json_decode(
        (string) (new ListTasks($user))->handle(new Request(['project' => $projectUuid, 'status' => 'done'])),
        true,
    );

    expect($listed['data']['tasks'])->toHaveCount(1)
        ->and(Task::query()->count())->toBe(1);
});

test('the project detail tool reports tasks and profitability', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateProject($user))->handle(new Request([
            'title' => 'Warehouse Expansion',
            'client' => $client->uuid,
            'budget' => 100000,
        ])),
        true,
    );

    $detail = json_decode(
        (string) (new GetProject($user))->handle(new Request([
            'project' => $created['data']['project']['uuid'],
        ])),
        true,
    );

    expect($detail['ok'])->toBeTrue()
        ->and($detail['data']['project']['title'])->toBe('Warehouse Expansion')
        ->and($detail['data'])->toHaveKeys(['tasks', 'profitability', 'billing']);
});

test('site supervisors can record daily logs for their own projects', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $foreman = User::factory()->role('foreman')->create(['company_id' => $company->id]);

    $project = json_decode(
        (string) (new CreateProject(User::factory()->role('project_manager')->create(['company_id' => $company->id])))->handle(new Request([
            'title' => 'Road Works',
            'client' => $client->uuid,
            'budget' => 500000,
        ])),
        true,
    );

    $log = json_decode(
        (string) (new CreateDailyLog($foreman))->handle(new Request([
            'project' => $project['data']['project']['uuid'],
            'log_date' => '2026-11-05',
            'work_performed' => 'Subbase compaction completed.',
            'crew_count' => 12,
            'labor_hours' => 96,
        ])),
        true,
    );

    expect($log['ok'])->toBeTrue()
        ->and($log['data']['log']['crew_count'])->toBe(12)
        ->and(DailyLog::query()->count())->toBe(1);
});
