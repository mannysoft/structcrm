<?php

declare(strict_types=1);

use App\Ai\Agents\CrmAssistant;
use App\Domains\CompanyManagement\Models\Company;
use App\Models\User;
use Livewire\Livewire;

test('a staff user can ask the assistant a question through the chat page', function () {
    CrmAssistant::fake(['Hello from the component.']);

    $company = Company::factory()->create();
    $user = User::factory()->create(['company_id' => $company->id]);

    $this->actingAs($user);

    Livewire::test('assistant.index')
        ->set('prompt', 'How are we doing?')
        ->call('send')
        ->assertSet('prompt', '')
        ->assertSee('Hello from the component.');
});
