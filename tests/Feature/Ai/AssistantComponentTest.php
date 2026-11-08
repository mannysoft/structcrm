<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Ai\Agents\CrmAssistant;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Livewire\Livewire;

test('a staff user can ask the assistant a question through the chat component', function () {
    CrmAssistant::fake(['Hello from the component.']);

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test('assistant.chat')
        ->set('prompt', 'How are we doing?')
        ->call('send')
        ->assertSet('prompt', '')
        ->assertSee('Hello from the component.');
});

test('the drawer hosts the chat and a launcher', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test('assistant.drawer')
        ->assertSee('Your CRM copilot')
        ->assertSee('Open AI assistant')
        ->assertSee('Full page');
});

test('the ask-assistant event prefills the composer', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test('assistant.chat')
        ->dispatch('ask-assistant', prompt: 'Which invoices are overdue?')
        ->assertSet('prompt', 'Which invoices are overdue?');
});

test('starting a new conversation clears the transcript', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test('assistant.chat')
        ->set('prompt', 'Draft message')
        ->call('newConversation')
        ->assertSet('conversationId', null)
        ->assertSet('prompt', '')
        ->assertSet('messages', []);
});
