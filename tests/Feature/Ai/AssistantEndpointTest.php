<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Agents\CrmAssistant;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Laravel\Ai\Models\Conversation;

test('the assistant endpoint requires authentication', function () {
    $this->postJson('/api/assistant', ['prompt' => 'Hello'])
        ->assertUnauthorized();
});

test('the assistant endpoint returns a faked response and stores the conversation', function () {
    CrmAssistant::fake(['Here is your answer.']);

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $response = $this->actingAs($user)
        ->postJson('/api/assistant', ['prompt' => 'How many projects do we have?'])
        ->assertOk()
        ->assertJsonPath('message', 'Here is your answer.')
        ->assertJsonCount(0, 'pending_approvals');

    expect($response->json('conversation_id'))->not->toBeNull()
        ->and(Conversation::query()->count())->toBe(1);

    CrmAssistant::assertPrompted('How many projects do we have?');
});

test('submitting approval decisions requires a conversation', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user)
        ->postJson('/api/assistant', ['decisions' => ['call_123' => true]])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['conversation_id']);
});

test('the assistant endpoint rejects conversations owned by another user', function () {
    CrmAssistant::fake(['Nope.']);

    $company = Company::factory()->create();
    $owner = User::factory()->create(['company_id' => $company->id]);
    $intruder = User::factory()->create(['company_id' => $company->id]);

    $conversation = $owner->conversations()->create([
        'id' => (string) str()->uuid7(),
        'title' => 'Private conversation',
    ]);

    $this->actingAs($intruder)
        ->postJson('/api/assistant', [
            'prompt' => 'Read this',
            'conversation_id' => $conversation->id,
        ])
        ->assertNotFound();
});

test('client portal users cannot use the assistant endpoint', function () {
    $company = Company::factory()->create();
    $clientUser = User::factory()->client()->create(['company_id' => $company->id]);

    $this->actingAs($clientUser)
        ->postJson('/api/assistant', ['prompt' => 'Hello'])
        ->assertForbidden();
});
