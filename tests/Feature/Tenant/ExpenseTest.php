<?php

use App\Models\Tenant\Expense;
use App\Models\Tenant\ExpenseCategory;
use App\Models\Tenant\User;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'accountant',   'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'teacher',      'guard_name' => 'web']);

    Permission::firstOrCreate(['name' => 'expenses.view',   'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'expenses.create', 'guard_name' => 'web']);

    Role::findByName('school_admin')->givePermissionTo(['expenses.view', 'expenses.create']);
    Role::findByName('accountant')->givePermissionTo(['expenses.view', 'expenses.create']);

    $this->adminUser = User::create([
        'name' => 'Admin', 'email' => 'admin_exp@test.test', 'password' => bcrypt('pw'), 'role' => 'school_admin',
    ]);
    $this->adminUser->assignRole('school_admin');

    $this->teacherUser = User::create([
        'name' => 'Teacher', 'email' => 'teacher_exp@test.test', 'password' => bcrypt('pw'), 'role' => 'teacher',
    ]);
    $this->teacherUser->assignRole('teacher');

    $this->category = ExpenseCategory::create(['name' => 'Utilities']);
});

test('expense can be created with category, amount, date, description', function (): void {
    $this->actingAs($this->adminUser);

    $expense = Expense::create([
        'category_id' => $this->category->id,
        'amount'      => 150.00,
        'date'        => now()->toDateString(),
        'description' => 'Monthly electricity bill',
        'recorded_by' => $this->adminUser->id,
    ]);

    expect($expense)->toBeInstanceOf(Expense::class)
        ->and($expense->description)->toBe('Monthly electricity bill')
        ->and((float) $expense->amount)->toBe(150.0);
});

test('expense category can be created inline if it does not exist', function (): void {
    $category = ExpenseCategory::firstOrCreate(['name' => 'Supplies']);

    $expense = Expense::create([
        'category_id' => $category->id,
        'amount'      => 75.50,
        'date'        => now()->toDateString(),
        'description' => 'Chalk and markers',
        'recorded_by' => $this->adminUser->id,
    ]);

    expect(ExpenseCategory::where('name', 'Supplies')->exists())->toBeTrue()
        ->and($expense->category->name)->toBe('Supplies');
});

test('monthly total is computed correctly from expenses table', function (): void {
    $month = now()->format('Y-m');

    Expense::insert([
        ['category_id' => $this->category->id, 'amount' => 200.00, 'date' => now()->toDateString(), 'description' => 'E1', 'recorded_by' => $this->adminUser->id, 'created_at' => now(), 'updated_at' => now()],
        ['category_id' => $this->category->id, 'amount' => 350.00, 'date' => now()->toDateString(), 'description' => 'E2', 'recorded_by' => $this->adminUser->id, 'created_at' => now(), 'updated_at' => now()],
    ]);

    $total = (float) Expense::whereYear('date', now()->year)
        ->whereMonth('date', now()->month)
        ->sum('amount');

    expect($total)->toBe(550.0);
});

test('accountant has expenses.create permission', function (): void {
    $accountant = User::create([
        'name' => 'Accountant', 'email' => 'acc_exp@test.test', 'password' => bcrypt('pw'), 'role' => 'accountant',
    ]);
    $accountant->assignRole('accountant');

    expect($accountant->can('expenses.create'))->toBeTrue()
        ->and($accountant->can('expenses.view'))->toBeTrue();
});

test('teacher does not have expenses.create permission', function (): void {
    expect($this->teacherUser->can('expenses.create'))->toBeFalse()
        ->and($this->teacherUser->can('expenses.view'))->toBeFalse();
});

test('expense category relationship resolves correctly', function (): void {
    $expense = Expense::create([
        'category_id' => $this->category->id,
        'amount'      => 500.00,
        'date'        => now()->toDateString(),
        'description' => 'Maintenance work',
        'recorded_by' => $this->adminUser->id,
    ]);

    expect($expense->category)->toBeInstanceOf(ExpenseCategory::class)
        ->and($expense->category->name)->toBe('Utilities');
});
