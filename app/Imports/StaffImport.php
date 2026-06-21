<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Tenant\Staff;
use App\Models\Tenant\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Spatie\Permission\Models\Role;

final class StaffImport implements ToCollection, WithHeadingRow
{
    public array $errors      = [];
    public array $credentials = []; // [['name', 'email', 'password'], ...]
    public int   $imported    = 0;

    public function collection(Collection $rows): void
    {
        // Preload valid staff roles (exclude system-only roles)
        $validRoles = Role::whereNotIn('name', ['school_admin', 'student', 'parent'])
            ->pluck('name')
            ->map(fn (string $n) => strtolower($n))
            ->toArray();

        // Track duplicate emails within this batch
        $batchEmails  = [];
        $rowsToInsert = [];

        // ── Pass 1: validate every row, collect ALL errors ────────────────────
        foreach ($rows as $index => $row) {
            $rowNum    = $index + 2;
            $rowErrors = [];

            $fullName  = trim((string) ($row['full_name'] ?? ''));
            $email     = strtolower(trim((string) ($row['email'] ?? '')));
            $role      = strtolower(trim((string) ($row['role']  ?? '')));

            if ($fullName === '') {
                $rowErrors[] = 'Full Name is required.';
            }
            if ($email === '') {
                $rowErrors[] = 'Email is required.';
            }
            if ($role === '') {
                $rowErrors[] = 'Role is required.';
            }

            // Email format
            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Email \"{$email}\" is not a valid email address.";
            }

            // Role existence
            if ($role !== '' && !in_array($role, $validRoles, true)) {
                $roleList    = implode(', ', $validRoles);
                $rowErrors[] = "Role \"{$role}\" is not valid. Valid roles: {$roleList}.";
            }

            // Duplicate email within batch
            if ($email !== '') {
                if (isset($batchEmails[$email])) {
                    $rowErrors[] = "Duplicate: email \"{$email}\" appears more than once in this file.";
                } else {
                    $batchEmails[$email] = true;
                }
            }

            // Duplicate email against existing users
            if ($email !== '' && User::where('email', $email)->exists()) {
                $rowErrors[] = "Email \"{$email}\" already has an account in the system.";
            }

            if (!empty($rowErrors)) {
                foreach ($rowErrors as $e) {
                    $this->errors[] = "Row {$rowNum}: {$e}";
                }
                continue;
            }

            $rowsToInsert[] = [
                'full_name'  => $fullName,
                'email'      => $email,
                'role'       => $role,
                'role_title' => trim((string) ($row['role_title'] ?? '')) ?: null,
                'phone'      => trim((string) ($row['phone']      ?? '')) ?: null,
            ];
        }

        // If any row failed validation, abort — nothing is imported
        if (!empty($this->errors)) {
            return;
        }

        // ── Pass 2: import all rows in a single transaction ───────────────────
        DB::transaction(function () use ($rowsToInsert): void {
            foreach ($rowsToInsert as $data) {
                $tempPassword = 'SF' . strtoupper(Str::random(6)) . rand(10, 99);

                $user = User::create([
                    'name'     => $data['full_name'],
                    'email'    => $data['email'],
                    'password' => $tempPassword,
                    'role'     => $data['role'],
                ]);

                $user->assignRole($data['role']);

                Staff::create([
                    'user_id'    => $user->id,
                    'full_name'  => $data['full_name'],
                    'role_title' => $data['role_title'],
                    'phone'      => $data['phone'],
                    'status'     => 'active',
                ]);

                $this->credentials[] = [
                    'name'     => $data['full_name'],
                    'email'    => $data['email'],
                    'password' => $tempPassword,
                ];
                $this->imported++;
            }
        });
    }
}
