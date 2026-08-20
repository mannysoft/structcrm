<?php

use App\Domains\CompanyManagement\Controllers\CompanyController;
use App\Domains\Finance\Controllers\BillingController;
use App\Domains\Finance\Controllers\BoqItemController;
use App\Domains\Finance\Controllers\BudgetController;
use App\Domains\Finance\Controllers\ContractController;
use App\Domains\Finance\Controllers\CostCodeController;
use App\Domains\Finance\Controllers\ProgressReportController;
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
use App\Http\Controllers\Auth\ApiTokenController;
use Illuminate\Support\Facades\Route;

Route::post('login', [ApiTokenController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('api.login');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::post('logout', [ApiTokenController::class, 'destroy'])->name('api.logout');

    Route::name('api.')->group(function () {
        Route::apiResource('companies', CompanyController::class);
        Route::apiResource('clients', ClientController::class);
        Route::apiResource('projects', ProjectController::class);
        Route::apiResource('tasks', TaskController::class);
        Route::apiResource('cost-codes', CostCodeController::class);
        Route::apiResource('contracts', ContractController::class);
        Route::apiResource('boq-items', BoqItemController::class);
        Route::apiResource('budgets', BudgetController::class);
        Route::apiResource('progress-reports', ProgressReportController::class);
        Route::apiResource('billings', BillingController::class);
        Route::post('billings/{billing}/payments', [BillingController::class, 'storePayment'])->name('api.billings.payments.store');
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
});
