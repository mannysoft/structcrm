<?php

declare(strict_types=1);

namespace Tests\Feature\Domains\Marketing;

use App\Domains\Marketing\Jobs\SyncResendContactJob;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Volt;

test('signing up with marketing opt-in queues a Resend contact sync', function () {

    Queue::fake();

    $component = Volt::test('pages.auth.register')
        ->set('company_name', 'Acme Builders')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('marketing_opt_in', true);

    $component->call('register');

    $user = User::where('email', 'test@example.com')->first();

    Queue::assertPushed(SyncResendContactJob::class, fn ($job) => $job->userId === $user->id);
});

test('signing up without marketing opt-in does not queue a Resend sync', function () {

    Queue::fake();

    $component = Volt::test('pages.auth.register')
        ->set('company_name', 'Acme Builders')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    Queue::assertNotPushed(SyncResendContactJob::class);
});

test('unsubscribing queues a Resend contact sync', function () {

    Queue::fake();

    $user = User::factory()->marketingOptIn()->create();

    $url = URL::signedRoute('marketing.unsubscribe', ['user' => $user->id]);

    $this->get($url)->assertOk();

    Queue::assertPushed(SyncResendContactJob::class, fn ($job) => $job->userId === $user->id);
});
