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
    Volt::route('companies', 'companies.index')->name('companies.index');
    Volt::route('companies/settings', 'companies.settings')->name('companies.settings');
    Volt::route('leads', 'leads.index')->name('leads.index');
    Volt::route('leads/create', 'leads.create')->name('leads.create');
});

require __DIR__.'/auth.php';
