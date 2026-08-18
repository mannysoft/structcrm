<?php

use App\Domains\CompanyManagement\Controllers\CompanyController;
use App\Domains\ProjectManagement\Controllers\DailyLogController;
use App\Domains\ProjectManagement\Controllers\DocumentController;
use App\Domains\ProjectManagement\Controllers\EquipmentController;
use App\Domains\ProjectManagement\Controllers\MaintenanceLogController;
use App\Domains\ProjectManagement\Controllers\ProjectController;
use App\Domains\ProjectManagement\Controllers\ProjectPhotoController;
use App\Domains\ProjectManagement\Controllers\TaskController;
use App\Domains\ProjectManagement\Controllers\TimeEntryController;
use App\Domains\Sales\Controllers\ChangeOrderController;
use App\Domains\Sales\Controllers\ClientController;
use App\Domains\Sales\Controllers\EstimateController;
use App\Domains\Sales\Controllers\ExpenseController;
use App\Domains\Sales\Controllers\InvoiceController;
use App\Domains\Sales\Controllers\LeadController;
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
    Route::apiResource('daily-logs', DailyLogController::class);
    Route::apiResource('project-photos', ProjectPhotoController::class);
    Route::apiResource('documents', DocumentController::class);
    Route::apiResource('time-entries', TimeEntryController::class);
    Route::apiResource('equipment', EquipmentController::class);
    Route::apiResource('maintenance-logs', MaintenanceLogController::class);
    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('change-orders', ChangeOrderController::class);
    Route::apiResource('purchase-orders', PurchaseOrderController::class);
    Route::apiResource('invoices', InvoiceController::class);
    Route::apiResource('estimates', EstimateController::class);
    Route::apiResource('leads', LeadController::class);
});
