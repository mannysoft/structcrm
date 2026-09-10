<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Livewire\Livewire;

test('registration screen can be rendered', function () {

    $response = $this->get('/register');

    $response
        ->assertOk()
        ->assertSeeLivewire('pages.auth.register');
});

test('new users can register', function () {

    $component = Livewire::test('pages.auth.register')
        ->set('company_name', 'Acme Builders')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $this->assertDatabaseHas('companies', [
        'name' => 'Acme Builders',
    ]);

    $this->assertNotNull(auth()->user()->company_id);
});

test('new users can opt in to marketing emails', function () {

    $component = Livewire::test('pages.auth.register')
        ->set('company_name', 'Acme Builders')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password')
        ->set('marketing_opt_in', true);

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->first();

    $this->assertTrue($user->marketing_opt_in);
    $this->assertNotNull($user->marketing_consented_at);
});

test('new users are not opted in to marketing by default', function () {

    $component = Livewire::test('pages.auth.register')
        ->set('company_name', 'Acme Builders')
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->set('password', 'password')
        ->set('password_confirmation', 'password');

    $component->call('register');

    $component->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->first();

    $this->assertFalse($user->marketing_opt_in);
    $this->assertNull($user->marketing_consented_at);
});
