<?php

use App\Domains\CompanyManagement\Controllers\CompanyController;
use App\Domains\ProjectManagement\Controllers\ProjectController;
use App\Domains\ProjectManagement\Controllers\TaskController;
use App\Domains\Sales\Controllers\ChangeOrderController;
use App\Domains\Sales\Controllers\ClientController;
use App\Domains\Sales\Controllers\ExpenseController;
use App\Domains\Sales\Controllers\InvoiceController;
use App\Domains\Sales\Controllers\PurchaseOrderController;
use App\Domains\Sales\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Note: these routes are intentionally not wrapped in auth middleware yet.
// Add Sanctum/session authentication once Breeze is scaffolded.
Route::name('api.')->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('change-orders', ChangeOrderController::class);
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::apiResource('invoices', InvoiceController::class);
});
