<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\DecideChangeOrderAction;
use App\Domains\Sales\Enums\ChangeOrderStatus;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecideChangeOrderActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_approves_a_change_order(): void
    {
        $changeOrder = $this->makeChangeOrder(ChangeOrderStatus::Pending);

        app(DecideChangeOrderAction::class)->execute($changeOrder, ChangeOrderStatus::Approved);

        $changeOrder->refresh();

        $this->assertSame(ChangeOrderStatus::Approved, $changeOrder->status);
        $this->assertNotNull($changeOrder->approved_at);
    }

    public function test_it_rejects_a_change_order(): void
    {
        $changeOrder = $this->makeChangeOrder(ChangeOrderStatus::Pending);

        app(DecideChangeOrderAction::class)->execute($changeOrder, ChangeOrderStatus::Rejected);

        $changeOrder->refresh();

        $this->assertSame(ChangeOrderStatus::Rejected, $changeOrder->status);
        $this->assertNull($changeOrder->approved_at);
    }

    private function makeChangeOrder(ChangeOrderStatus $status): ChangeOrder
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        return ChangeOrder::factory()->create([
            'company_id' => $company->id,
            'project_id' => $project->id,
            'status' => $status,
        ]);
    }
}
