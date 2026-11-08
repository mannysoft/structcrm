<?php

use App\Domains\Finance\Controllers\BoqImportController;
use App\Http\Controllers\Auth\ClientAuthenticatedSessionController;
use App\Http\Controllers\MarketingUnsubscribeController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::livewire('dashboard', 'dashboard.index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::livewire('getting-started', 'getting_started.index')
    ->middleware('auth')
    ->name('getting_started.index');

Route::livewire('assistant', 'assistant.index')
    ->middleware(['auth', 'verified'])
    ->name('assistant.index');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::livewire('projects', 'projects.index')->name('projects.index');
    Route::livewire('projects/create', 'projects.create')->middleware('getting-started')->name('projects.create');
    Route::livewire('projects/{project}/edit', 'projects.edit')->name('projects.edit');
    Route::livewire('companies', 'companies.index')->name('companies.index');
    Route::livewire('companies/settings', 'companies.settings')->name('companies.settings');
    Route::livewire('team', 'team.index')->name('team.index');
    Route::livewire('leads', 'leads.index')->name('leads.index');
    Route::livewire('leads/create', 'leads.create')->name('leads.create');
    Route::livewire('leads/{lead}/edit', 'leads.edit')->name('leads.edit');
    Route::livewire('clients', 'clients.index')->name('clients.index');
    Route::livewire('clients/create', 'clients.create')->name('clients.create');
    Route::livewire('clients/{client}/edit', 'clients.edit')->name('clients.edit');
    Route::livewire('tasks', 'tasks.index')->name('tasks.index');
    Route::livewire('tasks/create', 'tasks.create')->middleware('getting-started')->name('tasks.create');
    Route::livewire('tasks/{task}/edit', 'tasks.edit')->name('tasks.edit');
    Route::livewire('cost-codes', 'cost_codes.index')->name('cost_codes.index');
    Route::livewire('cost-codes/create', 'cost_codes.create')->name('cost_codes.create');
    Route::livewire('cost-codes/{costCode}/edit', 'cost_codes.edit')->name('cost_codes.edit');
    Route::livewire('contracts', 'contracts.index')->name('contracts.index');
    Route::livewire('contracts/create', 'contracts.create')->middleware('getting-started')->name('contracts.create');
    Route::livewire('contracts/{contract}/edit', 'contracts.edit')->name('contracts.edit');
    Route::livewire('boq-items', 'boq_items.index')->name('boq_items.index');
    Route::livewire('boq-items/create', 'boq_items.create')->middleware('getting-started')->name('boq_items.create');
    Route::livewire('boq-items/import', 'boq_items.import')->middleware('getting-started')->name('boq_items.import');
    Route::livewire('boq-items/{boqItem}/edit', 'boq_items.edit')->name('boq_items.edit');
    Route::get('boq-items/template', [BoqImportController::class, 'template'])->name('boq_items.template');
    Route::livewire('budgets', 'budgets.index')->name('budgets.index');
    Route::livewire('budgets/create', 'budgets.create')->middleware('getting-started')->name('budgets.create');
    Route::livewire('budgets/{budget}/edit', 'budgets.edit')->name('budgets.edit');
    Route::livewire('progress-reports', 'progress_reports.index')->name('progress_reports.index');
    Route::livewire('progress-reports/create', 'progress_reports.create')->middleware('getting-started')->name('progress_reports.create');
    Route::livewire('progress-reports/{progressReport}/edit', 'progress_reports.edit')->name('progress_reports.edit');
    Route::livewire('billings', 'billings.index')->name('billings.index');
    Route::livewire('billings/create', 'billings.create')->middleware('getting-started')->name('billings.create');
    Route::livewire('billings/{billing}/edit', 'billings.edit')->name('billings.edit');
    Route::livewire('daily-logs', 'daily_logs.index')->name('daily_logs.index');
    Route::livewire('daily-logs/create', 'daily_logs.create')->middleware('getting-started')->name('daily_logs.create');
    Route::livewire('daily-logs/{dailyLog}/edit', 'daily_logs.edit')->name('daily_logs.edit');
    Route::livewire('project-photos', 'project_photos.index')->name('project_photos.index');
    Route::livewire('project-photos/create', 'project_photos.create')->middleware('getting-started')->name('project_photos.create');
    Route::livewire('documents', 'documents.index')->name('documents.index');
    Route::livewire('documents/create', 'documents.create')->middleware('getting-started')->name('documents.create');
    Route::livewire('time-entries', 'time_entries.index')->name('time_entries.index');
    Route::livewire('time-entries/create', 'time_entries.create')->middleware('getting-started')->name('time_entries.create');
    Route::livewire('time-entries/{timeEntry}/edit', 'time_entries.edit')->name('time_entries.edit');
    Route::livewire('equipment', 'equipment.index')->name('equipment.index');
    Route::livewire('equipment/create', 'equipment.create')->name('equipment.create');
    Route::livewire('equipment/{equipment}/edit', 'equipment.edit')->name('equipment.edit');
    Route::livewire('maintenance-logs', 'maintenance_logs.index')->name('maintenance_logs.index');
    Route::livewire('maintenance-logs/create', 'maintenance_logs.create')->name('maintenance_logs.create');
    Route::livewire('suppliers', 'suppliers.index')->name('suppliers.index');
    Route::livewire('suppliers/create', 'suppliers.create')->name('suppliers.create');
    Route::livewire('suppliers/{supplier}/edit', 'suppliers.edit')->name('suppliers.edit');
    Route::livewire('expenses', 'expenses.index')->name('expenses.index');
    Route::livewire('expenses/create', 'expenses.create')->middleware('getting-started')->name('expenses.create');
    Route::livewire('expenses/{expense}/edit', 'expenses.edit')->name('expenses.edit');
    Route::livewire('change-orders', 'change_orders.index')->name('change_orders.index');
    Route::livewire('change-orders/create', 'change_orders.create')->middleware('getting-started')->name('change_orders.create');
    Route::livewire('change-orders/{changeOrder}/edit', 'change_orders.edit')->name('change_orders.edit');
    Route::livewire('purchase-orders', 'purchase_orders.index')->name('purchase_orders.index');
    Route::livewire('purchase-orders/create', 'purchase_orders.create')->middleware('getting-started')->name('purchase_orders.create');
    Route::livewire('purchase-orders/{purchaseOrder}/edit', 'purchase_orders.edit')->name('purchase_orders.edit');
    Route::livewire('invoices', 'invoices.index')->name('invoices.index');
    Route::livewire('invoices/create', 'invoices.create')->middleware('getting-started')->name('invoices.create');
    Route::livewire('invoices/{invoice}/edit', 'invoices.edit')->name('invoices.edit');
    Route::livewire('estimates', 'estimates.index')->name('estimates.index');
    Route::livewire('estimates/create', 'estimates.create')->name('estimates.create');
    Route::livewire('estimates/{estimate}/edit', 'estimates.edit')->name('estimates.edit');

    Route::livewire('marketing/campaigns', 'marketing.campaigns.index')->name('marketing.campaigns.index');
    Route::livewire('marketing/campaigns/create', 'marketing.campaigns.create')->name('marketing.campaigns.create');
    Route::livewire('marketing/campaigns/{campaign}/edit', 'marketing.campaigns.edit')->name('marketing.campaigns.edit');
});

require __DIR__.'/auth.php';
// Client Portal — external client access (client guard)
Route::middleware('guest')->group(function () {
    Route::livewire('portal/login', 'portal.login')->name('portal.login');
});

Route::livewire('portal/set-password/{user}', 'portal.set-password')
    ->middleware('signed')
    ->name('portal.set-password');

Route::livewire('staff/set-password/{user}', 'pages.auth.set-password')
    ->middleware('signed')
    ->name('staff.set-password');

Route::get('marketing/unsubscribe/{user}', MarketingUnsubscribeController::class)
    ->middleware('signed')
    ->name('marketing.unsubscribe');

Route::middleware('auth:client')->group(function () {
    Route::livewire('portal', 'portal.index')->name('portal.index');
    Route::post('portal/logout', [ClientAuthenticatedSessionController::class, 'destroy'])
        ->name('portal.logout');
});
