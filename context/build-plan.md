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

| Phase                                  | Features |
| ---------------------------------------- | -------- |
| Phase 1 — Foundation                    | 5        |
| Phase 2 — School Setup & Core Records   | 7        |
| Phase 3 — Attendance & Timetable        | 2        |
| Phase 4 — Exams & Report Cards          | 2        |
| Phase 5 — Fees & Payments               | 4        |
| Phase 6 — Communication & Public Page   | 2        |
| Phase 7 — Reports & Super Admin         | 3        |
| **Total**                               | **25**   |

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