# UI Registry

Living document. Updated after every component is built. Read this before building any new component — match existing patterns exactly before inventing new ones.

---

## How to Use

Before building any component:

1. Check if a similar component already exists here
2. If yes — match its exact classes
3. If no — build it following ui-rules.md and ui-tokens.md, then add it here

After building any component — update this file with the component name, file path, and exact classes used.

---

## Components

### Central Layout
**File:** `resources/views/layouts/central.blade.php`
**Description:** Root layout for all central (schoolflow.com) pages. No sidebar — top navbar + footer only.
- Navbar: `bg-surface border-b border-border sticky top-0 z-50` · height `h-16`
- Logo container: `w-9 h-9 rounded-[10px]` with `background: linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)`
- Logo text: `text-[19px] font-bold leading-7 text-text-darkest tracking-tight`
- Nav link (ghost): `px-4 py-2 text-sm font-medium text-text-dark hover:text-text-primary hover:bg-surface-secondary rounded-md transition-colors`
- Nav CTA (primary): `px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors`
- Mobile menu: Alpine.js `x-data="{ mobileOpen: false }"`, toggle button, collapsible nav
- Footer: `bg-surface border-t border-border` · content `max-w-[1440px] mx-auto px-6 py-12`
- Stacks: `@stack('title')`, `@stack('meta_description')`, `@stack('og_tags')`, `@stack('head')`, `@stack('scripts')`

---

### Navbar Logo
**Used in:** `layouts/central.blade.php`
- Outer: `flex items-center gap-2.5`
- Icon box: `w-9 h-9 rounded-[10px] flex items-center justify-center shrink-0` with blue gradient (`linear-gradient(45deg, #2563eb 0%, #1e3a8a 100%)`) inline style
- Text: `text-[19px] font-bold leading-7 text-text-darkest tracking-tight`

---

### Primary Button
**Used in:** landing, pricing, register-school, central layout
- Class: `px-4 py-2 text-sm font-medium bg-accent text-accent-foreground rounded-md hover:bg-accent-dark transition-colors`
- Large variant (CTAs): `px-6 py-3` or `px-8 py-3`

---

### Secondary Button (outlined)
**Used in:** landing, pricing
- Class: `px-4 py-2 text-sm font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors`

---

### Card
**Used in:** features section, pricing cards, register form, comparison table
- Class: `bg-surface border border-border rounded-2xl p-6 shadow-card`
- Shadow: `box-shadow: 0px 1px 3px rgba(0,0,0,0.1), 0px 1px 2px -1px rgba(0,0,0,0.1)` (via `shadow-card` utility)
- Never use colored backgrounds on cards

---

### Feature Card
**Used in:** `resources/views/central/landing.blade.php` — Features section
- Wrapper: `bg-surface border border-border rounded-2xl p-6 shadow-card`
- Icon container: `w-10 h-10 bg-accent-light rounded-xl flex items-center justify-center mb-4` (color varies by feature)
- Icon: `w-5 h-5 text-accent` (or appropriate color token)
- Title: `text-base font-semibold text-text-primary mb-2`
- Body: `text-sm text-text-secondary leading-relaxed`

---

### FAQ Accordion
**Used in:** `resources/views/central/landing.blade.php`
- Wrapper: Alpine.js `x-data="{ open: null }"`
- Item: `bg-surface border border-border rounded-xl overflow-hidden`
- Toggle button: `w-full flex items-center justify-between px-5 py-4 text-left`
- Question text: `text-sm font-medium text-text-primary`
- Chevron: `w-4 h-4 text-text-muted` with `:class="open === i ? 'rotate-180' : ''"`
- Answer: `x-show` with enter transition, `px-5 pb-4 text-sm text-text-secondary leading-relaxed`
- Schema markup: `itemscope itemprop` on FAQ container and items

---

### Form Input
**Used in:** `resources/views/central/register-school.blade.php`
- Base: `w-full px-3 py-2 bg-surface border rounded-md text-sm text-text-primary placeholder-text-muted focus:outline-none focus:ring-1 transition-colors`
- Default border: `border-border focus:ring-accent focus:border-accent`
- Error state: `border-error focus:ring-error focus:border-error`
- Label: `block text-sm font-medium text-text-dark mb-1.5`
- Error message: `mt-1 text-xs text-error`

---

### Subdomain Input with Preview
**Used in:** `resources/views/central/register-school.blade.php`
- Alpine.js: `x-data="{ subdomain: '', get preview() { return this.subdomain.trim().toLowerCase().replace(/[^a-z0-9-]/g, '') || 'yourschool'; } }"`
- Input bound: `x-model="subdomain"`
- Input wrapper: `flex items-stretch rounded-md overflow-hidden border` (border state changes on error)
- Suffix block: `flex items-center bg-surface-secondary border-l border-border px-3 text-xs text-text-muted`
- Preview text: `text-xs text-text-muted` with `<span class="font-medium text-accent" x-text="preview + '.schoolflow.com'">`

---

### Pricing Plan Card
**Used in:** `resources/views/central/pricing.blade.php`
- Base: `bg-surface border border-border rounded-2xl p-6 shadow-card flex flex-col`
- Highlighted (most popular): `bg-surface border-2 border-accent rounded-2xl p-6 shadow-card flex flex-col relative`
- Popular badge: `absolute -top-3 left-1/2 -translate-x-1/2` · `bg-accent text-accent-foreground text-xs font-semibold px-3 py-1 rounded-full`
- Plan label: `text-xs font-semibold uppercase tracking-wider text-text-muted mb-3`
- Price: `text-4xl font-bold text-text-primary`
- Feature check (basic): `w-4 h-4 rounded-full bg-success-lightest` with green SVG check
- Feature check (accent): `w-4 h-4 rounded-full bg-accent-light` with purple SVG check

---

### Feature Comparison Table
**Used in:** `resources/views/central/pricing.blade.php`
- Wrapper: `bg-surface border border-border rounded-2xl overflow-hidden shadow-card`
- Header row: `grid grid-cols-4` · `border-b border-border`
- Highlighted column: `bg-accent-muted`
- Row: `grid grid-cols-4` · `border-b border-border last:border-b-0`
- Alternating row bg: `{{ $i % 2 === 0 ? '' : 'bg-surface-secondary' }}`
- Check icon: `w-4 h-4 text-success mx-auto`
- Empty cell: `text-border-muted` dash `—`

---

### Dashboard Preview Mockup
**Used in:** `resources/views/central/landing.blade.php`
- Outer: `bg-surface rounded-2xl border border-border shadow-card overflow-hidden`
- Browser chrome: `bg-surface-secondary border-b border-border px-4 py-3 flex items-center gap-2`
- Traffic light dots: `w-3 h-3 rounded-full bg-error/60`, `bg-warning/60`, `bg-success/60`
- URL bar: `flex-1 mx-4 bg-surface border border-border rounded-md px-3 py-1 text-xs text-text-muted text-center`
- Sidebar: `bg-surface border-r border-border w-52 p-4`
- Stat card: `bg-surface border border-border rounded-xl p-3`
- Chart bar: inline `style` with gradient/color for mock bars

---

### Alert / Flash Message
**Used in:** `resources/views/central/register-school.blade.php`
- Success: `bg-success-lightest border border-success-light text-success-foreground text-sm px-4 py-3 rounded-xl flex items-start gap-3`
- Error: `bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl flex items-start gap-3`

---

### Section Eyebrow Badge
**Used in:** `resources/views/central/landing.blade.php` — Hero
- Class: `inline-flex items-center gap-2 bg-accent-light text-accent px-3 py-1 rounded-full text-xs font-semibold`

---

### Trust Signals Row
**Used in:** `resources/views/central/register-school.blade.php`
- Wrapper: `flex flex-col sm:flex-row items-center justify-center gap-6 text-xs text-text-muted`
- Item: `flex items-center gap-1.5` with success-colored SVG icon

---

### Tenant Layout (Sidebar + Topbar)
**File:** `resources/views/layouts/tenant.blade.php`
**Description:** Root layout for all tenant (school subdomain) pages. Fixed sidebar + topbar. Exposes `@stack('head')` (after `@vite`) and `@stack('scripts')` (before `</body>`).
- Outer: `flex h-screen overflow-hidden` on body wrapper
- Sidebar: `w-65 shrink-0 bg-surface border-r border-border flex flex-col sticky top-0 h-screen`
- Sidebar logo area: `flex items-center gap-2.5 px-4 h-16 border-b border-border shrink-0`
- Sidebar nav area: `flex-1 overflow-y-auto p-3`
- Sidebar user footer: `border-t border-border p-3 shrink-0`
- Main area: `flex-1 flex flex-col min-w-0 overflow-y-auto`
- Topbar: `h-16 shrink-0 bg-surface border-b border-border flex items-center px-6 gap-4`
- Page content: `flex-1 p-8`
- Yields: `title`, `page-title`, `content`; Stacks: `head`, `scripts`

---

### Sidebar Nav (Permission-Based)
**File:** `resources/views/components/sidebar-nav.blade.php`
**Description:** Permission-gated navigation. `null` permission = visible to all authenticated users. Uses `$user->can($permission)` — automatically respects custom roles.
- Nav wrapper: `flex flex-col gap-0.5`
- Active link: `flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium bg-accent-muted text-accent transition-colors`
- Inactive link: `text-text-dark hover:bg-surface-secondary hover:text-text-primary`
- Disabled span (route not registered): `text-text-muted cursor-not-allowed opacity-50`
- Icon: `w-4.5 h-4.5 shrink-0` SVG

---

### Dashboard Stat Card
**Used in:** `resources/views/tenant/dashboard.blade.php`
- Wrapper: `bg-surface border border-border rounded-2xl p-5 shadow-card`
- Icon container: `w-8 h-8 rounded-lg flex items-center justify-center shrink-0` (color varies per stat)
- Stat number: `text-[30px] font-semibold text-text-primary leading-none`
- Trend badge: `text-xs font-medium bg-success-lightest text-success-darker px-2 py-0.5 rounded-sm`
- Subtitle: `text-xs text-text-muted`

---

### Dashboard Setup Checklist Card
**Used in:** `resources/views/tenant/dashboard.blade.php` (school_admin view only)
- Wrapper: `bg-surface border border-border rounded-2xl p-6 shadow-card`
- Grid: `grid grid-cols-1 sm:grid-cols-2 gap-3`
- Done item: `flex items-center gap-3 p-3 rounded-xl border border-success-light bg-success-lightest`
- Pending item: `flex items-center gap-3 p-3 rounded-xl border border-border bg-surface-secondary`
- Done icon: `w-5 h-5 rounded-full bg-success` with white checkmark SVG
- Pending circle: `w-5 h-5 rounded-full border-2 border-border-muted bg-surface` with step number text

---

### Recent Activity Card
**Used in:** `resources/views/tenant/dashboard.blade.php`
- Wrapper: `bg-surface border border-border rounded-2xl p-6 shadow-card`
- Activity row: `flex items-start gap-3`
- Dot outer: `w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5` (inline style bg from type)
- Dot inner: `w-2 h-2 rounded-full` (inline style bg from type)
- Activity types → dot colors: attendance/student=#DBEAFE outer/#2563EB inner; fee=#D0FAE5/#00BC7D; announce=#FFF7ED/#FF8904; exam=#CFFAFE/#06B6D4

---

### Dashboard Chart Card (Chart.js)
**Used in:** `resources/views/tenant/dashboard.blade.php`
- Wrapper: `bg-surface border border-border rounded-2xl p-6 shadow-card`
- Alpine component: `x-data x-init="..."` on inner div; `<canvas>` inside a `style="height:180px; position:relative"` wrapper
- Chart.js loaded via CDN `@push('head')` — `https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js`
- Fee line chart: `borderColor: #2563EB`, gradient fill `rgba(37,99,235,0.18)→0`, `tension: 0.4`
- Attendance bar chart: `backgroundColor: #06B6D4`, `borderRadius: 4`
- Grade distribution bar chart: per-bar colors matching grade tokens (A=success, B=info, C=warning, D/F=error)
- All charts: `borderDash: [4,4]` grid lines, `#9CA3AF` axis labels, no legend, legend hidden

---

### Coming Soon Placeholder Page
**File:** `resources/views/tenant/coming-soon.blade.php`
- Extends `layouts.tenant`; accepts `$section` and `$phase` variables
- Center card: `bg-surface border border-border rounded-2xl p-8 shadow-card text-center` with icon, title, phase note, and back-to-dashboard button

---

### Settings Sub-Nav (Tab Bar)
**Used in:** `resources/views/tenant/settings/academic-year.blade.php`, `classes.blade.php`, `subjects.blade.php`
- Wrapper: `flex items-center gap-1 border-b border-border pb-0`
- Active tab: `px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-accent text-accent`
- Inactive tab: `px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors border-transparent text-text-secondary hover:text-text-primary`

---

### Settings CRUD Table Card
**Used in:** `resources/views/tenant/settings/` (academic-year, classes, subjects)
- Card wrapper: `bg-surface border border-border rounded-2xl shadow-card`
- Card header: `flex items-center justify-between px-6 py-4 border-b border-border`
- Table header cell: `text-left px-6 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary`
- Table row: `border-b border-border last:border-b-0 hover:bg-surface-secondary transition-colors`
- Table cell: `px-6 py-4 text-sm`
- Row actions (right-aligned): `flex items-center justify-end gap-2`
- Row action link: `text-xs font-medium text-text-secondary hover:text-text-primary transition-colors px-2 py-1 rounded hover:bg-surface-secondary`
- Row delete action: `text-xs font-medium text-error hover:text-red-700 transition-colors px-2 py-1 rounded hover:bg-error-light`

---

### CRUD Modal (Add / Edit)
**Used in:** `resources/views/tenant/settings/` (academic-year, classes, subjects)
- Overlay: `fixed inset-0 z-50 flex items-center justify-center p-4` with `absolute inset-0 bg-overlay/40` backdrop
- Panel: `relative w-full max-w-md bg-surface rounded-2xl shadow-xl border border-border p-6`
- Header: `flex items-center justify-between mb-5` · title `text-base font-semibold text-text-primary` · close button `p-1.5 rounded-md text-text-muted hover:bg-surface-secondary`
- Transitions: `x-transition:enter="transition ease-out duration-150"` enter-start opacity-0 scale-95 → enter-end opacity-100 scale-100
- Alpine pattern: `x-data="{ showModal: false, mode: 'add', form: {...}, openAdd() {...}, openEdit(data) {...}, close() {...} }"`
- Two `<form>` blocks inside: `x-show="mode === 'add'"` and `x-show="mode === 'edit'"` (edit form uses `:action` binding)

---

### Students List Page
**File:** `resources/views/tenant/students/index.blade.php`
**Description:** Paginated table of all students with filter bar, search, and import modal.
- Page header: `flex items-center justify-between` with total count subtitle `text-xs text-text-muted`
- Filter bar: `flex flex-wrap items-center gap-3` — search input with leading SVG icon, class `<select>`, conditional section `<select>`, clear link, Search button
- Search input wrapper: `relative flex-1 min-w-[200px] max-w-xs` with `absolute left-3 top-1/2 -translate-y-1/2` icon
- Table: reuses Settings CRUD Table Card pattern; Section column conditionally rendered via `$anyClassHasSections`
- Status badge (Active): `bg-success-lightest text-success-foreground`; (Inactive): `bg-surface-secondary text-text-secondary`; (Graduated): `bg-accent-muted text-accent`
- Import modal: standard CRUD Modal pattern with file input styled `file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-accent-muted file:text-accent`
- Alpine: `Alpine.data('studentsIndex', () => ({ showImport: false }))`

---

### Student Form (Create / Edit)
**Files:** `resources/views/tenant/students/create.blade.php`, `resources/views/tenant/students/edit.blade.php`
**Description:** Three-card form layout for add/edit student. Section field conditional via Alpine.
- Cards: Personal Information, Academic Information, Guardian Information — each `bg-surface border border-border rounded-2xl shadow-card p-6 flex flex-col gap-5`
- Section dropdown: `x-show="currentSections.length > 0"` — invisible when selected class has no sections
- Admission number field: `bg-surface-secondary border border-border rounded-md text-sm text-text-muted cursor-not-allowed` (read-only placeholder on create, actual value on edit)
- Breadcrumb: `flex items-center gap-2 text-sm text-text-muted` with chevron SVGs between segments
- Alpine: `Alpine.data('studentForm', (classes, initialClassId, initialSectionId) => ({...}))` — `onClassChange()` sets `currentSections` from classes JSON

---

### Student Profile Page
**File:** `resources/views/tenant/students/show.blade.php`
**Description:** Student profile with header card, four detail cards, and placeholder cards for future phases.
- Profile header card: `flex items-start justify-between gap-4`; avatar `w-14 h-14 rounded-2xl bg-accent-muted` with initial letter `text-xl font-semibold text-accent`
- Detail cards use `<dl>` with `grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4`; `<dt>` styled `text-xs font-medium text-text-muted uppercase tracking-wide mb-1`; `<dd>` styled `text-sm text-text-primary`
- Placeholder cards (Attendance, Exam Results, Fee Status): same card pattern with `text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md` phase badge and centered empty-state icon

---

### Staff List Page
**File:** `resources/views/tenant/staff/index.blade.php`
**Description:** Paginated table of all staff members with search/status filter bar.
- Page header: `flex items-center justify-between` with total count subtitle `text-xs text-text-muted`
- Filter bar: `flex flex-wrap items-center gap-3` — search input with leading SVG icon, status `<select>` (auto-submits on change), clear link, Search button
- Table: reuses Settings CRUD Table Card pattern; first column includes `w-8 h-8 rounded-lg bg-accent-muted` avatar with initial letter
- System role badge: `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-muted text-accent capitalize`
- Status badge (Active): `bg-success-lightest text-success-foreground`; (Inactive): `bg-surface-secondary text-text-secondary`
- Delete confirm: native browser confirm with message noting login account will also be deleted

---

### Staff Form (Create / Edit)
**Files:** `resources/views/tenant/staff/create.blade.php`, `resources/views/tenant/staff/edit.blade.php`
**Description:** Two-card form layout for add/edit staff.
- Card 1 — Personal Information: full_name (col-span-2), role_title + phone (2-col grid), status select
- Card 2 — Login Account: email (col-span-2), password + confirm (2-col grid, create only), new_password + confirm (2-col grid, edit only — optional), system_role select (col-span-2) with link to Manage Roles
- System role select excludes `school_admin`, `student`, `parent` — loads from DB
- On edit: `new_password` / `new_password_confirmation` are optional; blank = no change
- Breadcrumb: `flex items-center gap-2 text-sm text-text-muted` with chevron SVGs

---

### Staff Profile Page
**File:** `resources/views/tenant/staff/show.blade.php`
**Description:** Staff profile with header card, details card, and two Phase 3 placeholder cards.
- Profile header card: same pattern as Student Profile — `w-14 h-14 rounded-2xl bg-accent-muted` avatar, name, role_title subtitle, status + system role badges side by side, Edit/Delete buttons
- Staff Details card: `<dl>` with `grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4`; email is a `mailto:` link in accent color
- Placeholder cards (Assigned Classes & Subjects, Attendance Record): phase badge `text-xs text-text-muted bg-surface-secondary px-2 py-1 rounded-md` + centered empty-state icon + description text

---

### Sections Manager Modal
**Used in:** `resources/views/tenant/settings/classes.blade.php`
- Same overlay/panel pattern as CRUD Modal
- Sections list: `flex flex-col gap-2` with per-section row `flex items-center justify-between px-3 py-2 rounded-lg border border-border bg-surface-secondary`
- Delete button per section: `p-1 rounded text-text-muted hover:text-error hover:bg-error-light transition-colors`
- Add form at bottom: `flex gap-2 border-t border-border pt-4` with text input + primary button
- Alpine: `Alpine.data('classesPage', (classes, openClassId) => ({...}))` defined in `@push('scripts')`
- Re-opens on page load when `?class_open={id}` query param is present (SectionController redirects with this param after add/delete)

---

### Attendance Index Page (Student Daily Attendance)
**File:** `resources/views/tenant/attendance/index.blade.php`
**Description:** Class+date filter, student list with P/A/L quick-action toggle buttons, "Mark all present" bulk action, save via form POST.
- Filter card: `bg-surface border border-border rounded-2xl shadow-card p-5` with `flex flex-wrap items-end gap-4` form; section select hidden via `x-show="hasSections"` when class has no sections
- Sheet header: `flex items-center justify-between px-6 py-4 border-b border-border` with class/date title, marked count, "Mark all present" button, "Monthly Report" link
- P/A/L buttons (active state): Present → `bg-success-lightest text-success-foreground border-success-light font-semibold`; Absent → `bg-error-light text-error border-error font-semibold`; Late → `bg-warning-light text-warning border-warning font-semibold`
- P/A/L buttons (inactive): `bg-surface text-text-secondary border-border` with hover state matching the active colors
- Status badges (read-only for non-editors): rounded-full pill with same color tokens as buttons
- Hidden input per row: `<input type="hidden" name="statuses[{uuid}]" :value="statuses['{uuid}'] ?? ''">` — Alpine-bound value submitted in form POST
- Alpine: `attendancePage(classes, classId, sectionId)` manages filter state; `attendanceSheet(students, existingRecords)` manages P/A/L toggle state and "mark all present"
- Toggle behavior: clicking an already-active button deselects it (sets null); existing DB records not deleted on save

---

### Attendance Monthly Report Page
**File:** `resources/views/tenant/attendance/report.blade.php`
**Description:** Per-student monthly attendance table with summary stat cards and prev/next month navigation.
- Filter card: class, section (conditional), student dropdown (populated from selected class), month input type
- Summary grid: `grid grid-cols-2 sm:grid-cols-4 gap-4` with four stat cards (present/absent/late/unmarked); stat value uses color-coded text (`text-success-foreground`, `text-error`, `text-warning`, `text-text-muted`)
- Monthly table: date + day + status badge per past day; "Not marked" plain text for unmarked days
- Prev/next navigation: chevron icon links adjusting `month` query param; next link hidden if current month
- Alpine: `reportFilter(classes, classId, sectionId)` manages conditional section dropdown

---

### Staff Attendance Page
**File:** `resources/views/tenant/attendance/staff.blade.php`
**Description:** Date selector + staff list with P/A/L quick-action toggle buttons. Same pattern as student attendance, separate page for school admin to mark staff.
- Date filter card: single date input + Load button (GET form)
- Sheet header: date title, marked count, "Mark all present" button
- Reuses identical P/A/L button classes and Alpine pattern from student attendance index
- Alpine: `staffAttendanceSheet(staff, existingRecords)` — same structure as `attendanceSheet` but keyed to staff UUIDs

---

### Timetable Grid Page (Class View)
**File:** `resources/views/tenant/timetable/index.blade.php`
**Description:** Days-as-rows × periods-as-columns grid for a selected class/section. Inline cell editing via Alpine modal + fetch(). Conflict warnings shown as dismissible banner.
- Filter card: class `<select>`, conditional section `<select>` (`x-show="hasSections"`), Load button — same pattern as attendance filter
- Grid wrapper: `overflow-x-auto` → table `min-w-full` with `style="min-width:900px"` — horizontal scroll on mobile
- Period header row: `bg-surface-secondary` · cells `text-center px-3 py-3 text-xs font-medium uppercase tracking-wide text-text-secondary min-w-[120px]`
- Day label cell: day abbreviation `text-sm font-semibold text-text-primary` + full day suffix `text-xs text-text-muted`
- Filled cell: `rounded-xl border border-accent-light bg-accent-muted p-2.5 min-h-[68px]` · subject `text-xs font-semibold text-accent` · teacher `text-xs text-text-secondary` · clear button `opacity-0 group-hover:opacity-100 hover:text-error`
- Empty cell: `rounded-xl border border-dashed border-border min-h-[68px]` · `cursor-pointer hover:border-accent hover:bg-accent-muted`
- Banner (conflict/success): `bg-warning-light border-warning text-warning` or `bg-success-lightest border-success-light text-success-foreground` · dismissible via `banner.message = ''`
- Edit modal: standard CRUD Modal panel pattern via `Alpine.store('timetableModal')` — store pattern used so modal DOM (outside grid `x-data`) can access grid ref via `_gridRef`
- Alpine: `timetableFilter(classes, classId, sectionId)` for filter state; `timetableGrid(entries, subjects, staff, classId, sectionId)` for grid state; `Alpine.store('timetableModal')` for modal state + save logic

---

### Teacher Personal Timetable Page
**File:** `resources/views/tenant/timetable/teacher.blade.php`
**Description:** Read-only timetable grid showing a teacher's assigned periods across all classes. Admins see a staff selector; non-admins auto-load their own schedule.
- Admin selector: standard filter card with staff `<select>` + Load button
- Grid: same `overflow-x-auto` + `min-w-[900px]` structure as class grid
- Filled cell: `rounded-xl border border-success-light bg-success-lightest p-2.5 min-h-[68px]` · subject `text-xs font-semibold text-success-foreground` · class/section `text-xs text-text-secondary`
- Empty cell: same dashed border + "—" as class grid (no click handler — read-only)
- Footer: `text-xs text-text-muted` period + class count summary
- Teacher avatar: `w-9 h-9 rounded-xl bg-accent-muted` with initial letter `text-sm font-semibold text-accent`
- No Alpine JS needed — purely server-rendered (page reload for teacher change)

---

### Roles & Permissions Settings Page
**File:** `resources/views/tenant/settings/roles.blade.php`
**Description:** Lists all roles (fixed + custom) in a CRUD table. School Admin only. Modal with permission matrix for create/edit custom roles.
- Settings sub-nav: 4 tabs — Academic Year · Classes & Sections · Subjects · Roles & Permissions. All three other settings pages updated to include this tab.
- Roles table: reuses Settings CRUD Table Card pattern; role icon `w-8 h-8 rounded-lg` with person SVG; Fixed badge `bg-surface-secondary text-text-secondary`; Custom badge `bg-accent-muted text-accent`
- Info card: `bg-accent-muted border border-accent-light rounded-2xl p-5` with accent icon + copy linking to Add Staff form
- Create/Edit modal: single `<form>` with `:action` Alpine binding. `<input type="hidden" name="_method" :value="mode === 'edit' ? 'PUT' : ''">` — empty string is PHP-falsy so Laravel skips method override on create
- Modal panel: `max-w-2xl` (wider than standard `max-w-md`), `max-height: 90vh` with `overflow-y-auto` scrollable body
- Permission matrix: per-module rows (`border border-border rounded-xl overflow-hidden`); module header `bg-surface-secondary px-4 py-2.5` with module name (uppercase, tracking-wide) + "Select all / Deselect all" toggle button
- Checkboxes: `x-model="form.permissions"` (Alpine array binding) — value is the permission string (e.g. `students.view`). `name="permissions[]"` ensures PHP receives array on submit
- Alpine: `openAdd()` resets `form.permissions = []`; `openEdit(role)` sets `form.permissions = role.permissions` (array of permission name strings passed via `Js::from()`); `isModuleAllChecked(perms)`, `toggleModule(perms)` for per-module select-all

---

### Exams List Page
**File:** `resources/views/tenant/exams/index.blade.php`
**Description:** Table of all exams with status badges. Add/Edit via shared Alpine modal. Delete via confirm dialog.
- Page header: `flex items-center justify-between` with "Enter Marks" secondary button and "Add Exam" primary button (permission-gated)
- Exam icon cell: `w-8 h-8 rounded-lg bg-accent-muted` with document SVG `w-4 h-4 text-accent`
- Status badges: Upcoming → `bg-accent-muted text-accent`; Ongoing → `bg-info-light text-info-foreground`; Completed → `bg-surface-secondary text-text-secondary`; Published → `bg-success-lightest text-success-foreground`
- Date range column hidden below `lg:`, Academic Year column hidden below `md:`
- Add/Edit modal: `max-w-lg` standard CRUD Modal panel; uses `_form.blade.php` partial inside both add and edit `<form>` blocks; edit `<form>` uses `:action` binding with template literal; Alpine `examsPage(academicYears)` — `openAdd()` resets form; `openEdit(exam)` spreads exam data into form
- Shared form partial `_form.blade.php`: name (full width), term + status (2-col `md:grid-cols-2`), academic year select (populated via `x-for` from `academicYears` JSON), start/end date (2-col `md:grid-cols-2`)

---

### Marks Entry Page
**File:** `resources/views/tenant/exams/marks.blade.php`
**Description:** Filter bar (exam, class, conditional section, subject) + marks table per class/subject. Teacher mode filters dropdowns to assigned classes/subjects. Live grade + progress bar via Alpine.
- Filter card: `flex flex-wrap items-end gap-4` with 4 selects + Load button; section select `x-show="hasSections"`; teacher-mode "no assignments" hint text below form
- Marks table: wrapped in `overflow-x-auto` → table `min-width:600px`; columns: #, Student (avatar + name), Adm. No. (hidden below `sm:`), Marks (number input `w-24 text-center` step 0.5), Grade (badge), Progress bar
- Marks input: `w-24 px-3 py-1.5 border border-border rounded-md text-sm text-center`; `@input="updateMarks(...)"`
- Grade badge (editable mode): `:class="gradeClass(id)"` Alpine binding — A→success, B→info, C→warning (inline `#FFF7ED` bg), D/F→error
- Progress bar: `w-full h-1 rounded-full bg-border-light` with inner div `:style` for width + color from Alpine `progressWidth()` / `progressColor()`
- "Clear All" button: `px-3 py-1.5 text-xs font-medium text-text-secondary border border-border rounded-md`
- Alpine components: `marksFilter(classes, subjects, assignments, role, examId, classId, sectionId, subjectId)` — `availableClasses` / `availableSubjects` computed from teacher assignments; `marksSheet(students)` — `marks` map, `computeGrade()`, `gradeLabel()`, `gradeClass()`, `progressWidth()`, `progressColor()`, `enteredCount`
- Read-only fallback: static grade badges and server-rendered progress bar when `exams.edit` not granted

---

### Report Card Preview Page
**File:** `resources/views/tenant/exams/report-card.blade.php`
**Description:** Filter bar + inline report card for one student/exam. Admins select exam → class → section (conditional) → student. Students/parents see only published exams; student is auto-resolved from user_id.
- Filter card: `bg-surface border border-border rounded-2xl shadow-card p-5` · `flex flex-wrap items-end gap-4` · same select + label pattern as attendance/marks filter bars
- Section select: `x-show="hasSections"` — hidden when class has no sections
- Student select for admin: populated server-side from `$students` collection (class-filtered)
- Access error banner: `bg-warning-light border border-warning text-warning` with triangle SVG — shown when exam is unpublished and student/parent tries to access
- Report card card: `bg-surface border border-border rounded-2xl shadow-card overflow-hidden`
- Card header: `flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-6 py-5 border-b border-border` — avatar initial `w-14 h-14 rounded-2xl bg-accent-muted` + student name/admission/class badge + exam badge + Download PDF + Print buttons
- Results table: `overflow-x-auto` wrapper → `table w-full min-width:520px` — columns: #, Subject, Marks (/100), Grade (circular badge `w-8 h-8 rounded-full`), Remark (hidden below `sm:`), Progress bar (hidden below `md:`)
- Grade circles: `inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-semibold` · A→success-lightest/success-foreground, B→info-light/info-foreground, C→warning-light/warning, D/F→error-light/error
- Progress bar: `w-full h-1.5 rounded-full bg-border-light` inner `div` with inline `width` + `background-color` from service (matches ui-rules.md grade fill colors)
- Overall average tfoot row: `border-t-2 border-border bg-surface-secondary` · bold values, same grade circle + progress bar
- Grading scale key footer: `flex flex-wrap items-center gap-3 px-6 py-4 border-t border-border bg-surface-secondary` · per-band `inline-flex items-center gap-1.5 text-xs text-text-secondary`
- Alpine: `reportCardPage(classes, initialStudents, initialClassId, initialSectionId, initialStudentId)` — `onClassChange()` rebuilds `currentSections`, `hasSections`, resets student + section

---

### Fees Admin Page (Fee Collection + Fee Structure tabs)
**File:** `resources/views/tenant/fees/index.blade.php`
**Description:** Two-tab admin/accountant view. "Fee Collection" tab: student search → fee items with status badges and "Record Payment" modal. "Fee Structure" tab: CRUD table for fee items per class/term. Tab state driven by `?tab=` URL param, managed by `feesAdminPage` Alpine component.
- Tab bar: same Settings Sub-Nav pattern — `border-b border-border pb-0`, active tab `border-accent text-accent`, inactive `border-transparent text-text-secondary`
- Fee Collection search card: `flex flex-wrap items-end gap-4` with text search (leading SVG icon, `pl-9`), academic year select, term select, Search primary button, Clear secondary link
- Search results list: `bg-surface border border-border rounded-2xl shadow-card` → `divide-y divide-border` list items; each item `flex items-center gap-4 px-6 py-3.5` with avatar initial + name/admission row + chevron SVG
- Student info bar (after selecting): `flex items-center gap-4 p-5` card; summary stats strip `hidden sm:flex items-center gap-6` (Total Owed / Paid / Outstanding); mobile fallback `sm:hidden grid grid-cols-3 gap-3` mini stat cards
- Fee items table: `overflow-x-auto` wrapper → `min-width: 640px`; columns: Fee Item (with due date subtitle), Term/Year (`hidden md:table-cell`), Amount, Paid, Outstanding, Status badge, Record Payment button; outstanding column `text-error` when > 0
- Status badges: Paid → `bg-success-lightest text-success-foreground`; Partial → `bg-warning-light text-warning`; Unpaid/Overdue → `bg-error-light text-error`
- Record Payment modal: `max-w-md` standard CRUD Modal; context info block `bg-surface-secondary rounded-xl px-4 py-3 mb-5` showing student name/fee item/outstanding; amount number input with `:max="form.outstanding"` Alpine binding; defaults to full outstanding amount on open
- Fee Structure tab: header `flex items-center justify-between` + CRUD table (same pattern as former Feature 14) + Add/Edit modal (`max-w-lg`)
- Alpine components: `feesAdminPage(classes, academicYears)` — `activeTab`, `initTab(tab)`; `feeStructureTab(classes, academicYears)` — CRUD modal state; `paymentModal()` — Record Payment modal with `open(data)` spreading row data + defaulting `amount` to outstanding

---

### Student/Parent My Fees Page
**File:** `resources/views/tenant/fees/my-fees.blade.php`
**Description:** Read-only fee view for students and parents. Auto-resolved student record from `user_id`. Shows current academic year fees by default.
- Student header card: avatar `w-12 h-12 rounded-2xl bg-accent-muted` + name/admission/class; three-stat strip `grid grid-cols-3 gap-3 mt-5 pt-5 border-t border-border` (Total Owed / Paid / Outstanding with color-coded values)
- Fee items table: `overflow-x-auto` wrapper → `min-width: 620px`; columns: Fee Item (with due date subtitle), Term (`hidden sm:table-cell`), Amount, Paid (`hidden md:table-cell`), Outstanding (`hidden md:table-cell`), Status badge, Pay Now action column
- "Pay Now" action: form POST to `/paystack/checkout` with hidden `student_id` + `fee_structure_id`; visible only when `outstanding > 0` and status is `unpaid|partial|overdue`; button `text-accent hover:text-accent-dark hover:bg-accent-muted`
- Totals tfoot: `border-t-2 border-border bg-surface-secondary` matching summary stats
- Outstanding info banner: `bg-accent-muted border border-accent-light rounded-2xl px-5 py-4` with info SVG — shown only when `$totalOutstanding > 0`; instructs student to click "Pay Now" to pay online via Paystack
- No-student empty state: centered icon + explanatory message for accounts not linked to a student record
- Status badge classes: same as admin view (Paid=success, Partial=warning, Unpaid/Overdue=error)

---

### School Profile Settings Page
**File:** `resources/views/tenant/settings/school-profile.blade.php`
**Description:** Settings > School Profile tab. Upsert form for school branding (logo, name, description) and contact info. Single-row table (`school_profile`). School Admin only.
- Settings sub-nav: 5 tabs — Academic Year · Classes & Sections · Subjects · Roles & Permissions · School Profile. All 5 settings pages use `overflow-x-auto` + `whitespace-nowrap` on tabs. "School Profile" is the 5th tab (active: `border-accent text-accent`, inactive: `border-transparent text-text-secondary`).
- Logo upload: `w-20 h-20 rounded-xl border-2 border-border` preview container; Alpine `x-data="{ previewUrl: ... }"` with `URL.createObjectURL` on file input `@change`; hidden `<input type="file" name="logo" accept="image/*">` triggered by a visible styled `<label>`. Gradient icon fallback when no logo uploaded.
- Form: `method="POST" enctype="multipart/form-data"`. Fields: school_name (required, max 150), short_description (textarea rows=3, max 500), address (full-width in 2-col grid `sm:col-span-2`), phone + email (side-by-side in `sm:grid-cols-2`), website (`sm:col-span-2`). All fields show `old()` fallback + `@error()` validation messages.
- Controller: `SchoolProfile::first() ?? new SchoolProfile()` upsert pattern. Logo stored at `logos/{tenantId}/logo.{ext}` on `public` disk. Old logo deleted on replace.
- ViewComposer in `AppServiceProvider::boot()`: targets `layouts.tenant` + `tenant.auth.login`. Checks `tenancy()->initialized` before DB query. Injects `$schoolProfile` (null-safe).

---

### Report Card PDF Template
**File:** `resources/views/tenant/exams/report-card-pdf.blade.php`
**Description:** Self-contained dompdf HTML template (A4 portrait). Inline CSS only — no external resources. DejaVu Sans font.
- School logo (if set): `<img src="{{ $logoBase64 }}">` as base64 data URI (44×44px, `object-fit:contain`) above school name. Loaded from `public` disk in `ReportCardService::generatePdf()` — dompdf cannot fetch external URLs.
- School name: `$schoolProfile?->school_name ?? tenant('name') ?? 'School'` — uses school profile if set, falls back to tenant name.
- School name header + right-aligned "REPORT CARD" title, separated by a `border-bottom: 2px solid #2563eb`
- Student info grid: table-layout with alternating `#f9fafb`/`#ffffff` row bg; `info-label` at 9px uppercase + `info-value` at 11px bold
- Results table: `border-collapse: collapse` · blue header row (`background: #2563eb; color: #ffffff`) · alternating even-row `#f9fafb` · blue highlight tfoot for average row
- Grade badge: `display:inline-block; width:22px; height:22px; border-radius:50%` · per-grade CSS classes `.grade-A/B/C/D/F` with matching bg/color tokens
- Progress bar: `.bar-track` `width:80px; height:5px; background:#e7eaf3` with inner `.bar-fill` using `width:%` + `background-color` inline style
- Grading scale table at bottom of page
- Three-column signature area (Class Teacher / Head Teacher / School Stamp) via `display:table` layout
- Watermark footer: `font-size:9px; color:#99a1af` — "Generated by SchoolFlow · {date/time}"

---

### Announcements Notice Board Page
**File:** `resources/views/tenant/announcements/index.blade.php`
**Description:** Card-per-announcement list (not a table — body text needs vertical space). Add/Edit via shared Alpine modal. All authenticated users can read; write gated by `announcements.create/edit/delete`. Dashboard Recent Activity widget wired to real data from this table.
- Page header: `flex items-center justify-between` with count subtitle `text-xs text-text-muted` + "Add Announcement" primary button (gated by `@can('announcements.create')`)
- Announcement card: `bg-surface border border-border rounded-2xl shadow-card overflow-hidden` — card header `flex items-start justify-between gap-4 px-6 py-4 border-b border-border`; icon `w-8 h-8 rounded-lg bg-warning-light` with announcement SVG `text-warning`
- Public badge: `bg-success-lightest text-success-foreground` rounded-full with checkmark SVG; Staff Only: `bg-surface-secondary text-text-secondary` rounded-full
- Card body: `px-6 py-5` — body text `text-sm text-text-primary leading-relaxed whitespace-pre-line`; per-card Alpine `x-data="{ expanded: false }"` with "Read more / Show less" toggle on bodies > 240 chars
- Edit/Delete actions: `p-1.5 rounded-md text-text-muted hover:text-text-primary hover:bg-surface-secondary`; delete hover: `hover:text-error hover:bg-error-light`; delete uses form POST with `@method('DELETE')` + `onclick="return confirm(...)"` native dialog
- Empty state: `flex flex-col items-center justify-center py-16` with icon `w-12 h-12 rounded-xl bg-warning-light` + "Post First Announcement" CTA
- Add/Edit modal: standard CRUD Modal pattern — `fixed inset-0 z-50`, `max-w-lg` panel, `x-transition` transitions; dual `<form>` blocks (`x-show="mode === 'add'"` / `x-show="mode === 'edit'"`) inside single modal; edit form uses `:action` template literal binding
- Modal fields: title (text, max:150), body (textarea rows=6 resize-y, max:5000), `is_public` checkbox with `<input type="hidden" name="is_public" value="0">` sibling for unchecked-false PHP handling
- Alpine: `Alpine.data('announcementsPage', (announcements) => ({ showModal, mode, announcements, form: {id, title, body, is_public}, openAdd(), openEdit(data), close() }))` defined in `@push('scripts')`

---

### School Public Page
**File:** `resources/views/tenant/public-page.blade.php`
**Description:** Standalone self-contained HTML page (no sidebar/tenant layout). Unauthenticated, tenant-scoped via subdomain. Per-tenant `<title>`, meta description, and OpenGraph tags. Fully indexable (`meta robots: index, follow`).
- Outer: standalone `<!DOCTYPE html>` with Vite assets + Google Fonts Inter — does NOT extend any layout
- Navbar: `bg-surface border-b border-border sticky top-0 z-50` · inner `max-w-4xl mx-auto flex items-center justify-between h-16 px-4 lg:px-8` · logo (school `<img>` or gradient icon) + school name left · "Login" primary button right (`route('tenant.login')`)
- Hero card: `bg-surface border border-border rounded-2xl shadow-card p-8 flex flex-col items-center gap-5 text-center` · logo `w-24 h-24 rounded-2xl object-contain border border-border` (or `w-20 h-20 rounded-2xl` gradient icon fallback) · `<h1>` school name `text-2xl lg:text-3xl font-bold text-text-primary` · short_description `text-sm lg:text-base text-text-secondary` · contact strip: `flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-text-muted` with SVG icons + `tel:`/`mailto:` links
- Announcements section: `<h2 class="text-base font-semibold text-text-primary mb-4">` · per-announcement `<article class="bg-surface border border-border rounded-2xl shadow-card overflow-hidden">` with card header (icon `w-8 h-8 rounded-lg bg-warning-light text-warning` + title + date) + card body (body text `whitespace-pre-line` truncated at 300 chars via `Str::limit`) · empty state `py-12 flex flex-col items-center` with warning-light icon
- Contact card: `bg-surface border border-border rounded-2xl shadow-card overflow-hidden` · card header `px-6 py-4 border-b border-border` · `<dl class="px-6 py-5 flex flex-col gap-5">` · each row `flex items-start gap-3`: icon `w-8 h-8 rounded-lg bg-accent-muted text-accent` + `<dt>` `text-xs font-medium text-text-muted uppercase tracking-wide` + `<dd>` `text-sm text-text-primary` (email/website/phone are clickable links) · conditionally rendered if any contact field is set
- Footer: `border-t border-border mt-4 py-8` · `max-w-4xl mx-auto px-4 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2` · `© {year} {school_name}` + "Powered by SchoolFlow"
- SEO: `<title>` = `school_name` (unique per tenant) · `<meta name="description">` = `short_description` or auto-built from name + address · `<meta name="robots" content="index, follow">` · `og:type`, `og:url`, `og:title`, `og:description`, `og:image` (logo URL) · Twitter Card (`summary_large_image` with logo, `summary` without)
- No Alpine.js used — purely server-rendered, no interactive state

---

### Reports Page (Attendance & Fee Collection)
**File:** `resources/views/tenant/reports/index.blade.php`
**Description:** Two-tab reports page. Attendance Report tab: filter by class, optional section, date range → inline table + Export PDF/Excel. Fee Collection tab: filter by term → inline table with collected vs outstanding + Export PDF/Excel. Access gated by `permission:reports.view`.
- Tab bar: same Settings Sub-Nav pattern — `border-b border-border pb-0`, active `border-accent text-accent`, inactive `border-transparent text-text-secondary`. Uses Alpine buttons with `@click="activeTab = '...'"` rather than anchors (preserves form state on tab switch).
- Filter card: `bg-surface border border-border rounded-2xl shadow-card p-5` with `flex flex-wrap items-end gap-4`; section select `x-show="hasSections"` conditional on selected class
- Results card header: `flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-6 py-4 border-b border-border` — left: class/term name + subtitle; right: Export PDF + Export Excel anchor buttons
- Export buttons: `inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-surface border border-border text-text-primary rounded-md hover:bg-surface-secondary transition-colors` — PDF icon `text-error`, Excel icon `text-success-foreground`
- Export links forward filter params via `?{{ http_build_query([...]) }}` query string to the respective export routes
- Attendance results table: `overflow-x-auto` → `min-width:600px`; % Present column shows inline progress bar `h-1.5 rounded-full` + numeric value; bar color: green ≥80%, orange ≥60%, red <60%
- Fee Collection summary strip: `grid grid-cols-3 gap-0 border-b border-border` with Expected / Collected / Outstanding cells; Outstanding colored `text-error` when > 0, `text-success-foreground` when zero
- Fee Collection table: `overflow-x-auto` → `min-width:700px`; Amount/Student + Students columns hidden below `md:`; Outstanding cell shows "Cleared" (green checkmark) when 0, else red amount; tfoot totals row `border-t-2 border-border bg-surface-secondary`
- Empty state (no filter yet): centered icon + "No report loaded" + instruction text — same pattern across both tabs
- Alpine: `reportsPage(classes, selectedClassId, selectedSectionId, activeTab)` — `activeTab` string; `classId`, `sectionId` string state; `currentSections`, `hasSections` computed from classes JSON; `onClassChange()` resets sectionId

---

### Attendance Report PDF Template
**File:** `resources/views/tenant/reports/attendance-pdf.blade.php`
**Description:** Self-contained dompdf HTML template (A4 portrait). Inline CSS only. DejaVu Sans font.
- Same school header pattern as report-card-pdf.blade.php (logo base64 or text-only, blue underline separator, "ATTENDANCE REPORT" right-aligned)
- Meta block: `display:table` layout with Class/Period/Students/Generated cells — `background:#f9fafb`
- Progress bar per row: `.bar-track` (60px wide, 5px tall) with `.bar-fill` using green/orange/red based on `percent_present` threshold (≥80% green, ≥60% orange, <60% red)
- Status badges: `.badge-present` (`#d1fae5 / #065f46`), `.badge-absent` (`#fee2e2 / #b91c1c`), `.badge-late` (`#fff7ed / #c2410c`)
- Watermark footer: `font-size:9px; color:#99a1af` — "Generated by SchoolFlow · {date/time}"

---

### Fee Collection Report PDF Template
**File:** `resources/views/tenant/reports/fees-pdf.blade.php`
**Description:** Self-contained dompdf HTML template (A4 landscape). Inline CSS only. DejaVu Sans font.
- Same school header pattern; "FEE COLLECTION REPORT" right-aligned
- Summary row: `display:table` 3-cell layout (Expected / Collected / Outstanding) with color-coded values — Outstanding green when 0, red when > 0
- Table columns: Class, Fee Item, Amount/Student, Students, Total Expected, Total Collected, Outstanding — all right-aligned numeric columns
- "Cleared" shown in green instead of 0.00 in the Outstanding column; totals tfoot row with blue top border
- Watermark footer same as attendance PDF

---

### Topbar Account Dropdown
**Used in:** `resources/views/layouts/tenant.blade.php`
**Description:** Avatar button + dropdown with user info, "My Account" link, and sign out. Added alongside notifications placeholder button in the topbar.
- Avatar trigger button: `flex items-center gap-2 p-1 rounded-lg hover:bg-surface-secondary transition-colors`
- Avatar image (when set): `w-8 h-8 rounded-full object-cover border border-border`; served via `/account/avatar` route
- Avatar initials fallback: `w-8 h-8 rounded-full bg-accent-muted` with `text-xs font-semibold text-accent` initial letter
- Chevron: `w-3.5 h-3.5 text-text-muted` SVG, no rotation on open (static chevron-down)
- Dropdown panel: `absolute right-0 top-full mt-2 w-56 bg-surface border border-border rounded-xl shadow-xl z-50 overflow-hidden`
- User info block: `px-4 py-3 border-b border-border` — name `text-sm font-medium text-text-primary truncate` + email `text-xs text-text-muted truncate`
- Menu link: `flex items-center gap-2.5 px-4 py-2 text-sm text-text-dark hover:bg-surface-secondary hover:text-text-primary transition-colors` with `w-4 h-4 text-text-muted` icon
- Sign out form: same link class on `<button type="submit">` inside POST form; separated by `border-t border-border` divider
- Alpine: `x-data="{ open: false }" @click.outside="open = false"` on wrapper; `@click="open = !open"` on trigger; `x-transition` enter/leave scale-95↔scale-100 on panel

---

### Audit Log Page (Settings)
**File:** `resources/views/tenant/settings/audit-log.blade.php`
**Description:** 6th tab in the Settings sub-nav. Read-only activity feed with filter bar and paginated table. No Alpine component needed — server-side filtering via GET form.
- Settings sub-nav: same pattern as other settings views; "Audit Log" tab is the last item with active state `border-accent text-accent`
- Filter card: `bg-surface border border-border rounded-2xl shadow-card p-5` — filter form `flex flex-wrap items-end gap-4`; date inputs `px-3 py-2 bg-surface border border-border rounded-md text-sm`; "Clear" link only shown when `request()->hasAny(['date_from','date_to','causer_id','log_name'])`
- Table card header: `flex items-center justify-between px-6 py-4 border-b border-border` — count subtitle `text-xs text-text-muted mt-0.5`, "Logs kept for 90 days" badge
- Action badge: Created → `bg-success-lightest text-success-foreground`; Deleted → `bg-error-light text-error`; Updated → `bg-accent-muted text-accent`. Applied via `match($event)` in Blade `@php`.
- Summary column: `hidden md:table-cell` — shows up to 2 changed fields as `<span class="text-xs text-text-secondary">` with field name in `font-medium text-text-primary`. Truncated at 40 chars.
- Min table width: `style="min-width: 700px"` wrapped in `overflow-x-auto` — same responsive pattern as other tables
- Empty state: icon in `w-12 h-12 rounded-xl bg-surface-secondary`, same pattern as other list pages

---

### My Account Page
**File:** `resources/views/tenant/account/edit.blade.php`
**Description:** Two-card account settings page available to all roles. Profile card (avatar + name + email + phone) and Password card (current + new + confirm). Both forms are independent POSTs with submit-disable.
- Page wrapper: `flex flex-col gap-6 max-w-2xl`
- Both cards: `bg-surface border border-border rounded-2xl shadow-card` with `px-6 py-4 border-b border-border` header containing title `text-base font-semibold text-text-primary` + subtitle `text-xs text-text-muted mt-0.5`
- Inline error block (per card): `mx-6 mt-4 bg-error-light border border-error text-error text-sm px-4 py-3 rounded-xl` — shows `$errors->only([...fields...])` for that card's fields only
- Avatar upload: `w-20 h-20 rounded-xl border-2 border-border` preview container; Alpine `previewUrl` with `URL.createObjectURL` on `@change`; initials fallback `text-2xl font-semibold text-accent`; Upload button is a `<label>` for hidden `<input type="file">` — same pattern as School Profile logo upload
- Avatar preview persists after save: controller redirects to `/account` passing `$avatarUrl` (stored path served via `/account/avatar`), so `previewUrl` initialises to stored URL on page load
- Name + email + phone inputs: standard Form Input pattern; email uses `Rule::unique('users')->ignore($user->id)` in request
- Password card fields: current_password (autocomplete="current-password"), new_password + new_password_confirmation in `sm:grid-cols-2` grid
- Submit buttons: Alpine `submitting: false` state; `@submit="submitting = true"` on form; `:disabled="submitting"` + loading text on button
- Success flash: handled by the global toast in `layouts/tenant.blade.php` (triggers on `session('success')` or `session('error')`)

---

### Assignments Page (Multi-Role)
**Files:** `resources/views/tenant/assignments/index.blade.php`, `resources/views/tenant/assignments/_form.blade.php`
**Description:** Multi-role page. Students see 3-tab view (Pending/Submitted/Overdue). Teachers/admins see a CRUD table with submissions modal. Parent role gets a simple informational card.
- Student tabs: same Settings Sub-Nav pattern — tab badge with count; Pending tab shows card-per-assignment with inline submit form (toggled); Submitted tab shows grade + feedback; Overdue tab styled with `border-error`
- Submit form (student): `x-data="{ showSubmit: false, submitting: false }"` — textarea + file input (`file:bg-accent-muted file:text-accent`) + submit/cancel buttons; submit-disable pattern
- Teacher/admin table: Settings CRUD Table Card pattern (`min-width: 700px` in `overflow-x-auto`); submission count badge `bg-warning-light text-warning` when ungraded exist; "View Submissions" opens Submissions Modal
- "Create Assignment" button: primary button `bg-accent text-accent-foreground` gated by `@can('assignments.create')`
- Create/Edit modal: standard CRUD Modal pattern `max-w-lg`; `_form.blade.php` partial inside both add + edit forms; edit uses `:action` template literal binding + hidden `_method=PUT`
- Submissions modal: `max-w-2xl`, scrollable body `overflow-y-auto`; inline grading form per submission row (marks number input + feedback text input + Save Grade button); graded badge `bg-accent-muted text-accent`; "Not graded" badge `bg-surface-secondary text-text-secondary`
- Admin filter bar: standard filter card (`bg-surface border border-border rounded-2xl shadow-card p-5`); class + teacher selects; Clear link shown when filter active
- Due date past styling: `text-error` on past due dates + "Past"/"Today" suffix badges
- Dashboard badge (teacher): `bg-warning-light border border-warning rounded-2xl px-5 py-3.5` clickable banner linking to /assignments
- Dashboard badge (student): same pattern with clock icon for "X assignments due within 3 days"
- Alpine component: `assignmentsPage(classes, subjects, sections, staff, canManageAll)` — `showModal`, `showSubmissions`, `mode`, `submitting`, `form`, `currentAssignment`, `currentSections` + `hasSections` getters, `openAdd()`, `openEdit(data)`, `openSubmissions(assignment)`, `close()`

---

### Notification Bell (Topbar)
**File:** `resources/views/layouts/tenant.blade.php`
**Description:** Functional notification bell in the topbar with unread badge, recent-5 dropdown, mark-read, and mark-all-read.
- Wrapper: `relative` div with `x-data="{ open: false }" @click.outside="open = false"`
- Bell button: `relative p-2 rounded-md text-text-muted hover:text-text-primary hover:bg-surface-secondary transition-colors`
- Unread badge (when count > 0): `absolute top-1 right-1 min-w-[16px] h-4 bg-error text-white text-[10px] font-bold rounded-full flex items-center justify-center px-0.5 leading-none` — shows count or "9+" cap
- Dropdown: `absolute right-0 top-full mt-2 w-80 bg-surface border border-border rounded-xl shadow-xl z-50 overflow-hidden` — same structure as User Account Dropdown
- Dropdown header: `flex items-center justify-between px-4 py-3 border-b border-border` — title `text-sm font-semibold text-text-primary` + "Mark all as read" PATCH form (hidden when no unread)
- Notification item: `flex items-start gap-3 px-4 py-3` with `bg-accent-muted/20` tint when unread
- Unread dot: `w-2 h-2 rounded-full bg-accent` (unread) vs transparent placeholder (read) — `mt-1.5`
- Message text: `text-sm font-semibold text-text-primary` (unread) / `text-sm font-medium text-text-dark` (read); relative time `text-xs text-text-muted`
- Mark read button (✓): `text-xs text-text-muted hover:text-text-primary transition-colors`; PATCH form inside dropdown item
- Notification items area: `max-h-72 overflow-y-auto divide-y divide-border`
- Empty state: `py-8 px-4 text-center` with `text-sm text-text-muted`
- Footer: `border-t border-border px-4 py-3` with "View all notifications →" link `text-xs font-medium text-accent hover:text-accent-dark`
- PHP data: `@php` block queries `TenantNotification` (unread count + latest 5); wrapped in `try/catch (\Throwable)` so pages render even if tenancy not yet initialized

---

### Notifications Page
**File:** `resources/views/tenant/notifications/index.blade.php`
**Description:** Paginated list of the logged-in user's notifications. No Alpine needed — server-rendered with PATCH forms for mark-read.
- Page header: `flex items-center justify-between gap-4` — total count subtitle + "Mark all as read" button (hidden when list is empty)
- "Mark all as read": standard secondary button (`px-4 py-2 bg-surface border border-border text-sm font-medium text-text-primary rounded-md hover:bg-surface-secondary`) as a PATCH form
- List card: `bg-surface border border-border rounded-2xl shadow-card divide-y divide-border overflow-hidden`
- Notification row: `flex items-start gap-4 px-5 py-4`; unread rows get `bg-accent-muted/20` tint + `hover:bg-surface-secondary`
- Unread dot: `w-2 h-2 rounded-full bg-accent mt-1.5` (same as topbar bell pattern)
- Message: `text-sm font-semibold text-text-primary` (unread) / `font-medium text-text-dark` (read)
- Body snippet: `text-xs text-text-muted mt-0.5 line-clamp-2` (max 120 chars)
- Relative time: `text-xs text-text-muted mt-1`
- "View →" link: `text-xs font-medium text-accent hover:text-accent-dark` — links to /announcements
- "Mark read" button: `text-xs text-text-muted hover:text-text-primary`; PATCH form; only shown when `read_at` is null
- Empty state: bell icon in `w-12 h-12 rounded-xl bg-accent-muted` on `bg-surface border border-border rounded-2xl shadow-card`
- Pagination: `{{ $notifications->links() }}` only when `hasPages()`

---

### Announcements Audience Section (Modal)
**File:** `resources/views/tenant/announcements/index.blade.php`
**Description:** Audience targeting section added below `is_public` toggle in both add and edit announcement modals. Audience badge shown on each card.
- Section wrapper: `border-t border-border pt-4` with label `text-sm font-medium text-text-dark mb-2`
- Radio options: `flex flex-col gap-2`; each row `flex items-center gap-2.5 cursor-pointer`; radio `w-4 h-4 text-accent focus:ring-accent border-border`; label `text-sm text-text-primary`
- 5 audience options: `all` (All School), `all_students`, `all_parents`, `class` (Specific Class), `role` (Specific Role)
- Class multi-select: `x-show="form.audience_type === 'class'"`, `<select name="audience_ids[]" multiple x-model="form.audience_ids"` — `style="min-height: 100px"` + hint "Hold Ctrl / Cmd to select multiple classes"
- Role multi-select: same pattern for `form.audience_type === 'role'`; option label uses JS `.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())` for display
- Card badge (non-All): `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium` — Students: `bg-info-lightest text-info-foreground`; Parents: `bg-accent-muted text-accent`; Specific Classes: `bg-warning-light text-warning`; Specific Roles: `bg-surface-secondary text-text-secondary`; no badge for `all`
- Modal uses `overflow-y-auto flex-1` scrollable body so audience section doesn't overflow on small screens
- Alpine `announcementsPage(announcements, classes, roles)` — added `classes` (id+name) and `roles` (name strings) params; `form.audience_type` + `form.audience_ids: []` in form state; `openEdit()` restores both fields; `init()` restores from `old()` on validation error
