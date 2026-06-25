<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AccountController;
use App\Http\Controllers\Tenant\AcademicYearController;
use App\Http\Controllers\Tenant\ImpersonateController;
use App\Http\Controllers\Tenant\AnnouncementController;
use App\Http\Controllers\Tenant\AttendanceController;
use App\Http\Controllers\Tenant\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\CustomDomainController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\ExamController;
use App\Http\Controllers\Tenant\ReportCardController;
use App\Http\Controllers\Tenant\FeeController;
use App\Http\Controllers\Tenant\ReceiptController;
use App\Http\Controllers\Tenant\PaystackWebhookController;
use App\Http\Controllers\Tenant\PublicPageController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\RolesPermissionsController;
use App\Http\Controllers\Tenant\SchoolClassController;
use App\Http\Controllers\Tenant\SchoolProfileController;
use App\Http\Controllers\Tenant\SectionController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\Tenant\StudentController;
use App\Http\Controllers\Tenant\StudentPromotionController;
use App\Http\Controllers\Tenant\SubjectAssignmentController;
use App\Http\Controllers\Tenant\SubjectController;
use App\Http\Controllers\Tenant\AuditLogController;
use App\Http\Controllers\Tenant\NotificationsController;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\Tenant\TimetableController;
use App\Http\Controllers\Tenant\ParentStudentController;
use App\Http\Controllers\Tenant\ParentPortalController;
use App\Http\Controllers\Tenant\AssignmentController;
use App\Http\Controllers\Tenant\DisciplinaryController;
use App\Http\Controllers\Tenant\SubmissionController;
use App\Http\Controllers\Tenant\UserNotificationsController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| All routes here are scoped to {subdomain}.skolet.com (or a verified
| custom domain). The domain constraint keeps these names distinct from
| central routes so the route collection never has URI collisions.
|
| The `tenant.` name prefix is applied to the entire domain group, so:
|   ->name('dashboard')  →  tenant.dashboard
|   Route::resource('students', ...)  →  tenant.students.index, etc.
|
*/

$appHost = preg_replace('/^www\./i', '', parse_url(config('app.url'), PHP_URL_HOST) ?? 'skolet.com');

Route::domain('{subdomain}.' . $appHost)
    ->middleware([
        'web',
        InitializeTenancyByDomain::class,
        PreventAccessFromCentralDomains::class,
        \App\Http\Middleware\RemoveTenantDomainParam::class,
        // Restores the school admin auth for the current request when a valid
        // impersonation session is active — must run after tenancy is initialized.
        \App\Http\Middleware\ResumeImpersonation::class,
        // Tags Sentry errors with tenant_id and authenticated user context.
        \App\Http\Middleware\SetSentryContext::class,
    ])
    ->name('tenant.')
    ->group(function () {

        // --- Paystack webhook (server-to-server, no CSRF, no auth) ----------
        // Signature is verified inside the controller via HMAC-SHA512.
        Route::post('/paystack/webhook', [PaystackWebhookController::class, 'handle'])
            ->name('fees.paystack.webhook')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        // --- Public (unauthenticated) -------------------------------------
        Route::get('/', [PublicPageController::class, 'index'])->name('public');

        // School logo — served directly from tenant storage (StorageDriver scopes
        // uploads to storage/tenant{id}/app/public/, so the standard symlink can't
        // reach them; this route streams the file instead).
        Route::get('/school-logo', [SchoolProfileController::class, 'logo'])->name('school-logo');

        // Tenant robots.txt — explicitly allows the public page; disallows authenticated routes.
        Route::get('/robots.txt', function () {
            $content = implode("\n", [
                'User-agent: *',
                'Allow: /',
                'Disallow: /dashboard',
                'Disallow: /students',
                'Disallow: /staff',
                'Disallow: /attendance',
                'Disallow: /timetable',
                'Disallow: /exams',
                'Disallow: /fees',
                'Disallow: /announcements',
                'Disallow: /settings',
                'Disallow: /reports',
                'Disallow: /logout',
            ]);
            return response($content, 200)->header('Content-Type', 'text/plain');
        })->name('robots');

        Route::middleware('guest')->group(function () {
            Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
            Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:tenant-login');
        });

        // Super Admin impersonation handshake — unauthenticated, one-time token (60s TTL)
        Route::get('/impersonate/{token}', [ImpersonateController::class, 'handle'])->name('impersonate.handle');

        // --- Authenticated ------------------------------------------------
        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
            Route::post('/impersonate/exit', [ImpersonateController::class, 'exit'])->name('impersonate.exit');

            // Single permission-aware dashboard — role-filtered widgets rendered in view
            Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('onboarding')->name('dashboard');

            // Onboarding wizard — shown to school admins before initial setup is complete
            Route::get('/onboarding', fn () => redirect(request()->getSchemeAndHttpHost() . '/onboarding/1'));
            Route::get('/onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
            Route::get('/onboarding/{step}', [OnboardingController::class, 'show'])->where('step', '[1-5]')->name('onboarding.show');
            Route::post('/onboarding/{step}', [OnboardingController::class, 'store'])->where('step', '[1-5]')->name('onboarding.store');

            // Account Settings — no permission gate, every role can edit their own account
            Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
            Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
            Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
            Route::get('/account/avatar', [AccountController::class, 'avatar'])->name('account.avatar');

            // Notifications — personal notification centre for every authenticated user
            Route::get('/notifications', [UserNotificationsController::class, 'index'])->name('notifications.index');
            Route::patch('/notifications/read-all', [UserNotificationsController::class, 'markAllRead'])->name('notifications.read-all');
            Route::patch('/notifications/{notification}/read', [UserNotificationsController::class, 'markRead'])->name('notifications.read');

            // Announcements — read access for all authenticated users
            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
            Route::middleware('permission:announcements.create')->group(function () {
                Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
            });
            Route::middleware('permission:announcements.edit')->group(function () {
                Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
            });
            Route::middleware('permission:announcements.delete')->group(function () {
                Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
            });

            // Students — specific paths registered before {student} wildcard to avoid conflicts
            Route::middleware('permission:students.view')->group(function () {
                Route::get('/students', [StudentController::class, 'index'])->name('students.index');
                Route::get('/students/import/template', [StudentController::class, 'downloadTemplate'])->name('students.import.template');
            });
            Route::middleware('permission:students.create')->group(function () {
                Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
                Route::post('/students', [StudentController::class, 'store'])->name('students.store');
                Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
            });
            // Wildcard routes come last so literal paths above take precedence
            Route::middleware('permission:students.view')->group(function () {
                Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
            });
            Route::middleware('permission:students.edit')->group(function () {
                Route::get('/students/promote', [StudentPromotionController::class, 'index'])->name('students.promote');
                Route::post('/students/promote', [StudentPromotionController::class, 'execute'])->name('students.promote.execute');
                Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
                Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
                Route::post('/students/{student}/login', [StudentController::class, 'createLogin'])->name('students.login.create');
                Route::delete('/students/{student}/login', [StudentController::class, 'revokeLogin'])->name('students.login.revoke');
                Route::post('/students/{student}/parents', [ParentStudentController::class, 'store'])->name('students.parents.store');
                Route::delete('/students/{student}/parents/{parentUser}', [ParentStudentController::class, 'destroy'])->name('students.parents.destroy');
            });
            Route::middleware('permission:students.delete')->group(function () {
                Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
            });

            // Staff — literal paths before {staff} wildcard to avoid route conflicts
            Route::middleware('permission:staff.view')->group(function () {
                Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
                Route::get('/staff/import/template', [StaffController::class, 'downloadTemplate'])->name('staff.import.template');
            });
            Route::middleware('permission:staff.create')->group(function () {
                Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
                Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
                Route::post('/staff/import', [StaffController::class, 'import'])->name('staff.import');
            });
            Route::middleware('permission:staff.view')->group(function () {
                Route::get('/staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
            });
            Route::middleware('permission:staff.edit')->group(function () {
                Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
                Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
                Route::post('/staff/{staff}/resend-credentials', [StaffController::class, 'resendCredentials'])->name('staff.resend-credentials');
                Route::post('/staff/{staff}/assignments', [SubjectAssignmentController::class, 'store'])->name('staff.assignments.store');
                Route::delete('/staff/assignments/{assignment}', [SubjectAssignmentController::class, 'destroy'])->name('staff.assignments.destroy');
            });
            Route::middleware('permission:staff.delete')->group(function () {
                Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
            });

            Route::middleware('permission:attendance.view')->group(function () {
                Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
                Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
                Route::get('/attendance/staff', [AttendanceController::class, 'staff'])->name('attendance.staff');
            });
            Route::middleware('permission:attendance.edit')->group(function () {
                Route::post('/attendance', [AttendanceController::class, 'save'])->name('attendance.save');
                Route::post('/attendance/staff', [AttendanceController::class, 'saveStaff'])->name('attendance.staff.save');
            });

            Route::middleware('permission:timetable.view')->group(function () {
                Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
                Route::get('/timetable/my', [TimetableController::class, 'teacher'])->name('timetable.teacher');
            });
            Route::middleware('permission:timetable.edit')->group(function () {
                Route::post('/timetable', [TimetableController::class, 'save'])->name('timetable.save');
                Route::delete('/timetable/{timetable}', [TimetableController::class, 'destroy'])->name('timetable.destroy');
            });

            // Exams — literal paths registered before {exam} wildcard to avoid conflicts
            Route::middleware('permission:exams.view')->group(function () {
                Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
                Route::get('/exams/marks', [ExamController::class, 'marks'])->name('exams.marks');
                // Report card preview — accessible to all roles with exams.view (access control enforced in controller)
                Route::get('/exams/report-card', [ReportCardController::class, 'preview'])->name('exams.report-card');
                Route::get('/exams/report-card/download', [ReportCardController::class, 'download'])->name('exams.report-card.download');
            });
            Route::middleware('permission:exams.create')->group(function () {
                Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
            });
            Route::middleware('permission:exams.edit')->group(function () {
                Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
                Route::post('/exams/marks', [ExamController::class, 'saveMarks'])->name('exams.marks.save');
                Route::patch('/exams/{exam}/publish', [ExamController::class, 'publish'])->name('exams.publish');
            });
            Route::middleware('permission:exams.delete')->group(function () {
                Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
            });

            // Parent portal — parents view their linked children's data
            Route::get('/my-children', [ParentPortalController::class, 'index'])->name('parents.portal');

            // Assignments — literal paths before {assignment} wildcard to avoid conflicts
            Route::middleware('permission:assignments.view')->group(function () {
                Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
            });
            Route::middleware('permission:assignments.create')->group(function () {
                Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
            });
            Route::middleware('permission:assignments.edit')->group(function () {
                Route::put('/assignments/{assignment}', [AssignmentController::class, 'update'])->name('assignments.update');
                Route::patch('/submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
            });
            Route::middleware('permission:assignments.delete')->group(function () {
                Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
            });
            Route::middleware('permission:assignments.submit')->group(function () {
                Route::post('/assignments/{assignment}/submit', [SubmissionController::class, 'store'])->name('assignments.submit');
            });

            // Behavior / Disciplinary Records
            Route::middleware('permission:behavior.view')->group(function () {
                Route::get('/behavior', [DisciplinaryController::class, 'index'])->name('behavior.index');
            });
            Route::middleware('permission:behavior.create')->group(function () {
                Route::post('/behavior', [DisciplinaryController::class, 'store'])->name('behavior.store');
            });
            Route::middleware('permission:behavior.delete')->group(function () {
                Route::delete('/behavior/{disciplinaryRecord}', [DisciplinaryController::class, 'destroy'])->name('behavior.destroy');
            });

            // Fees — index accessible to all auth users; controller dispatches by permission
            // (admin/accountant → tabbed admin view; student/parent → own fees view)
            Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
            // Receipt download — literal path before {feeStructure} wildcard
            Route::get('/fees/receipt/{feePayment}', [ReceiptController::class, 'download'])->name('fees.receipt.download');
            // Term bill PDF — gated by fees.view in the controller
            Route::get('/fees/bill/{student}', [FeeController::class, 'printBill'])->name('fees.bill');
            // Paystack callback — authenticated (student/parent returns from Paystack checkout)
            Route::get('/paystack/callback', [FeeController::class, 'paystackCallback'])->name('fees.paystack.callback');
            // Paystack checkout initiation — any authenticated user (student/parent)
            Route::post('/paystack/checkout', [FeeController::class, 'paystackCheckout'])->name('fees.paystack.checkout');
            Route::middleware('permission:fees.create')->group(function () {
                Route::post('/fees', [FeeController::class, 'store'])->name('fees.store');
                Route::post('/fees/pay', [FeeController::class, 'pay'])->name('fees.pay');
            });
            Route::middleware('permission:fees.edit')->group(function () {
                Route::put('/fees/{feeStructure}', [FeeController::class, 'update'])->name('fees.update');
            });
            Route::middleware('permission:fees.delete')->group(function () {
                Route::delete('/fees/{feeStructure}', [FeeController::class, 'destroy'])->name('fees.destroy');
            });

            Route::middleware('permission:reports.view')->group(function () {
                Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
                Route::get('/reports/attendance/pdf', [ReportController::class, 'attendancePdf'])->name('reports.attendance.pdf');
                Route::get('/reports/attendance/excel', [ReportController::class, 'attendanceExcel'])->name('reports.attendance.excel');
                Route::get('/reports/fees/pdf', [ReportController::class, 'feesPdf'])->name('reports.fees.pdf');
                Route::get('/reports/fees/excel', [ReportController::class, 'feesExcel'])->name('reports.fees.excel');
            });

            Route::middleware('permission:settings.manage')->group(function () {
                // Settings index → redirect to academic year
                Route::get('/settings', fn () => redirect(request()->getSchemeAndHttpHost() . '/settings/academic-year'))->name('settings');

                // Academic Year
                Route::get('/settings/academic-year', [AcademicYearController::class, 'index'])->name('settings.academic-year');
                Route::post('/settings/academic-year', [AcademicYearController::class, 'store'])->name('settings.academic-year.store');
                Route::put('/settings/academic-year/{academicYear}', [AcademicYearController::class, 'update'])->name('settings.academic-year.update');
                Route::delete('/settings/academic-year/{academicYear}', [AcademicYearController::class, 'destroy'])->name('settings.academic-year.destroy');
                Route::patch('/settings/academic-year/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('settings.academic-year.set-current');
                Route::post('/settings/academic-year/period-system', [AcademicYearController::class, 'setPeriodSystem'])->name('settings.academic-year.period-system');
                Route::post('/settings/academic-year/{academicYear}/copy-terms', [AcademicYearController::class, 'copyTerms'])->name('settings.academic-year.copy-terms');

                // Terms
                Route::post('/settings/academic-year/{academicYear}/terms', [AcademicYearController::class, 'storeTerm'])->name('settings.terms.store');
                Route::put('/settings/terms/{term}', [AcademicYearController::class, 'updateTerm'])->name('settings.terms.update');
                Route::delete('/settings/terms/{term}', [AcademicYearController::class, 'destroyTerm'])->name('settings.terms.destroy');
                Route::patch('/settings/terms/{term}/set-current', [AcademicYearController::class, 'setCurrentTerm'])->name('settings.terms.set-current');

                // Classes
                Route::get('/settings/classes', [SchoolClassController::class, 'index'])->name('settings.classes');
                Route::post('/settings/classes', [SchoolClassController::class, 'store'])->name('settings.classes.store');
                Route::put('/settings/classes/{schoolClass}', [SchoolClassController::class, 'update'])->name('settings.classes.update');
                Route::delete('/settings/classes/{schoolClass}', [SchoolClassController::class, 'destroy'])->name('settings.classes.destroy');

                // Sections (nested under a class for store, flat for delete)
                Route::post('/settings/classes/{schoolClass}/sections', [SectionController::class, 'store'])->name('settings.sections.store');
                Route::delete('/settings/sections/{section}', [SectionController::class, 'destroy'])->name('settings.sections.destroy');

                // Subjects
                Route::get('/settings/subjects', [SubjectController::class, 'index'])->name('settings.subjects');
                Route::post('/settings/subjects', [SubjectController::class, 'store'])->name('settings.subjects.store');
                Route::put('/settings/subjects/{subject}', [SubjectController::class, 'update'])->name('settings.subjects.update');
                Route::delete('/settings/subjects/{subject}', [SubjectController::class, 'destroy'])->name('settings.subjects.destroy');

                // Roles & Permissions
                Route::get('/settings/roles', [RolesPermissionsController::class, 'index'])->name('settings.roles');
                Route::post('/settings/roles', [RolesPermissionsController::class, 'store'])->name('settings.roles.store');
                Route::put('/settings/roles/{role}', [RolesPermissionsController::class, 'update'])->name('settings.roles.update');
                Route::delete('/settings/roles/{role}', [RolesPermissionsController::class, 'destroy'])->name('settings.roles.destroy');

                // School Profile
                Route::get('/settings/profile', [SchoolProfileController::class, 'index'])->name('settings.profile');
                Route::post('/settings/profile', [SchoolProfileController::class, 'update'])->name('settings.profile.update');
                Route::post('/settings/profile/reset-counter', [SchoolProfileController::class, 'resetAdmissionCounter'])->name('settings.profile.reset-counter');
                Route::post('/settings/grading-scale', [SchoolProfileController::class, 'updateGradingScale'])->name('settings.grading-scale');

                // Notifications
                Route::get('/settings/notifications', [NotificationsController::class, 'index'])->name('settings.notifications');
                Route::post('/settings/notifications', [NotificationsController::class, 'save'])->name('settings.notifications.save');
                Route::post('/settings/notifications/test/{event}', [NotificationsController::class, 'test'])->name('settings.notifications.test');

                // Custom Domain
                Route::get('/settings/domain', [CustomDomainController::class, 'index'])->name('settings.domain');
                Route::post('/settings/domain', [CustomDomainController::class, 'store'])->name('settings.domain.store');
                Route::patch('/settings/domain/{domainId}/verify', [CustomDomainController::class, 'verify'])->name('settings.domain.verify');
                Route::delete('/settings/domain/{domainId}', [CustomDomainController::class, 'destroy'])->name('settings.domain.destroy');

                // Audit Log
                Route::get('/settings/audit-log', [AuditLogController::class, 'index'])->name('settings.audit-log');
            });
        });
    });
