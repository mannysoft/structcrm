<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\DuskTestCase;

/*
|--------------------------------------------------------------------------
| Dusk test case
|--------------------------------------------------------------------------
|
| Browser tests use Laravel Dusk's TestCase (which drives a real Chrome
| browser) and migrate a fresh database before the suite runs.
|
*/

uses(
    DuskTestCase::class,
    DatabaseMigrations::class,
)->in('Browser');
