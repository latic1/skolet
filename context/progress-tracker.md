# Progress Tracker

Update this file after every completed feature. Any AI agent reading this should immediately know what is done, what is in progress, and what is next.

---

## Current Status

**Phase:** 8/10 — Phase 8 closing item completed; Phase 10 in progress
**Last completed:** Feature 31b — Extended Test Coverage (Phase 8 close-out: 20 PestPHP test files covering StaffTest, TimetableTest, LeaveTest, SubjectTeacherAssignment, PaystackTest, ReceiptTest, TranscriptTest, ApiTest, ExpenseTest, FeeDiscountTest, AssignmentTest, ParentPortalTest, StudentPromotionTest, NotificationTest, PayrollTest, FinancialSummaryTest, PayrollTaxTest [Unit], GradingScaleTest [Unit], ExamAnalyticsServiceTest [Unit], SuperAdminTest [Central]; 6 new factories: LeaveRequest, Assignment, Expense, SalaryStructure, PayrollRun, PayrollItem; PaystackService tested via ReflectionProperty mock injection; no new migrations)
**Previous:** Feature 48b — Ghana Payroll Tax Compliance & Payment Tracking (migration adds 7 columns to payroll_items: ssnit_employee/tier2_employee/paye/ssnit_employer/tier2_employer decimal:2 default 0, payment_method string nullable, paid_at timestamp nullable; config/payroll.php with GHA 2024 rates: SSNIT employee 5.5%, Tier2 employee 5%, SSNIT employer 13%, Tier2 employer 5%, progressive PAYE bands; PayrollItem model updated with new fillable + decimal:2 casts + paid_at datetime cast; PayrollRun model adds 5 aggregate accessors: total_ssnit_employee, total_tier2_employee, total_paye, total_ssnit_employer, total_tier2_employer; PayrollService::runPayroll() rewritten to compute Ghana statutory deductions per staff, taxable_income = gross − ssnitEmployee − tier2Employee, computePaye() progressive band loop, net = gross + allowances − deductions − ssnit_employee − tier2_employee − paye, bulk insert via PayrollItem::insert; PayrollController::markPaid() validates payment_method + paid_at, updates payment_status=paid; route PATCH /payroll/{run}/items/{item}/pay under permission:payroll.create; payroll/index.blade.php: inner items table adds − Statutory column (ssnit+tier2+paye combined) + Status badge column + Actions column with Payslip link + per-row Alpine x-data Mark Paid inline form (Method select + Date input + Confirm/Cancel); Remittance Summary panel below items table showing Net Disbursement + SSNIT Emp + Tier2 Emp + PAYE→GRA + Employer Liability; payslip-pdf.blade.php: Statutory Deductions section with named SSNIT 5.5%/Tier2 5%/PAYE rows, Other Deductions section only if manual deductions > 0, net pay box updated, greyed Employer Contributions informational block; paid_at + payment_method displayed on payslip when paid; action required: php artisan tenants:migrate) (2 tenant migrations: class_registers UUID, lesson_plans UUID; ClassRegister + LessonPlan models with HasUuids; RegisterController: index (role-scoped classes/subjects via SubjectTeacherAssignment for teachers, all for admin; loads existing register entry + history; week-based lesson plan listing), store (updateOrCreate by teacher/class/section/subject/date), exportPdf (monthly dompdf A4); LessonPlanController: store (updateOrCreate, snaps week_start to Monday), update, destroy (ownership check); two-tab view (Class Register + Lesson Plans) with Alpine registerPage() — cascading class→section for both register filter and lesson plan modal; weekly navigation with prev/next links; lesson plan cards with inline edit via Alpine x-data={editing}; register filter loads entry form + history table; monthly PDF export via GET /register/pdf/{staff}/{month}; register.view/create/manage permissions; teacher gets register.view+create; sidebar Register nav item) (leave_requests UUID migration; LeaveRequest model with HasUuids, leave_type_label + leave_days accessors; LeaveController: index, store, approve, reject; two-tab view: My Requests (submit form + history list) + All Requests admin tab with inline reject form powered by Alpine; LeaveRequestSubmitted notification → school_admin on new request; LeaveRequestDecided notification → staff user on approve/reject; leave.view + leave.manage permissions seeded; school_admin gets both via \$all, teacher + accountant get leave.view; StaffAttendance integration: AttendanceController::staff() pre-marks on_leave from approved LeaveRequest records overlapping the selected date; SaveStaffAttendanceRequest allows on_leave status; On Leave button + badge added to staff attendance view; Leave nav item in sidebar after Payroll; 4 routes under permission:leave.view) (FinancialSummaryService::build(); income from fee_payments joined with fee_structures filtered by term or academic_year; expenses filtered by date range within year/term; both grouped by category + monthly trend; ReportController extended with FinancialSummaryService injection + financial data loading when tab=financial + financialPdf() method; Financial Summary tab added to reports/index.blade.php with Alpine financialYearId/financialTermId state + cascading year→term filter + Chart.js grouped bar chart for monthly income vs expenses + 3-card summary + income-by-fee-item + expense-by-category tables + Export PDF link; financial-pdf.blade.php A4 landscape self-contained dompdf with summary cards, two-column breakdown tables, monthly trend table; route GET /reports/financial/pdf) (3 tenant migrations: salary_structures, payroll_runs uuid, payroll_items uuid; SalaryStructure/PayrollRun/PayrollItem models; HasOne salaryStructure() added to Staff; PayrollService::runPayroll() wrapped in DB::transaction, bulk-inserts payroll items with manually generated UUIDs; PayrollService::logAsExpense() creates Expense record under Salaries category; PayrollController: index, updateSalaryStructure PATCH, runPayroll POST, downloadPayslip GET, logAsExpense POST; payroll/index.blade.php with Alpine two-tab layout: Salary Structures table with per-row edit modal + Payroll Runs table with collapsible staff items and per-item payslip download; payslip-pdf.blade.php self-contained dompdf with school header, itemised earnings/deductions tables, net pay highlighted box; permissions payroll.view/create/edit added to all list + school_admin + accountant roles; 5 routes in routes/tenant.php; Payroll nav item in sidebar-nav.blade.php; action required: run php artisan tenants:migrate)
**Next:** Feature 52 — Outbound Webhook System

---

## Progress

### Phase 1 — Foundation

- [x] 01 Central App — Landing, Pricing, Register School UI
- [x] 02 Multi-Tenancy Setup (stancl/tenancy, wildcard subdomains)
- [x] 03 Tenant Provisioning (DB creation, migrations, School Admin account)
- [x] 04 Auth & Roles (Breeze + spatie/laravel-permission)
- [x] 05 Central + Tenant Database Schema

### Phase 2 — School Setup & Core Records

- [x] 06 Tenant Dashboard — Full UI (sidebar layout, stats, recent activity)
- [x] 07 Academic Year, Classes, Sections, Subjects
- [x] 07b School Profile & Branding
- [x] 07c Terms Schema — `terms` table added; `exams` and `fee_structures` migrated from free-text `term`+`academic_year_id` to `term_id` FK; Terms management UI added to Academic Year settings page; status computed from `is_published`+dates
- [x] 08 Student Management — Full UI + CRUD + Bulk Import
- [x] 09 Staff Management — Full UI + CRUD
- [x] 09b Custom Roles & Permissions
- [x] 09c Account Settings — "My Account" page (Profile + Password, avatar upload, topbar dropdown with avatar)

### Phase 3 — Attendance & Timetable

- [x] 10 Daily Attendance — Full UI + Save Logic
- [x] 11 Timetable / Routine Builder

### Phase 4 — Exams & Report Cards

- [x] 12 Exam Scheduling + Marks Entry — Full UI
- [x] 13 Grading Scale + Report Card Generation (PDF)

### Phase 5 — Fees & Payments

- [x] 14 Fee Structure Setup — Full UI + Billing Cycle (Per Term / Annual)
- [x] 15 Fee Collection (Cash) — Full UI + Save Logic
- [x] 16 Paystack Online Payment Integration
- [x] 17 Receipts (PDF) + Due/Overdue Tracking + Term Bill PDF (two copies per A4, billing_cycle support)

### Phase 6 — Communication & Public Page

- [x] 18 Announcements / Notice Board
- [x] 19 Auto-Generated School Public Page

### Phase 7 — Reports & Super Admin

- [x] 20 Attendance & Fee Reports (exportable)
- [x] 21 Super Admin Dashboard — Manage Tenants & Subscriptions + Impersonation (session-based, audit-logged, 1-hour expiry)
- [x] 22 Custom Domain Support

### Phase 8 — Platform Foundation (MVP Gaps)

- [x] 23 Queue Worker + Horizon Setup
- [x] 24 Grading Scale Configuration Per School
- [x] 25 Student Academic Promotion Engine
- [x] 26 Tenant Onboarding Wizard
- [x] 27 Email Notification System
- [x] 28 Rate Limiting & Security Hardening
- [x] 29 Audit Log
- [x] 30 Error Tracking & Health Checks
- [x] 31 Automated Testing Suite (PestPHP)
- [x] 31b Extended Test Coverage — High-Priority Features (20 PestPHP test files + 6 factories: StaffTest, TimetableTest, LeaveTest, SubjectTeacherAssignmentTest, PaystackTest, ReceiptTest, TranscriptTest, ApiTest, ExpenseTest, FeeDiscountTest, AssignmentTest, ParentPortalTest, StudentPromotionTest, NotificationTest, PayrollTest, FinancialSummaryTest, PayrollTaxTest [Unit], GradingScaleTest [Unit], ExamAnalyticsServiceTest [Unit], SuperAdminTest [Central]; factories: LeaveRequestFactory, AssignmentFactory, ExpenseFactory, SalaryStructureFactory, PayrollRunFactory, PayrollItemFactory; PaystackService mocked via ReflectionProperty on private readonly GuzzleHttp Client; SuperAdminTest tests auth guard, dashboard listing, tenant toggle, mark-paid, cycle-end auto-expiry; no new migrations)

### Phase 9 — Growth Features

- [x] 32 Subject-Teacher Assignments (subject_teacher_assignments table; add/remove on staff profile; attendance + exam marks scoped to assigned classes/subjects for teachers)
- [x] 33 Staff Leave Management
- [x] 34 Parent Portal (parent_student pivot many-to-many; admin creates/links/unlinks parent accounts on student profile; parent role gets dedicated /my-children portal with child selector, fee status, attendance summary, published exam results; /dashboard and /fees redirect parents to portal; "My Children" sidebar nav for parent role)
- [x] 35 Homework & Assignment Management (assignments + assignment_submissions tables; AssignmentController + SubmissionController; 5 new permissions seeded per role; multi-role /assignments view — teacher/admin CRUD + grading modal, student Pending/Submitted/Overdue tabs with inline submit form; sidebar nav item; dashboard badges for teachers/students)
- [x] 36 Disciplinary & Behavior Tracking
- [x] 37 Targeted Announcements & Notification Centre
- [x] 38 Expense & Budget Management
- [x] 39 Scholarship & Fee Waiver Management
- [x] 40 Academic Performance Analytics
- [x] 41 Attendance Analytics & Chronic Absentee Reports
- [x] 42 Online Admission Application
- [x] 43 Student Transcript Generation
- [x] 44 Multi-Currency & Locale Support
- [x] 45 Data Export, Backup & Privacy Tools
- [x] 46 REST API (Sanctum)

### Phase 10 — Competitive Advantages

- [ ] 47 Platform Self-Service Billing
- [x] 48 Payroll & Staff Salary Management
- [x] 48b Ghana Payroll Tax Compliance & Payment Tracking
- [ ] 49 Payment Plans / Installment Support
- [x] 50 Financial P&L Dashboard
- [x] 51 Teacher Class Register & Lesson Plans
- [ ] 52 Outbound Webhook System
- [ ] 53 Custom Domain Support (Complete Feature 22)
- [ ] 54 Multi-Language UI Support

---

## Decisions Made During Build

### 01 — CSS & Tailwind Setup
- Upgraded from Tailwind v3 to **Tailwind v4** (v4.3.1) using `@tailwindcss/vite` Vite plugin
- Design tokens defined via `@theme {}` in `resources/css/app.css` — no `tailwind.config.js` used for colors
- PostCSS config emptied (Tailwind v4 Vite plugin handles CSS processing; Lightning CSS includes autoprefixer)
- `@tailwindcss/forms` v0.5.x is v3-only; form inputs styled manually with design tokens
- Font: Inter (Google Fonts), loaded via `<link>` in `layouts/central.blade.php`

### 01 — Architecture Decisions
- `resources/views/layouts/central.blade.php` uses `<x-component>` slot pattern (not `@extends`)
- `resources/views/central/` subfolder created for all central app views
- `app/Http/Controllers/Central/SchoolRegistrationController.php` created; provisioning logic stubbed (wired in Phase 1.3)
- `routes/web.php` now holds all central routes; Breeze auth routes still included via `require __DIR__.'/auth.php'`
- Sitemap generated via a Blade view (`central/sitemap.blade.php`) returned as `application/xml`
- Robots.txt served via inline route closure

### 01 — UI Patterns Established
- Dashboard preview on landing page is an HTML/CSS mockup (no real screenshot yet — will be replaced after Phase 2 dashboard is built)
- Subdomain live preview uses Alpine.js `x-data` computed property; strips invalid chars client-side (server still validates)
- FAQ uses Alpine.js accordion with Schema.org `FAQPage` markup for featured snippets
- Landing hero uses CSS gradient text (`-webkit-background-clip: text`) — only on the main H1 gradient span
- Pricing table uses a `grid grid-cols-4` layout with alternating row backgrounds (`bg-surface-secondary` on odd rows)

---

### 02 — Multi-Tenancy Setup
- stancl/tenancy v3 configured with **subdomain-based** tenant identification (`InitializeTenancyBySubdomain`)
- Custom `App\Models\Central\Tenant` and `App\Models\Central\Domain` models extend stancl base models
- `central` DB connection added to `config/database.php`; `DB_CONNECTION=central` in `.env`
- `config/tenancy.php`: `central_connection` hardcoded to `'central'`; central_domains includes `schoolflow.test`, `www.schoolflow.test`, `schoolflow.com`, `www.schoolflow.com`
- Central migrations ran on `schoolflow` DB (XAMPP MySQL): users, tenants, domains, personal_access_tokens, etc.
- Tenant database created automatically on `Tenant::create()` via `TenantCreated` event → `CreateDatabase + MigrateDatabase` pipeline
- `database/migrations/tenant/` directory created for future tenant-scoped migrations
- Verified: `demo` tenant + `demo.schoolflow.test` domain created via tinker; `tenantdemo` DB auto-provisioned
- `APP_NAME=SchoolFlow`, `APP_URL=http://schoolflow.test` set in `.env`

---

### 03 — Tenant Provisioning

- `App\Services\TenantProvisioningService` created — `provision()` returns `['success', 'data', 'error']` shape per code-standards
- `Tenant::create()` fires `TenantCreated` → `CreateDatabase + MigrateDatabase` synchronously (via `TenancyServiceProvider` pipeline)
- On failure, `$tenant->delete()` fires `TenantDeleted` → `DeleteDatabase` for rollback
- `buildTenantDomain()` derives the base host from `APP_URL` — works for both `schoolflow.test` (dev) and `schoolflow.com` (prod)
- `App\Models\Tenant\User` created at `app/Models/Tenant/User.php` — extends `Authenticatable`, uses `HasUuids`, no explicit `$connection` (stancl switches default connection inside `$tenant->run()`)
- `database/migrations/tenant/2026_06_13_000001_create_tenant_users_table.php` — only migration needed now; full schema in Phase 05
- **Existing demo tenant does not have the users table** — run `php artisan tenants:migrate` to apply to existing tenants
- `StoreSchoolRegistrationRequest` created — validation moved out of controller per code-standards
- `SchoolRegistrationController` updated: `final class`, constructor injection, `store()` delegates to service
- Subdomain collision check is in the service (domain uniqueness on `domains` table) — returns error shown on the `subdomain` field
- Success redirect goes to `{subdomain}.{APP_HOST}/login` — flash message won't survive cross-domain (session-scoped); login page wired in Phase 04

---

### 04 — Auth & Roles

- `config/auth.php` users provider switched to `App\Models\Tenant\User` — stancl's DB switch makes this work naturally per tenant
- `require __DIR__.'/auth.php'` removed from `routes/web.php` — central Breeze auth routes were unused; Super Admin auth added in Phase 7
- Tenant auth routes in `routes/tenant.php`: `GET /login`, `POST /login`, `POST /logout`, `GET /dashboard` — all named `tenant.*`
- `App\Http\Controllers\Tenant\Auth\AuthenticatedSessionController` — reuses Breeze `LoginRequest` (calls `Auth::attempt()` on the tenant-switched connection)
- Redirect after login: `route('tenant.dashboard')` via `redirect()->intended()`
- `spatie/laravel-permission` config published to `config/permission.php` — `teams: false`, cache 24h
- `database/migrations/tenant/2026_06_13_000002_create_permission_tables.php` — custom migration with `string` type for `model_id` columns (not `unsignedBigInteger`) to match UUID primary keys on `users`
- `App\Models\Tenant\User` — `HasRoles` trait added
- `TenantProvisioningService` — seeds 5 fixed roles (`school_admin`, `teacher`, `accountant`, `student`, `parent`) and assigns `school_admin` to the School Admin user inside `$tenant->run()`
- `EnsureUserHasRole` middleware registered as `has_role`; spatie's built-in `role`, `permission`, `role_or_permission` also registered in `Kernel.php`
- Tenant layout: `resources/views/layouts/tenant.blade.php` (sidebar + topbar skeleton — refined in Phase 06)
- Tenant guest layout: `resources/views/layouts/tenant-guest.blade.php` (login page wrapper)
- Sidebar nav: `resources/views/components/sidebar-nav.blade.php` — role-gated nav items; unbuilt routes render as disabled spans
- Dashboard placeholder: `resources/views/tenant/dashboard.blade.php` — replaced in Phase 06

---

### 05 — Central + Tenant Database Schema

**Tenant migrations** (`database/migrations/tenant/`):
- `000001` users — already created in Phase 03
- `000002` permission tables (spatie roles/permissions/pivots)
- `000003` school_profile (logo, address, contact, short description)
- `000004` academic_years (name, start/end dates, is_current flag)
- `000005` school_classes (name, order)
- `000006` sections (name, class_id FK)
- `000007` subjects (name, code)
- `000008` students (admission_no unique, class/section FK, guardian info, status)
- `000009` staff (user_id FK, role_title, status)
- `000010` attendances (student_id + date unique, status, marked_by FK)
- `000011` timetables (class+section+day+period unique, teacher FK, times)
- `000012` exams (name, term, academic_year_id, status)
- `000013` exam_results (exam+student+subject unique, marks, grade)
- `000014` fee_structures (class+term+item+year, amount, due_date)
- `000015` fee_payments (student+fee_structure FK, amount, status, paystack_ref, paid_at)
- `000016` announcements (title, body, posted_by FK, is_public flag)

**Central migration** (`database/migrations/`):
- `subscription_plans` — tenant_id FK, plan_name, status, renews_at

**Action required:** Run `php artisan migrate` (central) then `php artisan tenants:migrate` (all tenant DBs) to apply everything.

---

### 06 — Tenant Dashboard — Full UI

- Dashboard route `GET /dashboard` is a single route (`DashboardController::index`) — no role branching in routing; widget visibility is controlled by permission checks in the view (`@if($can['settings'])`, etc.)
- Four permission tiers render different dashboard layouts: school_admin (settings.manage) → full view with setup checklist + 4 stat cards + 3 charts; teacher (attendance.view + exams.view) → 2 stat cards + attendance chart; accountant (fees.view) → 2 fee cards + fee line chart; student/parent → simplified summary + announcements list
- Charts use **Chart.js 4.4.0 via CDN** (`cdn.jsdelivr.net`) loaded synchronously in `@stack('head')` — Alpine.js `x-init` initializes charts after mount
- `routes/tenant.php` fully restructured with `->name('tenant.')` prefix on the domain group + permission middleware on each protected group — resource routes (`students`, `staff`, `exams`) automatically get `tenant.students.index` etc. names
- Sidebar switched from role-array check (`hasRole([...])`) to `$user->can($permission)` — `null` permission = visible to all authenticated users (Dashboard, Announcements)
- `TenantProvisioningService` now seeds all 25 permissions + 5 roles with default permission sets inside `$tenant->run()` — called via the new private `seedPermissions()` method
- Stub controllers created for all 10 non-dashboard tenant routes (Student, Staff, Attendance, Timetable, Exam, Fee, Announcement, Report, RolesPermissions, CustomDomain) — each returns `tenant.coming-soon` view; PublicPageController stub returns `tenant.public-page`
- **Action required for existing tenants:** existing tenant DBs (e.g. `tenantdemo`) do not have permissions seeded. Run `php artisan tinker` and call `TenantProvisioningService::seedPermissions()` inside `$tenant->run()`, or delete and re-provision the demo tenant.

---

### 07 — Academic Year, Classes, Sections, Subjects

**Models** created in `app/Models/Tenant/`: `AcademicYear`, `SchoolClass` (table: `school_classes`), `Section`, `Subject`.

**Controllers** created in `app/Http/Controllers/Tenant/`:
- `AcademicYearController` — CRUD + `setCurrent()` (DB transaction: clears all is_current, sets one)
- `SchoolClassController` — CRUD; auto-increments `order` on create if not provided
- `SectionController` — `store()` nested under class route, `destroy()` flat; redirects back with `?class_open={id}` to re-open the Sections modal
- `SubjectController` — CRUD

**Form Requests** in `app/Http/Requests/Tenant/`: `StoreAcademicYearRequest`, `UpdateAcademicYearRequest`, `StoreSchoolClassRequest`, `UpdateSchoolClassRequest`, `StoreSectionRequest`, `StoreSubjectRequest`, `UpdateSubjectRequest`.

**Routes** (`routes/tenant.php`): All settings routes live under the `permission:settings.manage` middleware group. New routes added: `/settings` (redirect), `/settings/academic-year` (CRUD), `/settings/classes` (CRUD), `/settings/classes/{schoolClass}/sections` (POST), `/settings/sections/{section}` (DELETE), `/settings/subjects` (CRUD).

**Views** (`resources/views/tenant/settings/`):
- `academic-year.blade.php` — table with Set as Current / Edit / Delete; Add & Edit via shared Alpine.js modal
- `classes.blade.php` — table; Add Class modal; Manage Sections modal (lists existing sections as removable chips + inline add form); re-opens automatically when `?class_open={id}` is set in URL after section add/delete; class data passed as JSON for Alpine
- `subjects.blade.php` — table with Edit / Delete; Add & Edit via shared Alpine.js modal

**Settings sub-nav** (Academic Year | Classes & Sections | Subjects) rendered inline at top of each settings page, no shared partial needed.

**Sidebar** (`components/sidebar-nav.blade.php`): Settings item added after Reports with `settings.manage` permission. Added `activeRoute` field to nav items so Settings highlights for all `tenant.settings.*` routes.

**DB unique constraints** (added 2026-06-14 via migration `000004`):
- `school_classes.name` — unique index (`school_classes_name_unique`)
- `school_classes.order` — unique index (`school_classes_order_unique`)
- `sections(class_id, name)` — composite unique index (`sections_class_id_name_unique`)
- `subjects.name` — unique index (`subjects_name_unique`)
- Migration deduplicates any existing duplicates before adding indexes (renames conflicts as "Name (2)", etc.)
- Pre-existing duplicate "Primary 6" on tenant `936d55b2` was renamed to "Primary 6 (2)"

**Form Request validation for uniqueness:**
- `StoreSchoolClassRequest` / `UpdateSchoolClassRequest`: name uses `Rule::unique` with message "A class named ':input' already exists."; order uses a closure with message "Order N is already used by 'ClassName' — choose a different order." (Update ignores current class ID)
- `StoreSectionRequest`: name unique per class_id using `Rule::unique(...)->where('class_id', ...)` with message "This class already has a section named ':input'."
- `StoreSubjectRequest` / `UpdateSubjectRequest` (new): name uses `Rule::unique` with message "A subject named ':input' already exists." `SubjectController::update` now uses `UpdateSubjectRequest` instead of `StoreSubjectRequest`.
- `SubjectController::update` switched from `StoreSubjectRequest` to `UpdateSubjectRequest` to correctly ignore the current subject on edit.

**Error display:** Both classes and subjects modals re-open automatically on validation failure using hidden `_class_mode`/`_class_id` and `_subject_mode`/`_subject_id` sentinel fields plus `old()` to restore form state. Section add form error shown inline above the section name input. `@error()` directives added to all affected form fields.

**Dashboard checklist** (`DashboardController`): Now checks live DB — `AcademicYear::exists()`, `SchoolClass::exists()`, `Subject::exists()`. Pending checklist items with links are now clickable anchors with a chevron icon. School profile check uses `DB::table('school_profile')->exists()`.

**Sections/Classes contract**: A class with no sections is a single implicit group — anywhere class/section is selected (students, attendance, timetable), the section selector is hidden or shows "N/A" if the chosen class has no sections. This is enforced in consumer views (Phase 08+).

**Subjects-to-classes linking**: Not added yet — no pivot table in the migrations. Subject-class assignments are deferred to timetable/exam mark entry (Phase 3/4) where the teacher selects a subject for a specific class.

---

### 08 — Student Management

**Models** created: `app/Models/Tenant/Student.php` — relationships to `SchoolClass`, `Section`, `User`.

**Migration fix**: Created `2026_06_14_000001_make_students_section_id_nullable.php` — `section_id` is nullable because classes with no defined sections are treated as a single implicit group (no section row exists for those students).

**Admission number format**: `{YEAR}/{SEQUENCE}` e.g. `2026/0001`. Auto-generated in `StudentController::generateAdmissionNumber()` using the highest existing admission_no for the current year. Stored on create; never editable.

**Controllers** updated: `app/Http/Controllers/Tenant/StudentController.php` — full CRUD (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`) + `import()` (CSV via maatwebsite/excel) + `downloadTemplate()` (returns inline CSV).

**Form Requests** in `app/Http/Requests/Tenant/`: `StoreStudentRequest` (requires `students.create`), `UpdateStudentRequest` (requires `students.edit`), `ImportStudentsRequest` (requires `students.create`).

**Import class**: `app/Imports/StudentImport.php` — implements `ToCollection` + `WithHeadingRow`. Looks up class by name, then section by name within class. Errors collected per-row and surfaced as a flash message (first 5 shown). `imported` count returned for success/partial-success messaging.

**Routes** (`routes/tenant.php`): Students group split into 4 permission tiers: `students.view` (index, template download, show), `students.create` (create, store, import POST), `students.edit` (edit, update), `students.delete` (destroy). Template and import POST are registered before `{student}` wildcard to prevent route collision.

**Views** (`resources/views/tenant/students/`):
- `index.blade.php` — table with Admission No, Name, Class, Section (column hidden if no class has sections), Guardian Contact, Status badge. Filter bar: search, class select, section select (appears only when a class with sections is selected). Import modal with file upload + template download link.
- `create.blade.php` — three-card layout (Personal, Academic, Guardian). Section dropdown appears via Alpine.js only when selected class has sections (`x-show="currentSections.length > 0"`). Admission number field is read-only with format hint.
- `edit.blade.php` — same three-card layout as create; Status field included; Alpine pre-populates class/section from existing student data.
- `show.blade.php` — profile header (avatar initial, name, admission no, class/section/status badges, edit/delete actions). Four detail cards: Personal, Guardian, Academic, plus read-only placeholder cards for Attendance (Phase 3), Exam Results (Phase 4), Fee Status (Phase 5).

**Section conditional rule**: Section dropdown in create/edit forms is `x-show="currentSections.length > 0"` — visible only after a class with sections is selected. Section column in the index table is only rendered if at least one class has sections (`$anyClassHasSections`). Section value in list/profile shows "—" for students with no section_id.

**Action required**: Run `php artisan tenants:migrate` to apply the `section_id` nullable migration to all existing tenant databases.

---

---

### 09 — Staff Management

**Models**: `app/Models/Tenant/Staff.php` — `HasUuids`, fillable: `user_id`, `full_name`, `role_title`, `phone`, `photo_path`, `status`. `user()` BelongsTo `Tenant\User`.

**Controllers**: `app/Http/Controllers/Tenant/StaffController.php` — full CRUD (`index`, `create`, `store`, `show`, `edit`, `update`, `destroy`). `store()` and `update()` run in a `DB::transaction` that creates/updates both the `users` row and the `staff` row together.

**User account creation**: Each staff member has a linked `User` record created at the same time. The `store()` method creates the User (with `name`, `email`, `password`, `role` columns) and then calls `$user->assignRole($systemRole)` to set the spatie role. `destroy()` deletes the User first — the FK `onDelete('cascade')` in the `staff` migration cleans up the staff row automatically.

**Role assignment**: Staff can be assigned any spatie role except `school_admin`, `student`, `parent`. The `system_role` dropdown in the form loads from `Role::whereNotIn('name', ['school_admin','student','parent'])`. `update()` calls `$user->syncRoles([$systemRole])` to replace the old role.

**Password on edit**: The edit form has optional `new_password` / `new_password_confirmation` fields. If blank, the password is not changed. `UpdateStaffRequest` uses `nullable` + `required_with:new_password` rules.

**Email uniqueness**: `StoreStaffRequest` uses `unique:users,email`. `UpdateStaffRequest` uses `Rule::unique('users','email')->ignore($this->route('staff')?->user_id)` to exclude the current user's email.

**Routes** (`routes/tenant.php`): Staff group split into 5 permission tiers: `staff.view` (index, show), `staff.create` (create, store), `staff.edit` (edit, update), `staff.delete` (destroy). Literal `/staff/create` registered before `{staff}` wildcard to avoid route conflicts.

**Views** (`resources/views/tenant/staff/`):
- `index.blade.php` — table: avatar initial, Name (link to profile), Role Title, Email, System Role badge, Status badge. Filter bar: search (name/email), status select, clear link.
- `create.blade.php` — two-card layout: Personal Information (full_name, role_title, phone, status) + Login Account (email, password, password_confirmation, system_role with link to Manage Roles).
- `edit.blade.php` — two-card layout: Personal Information + Login Account (email, optional new_password, new_password_confirmation, system_role).
- `show.blade.php` — profile header card (avatar, name, role_title, status + system role badges, Edit/Delete actions). Staff Details card (phone, status, role_title, system_role, email). Two Phase 3 placeholder cards: Assigned Classes & Subjects, Attendance Record.

**Assigned classes/subjects**: Deferred to Phase 3 (timetable). Profile shows a placeholder card.

**Action required**: Run `php artisan tenants:migrate` if the `staff` table does not yet exist in tenant DBs. The migration is `000009_create_staff_table.php`.

---

### 09b — Custom Roles & Permissions

**Controller**: `app/Http/Controllers/Tenant/RolesPermissionsController.php` — `index()`, `store()`, `update(Role)`, `destroy(Role)`. Fixed roles (`school_admin`, `teacher`, `accountant`, `student`, `parent`) are blocked from edit/delete at the controller level.

**Routes** (`routes/tenant.php`): Under `permission:settings.manage`: `GET /settings/roles` (index), `POST /settings/roles` (store), `PUT /settings/roles/{role}` (update), `DELETE /settings/roles/{role}` (destroy). Route model binding resolves `Role` from the tenant DB automatically (tenancy switches the default connection).

**View** (`resources/views/tenant/settings/roles.blade.php`):
- Settings sub-nav with Roles & Permissions as the 4th tab — also added to academic-year, classes, subjects views.
- Roles table: Role Name (with avatar icon), Permissions count, Type badge (Fixed/Custom), Actions (Edit/Delete for custom only).
- "Create Role" / "Edit Role" shared modal (single `<form>` with `:action` Alpine binding and `:value="mode === 'edit' ? 'PUT' : ''"` on a hidden `_method` field — empty string is falsy in PHP so Laravel skips the method override for add mode).
- Permission matrix inside the modal: 8 modules (Students, Staff, Attendance, Timetable, Exams, Fees, Announcements, Reports) each with per-action checkboxes. Per-module "Select all / Deselect all" toggle via `toggleModule(permsArray)`.
- Checkboxes use `x-model="form.permissions"` (Alpine 3 array binding) — no manual `:checked` / `@change` needed.
- Info card below the table explains how roles wire to the staff form.

**Staff form wiring**: No code change needed — `StaffController::create()` and `edit()` already query `Role::whereNotIn('name', ['school_admin','student','parent'])`, which automatically includes any custom roles created here.

**Permission validation**: `store()` and `update()` validate that submitted permission names exist in the `permissions` table (`exists:permissions,name`), so arbitrary permission names cannot be injected.

**No migration needed**: All tables (`roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`) were created in Phase 04 migration `000002`. No new tables.

---

### 10 — Daily Attendance

**Models**: `app/Models/Tenant/Attendance.php` — fillable: `student_id`, `date`, `status`, `marked_by`, `note`. `date` cast to `date`. `app/Models/Tenant/StaffAttendance.php` — same shape but with `staff_id` FK.

**Migration**: `database/migrations/tenant/2026_06_14_000002_create_staff_attendances_table.php` — separate table for staff attendance (`staff_id + date` unique constraint, FK to `staff.id`). Student attendance uses the existing `attendances` table from Phase 05.

**Form Requests**: `app/Http/Requests/Tenant/SaveAttendanceRequest.php` (authorizes `attendance.edit`, validates date is before_or_equal:today, statuses array). `app/Http/Requests/Tenant/SaveStaffAttendanceRequest.php` (same pattern without class_id).

**Controllers** updated: `app/Http/Controllers/Tenant/AttendanceController.php` — `index()`, `save()`, `report()`, `staff()`, `saveStaff()`. Save uses `Attendance::updateOrCreate(['student_id','date'], ['status','marked_by'])` — idempotent re-marking.

**Routes** added under `permission:attendance.view`: `GET /attendance/report` → `attendance.report`, `GET /attendance/staff` → `attendance.staff`. Under `permission:attendance.edit`: `POST /attendance` → `attendance.save`, `POST /attendance/staff` → `attendance.staff.save`.

**Views** (`resources/views/tenant/attendance/`):
- `index.blade.php` — filter bar (class, conditional section, date, Load button); attendance sheet with P/A/L quick-action toggle buttons (Alpine `attendanceSheet` component); "Mark all present" bulk action; Save Attendance form POST. Read-only badge fallback for users without `attendance.edit`. Empty state when no class/date selected.
- `report.blade.php` — filter bar (class, section, student, month); summary stats (4 stat cards: present/absent/late/unmarked); monthly table (each past day as a row with status badge); prev/next month navigation.
- `staff.blade.php` — date selector; staff list with P/A/L toggle buttons; "Mark all present"; Save Staff Attendance. Same Alpine pattern as student attendance.

**Alpine pattern**: `attendanceSheet(students, existingRecords)` / `staffAttendanceSheet(staff, existingRecords)` initialized with server-side JSON. Statuses keyed by UUID. Toggle: clicking an active button deselects it (sets to null — existing DB record preserved, not deleted). Hidden `<input>` per row with `:value` binding carries status into form POST.

**Teacher class filter**: Teachers see all classes for now (timetable not built yet — Phase 11 will add assignment-based filtering). The controller is structured to add the filter later without structural changes.

**CSS token added**: `--color-warning-light: #fff7ed` added to `resources/css/app.css` `@theme` block — enables `bg-warning-light` and `border-warning` utility classes for Late status buttons/badges.

**Action required**: Run `php artisan tenants:migrate` to apply the `staff_attendances` table to all existing tenant databases.

---

### 11 — Timetable / Routine Builder

**Migration**: `database/migrations/tenant/2026_06_14_000003_make_timetable_section_id_nullable.php` — makes `section_id` nullable via raw `ALTER TABLE` statement. MySQL allows NULL in FK columns without violating the FK constraint. The existing DB-level unique constraint `(class_id, section_id, day, period)` does not enforce uniqueness when `section_id` is NULL (MySQL NULL != NULL behavior), so app-level `updateOrCreate` enforces uniqueness for null-section classes.

**Model**: `app/Models/Tenant/Timetable.php` — `HasUuids`, fillable: `class_id`, `section_id`, `subject_id`, `teacher_id`, `day`, `period`, `start_time`, `end_time`. Relationships: `schoolClass()`, `section()`, `subject()`, `teacher()`.

**Controller** (`app/Http/Controllers/Tenant/TimetableController.php`):
- `index(Request)` — loads timetable grid for a class/section; uses `whereNull('section_id')` for classes without sections; passes entries keyed by `"Day-Period"` as JSON.
- `save(Request)` — validates input, checks conflict (same teacher in same day+period for a different class/section, excluding the current cell), uses `updateOrCreate` for idempotent saves, returns JSON `{ success, entry, conflict }`.
- `destroy(Timetable)` — deletes a cell entry, returns JSON.
- `teacher(Request)` — teacher's personal schedule; non-admins auto-load their own staff record; admins get a staff dropdown selector.

**Routes** (`routes/tenant.php`): Under `permission:timetable.view`: `GET /timetable` + `GET /timetable/my`. Under `permission:timetable.edit`: `POST /timetable` (save) + `DELETE /timetable/{timetable}` (destroy).

**Views** (`resources/views/tenant/timetable/`):
- `index.blade.php` — filter bar (class, conditional section); days-as-rows × periods-as-columns grid wrapped in `overflow-x-auto` for mobile scroll; filled cells show subject + teacher name in `bg-accent-muted`; empty cells show dashed border + "+" icon; Alpine `timetableGrid` component manages entries client-side; cell edits use `fetch()` POST (no page reload). Alpine store `timetableModal` shared between grid and modal panel. Conflict detection returns a warning banner (save still committed). Clear (×) button on filled cells sends DELETE. Read-only for users without `timetable.edit`.
- `teacher.blade.php` — read-only grid showing subject + class/section name for each filled cell; cells styled `bg-success-lightest`; admins see a staff dropdown selector; non-admins auto-load own schedule; footer shows period count + class count summary.

**Period configuration**: 8 periods (1–8), Mon–Fri, hardcoded for MVP. Times (`start_time`/`end_time`) columns exist in DB for future use but not surfaced in UI.

**Conflict detection**: Same teacher assigned to any other class/section in the same day+period → `conflict` message returned in JSON. Save still succeeds; conflict shown as a dismissible orange banner on the grid.

**Alpine pattern**: `timetableFilter` manages class/section filter dropdown state. `timetableGrid(entriesData, subjects, staff, classId, sectionId)` manages entries in a client-side map keyed by `"Day-Period"`, handles cell open/clear, communicates with `Alpine.store('timetableModal')`. Store pattern used so modal (rendered outside the grid `x-data` scope) can access and update grid state via `_gridRef`.

**Action required**: Run `php artisan tenants:migrate` to apply the `section_id` nullable migration to all existing tenant databases.

---

### 12 — Exam Scheduling + Marks Entry

**Models**: `app/Models/Tenant/Exam.php` — `HasUuids`, fillable: `name`, `term`, `academic_year_id`, `start_date`, `end_date`, `status`. `academicYear()` BelongsTo, `results()` HasMany. `app/Models/Tenant/ExamResult.php` — `HasUuids`, fillable: `exam_id`, `student_id`, `subject_id`, `marks`, `grade`, `remarks`. Static `computeGrade(float)` returns A/B/C/D/F using the default scale (70/60/50/40 thresholds) — same scale as `ui-rules.md` grade colors.

**Form Requests**: `app/Http/Requests/Tenant/StoreExamRequest` (authorizes `exams.create`), `UpdateExamRequest` (authorizes `exams.edit`), `SaveMarksRequest` (authorizes `exams.edit`, validates `marks.*` as `nullable|numeric|min:0|max:100`).

**Controller** (`app/Http/Controllers/Tenant/ExamController.php`): `index()`, `store()`, `update(Exam)`, `destroy(Exam)`, `marks(Request)`, `saveMarks(SaveMarksRequest)`. `saveMarks()` uses `ExamResult::updateOrCreate` on the `[exam_id, student_id, subject_id]` triple — blank marks deletes the existing record.

**Teacher restriction**: `marks()` and `saveMarks()` check if the authenticated user has `settings.manage` (admin); if not, they verify the user's `Staff` record has a `Timetable` entry for the requested `class_id + section_id + subject_id` combination. `canManageAll` is passed to the view so Alpine can filter class/subject dropdowns for teachers. The teacher's assignments are passed as a flat JSON array `[{class_id, section_id, subject_id}]`.

**Routes** (`routes/tenant.php`): `GET /exams` (index, exams.view), `GET /exams/marks` (marks page, exams.view), `POST /exams` (store, exams.create), `PUT /exams/{exam}` (update, exams.edit), `POST /exams/marks` (saveMarks, exams.edit), `DELETE /exams/{exam}` (destroy, exams.delete). Literal `/exams/marks` registered before any future `{exam}` wildcard.

**Views** (`resources/views/tenant/exams/`):
- `index.blade.php` — exam list table; Status badge (upcoming/ongoing/completed/published); Add/Edit via shared Alpine modal (`examsPage` component); "Enter Marks" link pre-fills `?exam_id=` on marks page; Delete via form POST with confirm dialog.
- `_form.blade.php` — shared form partial (name, term+status 2-col, academic year dropdown via Alpine template, start/end date 2-col). Used inside both add and edit modal `<form>` blocks.
- `marks.blade.php` — filter bar (exam, class, section conditional, subject); marks entry table wrapped in `overflow-x-auto` (`min-width:600px`); marks input (0–100, step 0.5); live grade badge + progress bar computed by `marksSheet` Alpine component; "Clear All" button; Save Marks form POST. Teacher mode: class/subject dropdowns filtered by `assignments` array via Alpine. Read-only badge fallback for users without `exams.edit`.

**Grade color invariant**: Grade A → `bg-success-lightest text-success-foreground`, B → `bg-info-lightest text-info-foreground`, C → `bg-warning-light text-warning` (inline `#FFF7ED` bg), D/F → `bg-error-light text-error`. Same tokens as `ui-tokens.md` grade color table.

**Action required**: No new migrations needed — `exams` and `exam_results` tables were already created in Phase 05 (migrations `000012` and `000013`). Run `php artisan tenants:migrate` only if those migrations haven't been applied yet.

---

### 13 — Grading Scale + Report Card Generation (PDF)

**Config**: `config/schoolflow.php` created with `default_grading_scale` array — 5 bands: A (70–100 Excellent), B (60–69 Very Good), C (50–59 Good), D (40–49 Pass), F (0–39 Fail). Matches the thresholds already in `ExamResult::computeGrade()` and `ui-rules.md`.

**ReportCardService** (`app/Services/ReportCardService.php`):
- `build(Exam, Student): array` — loads ExamResults for the student+exam, applies `config('schoolflow.default_grading_scale')`, computes overall average and average grade/remark. Returns data array including results collection (with bar_width, bar_color per row), student, exam, scale.
- `generatePdf(Exam, Student): string` — renders `tenant.exams.report-card-pdf` via `Pdf::loadView()` (barryvdh/laravel-dompdf), saves to `storage/{tenantId}/report-cards/{student_id}/{exam_id}.pdf` on the local disk, returns absolute path.
- Both methods have `try/catch`; `generatePdf` rethrows after logging so the controller can show the user a safe error.

**ReportCardController** (`app/Http/Controllers/Tenant/ReportCardController.php`):
- `preview(Request)` — filter bar page. Admins see all exams + a class/section/student dropdown chain. Students/parents see only published exams; their student record is auto-resolved via `Student::where('user_id', Auth::id())`.
- `download(Request)` — same access checks as preview, then calls `generatePdf()` and returns `response()->file()` with a sanitized filename.
- Injected via constructor: `ReportCardService`.

**Exam publish action** (`ExamController::publish(Exam)`):
- Route: `PATCH /exams/{exam}/publish` (requires `exams.edit`).
- Simply sets `status = 'published'` with a try/catch. One-way action (no "unpublish" in MVP — admin can revert via the Edit modal by changing status back to completed/ongoing).

**Routes added** in `routes/tenant.php` (all under `permission:exams.view`):
- `GET /exams/report-card` → `ReportCardController::preview` → `tenant.exams.report-card`
- `GET /exams/report-card/download` → `ReportCardController::download` → `tenant.exams.report-card.download`
- Under `permission:exams.edit`: `PATCH /exams/{exam}/publish` → `ExamController::publish` → `tenant.exams.publish`
- All literal paths registered before the `{exam}` wildcard to avoid route conflicts.

**Views** (`resources/views/tenant/exams/`):
- `report-card.blade.php` — preview page with filter bar (exam + class/section/student for admins, auto-filled student for students/parents), inline report card table (subject, marks, grade badge, remark, progress bar), overall average tfoot row, grading scale key footer. Download PDF + Print buttons in card header.
- `report-card-pdf.blade.php` — self-contained HTML/CSS for dompdf (DejaVu Sans, A4 portrait). Includes: school header, student info grid, results table with grade badges + inline progress bars, grading scale table, signature area. Uses only inline styles (no external CSS).

**Exams index updates** (`exams/index.blade.php`):
- Flash messages added (success/error) — were previously missing.
- "Report Cards" top button added next to "Enter Marks".
- Per-row: "Report Cards" link appears for `completed`/`published` exams. "Publish" button (with confirm dialog) appears for admins when status is not yet `published`. Both appear in the same actions column.

**Access control invariant**: Students/parents can never see unpublished results — enforced in both `ReportCardController::preview` and `::download` via the `$exam->status !== 'published'` check, independently of the query-time filter on the exam list. Double-layer protection.

**PDF storage path**: `storage/{tenantId}/report-cards/{student_id}/{exam_id}.pdf` on `local` disk. If the directory doesn't exist, `Storage::disk('local')->makeDirectory()` creates it before `put()`. No public URL is exposed — download goes via the controller which streams the file.

**Action required**: No new migrations needed. `barryvdh/laravel-dompdf` (^3.1) was already in `composer.json`. Run `php artisan config:clear` to ensure `config/schoolflow.php` is picked up.

---

### 15 — Fee Collection (Cash)

**Model**: `app/Models/Tenant/FeePayment.php` — `HasUuids`, fillable: `student_id`, `fee_structure_id`, `amount`, `status`, `payment_method`, `paystack_ref`, `recorded_by`, `paid_at`. Casts: `amount` decimal:2, `paid_at` datetime. Relations: `student()`, `feeStructure()`, `recordedBy()` BelongsTo User.

**Service** (`app/Services/FeeService.php`):
- `getStudentFeeItems(Student, ?string $academicYearId, ?string $term): array` — loads fee_structures for student's class_id (filtered by year/term if provided), aggregates fee_payments per structure via a single query + groupBy, computes status per item. Returns array of `{fee_structure, paid_amount, outstanding, status, payments}`.
- `computeStatus(float $total, float $paid, ?Carbon $dueDate): string` — returns 'paid' (paid >= total), 'partial' (0 < paid < total, not overdue), 'overdue' (paid < total AND due_date is past), 'unpaid' (paid == 0, not overdue).
- `recordCashPayment(Student, FeeStructure, float $amount): array{success,data,error}` — creates FeePayment with payment_method='cash', paystack_ref=null, recorded_by=Auth::id(), paid_at=now(). Returns service result shape.

**Form Request**: `app/Http/Requests/Tenant/RecordPaymentRequest.php` — authorizes `fees.create`, validates `student_id` (uuid, exists:students), `fee_structure_id` (uuid, exists:fee_structures), `amount` (numeric, min:0.01).

**Controller** (`app/Http/Controllers/Tenant/FeeController.php`): Constructor injects `FeeService`. `index(Request)` dispatches: has `fees.view` → `adminView()`, else → `studentSelfView()`. `pay(RecordPaymentRequest)` computes outstanding, caps amount if above outstanding, calls service, redirects back to `?student_id&academic_year_id` with flash. CRUD redirects updated to `/fees?tab=structure`.

**Routes** (`routes/tenant.php`): `GET /fees` moved out of `permission:fees.view` group — now accessible to all authenticated users (controller dispatches). `POST /fees/pay` added under `permission:fees.create`.

**Sidebar** (`components/sidebar-nav.blade.php`): Fees permission changed from `fees.view` to `null` — now visible to all authenticated users including students/parents.

**Views**:
- `resources/views/tenant/fees/index.blade.php` — **redesigned** with two Alpine-managed tabs: "Fee Collection" (default) and "Fee Structure". Tab state initialized from `?tab=` URL param via `x-init="initTab('{{ $activeTab }}')"`. Fee Collection tab: search card (student name/adm no + academic year + term selects), search results list (click → loads student's fees), student info bar (name, class, total owed/paid/outstanding), fee items table with status badges + "Record Payment" button per outstanding item, Record Payment modal. Fee Structure tab: existing CRUD table + Add/Edit modal (moved from prior Feature 14 view). Three Alpine components: `feesAdminPage` (tab state), `feeStructureTab` (CRUD modal), `paymentModal` (Record Payment modal).
- `resources/views/tenant/fees/my-fees.blade.php` — **new** student/parent read-only view. Shows student summary header card (avatar, name, admission no, class, academic year), three-stat strip (total owed / paid / outstanding), fee items table (overflow-x-auto, Term/Paid/Outstanding columns hidden on small screens), totals tfoot row, info banner showing outstanding balance with instruction to contact school office. Empty state if no student record linked to user.

**Status computation invariant**: 'paid' takes precedence, then 'overdue' (paid < total AND due_date past — applies to both unpaid AND partial-paid items past due), then 'partial' (some paid, not due), then 'unpaid'. Computed dynamically from payment sums — never stored as an aggregate.

**Accountant permission note**: `pay()` requires `fees.create`. The default `accountant` role was seeded with only `fees.view`. To allow accountants to record payments, grant them `fees.create` via the Roles & Permissions settings page (or update TenantProvisioningService seeding for new tenants).

**No new migrations needed**: `fee_payments` table was created in Phase 05 (migration `000015`).

---

### 16 — Paystack Online Payment Integration

**Schema correction** (migration `000005_remove_status_from_fee_payments`): Dropped the stored `status` column from `fee_payments` — per architecture.md, status is computed from payment sums at runtime, never persisted. `FeePayment::$fillable` and `FeeStatusService::recordCashPayment` both updated to remove `status`.

**FeeStatusService** (`app/Services/FeeStatusService.php`): New service replacing `FeeService` (now dead code). Carries the same three methods — `getStudentFeeItems()`, `computeStatus()`, `recordCashPayment()`. All references in `FeeController` updated to inject `FeeStatusService` instead.

**PaystackService** (`app/Services/PaystackService.php`): Uses Guzzle (already in composer.json — no new SDK needed).
- `initializeTransaction(email, amount, callbackUrl, metadata, reference)` — POSTs to `api.paystack.co/transaction/initialize`; amount converted from major unit (GHS) to smallest unit (pesewas × 100) internally; returns `{success, authorization_url, reference, error}`.
- `verifyTransaction(reference)` — GETs `api.paystack.co/transaction/verify/{ref}`; converts amount back from pesewas; returns `{success, status, amount, metadata, error}`.
- `verifyWebhookSignature(rawPayload, signature)` — HMAC-SHA512 using the secret key; used by the webhook controller.

**config/paystack.php**: Reads `PAYSTACK_SECRET_KEY`, `PAYSTACK_PUBLIC_KEY`, `PAYSTACK_CURRENCY` (default `GHS`) from `.env`.

**PaystackWebhookController** (`app/Http/Controllers/Tenant/PaystackWebhookController.php`): Handles `POST /paystack/webhook`. Five-step flow: (1) verify HMAC signature, (2) only process `charge.success` events, (3) idempotency check (`paystack_ref` already recorded), (4) verify with Paystack API before writing anything, (5) create `fee_payments` row with `payment_method = 'paystack'`.

**FeeController updates**: Two new methods —
- `paystackCheckout()`: validates `student_id` + `fee_structure_id`, computes outstanding, calls `PaystackService::initializeTransaction()` with student email + metadata (`student_id`, `fee_structure_id`, `student_name`, `fee_item`), redirects away to Paystack authorization URL.
- `paystackCallback()`: called after Paystack redirects the user back. Checks idempotency first (webhook may have already recorded it), then falls back to `verifyTransaction()` and creates the `fee_payments` row.

**Routes** (`routes/tenant.php`):
- `POST /paystack/webhook` — outside auth group, CSRF excluded via `->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])`. Tenant resolved by subdomain (each school configures their subdomain as the Paystack webhook URL).
- `POST /paystack/checkout` — inside auth group, no permission gate (student/parent role can initiate).
- `GET /paystack/callback` — inside auth group, handles redirect back from Paystack checkout.

**my-fees.blade.php**: Added "Pay Now" column to the fee items table — visible for `unpaid`, `partial`, `overdue` items. Each "Pay Now" is a form POST to `/paystack/checkout` with hidden `student_id` + `fee_structure_id`. Updated the outstanding balance banner to say "Click Pay Now on any outstanding item to pay online via Paystack." Flash messages added (success, error, info).

**Paystack webhook URL**: Configure each tenant's Paystack webhook to `{subdomain}.schoolflow.com/paystack/webhook`. Signature is verified with `PAYSTACK_SECRET_KEY` before any payload is trusted. Run `php artisan tenants:migrate` to apply the `status` column removal. Add `PAYSTACK_SECRET_KEY`, `PAYSTACK_PUBLIC_KEY`, and optionally `PAYSTACK_CURRENCY` to `.env`.

---

### 07b — School Profile & Branding

**Model**: `app/Models/Tenant/SchoolProfile.php` — singleton table (`school_profile`), fillable: `school_name`, `logo_path`, `short_description`, `address`, `phone`, `email`, `website`.

**Controller** (`app/Http/Controllers/Tenant/SchoolProfileController.php`):
- `index()` — loads first (and only) `SchoolProfile` row, passes to view.
- `update(UpdateSchoolProfileRequest)` — upsert pattern: `SchoolProfile::first() ?? new SchoolProfile()`. If a logo file is uploaded, deletes the old file from `public` disk, stores the new one at `logos/{tenantId}/logo.{ext}`. Returns back with flash.

**Form Request**: `app/Http/Requests/Tenant/UpdateSchoolProfileRequest.php` — authorizes `settings.manage`. Logo validates as `image|mimes:jpg,jpeg,png,gif,webp,svg|max:2048`.

**Logo storage**: `Storage::disk('public')->storeAs("logos/{tenantId}", "logo.{ext}")`. URL in views: `asset('storage/' . $profile->logo_path)`. Requires `php artisan storage:link` (standard Laravel setup).

**Routes** (`routes/tenant.php`): Added `GET /settings/profile` and `POST /settings/profile` under `permission:settings.manage` middleware group.

**View** (`resources/views/tenant/settings/school-profile.blade.php`): Settings sub-nav with "School Profile" tab active. Logo upload with live Alpine.js preview (`URL.createObjectURL`). Text fields: school_name (required), short_description (textarea), address, phone, email, website. All fields have `old()` fallback and `@error()` display. Same card-based layout as other settings pages.

**Settings sub-nav**: "School Profile" tab added to all 5 settings views (`academic-year`, `classes`, `subjects`, `roles`, `school-profile`). All sub-navs now have `overflow-x-auto` and `whitespace-nowrap` on tabs to handle 5 items on mobile.

**ViewComposer** (`app/Providers/AppServiceProvider.php`): Targets `layouts.tenant` and `tenant.auth.login`. Checks `tenancy()->initialized` before querying the `school_profile` table. Falls back to `null` in any Throwable (e.g., un-migrated tenant, central-domain request). Sets `$schoolProfile` on both views.

**Sidebar logo** (`resources/views/layouts/tenant.blade.php`): If `$schoolProfile->logo_path` set → shows `<img>` (36×36 object-contain). Otherwise → gradient icon fallback. School name: `$schoolProfile?->school_name ?? config('app.name')`.

**Login page** (`resources/views/tenant/auth/login.blade.php`): Same logo/name logic — shows school logo if uploaded, gradient fallback otherwise. Title: `$schoolProfile?->school_name ?? config('app.name')`.

**PDF retrofit** (`app/Services/ReportCardService.php`): In `generatePdf()`, loads `SchoolProfile::first()`. If `logo_path` is set, reads the file from `public` disk and base64-encodes it into a data URI (dompdf cannot fetch external URLs). Passes `$schoolProfile` and `$logoBase64` to the PDF view.

**PDF header** (`resources/views/tenant/exams/report-card-pdf.blade.php`): Conditionally renders `<img src="{{ $logoBase64 }}">` (44×44px) above the school name. School name: `$schoolProfile?->school_name ?? tenant('name') ?? 'School'`.

**Dashboard checklist** (`DashboardController.php`): "School profile set up" checklist item now has `'link' => $host . '/settings/profile'` (was `null`).

**Action required**: Run `php artisan storage:link` to make uploaded logos accessible via `/storage/...` URL. Add `PAYSTACK_SECRET_KEY` / `PAYSTACK_PUBLIC_KEY` to `.env` for Paystack features. Run `php artisan tenants:migrate` if `school_profile` table doesn't exist in existing tenant DBs.

---

### 17 — Receipts (PDF) + Due/Overdue Tracking

**ReceiptService** (`app/Services/ReceiptService.php`):
- `build(FeePayment): array` — loads payment + student + fee structure + school profile, builds data array including `receiptNo` (first 10 hex chars of UUID), `logoBase64` (same base64-encode pattern as `ReportCardService`).
- `generatePdf(FeePayment): string` — renders `tenant.fees.receipt-pdf` via dompdf, saves to `storage/{tenantId}/receipts/{student_id}/{payment_id}.pdf` on the local disk, returns absolute path. Rethrows on failure after logging.

**ReceiptController** (`app/Http/Controllers/Tenant/ReceiptController.php`):
- `download(Request, FeePayment)` — authorization: users with `fees.view` can download any receipt; otherwise checks `feePayment->student->user_id === Auth::id()` (student/parent self-service). Returns file response with content-disposition `attachment`.
- Route: `GET /fees/receipt/{feePayment}` registered before the `{feeStructure}` wildcard routes to avoid conflicts.

**Receipt PDF** (`resources/views/tenant/fees/receipt-pdf.blade.php`):
- Self-contained HTML/CSS for dompdf (DejaVu Sans, A4 portrait). School logo (base64) or text-only header if `logo_path` null. Includes: status banner (PAYMENT CONFIRMED), amount box, two-column student/payment detail cards, fee breakdown table, signature area, footer note.
- Method badge: Paystack → cyan `bg-paystack-light text-info-foreground` style; Cash → grey surface badge.

**Overdue highlighting** (both views):
- Fee item rows with `status === 'overdue'` get `border-l-2 border-error bg-error-light/30` row class plus a warning triangle icon next to the fee item name.
- Due date text turns red with "· Overdue" suffix for overdue items.
- `my-fees.blade.php`: overdue items show a red "X overdue fee items" banner above the existing outstanding balance banner (overdue and outstanding banners are mutually exclusive; overdue takes priority).

**Receipt download links**:
- In the admin fee collection view (`index.blade.php`): after "Record Payment" button, iterates `$item['payments']` to render a download link per payment showing the amount.
- In the student view (`my-fees.blade.php`): same pattern — receipt link per payment in the actions column.

**Dashboard "Fees Collected This Term" stat** (`DashboardController`):
- `fees_this_term` = `FeePayment::whereHas('feeStructure', fn($q) => $q->where('term_id', $currentTerm->id))->sum('amount')` — actual money received.
- `fees_outstanding` = sum of (fs.amount − total_paid_for_that_structure) across all fee structures in the current term. Note: this is per-structure, not per-student; refined to per-student in Phase 7 Fee Reports.
- `overdue_count` = count of fee structures in current term where `due_date < today` AND some amount is still outstanding. Displayed as a red `⚠ N overdue` badge on the stat card (both admin and accountant views).
- Stat display changed from `₦{value/1000}k` format (mock) to `number_format($value, 2)` (real amounts).
- Both "Fees This Term" stat card (admin) and "Outstanding" card (accountant) now show the overdue badge when `overdue_count > 0`.

**Action required**: No new migrations needed. Run `php artisan tenants:migrate` only if `fee_structures` or `fee_payments` migrations haven't been applied yet.

---

### 18 — Announcements / Notice Board

**Migration**: `database/migrations/tenant/000016_create_announcements_table.php` — `id` (uuid PK), `title` (varchar 150), `body` (text), `posted_by` (FK→users), `is_public` (boolean, default false), timestamps.

**Model**: `app/Models/Tenant/Announcement.php` — `HasUuids`, fillable: `title`, `body`, `posted_by`, `is_public`. Cast: `is_public` boolean. Relationship: `postedBy()` BelongsTo `User` (FK `posted_by`).

**Form Requests**: `StoreAnnouncementRequest` (authorizes `announcements.create`) + `UpdateAnnouncementRequest` (authorizes `announcements.edit`). Both validate: `title` (required, string, max:150), `body` (required, string, max:5000), `is_public` (boolean).

**Controller** (`app/Http/Controllers/Tenant/AnnouncementController.php`): Full CRUD — `index()` (latest-first, eager-loads `postedBy`), `store()`, `update(Announcement)`, `destroy(Announcement)`. All mutations have try/catch + flash messages.

**Routes** (`routes/tenant.php`): `GET /announcements` (all auth users — no permission gate), `POST /announcements` (announcements.create), `PUT /announcements/{announcement}` (announcements.edit), `DELETE /announcements/{announcement}` (announcements.delete). Separate permission gates per verb — public read is intentional.

**View** (`resources/views/tenant/announcements/index.blade.php`):
- Page header: count subtitle + "Add Announcement" button (gated by `announcements.create`).
- Card-per-announcement layout (not a table — body text needs space). Card header: icon, title, date, posted-by, Public/Staff-Only badge, edit/delete actions. Card body: body text with "Read more / Show less" expand (Alpine per-card `x-data`). Truncates at 240 chars server-side with a toggle button.
- Alpine `announcementsPage(announcements)` component: `showModal`, `mode` (add/edit), `form.{id,title,body,is_public}`, `openAdd()`, `openEdit(data)`, `close()`. Dual `<form>` inside modal (one for add, one for edit) — `x-show` switches between them.
- Empty state: icon + text + "Post First Announcement" CTA.
- Flash messages: success (green) and error (red) panels.

**Dashboard Recent Activity wiring** (`DashboardController::index()`):
- Replaced mock `$activity` array with real data: latest 3 `Announcement` rows + latest 2 `FeePayment` rows, merged into a single collection sorted by `created_at` descending.
- Each entry: `{type, text, time (diffForHumans), ts (Carbon)}`.
- `$recentAnnouncements` (top 3 announcements, with `postedBy`) passed separately for the student/parent "Recent Announcements" panel — replaces hardcoded mock in that panel.

**Student/parent dashboard "Recent Announcements" panel**: Now renders real `$recentAnnouncements` (Announcement models) — shows title, `diffForHumans()`, and `Str::limit($ann->body, 120)`. Shows "No announcements yet." empty state when empty.

**is_public field**: Now active — Feature 19 `PublicPageController` queries `Announcement::where('is_public', true)` so only flagged announcements appear on the public page.

**No new migrations needed to run**: Migration `000016` was already planned in Phase 05 schema. Run `php artisan tenants:migrate` if tenant DBs pre-date this session.

---

### 19 — Auto-Generated School Public Page

**Controller** (`app/Http/Controllers/Tenant/PublicPageController.php`): `index()` loads `SchoolProfile::first()` and `Announcement::where('is_public', true)->latest()->limit(5)->get()`. Passes `$profile` and `$announcements` to the view.

**View** (`resources/views/tenant/public-page.blade.php`): Standalone self-contained HTML (no `@extends` — no sidebar or tenant layout). Sections:
- **Navbar**: sticky, `bg-surface border-b border-border h-16`. School logo (from `storage/` public disk) or blue gradient icon fallback. School name. "Login" button linking to `route('tenant.login')`.
- **Hero card**: School logo (96×96, rounded-2xl) or gradient icon (80×80). School name as `<h1>`. Short description paragraph. Inline contact strip (address / phone / email) with icon + clickable `tel:` and `mailto:` links.
- **Announcements section**: `<article>` cards per public announcement — icon badge, title, date (`d M Y`), body (truncated at 300 chars with "… Login to read more" hint). Empty state with icon when none published.
- **Contact Us card**: Separate full card below announcements listing address / phone / email / website, each with a colored icon, `<dt>/<dd>` pattern, clickable links. Only rendered if at least one contact field is set on the profile.
- **Footer**: `© {year} {school_name}` + "Powered by SchoolFlow".

**SEO** (all per-tenant, never generic):
- `<title>` = `$profile->school_name` (falls back to `tenant('name')` → `config('app.name')`)
- `<meta name="description">` = `short_description` if set; else auto-built from school name + address
- `<meta name="robots" content="index, follow">` — explicitly indexable
- OpenGraph: `og:type`, `og:url`, `og:title`, `og:description`, `og:image` (logo URL if set)
- Twitter Card: `summary_large_image` (with logo) or `summary` (without)

**robots.txt**: `GET /robots.txt` route added to `routes/tenant.php` (unauthenticated, within the tenant domain group). Returns `User-agent: * / Allow: / / Disallow: /dashboard / Disallow: /students …` — all authenticated routes disallowed, public page explicitly allowed. Named `tenant.robots`.

**Route**: `GET /` → `PublicPageController::index` → named `tenant.public`. Already existed as a stub; now wired with real data. No authentication or permission gate — fully public.

**No new migrations needed**: All data comes from `school_profile` (Feature 07b) and `announcements` (Feature 18). No new tables or migrations.

**Graceful empty states**: If `school_profile` row doesn't exist (unprovision state), all fields null-safe (`$profile?->school_name`). If no public announcements, empty-state card shown. Contact Us card is conditionally rendered only when at least one contact field is set.

---

### 20 — Attendance & Fee Reports

**AttendanceReportService** (`app/Services/AttendanceReportService.php`): `build(classId, sectionId, dateFrom, dateTo)` loads all students in class (filtered by section if provided), loads all `attendances` records in the date range, groups by `student_id`, computes present/absent/late counts + `percent_present` (based on days marked, not calendar days). Returns standard `['success', 'data', 'error']` shape.

**ReportController** (`app/Http/Controllers/Tenant/ReportController.php`): Replaced the stub. Methods: `index()` (two-tab page with inline report data when filters filled), `attendancePdf()`, `attendanceExcel()`, `feesPdf()`, `feesExcel()`. PDF generation uses `barryvdh/laravel-dompdf`; Excel uses `maatwebsite/excel`. Fee PDF is landscape (A4); attendance PDF is portrait.

**Fee Collection Report** (inline in controller via `buildFeeReport(termId)`): Loads all `fee_structures` for the selected term, counts active students per class in a single grouped query (no N+1), sums `fee_payments` per fee_structure_id in a single grouped query. Computes expected = `amount × student_count`, outstanding = expected − collected. Totals row included in table and exports.

**Export classes** (`app/Exports/`): `AttendanceReportExport` — FromCollection, WithHeadings, ShouldAutoSize, WithTitle. `FeeCollectionReportExport` — same interfaces; includes a TOTAL row appended to collection.

**Routes** added under `permission:reports.view`: `GET /reports/attendance/pdf` → `reports.attendance.pdf`, `GET /reports/attendance/excel` → `reports.attendance.excel`, `GET /reports/fees/pdf` → `reports.fees.pdf`, `GET /reports/fees/excel` → `reports.fees.excel`. All literal paths within the same `permission:reports.view` group as `/reports`.

**View** (`resources/views/tenant/reports/index.blade.php`): Two-tab layout (Attendance Report | Fee Collection). Alpine `reportsPage(classes, selectedClassId, selectedSectionId, activeTab)` manages tab switching and conditional section dropdown. Filter forms use `GET` with `?tab=` hidden input — submits reload the page with report data. Export buttons are anchor links with query params forwarded from the current filter state. No data loaded = empty-state card shown.

**PDF templates** (`resources/views/tenant/reports/attendance-pdf.blade.php`, `fees-pdf.blade.php`): Self-contained dompdf HTML (DejaVu Sans, inline CSS only). Both include school header (logo base64 or text-only), meta block (class/period or term/year), and the same data table as the inline view. School logo base64 encoding uses the same `encodeLogoBase64()` helper as ReportCardService. Attendance PDF: portrait; Fee PDF: landscape.

**% Present calculation**: Based on `total_marked` (days attendance was recorded for the student), not on calendar days. A student marked 45/50 days shows 90% even if the date range spans 90 calendar days.

**Access**: All 5 report routes gated by `permission:reports.view`. By default, `school_admin` and `accountant` roles have this permission (seeded in TenantProvisioningService).

---

### 14 — Fee Structure Setup

**Model**: `app/Models/Tenant/FeeStructure.php` — `HasUuids`, fillable: `class_id`, `academic_year_id`, `term`, `fee_item`, `amount`, `due_date`. Relationships: `schoolClass()` BelongsTo `SchoolClass`, `academicYear()` BelongsTo `AcademicYear`. `amount` cast to `decimal:2`, `due_date` cast to `date`.

**Form Requests**: `app/Http/Requests/Tenant/StoreFeeStructureRequest.php` (authorizes `fees.create`), `UpdateFeeStructureRequest.php` (authorizes `fees.edit`). Both validate: `class_id` (uuid, exists), `academic_year_id` (uuid, exists), `term` (required string max 50), `fee_item` (required string max 100), `amount` (required numeric min 0), `due_date` (nullable date).

**Controller** (`app/Http/Controllers/Tenant/FeeController.php`): Full CRUD — `index()` (loads fee structures with eager-loaded class + academic year, ordered by year → class → term → fee item), `store(StoreFeeStructureRequest)`, `update(UpdateFeeStructureRequest, FeeStructure)`, `destroy(FeeStructure)`. All mutation methods have try/catch returning human-readable errors.

**Routes** (`routes/tenant.php`): Extended fees routes block — `GET /fees` (fees.view), `POST /fees` (fees.create), `PUT /fees/{feeStructure}` (fees.edit), `DELETE /fees/{feeStructure}` (fees.delete).

**View** (`resources/views/tenant/fees/index.blade.php`):
- Page header with total count + "Add Fee Item" button (gated by `fees.create`).
- Table wrapped in `overflow-x-auto` → `min-width: 640px`. Columns: Class, Term, Academic Year, Fee Item, Amount, Due Date (hidden below `md:`), Actions.
- Add/Edit via shared Alpine modal (`max-w-lg`). Form fields: Class + Term (2-col on md+), Academic Year, Fee Item, Amount + Due Date (2-col on md+). Edit form uses `<input type="hidden" name="_method" value="PUT">` with `:action` template literal binding.
- `feeStructurePage(classes, academicYears)` Alpine component manages modal state. `openEdit(data)` spreads the row data into `form`. `openAdd()` resets form to empty defaults.
- Permission gates: `@can('fees.create')` on Add button and empty-state CTA, `@can('fees.edit')` on Edit button, `@can('fees.delete')` on Delete form.
- Flash messages: success (green) + error (red) using same token/pattern as other pages.
- Terms hardcoded to Term 1/2/3 — sufficient for MVP; easily extended.
- Empty state: icon + descriptive text + Add Fee Item CTA.

**No new migrations needed**: `fee_structures` table was created in Phase 05 (migration `000014`).

---

### 21 — Super Admin Dashboard — Manage Tenants & Subscriptions

**Migration** (`database/migrations/2026_06_15_000001_update_subscription_plans_for_per_student_billing.php`): Dropped `plan_name` and `renews_at` columns. Added `rate_per_student` (decimal 8,2), `student_count` (unsigned int, default 0), `student_count_synced_at` (timestamp nullable), `amount_due` (decimal 10,2), `payment_status` (string, default 'unpaid'), `cycle_start` (date nullable), `cycle_end` (date nullable). Existing `status` column kept (trial/active/expired semantics).

**Migration** (`database/migrations/2026_06_15_000002_create_super_admins_table.php`): New `super_admins` table (uuid PK, name, email unique, password, remember_token, timestamps).

**Config** (`config/schoolflow.php`): `default_rate_per_student` added — reads `DEFAULT_RATE_PER_STUDENT` env var, defaults to 5.00 GHS.

**Models**:
- `app/Models/Central/SubscriptionPlan.php` — central connection, fillable billing fields, `isExpired()` / `isPaid()` helpers, `tenant()` BelongsTo.
- `app/Models/Central/SuperAdmin.php` — central connection, `super_admins` table, `super_admin` guard, `HasUuids`.
- `app/Models/Central/Tenant.php` — added `subscriptionPlan()` HasOne relationship.

**Auth config** (`config/auth.php`): Added `super_admin` guard (session driver, `super_admins` provider) and `super_admins` provider (Eloquent, `SuperAdmin` model).

**TenantProvisioningService** (`app/Services/TenantProvisioningService.php`): After creating Domain, now creates a `SubscriptionPlan` row with `status=trial`, `rate_per_student=config('schoolflow.default_rate_per_student')`, `student_count=0`, `payment_status=unpaid`, `cycle_start=today`, `cycle_end=+1 year`.

**SyncTenantStudentCounts command** (`app/Console/Commands/SyncTenantStudentCounts.php`): Loops all tenants via `Tenant::all()`, inside each uses `$tenant->run()` to count `students` table rows, updates `subscription_plans.student_count`, `student_count_synced_at`, and recomputes `amount_due = rate_per_student × student_count`. Also auto-sets `status = expired` for tenants where `payment_status = unpaid` and `cycle_end` is in the past. Signature: `schoolflow:sync-student-counts`.

**Scheduler** (`app/Console/Kernel.php`): `$schedule->command('schoolflow:sync-student-counts')->daily()`.

**Tenant login blocking** (`app/Http/Controllers/Tenant/Auth/AuthenticatedSessionController.php`): After `$request->authenticate()`, checks `tenant()->status === 'suspended'` (logs out + returns error) and `SubscriptionPlan::where('tenant_id', tenant()->id)->first()->status === 'expired'` (logs out + returns error). Both checks happen before `session()->regenerate()`.

**Auto-expire on dashboard load** (`SuperAdminController::index()`): Runs a bulk update to set `status = expired` for any `subscription_plans` rows where `payment_status = unpaid`, `cycle_end < today`, and `status != expired`. Fires every time the dashboard is loaded — a lightweight safety net between daily scheduler runs.

**Controllers** (`app/Http/Controllers/Central/`):
- `SuperAdminAuthController` — `showLogin()`, `login()` (validates, `Auth::guard('super_admin')->attempt()`), `logout()`.
- `SuperAdminController` — `index()` (auto-expire + load tenants with `subscriptionPlan` eager-loaded + stats), `toggleStatus(Tenant)`, `updateRate(Request, Tenant)` (also recomputes `amount_due`), `markPaid(Request, Tenant)` (sets `payment_status=paid`, `status=active`, updates `cycle_start`/`cycle_end`, also enables tenant if suspended), `markUnpaid(Tenant)`.

**Routes** (`routes/web.php`): Super Admin auth routes (login GET/POST, logout POST) + protected group under `auth:super_admin` middleware with prefix `super-admin` and name prefix `super-admin.`: dashboard (GET /), toggle (PATCH /tenants/{tenant}/toggle), rate (PATCH /tenants/{tenant}/rate), mark-paid (PATCH /tenants/{tenant}/mark-paid), mark-unpaid (PATCH /tenants/{tenant}/mark-unpaid).

**Views** (`resources/views/central/super-admin/`):
- `login.blade.php` — standalone page (no central layout, no navbar/footer). SchoolFlow logo + "Super Admin Portal" subtitle. Email/password/remember-me form. Error display. Back link to home.
- `dashboard.blade.php` — standalone page with inline topbar (SchoolFlow brand + super admin name + Sign Out). 4 global stats (total schools, active, expired, total unpaid amount due). Schools table with: School name/domain/join date, student count + "last synced" timestamp, rate per student (click-to-edit pencil icon), amount due (red when unpaid), payment status badge, cycle end date (red when past due), subscription status badge, account status badge, Suspend/Enable + Mark Paid/Mark Unpaid actions. Two Alpine.js modals: Edit Rate (number input, PATCH on submit) and Mark Paid (cycle_start/cycle_end date inputs, PATCH on submit). Alpine component `superAdminPage()` manages modal state inline. No Blade `@extends` — fully self-contained HTML for clean isolation from central app navbar/footer.

**Action required**: Run `php artisan migrate` on the central DB to apply both new migrations. Create first Super Admin via `php artisan tinker`: `\App\Models\Central\SuperAdmin::create(['name'=>'Admin','email'=>'admin@schoolflow.com','password'=>bcrypt('password')])`. Then run `php artisan schoolflow:sync-student-counts` to populate student counts for existing tenants.

---

### 2026-06-15 — Bug Fixes & Retroactive Form Submission Rules

**Bug 1 — Academic Year unique constraint (Feature 07)**:
- Added migration `2026_06_15_000003_add_unique_index_to_academic_years_name.php` — deduplicates and adds `UNIQUE` index on `academic_years.name`.
- `StoreAcademicYearRequest` and `UpdateAcademicYearRequest` — added `Rule::unique('academic_years','name')` (update variant uses `->ignore($this->route('academicYear'))`). Added `messages()` returning `'name.unique' => "An academic year named ':input' already exists."`.
- `academic-year.blade.php` — added `submitting` state + `@submit` handler to all four forms (add year, edit year, add term, edit term); buttons disable with "Saving…" text while submitting. Modals re-open on validation failure using hidden `_modal_mode`/`_modal_id` sentinel fields + `old()` restoration in `init()`. Added missing `@error` directives to edit year form fields.

**Bug 2 — Custom Roles not appearing after creation (Feature 09b)**:
- `RolesPermissionsController` — changed all three redirect calls from `redirect()->route('tenant.settings.roles')` to `redirect(request()->getSchemeAndHttpHost() . '/settings/roles')`. Root cause: `route()` helper requires the `subdomain` parameter to resolve correctly in stancl/tenancy's subdomain routing.
- `roles.blade.php` — added flash message HTML (success/error), added `submitting` state + `@submit` handler, added `close()` reset of `submitting`.

**Bug 3 — School Profile logo preview not showing after upload (Feature 07b)**:
- `SchoolProfileController::index()` — now computes `$logoUrl` via `Storage::disk('public')->url($profile->logo_path)` and passes to view.
- `SchoolProfileController::update()` — changed `return back()` to `redirect(request()->getSchemeAndHttpHost() . '/settings/profile')` for a reliable explicit redirect.
- `school-profile.blade.php` — `x-data` now uses `$logoUrl` variable instead of inline `asset()` call. Added `submitting` state + button disable.

**Retroactive Form Submission Rules — all remaining forms**:
Applied consistent submit-disable pattern (Alpine `submitting` state, `@submit="submitting = true"`, `:disabled="submitting"`, loading text) and flash message rendering across:
- `settings/classes.blade.php` — add class, edit class, add section forms + flash messages added
- `settings/subjects.blade.php` — add and edit subject modal forms
- `students/create.blade.php`, `students/edit.blade.php`
- `staff/create.blade.php`, `staff/edit.blade.php`
- `exams/index.blade.php` — add exam and edit exam modal forms; `examsPage` component gains `submitting` + `close()` reset
- `fees/index.blade.php` — fee structure add/edit modal (`feeStructureTab` component gains `submitting` + `close()` reset) and payment modal (`paymentModal` component gains `submitting` + `close()` reset)

**Migration action required**: Run `php artisan tenants:migrate` to apply `2026_06_15_000003_add_unique_index_to_academic_years_name.php`.

---

### 2026-06-15 — Copy Term Structure from Previous Year (Feature 07 enhancement)

**New feature — "Copy term structure from previous year" button (Academic Year settings)**:
- `AcademicYearController::copyTerms()` — finds the most recently started other academic year that has at least one term, computes the signed day offset between the two years' `start_date` values, and bulk-creates copies of each source term (same `name`, dates shifted by offset, `is_current = false`) inside a DB transaction. Redirects back to the terms modal with a success/error flash.
- `routes/tenant.php` — added `POST /settings/academic-year/{academicYear}/copy-terms` → `copyTerms` (inside `permission:settings.manage` group).
- `academic-year.blade.php` — Terms modal empty-state section now includes a "Copy term structure from previous year" button, rendered via nested `<template x-if>`. The inner template checks `years.some(y => y.id !== termsYear.id && y.terms.length > 0)` — only visible when at least one other year has terms. Button is a form POST to the copy-terms route. Disappears once the year has terms (button is inside the zero-terms empty-state `x-if`).

---

### 2026-06-16 — Feature 07 Rebuild: Academic Calendar + Period System

**New migrations** (`database/migrations/tenant/`):
- `2026_06_16_000001_add_period_system_to_school_profile.php` — adds `period_system` string (default `'3_term'`) to `school_profile`.
- `2026_06_16_000002_make_terms_dates_nullable.php` — makes `terms.start_date` and `terms.end_date` nullable so auto-generated terms can be created without dates.

**Model updates**:
- `SchoolProfile` — added `period_system` to `$fillable`.

**Controller** (`AcademicYearController`):
- `index()` — now loads and passes `$schoolProfile` to the view.
- `store()` — wrapped in `DB::transaction`; auto-generates Term 1/2/3 or Semester 1/2 rows based on `school_profile.period_system`.
- `setPeriodSystem()` — new method; validates `period_system`, blocks change if terms already exist, calls `SchoolProfile::updateOrCreate`.
- `copyTerms()` — handles nullable term dates gracefully.

**Form Request**: `UpdateTermRequest` — `start_date` and `end_date` now nullable.

**Route**: `POST /settings/academic-year/period-system` added before copy-terms (literal before wildcard).

**View** (`resources/views/tenant/settings/academic-year.blade.php`) — **full rebuild**:
- Section 1: Period System — two form-POST cards (3-Term / 2-Semester); selected card highlighted in `bg-accent`.
- Section 2: Academic Years — pill buttons row; clicking a pill shows inline year panel with meta info, Set as Current / Edit / Delete actions, and terms list. Each term row has "Set Active" + "Edit Dates" inline form (no modal, no separate Terms tab). "Copy term structure from previous year" button in empty-terms state.
- Section 3: Active Configuration — shows current year name, period system label, current term name and date range.
- Alpine: `academicYearPage(yearsData, yearOpen)` — `selectedYearId`, `selectedYear` getter, `editingTermId`, `termForm`, modal re-open on validation error via `init()`.
- Sub-nav label: "Academic Year" → **"Academic Calendar"** (active tab in this view).

**Sub-nav label** updated to "Academic Calendar" in all 4 other settings views (`classes`, `subjects`, `roles`, `school-profile`).

**Action required**: Run `php artisan tenants:migrate` to apply the two new migrations.

---

### 2026-06-16 — Feature 14 Rebuild: Fee Structure with Target Classes & Mandatory Flag

**New migration** (`database/migrations/tenant/`):
- `2026_06_16_000003_update_fee_structures_add_target_class.php` — adds `target_class` string (default `'all'`) and `is_mandatory` boolean (default `true`); migrates existing `class_id` UUID data into `target_class`; drops FK and `class_id` column.

**Model updates** (`FeeStructure`):
- Replaced `class_id` with `target_class` in `$fillable`; added `is_mandatory`.
- Added `'is_mandatory' => 'boolean'` to `$casts`.
- Removed `schoolClass()` BelongsTo (target_class is not a FK).

**Form Requests** (`StoreFeeStructureRequest`, `UpdateFeeStructureRequest`):
- Removed `class_id`; added `target_class` (required, string, max:100) and `is_mandatory` (boolean).

**FeeStatusService** — `getStudentFeeItems()` now filters by `target_class = 'all' OR target_class = student.class_id` instead of `class_id = student.class_id`.

**FeeController** (`adminView()`):
- `$feeStructures` query: removed `schoolClass` eager load, removed `orderBy('class_id')`.
- Passes `$currentYear` (current academic year) and `$currentYearTerms` (terms for current year) to view.

**View** (`resources/views/tenant/fees/index.blade.php`) — fee structure tab updated:
- Table columns: Fee Name | Amount | Target Classes | Term | Mandatory | Due Date | Actions.
- "Target Classes" cell: `'all'` → "All Classes" pill; otherwise resolves class name via `$classes->firstWhere('id', …)`.
- "Mandatory" cell: Yes (red pill) / No (grey pill).
- "Add Fee Item" → **"Configure New Fee"** (header button + empty-state button).
- Modal title: "Configure New Fee" / "Edit Fee".
- Modal fields: Fee Name → Amount → Target Classes dropdown → Academic Year (read-only) + Academic Term (only current year's terms) → Mandatory toggle + Due Date.
- Alpine `feeStructureTab(classes, currentYearTerms, currentYearName)`: updated form fields (`target_class`, `is_mandatory`), updated `init()` old() restoration, `openEdit()` passes `target_class`/`is_mandatory`.

**Action required**: Run `php artisan tenants:migrate` to apply `2026_06_16_000003_update_fee_structures_add_target_class.php`.

---

---

### 2026-06-16 — Bulk Import Retrofit (Features 08 + 09) + Account Settings (09c)

**09c Account Settings — "My Account"**:
- Migration `2026_06_16_000004_add_phone_avatar_path_to_users.php` — adds `phone` (string, nullable) and `avatar_path` (string, nullable) to `users`.
- `User` model — `phone` and `avatar_path` added to `$fillable`.
- `UpdateAccountRequest`, `UpdatePasswordRequest` form requests (new).
- `AccountController` — `edit()`, `update()`, `updatePassword()`, `avatar()`. Avatar stored at `avatars/{tenantId}/{userId}/avatar.{ext}` on `public` disk, served via `/account/avatar` route.
- Routes (inside auth, no permission gate): `GET/PATCH /account`, `PUT /account/password`, `GET /account/avatar`.
- Topbar dropdown — avatar or initials trigger, user info block, "My Account" link, sign out form. Alpine `x-data` with `@click.outside` close.
- `resources/views/tenant/account/edit.blade.php` — two independent card forms (Profile + Password). Avatar preview persists after save. Per-section error display.

**Features 08 + 09 — Student & Staff Bulk Import Retrofit**:
- Migration `2026_06_16_000005_add_medical_notes_to_students.php` — adds `medical_notes` (text, nullable) to `students`.
- `StudentImportTemplate` (new) — `FromArray + WithStyles + WithTitle`; 9 columns including Medical Notes; blue header row, greyed italic example row; `.xlsx`.
- `StaffImportTemplate` (new) — `FromArray + WithStyles + WithTitle`; 5 columns (Full Name, Email, Phone, Role, Role Title); same styling.
- `StudentImport` (full rewrite) — `ToCollection + WithHeadingRow`. Two-pass: Pass 1 validates all rows (required fields, class/section existence, gender enum, batch dedup), collects all errors. Pass 2 runs only if zero errors, inside `DB::transaction()`.
- `StaffImport` (new) — same two-pass approach. Valid roles exclude `school_admin/student/parent`. Temp passwords format `'SF' . strtoupper(Str::random(6)) . rand(10,99)`. Collects `credentials[]` array for display after import.
- `ImportStudentsRequest` updated — field renamed `csv_file` → `import_file`, mimes `xlsx,csv`, max 5120.
- `ImportStaffRequest` (new) — same structure, authorizes `staff.create`.
- `StudentController::import()` — uses `import_file`, stores `student_import_errors` session on failure.
- `StudentController::downloadTemplate()` — returns `Excel::download(new StudentImportTemplate(), 'schoolflow-students-import-template.xlsx')`.
- `StaffController::import()` — stores `staff_import_errors` session on failure; on success stores `staff_import_credentials` session.
- `StaffController::downloadTemplate()` — returns `Excel::download(new StaffImportTemplate(), 'schoolflow-staff-import-template.xlsx')`.
- Routes — `GET /students/import/template`, `POST /students/import` (already existed); `GET /staff/import/template` (new), `POST /staff/import` (new).
- `students/index.blade.php` — import errors display, redesigned import modal (Step 1 download + Step 2 upload), `import_file` field, submit-disable.
- `staff/index.blade.php` — import errors display, credentials table (temp passwords after import), import modal (Step 1 download + Step 2 upload), `import_file` field, submit-disable.

**Import rule invariant**: Zero-tolerance two-pass approach — if any row fails validation, zero rows are imported and all errors are listed. Never partial imports.

**Action required**: Run `php artisan tenants:migrate` to apply migrations `000004` (users phone/avatar) and `000005` (students medical_notes).

---

### 2026-06-16 — Feature 14 + 17: Billing Cycle (Annual Fees) + Term Bill PDF

**New migration** (`database/migrations/tenant/`):
- `2026_06_16_000006_add_billing_cycle_to_fee_structures.php` — adds `billing_cycle` (string, default `'term'`) after `term_id`, and `academic_year_id` (uuid, nullable, FK → `academic_years` with `nullOnDelete`) after `billing_cycle`.

**Model** (`FeeStructure`):
- Added `billing_cycle` and `academic_year_id` to `$fillable`.
- Added `academicYear()` BelongsTo relationship.

**Form Requests** (`StoreFeeStructureRequest`, `UpdateFeeStructureRequest`):
- Added `billing_cycle` (required, in:term,annual) and `academic_year_id` (nullable, uuid, exists:academic_years,id) rules.

**FeeStatusService** — `getStudentFeeItems()` rewritten to handle both billing cycles:
- When `$termId` provided: queries per-term fees (`billing_cycle='term'`, `term_id=$termId`) ORed with annual fees (`billing_cycle='annual'`, `academic_year_id=term's academic year`). Legacy rows with null `billing_cycle` fall through on term match.
- When `$termId` null: queries all per-term fees plus annual fees for the current academic year. Falls back to unrestricted query if no current year found.
- Payment status computation is unchanged — annual fee status is naturally correct since payments are summed across all `fee_payments` records for that `fee_structure_id`.

**FeeController** updates:
- Added imports: `AcademicYear`, `SchoolProfile`, `Pdf` facade, `Storage`, `SymfonyResponse`.
- `adminView()`: updated `$feeStructures` query to eager-load `academicYear`; added `$currentTerm = Term::where('is_current', true)->first()`; passes `$currentTerm` to view.
- New `printBill(Request, Student)` method: resolves term (from `?term_id=` query param), loads fee items via `FeeStatusService`, computes arrears (outstanding per-term fees from previous terms of same academic year), loads school profile + logo base64, streams PDF via `term-bill-pdf.blade.php`.

**Route**: `GET /fees/bill/{student}` → `FeeController::printBill` — gated by `fees.view` check inside the controller. Added as a literal path before the `{feeStructure}` wildcard routes.

**View** (`resources/views/tenant/fees/term-bill-pdf.blade.php`) — new file:
- Two copies per A4 page (each copy constrained to 135mm height) with a dashed cut line + "✂ cut here" label between.
- Header: school logo (base64, 12mm) + school name + "TERM FEE BILL" title + "School Copy" / "Parent Copy" label on right.
- Student info strip: Student Name | Admission No | Class + Section | Term.
- Fee items table: Fee Item | Type (Term/Annual badge) | Amount | Paid | Balance | Status badge. "Annual" fees show a purple `bg-accent-muted` badge; term fees show a blue badge.
- Arrears row (red, italic) shown only when `$arrearsTotal > 0`.
- Totals row: aggregate Amount | Paid | Grand Balance (outstanding + arrears); Grand Balance shown red when > 0.
- Footer: school address · phone + "computer-generated" note.
- DejaVu Sans font, inline styles only (no Tailwind — dompdf).

**View** (`resources/views/tenant/fees/index.blade.php`) — fee structure tab and collection tab updated:
- "Print Bill" button added in the selected student info bar — links to `/fees/bill/{student->id}?term_id={currentTerm->id}` (opens in new tab); only shown when a student is selected, gated by `@can('fees.view')`.
- Fee structure table: "Term" column header renamed to "Period"; annual fee rows show an "Annual" badge + academic year name instead of term name.
- `openEdit()` now passes `billing_cycle` and `academic_year_id` into the Alpine form.
- Both Add and Edit forms: added "Billing Cycle" toggle (Per Term / Annual) above the Academic Year/Term row. Academic Term dropdown is hidden (`x-show`) when Annual is selected. `academic_year_id` is passed as a hidden field.
- Alpine `feeStructureTab()` function: added `currentYearId` fourth parameter; added `billing_cycle` and `academic_year_id` to `form`; updated `init()` old() restoration; `openAdd()` resets `billing_cycle='term'` and sets `academic_year_id=currentYearId`; `openEdit()` falls back to `currentYearId` if `academic_year_id` missing.

**Action required**: Run `php artisan tenants:migrate` to apply `2026_06_16_000006_add_billing_cycle_to_fee_structures.php`.

---

---

### 2026-06-19 — Feature 21 Impersonation (session-based, audit-logged, 1-hour expiry)

**Migration** (`database/migrations/2026_06_19_000001_create_impersonation_logs_table.php`): Central DB table `impersonation_logs` — `id` (uuid PK), `super_admin_id` (FK → super_admins cascade), `tenant_id` (FK → tenants cascade), `impersonated_user_id` (uuid — the tenant `users.id`), `started_at`, `ended_at` (nullable). No standard timestamps (uses `started_at`/`ended_at` as the audit trail). `$connection = 'central'` on the migration class.

**Model** (`app/Models/Central/ImpersonationLog.php`): Central connection, `HasUuids`, `$timestamps = false`, fillable: `super_admin_id`, `tenant_id`, `impersonated_user_id`, `started_at`, `ended_at`. BelongsTo relationships to `SuperAdmin` and `Tenant`.

**ImpersonationController** (`app/Http/Controllers/Central/ImpersonationController.php`): `start(Tenant $tenant)` — (1) finds school_admin user via `$tenant->run()`, (2) creates `ImpersonationLog` row in the central DB with `started_at = now()`, (3) writes a 90-second cache token containing `tenant_id`, `user_id`, `log_id`, (4) redirects to `{tenant_domain}/impersonate/{token}`. The 90-second token bridges the central → tenant subdomain session gap (separate cookies per domain). Old `impersonate()` method removed from `SuperAdminController`.

**ImpersonateController** (`app/Http/Controllers/Tenant/ImpersonateController.php`): **Rewritten** from the previous cache-token-only approach.
- `handle(string $token)`: validates cache token (single-use via `Cache::pull`), stores impersonation state in the tenant session: `impersonating`, `impersonating_tenant_id`, `impersonating_user_id`, `impersonating_log_id`, `impersonating_started_at` (Unix timestamp). Redirects to `/dashboard`. Does NOT call `Auth::login()` — authentication is handled per-request by `ResumeImpersonation` middleware.
- `exit()`: reads `impersonating_log_id` from session, updates `ImpersonationLog::on('central')->find($logId)->update(['ended_at' => now()])`, then `session()->forget(...)` the impersonation keys (does NOT call `Auth::logout()` or `session()->invalidate()`). Redirects to `config('app.url') . '/super-admin'`.

**ResumeImpersonation middleware** (`app/Http/Middleware/ResumeImpersonation.php`): Runs on every tenant request (registered in the top-level tenant domain route group, after `InitializeTenancyByDomain`).
- Skips if `session('impersonating')` is falsy or `session('impersonating_tenant_id') !== tenant('id')`.
- Enforces 1-hour limit: if `(time() - session('impersonating_started_at')) > 3600`, calls `expireSession()` (marks `ended_at` on log, clears session keys) and redirects to `/login` with expiry error.
- On valid session: calls `Auth::onceUsingId(session('impersonating_user_id'))` — logs in for this request only, never written to session. The tenant `web` guard session keys are never set, so the super_admin guard session on `schoolflow.com` (separate domain cookie) is completely untouched.
- Registered as `resume_impersonation` alias in `Kernel.php`.

**Routes**: `web.php` — `/super-admin/tenants/{tenant}/impersonate` now routes to `ImpersonationController::start` (was `SuperAdminController::impersonate`). `tenant.php` — `ResumeImpersonation::class` added to the top-level domain middleware array (between `RemoveTenantDomainParam` and the route closure).

**Super Admin dashboard** (`central/super-admin/dashboard.blade.php`): "Login to School" button renamed to "Impersonate".

**Tenant layout banner** (`layouts/tenant.blade.php`): Updated to `"You are viewing as {school name} (Super Admin support session) — any changes are real and attributed to the school admin account."` with an "Exit Impersonation" button. Banner uses `border-b-2` (heavier border) for better visual weight. No close/dismiss control — it is non-dismissible by design.

**Invariants enforced:**
- Super_admin guard is never touched during impersonation — `Auth::onceUsingId()` only affects the `web` guard for the duration of one request, never the session
- Every start/stop is logged to `impersonation_logs` in the central DB
- Sessions auto-expire after 1 hour; expiry is caught in the middleware and the log row is closed
- The impersonation banner is non-dismissible (no dismiss button, no Alpine x-show)
- Cache token TTL is 90 seconds (was 60) to survive slow redirects

**Action required**: Run `php artisan migrate` on the central DB to create the `impersonation_logs` table.

---

### 27 — Email Notification System

**Already existed:** `WelcomeCredentialsMail` + `resources/views/mail/welcome-credentials.blade.php` — custom HTML email sent on staff/school admin account creation, already using `->queue()`.

**Migration** (`database/migrations/tenant/2026_06_23_000005_add_notification_settings_to_school_profile.php`): Adds `notification_settings` (JSON, nullable) to `school_profile`. Ran on all 3 tenant DBs.

**SchoolProfile model**: Added `notification_settings` to fillable/casts (`array`). Added helper `isNotificationEnabled(string $key): bool` — returns `true` when `notification_settings` is null (default all enabled), otherwise reads `$settings[$key]['email']`.

**Notification classes** (`app/Notifications/`): All implement `ShouldQueue` + `Queueable`. All use `toMail()` returning `MailMessage` (fluent builder, uses Laravel's default notification template).
- `AbsenceAlert(Student, string $date)` — recipient: `$student->guardian_email`
- `FeeOverdueReminder(Student, FeeStructure, float $outstanding)` — recipient: `$student->guardian_email`
- `ExamResultsPublished(Exam, Student, string $loginUrl)` — recipient: student's linked `User->email`
- `PaymentConfirmation(FeePayment)` — recipient: `$payment->student->guardian_email`

**Dispatch points** (all gated by `isNotificationEnabled()`):
- `AttendanceController::save()` — dispatches `AbsenceAlert` per student with status `absent` and a `guardian_email`. Uses `Notification::route('mail', $email)->notify(...)` (on-demand notification, no `Notifiable` model needed).
- `FeeStatusService::recordCashPayment()` — dispatches `PaymentConfirmation` after creating the `FeePayment` row.
- `PaystackWebhookController::handle()` — dispatches `PaymentConfirmation` after recording Paystack payment; queries the newly created `FeePayment` by `paystack_ref`.
- `ExamController::publish()` — dispatches `ExamResultsPublished` for each `ExamResult` row belonging to the exam; skips students without a linked user email. Uses `->each()` on the result collection.

**Command** (`app/Console/Commands/SendFeeOverdueReminders.php`): Loops `Tenant::all()`, runs inside `$tenant->run()`. Queries `FeeStructure` rows with a past `due_date`, then per-student computes outstanding balance from `fee_payments` sum and dispatches `FeeOverdueReminder` for students with `guardian_email` and outstanding > 0. Registered as `schoolflow:send-fee-overdue-reminders`, scheduled weekly in `Kernel.php`.

**Settings UI** (`/settings/notifications`):
- `NotificationsController`: `index()` resolves settings (fills defaults if null), `save()` writes full settings array, `test(string $event)` sends a plain-text test via `Mail::raw()` to the logged-in admin's email.
- `settings/notifications.blade.php`: toggle row per event with event icon, description, recipient label, "Send test" form button, and an HTML toggle switch (CSS `peer-checked` pattern). Info card explains `.env` MAIL_* configuration. SMS column is stub (flag stored as `sms: false`, not surfaced in UI).
- Routes: `GET /settings/notifications`, `POST /settings/notifications`, `POST /settings/notifications/test/{event}` — all under `permission:settings.manage`.
- "Notifications" tab added to all 4 settings sub-navs: academic-year, roles, school-profile, domain.

**Default behaviour**: When `notification_settings` is null (not yet saved), all events default to `email=true`. Admin must actively disable events to suppress them.

**SMS stub**: `sms` key stored in `notification_settings` schema (`false` always), `SMS_PROVIDER` env noted in UI info card. No SMS provider integration in Phase 8.

**Action required**: ~~Run `php artisan tenants:migrate --path=database/migrations/tenant/2026_06_23_000005_add_notification_settings_to_school_profile.php`~~ — **DONE** (all 3 tenant DBs migrated).

---

### 26 — Tenant Onboarding Wizard

**Migration** (`database/migrations/tenant/2026_06_23_000004_add_onboarding_to_school_profile.php`): Adds `onboarding_completed` (boolean, default false) and `onboarding_step` (tinyint, default 1) to `school_profile`. Ran on all 3 tenant DBs.

**Model** (`SchoolProfile`): Added `onboarding_completed` and `onboarding_step` to `$fillable` and `$casts` (`boolean` and `integer` respectively).

**Middleware** (`app/Http/Middleware/TenantOnboardingMiddleware.php`):
- Only intercepts users with `settings.manage` permission (school admins).
- Skips redirect if `session('onboarding_skipped')` is truthy.
- If `SchoolProfile::first()` is null OR `onboarding_completed` is false → redirects to `/onboarding/{onboarding_step}`.
- Registered as `'onboarding'` alias in `Kernel.php`. Applied inline on the dashboard route: `->middleware('onboarding')`.

**Controller** (`app/Http/Controllers/Tenant/OnboardingController.php`): `show(int $step)` — clamps step 1–5, redirects to dashboard if already completed. `store(Request, int $step)` — dispatches to private step handlers via `match`. `skip(Request)` — sets session key, redirects to dashboard.

**5-Step Wizard:**
- Step 1 — School Profile: validates `school_name` (required), `short_description`, `logo` (image upload). Creates or updates `SchoolProfile` row. Logo stored at `logos/{tenantId}/logo.{ext}` on `public` disk (same path as Feature 07b `SchoolProfileController`).
- Step 2 — Academic Year: validates `year_name`, `start_date`, `end_date`, `period_system` (3_term or 2_semester). Clears all `is_current` flags, creates a new current `AcademicYear`. Updates `school_profile.period_system`.
- Step 3 — Classes: validates `classes[]` array (min 1). Uses `SchoolClass::firstOrCreate` — safe to re-submit. Sets `onboarding_step = 4`.
- Step 4 — Subjects: validates `subjects[]` array (min 1). Uses `Subject::firstOrCreate`. Has "Skip for now" link to step 5.
- Step 5 — Done: sets `onboarding_completed = true`. Redirects to dashboard with success flash.

**Layout** (`resources/views/layouts/onboarding.blade.php`): Minimal no-sidebar layout — slim topbar with app name + "Skip setup →" link, `max-w-2xl` content area. Uses `@yield('content')`. No sidebar, no auth nav.

**View** (`resources/views/tenant/onboarding.blade.php`): `@extends('layouts.onboarding')`. Progress stepper (5 circles with checkmarks for completed steps, accent color for active). Steps 1–4 use Alpine `submitting` state pattern. Steps 3 & 4 have dynamic add/remove rows via Alpine. Step 5 shows success checkmark with summary chips.

**Dashboard banner** (`resources/views/tenant/dashboard.blade.php`): Shown to `$can['settings']` users when `! ($schoolProfile?->onboarding_completed) && ! session('onboarding_skipped')`. Links to `/onboarding/{current_step}`. `DashboardController` now imports and loads `SchoolProfile::first()` as `$schoolProfile` and passes it to the view.

**Routes** (`routes/tenant.php`):
- `GET /onboarding` — redirects to `/onboarding/1`
- `GET /onboarding/skip` → `OnboardingController::skip` (named `onboarding.skip`)
- `GET /onboarding/{step}` → `OnboardingController::show` (where: `[1-5]`, named `onboarding.show`)
- `POST /onboarding/{step}` → `OnboardingController::store` (where: `[1-5]`, named `onboarding.store`)
- `/dashboard` route has `->middleware('onboarding')` added inline.

**Skip contract**: Setting `session('onboarding_skipped', true)` is permanent for the session. The dashboard banner is also hidden when this key is set. The admin can always come back to the wizard by visiting `/onboarding` directly.

**Action required**: ~~Run `php artisan tenants:migrate --path=database/migrations/tenant/2026_06_23_000004_add_onboarding_to_school_profile.php`~~ — **DONE** (all 3 tenant DBs migrated).

---

### 28 — Rate Limiting & Security Hardening

**SecurityHeaders middleware** (`app/Http/Middleware/SecurityHeaders.php`): New `final` middleware class. Adds four security headers to every response: `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(), microphone=()`. Registered at the end of `$middleware` in `Kernel.php` global stack — runs on every request regardless of route.

**Tenant login rate limit**: `RateLimiter::for('tenant-login', ...)` defined in `AppServiceProvider::boot()`. Limit: 5 attempts per minute, keyed by `$request->ip() . $request->input('email')` — per-user-per-IP keying prevents one attacker from locking out a specific account globally while still catching brute-force. Applied via `->middleware('throttle:tenant-login')` on the `POST /login` tenant route only (not the GET — no need to rate-limit page loads). Laravel's built-in throttle returns the standard `429 Too Many Requests` with `Retry-After` header and back-redirect with `"Too many login attempts. Please try again in :seconds seconds."` error on the `email` field — no custom code needed.

**Central registration rate limit**: `throttle:3,60` applied to `POST /register-school` in `routes/web.php`. Limits school registrations to 3 per hour per IP — prevents spam tenant provisioning.

**Honeypot on login form**: `<input type="text" name="hp_check" class="hidden" autocomplete="off" tabindex="-1" aria-hidden="true">` added immediately after `@csrf` in `tenant/auth/login.blade.php`. `AuthenticatedSessionController::store()` checks `$request->filled('hp_check')` before `$request->authenticate()` — returns `back()->withErrors(['email' => 'Invalid credentials.'])` if filled. Generic error message — does not reveal that a honeypot exists.

**Password strength rule**: `Password::min(8)->mixedCase()->numbers()` applied to:
- `UpdatePasswordRequest` (`new_password` field) — requires 8+ chars, at least one uppercase + one lowercase letter, at least one number. `'string'` + `'min:8'` rules replaced with the `Password` rule object.
- `UpdateStaffRequest` (`new_password` field) — upgraded from `Password::min(8)` to `Password::min(8)->mixedCase()->numbers()`.

**No new migrations**: This feature is pure middleware, routing, and validation — no schema changes.

---

### 29 — Audit Log

**Package**: `spatie/laravel-activitylog` — already in `composer.json`, installed.

**Tenant migration** (`database/migrations/tenant/2026_06_23_000006_create_activity_log_table.php`): Creates `activity_log` table. Uses explicit `string` columns for `subject_id` and `causer_id` (not `nullableMorphs`) — standard `nullableMorphs()` creates `unsignedBigInteger` ID columns which are incompatible with UUID primary keys used throughout all tenant models.

**Models updated** (11 total — `LogsActivity` trait + `getActivitylogOptions()` with `logOnlyDirty()->dontLogIfAttributesChangedOnly(['updated_at'])->useLogName(...)`):
- `Student` → log_name `student`
- `Staff` → log_name `staff`
- `Exam` → log_name `exam`
- `ExamResult` → log_name `exam_result`
- `FeeStructure` → log_name `fee_structure`
- `FeePayment` → log_name `fee_payment`
- `Announcement` → log_name `announcement`
- `SchoolProfile` → log_name `school_profile` (uses only `LogsActivity`, no `HasUuids` — SchoolProfile has no UUID PK)
- `AcademicYear` → log_name `academic_year`
- `Term` → log_name `term`
- `SchoolClass` → log_name `school_class`

**AuditLogController** (`app/Http/Controllers/Tenant/AuditLogController.php`): `index(Request)` — queries `Activity::with('causer')->latest()`, applies optional filters: `date_from`, `date_to`, `causer_id` (scoped to `User` morphType), `log_name`. Paginates at 25 with `withQueryString()`. Passes `$logs`, `$users`, `$logNames` to view.

**View** (`resources/views/tenant/settings/audit-log.blade.php`): 6th tab in settings sub-nav (active state). Filter card: date from/to (date inputs), user dropdown, record type dropdown. Table columns: Date (with time), User (name + email), Action (Created/Updated/Deleted badge), Record Type, Summary (first 2 changed field values, hidden on small screens). Empty state. Pagination. "Logs kept for 90 days" info badge in card header.

**Route**: `GET /settings/audit-log` → `AuditLogController::index` → `settings.audit-log` — inside `permission:settings.manage` group.

**Sub-nav**: "Audit Log" tab added to `academic-year.blade.php`, `roles.blade.php`, `school-profile.blade.php`, `notifications.blade.php`, `domain.blade.php` (5 views with the sub-nav; `classes.blade.php` and `subjects.blade.php` don't have the settings sub-nav).

**Scheduler** (`app/Console/Kernel.php`): `$schedule->command('activitylog:clean')->monthly()`.

**Action required**: Run `php artisan tenants:migrate` to create the `activity_log` table in all tenant DBs.

---

### 30 — Error Tracking & Health Checks

**Package**: `sentry/sentry-laravel` v4.26.0 installed. `config/sentry.php` published via `vendor:publish`.

**SetSentryContext middleware** (`app/Http/Middleware/SetSentryContext.php`): Runs on every tenant request (registered last in the tenant domain middleware group in `routes/tenant.php`, after `ResumeImpersonation`). Guards with `app()->bound('sentry') && tenancy()->initialized` — silently no-ops if Sentry DSN is not configured or tenancy is not yet active. Calls `\Sentry\configureScope()` to set `tenant_id` tag and authenticated user data (`id`, `email`).

**HealthController** (`app/Http/Controllers/Central/HealthController.php`): Two methods:
- `check()` — probes three systems: central DB (`DB::connection('central')->getPdo()`), cache (`Cache::put/__get`), local storage (`Storage::disk('local')->put/delete`). Returns JSON `{'status':'ok'|'degraded'|'fail', 'checks':{...}}`. HTTP 200 when ok or degraded; 503 when all checks fail.
- `ping()` — returns plain-text `pong` with 200, no DB hit. For lightweight uptime monitors.

**Routes** (`routes/web.php`): `GET /health` and `GET /ping` wrapped in `Route::withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class])` — no CSRF, no tenant middleware. Central-domain only.

**Config**: `SENTRY_LARAVEL_DSN=` added to `.env.example`. `config/sentry.php` reads from `env('SENTRY_LARAVEL_DSN')`. When DSN is blank/null, Sentry SDK is a no-op — no errors thrown in dev without a DSN.

**Action required**: Set `SENTRY_LARAVEL_DSN` in `.env` on production to activate error reporting. `/health` endpoint is safe to expose to uptime monitors without auth.

---

### 31 — Automated Testing Suite (PestPHP)

**Package**: `pestphp/pest` v2.36.0 + `pestphp/pest-plugin-laravel` installed as dev dependencies.

**Bootstrap** (`tests/Pest.php`): `uses(TestCase::class)->in('Feature/Central', 'Unit')` and `uses(TenantTestCase::class)->in('Feature/Tenant')`.

**TestCase** (`tests/TestCase.php`): Added `TenantTestCase` abstract class. `setUpTenant()` creates a fresh tenant via `Tenant::create()` (which fires `CreateDatabase + MigrateDatabase` pipeline), initializes tenancy, skips with a message if the central DB isn't reachable. `tearDownTenant()` ends tenancy and deletes the tenant.

**Factories** (`database/factories/Tenant/`): `UserFactory`, `StudentFactory`, `StaffFactory`, `ExamFactory`, `FeeStructureFactory`, `FeePaymentFactory`, `AttendanceFactory`. Namespaced `Database\Factories\Tenant\`, resolved by the existing PSR-4 autoload entry.

**Unit tests** (all pass in any environment):
- `tests/Unit/FeeStatusServiceTest.php` — 8 tests, all `computeStatus()` branches
- `tests/Unit/AdmissionNumberServiceTest.php` — 7 tests, pattern substitution and padding
- `tests/Unit/ReportCardServiceTest.php` — 11 tests, grade bands via Reflection on private methods, scale continuity

**Central feature tests**: Validation rejections, health/ping endpoints, provisioning tests skipped explicitly.

**Tenant feature tests** (skip when central DB unavailable): StudentTest, AttendanceTest, ExamTest, FeeTest, PermissionTest — 43 tests covering CRUD, service logic, role permission grants/denials.

**GitHub Actions** (`.github/workflows/tests.yml`): MySQL 8.0 service, PHP 8.2, Composer cache, `php artisan test --parallel` on push/PR to main.

**Cleanup**: Removed stale Breeze tests (`tests/Feature/Auth/*`, `tests/Feature/ProfileTest.php`).

**Result**: `33 passed, 43 skipped, 0 failed` in dev environment. All 43 tenant tests pass in CI with MySQL.

---

### 35 — Homework & Assignment Management

**Migrations** (`database/migrations/tenant/`):
- `2026_06_24_000004_create_assignments_table.php` — uuid PK, teacher_id FK→staff, subject_id FK, class_id FK, section_id FK nullable, title, description (text), due_date (datetime), total_marks (decimal nullable), timestamps.
- `2026_06_24_000005_create_assignment_submissions_table.php` — uuid PK, assignment_id FK, student_id FK, submission_text (text nullable), file_path (string nullable), submitted_at (timestamp), marks_awarded (decimal nullable), feedback (text nullable), timestamps. Unique constraint on [assignment_id, student_id].

**Models**: `app/Models/Tenant/Assignment.php` (HasUuids, LogsActivity, relationships to Staff/Subject/SchoolClass/Section/AssignmentSubmission). `app/Models/Tenant/AssignmentSubmission.php` (HasUuids, BelongsTo Assignment + Student).

**Permissions** (added to `TenantProvisioningService::seedPermissions()`):
- `assignments.view`, `assignments.create`, `assignments.edit`, `assignments.delete`, `assignments.submit`
- school_admin: all; teacher: view/create/edit/delete (not submit); student: view/submit; parent: view only.

**Controllers**:
- `AssignmentController`: `index()` dispatches to student card view (Pending/Submitted/Overdue) or teacher/admin table view. `store()`, `update()`, `destroy()` with teacher-scope guard (teachers can only CRUD their own assignments). `submissionFile()` serves uploaded files.
- `SubmissionController`: `store()` — student submits text or file upload (stored at `tenant{id}/assignments/{assignment_id}/{student_id}/...`). `grade()` — teacher/admin saves marks_awarded + feedback via PATCH.

**Routes** (`routes/tenant.php`): `GET /assignments` (assignments.view), `POST /assignments` (assignments.create), `PUT /assignments/{assignment}` (assignments.edit), `DELETE /assignments/{assignment}` (assignments.delete), `POST /assignments/{assignment}/submit` (assignments.submit), `PATCH /submissions/{submission}/grade` (assignments.edit).

**Views** (`resources/views/tenant/assignments/`):
- `index.blade.php` — multi-role: student gets 3-tab view (Pending/Submitted/Overdue) with inline submit form; teacher/admin gets CRUD table with submission count badges and View Submissions modal (inline grading form per submission); admin gets class/teacher filter bar.
- `_form.blade.php` — shared form partial (title, description, subject_id, class_id, section_id conditional via Alpine, due_date + total_marks 2-col, admin-only teacher select).

**Sidebar**: "Assignments" nav item added between Fees/My Children and Announcements, gated by `assignments.view`.

**Dashboard badges**: Teacher sees "X submissions waiting to be graded" warning banner linking to /assignments. Student sees "X assignments due within 3 days" warning banner. Both computed in `DashboardController::index()`.

**Action required**: Run `php artisan tenants:migrate` to create `assignments` and `assignment_submissions` tables. For existing tenants, also run `php artisan permission:cache-reset` then add the new permissions via the Roles & Permissions UI or tinker (existing tenants do not auto-receive new permissions seeded by TenantProvisioningService).

---

### 37 — Targeted Announcements & Notification Centre

**Migrations** (`database/migrations/tenant/`):
- `2026_06_24_000006_add_audience_to_announcements.php` — adds `audience_type` (string, default 'all') and `audience_ids` (JSON, nullable) to `announcements`.
- `2026_06_24_000007_create_notifications_table.php` — uuid PK, `user_id` FK→users (cascade), `announcement_id` FK→announcements (set null on delete), `type` string (default 'announcement'), `message` string, `data` JSON nullable, `read_at` timestamp nullable, timestamps. Composite index on [user_id, read_at].

**Model** `app/Models/Tenant/TenantNotification.php`: `HasUuids`, table = 'notifications', fillable + casts (data: array, read_at: datetime), `user()` + `announcement()` BelongsTo.

**Announcement model** updated: `audience_type` + `audience_ids` added to `$fillable`; `audience_ids` cast to `array`.

**Form requests** (`StoreAnnouncementRequest`, `UpdateAnnouncementRequest`) updated: `audience_type` (string, in:all,all_students,all_parents,class,role), `audience_ids` (nullable, array), `audience_ids.*` (string).

**Job** `app/Jobs/SendAnnouncementNotifications.php` (ShouldQueue): receives `$announcementId` + `$tenantId`. In `handle()`, runs `$tenant->run()` to query within the tenant context. Resolves target user IDs based on `audience_type`: `all` → all users, `all_students`/`all_parents` → role scoped, `class` → Student::whereIn('class_id')->whereNotNull('user_id'), `role` → User::role(). Bulk-inserts `notifications` rows in chunks of 100.

**AnnouncementController** updated:
- `index()` — admins see all announcements; non-admins see only announcements matching their role/class via audience_type filter (whereJsonContains for class_id and role names).
- `store()`/`update()` — saves `audience_type` and `audience_ids` (clears audience_ids for non-class/role types); dispatches `SendAnnouncementNotifications`.
- Passes `$classes` (SchoolClass ordered by order) and `$roles` (Role names) to view.

**UserNotificationsController** (`app/Http/Controllers/Tenant/UserNotificationsController.php`):
- `index()` — paginated (20) list of current user's notifications, eager-loads announcement.
- `markRead(TenantNotification)` — 403 if not own; sets `read_at`; returns JSON if wantsJson, else redirect.
- `markAllRead()` — bulk update `read_at` for current user.

**Routes** (all under `auth` middleware in `routes/tenant.php`):
- `GET /notifications` → `UserNotificationsController::index` → `tenant.notifications.index`
- `PATCH /notifications/read-all` → `markAllRead` → `tenant.notifications.read-all`
- `PATCH /notifications/{notification}/read` → `markRead` → `tenant.notifications.read`

**Views**:
- `announcements/index.blade.php` — modal scrollable body; Audience section added below `is_public` toggle: 5 radio options (All School / All Students / All Parents / Specific Class / Specific Role) + conditional class multi-select + role multi-select (both x-show, x-model="form.audience_ids"). Alpine `announcementsPage` updated to accept `classes` + `roles` params, initialise audience in form, restore audience on validation error via `old()`. Audience badge shown on each card (Students/Parents/Specific Classes/Specific Roles).
- `notifications/index.blade.php` (new) — paginated list; unread dot (blue circle) + bold message; announcement body snippet; relative time; "View →" link to announcements; per-item "Mark read" form; "Mark all as read" button in page header. Empty state with bell icon.

**layouts/tenant.blade.php** updated — notification bell placeholder replaced with:
- `@php` block: queries `TenantNotification` count (unread) + latest 5, wrapped in try/catch for safety.
- `<div x-data="{ open: false }">` dropdown: bell button with red badge when unread count > 0. Dropdown: header with "Mark all as read" form (shown when unread > 0), scrollable list (max-h-72) of 5 recent notifications with unread dot + message + time + ✓ mark-read form, empty state, "View all notifications →" link at bottom.

**No new permissions needed** — announcements are already visible to all authenticated users; the audience filter is a presentation/targeting control, not an authorization control.

**Action required**: Run `php artisan tenants:migrate` to create the `notifications` table and add audience columns to `announcements`.

---

### 36 — Disciplinary & Behavior Tracking

**Migration** (`database/migrations/tenant/2026_06_25_000001_create_disciplinary_records_table.php`):
- uuid PK, `student_id` FK→students (cascade), `reported_by` FK→users (cascade), `incident_type` enum (warning/detention/suspension/expulsion/commendation), `description` text, `action_taken` text nullable, `date` date, `parent_notified` boolean default false, timestamps. Composite index on [student_id, date].

**Model** (`app/Models/Tenant/DisciplinaryRecord.php`): `HasUuids`, `LogsActivity`, fillable, casts (date → date, parent_notified → boolean), `student()` BelongsTo, `reportedBy()` BelongsTo User.

**Student model** updated: `disciplinaryRecords()` HasMany → `DisciplinaryRecord::class`, ordered by date descending.

**Permissions** added to `TenantProvisioningService::seedPermissions()`:
- `behavior.view`, `behavior.create`, `behavior.edit`, `behavior.delete`
- school_admin: all; teacher: view + create; others: none

**Notification** (`app/Notifications/DisciplinaryIncidentNotification.php`): `ShouldQueue` + Queueable. Sends formatted `MailMessage` to guardian email (via `AnonymousNotifiable`) when `parent_notified = true`. Includes incident type, date, description, action taken.

**DisciplinaryController** (`app/Http/Controllers/Tenant/DisciplinaryController.php`):
- `index()` — paginated (25) records with eager-loaded student.schoolClass + reportedBy; filter by class_id, incident_type, date_from, date_to via query params. Passes `$classes` and `$students` to view.
- `store()` — validates all fields; sets `reported_by = Auth::id()`; creates record; conditionally dispatches `DisciplinaryIncidentNotification` via `AnonymousNotifiable` if `parent_notified = true` and guardian email exists.
- `destroy(DisciplinaryRecord $disciplinaryRecord)` — deletes record; redirects back with success flash.

**Routes** (`routes/tenant.php`):
- `GET /behavior` → `DisciplinaryController::index` → `behavior.index` (permission: behavior.view)
- `POST /behavior` → `DisciplinaryController::store` → `behavior.store` (permission: behavior.create)
- `DELETE /behavior/{disciplinaryRecord}` → `DisciplinaryController::destroy` → `behavior.destroy` (permission: behavior.delete)

**Views**:
- `resources/views/tenant/behavior/index.blade.php` — filter bar (class, incident type, date range) + filter/clear button; paginated table (student name/class, type badge, date, description preview, reported by, parent notified badge, delete button); "Log Incident" modal (student select + type + date + description + action taken + notify parent checkbox); Alpine `behaviorPage()` — showModal/submitting/form; re-opens modal with old() on validation error; empty state.
- `resources/views/tenant/students/show.blade.php` — new "Behavior & Discipline" card at bottom, gated by `@can('behavior.view')`; lists all discipline records with type badge + truncated description (expandable) + date + reported by + parent notified; per-record expand/collapse + delete form; "Log Incident" button opens pre-filled modal (student_id hardcoded, no student select); `studentBehavior()` Alpine component; inline modal within the card section.

**Sidebar**: "Behavior" nav item added between Assignments and Announcements, gated by `behavior.view`, uses warning triangle SVG icon.

**StudentController::show()** updated: loads `$student->disciplinaryRecords()->with('reportedBy')->get()` and passes as `$disciplinaryRecords`.

**Action required**: Run `php artisan tenants:migrate` to create the `disciplinary_records` table. For existing tenants also run `php artisan permission:cache-reset` then grant the 4 new behavior permissions via the Roles & Permissions UI.

---

### 38 — Expense & Budget Management

**Migrations** (`database/migrations/tenant/`):
- `2026_06_25_000002_create_expense_categories_table.php` — uuid PK, name string unique, timestamps.
- `2026_06_25_000003_create_expenses_table.php` — uuid PK, category_id FK→expense_categories (cascade), amount decimal:2, date date, description string, receipt_path string nullable, recorded_by FK→users (cascade), timestamps. Composite index on [date, category_id].

**Models**: `app/Models/Tenant/ExpenseCategory.php` (HasUuids, fillable name, expenses() HasMany). `app/Models/Tenant/Expense.php` (HasUuids, LogsActivity log_name='expense', fillable, casts amount/date, category() BelongsTo, recordedBy() BelongsTo User).

**Permissions** added to `TenantProvisioningService::seedPermissions()`:
- `expenses.view`, `expenses.create`, `expenses.edit`, `expenses.delete`
- school_admin: all; accountant: all; others: none.

**Default categories seeded** in `TenantProvisioningService::seedExpenseCategories()`: Salaries, Utilities, Supplies, Maintenance, Events, Other — using `firstOrCreate` (safe for existing tenants run manually).

**Form Requests** (`app/Http/Requests/Tenant/`): `StoreExpenseCategoryRequest` (expenses.create, name unique), `StoreExpenseRequest` (expenses.create), `UpdateExpenseRequest` (expenses.edit). All validate category_id exists, amount numeric min:0.01, date before_or_equal:today, receipt file optional (jpg/jpeg/png/pdf max 5MB).

**Controller** (`app/Http/Controllers/Tenant/ExpenseController.php`): `index()` — paginated (25) with category + date filters; computes totalThisMonth, totalThisTerm (via current Term dates), totalYtd. `store()` — handles optional receipt upload stored at `expenses/receipts/{tenantId}/` on local disk. `update()` — replaces receipt if new file uploaded. `destroy()` — deletes receipt file if exists. `receipt(Expense)` — streams file from local disk (gated by expenses.view). `storeCategory(StoreExpenseCategoryRequest)` — creates new ExpenseCategory.

**Routes** (`routes/tenant.php`): `GET /expenses` (expenses.view), `GET /expenses/receipt/{expense}` (expenses.view), `POST /expenses` (expenses.create), `POST /expenses/categories` (expenses.create), `PUT /expenses/{expense}` (expenses.edit), `DELETE /expenses/{expense}` (expenses.delete). All literal `/expenses/receipt/{expense}` registered before `{expense}` wildcard.

**Sidebar**: "Expenses" nav item added between Behavior and Announcements, gated by `expenses.view`, uses wallet/money SVG icon.

**View** (`resources/views/tenant/expenses/index.blade.php`): Summary strip (3 stat cards: This Month / This Term / YTD). Filter bar: category select + date from/to. Paginated expense table (Date / Category badge / Description / Amount / Recorded By / Receipt link / Edit+Delete actions). Log Expense modal + Edit modal (shared `_form.blade.php` partial). Add Category inline modal (accessible from the category dropdown in the form). Alpine `expensesPage(categories, expenses)` — openAdd/openEdit/close + showCategoryModal. Modals re-open on validation error via `old()` sentinel fields.

**Receipt storage**: `expenses/receipts/{tenantId}/{filename}` on local disk. Served via controller, never via public URL. Supports JPG, PNG, PDF.

**Action required**: Run `php artisan tenants:migrate` to create `expense_categories` and `expenses` tables. For existing tenants: run `php artisan permission:cache-reset` then add the 4 new expense permissions via the Roles & Permissions UI (or tinker). To seed default categories for existing tenants: run inside `$tenant->run()` calling `(new TenantProvisioningService)->seedExpenseCategories()`.

---

### 39 — Scholarship & Fee Waiver Management

**Migration** (`database/migrations/tenant/2026_06_25_000004_create_fee_discounts_table.php`): uuid PK, student_id FK→students (cascade), fee_structure_id FK→fee_structures nullable (cascade), discount_type enum['percentage','fixed'], discount_value decimal:2, reason text, approved_by FK→users (cascade), valid_from date nullable, valid_until date nullable, timestamps. Index on student_id and (student_id, fee_structure_id).

**Model**: `app/Models/Tenant/FeeDiscount.php` (HasUuids, fillable all fields, casts discount_value/valid_from/valid_until, student() BelongsTo, feeStructure() BelongsTo, approver() BelongsTo User with 'approved_by'). `Student` model gained `feeDiscounts(): HasMany`.

**Form Request**: `app/Http/Requests/Tenant/StoreFeeDiscountRequest.php` — authorizes `fees.edit`; validates discount_type (required, in: percentage/fixed), discount_value (numeric min:0.01, max:100 when type=percentage), reason (required max:500), fee_structure_id (nullable uuid exists:fee_structures), valid_from/valid_until (date, valid_until after_or_equal:valid_from).

**Controller**: `app/Http/Controllers/Tenant/FeeDiscountController.php` — `store(StoreFeeDiscountRequest, Student)`: creates discount with `approved_by = Auth::id()`, redirects to student profile. `destroy(Request, Student, FeeDiscount)`: gates on `fees.edit`, deletes, redirects to student profile.

**FeeStatusService** (`app/Services/FeeStatusService.php`) updated:
- Imports `FeeDiscount`
- After loading payments, queries all active discounts for the student in one query (valid_from ≤ today or null, valid_until ≥ today or null).
- For each fee structure, merges blanket discounts (fee_structure_id null) + specific discounts for that fee_structure_id.
- Computes reduction as sum of (percentage × original_amount) and fixed amounts; `effective_amount = max(0, original_amount − reduction)`.
- `outstanding = max(0, effective_amount − paid_amount)`; `status = computeStatus(effective_amount, paid_amount, due_date)`.
- Return array now includes `original_amount`, `effective_amount`, `has_discount`, `discounts` in addition to existing keys.

**StudentController::show** updated: loads `feeDiscounts` (with feeStructure + approver), loads `studentFeeStructures` (fee items applicable to the student's class — for the "Applies To" select). Both passed to view.

**Student profile view** (`resources/views/tenant/students/show.blade.php`): "Fee Discounts" card added between Academic Details and Login Account (visible to `fees.edit` or `fees.view`). Table: Type badge | Value | Applies To | Reason | Expiry (with expired/pending/active states). "Add Discount" button (fees.edit only) opens Alpine modal. Expired rows rendered at 50% opacity. Add Discount modal: discount type select (percentage/fixed), value input (max=100 when percentage), Applies To select (all fees or specific fee structure from studentFeeStructures), Reason input, Valid From + Valid Until date pair. Modal re-opens on validation error via `x-init` checking `$errors->hasAny(...)`.

**Fee collection view** (`resources/views/tenant/fees/index.blade.php`):
- `$totalOwed` now sums `effective_amount` (not `fee_structure.amount`) — shows discounted total.
- Amount column: when `has_discount=true`, shows struck-through original (text-muted line-through) + effective amount + "Discounted" badge (bg-accent-muted text-accent). When no discount, shows amount normally.
- `@php` extraction block now also captures `has_discount`, `original_amount`, `effective_amount` with safe defaults.

**Routes** (`routes/tenant.php`): `POST /students/{student}/discounts` (fees.edit) → students.discounts.store; `DELETE /students/{student}/discounts/{discount}` (fees.edit) → students.discounts.destroy.

**No new permissions** required — discount CRUD uses existing `fees.edit` permission.

**Action required**: Run `php artisan tenants:migrate` to add the `fee_discounts` table to all tenant databases.

---

### 40 — Academic Performance Analytics

**Service** `app/Services/ExamAnalyticsService.php`:
- `buildSubjectReport(string $examId, string $classId, ?string $sectionId): array` — loads active students for class/section, queries exam_results, groups by subject_id, computes avg/highest/lowest/pass_rate per subject. Pass threshold derived from grading scale: sorted by min ascending, second band's min (e.g. 40 for default scale).
- `buildClassTrend(string $classId, ?string $sectionId, string $termId): array` — loads all exams for the term ordered by start_date, per-exam average across all students in class/section; returns `[{exam_name, average}]`.
- Both methods query active students only (`status = 'active'`). Return empty structures when no students or no results.
- Pass threshold: `collect($scale)->sortBy('min')->values()->get(1)['min']` — second-lowest band's min, defaults to 40.

**ReportController** updated:
- Constructor now injects `ExamAnalyticsService`.
- `index()` handles `tab=academic`: loads all exams; if `class_id` filled, calls `buildSubjectReport` (when `exam_id` set) and `buildClassTrend` (when `term_id` set or inferred from exam). Passes `$exams`, `$analyticsReport`, `$trendData`, `$selectedExamId` to view.
- `academicPdf(Request $request)`: validates exam_id+class_id+section_id, renders `academic-analytics-pdf.blade.php` as A4 portrait PDF.

**Route**: `GET /reports/academic/pdf` → `reports.academic.pdf` under `permission:reports.view`.

**View** `resources/views/tenant/reports/index.blade.php`:
- Chart.js CDN added via `@push('head')`.
- `reportsPage` Alpine component now accepts 2 new params: `examsData` (JSON array {id,name,term_id}) and `chartData` (JSON {labels,avgScores,passRates,trendLabels,trendAverages}).
- Computed `filteredExams` filters `examsData` by `academicTermId`.
- `initAcademicCharts()` method: initializes avgChart (horizontal bar, accent color), passChart (horizontal bar, success color), trendChart (line, accent color, only when trendLabels > 1). Called from `init()` when `activeTab === 'academic'` via `$nextTick`.
- Chart canvases sized dynamically: `height = max(120, subjectCount * 36)px` for bar charts, `200px` for trend.
- "Academic Analytics" 5th tab button added.
- Academic tab: filter form (term/exam/class/section selects); charts grid (2-col lg); summary table; 3 empty states (initial / no data / trend-only).
- Chart colors use `getComputedStyle` on CSS variables (`--color-accent`, `--color-success-foreground`).

**PDF view** `resources/views/tenant/reports/academic-analytics-pdf.blade.php`: A4 portrait, no charts. School header + meta block (exam/class/term/threshold) + summary stats strip + per-subject table (Subject | Students | Avg | Highest | Lowest | Pass Rate) with tfoot averages row. Pass rate colored green/red per ≥50% threshold.

**Pass threshold decision**: using `min` of second-lowest grade band (not a configurable field). For default scale this is 40%. If the school configures a different grading scale, the threshold auto-updates. This is simpler than adding a dedicated "passing grade" setting.

---

### 41 — Attendance Analytics & Chronic Absentee Reports

**`AttendanceReportService::buildChronicAbsentees(string $termId, string $classId, ?string $sectionId, int $threshold = 80): array`**:
- Loads `Term` to get `start_date` → `end_date` (capped at today if term end_date is in the future).
- Queries active students for class/section, loads all attendances within that date range in a single query.
- Per student: computes `absent`, `days_marked`, `percent_present`. Includes only students below `$threshold`.
- Rows sorted ascending by `percent_present` (worst attendance first).
- Returns standard `['success', 'data', 'error']` envelope. Data keys: `term`, `class`, `section`, `threshold`, `rows[]`.

**`LowAttendanceAlert` notification** (`app/Notifications/LowAttendanceAlert.php`):
- Constructor: `Student $student`, `float $percentPresent`, `string $termName`.
- Queued mail via `ShouldQueue`. Subject: "Low Attendance Alert — {name}". Body includes attendance rate + term name + call to action.

**`AttendanceController::notifyGuardian(Request, Student)`**:
- Permission check: `attendance.view`. Validates `term_id` (uuid, exists:terms) + `percent_present`.
- Rate-limited: 3 attempts per student-user pair per hour (key: `low-attendance-alert:{studentId}:{userId}`).
- Dispatches `LowAttendanceAlert` to `$student->guardian_email`. Returns redirect with flash.

**`AttendanceController::notifyAll(Request)`**:
- Rate-limited: 5 bulk dispatches per user per hour (key: `low-attendance-bulk:{userId}`).
- Calls `buildChronicAbsentees` to get the filtered list, then dispatches `LowAttendanceAlert` for each student with a guardian email. Returns count sent.

**Routes** (under `permission:attendance.view`):
- `POST /attendance/notify/{student}` → `attendance.notify.guardian`
- `POST /attendance/notify-bulk` → `attendance.notify.bulk`

**Reports page "Attendance Alerts" tab** (4th tab, between Fee Collection and Academic Analytics):
- Filter form: class / section / term / threshold range slider (1–99, default 80).
- Results card: header with class + section + term + threshold summary. Table columns: Student (name + admission no) | Absences | Days Marked | % Present (colored red <50%, warning <70%) | Guardian (name + contact, hidden md) | Action (Notify Guardian button per-row if guardian_email exists).
- "Notify All Guardians" bulk banner shown above table when rows > 0: red alert strip with count summary + POST form button.
- Empty success state when all students meet threshold.
- Initial state when no filters applied.

**Dashboard stat card**:
- Admin view (5th card in `grid-cols-2 xl:grid-cols-5`): clickable `<a>` card linking to `/reports?tab=alerts`. Shows chronic absentees count in red when > 0 else primary color. Hover: red border + light red background.
- Teacher view (3rd card in `grid-cols-2 xl:grid-cols-3`): same card, conditionally rendered only when `$can['reports']`.
- DashboardController: queries all active student IDs, loads attendances for current term date range in one query, groups by student_id, counts students with `(present/total)*100 < 80`. Stored in `$stats['chronic_absentees']`.

**Threshold slider design**: `<input type="range">` with Alpine `x-data="{ thresh: N }"` + `x-model.number`; live label update shows `thresh%` via `x-text`. The submitted `threshold` value is used server-side.

---

### 42 — Online Admission Application

**Migrations**:
- `2026_06_25_000005_create_admission_applications_table` — uuid PK, applicant_name, date_of_birth nullable, gender nullable, class_applying_for, guardian_name, guardian_contact, guardian_email nullable, previous_school nullable, status enum['pending','accepted','rejected'] default 'pending', notes nullable, reviewed_by FK→users nullable on delete set null, reviewed_at nullable, rejection_reason nullable. Indexes on status, (status, class_applying_for).
- `2026_06_25_000006_add_admissions_open_to_school_profile` — adds boolean `admissions_open` default false to `school_profile`.

**Permissions**: `admissions.view`, `admissions.manage` added to `TenantProvisioningService::seedPermissions()` and `SeedTenantPermissions` command. `school_admin` gets both via `$all`. No other role gets them by default.

**Model**: `AdmissionApplication` — HasUuids, fillable all columns, casts date_of_birth+reviewed_at, `reviewer()` BelongsTo User, `isPending()` / `isAccepted()` / `isRejected()` helpers.

**`PublicApplicationController`** (no auth):
- `GET /apply` — loads `SchoolProfile` + `SchoolClass` list, returns `tenant.apply` view.
- `POST /apply` — validates all fields, creates `AdmissionApplication`, dispatches `AdmissionConfirmation` to `guardian_email` if provided, returns `tenant.apply-confirmation` view (not a redirect, so confirmation data is available).

**`AdmissionController`** (under `admissions.manage`):
- `GET /admissions` — paginated table with status + class filters.
- `POST /admissions/{application}/accept` — guards that application is pending, redirects to `/students/create?from_application={id}`.
- `POST /admissions/{application}/reject` — validates `rejection_reason`, updates application to rejected, dispatches `AdmissionRejected` notification.

**`StudentController` changes**:
- `create(Request)` — now type-hinted to accept Request. If `?from_application={id}` present + application is pending, builds `$prefill` array (full_name, date_of_birth, gender, guardian_name, guardian_contact, guardian_email) and passes `$application` + `$prefill` to view. Both are `null` when no application.
- `store()` — if `from_application_id` hidden field is present, finds the application and marks it accepted with `reviewed_by = Auth::id()`.

**Notifications**:
- `AdmissionConfirmation` — queued mail to guardian on submission. Includes applicant name + class applied for.
- `AdmissionRejected` — queued mail to guardian on rejection. Includes rejection_reason if set.

**Views**:
- `tenant/apply.blade.php` — standalone (no layout extension). School navbar with logo + Login button. Three sections: Student Info (name, DOB, gender, class select or text fallback) + Guardian Info (name, contact, email) + Previous School. Alpine `submitting` guard on submit.
- `tenant/apply-confirmation.blade.php` — standalone. Success icon + confirmation card with all submitted details + "email sent" accent notice + "Back to School Page" button. `noindex` meta.
- `tenant/admissions/index.blade.php` — tenant layout. Filter bar (status + class). Table with Review button per pending application. Review opens an Alpine slide-over panel (right-side drawer) with full details + Accept form (POST to `/admissions/{id}/accept`) + Reject section (Alpine toggled, inline rejection_reason textarea + Confirm Rejection submit).

**School profile settings**: `admissions_open` toggle added as a CSS-only checkbox (`<input type="hidden" value="0">` + `<input type="checkbox" value="1">`). Controller reads via `$request->boolean('admissions_open')`.

**Public page**: "Apply Now" button added in header navbar next to Login, shown only when `$profile?->admissions_open` is true. Style: `bg-accent-muted text-accent border border-accent` outline button.

**Sidebar nav**: Admissions item added before Reports, uses `admissions.view` permission gate.

**Robots.txt**: `Allow: /apply` added alongside `Allow: /`.

**Accept flow design choice**: `accept` action only redirects (does not mark accepted). The application is marked accepted in `StudentController::store()` when the student is actually saved — this avoids accepting applications where the admin abandoned the form.

---

### 43 — Student Transcript Generation

**`ReportCardService::generateTranscript(Student)`**:
- Queries `ExamResult::where('student_id', …)->whereHas('exam', fn($q) => $q->where('is_published', true))->with(['exam.term.academicYear', 'subject'])`. Only results with a non-null `exam.term.academicYear` are included.
- Groups by academicYear.id → term.id → exam.id using Collection `groupBy()` chains.
- Sorts years by `academicYear.start_date` ASC; terms by `term.start_date` ASC; exams by `exam.start_date` ASC within each term.
- Attendance per term: queries `Attendance::where('student_id', …)->whereBetween('date', [term.start_date, cappedEndDate])` directly (single student — efficient). Caps end date at today if term.end_date is future.
- Computes average marks at 3 levels: per-exam, per-term (all results in that term), per-year, and cumulative.
- Saves PDF to `storage/{tenantId}/transcripts/{student_id}.pdf`. Overwrites on each download (always fresh).
- Returns absolute storage path.

**`TranscriptController::download(Student)`**:
- Authorization: admin (`students.view` permission) always allowed. Own student (`student->user_id === Auth::id()`). Parent (loaded already via `$student->parents` relationship — `$student->parents->contains('id', $user->id)`).
- No separate permission gate on the route — sits inside `students.view` middleware group, which all authenticated users with at least student view access have.
- Returns `response()->download($path, $filename, ['Content-Type' => 'application/pdf'])`.

**Show view button**: `$canDownloadTranscript` boolean passed from `StudentController::show()`. Computed from `$hasPublishedResults && (isAdmin || isOwn || isParent)`. Button is a simple `<a href="…/transcript">` link — not a form POST.

**PDF design**: DejaVu Sans font, A4 portrait. Section hierarchy: year banner (blue) → term sub-header (blue-left-border) → exam label row (gray) → results table → year cumulative row (blue). Overall cumulative block at bottom (navy). Attendance shown as color-coded pill: green ≥80%, orange ≥60%, red <60%.

---

### 44 — Multi-Currency & Locale Support

**Migration** `2026_06_26_000001_add_currency_to_school_profile.php`: adds `currency_code` (string 3, default `'GHS'`) and `currency_symbol` (string 5, default `'₵'`) to `school_profile`.

**`app/Helpers/Money.php`**: global `format_money(float $amount, string $symbol = '₵'): string` helper — returns `$symbol . ' ' . number_format($amount, 2)`. Auto-loaded via `composer.json` `autoload.files`. Run `composer dump-autoload` after adding.

**ViewComposer** (`AppServiceProvider`): fetches `SchoolProfile::first()` once per request, shares both `$schoolProfile` and `$currencySymbol` (`$profile?->currency_symbol ?? '₵'`) into `layouts.tenant` and `tenant.auth.login`.

**School profile settings** (`settings/school-profile.blade.php`): Currency section with `<select name="currency_code">` (GHS/NGN/KES/USD/EUR options) and a hidden `<input name="currency_symbol">` auto-populated via Alpine `x-data` options map `@change` handler.

**PaystackService**: reads `currency_code` from `SchoolProfile::first()?->currency_code ?? config('paystack.currency', 'GHS')` — dynamic per-school Paystack currency.

**PDF views**: PDF blades don't go through `layouts.tenant` so they don't get the ViewComposer `$currencySymbol`. Each PDF view has `@php $currencySymbol = $schoolProfile?->currency_symbol ?? '₵'; @endphp` at the top (except `fees-pdf.blade.php` which uses `$profile` not `$schoolProfile`, so uses `$profile?->currency_symbol ?? '₵'`).

**Views updated**: `dashboard.blade.php`, `fees/index.blade.php`, `fees/my-fees.blade.php`, `parents/portal.blade.php`, `expenses/index.blade.php`, `reports/index.blade.php`, `students/show.blade.php` (flat discount), `fees/term-bill-pdf.blade.php`, `fees/receipt-pdf.blade.php`, `reports/fees-pdf.blade.php`.

---

### 45 — Data Export, Backup & Privacy Tools

**Migration** `2026_06_26_000002_add_soft_deletes_to_students_staff_users.php`: adds `deleted_at` nullable timestamp column to `students`, `staff`, and `users` tables.

**SoftDeletes on models**: `Student`, `Staff`, and `User` models all gain `SoftDeletes` trait. Route model binding auto-excludes soft-deleted records (restore/forceDelete methods use explicit `onlyTrashed()->findOrFail()`).

**StudentController**: `destroy()` now soft-deletes (flash changed to "moved to trash"). Added `trash()` (index of `onlyTrashed()`), `restore(string $id)`, `forceDelete(string $id)`, `anonymize(Student)` (blanks PII fields, preserves academic records), `exportData(Request, Student)` (dispatches `ExportStudentDataJob`).

**StaffController**: `destroy()` now soft-deletes both staff + user records. Added `trash()`, `restore(string $id)` (restores staff + user), `forceDelete(string $id)`.

**ExportStudentDataJob**: dispatched with student_id, tenant_id, tenant_host, admin_email/name. Inside `$tenant->run()`: builds ZIP with `student.json`, `attendance.csv`, `exam_results.csv`, `fee_payments.csv`; saves to `storage/{tenantId}/exports/{uuid}.zip`; emails admin with `DataExportReadyMail`.

**ExportAllSchoolDataJob**: same pattern — exports all known tenant tables as CSV files in one ZIP; emails admin.

**DataExportReadyMail** + `resources/views/mail/data-export-ready.blade.php`: branded HTML email with download link button, export type, and expiry timestamp.

**PrivacyController**: `index()` → `settings/privacy` view. `requestFullExport()` → dispatches `ExportAllSchoolDataJob`. `download(token)` → checks file exists + is <24h old, then streams ZIP.

**Download URL pattern**: `GET /export/download/{uuid}` — no signed URL needed; controller verifies `settings.manage` permission and 24h file mtime. Files stored at `storage/app/{tenantId}/exports/{uuid}.zip`.

**PurgeDeletedRecords command** (`schoolflow:purge-deleted`): iterates all tenants via `$tenant->run()`, hard-deletes students/staff/users with `deleted_at < 90 days ago`. Scheduled monthly in `Kernel.php`.

**Routes**: `GET /students/trash` added to `students.view` group (before wildcard). `POST /students/{id}/restore`, `DELETE /students/{id}/force-delete` in `students.delete` group. `POST /students/{student}/anonymize` and `POST /students/{student}/export` in `students.edit`. Same pattern for staff. `GET /settings/privacy` + `POST /settings/privacy/export` in `settings.manage`. `GET /export/download/{token}` in auth group.

**Views**: `students/trash.blade.php`, `staff/trash.blade.php`, `settings/privacy.blade.php`. "Data & Privacy" tab added to all 6 settings sub-navs. Student show page gains Export Data + Anonymise buttons (inside `students.edit` guard, next to Edit).

---

### 46 — REST API (Sanctum)

**Migration** (`database/migrations/tenant/2026_06_26_000003_create_personal_access_tokens_table.php`): Creates `personal_access_tokens` in the tenant DB with `string tokenable_type` and `string tokenable_id` (not the default `morphs` which uses `unsignedBigInteger` — incompatible with tenant Users' UUID PKs). Manual composite index on `[tokenable_type, tokenable_id]`.

**Model** (`User`): `HasApiTokens` trait added (from `Laravel\Sanctum\HasApiTokens`). Sanctum reads from the current default connection, which stancl/tenancy already switches to the tenant DB before `auth:sanctum` runs.

**API Resource classes** (`app/Http/Resources/Api/`):
- `StudentResource` — id, admission_no, full_name, DOB, gender, class/section name, guardian info, status, created_at
- `AttendanceResource` — id, student_id, date, status, note, marked_by
- `ExamResultResource` — id, exam_id/name/term, subject_id/name, marks, grade, remarks
- `FeeStatusResource` — wraps `FeeStatusService` output array; fee_structure_id, fee_item, term, billing_cycle, original/effective amount, has_discount, paid/outstanding/status, due_date

**API Controllers** (`app/Http/Controllers/Tenant/Api/`):
- `StudentApiController`: `index()` (paginated 50, filterable by class/status/search), `show(Student)`, `attendance(Student)` (date-range filterable, checks attendance.view), `exams(Student)` (published only, checks exams.view)
- `AttendanceApiController`: `store()` — bulk mark attendance for a `records[]` array; checks `tokenCan('write')` first (read-only tokens rejected); idempotent via `updateOrCreate`; returns saved count + per-row errors
- `FeeApiController`: `show(Student)` — calls `FeeStatusService::getStudentFeeItems()`, returns full discount-aware status array
- `AnnouncementApiController`: `index()` — paginated 25, includes meta pagination block

**Token management** (`AccountController`): `createToken(Request)` — validates `token_name` (max 100) + `token_scope` (in: read-only, full); abilities `['read']` or `['read','write']`; flashes `new_token` + `new_token_name` to session for one-time display. `revokeToken(Request, string $tokenId)` — deletes token owned by current user.

**Routes** (`routes/tenant.php`):
- Token management (inside auth group): `POST /account/tokens` + `DELETE /account/tokens/{tokenId}`
- API group (outside auth group, inside domain group): `prefix('api/v1')`, `middleware(['auth:sanctum','throttle:60,1'])`, `withoutMiddleware(VerifyCsrfToken)`. Seven endpoints — GET /students, /students/{id}, /students/{id}/attendance, /students/{id}/exams, POST /attendance, GET /fees/{student}, GET /announcements.

**Exception handler** (`app/Exceptions/Handler.php`): `isApiRequest()` checks `$request->is('api/*') || $request->expectsJson()`. Returns JSON for `AuthenticationException` (401), `AuthorizationException` (403), `ValidationException` (422), `HttpExceptionInterface` (status code), and generic 500 on production.

**UI** (`tenant/account/edit.blade.php`): "API Tokens" card appended below Password card. Table of active tokens (name, scope badge, created, last used, revoke). New-token one-time display banner (mono code + clipboard copy button). Generate Token modal (token name + scope radio). Alpine component manages `showCreateModal`, `showNewToken`, `newToken`, `copied`, `submitting` state.

**Token scope enforcement**: `tokenCan('write')` checked explicitly in `AttendanceApiController::store()` — read-only tokens get a 403 before any DB write. All other API endpoints are read-only and don't need this check.

**Action required**: Run `php artisan tenants:migrate` to create the `personal_access_tokens` table in all existing tenant databases.

### 51 — Teacher Class Register & Lesson Plans

**Migrations**: `class_registers` (UUID; teacher_id FK→staff; class_id FK→school_classes; section_id nullable FK→sections; subject_id FK→subjects; date; topic_covered string; notes text nullable) and `lesson_plans` (UUID; teacher_id; subject_id; class_id; section_id nullable; week_start date; objectives text nullable; content text).

**Models**: `ClassRegister` + `LessonPlan` — both `HasUuids`; date/week_start cast; teacher (BelongsTo Staff), schoolClass, section, subject relations.

**RegisterController**:
- `index()` — for teacher role: filters classes/subjects via `SubjectTeacherAssignment::where('staff_id', $staffId)`; for admin: all classes + subjects; loads `existingEntry` + `registerHistory` (last 30, same teacher/class/section/subject) if reg_class_id + reg_subject_id present in query; loads `lessonPlans` for selected week_start when tab=plans; snaps week_start to Monday via `Carbon::startOfWeek(Carbon::MONDAY)`.
- `store()` — `updateOrCreate([teacher_id, class_id, section_id, subject_id, date], [topic_covered, notes])`; redirects back with filter params.
- `exportPdf(Staff, month)` — month param is `Y-m` string; loads all class_registers for staff in that month; renders dompdf A4 portrait; accessible if `register.manage` OR own staff record.

**LessonPlanController**:
- `store()` — snaps `week_start` to Monday; `updateOrCreate([teacher_id, subject_id, class_id, section_id, week_start], [objectives, content])`.
- `update()` / `destroy()` — ownership check: `abort_unless(canManage || plan->teacher_id === staff->id)`.

**View** (`tenant/register/index.blade.php`): Alpine `registerPage(initialTab, classesData, subjectsData, selectedClassId, selectedSectionId, currentWeekStart)`. State: `tab`, `regClassId`, `regSectionId` (register filter), `createModal`, `planWeekStart`, `planClassId`, `planSectionId` (lesson plan modal). Computed: `sectionsForClass` (register), `planSectionsForClass` (modal).

- Class Register tab: filter bar (GET form with tab=register, class/section/subject/date + optional teacher for admin) → Load button. If filter active: 2-col layout (entry form left + history table right). Entry form uses `updateOrCreate` pattern (button says "Update Entry" if existing). History table shows Date/Topic/Notes, last 30 entries.
- Lesson Plans tab: week navigation bar (prev/next links, "This week" link, Mon–Fri day chips display, admin teacher filter form). Plans shown as card grid (md:grid-cols-2 xl:grid-cols-3). Each card: Alpine `x-data={editing:false}` — read view + inline edit form (PATCH). Delete = form with confirm. "New Plan" button → full-screen modal with week_start/subject/class/section/objectives/content.

**PDF** (`tenant/register/pdf.blade.php`): A4 portrait self-contained dompdf; staff info 2-cell table row; register table (Date, Class, Section badge, Subject, Topic Covered, Notes); row striping.

**Permissions**: `register.view`, `register.create`, `register.manage` added to `$all`; school_admin gets all three; teacher gets `register.view` + `register.create`.

**Routes** under `permission:register.view`: `GET /register` (index), `GET /register/pdf/{staff}/{month}` (exportPdf). Under `permission:register.create`: `POST /register` (store), `POST /lesson-plans` (store), `PATCH /lesson-plans/{id}` (update), `DELETE /lesson-plans/{id}` (destroy).

**Action required**: `php artisan tenants:migrate` to create `class_registers` and `lesson_plans` tables.

### 33 — Staff Leave Management

**Migration** (`leave_requests`): UUID PK; staff_id FK → staff (cascade); leave_type enum (sick/annual/maternity/paternity/personal/other); start_date/end_date date; reason text; status enum (pending/approved/rejected) default pending; approved_by FK → users (null on delete); approved_at timestamp nullable; rejection_reason text nullable.

**LeaveRequest model**: HasUuids; `leave_type_label` accessor (human-readable); `leave_days` accessor (diffInDays + 1).

**LeaveController**:
- `index()` — loads `currentStaff` (Staff where user_id = Auth::id()); `myRequests` paginated on `my_page`; if `canManage`: `pendingRequests` (pending, oldest start_date) + `historyRequests` paginated on `hist_page`
- `store()` — validates leave_type/start_date/end_date/reason; finds staff by user_id; creates LeaveRequest; sends `LeaveRequestSubmitted` to all User::role('school_admin')
- `approve(LeaveRequest)` — `abort_unless(leave.manage)` in controller (route is leave.view); updates status=approved/approved_by/approved_at; notifies staff user via `LeaveRequestDecided`
- `reject(Request, LeaveRequest)` — same guard; validates rejection_reason; updates status=rejected; notifies staff user

**Notifications**:
- `LeaveRequestSubmitted`: mail to school_admin users; subject "Leave Request — {staffName}"; body: type + dates + review prompt
- `LeaveRequestDecided`: mail to staff user; subject "Leave Request {Approved/Rejected} — {type}"; shows rejection_reason if rejected

**View** (`tenant/leave/index.blade.php`): Alpine `leavePage(initialTab)` — initialTab = 'all' if canManage else 'my'. My Requests tab: submit form card (1/3 col) + history table (2/3 col). All Requests tab: pending table with Approve form + inline Reject textarea (Alpine `rejectId`/`rejectReason` state, toggle per row) + history table. Status badges: pending=bg-warning-light text-warning; approved=bg-success-lightest text-success-foreground; rejected=bg-error-light text-error. Pending count badge shown on All Requests tab button.

**Permissions**: `leave.view` + `leave.manage` added to `$all`; school_admin gets both via `$all`; teacher and accountant get `leave.view`.

**StaffAttendance integration**: `AttendanceController::staff()` queries approved LeaveRequests overlapping `$date` (start_date ≤ date ≤ end_date); for each matching staff with no existing attendance record, injects a virtual `StaffAttendance` with `status = 'on_leave'` into `$existingRecords`; `SaveStaffAttendanceRequest` validation updated to allow `on_leave`; staff attendance view adds "On Leave" button (`bg-accent/10 text-accent`) and read-only badge.

**Routes** (all under `permission:leave.view` middleware):
- `GET /leave` → `index`
- `POST /leave` → `store`
- `PATCH /leave/{leaveRequest}/approve` → `approve`
- `PATCH /leave/{leaveRequest}/reject` → `reject`

**Action required**: `php artisan tenants:migrate` to create `leave_requests` table in all tenant databases.

### 50 — Financial P&L Dashboard

**FinancialSummaryService** (`app/Services/FinancialSummaryService.php`):
- `build(string $academicYearId, ?string $termId): array` — resolves date range from AcademicYear or Term; uses closure-based query factories to avoid Query Builder clone issues
- Income: `FeePayment` JOIN `fee_structures` WHERE `term_id` or `academic_year_id`; grouped by `fee_item` for breakdown; grouped by YEAR/MONTH(paid_at) for trend
- Expenses: `Expense` JOIN `expense_categories` WHERE `expenses.date BETWEEN dateFrom AND dateTo`; grouped by category name; grouped by YEAR/MONTH(date) for trend
- `buildMonthlyTrend()`: iterates month-by-month from dateFrom to dateTo using `keyBy("{yr}-{mo}")` lookup for O(1) access; returns array of {month, income, expenses}
- Date fallback: if year/term have no start/end dates, defaults to `now()->startOfYear()` / `now()->endOfYear()`

**ReportController** additions:
- Constructor gains `FinancialSummaryService $financialSummaryService`
- `index()`: gains `$academicYears = AcademicYear::orderByDesc('start_date')->get()`; `$financialReport = null`; `$selectedFinancialYearId`, `$selectedFinancialTermId` from request; loads financial report when `$activeTab === 'financial' && request has financial_year_id`; passes all to view
- `financialPdf()`: validates `financial_year_id` (required uuid) + `financial_term_id` (nullable uuid); calls service; renders `financial-pdf.blade.php` A4 landscape

**reports/index.blade.php** additions:
- PHP block: `$financialChartData` (labels/income/expenses arrays); `$academicYearsForJs` (id, name, terms[id,name]); passed as 4 extra args to `reportsPage()`
- Alpine: `financialYearId`, `financialTermId` state; computed `financialTermsForYear` (cascading term filter); `initFinancialChart()` draws Chart.js grouped bar (income=green, expenses=red); `init()` calls `initFinancialChart()` when tab=financial
- "Financial Summary" tab button added after "Academic Analytics"
- Tab content: filter bar (Academic Year select → Term select cascading via Alpine); 3 summary cards (Total Income/green, Total Expenses/red, Net Balance/colour-coded); Export PDF link; monthly trend grouped bar chart (`id="financialTrendChart"`, 220px height); two-column breakdown tables (income by fee item, expenses by category); empty state

**financial-pdf.blade.php** (A4 landscape, self-contained dompdf):
- Header: school name + "Financial Summary Report" + period label + generated date
- Summary cards: display:table three-cell row with income/expense/net figures
- Two-column breakdown: `display:table-cell` 50%/50% with breakdown-table for each
- Monthly trend table: month | income | expenses | net (net colour-coded)
- All negative net shown as (amount) in red

**Route**: `GET /reports/financial/pdf` → `ReportController::financialPdf()` (inside permission:reports.view group)

### 48 — Payroll & Staff Salary Management

**Migrations** (3 tenant migrations):
- `salary_structures`: standard bigint id; `staff_id` foreignUuid unique (one-to-one); `gross` decimal(15,2); `allowances`/`deductions` JSON (default `{}`); `effective_from` date nullable
- `payroll_runs`: uuid primary key; `month` tinyint unsigned, `year` smallint unsigned, unique([month,year]); `status` enum(draft|processed); `processed_by` foreignUuid nullable→users nullOnDelete; `processed_at` timestamp nullable
- `payroll_items`: uuid primary key; `payroll_run_id` foreignUuid cascadeOnDelete; `staff_id` foreignUuid cascadeOnDelete; `gross`/`allowances_total`/`deductions_total`/`net` decimal(15,2); `payment_status` enum(pending|paid)

**Models**: `SalaryStructure` (hasOne via staff_id; computed accessors: allowances_total, deductions_total, net); `PayrollRun` (HasUuids; period_label/month_name/total_net accessors); `PayrollItem` (HasUuids). `Staff::salaryStructure()` HasOne added.

**PayrollService**:
- `runPayroll(int $month, int $year)` — DB::transaction; creates PayrollRun(status:processed); queries SalaryStructure whereHas active staff; bulk-inserts PayrollItem rows with manually generated Str::uuid() IDs
- `logAsExpense(PayrollRun)` — firstOrCreate 'Salaries' ExpenseCategory; creates Expense for total net pay, date = end of month, description = "Payroll — {month year}"

**PayrollController** (5 actions):
- `index()` — active staff with salaryStructure + paginated runs with items.staff
- `updateSalaryStructure(PATCH, Staff)` — permission:payroll.edit; SalaryStructure::updateOrCreate with array_merge defaults for allowances/deductions keys
- `runPayroll(POST)` — permission:payroll.create; checks for existing run; calls service; redirects to ?tab=runs
- `downloadPayslip(GET, PayrollRun, PayrollItem)` — permission:payroll.view; aborts if item not in run; streams dompdf PDF
- `logAsExpense(POST, PayrollRun)` — permission:payroll.create; aborts if status≠processed

**Routes** (`routes/tenant.php`): `GET /payroll`, `PATCH /payroll/salary/{staff}`, `POST /payroll/run`, `GET /payroll/{run}/{item}/payslip`, `POST /payroll/{run}/expense`

**UI** (`tenant/payroll/index.blade.php`): Alpine two-tab layout (tab state from `?tab=` query param). Tab 1 (Salary Structures): table of active staff with gross/allowances/deductions/net columns; per-row Edit/Set Up button opens shared edit modal with dynamic `:action` URL, gross field, effective_from, 4-field allowances grid (housing/transport/medical/other), 4-field deductions grid (tax/pension/loan/other). Tab 2 (Payroll Runs): collapsible rows (chevron toggle via `expandedRun`); each run shows period, status badge, staff count, total net, processed by; Log Expense form per processed run; expanded view shows nested staff items table with individual payslip Download links.

**Payslip PDF** (`tenant/payroll/payslip-pdf.blade.php`): Self-contained inline CSS; school header + payslip title + period; staff info table; Earnings table (gross + itemised allowances from SalaryStructure.allowances if available, else total); Deductions table (itemised from SalaryStructure.deductions); dark-blue Net Pay highlighted box; signature lines; generated-on footer.

**Permissions**: `payroll.view`, `payroll.create`, `payroll.edit` added to all list; school_admin gets all; accountant gets all three.

**Sidebar**: "Payroll" nav item added after "Expenses" (permission: payroll.view).

**Action required**: Run `php artisan tenants:migrate` to create the 3 payroll tables in all existing tenant databases.

---

## Notes

- Tailwind `tailwind.config.js` still exists in the project root but is ignored by Tailwind v4 (no `@config` import in app.css). Can be deleted once confirmed no other tooling reads it.
- The old `layouts/app.blade.php` and `layouts/guest.blade.php` (Breeze defaults) are still present — they will be replaced or removed in Phase 1.4 (Auth & Roles).
- The `routes/auth.php` (Breeze) is still included; auth routes point to the default Breeze views which will be updated in Phase 1.4.
- A test `demo` tenant exists in the `schoolflow` DB with domain `demo.schoolflow.test` and DB `tenantdemo`. Remove before production or in Phase 03 seeder.
