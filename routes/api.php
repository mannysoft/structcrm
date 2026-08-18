<?php

use App\Domains\CompanyManagement\Controllers\CompanyController;
use App\Domains\ProjectManagement\Controllers\ProjectController;
use App\Domains\Sales\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

// Note: these routes are intentionally not wrapped in auth middleware yet.
// Add Sanctum/session authentication once Breeze is scaffolded.
Route::name('api.')->group(function () {
    Route::apiResource('companies', CompanyController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('projects', ProjectController::class);
});
