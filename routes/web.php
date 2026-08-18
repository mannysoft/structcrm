<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
});

// For now, protecting routes behind auth is commented out until Auth is fully scaffolded,
// but the Volt components are ready to be used.
// Volt::route('/projects', 'projects.index')->name('projects.index');
// Volt::route('/projects/create', 'projects.create')->name('projects.create');
