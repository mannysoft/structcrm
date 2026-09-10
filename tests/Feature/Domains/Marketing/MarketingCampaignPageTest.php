<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Marketing;

use App\Domains\Marketing\Models\MarketingCampaign;
use App\Domains\Marketing\Models\MarketingCampaignStep;
use App\Models\User;
use Livewire\Livewire;

test('campaigns page requires authentication', function () {

    $this->get(route('marketing.campaigns.index'))->assertRedirect('/login');
});

test('owner can view the campaigns index', function () {

    $user = User::factory()->create();
    MarketingCampaign::factory()->create(['name' => 'Onboarding']);

    $this->actingAs($user)
        ->get(route('marketing.campaigns.index'))
        ->assertOk()
        ->assertSee('Onboarding');
});

test('staff cannot view the campaigns index', function () {

    $staff = User::factory()->role('staff')->create();

    $this->actingAs($staff)
        ->get(route('marketing.campaigns.index'))
        ->assertForbidden();
});

test('staff cannot access campaign management pages', function () {

    $staff = User::factory()->role('staff')->create();

    $this->actingAs($staff)
        ->get(route('marketing.campaigns.create'))
        ->assertForbidden();
});

test('owner can create a campaign with steps', function () {

    $user = User::factory()->create();

    $this->actingAs($user);

    $component = Livewire::test('marketing.campaigns.create')
        ->set('name', 'Onboarding')
        ->set('is_active', true)
        ->set('steps', [
            ['id' => null, 'subject' => 'Welcome', 'delay_days' => 0, 'body' => '<p>Hello {name}</p>'],
            ['id' => null, 'subject' => 'Check-in', 'delay_days' => 3, 'body' => '<p>Hello again</p>'],
        ]);

    $component->call('save');

    $component->assertRedirect(route('marketing.campaigns.index'));

    $campaign = MarketingCampaign::where('name', 'Onboarding')->first();

    $this->assertNotNull($campaign);
    $this->assertCount(2, $campaign->steps);
    $this->assertTrue($campaign->is_active);
});

test('owner can update a campaign and its steps', function () {

    $user = User::factory()->create();

    $campaign = MarketingCampaign::factory()->create(['name' => 'Old name']);
    $step = MarketingCampaignStep::factory()->create([
        'campaign_id' => $campaign->id,
        'subject' => 'Old subject',
        'delay_days' => 1,
    ]);

    $this->actingAs($user);

    $component = Livewire::test('marketing.campaigns.edit', ['campaign' => $campaign])
        ->set('name', 'New name')
        ->set('steps', [
            ['id' => $step->id, 'subject' => 'New subject', 'delay_days' => 5, 'body' => '<p>Updated</p>'],
        ]);

    $component->call('save');

    $component->assertRedirect(route('marketing.campaigns.index'));

    $campaign->refresh();

    $this->assertSame('New name', $campaign->name);
    $this->assertCount(1, $campaign->steps);
    $this->assertSame('New subject', $campaign->steps->first()->subject);
    $this->assertSame(5, $campaign->steps->first()->delay_days);
});

test('owner can delete a campaign', function () {

    $user = User::factory()->create();
    $campaign = MarketingCampaign::factory()->create();

    $this->actingAs($user);

    Livewire::test('marketing.campaigns.index')
        ->call('delete', $campaign->uuid);

    $this->assertModelMissing($campaign);
});
