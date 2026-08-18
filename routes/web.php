<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Volt::route('projects', 'projects.index')->name('projects.index');
    Volt::route('projects/create', 'projects.create')->name('projects.create');
    Volt::route('projects/{project}/edit', 'projects.edit')->name('projects.edit');
    Volt::route('companies', 'companies.index')->name('companies.index');
    Volt::route('companies/settings', 'companies.settings')->name('companies.settings');
    Volt::route('leads', 'leads.index')->name('leads.index');
    Volt::route('leads/create', 'leads.create')->name('leads.create');
    Volt::route('clients', 'clients.index')->name('clients.index');
    Volt::route('clients/create', 'clients.create')->name('clients.create');
    Volt::route('clients/{client}/edit', 'clients.edit')->name('clients.edit');
    Volt::route('tasks', 'tasks.index')->name('tasks.index');
    Volt::route('tasks/create', 'tasks.create')->name('tasks.create');
    Volt::route('tasks/{task}/edit', 'tasks.edit')->name('tasks.edit');
    Volt::route('suppliers', 'suppliers.index')->name('suppliers.index');
    Volt::route('suppliers/create', 'suppliers.create')->name('suppliers.create');
    Volt::route('suppliers/{supplier}/edit', 'suppliers.edit')->name('suppliers.edit');
    Volt::route('expenses', 'expenses.index')->name('expenses.index');
    Volt::route('expenses/create', 'expenses.create')->name('expenses.create');
    Volt::route('expenses/{expense}/edit', 'expenses.edit')->name('expenses.edit');
    Volt::route('change-orders', 'change_orders.index')->name('change_orders.index');
    Volt::route('change-orders/create', 'change_orders.create')->name('change_orders.create');
    Volt::route('change-orders/{changeOrder}/edit', 'change_orders.edit')->name('change_orders.edit');
    Volt::route('purchase-orders', 'purchase_orders.index')->name('purchase_orders.index');
    Volt::route('purchase-orders/create', 'purchase_orders.create')->name('purchase_orders.create');
    Volt::route('purchase-orders/{purchaseOrder}/edit', 'purchase_orders.edit')->name('purchase_orders.edit');
    Volt::route('invoices', 'invoices.index')->name('invoices.index');
    Volt::route('invoices/create', 'invoices.create')->name('invoices.create');
    Volt::route('invoices/{invoice}/edit', 'invoices.edit')->name('invoices.edit');
});

require __DIR__.'/auth.php';
