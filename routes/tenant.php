<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\AccountController;
use App\Http\Controllers\Tenant\Api\AnnouncementApiController;
use App\Http\Controllers\Tenant\Api\AttendanceApiController;
use App\Http\Controllers\Tenant\Api\FeeApiController;
use App\Http\Controllers\Tenant\Api\StudentApiController;
use App\Http\Controllers\Tenant\AcademicYearController;
use App\Http\Controllers\Tenant\ImpersonateController;
use App\Http\Controllers\Tenant\AnnouncementController;
use App\Http\Controllers\Tenant\AttendanceController;
use App\Http\Controllers\Tenant\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Tenant\Auth\PasswordResetLinkController;
use App\Http\Controllers\Tenant\Auth\NewPasswordController;
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
use App\Http\Controllers\Tenant\ExpenseController;
use App\Http\Controllers\Tenant\PayrollController;
use App\Http\Controllers\Tenant\LeaveController;
use App\Http\Controllers\Tenant\RegisterController;
use App\Http\Controllers\Tenant\LessonPlanController;
use App\Http\Controllers\Tenant\AdmissionController;
use App\Http\Controllers\Tenant\FeeDiscountController;
use App\Http\Controllers\Tenant\PublicApplicationController;
use App\Http\Controllers\Tenant\TranscriptController;
use App\Http\Controllers\Tenant\SubmissionController;
use App\Http\Controllers\Tenant\UserNotificationsController;
use App\Http\Controllers\Tenant\PlatformBroadcastController;
use App\Http\Controllers\Tenant\PrivacyController;
use App\Http\Controllers\Tenant\BillingController;
use App\Http\Controllers\Tenant\WebhookController;
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
        // Checks for active platform broadcasts for this tenant and shares to view.
        \App\Http\Middleware\CheckPlatformBroadcast::class,
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
        Route::get('/apply', [PublicApplicationController::class, 'show'])->name('apply.show');
        Route::post('/apply', [PublicApplicationController::class, 'store'])->name('apply.store');

        // School logo — served directly from tenant storage (StorageDriver scopes
        // uploads to storage/tenant{id}/app/public/, so the standard symlink can't
        // reach them; this route streams the file instead).
        Route::get('/school-logo', [SchoolProfileController::class, 'logo'])->name('school-logo');

        // Tenant robots.txt — explicitly allows the public page; disallows authenticated routes.
        Route::get('/robots.txt', function () {
            $content = implode("\n", [
                'User-agent: *',
                'Allow: /',
                'Allow: /apply',
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

            // Password reset
            Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
            Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
            Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
            Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
        });

        // Super Admin impersonation handshake — unauthenticated, one-time token (60s TTL)
        Route::get('/impersonate/{token}', [ImpersonateController::class, 'handle'])->name('impersonate.handle');

        // --- Authenticated ------------------------------------------------
        Route::middleware('auth')->group(function () {
            Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
            Route::post('/impersonate/exit', [ImpersonateController::class, 'exit'])->name('impersonate.exit');

            // Platform broadcast dismiss (Info/Warning only — Critical is non-dismissible)
            Route::patch('/platform-notice/{notificationId}/dismiss', [PlatformBroadcastController::class, 'dismiss'])->name('broadcast.dismiss');

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

            // API Token management — web UI for creating/revoking Sanctum tokens
            Route::post('/account/tokens', [AccountController::class, 'createToken'])->name('account.tokens.create');
            Route::delete('/account/tokens/{tokenId}', [AccountController::class, 'revokeToken'])->name('account.tokens.revoke');

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
                Route::get('/students/trash', [StudentController::class, 'trash'])->name('students.trash');
            });
            Route::middleware('permission:students.create')->group(function () {
                Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
                Route::post('/students', [StudentController::class, 'store'])->name('students.store');
                Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
            });
            // Literal promote paths must be registered before the {student} wildcard
            Route::middleware('permission:students.edit')->group(function () {
                Route::get('/students/promote', [StudentPromotionController::class, 'index'])->name('students.promote');
                Route::post('/students/promote', [StudentPromotionController::class, 'execute'])->name('students.promote.execute');
            });
            // Wildcard routes come last so literal paths above take precedence
            Route::middleware('permission:students.view')->group(function () {
                Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
                Route::get('/students/{student}/transcript', [TranscriptController::class, 'download'])->name('students.transcript');
            });
            Route::middleware('permission:students.edit')->group(function () {
                Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
                Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
                Route::post('/students/{student}/login', [StudentController::class, 'createLogin'])->name('students.login.create');
                Route::delete('/students/{student}/login', [StudentController::class, 'revokeLogin'])->name('students.login.revoke');
                Route::post('/students/{student}/parents', [ParentStudentController::class, 'store'])->name('students.parents.store');
                Route::delete('/students/{student}/parents/{parentUser}', [ParentStudentController::class, 'destroy'])->name('students.parents.destroy');
            });
            // Fee discounts — gated by fees.edit (accountant / admin only)
            Route::middleware('permission:fees.edit')->group(function () {
                Route::post('/students/{student}/discounts', [FeeDiscountController::class, 'store'])->name('students.discounts.store');
                Route::delete('/students/{student}/discounts/{discount}', [FeeDiscountController::class, 'destroy'])->name('students.discounts.destroy');
            });
            Route::middleware('permission:students.delete')->group(function () {
                Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
                Route::post('/students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
                Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete'])->name('students.force-delete');
            });
            Route::middleware('permission:students.edit')->group(function () {
                Route::post('/students/{student}/anonymize', [StudentController::class, 'anonymize'])->name('students.anonymize');
                Route::post('/students/{student}/export', [StudentController::class, 'exportData'])->name('students.export');
            });

            // Staff — literal paths before {staff} wildcard to avoid route conflicts
            Route::middleware('permission:staff.view')->group(function () {
                Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
                Route::get('/staff/import/template', [StaffController::class, 'downloadTemplate'])->name('staff.import.template');
                Route::get('/staff/trash', [StaffController::class, 'trash'])->name('staff.trash');
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
                Route::post('/staff/{id}/restore', [StaffController::class, 'restore'])->name('staff.restore');
                Route::delete('/staff/{id}/force-delete', [StaffController::class, 'forceDelete'])->name('staff.force-delete');
            });

            Route::middleware('permission:attendance.view')->group(function () {
                Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
                Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
                Route::get('/attendance/staff', [AttendanceController::class, 'staff'])->name('attendance.staff');
                Route::post('/attendance/notify/{student}', [AttendanceController::class, 'notifyGuardian'])->name('attendance.notify.guardian');
                Route::post('/attendance/notify-bulk', [AttendanceController::class, 'notifyAll'])->name('attendance.notify.bulk');
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

            // Expenses
            Route::middleware('permission:expenses.view')->group(function () {
                Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
                Route::get('/expenses/receipt/{expense}', [ExpenseController::class, 'receipt'])->name('expenses.receipt');
            });
            Route::middleware('permission:expenses.create')->group(function () {
                Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
                Route::post('/expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
            });
            Route::middleware('permission:expenses.edit')->group(function () {
                Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
            });
            Route::middleware('permission:expenses.delete')->group(function () {
                Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
            });

            // Payroll
            Route::middleware('permission:payroll.view')->group(function () {
                Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
                Route::get('/payroll/{payrollRun}/{payrollItem}/payslip', [PayrollController::class, 'downloadPayslip'])->name('payroll.payslip');
            });
            Route::middleware('permission:payroll.edit')->group(function () {
                Route::patch('/payroll/salary/{staff}', [PayrollController::class, 'updateSalaryStructure'])->name('payroll.salary.update');
            });
            Route::middleware('permission:payroll.create')->group(function () {
                Route::post('/payroll/run', [PayrollController::class, 'runPayroll'])->name('payroll.run');
                Route::post('/payroll/{payrollRun}/expense', [PayrollController::class, 'logAsExpense'])->name('payroll.expense');
                Route::patch('/payroll/{payrollRun}/items/{payrollItem}/pay', [PayrollController::class, 'markPaid'])->name('payroll.item.pay');
            });

            Route::middleware('permission:leave.view')->group(function () {
                Route::get('/leave', [LeaveController::class, 'index'])->name('leave.index');
                Route::post('/leave', [LeaveController::class, 'store'])->name('leave.store');
                Route::patch('/leave/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leave.approve');
                Route::patch('/leave/{leaveRequest}/reject', [LeaveController::class, 'reject'])->name('leave.reject');
            });

            Route::middleware('permission:register.view')->group(function () {
                Route::get('/register', [RegisterController::class, 'index'])->name('register.index');
                Route::get('/register/pdf/{staff}/{month}', [RegisterController::class, 'exportPdf'])->name('register.pdf');
            });
            Route::middleware('permission:register.create')->group(function () {
                Route::post('/register', [RegisterController::class, 'store'])->name('register.store');
                Route::post('/lesson-plans', [LessonPlanController::class, 'store'])->name('lesson-plan.store');
                Route::patch('/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'update'])->name('lesson-plan.update');
                Route::delete('/lesson-plans/{lessonPlan}', [LessonPlanController::class, 'destroy'])->name('lesson-plan.destroy');
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

            Route::middleware('permission:admissions.view')->group(function () {
                Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions.index');
            });
            Route::middleware('permission:admissions.manage')->group(function () {
                Route::post('/admissions/{application}/accept', [AdmissionController::class, 'accept'])->name('admissions.accept');
                Route::post('/admissions/{application}/reject', [AdmissionController::class, 'reject'])->name('admissions.reject');
            });

            Route::middleware('permission:reports.view')->group(function () {
                Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
                Route::get('/reports/attendance/pdf', [ReportController::class, 'attendancePdf'])->name('reports.attendance.pdf');
                Route::get('/reports/attendance/excel', [ReportController::class, 'attendanceExcel'])->name('reports.attendance.excel');
                Route::get('/reports/fees/pdf', [ReportController::class, 'feesPdf'])->name('reports.fees.pdf');
                Route::get('/reports/fees/excel', [ReportController::class, 'feesExcel'])->name('reports.fees.excel');
                Route::get('/reports/academic/pdf', [ReportController::class, 'academicPdf'])->name('reports.academic.pdf');
                Route::get('/reports/financial/pdf', [ReportController::class, 'financialPdf'])->name('reports.financial.pdf');
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

                // Data & Privacy
                Route::get('/settings/privacy', [PrivacyController::class, 'index'])->name('settings.privacy');
                Route::post('/settings/privacy/export', [PrivacyController::class, 'requestFullExport'])->name('settings.privacy.export');

                // Billing (read-only view of subscription + payment history from central DB)
                Route::get('/settings/billing', [BillingController::class, 'index'])->name('settings.billing');
                Route::get('/settings/billing/invoices/{paymentId}', [BillingController::class, 'downloadInvoice'])->name('settings.billing.invoice');

                // Webhooks
                Route::middleware('permission:webhooks.manage')->group(function () {
                    Route::get('/settings/webhooks', [WebhookController::class, 'index'])->name('settings.webhooks');
                    Route::post('/settings/webhooks', [WebhookController::class, 'store'])->name('settings.webhooks.store');
                    Route::patch('/settings/webhooks/{webhook}/toggle', [WebhookController::class, 'toggle'])->name('settings.webhooks.toggle');
                    Route::delete('/settings/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('settings.webhooks.destroy');
                    Route::get('/settings/webhooks/{webhook}/deliveries', [WebhookController::class, 'deliveries'])->name('settings.webhooks.deliveries');
                    Route::post('/settings/webhooks/{webhook}/deliveries/{delivery}/retry', [WebhookController::class, 'retry'])->name('settings.webhooks.retry');
                });
            });

            // Export download — requires auth + settings.manage (checked in controller)
            Route::get('/export/download/{token}', [PrivacyController::class, 'download'])->name('export.download');
        });

        // --- REST API v1 — Sanctum bearer-token auth, no CSRF, 60 req/min per token ---
        Route::prefix('api/v1')
            ->name('api.v1.')
            ->middleware(['auth:sanctum', 'throttle:60,1'])
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])
            ->group(function () {

                // Students — requires students.view permission
                Route::middleware('permission:students.view')->group(function () {
                    Route::get('/students', [StudentApiController::class, 'index'])->name('students.index');
                    Route::get('/students/{student}', [StudentApiController::class, 'show'])->name('students.show');
                    Route::get('/students/{student}/attendance', [StudentApiController::class, 'attendance'])->name('students.attendance');
                    Route::get('/students/{student}/exams', [StudentApiController::class, 'exams'])->name('students.exams');
                });

                // Attendance — POST for biometric gate integration; requires attendance.edit + write token
                Route::middleware('permission:attendance.edit')->group(function () {
                    Route::post('/attendance', [AttendanceApiController::class, 'store'])->name('attendance.store');
                });

                // Fees — requires fees.view permission
                Route::middleware('permission:fees.view')->group(function () {
                    Route::get('/fees/{student}', [FeeApiController::class, 'show'])->name('fees.show');
                });

                // Announcements — all authenticated tokens (no additional permission gate)
                Route::get('/announcements', [AnnouncementApiController::class, 'index'])->name('announcements.index');
            });
    });
