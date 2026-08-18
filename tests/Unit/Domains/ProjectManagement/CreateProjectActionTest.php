<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Actions\CreateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Domains\ProjectManagement\Events\ProjectCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CreateProjectActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_project_action_creates_project_and_dispatches_event(): void
    {
        Event::fake();

        $company = Company::factory()->create();

        $action = app(CreateProjectAction::class);

        $dto = new ProjectData(
            name: 'New Tower Build',
            status: ProjectStatus::Draft,
            budget: Money::fromDecimal(250000.00),
        );

        $project = $action->execute($dto, $company->id);

        $this->assertSame('New Tower Build', $project->name);
        $this->assertSame(ProjectStatus::Draft, $project->status);
        $this->assertSame(25000000, $project->budget_amount);

        Event::assertDispatched(ProjectCreated::class, function (ProjectCreated $event) use ($project): bool {
            return $event->project->id === $project->id;
        });
    }
}
