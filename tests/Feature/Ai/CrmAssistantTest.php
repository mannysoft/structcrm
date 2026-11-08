<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Agents\CrmAssistant;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Contracts\Tool;

test('the assistant exposes the full tool suite', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $tools = (new CrmAssistant($user))->tools();

    expect($tools)->toHaveCount(78)
        ->and($tools)->each->toBeInstanceOf(Tool::class);
});

test('every tool exposes a valid schema', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $schema = new JsonSchemaTypeFactory;

    foreach ((new CrmAssistant($user))->tools() as $tool) {
        expect(fn () => $tool->schema($schema))->not->toThrow(Exception::class);
    }
});

test('the agent instructions include company and user context', function () {
    $company = Company::factory()->create(['name' => 'Isles Builders', 'currency' => 'PHP']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'name' => 'Manny Isles',
        'email' => 'manny@example.test',
    ]);

    $instructions = (string) (new CrmAssistant($user))->instructions();

    expect($instructions)
        ->toContain('Isles Builders')
        ->toContain('manny@example.test')
        ->toContain('PHP')
        ->toContain((string) $company->id);
});

test('tools are scoped to the acting user', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    foreach ((new CrmAssistant($user))->tools() as $tool) {
        expect($tool->user->is($user))->toBeTrue();
    }
});
