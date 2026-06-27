<?php

use App\Models\Central\SuperAdmin;
use App\Models\Central\SubscriptionPlan;
use App\Models\Central\Tenant;

beforeEach(function (): void {
    $this->superAdmin = SuperAdmin::firstOrCreate(
        ['email' => 'super@schoolflow.test'],
        ['name' => 'Super Admin', 'password' => bcrypt('SuperSecret1!')]
    );
});

afterEach(function (): void {
    SuperAdmin::where('email', 'super@schoolflow.test')->delete();
});

test('unauthenticated request to super-admin dashboard redirects to login', function (): void {
    $response = $this->get('/super-admin');

    // Authenticate middleware redirects super-admin paths to the super-admin login
    $response->assertRedirect('/super-admin/login');
});

test('unauthenticated request to tenant toggle redirects to login', function (): void {
    $response = $this->patch('/super-admin/tenants/fake-id/toggle');

    $response->assertRedirect('/super-admin/login');
});

test('authenticated super admin can access the dashboard', function (): void {
    $response = $this->actingAs($this->superAdmin, 'super_admin')
        ->get('/super-admin');

    $response->assertOk();
});

test('super admin dashboard lists all tenants', function (): void {
    $tenant = Tenant::create(['id' => 'school1', 'name' => 'School One', 'subdomain' => 'school1', 'status' => 'active']);

    $response = $this->actingAs($this->superAdmin, 'super_admin')
        ->get('/super-admin');

    $response->assertOk()
        ->assertSee('School One');

    $tenant->delete();
});

test('super admin can toggle tenant status from active to suspended', function (): void {
    $tenant = Tenant::create(['id' => 'school2', 'name' => 'School Two', 'subdomain' => 'school2', 'status' => 'active']);

    $this->actingAs($this->superAdmin, 'super_admin')
        ->patch("/super-admin/tenants/{$tenant->id}/toggle");

    expect(Tenant::find($tenant->id)->status)->toBe('suspended');

    $tenant->delete();
});

test('super admin can toggle tenant status from suspended back to active', function (): void {
    $tenant = Tenant::create(['id' => 'school3', 'name' => 'School Three', 'subdomain' => 'school3', 'status' => 'suspended']);

    $this->actingAs($this->superAdmin, 'super_admin')
        ->patch("/super-admin/tenants/{$tenant->id}/toggle");

    expect(Tenant::find($tenant->id)->status)->toBe('active');

    $tenant->delete();
});

test('super admin marking a tenant as paid sets payment_status and status to active', function (): void {
    $tenant = Tenant::create(['id' => 'school4', 'name' => 'School Four', 'subdomain' => 'school4', 'status' => 'active']);

    SubscriptionPlan::create([
        'tenant_id'        => $tenant->id,
        'plan'             => 'standard',
        'status'           => 'trial',
        'payment_status'   => 'unpaid',
        'rate_per_student' => 5.00,
        'student_count'    => 0,
        'amount_due'       => 0.00,
    ]);

    $this->actingAs($this->superAdmin, 'super_admin')
        ->patch("/super-admin/tenants/{$tenant->id}/mark-paid", [
            'cycle_start' => '2026-07-01',
            'cycle_end'   => '2026-09-30',
        ]);

    $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->first();
    expect($plan->payment_status)->toBe('paid')
        ->and($plan->status)->toBe('active')
        ->and($plan->cycle_end)->toBe('2026-09-30');

    $tenant->delete();
});

test('dashboard auto-expires unpaid tenants whose cycle_end has passed', function (): void {
    $tenant = Tenant::create(['id' => 'school5', 'name' => 'School Five', 'subdomain' => 'school5', 'status' => 'active']);

    SubscriptionPlan::create([
        'tenant_id'        => $tenant->id,
        'plan'             => 'standard',
        'status'           => 'active',
        'payment_status'   => 'unpaid',
        'rate_per_student' => 5.00,
        'student_count'    => 0,
        'amount_due'       => 0.00,
        'cycle_start'      => '2025-01-01',
        'cycle_end'        => '2025-03-31',
    ]);

    $this->actingAs($this->superAdmin, 'super_admin')
        ->get('/super-admin');

    $plan = SubscriptionPlan::where('tenant_id', $tenant->id)->first();
    expect($plan->status)->toBe('expired');

    $tenant->delete();
});
