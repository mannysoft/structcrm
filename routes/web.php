<?php

use App\Http\Controllers\Auth\ClientAuthenticatedSessionController;
use App\Domains\Finance\Controllers\BoqImportController;
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
    Volt::route('team', 'team.index')->name('team.index');
    Volt::route('leads', 'leads.index')->name('leads.index');
    Volt::route('leads/create', 'leads.create')->name('leads.create');
    Volt::route('leads/{lead}/edit', 'leads.edit')->name('leads.edit');
    Volt::route('clients', 'clients.index')->name('clients.index');
    Volt::route('clients/create', 'clients.create')->name('clients.create');
    Volt::route('clients/{client}/edit', 'clients.edit')->name('clients.edit');
    Volt::route('tasks', 'tasks.index')->name('tasks.index');
    Volt::route('tasks/create', 'tasks.create')->name('tasks.create');
    Volt::route('tasks/{task}/edit', 'tasks.edit')->name('tasks.edit');
    Volt::route('cost-codes', 'cost_codes.index')->name('cost_codes.index');
    Volt::route('cost-codes/create', 'cost_codes.create')->name('cost_codes.create');
    Volt::route('cost-codes/{costCode}/edit', 'cost_codes.edit')->name('cost_codes.edit');
    Volt::route('contracts', 'contracts.index')->name('contracts.index');
    Volt::route('contracts/create', 'contracts.create')->name('contracts.create');
    Volt::route('contracts/{contract}/edit', 'contracts.edit')->name('contracts.edit');
    Volt::route('boq-items', 'boq_items.index')->name('boq_items.index');
    Volt::route('boq-items/create', 'boq_items.create')->name('boq_items.create');
    Volt::route('boq-items/import', 'boq_items.import')->name('boq_items.import');
    Volt::route('boq-items/{boqItem}/edit', 'boq_items.edit')->name('boq_items.edit');
    Route::get('boq-items/template', [BoqImportController::class, 'template'])->name('boq_items.template');
    Volt::route('daily-logs', 'daily_logs.index')->name('daily_logs.index');
    Volt::route('daily-logs/create', 'daily_logs.create')->name('daily_logs.create');
    Volt::route('daily-logs/{dailyLog}/edit', 'daily_logs.edit')->name('daily_logs.edit');
    Volt::route('project-photos', 'project_photos.index')->name('project_photos.index');
    Volt::route('project-photos/create', 'project_photos.create')->name('project_photos.create');
    Volt::route('documents', 'documents.index')->name('documents.index');
    Volt::route('documents/create', 'documents.create')->name('documents.create');
    Volt::route('time-entries', 'time_entries.index')->name('time_entries.index');
    Volt::route('time-entries/create', 'time_entries.create')->name('time_entries.create');
    Volt::route('time-entries/{timeEntry}/edit', 'time_entries.edit')->name('time_entries.edit');
    Volt::route('equipment', 'equipment.index')->name('equipment.index');
    Volt::route('equipment/create', 'equipment.create')->name('equipment.create');
    Volt::route('equipment/{equipment}/edit', 'equipment.edit')->name('equipment.edit');
    Volt::route('maintenance-logs', 'maintenance_logs.index')->name('maintenance_logs.index');
    Volt::route('maintenance-logs/create', 'maintenance_logs.create')->name('maintenance_logs.create');
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
    Volt::route('estimates', 'estimates.index')->name('estimates.index');
    Volt::route('estimates/create', 'estimates.create')->name('estimates.create');
    Volt::route('estimates/{estimate}/edit', 'estimates.edit')->name('estimates.edit');
});

require __DIR__.'/auth.php';
// Client Portal — external client access (client guard)
Route::middleware('guest')->group(function () {
    Volt::route('portal/login', 'portal.login')->name('portal.login');
});

Volt::route('portal/set-password/{user}', 'portal.set-password')
    ->middleware('signed')
    ->name('portal.set-password');

Volt::route('staff/set-password/{user}', 'pages.auth.set-password')
    ->middleware('signed')
    ->name('staff.set-password');

Route::middleware('auth:client')->group(function () {
    Volt::route('portal', 'portal.index')->name('portal.index');
    Route::post('portal/logout', [ClientAuthenticatedSessionController::class, 'destroy'])
        ->name('portal.logout');
});
