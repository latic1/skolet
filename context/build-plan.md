# Build Plan

## Core Principle

Full page UI built with mock data first — verified visually before any logic is written. Then functionality is built and wired to the database step by step. Every feature must be visible and testable before moving to the next. No invisible backend phases.

---

## Phase 1 — Foundation

### 01 Central App — Landing, Pricing, Register School UI

Build the complete central app UI (no sidebar — top navbar only).

**UI:**

- Navbar — SchoolFlow logo, Pricing, Login link, Register School button
- Landing page — headline, subheadline, "Register Your School" CTA
- Dashboard/product preview image embedded below hero
- Features section — value props (multi-tenant, attendance, fees, exams, public page)
- FAQ section (structured for featured snippets)
- Pricing page — per-student annual rate explanation (e.g. "GHS X per student, per year — billed annually"), what's included (all MVP features, no tiers), simple cost example/calculator (e.g. "200 students = GHS X/year")
- Register School page — form: school name, desired subdomain, admin name, admin email, admin password
- Footer

**Logic:**

- "Register School" → /register-school
- Subdomain field shows live preview: `{input}.schoolflow.com`
- Form submission validated but not yet wired to provisioning (Phase 1.3)
- Unique `<title>`, meta description, and OpenGraph tags per page (landing, pricing)
- `SoftwareApplication` schema markup added to landing page
- `sitemap.xml` and `robots.txt` generated for the central domain

---

### 02 Multi-Tenancy Setup

Install and configure `stancl/tenancy` for subdomain-based tenant resolution.

**Logic:**

- Install `stancl/tenancy`, configure central domains (`schoolflow.com`, `www.schoolflow.com`)
- Configure wildcard subdomain routing for `*.schoolflow.com`
- Create `routes/tenant.php` — separate route file for tenant-context routes
- `InitializeTenancyBySubdomain` middleware applied to all tenant routes
- Local dev: configure `*.schoolflow.test` via `/etc/hosts` or Valet
- Verify: visiting `test.schoolflow.test` resolves a (manually created) test tenant and switches DB connection

---

### 03 Tenant Provisioning

Wire the Register School form to actually create a tenant.

**Logic:**

- `TenantProvisioningService::provision()`:
  - Creates `Tenant` record (central DB)
  - Creates `Domain` record (`{subdomain}.schoolflow.com`)
  - Creates a `subscription_plans` row (central DB) — `status = 'trial'`, `rate_per_student` defaulted to the standard rate (configurable, e.g. `config('schoolflow.default_rate_per_student')`), `student_count = 0`, `cycle_start = now()`, `cycle_end = now()->addYear()`
  - Creates tenant database
  - Runs tenant migrations (empty schema at this point — tables added in later phases)
  - Creates School Admin user in tenant DB with `school_admin` role
- `SchoolRegistrationController::store()` calls the service, handles subdomain collisions (return error if taken)
- On success — redirect to `{subdomain}.schoolflow.com/login` with success flash message
- Wrap entire provisioning in a transaction-like rollback: if any step fails, clean up partially created tenant/database and show a human-readable error

---

### 04 Auth & Roles

Authentication and role system for the tenant app.

**UI:**

- Login page (tenant) — email + password form, SchoolFlow branding replaced by tenant's logo if uploaded

**Logic:**

- Laravel Breeze installed for tenant-side authentication
- `spatie/laravel-permission` installed — roles: `school_admin`, `teacher`, `accountant`, `student`, `parent` (tenant DB), `super_admin` (central)
- Middleware: protected tenant routes require authentication + tenant context
- After login → redirect to `/dashboard`
- Role-based sidebar nav filtering implemented (full nav for school_admin, restricted for others)

---

### 05 Central + Tenant Database Schema

All central and tenant tables created before any feature writes data.

**Logic:**

- Central DB migrations: `tenants`, `domains`, `subscription_plans`
- Tenant DB migrations (in `database/migrations/tenant/`):
  - `users` (with role via spatie)
  - `academic_years`, `terms`
  - `school_classes`, `sections`, `subjects`
  - `students`, `staff`
  - `attendances`
  - `timetables`
  - `exams`, `exam_results`
  - `fee_structures`, `fee_payments`
  - `announcements`
  - `school_profile` (single row, created empty — populated in Feature 07b)
- All tenant migrations run automatically as part of `TenantProvisioningService`

---

## Phase 2 — School Setup & Core Records

### 06 Tenant Dashboard — Full UI

Build the complete dashboard UI with mock data. Sidebar + topbar layout.

**UI:**

- Sidebar — Dashboard, Students, Staff, Attendance, Timetable, Exams, Fees, Announcements, Reports, Settings (filtered by permission — see Logic)
- Topbar — school logo/name, notifications icon, account dropdown
- Setup checklist card (shown to School Admin until complete): Academic Year, Classes/Sections, Subjects, Branding
- Four stat cards: Total Students, Total Staff, Attendance Rate Today, Fees Collected This Term — mock numbers with trend indicators
- Recent Activity card — list of 5 activity entries with colored dots and timestamps (mock data)
- Fee Collection Over Time — line chart (mock data)
- Attendance Rate — bar chart (mock data, last 7 days)
- Grade Distribution — bar chart (mock data, grade bands A-F)

**Logic:**

- Implement `routes/web.php` (central) and `routes/tenant.php` per `architecture.md`'s "Route Structure" section — including the `super_admin` guard and Super Admin login/dashboard routes
- Seed the per-module permissions list (`students.view`, `students.create`, ... per `architecture.md`) into each tenant database during `TenantProvisioningService`, and assign default permission sets to the fixed roles (school_admin, teacher, accountant, student, parent)
- All authenticated tenant routes gated by `permission:{module}.view` middleware — never by hardcoded role name
- `/dashboard` is a single route; `DashboardController` (or Livewire component) checks the logged-in user's permissions and renders only the widgets that role/permission set should see (per the table in `architecture.md`):
  - School Admin (all `*.view` permissions) → setup checklist + all stat cards + recent activity + all 3 charts
  - Teacher (`attendance.view` + `exams.view`) → stats for own classes only, filtered recent activity
  - Accountant (`fees.view` only) → fee stat cards + fee collection chart only
  - Student/Parent (no module `.view` beyond own data) → simplified read-only view, no charts
- Sidebar nav items rendered conditionally based on the same permissions — a custom role with only `exams.*` sees only Exams (+ Dashboard)
- Mock data for all widgets at this stage; real data wired in Phase 5 (15-17) and Phase 7 (20)

---

### 07 Academic Year, Classes, Sections, Subjects

Setup pages for core academic structure — required before students/staff/timetable can be meaningfully created.

**UI:**

- Settings sub-pages: **Academic Calendar** (period system + academic years), Classes (e.g. Grade 1-6, with a sequence/order — used later for promotion, Phase 2), Sections (e.g. A, B per class — optional), Subjects (e.g. Math, English)
- **Academic Calendar page** (matches the design mockup):
  - **Academic Period System** section — two selectable cards: "3-Term System (Term 1 · Term 2 · Term 3)" and "2-Semester System (Semester 1 · Semester 2)". Selected card highlighted in `bg-accent` blue with white text. Saved to `school_profile.period_system`. Set once.
  - **Current Academic Year** section — pill/button row showing all created academic years (e.g. 2023/2024, 2024/2025, 2025/2026); active year highlighted in `bg-accent` blue. "+ Add Year" adds a new academic year — auto-generates the correct `terms` rows immediately based on `period_system`.
  - **Active Configuration** card — shows the current combination, e.g. "2024/2025 · 3-Term System (Term 1, Term 2, Term 3)". Admin sets which specific term is `is_current` here (e.g. "Term 1 — Active").
- Classes page — each class row has an optional "Add Sections" action; classes with no sections added simply have none
- Simple CRUD tables for Classes, Sections, Subjects — add/edit/delete rows

**Logic:**

- `school_profile.period_system` saved when the School Admin picks the Academic Period System card — `3_term` or `2_semester`. Changing this after `terms` data exists against it is blocked with a warning message.
- Adding a new academic year auto-generates `terms` rows immediately: `3_term` → 3 rows ("Term 1", "Term 2", "Term 3"); `2_semester` → 2 rows ("Semester 1", "Semester 2"). No manual term creation needed.
- `is_current` on `terms` — only one row is current at a time across all academic years. Setting one current unsets all others. Displayed as "Active" on the Academic Calendar page.
- `academic_years` and `terms` are the entities referenced everywhere "term" appears in later features (`exams.term_id`, `fee_structures.term_id`, dashboard "Fees Collected This Term," Fee Collection Report term filter) — Feature 07 must exist before Features 12, 14, 17, 20 can be built against a real `term_id`
- Classes have an `order` field (drag-to-reorder or numeric input) defining their sequence from lowest to highest — required for Phase 2 promotion, but otherwise unused in MVP
- `school_classes.name` and `school_classes.order` are each unique — enforced via database unique index plus Form Request validation (`unique` rule), with a human-readable error (e.g. "A class named 'Primary 4' already exists" / "Order 4 is already used by Primary 4 — choose a different order")
- `sections.name` is unique per `class_id` — two classes can each have a "Section A," but one class cannot have two
- Classes and Sections linked (a class has zero or many sections) — `sections` table is not required to have a row for every class
- A class with no sections is treated as a single implicit group — anywhere "class/section" is selected (students, attendance, timetable, marks entry, reports), the section selector is hidden or shows "N/A" if the chosen class has no sections defined
- Subjects optionally linked to classes (which classes take which subjects)
- Setup checklist on dashboard updates as each is completed (Sections step can be skipped — checklist treats "Classes" as complete without requiring sections; "Academic Year" requires at least one term to exist)

---

### 07b School Profile & Branding

This satisfies the "Branding" item in Feature 06's setup checklist, and creates `school_profile` — the single row consumed by the topbar, login page, generated PDFs (Features 13, 17), and the public page (Feature 19).

**UI:**

- Settings > School Profile page (School Admin, `permission:settings.manage`) — form: school name, short description, address, phone, email, logo upload with preview
- Logo upload accepts PNG/JPG, shows a live preview, with "Remove" to revert to the default SchoolFlow logo
- Topbar (Feature 06) and tenant login page (Feature 04) updated to render `school_profile.logo_path` if set, falling back to the default gradient logo from `ui-tokens.md` if not

**Logic:**

- `school_profile` table — single row per tenant, created empty during provisioning (Feature 03), filled in here
- Logo stored at `storage/{tenant}/logos/`, path saved to `logo_path`
- Setup checklist (Feature 06) marks "Branding" complete once `school_name` and `logo_path` are both set
- Apply responsive rules from `ui-rules.md` — stacked form on mobile

---

### 08 Student Management — Full UI + CRUD + Bulk Import

**UI:**

- Students list page — table: Admission No, Full Name, Class, Section, Guardian Contact, Status — Section column shows "—" for classes with no sections
- Filter by class/section, search by name — section filter hidden if no classes have sections defined
- Add Student form — personal info, guardian info, class assignment; Section field only appears once a class with defined sections is selected
- Student profile page — view/edit details, view attendance history, exam results, fee status (read-only summaries pulling from later phases)
- Bulk Import — "Download Template" button + upload input (per CSV Import Rules in code-standards.md). Template: `schoolflow-students-import-template.xlsx`

**Student import template columns:**

| Column | Required | Notes |
|---|---|---|
| Full Name | Yes | |
| Date of Birth | No | |
| Gender | No | Male / Female |
| Class Name | Yes | Must match an existing class name exactly (e.g. "Primary 4") |
| Section Name | No | Must match an existing section for that class; leave blank if no sections |
| Guardian Name | Yes | |
| Guardian Contact | Yes | Phone number |
| Guardian Email | No | |
| Medical Notes | No | |

Admission number is auto-generated by the system — never in the template.

**Logic:**

- Admission number auto-generated on creation (e.g. `2026/0001`) — never from the import file
- Student CRUD wired to `students` table
- Bulk import via `maatwebsite/excel` — validate all rows first (class exists, required fields present, no duplicate guardian contact per student), abort with row-level error report if any fail, import all on success
- Optional: create linked `users` account for student/parent login (role: student or parent) at creation time or later

---

### 09 Staff Management — Full UI + CRUD

**UI:**

- Staff list page — table: Name, Role Title, Email, Assigned Classes/Subjects
- Add Staff form — personal info, role title, linked user account (role: teacher, accountant, or custom role)
- Staff profile page — view/edit details, assigned classes/subjects
- Bulk Import — "Download Template" button + upload input (per CSV Import Rules in code-standards.md). Template: `schoolflow-staff-import-template.xlsx`

**Staff import template columns:**

| Column | Required | Notes |
|---|---|---|
| Full Name | Yes | |
| Email | Yes | Used as login email — must be unique |
| Phone | No | |
| Role | Yes | Must match an existing role name exactly: "teacher", "accountant", or a custom role name |
| Role Title | No | Display label e.g. "Class Teacher", "Head of Science" |

Assigned classes/subjects are set manually after import via the staff profile — not included in the template (too complex for bulk entry).

**Logic:**

- Staff CRUD wired to `staff` table, linked to `users` table for login
- Bulk import via `maatwebsite/excel` — validate all rows first (email unique, role exists, required fields present), abort with row-level error report if any fail, import all on success; auto-creates a `users` account per staff row with a temporary password (emailed to them, or shown on the success screen for the admin to share)
- Assigning a teacher to a class/subject feeds into Timetable (Phase 3) and Marks Entry (Phase 4)

---

### 09b Custom Roles & Permissions

**UI:**

- Roles & Permissions page (School Admin only) — list of roles (fixed + custom)
- "Create Role" form — role name + permission checkboxes grouped by module (Students, Staff, Attendance, Timetable, Exams, Fees, Announcements, Reports), each with View/Create/Edit/Delete
- Edit/delete custom roles (fixed roles cannot be deleted)
- Assign role to staff from the staff form (Phase 2.09)

**Logic:**

- Uses `spatie/laravel-permission`'s `roles` and `permissions` tables (tenant DB)
- Permissions seeded per module on tenant provisioning (e.g. `students.view`, `students.create`, ...)
- Custom role creation simply creates a new `role` row and attaches selected permissions
- Sidebar nav and controller authorization both check permissions, not hardcoded role names — so custom roles automatically get correct access

---

### 09c Account Settings — "My Account"

Available to every authenticated user, regardless of role or permissions — this is their own account, not a module.

**UI:**

- "My Account" link in the topbar account dropdown (Feature 06), available to all roles
- Account page — two sections in cards:
  - **Profile**: name, email, phone, avatar upload with preview (same upload/preview pattern as Feature 07b's logo)
  - **Password**: current password, new password, confirm new password
- Both sections save independently (separate forms/buttons)

**Logic:**

- `AccountController` (or `Account` Livewire component) — `update()` saves name/email/phone/avatar to the logged-in `users` row; `updatePassword()` validates current password before updating
- Avatar stored at `storage/{tenant}/avatars/{user_id}/`, path saved to `users.avatar_path`
- Apply the "Form Submission Rules" from `code-standards.md` — disable submit while saving, avatar preview updates immediately on upload and persists after save
- Topbar account dropdown (Feature 06) shows `avatar_path` if set, falling back to initials/default icon
- No permission middleware on these routes beyond `auth` — every role can edit their own account

---

## Phase 3 — Attendance & Timetable

### 10 Daily Attendance — Full UI + Save Logic

**UI:**

- Attendance page — select class, then section if that class has sections defined, and date
- List of enrolled students, each with Present / Absent / Late quick-action buttons
- "Mark all present" bulk action
- Save button — confirms attendance saved for that class/date
- Monthly attendance report view per student (table of dates + status)

**Logic:**

- Teacher sees only their assigned classes; School Admin sees all
- Saving writes one `attendances` row per student for the selected date
- Re-opening an already-marked date pre-fills existing statuses (editable)
- Staff attendance — same pattern, separate page, School Admin marks staff attendance

---

### 11 Timetable / Routine Builder

**UI:**

- Timetable page — grid view: days of week (rows) × periods (columns), per class (and section, if that class has sections defined)
- Each cell shows subject + teacher, editable via dropdown selection
- Teacher's personal timetable view — their periods across all classes

**Logic:**

- Timetable CRUD wired to `timetables` table
- Basic conflict detection: warn if the same teacher is assigned two classes in the same period
- No substitute-teacher workflow in MVP — flagged for Phase 2

---

## Phase 4 — Exams & Report Cards

### 12 Exam Scheduling + Marks Entry — Full UI

**UI:**

- Exams list page — Add Exam (name, Term — dropdown from `terms`, date range)
- Marks Entry page — select exam, class (and section if defined), subject; table of students with marks input field
- Save button per subject/class combination

**Logic:**

- `exams` table stores exam metadata, including `term_id` (references `terms`, created in Feature 07)
- `exam_results` table stores one row per student/subject/exam with raw `marks`
- Teachers can only enter marks for subjects/classes they're assigned to

---

### 13 Grading Scale + Report Card Generation (PDF)

**UI:**

- Report Card preview page — header shows school logo (if set in `school_profile`) and school name, then per student/exam: subject list with marks, computed grade, overall average/GPA
- Download/Print button (PDF)

**Logic:**

- `ReportCardService` reads `exam_results`, applies `config('schoolflow.default_grading_scale')` to compute each subject's grade and overall grade
- `ReportCardService` also reads `school_profile` (Feature 07b) for `school_name` and `logo_path` to render the PDF header — text-only header if `logo_path` is null
- `barryvdh/laravel-dompdf` renders the report card view to PDF
- PDF saved to `storage/{tenant}/report-cards/{student_id}/{exam_id}.pdf` and made downloadable
- Result publishing: marks are visible to students/parents only after School Admin marks the exam as "published"

---

## Phase 5 — Fees & Payments

### 14 Fee Structure Setup — Full UI

**UI:**

- Fee Structure page — list of configured fees (fee name, amount, target class, term or "Annual", mandatory badge, due date) with "Configure New Fee" button
- **"Configure New Fee" modal:**
  - **Fee Name** — text input (e.g. "School Fees", "Feeding (Lunch)", "Hostel", "Bus", "PTA Dues", "Extra Classes")
  - **Amount (GHS)** — decimal input (admin enters the total flat amount — e.g. for feeding fees, they compute GHS 3.50 × 59 days = GHS 206.50 and enter 206.50)
  - **Target Classes** — dropdown: "All Classes" or a specific class from `school_classes`
  - **Billing Cycle** — two options: "Per Term" (default) or "Annual". Selecting "Annual" hides the Academic Term field (annual fees belong to the whole academic year, not a specific term)
  - **Academic Term** — dropdown: auto-populated from `terms` for the current academic year (hidden if "Annual" selected)
  - **Academic Year** — read-only, auto-filled from the current academic year
  - **Mandatory Fee toggle** — on by default. MVP: informational only. Phase 2: per-student assignment for non-mandatory fees (Hostel, Bus, Feeding, Extra Classes)
  - **Due Date** — date picker (optional)
  - Cancel + "Save Fee Configuration" button (disabled while saving per code-standards.md)

**Logic:**

- `fee_structures` table — `billing_cycle = 'term'` (term_id set) or `billing_cycle = 'annual'` (term_id null, academic_year_id set). `target_class = 'all'` or a specific `class_id`.
- Annual fees appear on every term's bill as a line item, but `FeeStatusService` only marks them outstanding if unpaid across the entire academic year — not re-charged every term
- When computing which fees apply to a student (Feature 15): per `architecture.md` — `target_class` match + current term (for `billing_cycle = 'term'`) or current academic year (for `billing_cycle = 'annual'`)

---

### 15 Fee Collection (Cash) — Full UI + Save Logic

**UI:**

- Fees page (Accountant/Admin view) — student search, shows outstanding fee items and amounts
- "Record Cash Payment" action — enter amount paid, generates receipt
- Fees page (Parent/Student view) — read-only list of fee items, amounts, status badges (Paid/Unpaid/Partial/Overdue)

**Logic:**

- `fee_payments` table — one row per payment (full or partial), linked to `fee_structure_id`
- `FeeStatusService` computes status per `fee_structure_id` for a student: `unpaid` (no payments), `partial` (sum of payments < amount), `paid` (sum >= amount), `overdue` (sum < amount AND `fee_structures.due_date` has passed)
- Cash payments recorded directly by Accountant/Admin — `payment_method = 'cash'`, `paystack_ref` null, `recorded_by` set to the logged-in user

---

### 16 Paystack Online Payment Integration

**UI:**

- "Pay Now" button on Parent/Student fee view for unpaid/partial items
- Redirects to Paystack checkout

**Logic:**

- `PaystackService::initializeTransaction()` — takes `student_id`, `fee_structure_id`, and the outstanding amount (from `FeeStatusService`), creates a Paystack transaction, returns checkout URL
- Webhook endpoint (`/paystack/webhook`, tenant-aware route) receives payment confirmation
- `PaystackService::verifyTransaction()` — always verifies with Paystack's API before recording payment, never trusts webhook payload alone
- On verified success — `fee_payments` row created with `payment_method = 'paystack'`, `paystack_ref` set; `FeeStatusService` recomputes status for that `fee_structure_id`
- PostHog-equivalent: no analytics events in MVP — logged to `agent_logs`-style table is out of scope; rely on standard Laravel logging

---

### 17 Receipts (PDF) + Due/Overdue Tracking

**Two types of fee PDFs:**

1. **Payment Receipt** — generated per `fee_payments` row after a payment (cash or Paystack). Shows school logo/name, student name, fee item paid, amount, date, payment method, and receipt number.

2. **Term Bill** — generated per student per term. Shows the full list of applicable fee items for that student (both `billing_cycle = 'term'` for the current term and `billing_cycle = 'annual'` items), each with status (Paid/Unpaid/Overdue), plus an "Arrears" line (total outstanding from previous terms). **Prints two copies per A4 page** — standard Ghanaian school practice (one copy for the parent to keep, one to return signed).

**UI:**

- Receipt download link after any payment (cash or Paystack)
- "Print Bill" button per student on the Fees page (Accountant/Admin view) — generates the Term Bill PDF for that student
- Overdue fees highlighted on dashboard stat card and fee list (red badge)

**Logic:**

- Receipt PDF rendered via dompdf from a `fee_payments` record, saved to `storage/{tenant}/receipts/`
- Term Bill PDF rendered via dompdf — reads `FeeStatusService` output for the student (all applicable fees + computed status), plus an Arrears total (sum of all unpaid/partial `fee_payments` balances from previous terms). Header includes `school_profile.school_name` and `logo_path`.
- Two-per-page layout: the bill HTML template renders two identical copies stacked vertically, with a dashed cut line between them — dompdf renders to one A4 page
- Receipt header includes `school_profile.school_name` and `logo_path` — text-only if no logo
- Overdue status from `FeeStatusService` — `fee_structures.due_date` passed and sum of `fee_payments` < `fee_structures.amount`
- Dashboard "Fees Collected This Term" stat — sum of `fee_payments.amount` for `fee_structures` where `term_id` is the current term (`is_current = true`)

---

## Phase 6 — Communication & Public Page

### 18 Announcements / Notice Board

**UI:**

- Announcements page — list of posted announcements (title, body, date, posted by)
- Add Announcement form (School Admin / Teacher)
- Dashboard "Recent Activity" includes latest announcements

**Logic:**

- `announcements` table — CRUD
- Visible to all roles within the tenant (read access for everyone, write access for school_admin/teacher)

---

### 19 Auto-Generated School Public Page

**UI:**

- `{school}.schoolflow.com/` (logged-out view) — hero with school logo/name/short description, recent announcements list, contact info (address, phone, email)
- "Login" button in top navbar

**Logic:**

- Public page reads directly from the `school_profile` table (created in Feature 07b — school name, logo, description, address, phone, email) and the `announcements` table
- No separate CMS — content is whatever the School Admin has already entered via settings and announcements
- Route is unauthenticated but still tenant-scoped (resolved via subdomain)
- Page `<title>` and meta description generated per-tenant from `school_profile` data (school name + location) — never a shared/generic title
- Page is indexable — not excluded in `robots.txt`
- OpenGraph tags use the school's logo and description for social sharing

---

## Phase 7 — Reports & Super Admin

### 20 Attendance & Fee Reports

**UI:**

- Reports page — Attendance Report (by class, optionally section, and date range, % present per student) and Fee Collection Report (filterable by Term — dropdown from `terms` — total collected vs outstanding)
- Export to PDF/Excel button on each report

**Logic:**

- `AttendanceReportService` aggregates `attendances` records into per-student/per-class summaries
- Fee report aggregates `fee_payments` and `fee_structures` into collected vs outstanding totals
- Export via dompdf (PDF) and `maatwebsite/excel` (Excel)

---

### 21 Super Admin Dashboard — Manage Tenants & Subscriptions

**UI:**

- Super Admin page (central app, `schoolflow.com/super-admin`) — table of all schools: name, subdomain, student count, rate per student, amount due, payment status (Paid/Unpaid badge), cycle end date, status (trial/active/expired), created date
- Per-school detail/edit — Super Admin can set/edit `rate_per_student` for that school (defaults to a standard rate for new tenants), and toggle `payment_status` (Unpaid → Paid) after confirming offline payment, which also updates `cycle_start`/`cycle_end` for the next annual cycle
- Enable/Disable toggle per school (independent of payment status — Super Admin can manually suspend a tenant for any reason)
- **"Impersonate" button** per school (on the detail/list row) — logs the Super Admin in as that school's School Admin for support purposes
- Basic global stats: total schools, active subscriptions, total amount due across all unpaid schools, schools with `status = expired`
- "Last synced" timestamp shown per school (from `student_count_synced_at`)
- **Impersonation banner** — when an impersonation session is active, every tenant page (any role's view, though typically School Admin) shows a non-dismissible top banner: "You are viewing as {School Name} (Super Admin support session) — [Exit]"

**Logic:**

- Queries central DB only — `tenants`, `domains`, `subscription_plans`
- `SyncTenantStudentCounts` scheduled command (daily, via Laravel Scheduler) — for each tenant, runs `Tenant::run()` to count `students`, updates `subscription_plans.student_count`, `student_count_synced_at`, and recomputes `amount_due = rate_per_student * student_count`
- A tenant's `status` becomes `expired` automatically when `cycle_end` passes and `payment_status` is still `unpaid` — checked by a scheduled job or on Super Admin dashboard load
- Disabling a tenant (manual toggle) OR a tenant with `status = expired` both block login at that subdomain and any custom domain (middleware checks tenant status before allowing auth)
- **Impersonation** — per the full flow in `architecture.md`'s "Impersonation" section: `ImpersonationController::start()` finds/uses the tenant's School Admin account, stores impersonation state in session, logs an `impersonation_logs` row, and redirects to the tenant subdomain. `ResumeImpersonation` middleware (tenant side) resumes the session on each request. "Exit" calls `ImpersonationController::stop()`, clears session state, sets `ended_at` on the log row, redirects back to `/super-admin`. Sessions auto-expire after 1 hour.
- Self-service Paystack billing for this subscription (school pays SchoolFlow in-app) is Phase 2 — see Roadmap

---

### 22 Custom Domain Support

**UI:**

- Settings page (School Admin) — "Custom Domain" section: input for their domain (e.g. `admin.exampleschool.com`), instructions to add a CNAME record pointing to `schoolflow.com`
- Status indicator: Pending / Verified / Active
- "Verify" button — checks DNS resolution

**Logic:**

- On save, a new `domains` row is added for the tenant (in addition to their existing `*.schoolflow.com` domain)
- Verification checks that the custom domain's CNAME resolves to SchoolFlow's server
- Once verified, Caddy auto-issues an SSL certificate for the custom domain on first request (no manual certbot steps)
- Tenant resolution works identically whether the request arrives via `{subdomain}.schoolflow.com` or the custom domain — both map to the same tenant

---

## Feature Count

| Phase                                      | Features |
| ------------------------------------------ | -------- |
| Phase 1 — Foundation                       | 5        |
| Phase 2 — School Setup & Core Records      | 7        |
| Phase 3 — Attendance & Timetable           | 2        |
| Phase 4 — Exams & Report Cards             | 2        |
| Phase 5 — Fees & Payments                  | 4        |
| Phase 6 — Communication & Public Page      | 2        |
| Phase 7 — Reports & Super Admin            | 3        |
| Phase 8 — Platform Foundation (MVP Gaps)   | 9        |
| Phase 9 — Growth Features                  | 15       |
| Phase 10 — Competitive Advantages          | 8        |
| **Total**                                  | **57**   |

---

## Roadmap — Phase 2+ (Post-Launch)

Not built as part of MVP. Listed here so schema decisions made during MVP (e.g. the `order` column on `school_classes`) account for these.

### Student Promotion

**UI:**

- "Promote Students" page (School Admin) — select an outgoing class+section, see the student list
- Per student: Promote / Retain / Graduate (Graduate only available for the highest-`order` class)
- "Promote all" bulk action with per-student overrides
- Confirmation step — shows summary (e.g. "42 promoted to Grade 6A, 3 retained in Grade 5A, 0 graduated") before committing

**Logic:**

- Creates a new `academic_years` row (or activates an existing one) as the new "current" year
- For each student: writes a `student_class_history` row for the outgoing year (preserving their actual class/section so old report cards and attendance remain correctly attributed), then updates `students.class_id`/`section_id` based on the chosen outcome — next class in `order` (promoted), unchanged (retained), or marks student inactive (graduated)
- Report cards and attendance reports for past years are read via `student_class_history`, not the student's current `class_id`/`section_id`

---

### Per-Subject Max Marks

MVP assumes every subject is scored out of 100 (see `architecture.md` exam_results note). If needed later: add `max_marks` to `exams` or `exam_results`, store raw `marks`, and compute `percentage = marks / max_marks * 100` before applying the grading scale. Affects Marks Entry (Feature 12) validation and Report Card Generation (Feature 13).

---

### Optional Fee Items

The "Mandatory Fee" toggle already exists in the Fee Structure modal (Feature 14 UI) and `fee_structures.is_mandatory` is already stored. In MVP all fees behave as mandatory regardless of this toggle — but the toggle is shown in the UI ready for Phase 2.

**Primary use cases:** Hostel (boarding students only), Bus (transport users only), Feeding/Breakfast (optional at some class levels), Extra Classes (JHS students who sign up).

**Phase 2 — wire up the non-mandatory behavior:**

- Add a `student_fee_assignments` table (`student_id`, `fee_structure_id`, `assigned_at`) — only used for `is_mandatory = false` rows.
- Fee Collection (Feature 15) gains a section per student showing optional fee items for their class/term — Accountant/Admin assigns/unassigns (e.g. "This student uses the bus"). Unassigned optional items don't appear on the student's bill or Fees page.
- `FeeStatusService` computes applicable fees as: all `is_mandatory = true` rows matching target class + all `is_mandatory = false` rows with a `student_fee_assignments` entry for that student.
- Term Bill PDF (Feature 17) shows only assigned optional fees per student.

---

### Self-Service SaaS Subscription Billing

MVP tracks SchoolFlow's per-student annual subscription payment manually (Super Admin marks `payment_status` paid/unpaid after offline payment, per Feature 21).

**If needed later:**

- School Admin sees their `amount_due`, `cycle_end`, and `payment_status` on a Settings > Billing page (tenant app)
- "Pay Now" button — same `PaystackService::initializeTransaction()`/`verifyTransaction()` pattern as Feature 16, but the transaction is between the school and SchoolFlow (central), not between a parent and the school
- On verified payment: central `subscription_plans.payment_status = 'paid'`, `cycle_start`/`cycle_end` advance by one year
- Automated email reminders as `cycle_end` approaches (Laravel Mail + scheduled job)
- Requires a webhook endpoint on the **central** app (`schoolflow.com/paystack/webhook`) distinct from each tenant's fee-payment webhook (Feature 16, tenant-scoped)

---

## Phase 8 — Platform Foundation (MVP Gaps)

These are the critical gaps that block production use and business growth. Build all of Phase 8 before starting Phase 9.

### 23 Queue Worker + Horizon Setup

**Logic:**

- Install `laravel/horizon`; configure `config/horizon.php` with a `default` queue worker supervisor
- Switch all `Mail::send()` / `Mail::to()->send()` calls to queued Mailables — wrap in dedicated Job classes under `app/Jobs/`
- Move PDF generation (report cards, receipts, term bills) to background jobs — controller dispatches job, job streams file to storage, emails admin a download link
- Configure `QUEUE_CONNECTION=redis` for production; keep `sync` for local dev (`.env.example` updated)
- Expose Horizon dashboard at `/horizon` on the **central** app, gated by `auth:super_admin` middleware
- Add `supervisor.conf` example to project root for production queue worker
- Add `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`, `HORIZON_PREFIX` to `.env.example`

---

### 24 Grading Scale Configuration Per School

**UI:**

- Settings > Academic Calendar page: add a **Grading Scale** card below the Period System section
- Repeatable rows table: Min Score | Max Score | Grade Label | Remark — with Add Row / Remove Row controls (Alpine dynamic rows)
- Live preview: sample bar showing grade bands colour-coded per `ui-tokens.md` grade colours
- Validation error shown inline per row (gaps or overlaps between bands)
- Save button — same submit-disable pattern per `code-standards.md`

**Logic:**

- New migration: add `grading_scale` (JSON, nullable) to `school_profile`
- `SchoolProfile` model: add `'grading_scale' => 'array'` cast
- New route: `POST /settings/grading-scale` → `SchoolProfileController::updateGradingScale` (inside `permission:settings.manage`)
- `StoreGradingScaleRequest`: validate array of bands, each row requires `min` (int 0–100), `max` (int 0–100), `grade` (string max 5), `remark` (string max 50). Full 0–100 coverage required; no overlapping ranges; min < max per row.
- `ReportCardService::build()`: load `SchoolProfile::first()->grading_scale ?? config('schoolflow.default_grading_scale')` — falls back to config for tenants that haven't set a custom scale
- `ExamResult::computeGrade(float $marks, array $scale)`: make `$scale` a parameter, not hardcoded; update all callers
- Settings sub-nav: "Academic Calendar" tab is already the 1st tab — grading scale added as a new section within that page (no new tab needed)

---

### 25 Student Academic Promotion Engine

**UI:**

- New page: `/students/promote` — accessible from Students list via "End of Year Promotion" button (school_admin only)
- Step 1 — Select: choose source class + section → loads student list with counts
- Step 2 — Assign Outcomes: per-student dropdown: **Promote** (to next class in order) / **Retain** (same class) / **Graduate** (only for highest-`order` class). "Promote All" bulk button pre-fills everyone as Promote.
- Step 3 — Confirm: summary card showing "X promoted, Y retained, Z graduated" with student-level diff table. Cancel or Execute buttons.
- Step 4 — Done: success banner + "View Students" link. Graduated students shown with `status=graduated` badge.
- Breadcrumb: Students → Promote Students

**Logic:**

- New migration: `create_student_class_history_table` (id uuid PK, student_id FK→students, academic_year_id FK→academic_years, class_id FK→school_classes, section_id FK→sections nullable, outcome enum['promoted','retained','graduated'], timestamps)
- `StudentClassHistory` model — `HasUuids`, fillable all columns, BelongsTo relationships
- `StudentPromotionService::promote(array $outcomes, AcademicYear $fromYear): array`:
  - Wraps entirely in `DB::transaction`
  - For each `[student_id => outcome]` entry: insert `student_class_history` row for `$fromYear`
  - `promoted` → update `students.class_id` to next class (next by `order`), set `section_id` to section with same name in next class or null. Guard: if no next class exists, return error — admin must pick destination manually.
  - `retained` → no class change; history row records outcome=retained
  - `graduated` → set `students.status = 'graduated'`; history row outcome=graduated
  - Returns `['promoted' => N, 'retained' => N, 'graduated' => N, 'errors' => []]`
- `SchoolClassController::nextClass(SchoolClass)` private helper: `SchoolClass::where('order', $class->order + 1)->first()`
- Routes: `GET /students/promote` (show wizard), `POST /students/promote` (execute) under `permission:students.edit`
- Historical report card and attendance queries: no schema change needed — they use `student_id` not `class_id`. The `student_class_history` table serves future "which class was this student in at time T" analytics queries only.
- Guard: block promotion attempt if current academic year has no `is_current = true` term set

---

### 26 Tenant Onboarding Wizard

**UI:**

- After new tenant provisions and admin logs in for first time, redirect to `/onboarding` instead of `/dashboard`
- Full-screen wizard (no sidebar — same guest layout pattern as login page) with 5 steps shown in a progress stepper at top
- Step 1 — School Profile: school name (required), logo upload, short description
- Step 2 — Academic Calendar: period system selector (same two-card pattern as Settings > Academic Calendar), first academic year name + start/end dates
- Step 3 — Classes: add at least one class (name + order); sections optional ("Add sections later" skip link)
- Step 4 — Subjects: add at least 2 subjects; "Skip for now" link
- Step 5 — Done: animated checkmark, summary of what was set up, "Go to Dashboard →" button
- "Skip wizard" link on every step (bottom-right) marks onboarding as skipped and redirects to dashboard
- If onboarding not completed: show a persistent setup banner at top of dashboard (dismissible per session, re-appears on next login)

**Logic:**

- New migration: add `onboarding_completed` (boolean, default false) and `onboarding_step` (tinyint, default 1) to `school_profile`
- `OnboardingMiddleware`: registered on `/dashboard` route — if `school_profile.onboarding_completed = false` AND `Auth::user()->can('settings.manage')`, redirect to `/onboarding`. Skip if onboarding was explicitly skipped (session key `onboarding_skipped`).
- `OnboardingController`: `show(int $step)`, `store(int $step, Request $request)` — saves step data using existing form requests (reuses `UpdateSchoolProfileRequest`, `StoreAcademicYearRequest`, `StoreSchoolClassRequest`, `StoreSubjectRequest`), advances `onboarding_step`, redirects to next step. Step 5: sets `onboarding_completed = true`.
- `skip()`: sets session `onboarding_skipped = true`, redirects to dashboard
- Routes: `GET /onboarding`, `GET /onboarding/{step}`, `POST /onboarding/{step}` — inside auth, no extra permission gate
- Dashboard banner: `@if(!$schoolProfile?->onboarding_completed && Auth::user()->can('settings.manage'))` — shows only to school_admin

---

### 27 Email Notification System

**UI:**

- Settings > Notifications page (`/settings/notifications`) — school_admin only, `settings.manage` permission
- Toggle list — one row per event type with email on/off switch:
  - Absent Alert (fires when student marked absent)
  - Fee Overdue Reminder (fires weekly for overdue fees)
  - Exam Results Published (fires when admin publishes an exam)
  - Payment Confirmation (fires after any successful fee payment)
  - Welcome Email (fires when staff account created — already exists, just make it configurable)
- "Send Test Email" button per row — sends a sample email to the logged-in admin's email

**Logic:**

- New migration: add `notification_settings` (JSON, nullable, default null) to `school_profile`. Schema: `{"absent_alert":{"email":true,"sms":false}, ...}`
- `app/Notifications/` directory with queued Notification classes (all extend `Illuminate\Notifications\Notification`):
  - `AbsenceAlert(Student, string $date)` — via mail, sent to `$student->guardian_email`
  - `FeeOverdueReminder(Student, FeeStructure, float $outstanding)` — via mail, sent to `$student->guardian_email`
  - `ExamResultsPublished(Exam, Student)` — via mail, sent to student's linked `User->email` if exists
  - `PaymentConfirmation(FeePayment)` — via mail, sent to `feePayment->student->guardian_email`
- Dispatch points:
  - `AttendanceController::save()`: after `updateOrCreate`, dispatch `AbsenceAlert` for each absent student (check notification_settings before dispatching)
  - `FeeStatusService::recordCashPayment()` and `PaystackWebhookController`: dispatch `PaymentConfirmation`
  - `ExamController::publish(Exam)`: dispatch `ExamResultsPublished` for all students with results in the exam
- Scheduled command `SendFeeOverdueReminders` — runs weekly (Kernel.php); queries all tenants, for each queries overdue fees and dispatches `FeeOverdueReminder` per student
- All dispatches check notification_settings before sending — if null (not configured), default to email=true for all events
- SMS: stub adapter with `sms_enabled` flag; if `SMS_PROVIDER` not set in `.env`, SMS silently skipped. No SMS provider integration in Phase 8 — wire the flag, integrate provider in Phase 10.

---

### 28 Rate Limiting & Security Hardening

**Logic:**

- Tenant login rate limit: add `RateLimiter::for('tenant-login', fn($request) => Limit::perMinute(5)->by($request->ip() . $request->email))` in `AppServiceProvider::boot()`; apply `throttle:tenant-login` middleware to `POST /login` tenant route
- Lock-out response: returns back with error `"Too many login attempts. Please try again in :seconds seconds."` (Laravel default throttle behaviour — no custom code needed, just apply the middleware)
- Central registration rate limit: `throttle:3,60` on `POST /register-school`
- Security headers middleware (`app/Http/Middleware/SecurityHeaders.php`): adds `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy: camera=(), microphone=()` to every response. Registered in `Kernel.php` global middleware stack.
- Honeypot on tenant login form: add `<input type="text" name="hp_check" class="hidden" autocomplete="off" tabindex="-1">` to login blade; `AuthenticatedSessionController::store()` rejects any submission where `$request->hp_check` is not empty (return back with generic error — do not reveal honeypot)
- Password strength rule: update all password inputs to use `Password::min(8)->mixedCase()->numbers()` rule — apply in `UpdatePasswordRequest`, `StoreStaffRequest`, `SchoolRegistrationRequest`

---

### 29 Audit Log

**Logic:**

- Install `spatie/laravel-activitylog` (add to approved dependencies in `code-standards.md`)
- Add the spatie activitylog migration to the tenant migration pipeline (`TenantProvisioningService`)
- Add `LogsActivity` trait to tenant models: `Student`, `Staff`, `Exam`, `ExamResult`, `FeeStructure`, `FeePayment`, `Announcement`, `SchoolProfile`, `AcademicYear`, `Term`, `SchoolClass`
- Configure per model: `logOnlyDirty()`, `dontLogIfAttributesChangedOnly(['updated_at'])`, `getActivitylogOptions()` returns model-specific log name
- New route: `GET /settings/audit-log` (school_admin only, `settings.manage`) — `AuditLogController::index()`
- View (`settings/audit-log.blade.php`): settings sub-nav adds 6th tab "Audit Log". Table: Date | User | Action (created/updated/deleted) | Record Type | Summary (first 2 changed attributes). Filterable by date range + user + model type. Paginated (25 per page).
- Pruning: `$schedule->command('activitylog:clean')->monthly()` in `Kernel.php` (removes logs older than 90 days)
- Super admin impersonation already logged separately in `impersonation_logs` — no change needed

---

### 30 Error Tracking & Health Checks

**Logic:**

- Install `sentry/sentry-laravel` (add to approved dependencies); add `SENTRY_LARAVEL_DSN` to `.env.example`
- `app/Http/Middleware/SetSentryContext.php`: if `tenancy()->initialized`, calls `Sentry::configureScope(fn($scope) => $scope->setTag('tenant_id', tenant('id'))->setUser(['id' => auth()->id(), 'email' => auth()->user()?->email]))`. Register in the top-level tenant middleware group in `routes/tenant.php`.
- Health check (`routes/web.php`, central, no auth): `GET /health` → `HealthController::check()` — checks: `DB::connection('central')->getPdo()`, `Cache::put('__health', 1, 5) && Cache::get('__health') === 1`, `Storage::disk('local')->put('__health', '1') && Storage::disk('local')->delete('__health')`. Returns JSON `{'status':'ok'|'degraded'|'fail', 'checks': {'db':..., 'cache':..., 'storage':...}}` with 200 or 503 status.
- Ping route: `GET /ping` returns `200 pong` (plain text, no DB hit, no auth — for uptime monitors)
- Both `/health` and `/ping` excluded from CSRF middleware and tenant resolution middleware

---

### 31 Automated Testing Suite (PestPHP)

**Logic:**

- Use PestPHP (ships with Laravel 11 default `composer require pestphp/pest --dev`)
- Tenant test helper: configure `stancl/tenancy`'s `TestsMultitenancy` trait in `tests/TestCase.php`; each tenant feature test creates a fresh tenant and runs inside `$tenant->run()` with `RefreshDatabase`
- Test structure:
  - `tests/Feature/Central/SchoolRegistrationTest.php` — registers school, verifies tenant DB created, admin user exists, subscription plan row created
  - `tests/Feature/Tenant/StudentTest.php` — CRUD, admission number generation, bulk import success/failure
  - `tests/Feature/Tenant/AttendanceTest.php` — mark attendance, re-mark idempotency, report generation
  - `tests/Feature/Tenant/ExamTest.php` — create exam, enter marks, grade computation, publish gating (student can't see unpublished)
  - `tests/Feature/Tenant/FeeTest.php` — fee structure creation, cash payment, FeeStatusService status computation (paid/partial/unpaid/overdue)
  - `tests/Feature/Tenant/PermissionTest.php` — each role can only access its permitted routes (403 on others)
  - `tests/Unit/FeeStatusServiceTest.php` — computeStatus() all four branches
  - `tests/Unit/ReportCardServiceTest.php` — build() returns correct grades for given scale
  - `tests/Unit/AdmissionNumberServiceTest.php` — generates correct pattern, handles concurrent counter increments
- Model factories: `StudentFactory`, `StaffFactory`, `ExamFactory`, `FeeStructureFactory`, `FeePaymentFactory`, `AttendanceFactory` under `database/factories/Tenant/`
- GitHub Actions: `.github/workflows/tests.yml` — runs `php artisan test --parallel` on push/PR to main; uses SQLite for tenant DB in CI

---

## Phase 9 — Growth Features

Build these after Phase 8 is complete and stable. Each feature is independently deployable.

### 32 Subject-Teacher Assignments

**UI:**

- New settings tab: Settings > Assignments (`/settings/assignments`) — 6th settings tab
- Table: Subject | Class | Section | Assigned Teacher | Academic Year | Actions (Edit / Delete)
- "Add Assignment" primary button → modal: select Teacher (staff with teacher role), Subject, Class, Section (conditional), Academic Year (defaults to current)
- Filter bar: academic year select + class select — same filter pattern as other settings pages

**Logic:**

- New migration: `create_subject_teacher_assignments_table` (id uuid, teacher_id FK→staff, subject_id FK→subjects, class_id FK→school_classes, section_id FK→sections nullable, academic_year_id FK→academic_years, unique constraint [teacher_id, subject_id, class_id, section_id, academic_year_id], timestamps)
- `SubjectTeacherAssignment` model — `HasUuids`, fillable, relationships to Staff, Subject, SchoolClass, Section, AcademicYear
- `SubjectTeacherAssignmentController` — CRUD under `permission:settings.manage`
- `ExamController::marks()` and `saveMarks()`: replace current timetable-based teacher restriction with `SubjectTeacherAssignment::where('teacher_id', $staff->id)->where('class_id', $classId)->where('subject_id', $subjectId)->where('academic_year_id', currentYear)->exists()` check
- `AttendanceController::index()`: for teachers (without `settings.manage`), filter class list to only classes where they have at least one `SubjectTeacherAssignment` row in the current academic year
- Backfill command: `php artisan schoolflow:backfill-assignments` — creates `SubjectTeacherAssignment` rows from existing `Timetable` rows for all tenants (run once after deploy)

---

### 33 Staff Leave Management

**UI:**

- New nav item: "Leave" (visible to all authenticated users — staff see own requests; admin sees all)
- Two tabs: **My Requests** (all roles) | **All Requests** (admin/school_admin only)
- My Requests tab: submit form card (leave type select, start date, end date, reason textarea), list of own requests with status badge and reason
- All Requests tab: pending requests table first, then approved/rejected history. Per row: staff name, type, dates, reason, Approve / Reject actions. Reject opens inline reason input.
- Status badges: Pending → `bg-warning-light text-warning`; Approved → `bg-success-lightest text-success-foreground`; Rejected → `bg-error-light text-error`

**Logic:**

- New permission: `leave.view` (own requests), `leave.manage` (all requests + approve/reject); school_admin gets manage, all others get view
- New migration: `create_leave_requests_table` (id uuid, staff_id FK→staff, leave_type enum['sick','annual','maternity','paternity','personal','other'], start_date date, end_date date, reason text, status enum['pending','approved','rejected'] default 'pending', approved_by FK→users nullable, approved_at timestamp nullable, rejection_reason text nullable, timestamps)
- `LeaveRequest` model + `LeaveController` (index, store, approve, reject)
- Routes: `GET /leave`, `POST /leave` (create), `PATCH /leave/{leaveRequest}/approve`, `PATCH /leave/{leaveRequest}/reject` under `permission:leave.view`; approve/reject additionally check `permission:leave.manage` in controller
- `StaffAttendance` integration: `AttendanceController::staff()` checks for approved leave rows overlapping the selected date — pre-marks those staff as "On Leave" status (new value, distinct from absent)
- `AttendanceReportService`: "On Leave" counted separately from absent in staff attendance summary
- Notifications: dispatch `LeaveRequestSubmitted` notification to school_admin on new request; `LeaveRequestDecided` notification to staff on approve/reject

---

### 34 Parent Portal (Parent-Child Relationships)

**UI:**

- Student create/edit form: existing `guardian_email` field gets a new companion toggle: **"Create Parent Login Account"** — if checked, system creates a `users` row with parent role and links it to the student
- Student profile: new **"Linked Parent Account"** card — shows parent login status (Active / Not Created), "Create Login" / "Revoke Login" actions
- Parent dashboard: child selector pill row at top of page (shows all linked children by name + class). Clicking a child switches the dashboard to that child's data. Selected child stored in session.
- Parent views for attendance, exams, fees — identical in layout to student views but controlled by session-selected child

**Logic:**

- New migration: `create_parent_student_table` (parent_user_id FK→users on delete cascade, student_id FK→students on delete cascade, composite PK [parent_user_id, student_id])
- `User` model: `students()` BelongsToMany `Student` through `parent_student`
- `Student` model: `parents()` BelongsToMany `User` through `parent_student`
- `StudentController::createParentLogin(Student)`: find or create `User` by `guardian_email`, assign parent role, attach via `parent_student`, email credentials — wrapped in `DB::transaction`
- `StudentController::revokeParentLogin(Student)`: detach from `parent_student`, optionally delete `User` if not linked to any other student
- Gate enforcement: in `AttendanceController`, `ExamController`, `FeeController` — for parent role, resolve student from `Auth::user()->students()` keyed by session `selected_child_id`; 403 if requested student not in their linked list
- `DashboardController`: for parent role, pass `$linkedStudents = Auth::user()->students()->with('schoolClass','section')->get()` and `$selectedStudent` (session-resolved or first child)
- Guardian email deduplication: if `guardian_email` already belongs to an existing `User`, link that existing account rather than creating a duplicate

---

### 35 Homework & Assignment Management

**UI:**

- New nav item: "Assignments" (visible with `assignments.view` permission — teacher, student, parent)
- Teacher view: table of own created assignments; columns: Title, Subject, Class, Due Date, Submissions (X/Y count badge), Status. "Create Assignment" primary button → modal.
- Student view: list of assignments for their class — Pending (due in future) and Submitted tabs. Each card shows title, subject, teacher, due date badge, "Submit" button. Submitted card shows mark + feedback.
- Admin view: all assignments across all teachers; filter by teacher + class

**Logic:**

- New permissions: `assignments.view`, `assignments.create`, `assignments.edit`, `assignments.delete`, `assignments.submit`. Seed: teacher gets all except submit; student/parent get view + submit; school_admin gets all.
- New migrations:
  - `create_assignments_table` (id uuid, teacher_id FK→staff, subject_id FK, class_id FK, section_id FK nullable, title string, description text, due_date datetime, total_marks decimal nullable, timestamps)
  - `create_assignment_submissions_table` (id uuid, assignment_id FK, student_id FK, submission_text text nullable, file_path string nullable, submitted_at timestamp, marks_awarded decimal nullable, feedback text nullable, timestamps; unique [assignment_id, student_id])
- `Assignment` model + `AssignmentSubmission` model
- `AssignmentController`: CRUD; teacher can only manage own assignments (check `$teacher->user_id === Auth::id()`); admin manages all
- `SubmissionController`: student submits (`store`); teacher grades (`grade` — PATCH with marks + feedback)
- File submissions: stored at `storage/{tenantId}/assignments/{assignment_id}/{student_id}/submission.{ext}` on local disk; served via controller (no public URL)
- Dashboard badge: student sees "X assignments due soon (within 3 days)" in their simplified dashboard view; teacher sees "X ungraded submissions" badge

---

### 36 Disciplinary & Behavior Tracking

**UI:**

- Student profile: new **"Behavior"** tab card (alongside Personal, Guardian, Academic cards on the `show.blade.php` page)
- Tab content: chronological incident list — Type badge | Date | Reported By | Description (expandable) | Action Taken | Parent Notified badge
- "Log Incident" button (teacher/admin only): slide-in modal with student name (pre-filled from profile, or searchable from main incidents page), Incident Type, Date, Description, Action Taken, Notify Parent checkbox
- Reports: `/reports/behavior` — filter by class, date range, incident type; exportable PDF

**Logic:**

- New permissions: `behavior.view`, `behavior.create`, `behavior.edit`, `behavior.delete`; teacher gets view + create; school_admin gets all
- New migration: `create_disciplinary_records_table` (id uuid, student_id FK→students, reported_by FK→users, incident_type enum['warning','detention','suspension','expulsion','commendation'], description text, action_taken text nullable, date date, parent_notified boolean default false, timestamps)
- `DisciplinaryRecord` model + `DisciplinaryController`
- Routes: `GET /behavior` (index, admin view, `behavior.view`), `GET /students/{student}/behavior` (student profile tab, `behavior.view`), `POST /behavior` (create, `behavior.create`), `DELETE /behavior/{record}` (destroy, `behavior.delete`)
- Email notification to `guardian_email` when `parent_notified = true` on creation — queued `DisciplinaryNotification`
- Student profile `show.blade.php`: add "Behavior" tab card; loads via `DisciplinaryRecord::where('student_id', $student->id)->latest()` — only visible to users with `behavior.view`

---

### 37 Targeted Announcements & Notification Centre

**UI:**

- Announcement create/edit modal: add **Audience** section below the `is_public` toggle:
  - Radio: All School / Specific Class / Specific Role / All Students / All Parents
  - Multi-select class list appears when "Specific Class" chosen
- Notification bell icon in topbar (already has placeholder): show unread count red badge; dropdown lists 5 most recent notifications with title, time, mark-read on click; "Mark all as read" link at bottom; "View all" link to `/notifications`
- `/notifications` page: full paginated list of notifications for the logged-in user

**Logic:**

- New columns on `announcements` (migration): `audience_type` string default 'all', `audience_ids` JSON nullable
- `AnnouncementController::store()` / `update()`: save audience fields; dispatch `SendAnnouncementNotifications` queued job
- `SendAnnouncementNotifications` job: resolves target user IDs based on `audience_type` (all authenticated users / students in given class / users with given role / etc.); bulk-inserts `notifications` rows; dispatches email per user if notification settings allow
- New migration: `create_notifications_table` (id uuid, user_id FK→users, announcement_id FK→announcements nullable, type string, message string, data JSON nullable, read_at timestamp nullable, timestamps)
- `NotificationController`: `index()` (paginated list), `markRead(Notification)` (PATCH), `markAllRead()` (PATCH)
- Topbar bell: Livewire component `NotificationBell` — polls count on page load via `wire:init="loadCount"`, updates badge. Alternative: Alpine `fetch()` on page load if Livewire not used elsewhere on the layout.
- `AnnouncementController::index()`: filter announcements shown to non-admin users based on `audience_type` — only show announcements targeting their role/class or "all"

---

### 38 Expense & Budget Management

**UI:**

- New nav item: "Expenses" (visible to accountant/admin with `expenses.view`)
- Table: Date | Category | Description | Amount | Recorded By | Receipt | Actions
- Filter bar: category select + date range (month picker)
- "Log Expense" primary button → modal: Category (select + "Add Category" inline), Amount, Date, Description, Receipt (file upload, optional)
- Summary strip above table: Total This Month | Total This Term | YTD Total

**Logic:**

- New permissions: `expenses.view`, `expenses.create`, `expenses.edit`, `expenses.delete`; accountant gets all; school_admin gets all
- New migrations:
  - `create_expense_categories_table` (id uuid, name string unique, timestamps)
  - `create_expenses_table` (id uuid, category_id FK→expense_categories, amount decimal:2, date date, description string, receipt_path string nullable, recorded_by FK→users, timestamps)
- `ExpenseCategory` model, `Expense` model, `ExpenseController`
- Routes: `GET /expenses`, `POST /expenses`, `PUT /expenses/{expense}`, `DELETE /expenses/{expense}` under `permission:expenses.view/create/edit/delete`
- Receipt stored at `storage/{tenantId}/expenses/{expense_id}/receipt.{ext}` on local disk; served via controller
- Seed default categories: "Salaries", "Utilities", "Supplies", "Maintenance", "Events", "Other" — inserted during `TenantProvisioningService`

---

### 39 Scholarship & Fee Waiver Management

**UI:**

- Student profile: new **"Fee Discounts"** card — table of active discounts (Type | Value | Applies To | Reason | Expiry); "Add Discount" button (accountant/admin only)
- "Add Discount" modal: Discount Type (Percentage / Fixed Amount), Value, Applies To (All fees / Specific fee item select), Reason, Valid Until (optional date)
- Fee collection view (admin): discounted fee items show original amount struck-through with adjusted amount and a "Discounted" badge

**Logic:**

- New migration: `create_fee_discounts_table` (id uuid, student_id FK→students, fee_structure_id FK→fee_structures nullable [null=blanket discount on all fees], discount_type enum['percentage','fixed'], discount_value decimal:2, reason text, approved_by FK→users, valid_from date nullable, valid_until date nullable, timestamps)
- `FeeDiscount` model + `FeeDiscountController` (CRUD under `fees.edit` permission)
- `FeeStatusService::getStudentFeeItems()`: for each fee structure, query active `FeeDiscount` rows for the student (where `fee_structure_id` matches or is null, and `valid_until` is null or in future); apply discount to `effective_amount` before computing payment status
- `PaystackService::initializeTransaction()`: recalculates outstanding via `FeeStatusService` at checkout time — never use page-load cached amount
- Term Bill PDF (`term-bill-pdf.blade.php`): show original amount and discount line per discounted item
- Receipt PDF: show discount breakdown if a discount was applied to the payment

---

### 40 Academic Performance Analytics

**UI:**

- Reports page: new tab **"Academic Analytics"** (5th tab, after Fee Collection)
- Filter bar: Exam select (or "All exams this term") + Class + Section (conditional)
- Results card with three sections:
  1. Subject Averages — horizontal bar chart (`Chart.js`, `chartType: 'bar'`, `indexAxis: 'y'`), one bar per subject showing avg score
  2. Pass Rate — horizontal bar chart showing % of students at or above passing grade per subject
  3. Class Performance Trend — line chart showing class average per exam over time (only shown when multiple exams exist for the term)
- Summary table: Subject | Students | Avg Score | Highest | Lowest | Pass Rate

**Logic:**

- `app/Services/ExamAnalyticsService.php`:
  - `buildSubjectReport(string $examId, string $classId, ?string $sectionId): array` — queries `exam_results` filtered by exam+class+section, groups by `subject_id`, computes avg/min/max/pass_rate (using school's grading scale passing threshold)
  - `buildClassTrend(string $classId, ?string $sectionId, string $termId): array` — queries all exams in term, per-exam class average; returns `[['exam_name' => '...', 'average' => N], ...]`
- `ReportController`: add `academicAnalytics(Request $request)` method; passes analytics data as JSON to view
- Chart data passed to view via `Js::from()` — Alpine `x-init` initialises Chart.js charts (same pattern as dashboard)
- PDF export: `GET /reports/academic/pdf` — renders `academic-analytics-pdf.blade.php` (A4 portrait, table format — no charts in PDF)

---

### 41 Attendance Analytics & Chronic Absentee Reports

**UI:**

- Reports page: new tab **"Attendance Alerts"** (alongside existing Attendance Report tab — or as sub-section)
- Filter bar: Class | Section (conditional) | Term | Absence threshold slider (default 80%)
- Table: Student Name | Admission No | Class | Absences | Days Marked | % Present | Guardian Contact | "Notify Guardian" button
- "Notify All Below Threshold" bulk button — sends email to all guardian emails in the filtered list

**Logic:**

- `AttendanceReportService::buildChronicAbsentees(string $termId, string $classId, ?string $sectionId, int $threshold = 80): array` — queries `attendances` for the term's date range, computes per-student absent count + percent_present; returns students below threshold with guardian_contact + guardian_email
- `AttendanceController::notifyGuardian(Request, Student)` — dispatches `LowAttendanceAlert` notification to `$student->guardian_email`; returns redirect with flash message
- `AttendanceController::notifyAll(Request)` — bulk dispatch; rate-limited to prevent abuse
- Route: `POST /attendance/notify/{student}` under `permission:attendance.view`; bulk: `POST /attendance/notify-bulk`
- Dashboard: teacher/admin dashboard adds a "Chronic Absentees" stat card showing count of students below 80% attendance this term — clicking navigates to the Attendance Alerts report

---

### 42 Online Admission Application

**UI:**

- Public route: `{school}.schoolflow.com/apply` — unauthenticated, tenant-scoped
- Multi-section form: Student Information (name, DOB, gender, class applying for) | Guardian Information (name, contact, email) | Previous School (optional)
- Submit → "Application Received" confirmation card; option to download a receipt PDF
- New admin page: `/admissions` (school_admin / `admissions.manage`)
  - Table: Applicant Name | Class Applying | Guardian | Date | Status badge | Review button
  - Filter: status (pending/accepted/rejected) + class applying
  - "Review" opens a detail slide-over with full application data + Accept / Reject buttons
  - Reject: requires a reason (shown in rejection email)
  - Accept: redirects to `/students/create?from_application={id}` with form pre-filled

**Logic:**

- New permissions: `admissions.view`, `admissions.manage`; school_admin gets manage
- New migration: `create_admission_applications_table` (id uuid, applicant_name, date_of_birth date nullable, gender string nullable, class_applying_for string, guardian_name, guardian_contact, guardian_email nullable, previous_school nullable, status enum['pending','accepted','rejected'] default 'pending', notes text nullable, reviewed_by FK→users nullable, reviewed_at timestamp nullable, rejection_reason text nullable, timestamps)
- `AdmissionApplication` model
- `PublicApplicationController::store(Request)`: no auth, validates fields, creates application row, dispatches confirmation email to `guardian_email`, returns confirmation view
- `AdmissionController` (admin): index, show, accept, reject — under `permission:admissions.manage`
- `StudentController::create(Request)`: if `?from_application={id}` query param present, load `AdmissionApplication` and pass pre-fill data to view; mark application as `accepted` and set `reviewed_by` on student save
- Robots.txt for tenant: update `allow: /apply` alongside existing `allow: /`
- Public nav on school public page: add "Apply Now" button if `admissions.open` school_profile flag is set (add boolean `admissions_open` to school_profile; default false)

---

### 43 Student Transcript Generation

**UI:**

- Student profile `show.blade.php`: add "Download Transcript" button in the profile header card actions (visible to admin always; visible to student/parent only when at least one published exam exists)
- Transcript is a multi-page PDF with all published exam results across all academic years, grouped by year → term → exam

**Logic:**

- `ReportCardService::generateTranscript(Student): string`:
  - Queries all `exam_results` for the student where `exams.is_published = true`
  - Eager-loads: `exam.term.academicYear`, `subject`
  - Groups by academic year → term → exam
  - Computes per-exam average and overall cumulative average
  - Fetches attendance % per term from `AttendanceReportService`
  - Renders `transcript-pdf.blade.php` via dompdf, saves to `storage/{tenantId}/transcripts/{student_id}.pdf`
  - Returns absolute path
- `TranscriptController::download(Student)`: authorization — admin always allowed; student/parent checks `student->user_id === Auth::id()` (or parent-child link). Calls service, streams file.
- Route: `GET /students/{student}/transcript` (no separate permission gate — inherits from student profile access)
- New view `transcript-pdf.blade.php`: same school header pattern as `report-card-pdf.blade.php`. Grouped by year section headers (blue). Per-exam results table. Cumulative GPA row per year. Attendance % summary per term. Signature block at end.

---

### 44 Multi-Currency & Locale Support

**UI:**

- Settings > School Profile: add **Currency** select dropdown (options: GHS — Ghanaian Cedi, NGN — Nigerian Naira, KES — Kenyan Shilling, USD — US Dollar, EUR — Euro; symbol auto-populated from selection)
- All money displays throughout the app update to use the school's configured currency symbol

**Logic:**

- New migration: add `currency_code` (string 3 chars, default 'GHS') and `currency_symbol` (string 5 chars, default '₵') to `school_profile`
- `app/Helpers/Money.php` (new helper, not a class — global functions file): `format_money(float $amount, string $symbol): string` — `$symbol . ' ' . number_format($amount, 2)`; auto-loaded via `composer.json` autoload files
- `ViewComposer` in `AppServiceProvider`: add `$currencySymbol = $schoolProfile?->currency_symbol ?? '₵'` to the shared data injected into `layouts.tenant`
- All Blade views: replace hardcoded `GHS` and `number_format` calls with `format_money($amount, $currencySymbol)` or `{{ $currencySymbol . ' ' . number_format($amount, 2) }}`
- `PaystackService`: read currency from `SchoolProfile::first()->currency_code` instead of `config('paystack.currency')`; pesewa conversion factor stays 100 (applies to GHS/NGN/KES — verify Paystack doc for each)
- PDF templates: update all PDF views to use `$currencySymbol` variable (passed by service methods that already pass `$schoolProfile`)

---

### 45 Data Export, Backup & Privacy Tools

**UI:**

- Student profile: "Export Student Data" button (admin only) — triggers background job, shows "We'll email you when it's ready"
- Settings > Data & Privacy (`/settings/privacy`): new settings tab
  - Deleted Records: link to student/staff trash views
  - Data Retention Policy: description of 90-day purge policy
  - Export All School Data: "Request Full Export" button — exports everything as a ZIP of CSVs

**Logic:**

- Soft deletes: add `SoftDeletes` trait to `Student`, `Staff`, `User` models; add `deleted_at` column to each via new migration
- Trash routes: `GET /students/trash` (admin only) — shows `Student::onlyTrashed()`, with Restore and Permanently Delete actions. Same for staff.
- `StudentController::destroy()`: changes from `$student->delete()` (now soft deletes); add `restore(Student)` and `forceDelete(Student)` methods
- `ExportStudentDataJob`: generates ZIP containing `student.json` (all model data), `attendance.csv`, `exam_results.csv`, `fee_payments.csv`, and all downloaded report card PDFs for that student. Emails admin with download link (signed URL, expires 24h).
- `ExportAllSchoolDataJob`: CSVs for all tables in the tenant DB. Emails admin with download link.
- Anonymisation: `StudentController::anonymize(Student)`: sets `full_name = 'Deleted Student'`, `guardian_name = 'Removed'`, `guardian_contact = '000'`, `guardian_email = null`, `photo_path = null`; preserves academic records for reporting. Admin-only action with confirmation dialog.
- Scheduled purge: artisan command `schoolflow:purge-deleted` — permanently deletes soft-deleted records older than 90 days. `$schedule->command('schoolflow:purge-deleted')->monthly()` in `Kernel.php`.
- Privacy settings route group: `GET/POST /settings/privacy` under `permission:settings.manage`

---

### 46 REST API (Sanctum)

**UI:**

- My Account page: new **"API Tokens"** card — list of active tokens with name + creation date + last used. "Generate Token" modal: token name + permission scopes (read-only/full). Copy-to-clipboard on creation (one-time display). Revoke button per token.

**Logic:**

- Populate `routes/api.php` with tenant-scoped routes, wrapped in the same `InitializeTenancyBySubdomain` + `auth:sanctum` middleware group
- Tenant resolution: API requests to `{school}.schoolflow.com/api/v1/*` resolved by subdomain (same as web routes)
- v1 endpoints (all under `/api/v1/`):
  - `GET /students` — paginated list (students.view)
  - `GET /students/{id}` — single student (students.view)
  - `GET /students/{id}/attendance` — attendance history (attendance.view)
  - `POST /attendance` — mark attendance records (attendance.edit) — for biometric gate integration
  - `GET /students/{id}/exams` — published exam results (exams.view)
  - `GET /fees/{studentId}` — fee status list (fees.view)
  - `GET /announcements` — public + non-public announcements (no permission gate — all authenticated)
- API Resource classes: `StudentResource`, `AttendanceResource`, `ExamResultResource`, `FeeStatusResource` — JSON:API-style with `data` wrapper
- API token management: `AccountController` extended with `tokens()`, `createToken()`, `revokeToken()` methods; route: `GET/POST/DELETE /account/tokens`
- Rate limiting: `throttle:60,1` per token on all `/api/v1/*` routes
- Error responses: always JSON `{'message': '...', 'errors': {...}}` — never HTML. `Handler.php` updated to return JSON for API routes.
- `routes/api.php` must be added to `config/tenancy.php` tenant route files list

---

## Phase 10 — Competitive Advantages

Build these after Phase 9 is stable. They differentiate SchoolFlow from generic alternatives.

### 47 Platform Self-Service Billing

**UI:**

- Tenant Settings > Billing (`/settings/billing`): shows student count, rate per student, total amount due, cycle dates, payment status badge
- "Pay Now" button (visible when `payment_status = unpaid`) — initiates Paystack payment from school to SchoolFlow
- Payment history table: Date | Amount | Reference | Status | Receipt
- If within 14 days of `cycle_end` and unpaid: show persistent warning banner at top of every page ("Your subscription expires in X days")
- If past `cycle_end` and unpaid: login redirects to upgrade wall (not a hard block — full-screen modal with Pay Now + "Contact Us" options; dismiss-able for 24h grace period)

**Logic:**

- Webhook endpoint on **central** app (`routes/web.php`): `POST /paystack/webhook` — distinct from tenant-level webhook. Uses same `PaystackService::verifyWebhookSignature()` and `verifyTransaction()` pattern. On `charge.success`: updates `subscription_plans.payment_status = 'paid'`, advances `cycle_start`/`cycle_end` by one year.
- `SubscriptionController` (central, tenant-facing): `GET /settings/billing` renders billing view, `POST /settings/billing/pay` initialises Paystack transaction with `metadata: {tenant_id, subscription_plan_id}`, `GET /settings/billing/callback` handles redirect back
- Dunning command: `schoolflow:send-billing-reminders` — queries `subscription_plans` where `payment_status = unpaid` and `cycle_end` is within 14 days; sends email per school admin. Schedule: daily at 09:00.
- Upgrade wall middleware: `EnsureSubscriptionActive` — applied after auth on all tenant routes except `/settings/billing` and `/login`; checks `cycle_end < today && payment_status = unpaid && (grace period flag not set or expired)`

---

### 48 Payroll & Staff Salary Management

**UI:**

- New nav item: "Payroll" (accountant/admin with `payroll.view`)
- **Salary Structures** tab: table of all staff with gross salary, total allowances, total deductions, net. "Edit" per row opens modal with allowances/deductions breakdown (itemised JSON fields: housing, transport, medical, etc.)
- **Payroll Runs** tab: list of past runs (month, year, status, processed by, total payout). "Run Payroll" button for current month.
- Payslip download per staff per run

**Logic:**

- New permissions: `payroll.view`, `payroll.create`, `payroll.edit`; accountant + school_admin get all
- New migrations:
  - `create_salary_structures_table` (staff_id FK unique, gross decimal:2, allowances JSON default '{}', deductions JSON default '{}', effective_from date, timestamps)
  - `create_payroll_runs_table` (id uuid, month tinyint, year smallint, status enum['draft','processed'], processed_by FK→users nullable, processed_at timestamp nullable, unique [month, year], timestamps)
  - `create_payroll_items_table` (id uuid, payroll_run_id FK, staff_id FK, gross decimal:2, allowances_total decimal:2, deductions_total decimal:2, net decimal:2, payment_status enum['pending','paid'], timestamps)
- `PayrollService::runPayroll(int $month, int $year)`: queries `salary_structures` for all active staff, creates `payroll_run` row, bulk-inserts `payroll_items`, dispatches payslip PDF generation per staff. Wrapped in `DB::transaction`.
- Payslip PDF (`payslip-pdf.blade.php`): same dompdf self-contained pattern; school header; itemised allowances/deductions table; highlighted net pay box.
- `Expense` integration: on payroll run processed, optionally create an `Expense` row for total payroll cost (category: "Salaries") for the month — manual trigger by accountant

---

### 49 Payment Plans / Installment Support

**UI:**

- Fee collection admin view: per outstanding fee item, additional action "Set Installment Plan" button (alongside "Record Payment")
- Installment plan modal: number of installments (2–6), auto-split or manual amount per installment, due date per installment
- Student fee view: if installment plan exists, shows installment schedule instead of single outstanding amount — each installment has its own status badge and "Pay Now" button

**Logic:**

- New migration: `create_payment_plans_table` (id uuid, student_id FK, fee_structure_id FK, installments JSON [{due_date, amount, status, paid_at}], approved_by FK→users, timestamps; unique [student_id, fee_structure_id])
- `PaymentPlan` model + `PaymentPlanController` (create, update under `fees.edit`)
- `FeeStatusService::getStudentFeeItems()`: if `payment_plan` exists for student+fee_structure, replace standard amount/status computation with installment-based computation — status per installment; overall status is "partial" until all installments paid
- `PaystackService::initializeTransaction()`: when fee has a payment plan, amount = next unpaid installment's amount (not full outstanding). Metadata includes `installment_index` so webhook can update correct installment in the JSON.
- `PaystackWebhookController`: on verified payment, if metadata has `installment_index`, update that installment's `status` and `paid_at` in the `payment_plans.installments` JSON

---

### 50 Financial P&L Dashboard

Requires Feature 38 (Expense Management) to be built first.

**UI:**

- Reports page: new tab **"Financial Summary"** (alongside Academic Analytics, Fee Collection, etc.)
- Filter bar: Academic Year select + term select (or "Full Year")
- Summary cards row: Total Income (fee collections) | Total Expenses | Net Balance (colour-coded green/red)
- Monthly trend chart: grouped bar chart (income vs expenses per month), Chart.js
- Income breakdown table: fee category → amount collected
- Expense breakdown table: expense category → amount spent
- "Export PDF" button

**Logic:**

- `FinancialSummaryService::build(string $academicYearId, ?string $termId): array`:
  - Income: query `fee_payments` joined with `fee_structures` filtered by year/term; group by `fee_item` → sum amounts
  - Expenses: query `expenses` filtered by date range matching year/term; group by `expense_categories.name` → sum amounts
  - Monthly breakdown: group both by `YEAR(date), MONTH(date)` for chart data
  - Returns `['income_total', 'expense_total', 'net', 'income_by_category', 'expense_by_category', 'monthly_trend']`
- Route: `GET /reports/financial` under `permission:reports.view`
- `GET /reports/financial/pdf` → PDF version (`financial-pdf.blade.php`, A4 landscape)

---

### 51 Teacher Class Register & Lesson Plans

**UI:**

- New nav item: "Register" (visible to teacher + admin with `register.view`)
- Two tabs: **Class Register** | **Lesson Plans**
- Class Register tab: filter by class/subject/date + Load button; entry form with topic covered (text), notes (textarea), materials upload (optional). History view: previous entries for selected class/subject in reverse chronological order.
- Lesson Plans tab: weekly calendar view (Mon-Fri columns); "Create Lesson Plan" button → modal with week_start date picker, subject, class, objectives, content (plain textarea — no rich text dependency). Admin can view all teachers; teachers see only own.

**Logic:**

- New permissions: `register.view`, `register.create`; teacher gets both; school_admin gets all
- New migrations:
  - `create_class_registers_table` (id uuid, teacher_id FK→staff, class_id FK, section_id FK nullable, subject_id FK, date date, topic_covered string, notes text nullable, timestamps)
  - `create_lesson_plans_table` (id uuid, teacher_id FK, subject_id FK, class_id FK, section_id FK nullable, week_start date, content text, objectives text nullable, timestamps; unique [teacher_id, subject_id, class_id, section_id, week_start])
- `ClassRegister` model + `LessonPlan` model
- `RegisterController`: CRUD + filter; teacher scope (`where('teacher_id', $staff->id)` unless admin)
- `LessonPlanController`: CRUD; admin can view all teachers' plans
- Register PDF: `GET /register/export/{staff}/{month}` — monthly register for one teacher as PDF (for school inspection use)

---

### 52 Outbound Webhook System

**UI:**

- Settings > Webhooks (`/settings/webhooks`): new settings tab (school_admin only)
- Table: Endpoint URL | Events subscribed | Status (active/inactive) | Last delivery status | Actions
- "Add Webhook" modal: URL (https required), Secret (auto-generated or custom), per-event checkboxes: student_enrolled / payment_received / attendance_marked / exam_published / announcement_posted
- Delivery log: per-webhook "View Deliveries" link → paginated table of delivery attempts (event, HTTP status, response time, timestamp, "Retry" button on failed)

**Logic:**

- New permissions: `webhooks.manage`; school_admin only
- New migrations:
  - `create_webhooks_table` (id uuid, url string, events JSON, secret string, active boolean default true, timestamps)
  - `create_webhook_deliveries_table` (id uuid, webhook_id FK, event string, payload JSON, response_status smallint nullable, response_body text nullable, attempted_at timestamp, next_retry_at timestamp nullable, timestamps)
- `Webhook` model + `WebhookDelivery` model + `WebhookController` (CRUD) + `WebhookDeliveryService`
- `SendWebhookPayload` queued job: for each active webhook subscribed to the event, POSTs signed JSON payload (`X-SchoolFlow-Signature: hmac-sha256({payload}, {secret})`); records delivery row; on failure (non-2xx), schedules retry with exponential backoff (3 attempts: 1min, 5min, 30min)
- Dispatch hooks in: `StudentController::store()` → `student_enrolled`; `FeeController::pay()` + `PaystackWebhookController` → `payment_received`; `AttendanceController::save()` → `attendance_marked`; `ExamController::publish()` → `exam_published`; `AnnouncementController::store()` → `announcement_posted`
- Retry mechanism: `RetryFailedWebhooks` scheduled command runs every 5 minutes; queries `webhook_deliveries` where `next_retry_at <= now()` and attempt count < 3

---

### 53 Custom Domain Support (Complete Feature 22)

Feature 22 currently shows "coming soon." This feature replaces that placeholder with full implementation.

**UI:**

- Settings > Custom Domain: replace coming-soon view with full management UI
- Input: full domain (e.g. `portal.exampleschool.com`), CNAME instructions card explaining DNS setup (`portal.exampleschool.com CNAME schoolflow.com.`)
- Status indicator badge: Pending / Verified / Active — updates after "Verify DNS" button click
- Active custom domains list (can have multiple); delete action

**Logic:**

- `CustomDomainController::store(Request)`:
  - Validates domain format (`regex:/^[a-z0-9.-]+\.[a-z]{2,}$/`) and HTTPS compliance
  - Checks `domains` table for uniqueness (central DB)
  - Creates new `Domain` row for the tenant with `verified_at = null`
  - Redirects back with instructions
- `CustomDomainController::verify(Domain)`:
  - Calls `dns_get_record($domain, DNS_CNAME)` — checks if any record's `target` matches `schoolflow.com`
  - On success: `$domain->update(['verified_at' => now()])` and flash success
  - On failure: flash "DNS not yet propagated — please try again in a few minutes"
- Caddy infrastructure: already handles auto-SSL per `architecture.md` — no Caddy config changes needed; the new `domains` row is picked up on the next request via stancl's domain resolution
- `CustomDomainController::destroy(Domain)`: soft-guard — cannot delete the original `{subdomain}.schoolflow.com` domain; only custom domains deletable
- Tenant login block: applies equally whether request comes via subdomain or custom domain — `InitializeTenancyBySubdomain` (or `InitializeTenancyByDomain`) resolves same tenant either way

---

### 54 Multi-Language UI Support

**UI:**

- Settings > School Profile: add **Language** select (English / Français / Kiswahili) below currency select
- Selecting a language switches the UI language for all authenticated users in that tenant on next page load
- Flash messages, labels, status badges, nav items, button text all translated

**Logic:**

- New migration: add `locale` (string 5 chars, default 'en') to `school_profile`
- Laravel `lang/` directory structure:
  - `lang/en/` — extract all UI strings from Blade views into translation files: `lang/en/common.php`, `lang/en/students.php`, `lang/en/attendance.php`, etc.
  - `lang/fr/` — French translations
  - `lang/sw/` — Swahili translations
- Replace all hardcoded UI strings in tenant Blade views with `__('common.save')`, `__('students.add')`, etc.
- `LocaleMiddleware`: reads `SchoolProfile::first()->locale` (cached for 5 minutes); calls `App::setLocale($locale)` and `Carbon::setLocale($locale)` on every tenant request; registered in tenant middleware group
- Settings > School Profile: `UpdateSchoolProfileRequest` — add `locale` field validation (`in:en,fr,sw`)
- PDF templates: strings in PDF views also use `__()` calls — dompdf renders with the locale set for that request
- Date formatting: use `$date->translatedFormat('d M Y')` throughout for locale-aware dates
- DejaVu Sans font (already used in all PDFs) covers Latin Extended characters for French; Swahili also uses Latin script — no font change needed