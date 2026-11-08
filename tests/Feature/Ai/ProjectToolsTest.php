<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Projects\CreateDailyLog;
use App\Ai\Tools\Projects\CreateDocument;
use App\Ai\Tools\Projects\CreateEquipment;
use App\Ai\Tools\Projects\CreateMaintenanceLog;
use App\Ai\Tools\Projects\CreateProject;
use App\Ai\Tools\Projects\CreateProjectPhoto;
use App\Ai\Tools\Projects\CreateTask;
use App\Ai\Tools\Projects\CreateTimeEntry;
use App\Ai\Tools\Projects\DeleteDailyLog;
use App\Ai\Tools\Projects\DeleteDocument;
use App\Ai\Tools\Projects\DeleteEquipment;
use App\Ai\Tools\Projects\DeleteMaintenanceLog;
use App\Ai\Tools\Projects\DeleteProjectPhoto;
use App\Ai\Tools\Projects\DeleteTimeEntry;
use App\Ai\Tools\Projects\GetDailyLog;
use App\Ai\Tools\Projects\GetProject;
use App\Ai\Tools\Projects\ListTasks;
use App\Ai\Tools\Projects\UpdateDailyLog;
use App\Ai\Tools\Projects\UpdateDocument;
use App\Ai\Tools\Projects\UpdateMaintenanceLog;
use App\Ai\Tools\Projects\UpdateProjectPhoto;
use App\Ai\Tools\Projects\UpdateTask;
use App\Ai\Tools\Projects\UpdateTimeEntry;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\DailyLog;
use App\Domains\ProjectManagement\Models\Document;
use App\Domains\ProjectManagement\Models\Equipment;
use App\Domains\ProjectManagement\Models\MaintenanceLog;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\ProjectManagement\Models\ProjectPhoto;
use App\Domains\ProjectManagement\Models\Task;
use App\Domains\ProjectManagement\Models\TimeEntry;
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

test('the assistant can update and delete daily logs, time entries and documents', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $client = Client::factory()->create(['company_id' => $company->id]);
    $owner = User::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $log = json_decode(
        (string) (new CreateDailyLog($owner))->handle(new Request([
            'project' => $project->uuid,
            'log_date' => '2026-11-05',
            'work_performed' => 'Slab poured.',
            'crew_count' => 8,
        ])),
        true,
    );

    $logUuid = $log['data']['log']['uuid'];

    $updatedLog = json_decode(
        (string) (new UpdateDailyLog($owner))->handle(new Request([
            'log' => $logUuid,
            'crew_count' => 15,
            'issues' => 'Concrete delivery delayed.',
        ])),
        true,
    );

    expect($updatedLog['ok'])->toBeTrue()
        ->and($updatedLog['data']['log']['crew_count'])->toBe(15)
        ->and($updatedLog['data']['log']['issues'])->toBe('Concrete delivery delayed.');

    $detail = json_decode((string) (new GetDailyLog($owner))->handle(new Request(['log' => $logUuid])), true);

    expect($detail['ok'])->toBeTrue()
        ->and($detail['data']['log']['work_performed'])->toBe('Slab poured.');

    $entry = json_decode(
        (string) (new CreateTimeEntry($owner))->handle(new Request([
            'project' => $project->uuid,
            'clock_in_at' => '2026-11-05 07:00',
        ])),
        true,
    );

    $updatedEntry = json_decode(
        (string) (new UpdateTimeEntry($owner))->handle(new Request([
            'entry' => $entry['data']['entry']['uuid'],
            'clock_out_at' => '2026-11-05 16:30',
            'break_minutes' => 45,
        ])),
        true,
    );

    expect($updatedEntry['ok'])->toBeTrue()
        ->and($updatedEntry['data']['entry']['break_minutes'])->toBe(45)
        ->and($updatedEntry['data']['entry']['clock_out_at'])->toContain('16:30');

    $document = json_decode(
        (string) (new CreateDocument($owner))->handle(new Request([
            'project' => $project->uuid,
            'name' => 'Ground floor plan',
            'category' => 'blueprint',
            'file_path' => 'documents/plan.pdf',
        ])),
        true,
    );

    $updatedDocument = json_decode(
        (string) (new UpdateDocument($owner))->handle(new Request([
            'document' => $document['data']['document']['uuid'],
            'name' => 'Ground floor plan rev B',
        ])),
        true,
    );

    expect($updatedDocument['ok'])->toBeTrue()
        ->and($updatedDocument['data']['document']['name'])->toBe('Ground floor plan rev B');

    $deletedDocument = json_decode(
        (string) (new DeleteDocument($owner))->handle(new Request(['document' => $updatedDocument['data']['document']['uuid']])),
        true,
    );
    $deletedEntry = json_decode(
        (string) (new DeleteTimeEntry($owner))->handle(new Request(['entry' => $entry['data']['entry']['uuid']])),
        true,
    );
    $deletedLog = json_decode(
        (string) (new DeleteDailyLog($owner))->handle(new Request(['log' => $logUuid])),
        true,
    );

    expect($deletedDocument['ok'])->toBeTrue()
        ->and($deletedEntry['ok'])->toBeTrue()
        ->and($deletedLog['ok'])->toBeTrue()
        ->and(Document::query()->count())->toBe(0)
        ->and(TimeEntry::query()->count())->toBe(0)
        ->and(DailyLog::query()->count())->toBe(0);
});

test('the assistant can manage project photos, equipment and maintenance logs', function () {
    $company = Company::factory()->create(['currency' => 'PHP']);
    $client = Client::factory()->create(['company_id' => $company->id]);
    $owner = User::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $photo = json_decode(
        (string) (new CreateProjectPhoto($owner))->handle(new Request([
            'project' => $project->uuid,
            'category' => 'during',
            'file_path' => 'photos/slab.jpg',
            'caption' => 'Slab pour',
        ])),
        true,
    );

    expect($photo['ok'])->toBeTrue()
        ->and($photo['data']['photo']['uploaded_by'])->toBe($owner->name);

    $updatedPhoto = json_decode(
        (string) (new UpdateProjectPhoto($owner))->handle(new Request([
            'photo' => $photo['data']['photo']['uuid'],
            'category' => 'after',
            'caption' => 'Slab cured',
        ])),
        true,
    );

    expect($updatedPhoto['ok'])->toBeTrue()
        ->and($updatedPhoto['data']['photo']['category'])->toBe('after')
        ->and($updatedPhoto['data']['photo']['caption'])->toBe('Slab cured');

    $equipment = json_decode(
        (string) (new CreateEquipment($owner))->handle(new Request([
            'name' => 'Excavator 320',
            'type' => 'heavy_machinery',
            'service_due_at' => '2026-12-01',
        ])),
        true,
    );

    $log = json_decode(
        (string) (new CreateMaintenanceLog($owner))->handle(new Request([
            'equipment' => $equipment['data']['equipment']['uuid'],
            'service_date' => '2026-11-06',
            'description' => 'Hydraulic hose replaced.',
            'cost' => 1250.5,
        ])),
        true,
    );

    $updatedLog = json_decode(
        (string) (new UpdateMaintenanceLog($owner))->handle(new Request([
            'log' => $log['data']['log']['uuid'],
            'cost' => 1500,
            'description' => 'Hydraulic hose and pump seal replaced.',
        ])),
        true,
    );

    expect($updatedLog['ok'])->toBeTrue()
        ->and($updatedLog['data']['log']['cost'])->toEqual(1500);

    $deletedPhoto = json_decode(
        (string) (new DeleteProjectPhoto($owner))->handle(new Request(['photo' => $photo['data']['photo']['uuid']])),
        true,
    );
    $deletedLog = json_decode(
        (string) (new DeleteMaintenanceLog($owner))->handle(new Request(['log' => $log['data']['log']['uuid']])),
        true,
    );
    $deletedEquipment = json_decode(
        (string) (new DeleteEquipment($owner))->handle(new Request(['equipment' => $equipment['data']['equipment']['uuid']])),
        true,
    );

    expect($deletedPhoto['ok'])->toBeTrue()
        ->and($deletedLog['ok'])->toBeTrue()
        ->and($deletedEquipment['ok'])->toBeTrue()
        ->and(ProjectPhoto::query()->count())->toBe(0)
        ->and(MaintenanceLog::query()->count())->toBe(0)
        ->and(Equipment::query()->count())->toBe(0);
});
