<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The base TestCase is applied to every test, and the database is refreshed
| and migrated before each test. Roles/permissions are seeded by the base
| TestCase's setUp().
|
*/

uses(
    Tests\TestCase::class,
    RefreshDatabase::class,
)->in('Feature', 'Unit');
