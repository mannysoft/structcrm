<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Agents\CrmAssistant;
use App\Ai\Tools\Projects\CreateProject;
use App\Ai\Tools\Projects\GetProject;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Models\User;
use Laravel\Ai\Contracts\Approvable;
use Laravel\Ai\Tools\Request;

test('tools cannot read records from another company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();

    $user = User::factory()->create(['company_id' => $company->id]);
    $foreignClient = Client::factory()->create(['company_id' => $otherCompany->id]);
    $foreignProject = Project::factory()->create([
        'company_id' => $otherCompany->id,
        'client_id' => $foreignClient->id,
    ]);

    $result = json_decode(
        (string) (new GetProject($user))->handle(new Request(['project' => $foreignProject->uuid])),
        true,
    );

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toContain('No project matching');
});

test('users without the module permission cannot create records', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $staff = User::factory()->role('staff')->create(['company_id' => $company->id]);

    $result = json_decode(
        (string) (new CreateProject($staff))->handle(new Request([
            'title' => 'Blocked project',
            'client' => $client->uuid,
            'budget' => 1000,
        ])),
        true,
    );

    expect($result['ok'])->toBeFalse()
        ->and($result['error'])->toContain('permission');
});

test('destructive tools require human approval', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $approvable = collect((new CrmAssistant($user))->tools())
        ->filter(fn ($tool) => $tool instanceof Approvable);

    expect($approvable)->not->toBeEmpty();

    foreach ($approvable as $tool) {
        expect($tool->shouldRequestApproval(new Request))->not->toBeNull(class_basename($tool));
    }
});

test('the assistant cannot be asked to act without a company', function () {
    $user = new User(['name' => 'No Company', 'email' => 'none@example.test']);

    $result = json_decode(
        (string) (new GetProject($user))->handle(new Request(['project' => 'missing'])),
        true,
    );

    expect($result['ok'])->toBeFalse();
});

test('every approved mutation tool is listed on the agent', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $approvable = collect((new CrmAssistant($user))->tools())
        ->filter(fn ($tool) => $tool instanceof Approvable);

    expect($approvable->count())->toBeGreaterThanOrEqual(10);
});
