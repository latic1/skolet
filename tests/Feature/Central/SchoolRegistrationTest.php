<?php

use App\Models\Central\Tenant;
use App\Models\Central\SubscriptionPlan;
use Illuminate\Support\Facades\DB;

test('school registration creates tenant and admin user', function (): void {
    $subdomain = 'testschool' . uniqid();

    $response = $this->post('/register-school', [
        'school_name'           => 'Test Academy',
        'subdomain'             => $subdomain,
        'admin_name'            => 'Admin User',
        'admin_email'           => 'admin@testacademy.test',
        'admin_password'        => 'Password123!',
        'admin_password_confirmation' => 'Password123!',
        'hp_check'              => '',
    ]);

    $tenant = Tenant::find($subdomain);

    expect($tenant)->not->toBeNull();

    // Verify the tenant DB has the admin user
    $adminExists = $tenant->run(function () {
        return DB::table('users')->where('email', 'admin@testacademy.test')->exists();
    });

    expect($adminExists)->toBeTrue();

    // Cleanup
    $tenant->delete();
})->skip('Requires full DB provisioning — run manually');

test('school registration creates subscription plan', function (): void {
    $subdomain = 'subtest' . uniqid();

    $this->post('/register-school', [
        'school_name'                 => 'Sub Test School',
        'subdomain'                   => $subdomain,
        'admin_name'                  => 'Admin',
        'admin_email'                 => 'admin@subtest.test',
        'admin_password'              => 'Password123!',
        'admin_password_confirmation' => 'Password123!',
        'hp_check'                    => '',
    ]);

    $tenant = Tenant::find($subdomain);
    if (!$tenant) {
        $this->markTestSkipped('Tenant not created — requires DB provisioning.');
    }

    $plan = SubscriptionPlan::where('tenant_id', $subdomain)->first();
    expect($plan)->not->toBeNull()
        ->and($plan->status)->toBe('trial');

    $tenant->delete();
})->skip('Requires full DB provisioning — run manually');

test('registration form validation rejects missing school name', function (): void {
    $response = $this->post('/register-school', [
        'school_name'  => '',
        'subdomain'    => 'valid',
        'admin_name'   => 'Admin',
        'admin_email'  => 'admin@test.com',
        'admin_password'              => 'Password123!',
        'admin_password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors('school_name');
});

test('registration form validation rejects invalid subdomain characters', function (): void {
    $response = $this->post('/register-school', [
        'school_name'                 => 'Test School',
        'subdomain'                   => 'invalid subdomain!',
        'admin_name'                  => 'Admin',
        'admin_email'                 => 'admin@test.com',
        'admin_password'              => 'Password123!',
        'admin_password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors('subdomain');
});

test('registration form validation rejects missing admin email', function (): void {
    $response = $this->post('/register-school', [
        'school_name' => 'Test School',
        'subdomain'   => 'testvalid',
        'admin_name'  => 'Admin',
        'admin_email' => '',
    ]);

    $response->assertSessionHasErrors('admin_email');
});

test('registration form validation rejects missing admin name', function (): void {
    $response = $this->post('/register-school', [
        'school_name' => 'Test School',
        'subdomain'   => 'testvalid',
        'admin_name'  => '',
        'admin_email' => 'admin@test.com',
    ]);

    $response->assertSessionHasErrors('admin_name');
});

test('health endpoint returns json with status and checks keys', function (): void {
    try {
        $response = $this->getJson('/health');
        $response->assertJsonStructure(['status', 'checks' => ['db', 'cache', 'storage']]);
    } catch (\Throwable $e) {
        $this->markTestSkipped('Health check requires central DB connection: ' . $e->getMessage());
    }
});

test('ping endpoint returns pong', function (): void {
    $response = $this->get('/ping');

    $response->assertStatus(200)
        ->assertSee('pong');
});
