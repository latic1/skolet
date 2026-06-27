<?php

use App\Models\Tenant\SchoolClass;
use App\Models\Tenant\Student;
use App\Models\Tenant\User;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    // Build a full base URL using the tenant domain so Symfony's Request::create()
    // correctly sets HTTP_HOST from the URI (serverVariables alone get overridden).
    $domain = $this->tenant->domains()->first()->domain;
    $this->apiBase = 'http://' . $domain;

    Role::firstOrCreate(['name' => 'school_admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'teacher',      'guard_name' => 'web']);

    $permissions = ['students.view', 'attendance.view', 'attendance.edit', 'fees.view'];
    foreach ($permissions as $perm) {
        Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
    }

    Role::findByName('school_admin')->givePermissionTo($permissions);

    $class = SchoolClass::create(['name' => 'API Class', 'order' => 1]);

    Student::create([
        'admission_no'     => '2026/0800',
        'full_name'        => 'API Student',
        'gender'           => 'male',
        'guardian_name'    => 'Guardian',
        'guardian_contact' => '0200000008',
        'status'           => 'active',
        'class_id'         => $class->id,
    ]);

    $this->adminUser = User::create([
        'name'     => 'API Admin',
        'email'    => 'api_admin@test.test',
        'password' => bcrypt('password'),
        'role'     => 'school_admin',
    ]);
    $this->adminUser->assignRole('school_admin');
});

test('unauthenticated request to students API returns 401', function (): void {
    $response = $this->getJson($this->apiBase . '/api/v1/students');

    expect($response->status())->toBe(401);
});

test('authenticated request with valid Sanctum token returns paginated student list', function (): void {
    $token = $this->adminUser->createToken('test-token', ['*'])->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson($this->apiBase . '/api/v1/students');

    expect($response->status())->toBe(200);
    $data = $response->json();
    expect($data)->toHaveKey('data');
});

test('sanctum token can be created for a user', function (): void {
    $tokenResult = $this->adminUser->createToken('my-api-token', ['read']);

    expect($tokenResult->plainTextToken)->not->toBeEmpty()
        ->and(PersonalAccessToken::findToken($tokenResult->plainTextToken))->not->toBeNull();
});

test('token revocation invalidates subsequent API calls', function (): void {
    $tokenResult = $this->adminUser->createToken('revoke-test', ['*']);
    $plainToken  = $tokenResult->plainTextToken;

    // Token works before revocation
    $before = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
        ->getJson($this->apiBase . '/api/v1/students');
    expect($before->status())->toBe(200);

    // Revoke the token
    $tokenResult->accessToken->delete();
    auth()->forgetGuards(); // Flush cached auth state so Sanctum re-checks the DB

    // Token is invalid after revocation
    $after = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
        ->getJson($this->apiBase . '/api/v1/students');
    expect($after->status())->toBe(401);
});

test('user without students.view permission cannot access students API', function (): void {
    $teacherUser = User::create([
        'name'     => 'No Permission',
        'email'    => 'noperm@test.test',
        'password' => bcrypt('pw'),
        'role'     => 'teacher',
    ]);
    $teacherUser->assignRole('teacher');

    $token = $teacherUser->createToken('no-perm-token', ['*'])->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson($this->apiBase . '/api/v1/students');

    // Teacher role doesn't have students.view by default
    expect($response->status())->toBeIn([403, 401]);
});

test('announcements API is accessible to any authenticated token', function (): void {
    $token = $this->adminUser->createToken('announce-token', ['*'])->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson($this->apiBase . '/api/v1/announcements');

    expect($response->status())->toBe(200);
});
