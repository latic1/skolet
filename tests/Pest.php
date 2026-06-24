<?php

use Tests\TenantTestCase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

uses(TestCase::class)->in('Feature/Central', 'Unit');

uses(TenantTestCase::class)->in('Feature/Tenant');
