<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Common\ValueObjects\Money;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Actions\CreateExpenseAction;
use App\Domains\Sales\DTOs\ExpenseData;
use App\Domains\Sales\Enums\ExpenseCategory;
use App\Domains\Sales\Models\Client;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateExpenseActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_expense_scoped_to_a_company(): void
    {
        $company = Company::factory()->create();
        $client = Client::factory()->create(['company_id' => $company->id]);
        $project = Project::factory()->create([
            'company_id' => $company->id,
            'client_id' => $client->id,
        ]);

        $expense = app(CreateExpenseAction::class)->execute(new ExpenseData(
            projectId: $project->id,
            category: ExpenseCategory::Materials,
            amount: Money::fromDecimal(1500.50),
            date: CarbonImmutable::parse('2026-08-18'),
        ), $company->id);

        $this->assertSame($project->id, $expense->project_id);
        $this->assertSame(ExpenseCategory::Materials, $expense->category);
        $this->assertSame(150050, $expense->amount);

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'amount' => 150050,
        ]);
    }
}
