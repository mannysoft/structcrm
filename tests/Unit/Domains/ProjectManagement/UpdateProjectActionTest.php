<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\UpdateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;

test('it updates a project', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);

    $updated = app(UpdateProjectAction::class)->execute($project, new ProjectData(
        title: 'Updated Title',
        clientId: $client->id,
        status: ProjectStatus::Active,
        budget: Money::fromDecimal(1000.00),
    ));

    $this->assertSame('Updated Title', $updated->title);
    $this->assertSame(ProjectStatus::Active, $updated->status);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'title' => 'Updated Title',
    ]);
});
