<?php

use Tests\DuskTestCase;

/*
|--------------------------------------------------------------------------
| Dusk test case
|--------------------------------------------------------------------------
|
| Browser tests use Laravel Dusk's TestCase (which drives a real Chrome
| browser). ChromeDriver is started once before the suite; the database is
| prepared separately (`php artisan migrate:fresh --seed` under the dusk env).
|
*/

uses(DuskTestCase::class)->in('Browser');

beforeAll(function () {
    DuskTestCase::prepare();
});

afterAll(function () {
    DuskTestCase::stopChromeDriver();
});
