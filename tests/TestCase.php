<?php

namespace Tests;

use App\Models\Central\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

abstract class TenantTestCase extends TestCase
{
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    protected function tearDown(): void
    {
        $this->tearDownTenant();
        parent::tearDown();
    }

    protected function setUpTenant(): void
    {
        try {
            \Illuminate\Support\Facades\DB::connection('central')->getPdo();
        } catch (\Throwable $e) {
            $this->markTestSkipped('Tenant tests require a configured central DB: ' . $e->getMessage());
        }

        $subdomain = 'test' . uniqid();

        // Tenant::create() fires CreateDatabase + MigrateDatabase via the pipeline.
        $this->tenant = Tenant::create([
            'id'        => $subdomain,
            'name'      => 'Test School ' . $subdomain,
            'subdomain' => $subdomain,
        ]);
        $appHost = preg_replace('/^www\./i', '', parse_url(config('app.url'), PHP_URL_HOST) ?? 'localhost');
        $this->tenant->domains()->create(['domain' => $subdomain . '.' . $appHost]);

        tenancy()->initialize($this->tenant);
    }

    protected function tearDownTenant(): void
    {
        if (tenancy()->initialized) {
            tenancy()->end();
        }

        if (isset($this->tenant)) {
            $this->tenant->delete();
        }
    }
}
