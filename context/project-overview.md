# Project Overview

## About the Project

SchoolFlow is a multi-tenant SaaS school management system. Each school signs up, gets its own isolated database and a branded subdomain (`schoolname.schoolflow.com`), and runs its daily operations from there — admissions, attendance, timetables, exams, fees, and communication. A Super Admin panel oversees all schools, subscriptions, and billing from the central app.

Every school also gets a simple public-facing page on its own subdomain, generated from its profile data, so logged-out visitors see something other than a blank login screen.

---

## The Problem It Solves

Small and mid-sized schools run on spreadsheets, paper registers, and WhatsApp groups. Attendance, fee collection, exam results, and parent communication are scattered across tools that don't talk to each other, and nothing is backed up or auditable.

SchoolFlow gives a school one system: students and staff are managed in one place, attendance and exams feed directly into report cards, fees are tracked and paid online via Paystack, and parents/teachers/admins all see the same data through role-based dashboards.

---

## Tenancy & Subdomains

```
schoolflow.com                  → Central app (landing page, pricing, school signup, Super Admin)
{school}.schoolflow.com         → Tenant app (school's management system + public page)
```

- Each tenant has its own isolated database, created automatically on signup.
- Wildcard DNS (`*.schoolflow.com`) + wildcard SSL via Caddy route every subdomain to the same app.
- Custom domains — a school can point their own domain (e.g. `exampleschool.com` or `admin.exampleschool.com`) at SchoolFlow via CNAME. Caddy auto-issues SSL per custom domain on first request. A tenant can have multiple domains (their `*.schoolflow.com` subdomain plus any custom domains) all resolving to the same isolated data.

---

## Pages (MVP)

```
Central app
/                          → SchoolFlow marketing/landing page
/pricing                   → Pricing (per-student annual rate)
/register-school           → New school signup
/super-admin               → Super Admin dashboard (list/manage schools, subscriptions)

Tenant app — {school}.schoolflow.com
/                          → School's public page (auto-generated from profile + announcements)
/login                     → Login (School Admin, Teacher, Student, Parent, Accountant)
/dashboard                 → Role-based dashboard
/students                  → Student list, admission, profiles
/staff                     → Staff/teacher list and profiles
/attendance                → Daily attendance (manual entry)
/timetable                 → Class routine / timetable
/exams                     → Exam scheduling, marks entry, report cards
/fees                      → Fee structure, collection, Paystack payments, receipts
/announcements             → Notice board
/reports                   → Attendance & fee reports
```

---

## Navigation

Sidebar layout for the tenant app — collapsible, grouped by module. Top bar shows the school's logo/name, notifications, and account menu.

Nav items are filtered by the logged-in user's role (e.g. a Parent sees Dashboard, Fees, Attendance, Announcements only; a School Admin sees everything).

The central app (Super Admin / marketing) uses a simple top navbar — Pricing, Login, Register School.

---

## Core User Flow

### Marketing / Central App

- Visitor lands on `schoolflow.com`
- Views pricing, clicks "Register School"
- Fills school registration form (school name, admin name/email, subdomain choice)
- System creates tenant record, provisions isolated database, runs migrations, creates the School Admin account
- Redirect to `{school}.schoolflow.com/login`

### School Admin Onboarding

- School Admin logs in for the first time
- Dashboard shows a setup checklist: academic year, classes/sections, subjects, branding (logo/colors)
- Admin adds staff and students (manually or via bulk CSV import)

### Daily Operations

- Teachers mark daily attendance manually per class
- Teachers enter exam marks; report cards generate automatically based on grading scale
- Accountant/Admin sets up fee structure per class/term; parents pay via Paystack or admin records cash payments
- Admin posts announcements; visible on dashboard and on the school's public page

### Parent/Student View

- Parent logs in and sees their child's attendance, fee status, exam results, and school announcements
- No editing rights — read-only views plus online fee payment

### Public Page

- Logged-out visitor to `{school}.schoolflow.com` sees the school's name, logo, address, contact info, and recent announcements
- Auto-generated from data already entered by the School Admin — no separate CMS required for MVP

---

## Roles

| Role          | Access                                                                 |
| ------------- | ------------------------------------------------------------------------ |
| Super Admin   | All schools — subscriptions, enable/disable tenants, global analytics |
| School Admin  | Full access within their school, including creating custom roles      |
| Teacher       | Attendance, timetable, marks entry for assigned classes               |
| Accountant    | Fee structure, fee collection, financial reports                      |
| Student       | Read-only — own attendance, timetable, results, fees                   |
| Parent        | Read-only — linked child's attendance, results, fees, announcements    |

### Custom Roles

In addition to the fixed roles above, School Admin can create custom roles (e.g. "Vice Principal", "Exam Officer", "Librarian") and assign granular permissions to each — per module (Students, Staff, Attendance, Exams, Fees, Announcements), with view/create/edit/delete granularity. Custom roles are scoped to that school only.

---

## Features In Scope (MVP)

- Tenant registration with auto-provisioned database and subdomain
- Custom domain support per school (CNAME + Caddy auto-SSL)
- Per-student annual subscription billing — Super Admin sets rate per school, student count synced daily, payment tracked manually offline (Paystack self-service for this is Phase 2)
- Super Admin dashboard — list schools, student counts, amounts due, payment status, enable/disable subscriptions, impersonate ("login as school") for support, with full audit logging
- Academic year, class, section, subject setup
- Student admission and profiles, bulk CSV import
- Staff/teacher profiles
- Roles & permissions (Super Admin, School Admin, Teacher, Accountant, Student, Parent) plus custom roles with granular per-module permissions, created by School Admin
- Manual daily attendance — students and staff
- Class timetable/routine builder
- Exam scheduling, marks entry, grading scale, printable report cards
- Fee structure setup, fee collection (cash + Paystack), receipts, due/overdue tracking
- Ghana payroll compliance — SSNIT (employee 5.5%, employer 13%), Tier 2 (employee 5%, employer 5%), PAYE auto-computed via GRA 2024 tax bands; per-staff payment tracking; remittance summary for accountant
- Announcements / notice board
- Auto-generated public page per school
- Attendance and fee reports (exportable)
- Audit logs, role-based access control

---

## Features Out of Scope (Phase 2+)

- Student promotion — year-end bulk promotion to the next class, with retain/graduate options (see architecture.md for the `academic_years` and `student_class_history` schema this will use)
- Full website/CMS builder with multiple themes
- Multi-branch / multi-campus support
- Multi-language support
- Mobile apps (API will be built alongside web from day one, but apps come later)
- Library management
- SMS notifications, in-app messaging, parent-teacher meeting scheduler
- Leave management workflow
- Question bank / online quizzes
- QR attendance, biometric integration
- Transport and hostel management
- Inventory/asset management
- Advanced analytics dashboards

---

## Target User

A school owner, administrator, or small education group who:

- Currently manages student records, attendance, and fees manually or across disconnected tools
- Wants a single system for daily school operations
- Wants to collect fees online without building their own payment integration
- Wants parents and staff to have their own logins without IT overhead
- Is comfortable with a modern web dashboard

---

## SEO Considerations

### Central Marketing Site (`schoolflow.com`)

- Server-rendered Blade pages — no client-side rendering blocking indexability
- Each page (`/`, `/pricing`, feature pages in Phase 2) has unique title, meta description, and OpenGraph tags
- `sitemap.xml` and `robots.txt` present from launch
- `SoftwareApplication` schema markup on the landing/pricing pages
- FAQ section on landing page, structured for featured snippets

### Per-School Public Pages (`{school}.schoolflow.com/`)

- Each school's public page is a genuine SEO asset — it can rank for the school's own name and location ("[School Name] admission", "[School Name] contact")
- Meta title/description per public page generated from that school's profile data (school name, location, short description) — never a generic shared title across all schools
- Public pages must be indexable (not blocked in `robots.txt`) and contain real, unique content (school name, address, contact info, announcements) — avoid thin/duplicate-looking pages across schools
- Custom domains (when configured) carry their own SEO value — the school's existing domain authority transfers to their SchoolFlow-hosted page

---

## Success Criteria

- A new school can register, get a working subdomain, and log in within minutes
- School Admin can set up classes, subjects, and add students/staff without confusion
- Teachers can mark daily attendance in under a minute per class
- Report cards generate correctly from entered marks and the configured grading scale
- Parents can view their child's attendance, results, and fee status, and pay fees via Paystack
- Each school's data is fully isolated from every other school
- The Super Admin can see all registered schools and their subscription status
- UI is visually consistent across all tenant and central pages