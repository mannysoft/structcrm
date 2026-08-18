<?php

use App\Domains\CompanyManagement\Controllers\CompanyController;
use App\Domains\ProjectManagement\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

// Note: these routes are intentionally not wrapped in auth middleware yet.
// Add Sanctum/session authentication once Breeze is scaffolded.
Route::apiResource('companies', CompanyController::class);
Route::post('projects', [ProjectController::class, 'store']);
