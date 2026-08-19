<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Events\ProjectCreated;
use App\Domains\Sales\Models\Client;
use Illuminate\Support\Facades\Event;

test('create project action creates project and dispatches event', function () {

    Event::fake([ProjectCreated::class]);

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    $action = app(CreateProjectAction::class);

    $dto = new ProjectData(
        title: 'New Tower Build',
        clientId: $client->id,
        status: ProjectStatus::Draft,
        budget: Money::fromDecimal(250000.00),
    );

    $project = $action->execute($dto, $company->id);

    $this->assertSame('New Tower Build', $project->title);
    $this->assertSame($client->id, $project->client_id);
    $this->assertSame(ProjectStatus::Draft, $project->status);
    $this->assertSame(25000000, $project->budget_amount);
    $this->assertNotNull($project->project_number);

    Event::assertDispatched(ProjectCreated::class, function (ProjectCreated $event) use ($project): bool {
        return $event->project->id === $project->id;
    });
});
