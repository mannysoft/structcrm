<?php

declare(strict_types=1);

use App\Domains\ProjectManagement\Actions\CreateProjectAction;
use App\Domains\ProjectManagement\DTOs\ProjectData;
use App\Domains\ProjectManagement\Enums\ProjectStatus;
use App\Common\ValueObjects\Money;
use App\Domains\ProjectManagement\Events\ProjectCreated;
use Illuminate\Support\Facades\Event;

uses(Tests\TestCase::class);

test('create project action creates project and dispatches event', function () {
    Event::fake();

    $action = app(CreateProjectAction::class);
    
    $dto = new ProjectData(
        name: 'New Tower Build',
        status: ProjectStatus::Draft,
        budget: Money::fromDecimal(250000.00),
    );

    $companyId = 1; // Assuming company exists in DB

    $project = $action->execute($dto, $companyId);

    expect($project->name)->toBe('New Tower Build')
        ->and($project->status)->toBe(ProjectStatus::Draft)
        ->and($project->budget_amount)->toBe(25000000); // In cents

    Event::assertDispatched(ProjectCreated::class, function ($event) use ($project) {
        return $event->project->id === $project->id;
    });
});
