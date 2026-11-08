<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Tools\Sales\CreateClient;
use App\Ai\Tools\Sales\CreateInvoice;
use App\Ai\Tools\Sales\CreateLead;
use App\Ai\Tools\Sales\ListClients;
use App\Ai\Tools\Sales\UpdateInvoiceStatus;
use App\Ai\Tools\Sales\UpdateLead;
use App\Domains\CompanyManagement\Models\Company;
use App\Domains\ProjectManagement\Models\Project;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Models\Invoice;
use App\Models\User;
use Laravel\Ai\Tools\Request;

test('the assistant can create and list clients', function () {
    $company = Company::factory()->create();
    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateClient($user))->handle(new Request([
            'name' => 'Jane Doe',
            'company_name' => 'Doe Holdings',
            'email' => 'jane@example.test',
        ])),
        true,
    );

    expect($created['ok'])->toBeTrue()
        ->and($created['data']['client']['name'])->toBe('Jane Doe');

    $listed = json_decode(
        (string) (new ListClients($user))->handle(new Request(['search' => 'Doe'])),
        true,
    );

    expect($listed['data']['clients'])->toHaveCount(1)
        ->and($listed['data']['clients'][0]['company_name'])->toBe('Doe Holdings');
});

test('the assistant can move a lead through the pipeline', function () {
    $company = Company::factory()->create();
    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateLead($user))->handle(new Request([
            'first_name' => 'Ana',
            'last_name' => 'Cruz',
            'email' => 'ana@example.test',
        ])),
        true,
    );

    expect($created['ok'])->toBeTrue()
        ->and($created['data']['lead']['status'])->toBe('new');

    $updated = json_decode(
        (string) (new UpdateLead($user))->handle(new Request([
            'lead' => $created['data']['lead']['uuid'],
            'status' => 'qualified',
        ])),
        true,
    );

    expect($updated['ok'])->toBeTrue()
        ->and($updated['data']['lead']['status'])->toBe('qualified');
});

test('the assistant can create an invoice and mark it paid', function () {
    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'client_id' => $client->id,
    ]);
    $accountant = User::factory()->role('accountant')->create(['company_id' => $company->id]);

    $created = json_decode(
        (string) (new CreateInvoice($accountant))->handle(new Request([
            'project' => $project->uuid,
            'status' => 'sent',
            'items' => [
                ['description' => 'Concrete works', 'quantity' => 2, 'unit_price' => 1500],
            ],
        ])),
        true,
    );

    expect($created['ok'])->toBeTrue();

    $invoice = Invoice::query()->where('uuid', $created['data']['invoice']['uuid'])->firstOrFail();

    expect($invoice->total)->toBe(300_000);

    $updated = json_decode(
        (string) (new UpdateInvoiceStatus($accountant))->handle(new Request([
            'invoice' => $invoice->invoice_number,
            'status' => 'paid',
        ])),
        true,
    );

    expect($updated['ok'])->toBeTrue()
        ->and($updated['data']['invoice']['status'])->toBe('paid');
});

test('sales tools reject records belonging to another company', function () {
    $company = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $foreignClient = Client::factory()->create(['company_id' => $otherCompany->id]);

    $user = User::factory()->role('project_manager')->create(['company_id' => $company->id]);

    $result = json_decode(
        (string) (new CreateInvoice($user))->handle(new Request([
            'project' => $foreignClient->uuid,
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ])),
        true,
    );

    expect($result['ok'])->toBeFalse();
});
