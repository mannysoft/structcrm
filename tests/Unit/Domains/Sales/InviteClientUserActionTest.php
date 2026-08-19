<?php

declare(strict_types=1);

namespace Tests\Unit\Domains\Sales;

use App\Domains\CompanyManagement\Models\Company;
use App\Domains\Sales\Actions\InviteClientUserAction;
use App\Domains\Sales\Models\Client;
use App\Domains\Sales\Notifications\ClientInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

test('it creates a client user and queues an invitation', function () {

    Notification::fake();

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    $user = app(InviteClientUserAction::class)->execute($client, 'jane@example.test');

    $this->assertTrue($user->isClient());
    $this->assertSame($client->id, $user->client_id);
    $this->assertSame($company->id, $user->company_id);
    $this->assertNull($user->email_verified_at);

    Notification::assertSentTo($user, ClientInvitation::class);
});

test('it promotes an existing user to client', function () {

    Notification::fake();

    $company = Company::factory()->create();
    $client = Client::factory()->create(['company_id' => $company->id]);

    $existing = User::factory()->create([
        'company_id' => $company->id,
        'email' => 'jane@example.test',
        'type' => User::TYPE_STAFF,
    ]);

    $user = app(InviteClientUserAction::class)->execute($client, 'jane@example.test');

    $this->assertSame($existing->id, $user->id);
    $this->assertTrue($user->isClient());
    $this->assertSame($client->id, $user->client_id);
});
