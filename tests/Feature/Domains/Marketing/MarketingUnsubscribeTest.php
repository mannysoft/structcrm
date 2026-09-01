<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Marketing;

use App\Models\User;
use Illuminate\Support\Facades\URL;

test('unsubscribing opts the user out of marketing emails', function () {

    $user = User::factory()->marketingOptIn(5)->create();

    $url = URL::signedRoute('marketing.unsubscribe', ['user' => $user->id]);

    $this->get($url)
        ->assertOk()
        ->assertSee('unsubscribed');

    $user->refresh();

    $this->assertFalse($user->marketing_opt_in);
    $this->assertNull($user->marketing_consented_at);
});

test('unsubscribe link requires a valid signature', function () {

    $user = User::factory()->marketingOptIn()->create();

    $this->get(route('marketing.unsubscribe', ['user' => $user->id]))
        ->assertForbidden();
});
