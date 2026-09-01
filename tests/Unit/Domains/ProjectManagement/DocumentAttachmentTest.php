<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\ProjectManagement;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Finance\Models\Billing;
use App\Domains\ProjectManagement\Models\Document;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\ChangeOrder;
use App\Domains\Sales\Models\Client;

test('documents can be attached to a change order and a billing', function () {

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create(['company_id' => $company->id, 'client_id' => $client->id]);
    $changeOrder = ChangeOrder::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);
    $billing = Billing::factory()->create(['company_id' => $company->id, 'project_id' => $project->id]);

    $document = Document::factory()->create([
        'company_id' => $company->id,
        'project_id' => $project->id,
        'change_order_id' => $changeOrder->id,
        'billing_id' => $billing->id,
    ]);

    $this->assertSame($changeOrder->id, $changeOrder->documents()->first()->id);
    $this->assertSame($billing->id, $billing->documents()->first()->id);
    $this->assertSame($changeOrder->id, $document->changeOrder->id);
    $this->assertSame($billing->id, $document->billing->id);
});
