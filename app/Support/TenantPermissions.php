<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Single source of truth for tenant roles and permissions.
 *
 * Used by TenantProvisioningService (new tenants) and the
 * tenants:seed-permissions command (backfilling existing tenants).
 * Update this file whenever a new permission is introduced.
 */
final class TenantPermissions
{
    public static function all(): array
    {
        return [
            'students.view', 'students.create', 'students.edit', 'students.delete',
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
            'attendance.view', 'attendance.edit',
            'timetable.view', 'timetable.edit',
            'exams.view', 'exams.create', 'exams.edit', 'exams.delete',
            'fees.view', 'fees.create', 'fees.edit',
            'announcements.view', 'announcements.create', 'announcements.edit', 'announcements.delete',
            'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.delete', 'assignments.submit',
            'behavior.view', 'behavior.create', 'behavior.edit', 'behavior.delete',
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
            'admissions.view', 'admissions.manage',
            'payroll.view', 'payroll.create', 'payroll.edit',
            'leave.view', 'leave.manage',
            'register.view', 'register.create', 'register.manage',
            'reports.view',
            'settings.manage',
            'webhooks.manage',
        ];
    }

    public static function byRole(): array
    {
        $all = self::all();

        return [
            'school_admin' => $all,

            'teacher' => [
                'students.view',
                'attendance.view', 'attendance.edit',
                'timetable.view', 'timetable.edit',
                'exams.view', 'exams.create', 'exams.edit',
                'announcements.view', 'announcements.create', 'announcements.edit',
                'assignments.view', 'assignments.create', 'assignments.edit', 'assignments.delete',
                'behavior.view', 'behavior.create',
                'leave.view',
                'register.view', 'register.create',
            ],

            'accountant' => [
                'fees.view', 'fees.create', 'fees.edit',
                'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete',
                'payroll.view', 'payroll.create', 'payroll.edit',
                'leave.view',
                'reports.view',
                'announcements.view',
            ],

            'student' => [
                'attendance.view',
                'exams.view',
                'fees.view',
                'announcements.view',
                'assignments.view', 'assignments.submit',
            ],

            'parent' => [
                'attendance.view',
                'exams.view',
                'fees.view',
                'announcements.view',
                'assignments.view',
            ],
        ];
    }
}
