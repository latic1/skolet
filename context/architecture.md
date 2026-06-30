# Architecture

## Stack

| Layer               | Tool                                  | Purpose                                           |
| ------------------- | -------------------------------------- | --------------------------------------------------- |
| Framework           | Laravel 11 (PHP 8.3)                  | Full stack framework                                 |
| Multi-tenancy       | stancl/tenancy                        | Per-school isolated databases, subdomain routing     |
| Frontend            | Blade + Livewire + Alpine.js          | Server-driven UI, dynamic forms/tables               |
| Styling             | Tailwind CSS v4 + Flowbite/Preline     | Component-based styling on top of design tokens      |
| API (mobile, Phase 2) | Laravel Sanctum                      | Token auth for future mobile apps                    |
| Database            | MySQL 8                               | Central DB (tenants/billing) + per-tenant DBs        |
| Auth & Roles        | Laravel Breeze + spatie/laravel-permission | Authentication scaffolding, role/permission management |
| Payments            | Paystack PHP SDK                      | Online fee collection                                |
| PDF generation      | barryvdh/laravel-dompdf                | Report cards, receipts, certificates                 |
| Queues              | Laravel Queue + Redis                 | Emails, PDF generation, reminders                    |
| Email               | Laravel Mail + Mailtrap (dev) / Mailgun (prod) | Notifications                              |
| File storage        | Laravel filesystem (local → DO Spaces) | Student/staff documents, logos                       |
| Web server / SSL    | Caddy                                 | Wildcard SSL for `*.schoolflow.com`, plus auto-SSL per custom domain on first request |
| Hosting             | DigitalOcean Droplet + Forge          | Application hosting                                  |
| Language            | PHP 8.3                               | Throughout                                           |

---

## Folder Structure

```
/
├── AGENTS.md
├── context/
│   ├── project-overview.md
│   ├── architecture.md
│   ├── ui-tokens.md
│   ├── ui-rules.md
│   ├── ui-registry.md
│   ├── code-standards.md
│   ├── build-plan.md
│   └── progress-tracker.md
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Central/
│   │   │   │   ├── LandingController.php
│   │   │   │   ├── PricingController.php
│   │   │   │   ├── SchoolRegistrationController.php   → New school signup
│   │   │   │   ├── SuperAdminAuthController.php       → Super Admin login (guard: super_admin)
│   │   │   │   ├── SuperAdminController.php           → Manage tenants, subscriptions
│   │   │   │   └── ImpersonationController.php        → Start/stop "login as school"
│   │   │   ├── Auth/                                  → Breeze auth controllers
│   │   │   └── Tenant/
│   │   │       ├── DashboardController.php
│   │   │       ├── StudentController.php
│   │   │       ├── StaffController.php
│   │   │       ├── AttendanceController.php
│   │   │       ├── TimetableController.php
│   │   │       ├── ExamController.php
│   │   │       ├── FeeController.php
│   │   │       ├── AnnouncementController.php
│   │   │       └── AccountController.php                → Logged-in user's own profile (any role)
│   │   ├── Middleware/
│   │   │   ├── InitializeTenancyBySubdomain.php       → stancl/tenancy middleware
│   │   │   ├── EnsureUserHasRole.php
│   │   │   └── ResumeImpersonation.php                → Logs in as impersonated user if session flag matches current tenant
│   │   └── Requests/                                  → Form Request validation classes
│   ├── Models/
│   │   ├── Central/
│   │   │   ├── Tenant.php
│   │   │   ├── Domain.php
│   │   │   ├── SubscriptionPlan.php
│   │   │   └── SuperAdmin.php          → Authenticatable, guard: super_admin
│   │   └── Tenant/
│   │       ├── User.php
│   │       ├── AcademicYear.php
│   │       ├── Term.php
│   │       ├── Student.php
│   │       ├── Staff.php
│   │       ├── SchoolClass.php
│   │       ├── Section.php
│   │       ├── Subject.php
│   │       ├── Attendance.php
│   │       ├── Timetable.php
│   │       ├── Exam.php
│   │       ├── ExamResult.php
│   │       ├── FeeBundle.php
│   │       ├── FeeStructure.php
│   │       ├── FeePayment.php
│   │       ├── Announcement.php
│   │       └── SchoolProfile.php
│   ├── Livewire/
│   │   ├── Students/
│   │   │   ├── StudentList.php
│   │   │   └── StudentForm.php
│   │   ├── Attendance/
│   │   │   └── DailyAttendance.php
│   │   ├── Exams/
│   │   │   ├── MarksEntry.php
│   │   │   └── ReportCard.php
│   │   ├── Fees/
│   │   │   ├── FeeStructureForm.php
│   │   │   └── FeeCollection.php
│   │   └── Dashboard/
│   │       ├── StatsBar.php
│   │       └── RecentActivity.php
│   ├── Services/
│   │   ├── TenantProvisioningService.php              → Creates tenant DB, runs migrations
│   │   ├── PaystackService.php                        → Initialize/verify payments
│   │   ├── ReportCardService.php                      → Builds report card data + PDF
│   │   ├── FeeStatusService.php                       → Computes paid/unpaid/partial/overdue per student+fee_structure, and per-bundle
│   │   ├── ReceiptService.php                         → Allocates a payment across bundle items, generates shared receipt_number, builds receipt PDF
│   │   └── AttendanceReportService.php
│   ├── Console/
│   │   └── Commands/
│   │       └── SyncTenantStudentCounts.php            → Daily: loops tenants, counts students, updates subscription_plans
│   └── Providers/
├── routes/
│   ├── web.php                → Central app routes (landing, registration, super admin)
│   ├── tenant.php              → Tenant app routes (dashboard, students, fees, etc.)
│   └── api.php                 → Sanctum API routes (Phase 2 mobile)
├── database/
│   ├── migrations/             → Central DB migrations (tenants, domains, plans)
│   └── migrations/tenant/      → Per-tenant DB migrations (students, fees, attendance, etc.)
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── central.blade.php
│       │   └── tenant.blade.php       → Sidebar + topbar layout
│       ├── central/
│       │   ├── landing.blade.php
│       │   ├── pricing.blade.php
│       │   └── register-school.blade.php
│       ├── tenant/
│       │   ├── public-page.blade.php  → Auto-generated school public page
│       │   ├── dashboard.blade.php
│       │   ├── students/
│       │   ├── staff/
│       │   ├── attendance/
│       │   ├── timetable/
│       │   ├── exams/
│       │   ├── fees/
│       │   └── announcements/
│       └── livewire/                  → Livewire component views
└── config/
    └── tenancy.php              → stancl/tenancy configuration
```

---

## System Boundaries

| Folder                     | Owns                                                                                    |
| -------------------------- | ----------------------------------------------------------------------------------------- |
| `Http/Controllers/Central/` | Routes/logic for `schoolflow.com` only — signup, billing, Super Admin. No tenant data. |
| `Http/Controllers/Tenant/`  | Routes/logic for `{school}.schoolflow.com`. Always operates within the resolved tenant context. |
| `Livewire/`                 | Interactive UI components — forms, tables, dashboards. Calls Services or Models, never raw queries to other tenants. |
| `Services/`                 | Business logic — tenant provisioning, Paystack, report card generation. No HTTP concerns. |
| `Models/Central/`           | Central DB models only — Tenant, Domain, SubscriptionPlan.                              |
| `Models/Tenant/`             | Per-tenant DB models — Student, Staff, Attendance, etc. Never queried without tenant context. |

---

## Data Flow

### Tenant Resolution (every request to `*.schoolflow.com`)

```
Incoming request to {school}.schoolflow.com
        ↓
InitializeTenancyBySubdomain middleware
        ↓
stancl/tenancy resolves Tenant from subdomain
        ↓
Database connection switched to tenant database
        ↓
Request proceeds to Tenant controller/Livewire component
```

### School Registration (Central)

```
Visitor submits Register School form
        ↓
SchoolRegistrationController validates input
        ↓
TenantProvisioningService:
  - creates Tenant record (central DB)
  - creates Domain record ({school}.schoolflow.com)
  - creates tenant database
  - runs tenant migrations
  - creates School Admin user (tenant DB)
        ↓
Redirect to {school}.schoolflow.com/login
```

### Daily Attendance

```
Teacher opens Attendance page for their class
        ↓
DailyAttendance Livewire component loads enrolled students
        ↓
Teacher marks each student present/absent/late
        ↓
Component saves Attendance records (tenant DB)
        ↓
Dashboard "recent activity" and reports reflect new records
```

### Exam Marks → Report Card

```
Teacher enters marks per subject via MarksEntry component
        ↓
ExamResult records saved (tenant DB)
        ↓
ReportCardService applies grading scale to compute grades/GPA
        ↓
dompdf renders report card PDF
        ↓
PDF available for download / print
```

### Fee Payment (Paystack)

```
Parent/Admin opens Fees page
        ↓
FeeStatusService computes status per fee_structure (paid/unpaid/partial/overdue)
        ↓
Parent selects an outstanding fee_structure, clicks "Pay Now"
        ↓
FeeController calls PaystackService::initializeTransaction()
  — student_id, fee_structure_id, outstanding amount, callback URL
        ↓
Redirect to Paystack checkout
        ↓
Paystack webhook confirms payment
        ↓
PaystackService::verifyTransaction() confirms with Paystack's API
        ↓
fee_payments row created (student_id, fee_structure_id, amount, 
payment_method=paystack, paystack_ref set)
        ↓
FeeStatusService recomputes status for that fee_structure
        ↓
Receipt generated via dompdf
```

### Subscription Billing Sync (Scheduled, Daily)

```
SyncTenantStudentCounts command runs (Laravel Scheduler, daily)
        ↓
For each tenant in central `tenants` table:
        ↓
  Tenant::run($tenant, function () {
      count rows in tenant's `students` table
  })
        ↓
  Update central `subscription_plans` for this tenant:
      student_count = count
      student_count_synced_at = now()
      amount_due = rate_per_student * student_count
        ↓
Super Admin dashboard (Feature 21) reflects updated 
student_count/amount_due on next page load
```

This is the only place a "loop over all tenants" pattern is allowed — every iteration switches tenant context via `Tenant::run()` and only reads (never writes student data), strictly to produce a cached count in the central DB.

---

## Central Database Schema

### `tenants`

| Column       | Type        | Notes                          |
| ------------ | ----------- | ------------------------------- |
| id           | uuid        |                                 |
| name         | string      | School name                    |
| subdomain    | string      | e.g. `exampleschool`           |
| database     | string      | Tenant database name             |
| status       | string      | active / suspended              |
| created_at   | timestamp   |                                 |

### `domains`

| Column     | Type      | Notes                                              |
| ---------- | --------- | ---------------------------------------------------- |
| id         | uuid      |                                                       |
| tenant_id  | uuid      | References tenants                                   |
| domain     | string    | Default `{subdomain}.schoolflow.com`, or a custom domain (e.g. `admin.exampleschool.com`) added via CNAME |

### `subscription_plans`

Billing is per academic year (one cycle per tenant per year), priced per student.

| Column            | Type      | Notes                                                          |
| ------------------ | --------- | -------------------------------------------------------------- |
| id                  | uuid      |                                                                |
| tenant_id           | uuid      | References tenants                                             |
| rate_per_student    | decimal   | Price per student for the year — set per tenant by Super Admin (allows negotiated/discounted rates), defaults to a standard rate |
| student_count       | integer   | Cached count of students in this tenant's database — updated by the daily `SyncTenantStudentCounts` job |
| student_count_synced_at | timestamp | When `student_count` was last refreshed                  |
| amount_due          | decimal   | Computed: `rate_per_student * student_count` — recalculated whenever `student_count` syncs or `rate_per_student` changes |
| payment_status      | string    | unpaid / paid — set manually by Super Admin after offline payment |
| cycle_start         | date      | Start of the current annual billing cycle                      |
| cycle_end           | date      | End of the current annual billing cycle (renewal due date)      |
| status              | string    | trial / active / expired — `expired` blocks tenant login (see Feature 21) |

**MVP payment flow:** Super Admin sets `rate_per_student` per school (or accepts the default), the `SyncTenantStudentCounts` job keeps `student_count`/`amount_due` current, the school pays SchoolFlow offline (bank transfer, etc.), and Super Admin flips `payment_status` to `paid` and updates `cycle_start`/`cycle_end` for the next year. Self-service Paystack billing for this subscription (school paying SchoolFlow directly in-app) is Phase 2 — see Roadmap.

### `super_admins`

| Column     | Type      | Notes                          |
| ---------- | --------- | -------------------------------- |
| id         | uuid      |                                  |
| name       | string    |                                  |
| email      | string    |                                  |
| password   | string    | Hashed                            |

Authenticated via a dedicated `super_admin` guard — entirely separate from tenant `users` tables. Created manually (e.g. via `artisan tinker` or a seeder) — no public signup.

### `impersonation_logs`

Audit trail for every "Login as School" session — central DB, never written to from within a tenant connection.

| Column          | Type      | Notes                                  |
| ---------------- | --------- | ------------------------------------------ |
| id                | uuid      |                                           |
| super_admin_id    | uuid      | References super_admins                  |
| tenant_id         | uuid      | References tenants                        |
| impersonated_user_id | uuid  | The tenant `users.id` impersonated (typically the School Admin) |
| started_at        | timestamp |                                           |
| ended_at          | timestamp | Nullable — set on explicit exit or session expiry |

---

## Tenant Database Schema (per school)

### `users`

| Column      | Type    | Notes                                              |
| ----------- | ------- | ---------------------------------------------------- |
| id          | uuid    |                                                       |
| name        | string  |                                                       |
| email       | string  |                                                       |
| phone       | string  | Nullable                                              |
| avatar_path | string  | Nullable — path under `storage/{tenant}/avatars/{user_id}/` |
| role        | string  | school_admin / teacher / accountant / student / parent / custom (via spatie/laravel-permission roles + permissions tables, per tenant DB) |

Custom roles created by School Admin are stored in spatie's standard `roles` and `permissions` tables within the tenant database — no separate schema needed. Each custom role is a row in `roles`, with granular permissions (e.g. `students.view`, `fees.edit`) attached via `role_has_permissions`.

### `students`

| Column          | Type    | Notes                                |
| --------------- | ------- | --------------------------------------- |
| id              | uuid    |                                         |
| admission_no    | string  | Auto-generated                          |
| user_id         | uuid    | Linked login (student/parent), nullable |
| full_name       | string  |                                         |
| class_id        | uuid    | References school_classes               |
| section_id      | uuid    | References sections — nullable; null if the class has no sections |
| guardian_name   | string  |                                         |
| guardian_contact | string |                                         |

### `staff`

| Column     | Type    | Notes                |
| ---------- | ------- | ---------------------- |
| id         | uuid    |                       |
| user_id    | uuid    | References users      |
| full_name  | string  |                       |
| role_title | string  | e.g. Class Teacher    |

### `school_classes`, `sections`, `subjects`

Standard lookup tables — class name, section name, subject name, linked by foreign keys. `sections` has a `class_id` foreign key but is **optional per class** — a class may have zero sections (small schools that don't subdivide a grade) or several (e.g. Grade 5A, 5B, 5C). Anywhere a class/section is selected (students, attendance, timetable, marks entry, reports), the section field is conditional on whether the chosen class has any `sections` rows.

`school_classes` includes an `order` column (integer) — the sequence of classes from lowest to highest (e.g. Primary 1 = 1, Primary 2 = 2, ... JHS 3 = 9). This is set during Feature 07 and has no effect in MVP, but is required by the Phase 2 promotion feature below to determine each student's "next class."

**Constraints:** `school_classes.name` and `school_classes.order` are both unique within a tenant database (database-level unique indexes, enforced via migration, plus Form Request validation for a human-readable error). Two classes cannot share a name (e.g. two "Primary 4" rows) or the same `order` value (since `order` must define an unambiguous sequence for promotion). `sections.name` is unique per `class_id` (e.g. two sections named "A" can exist, but not two "A" sections under the same class).

### `academic_years`

Set up in Feature 07's "Academic Year" settings page.

| Column     | Type    | Notes                          |
| ---------- | ------- | -------------------------------- |
| id         | uuid    |                                  |
| name       | string  | e.g. "2025/2026"                 |
| start_date | date    |                                  |
| end_date   | date    |                                  |
| is_current | boolean | Only one row is current at a time |

**Constraint:** `academic_years.name` is unique within a tenant database — database-level unique index plus Form Request validation, with a human-readable error (e.g. "An academic year named '2025/2026' already exists").

### `terms`

Terms are **not manually created** — they are auto-generated when an `academic_years` row is created, based on `school_profile.period_system`:

- `3_term` → generates "Term 1", "Term 2", "Term 3" rows under that academic year
- `2_semester` → generates "Semester 1", "Semester 2" rows under that academic year

| Column           | Type    | Notes                              |
| ----------------- | ------- | -------------------------------------- |
| id                | uuid    |                                       |
| academic_year_id  | uuid    | References academic_years             |
| name              | string  | Auto-derived: "Term 1/2/3" or "Semester 1/2" |
| start_date        | date    | Nullable — admin can optionally fill these in |
| end_date          | date    | Nullable                               |
| is_current        | boolean | Only one row is current at a time across all academic years |

**On academic year creation:** `TenantProvisioningService` (or the Academic Year controller) immediately inserts the correct number of `terms` rows for the new year based on `period_system`. The admin never manually adds terms — they only set start/end dates on existing rows (optional) and mark one as `is_current`.

**Constraint:** `terms.name` is unique per `academic_year_id`.

**Period system change:** not supported in MVP once data (`exams`, `fee_structures`) exists against terms. Treat `period_system` as a one-time setup choice made before any exams or fees are created.

`terms` is the entity referenced everywhere "term" appears: `exams.term_id`, `fee_structures.term_id`, the dashboard's "Fees Collected This Term" stat, and the Fee Collection Report's per-term filter (Feature 20). Term names displayed in dropdowns come directly from these auto-generated rows.

---

### Phase 2 — Promotion Schema (not built in MVP)

#### `student_class_history`

| Column           | Type    | Notes                                              |
| ----------------- | ------- | ----------------------------------------------------- |
| id                | uuid    |                                                       |
| student_id        | uuid    | References students                                  |
| academic_year_id  | uuid    | References academic_years                            |
| class_id          | uuid    | References school_classes — class held during that year |
| section_id        | uuid    | References sections, nullable                         |
| outcome           | string  | promoted / retained / graduated                       |

When the year-end promotion workflow runs: a `student_class_history` row is written for the outgoing academic year (preserving the class/section the student was actually in, so old report cards/attendance remain correctly attributed), then `students.class_id`/`section_id` is updated to the next class in `order` (promoted), left unchanged (retained), or the student is marked inactive (graduated, if they were in the highest-`order` class). The new academic year's row in `academic_years` has `is_current` set, and a corresponding `terms` row is activated as the workflow progresses through the year.

### `attendances`

| Column     | Type    | Notes                            |
| ---------- | ------- | ----------------------------------- |
| id         | uuid    |                                     |
| student_id | uuid    | References students                 |
| date       | date    |                                     |
| status     | string  | present / absent / late             |
| marked_by  | uuid    | References users (teacher)          |

### `timetables`

| Column     | Type    | Notes                  |
| ---------- | ------- | ------------------------ |
| id         | uuid    |                          |
| class_id   | uuid    | References school_classes |
| subject_id | uuid    | References subjects       |
| teacher_id | uuid    | References staff          |
| day        | string  |                          |
| period     | integer |                          |

### `exams`

| Column     | Type    | Notes                  |
| ----------- | ------- | ------------------------ |
| id          | uuid    |                          |
| term_id     | uuid    | References terms          |
| name        | string  | e.g. "Mid-Term Exam"       |
| start_date  | date    |                          |
| end_date    | date    |                          |
| is_published | boolean | Marks/report cards visible to students/parents once true (Feature 13) |

### `exam_results`

| Column     | Type    | Notes                  |
| ---------- | ------- | ------------------------ |
| id         | uuid    |                          |
| exam_id    | uuid    | References exams         |
| student_id | uuid    | References students      |
| subject_id | uuid    | References subjects      |
| marks      | decimal | Out of 100 for every subject (see note below) |
| grade      | string  | Computed from grading scale |

**MVP assumption:** every subject's `marks` is recorded out of 100, so `marks` is directly the percentage used against `config('schoolflow.default_grading_scale')` — no per-subject max-marks conversion. Marks Entry (Feature 12) validates input as `0-100`. If a school needs subjects scored out of a different total (e.g. 50 or 20), this is a Phase 2 change: add a `max_marks` column to `exams` or `exam_results`, store raw marks, and compute `percentage = marks / max_marks * 100` before applying the grading scale.

### `fee_bundles`

A bundle groups several `fee_structures` rows under one parent label that the parent sees and pays as a single total — e.g. "First Semester Fees" bundling Medical Bill + PTA Dues + School Fees + Toiletries. Bundles are how a school presents "one bill" to parents while still tracking each fee item individually for internal accounting.

| Column        | Type    | Notes                                                   |
| -------------- | ------- | ----------------------------------------------------------- |
| id             | uuid    |                                                             |
| academic_year_id | uuid  | References academic_years                                   |
| term_id        | uuid    | References terms — nullable, same `billing_cycle` rules as `fee_structures` |
| name           | string  | e.g. "First Semester Fees", "Term 3 Bill"                    |
| target_class   | string  | `all` or a specific `class_id`                              |
| due_date       | date    | Nullable                                                    |

**Bundle total** is always computed, never stored: sum of `amount` across every `fee_structures` row where `fee_bundle_id` matches.

### `fee_structures`

One row per individual fee configuration. Supports any fee type — School Fees, Feeding, Hostel, Bus, PTA Dues, Extra Classes, Toileteries, Medical Bill, Extra Curricular Activities, etc. All fees are flat amounts (the admin computes any daily-rate or per-day calculations themselves before entering the total).

| Column        | Type    | Notes                                                   |
| -------------- | ------- | ----------------------------------------------------------- |
| id             | uuid    |                                                             |
| fee_bundle_id  | uuid    | References fee_bundles — nullable. If set, this item is part of a bundle and the parent pays the bundle total, not this item individually. If null, this fee is collected and paid standalone (current default behavior). |
| academic_year_id | uuid  | References academic_years — always set                      |
| term_id        | uuid    | References terms — nullable. Null if `billing_cycle = 'annual'` |
| fee_item       | string  | Admin-defined name e.g. "School Fees", "Feeding (Lunch)", "Hostel", "Bus", "PTA Dues", "Medical Bill", "Toiletries" |
| amount         | decimal |                                                             |
| target_class   | string  | `all` or a specific `class_id` — inherited from the bundle's `target_class` if bundled, but stored per-row for reporting flexibility |
| billing_cycle  | string  | `term` (default) or `annual`. Annual fees belong to `academic_year_id` only — `term_id` is null. Shown on every term's bill but owed once per year. |
| is_mandatory   | boolean | Default `true`. MVP: all fees behave as mandatory. Phase 2: optional per-student assignment |
| due_date       | date    | Nullable — bundled items typically inherit the bundle's due date for display, but each row keeps its own for reporting |

**Annual fees (e.g. PTA Dues) inside a bundle:** still possible — a bundle can mix `term` and `annual` items (matching the photo example: PTA Dues alongside Tuition Fee in one receipt). `FeeStatusService` still tracks the annual item's once-per-year status independently even when bundled; once paid, it stops contributing to the bundle's outstanding total in later terms but the bundle itself persists for the other (term) items.

### `fee_payments`

One row per individual fee item paid within a transaction — when a parent pays a bundle total, multiple `fee_payments` rows are created at once (one per component `fee_structures` row), all sharing the same `receipt_number` so they print together as one receipt.

| Column            | Type      | Notes                                                       |
| ------------------ | --------- | ----------------------------------------------------------------- |
| id                  | uuid      |                                                                   |
| student_id          | uuid      | References students                                              |
| fee_structure_id    | uuid      | References fee_structures                                        |
| receipt_number      | string    | Shared across every `fee_payments` row created in the same transaction — e.g. all rows from one bundle payment have the same `receipt_number`. Sequential, school-scoped (e.g. `RASN26.05.01`, configurable prefix per school). |
| amount              | decimal   | Amount paid toward this specific fee item in this transaction      |
| payment_method      | string    | cash / paystack                                                   |
| paystack_ref        | string    | Nullable — set when paid online                                   |
| recorded_by         | uuid      | Nullable — references users; set for cash payments                |
| paid_at             | timestamp |                                                                   |

**Paying a bundle:** when a parent/Accountant pays a bundle total, the payment amount is allocated across the bundle's component `fee_structures` rows proportionally (or in a fixed order — oldest/cheapest first — if the payment is partial and doesn't cover the full bundle). Each allocation becomes its own `fee_payments` row, all sharing one `receipt_number`. This is what `ReceiptService` (Feature 17) groups by when rendering the printed receipt — one receipt, multiple fee-item line items, one total.

**Which fee_structures/bundles apply to a student:**
- `target_class = 'all'` OR `target_class = student.class_id`
- AND (`billing_cycle = 'term'` AND `term_id = current term`) OR (`billing_cycle = 'annual'` AND `academic_year_id = current academic year`)
- Bundled items (`fee_bundle_id` set) are grouped and shown as one bundle card with one total on the Fees page; unbundled items (`fee_bundle_id` null) are shown individually as today

Annual fees (`billing_cycle = 'annual'`) appear on every term's bill/bundle as a line item for transparency, but `FeeStatusService` checks `fee_payments` across all terms of the current academic year — if already paid in any previous term, it shows as `paid` and is not collected again, and its amount drops out of the *current* bundle's outstanding total (though it may still display as a zero-balance line item for transparency).

**Status (computed, not stored):**

For an individual `fee_structures` row: sum all `fee_payments.amount` rows for that `fee_structure_id` + student, compare to `fee_structures.amount`:
- `unpaid` — sum is 0
- `partial` — `0 < sum < amount`
- `paid` — `sum >= amount`
- `overdue` — `sum < amount` AND `due_date` has passed (overrides `unpaid`/`partial` for display)

For a bundle: sum the same logic across every component `fee_structures` row, then compare the combined paid total to the combined bundle total. Same four statuses, computed at the bundle level for what the parent sees.

`FeeStatusService` computes both levels on the fly — no `status` column to keep in sync.

### `announcements`

| Column     | Type      | Notes              |
| ---------- | --------- | -------------------- |
| id         | uuid      |                     |
| title      | string    |                     |
| body       | text      |                     |
| posted_by  | uuid      | References users    |
| created_at | timestamp |                     |

### `school_profile`

Single row per tenant — set up during Feature 07b, used throughout the app (topbar, login page, public page, generated PDFs).

| Column        | Type    | Notes                                              |
| -------------- | ------- | ------------------------------------------------------ |
| id             | uuid    |                                                        |
| school_name    | string  |                                                        |
| motto          | string  | Nullable — shown under school name on receipts/bills and public page header, e.g. "Developing Innovative and Pragmatic Leaders" |
| description    | text    | Nullable — short description, used on public page      |
| address        | string  | Nullable                                               |
| phone          | string  | Nullable                                               |
| email          | string  | Nullable                                               |
| logo_path      | string  | Nullable — path under `storage/{tenant}/logos/`         |
| receipt_prefix | string  | Nullable — short code used to generate `receipt_number`, e.g. school initials (defaults to first letters of `school_name` if not set) |
| period_system  | string  | `3_term` (default) or `2_semester` — set once during Feature 07 Academic Calendar setup; determines what "Term 1/2/3" or "Semester 1/2" labels appear throughout the app. Changing this after data exists against old terms is not supported in MVP — treat as a one-time setup choice. |

If `logo_path` is null, the topbar/login/public page fall back to the default SchoolFlow logo gradient (`ui-tokens.md`), and generated PDFs (report cards, receipts) show `school_name` as text only, no image.

---

## File Storage

| Disk/Path                            | Contents                          |
| ------------------------------------- | ------------------------------------ |
| `storage/{tenant}/logos/`             | School logo, used on public page and branding |
| `storage/{tenant}/students/{id}/docs/` | Student documents (birth certificate, ID, etc.) |
| `storage/{tenant}/receipts/`           | Generated fee receipt PDFs           |
| `storage/{tenant}/report-cards/`       | Generated report card PDFs           |

MVP uses local disk; migrate to DigitalOcean Spaces when scaling beyond a single droplet.

---

## Authentication

- Provider: Laravel Breeze (session-based) for web; Sanctum tokens for API (Phase 2)
- Two separate guards:
  - `web` guard — tenant `users` table (per tenant DB): school_admin, teacher, accountant, student, parent, custom roles via `spatie/laravel-permission`
  - `super_admin` guard — central `super_admins` table, completely separate authentication, no role/permission system needed (Super Admin has unrestricted access by definition)
- Protected tenant routes (`/dashboard`, `/students`, `/staff`, `/attendance`, `/timetable`, `/exams`, `/fees`, `/announcements`, `/reports`, `/settings/*`) — gated by `permission:{module}.view` middleware, not hardcoded role checks (see Route Structure)
- Protected central routes (`/super-admin/*`) — gated by `auth:super_admin`
- Public routes (tenant): `/` (public page), `/login`
- Public routes (central): `/`, `/pricing`, `/register-school`, `/super-admin/login`
- On tenant login → redirect to `/dashboard` (same route for all roles, content varies by permission)
- On Super Admin login → redirect to `/super-admin`

---

## Route Structure

### Central Routes (`routes/web.php`)

Domain: `schoolflow.com` (no subdomain, or `www`). Never resolves a tenant — only queries the central database.

```php
// Public
Route::get('/', LandingController::class)->name('landing');
Route::get('/pricing', PricingController::class)->name('pricing');
Route::get('/register-school', [SchoolRegistrationController::class, 'create'])->name('register-school');
Route::post('/register-school', [SchoolRegistrationController::class, 'store']);

// Super Admin auth
Route::get('/super-admin/login', [SuperAdminAuthController::class, 'showLogin'])->name('super-admin.login');
Route::post('/super-admin/login', [SuperAdminAuthController::class, 'login']);

// Super Admin (protected)
Route::middleware(['auth:super_admin'])->prefix('super-admin')->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('super-admin.dashboard');
    Route::patch('/tenants/{tenant}/toggle', [SuperAdminController::class, 'toggleStatus'])
        ->name('super-admin.tenants.toggle');
    Route::get('/tenants/{tenant}', [SuperAdminController::class, 'show'])->name('super-admin.tenants.show');
    Route::post('/tenants/{tenant}/impersonate', [ImpersonationController::class, 'start'])
        ->name('super-admin.tenants.impersonate');
});

// Impersonation exit — lives on the TENANT side since that's where the impersonated
// session is active, but is reachable regardless of which tenant guard state exists
Route::post('/stop-impersonating', [ImpersonationController::class, 'stop'])
    ->middleware(['web', InitializeTenancyBySubdomain::class])
    ->name('impersonation.stop');
```

- Super Admin uses a separate `super_admin` guard, authenticating against a `super_admins` table in the **central** database — entirely distinct from any tenant's `users` table.
- `SuperAdminController::index` lists all `tenants` with their `domains` and `subscription_plans` status — central DB queries only, no `Tenant::run()`.
- `toggleStatus` flips a tenant's `status` between `active`/`suspended`. A suspended tenant's `InitializeTenancyBySubdomain` check (tenant side) blocks login.

### Impersonation ("Login as School")

Lets a Super Admin view/use a tenant's app as that school's School Admin, for support purposes — without knowing or resetting the School Admin's password.

**Flow:**

```
Super Admin clicks "Impersonate" on a tenant (Feature 21)
        ↓
POST /super-admin/tenants/{tenant}/impersonate
        ↓
ImpersonationController::start():
  - Tenant::run($tenant, fn () => find or create the school_admin user for that tenant)
  - Store impersonation state in the Super Admin's session:
      session(['impersonating_tenant_id' => $tenant->id, 
               'impersonating_user_id' => $schoolAdmin->id,
               'super_admin_id' => auth('super_admin')->id()])
  - Redirect to {tenant_subdomain}/dashboard
        ↓
On the tenant subdomain, InitializeTenancyBySubdomain resolves the tenant as normal
        ↓
A dedicated middleware (ResumeImpersonation) checks the session for 
impersonating_tenant_id matching the current tenant — if present, logs in 
as impersonating_user_id on the tenant's `web` guard for this request
        ↓
Tenant app renders normally as that School Admin — with a persistent 
banner: "You are viewing as {School Name} (Super Admin impersonation) — [Exit]"
        ↓
"Exit" calls POST /stop-impersonating — clears the impersonation session keys, 
redirects back to /super-admin (central, re-authenticated via the super_admin guard, 
which was never logged out)
```

**Invariants:**

- Impersonation never logs the Super Admin out of the `super_admin` guard — the central session and the impersonated tenant `web` session coexist; exiting simply drops the tenant session and returns to the still-active Super Admin session.
- Impersonation is always read of the tenant's actual School Admin account — never a separate "ghost" account. Every action taken while impersonating is attributable to that real `users` row (so `recorded_by`, `posted_by`, etc. fields are accurate) — but every write made during an impersonated session is also logged to a central `impersonation_logs` table (`super_admin_id`, `tenant_id`, `action`, `created_at`) for audit purposes.
- Impersonation sessions expire automatically after 1 hour or on browser session end, whichever is first — never persists indefinitely.
- The impersonation banner is non-dismissible and always visible at the top of every tenant page while active — never silently impersonate without a visible indicator.
- A School Admin's own login is never affected or interrupted by an impersonation session — Super Admin impersonating doesn't log the real School Admin out if they're concurrently logged in elsewhere.

### Tenant Routes (`routes/tenant.php`)

Domain: `{school}.schoolflow.com` or a verified custom domain. Every route group below runs through `InitializeTenancyBySubdomain`.

```php
// Public, unauthenticated
Route::middleware(['web', InitializeTenancyBySubdomain::class])->group(function () {
    Route::get('/', PublicPageController::class)->name('public');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated, permission-gated
Route::middleware(['web', InitializeTenancyBySubdomain::class, 'auth'])->group(function () {

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('permission:students.view')->group(function () {
        Route::resource('students', StudentController::class);
    });

    Route::middleware('permission:staff.view')->group(function () {
        Route::resource('staff', StaffController::class);
    });

    Route::middleware('permission:attendance.view')->group(function () {
        Route::get('/attendance', AttendanceController::class)->name('attendance.index');
    });

    Route::middleware('permission:timetable.view')->group(function () {
        Route::get('/timetable', TimetableController::class)->name('timetable.index');
    });

    Route::middleware('permission:exams.view')->group(function () {
        Route::resource('exams', ExamController::class);
    });

    Route::middleware('permission:fees.view')->group(function () {
        Route::get('/fees', FeeController::class)->name('fees.index');
    });

    Route::get('/announcements', AnnouncementController::class)->name('announcements.index');

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('/reports', ReportController::class)->name('reports.index');
    });

    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings/roles', RolesPermissionsController::class)->name('settings.roles');
        Route::get('/settings/domain', CustomDomainController::class)->name('settings.domain');
    });

    // Available to every authenticated user, regardless of permissions —
    // this is the user's own account, not a module
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::patch('/account', [AccountController::class, 'update'])->name('account.update');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
});
```

### Dashboard — Single Route, Role-Aware Rendering

`/dashboard` is one route backed by `DashboardController` (or a `Dashboard` Livewire component). It does not branch on role name — it checks the logged-in user's **permissions** and renders only the widgets they're allowed to see. This keeps custom roles working automatically (see Permissions Seeding below) while still giving each of the five default roles a genuinely different dashboard.

| Role (typical permissions)         | What they see |
| ------------------------------------ | ---------------------------------------------------------------------------------------- |
| **School Admin** (all `*.view`)      | Setup checklist (until complete) · all 4 stat cards (Total Students, Total Staff, Attendance Rate Today, Fees Collected This Term) · full Recent Activity feed (all users' actions) · all 3 charts (Fee Collection, Attendance Rate, Grade Distribution) |
| **Teacher** (`attendance.view`, `exams.view`, `timetable.view`, no `fees.view`/`staff.view`) | Stat cards scoped to their own assigned classes only (their students' attendance rate today, their student count) · Recent Activity filtered to their own actions (marks entered, attendance marked) · Today's Timetable widget (their periods) · no fee/staff charts |
| **Accountant** (`fees.view`/`fees.create`/`fees.edit`, `reports.view`, no `students.edit`/`exams.view`) | Fee-specific stat cards (Fees Collected This Term, Outstanding/Overdue total) · Fee Collection chart · Recent Activity filtered to fee-related events (payments recorded) · no attendance/exam widgets |
| **Student** (no module `.view` beyond their own data) | Simplified card-based view, no charts: their own Attendance % (current term), their own Fee status (Paid/Unpaid/Overdue badge), latest exam results summary (if published), recent Announcements |
| **Parent** (same shape as Student, scoped via linked child) | Same as Student, but for their linked child — if multiple children, a child-selector switches the whole dashboard between them; each card shows the selected child's data |
| **Custom roles** | Whatever combination of the above widgets matches their assigned permissions — e.g. a custom "Exam Officer" role with only `exams.view`/`exams.edit` sees an exam-focused dashboard (recent exam activity, no fee/attendance widgets) automatically, with no code changes required |

Sidebar nav items are filtered the same way — a nav item only renders if the user has the corresponding `*.view` permission.

### Permissions Seeding

On tenant provisioning (`TenantProvisioningService`), the following permissions are seeded into the tenant database via `spatie/laravel-permission`, one set per module:

```
students.view   students.create   students.edit   students.delete
staff.view      staff.create      staff.edit      staff.delete
attendance.view attendance.edit
timetable.view  timetable.edit
exams.view      exams.create      exams.edit      exams.delete
fees.view        fees.create       fees.edit
announcements.view  announcements.create  announcements.edit  announcements.delete
reports.view
settings.manage
```

The fixed roles (`school_admin`, `teacher`, `accountant`, `student`, `parent`) are also seeded with sensible default permission sets (e.g. `school_admin` gets everything, `accountant` gets `fees.*` + `reports.view`, `parent`/`student` get only `*.view` on their own data). School Admin can then create custom roles by combining these same permissions — no new permissions are introduced by custom roles, only new combinations.

---

Rules that must never be violated:

- Central controllers never query tenant databases directly — only via `Tenant::run()` or after `InitializeTenancyBySubdomain` has resolved the tenant.
- Tenant controllers/Livewire components never assume a specific tenant — always operate on the currently resolved tenant connection.
- Every query touching `students`, `staff`, `attendances`, `exam_results`, `fee_payments` is implicitly scoped to the current tenant database — there is no `school_id` column needed because each school has its own database.
- All Paystack webhook handlers verify the transaction with Paystack's API before marking a `fee_payments` row as paid — never trust webhook payload alone.
- Every PDF (report card, receipt) is generated server-side via dompdf — never generated client-side.
- New tenant provisioning always happens inside `TenantProvisioningService` — never create a tenant database manually or out of band.
- A tenant's default `*.schoolflow.com` domain is never deleted, even if a custom domain is added — it always remains as a fallback.
- Custom domain requests are added as additional `domains` rows for the existing tenant — never as a new tenant. stancl/tenancy resolves the tenant the same way regardless of which domain was used.
- Custom roles created by School Admin can never be granted `super_admin`-level permissions or cross-tenant access — permission assignment UI only exposes tenant-scoped module permissions.
- The `super_admin` guard and tenant `web` guard are never mixed — a Super Admin session never grants access to a tenant's `/dashboard`, and a tenant user session never grants access to `/super-admin/*`. The one controlled exception is impersonation (see "Impersonation" in Route Structure) — even then, the Super Admin's central session and the impersonated tenant session are tracked separately and never merged into one identity; every impersonated action is attributed to the real tenant user and logged to `impersonation_logs`.
- Impersonation sessions always expire (1 hour or browser session end) and are always visibly indicated to anyone using the impersonated session — there is no silent/invisible impersonation mode.
- Route-level access control always uses `permission:{module}.view`-style middleware, never `role:teacher`-style — this is what makes custom roles work without route changes.
- No hardcoded hex values or raw Tailwind color classes in Blade/Livewire views — use CSS variables from `ui-tokens.md`.
- Every Service method that can fail wraps its logic in try/catch and logs failures — never let a queued job (e.g. PDF generation, email) crash silently.