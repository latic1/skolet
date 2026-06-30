# Code Standards

Implementation rules and conventions for the entire project. The AI agent must follow these in every session without exception. These rules prevent pattern drift across sessions.

---

## Engineering Mindset

The AI agent on this project operates as a senior engineer. This means:

- **Think before implementing** — understand what is being built and why before writing a single line
- **Read context files first** — never assume, always verify against architecture.md and project-overview.md
- **Scope is sacred** — only build what the current feature requires. Never go beyond scope even if it seems helpful
- **Every feature must be testable** — if it cannot be verified immediately after implementation, it is incomplete
- **Clean over clever** — simple readable code that a junior developer can understand is always preferred over clever abstractions
- **One thing at a time** — complete one feature fully before touching the next
- **Failures are expected** — wrap external calls (Paystack, PDF generation, queued jobs) in try/catch, log failures, never let one failure crash everything

---

## PHP & Laravel

- PHP 8.3, `declare(strict_types=1)` at the top of every new class
- Laravel 11 conventions — no deprecated patterns from earlier Laravel versions
- Never use `any`-style loose typing — type all method parameters and return types
- Use constructor property promotion for new classes
- Use Form Request classes for all validation — never validate inline in controllers
- Eloquent over raw DB queries — raw queries only when Eloquent genuinely cannot express the operation, and must be commented why
- Use `final class` for Services and Livewire components unless inheritance is explicitly needed
- All async/queued work uses Laravel Jobs — never `dispatch` a closure for anything that touches external APIs (Paystack, email, PDF)

---

## Multi-Tenancy Rules

- Every tenant-facing route is registered in `routes/tenant.php` and runs through `InitializeTenancyBySubdomain`
- Every central route is registered in `routes/web.php` and never resolves a tenant
- Never write a query that spans multiple tenant databases within a request cycle — if cross-tenant data is needed (e.g. Super Admin dashboard), query the central DB only. The one approved exception is the `SyncTenantStudentCounts` scheduled command (see architecture.md), which runs outside the request cycle, iterates tenants via `Tenant::run()`, and only reads — it writes results back to the central `subscription_plans` table, never to any tenant database.
- Tenant database creation, migration, and teardown only happens via `TenantProvisioningService` — never call `Tenant::create()` directly from a controller without going through the service
- Always read which tenant is active via the resolved `tenant()` helper — never infer tenant from request input (e.g. a posted school ID)

---

## File and Folder Naming

- Folders: kebab-case for views — `report-cards`, `fee-collection`
- Controllers: PascalCase, suffixed `Controller` — `StudentController.php`
- Models: PascalCase, singular — `Student.php`, `FeeStructure.php`
- Livewire components: PascalCase — `DailyAttendance.php`, `MarksEntry.php`
- Services: PascalCase, suffixed `Service` — `PaystackService.php`
- Blade views: kebab-case, matching component/controller — `daily-attendance.blade.php`
- Migrations: standard Laravel timestamped naming, tenant migrations live in `database/migrations/tenant/`
- One class per file — never define multiple classes in one file

---

## Livewire Component Structure

Every Livewire component follows this order:

```php
<?php

declare(strict_types=1);

namespace App\Livewire\Attendance;

use Livewire\Component;
use App\Models\Tenant\Student;
use App\Models\Tenant\Attendance;

final class DailyAttendance extends Component
{
    // 1. Public properties (bound to UI)
    public string $date;
    public ?int $classId = null;

    // 2. Computed properties / lifecycle hooks
    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    // 3. Action methods
    public function markAttendance(int $studentId, string $status): void
    {
        // implementation
    }

    // 4. Render
    public function render()
    {
        return view('livewire.attendance.daily-attendance');
    }
}
```

- Props/public properties first, lifecycle hooks second, action methods third, `render()` last
- No business logic in Livewire components beyond orchestration — delegate to Services for anything non-trivial (report card generation, Paystack calls)
- No inline styles in Blade views — all styling via Tailwind classes using CSS variables from `ui-tokens.md`

---

## Form Submission Rules

These apply to every create/edit form across the app — not just the feature being built when written. Bugs here (double submissions, stale lists, missing previews) recur across every CRUD page if not enforced as a standing rule.

- **Disable the submit button on click, until the response returns.** Use `wire:loading.attr="disabled"` on the submit button plus `wire:target="save"` (or the relevant method name) so the button disables specifically during that action, not for unrelated loading states on the page. Re-enable automatically once Livewire's request completes (success or validation error) — don't manually re-enable in code.
- **Validate uniqueness before insert, with a human-readable error** — for any field that must be unique (academic year name, class name/order, role name, etc.), check for an existing row first via Form Request `unique` validation rules (or a manual check in the Livewire component if the table is tenant-scoped and `unique` needs a `where` clause). Show the error inline next to the field, e.g. "An academic year named '2025/2026' already exists" — never a generic "Something went wrong."
- **Lists refresh after create/edit/delete without a full page reload.** If the list is rendered by the same Livewire component as the form, simply re-render after the save (Livewire does this automatically when public properties change — ensure the list is read from a property or computed method, not cached in `mount()` only). If the list is a separate component, dispatch a Livewire event (e.g. `$this->dispatch('role-created')`) and have the list component listen for it and refresh.
- **File uploads (e.g. logo) show a preview immediately after upload AND after save.** Livewire's `wire:model` on a file input gives an immediate local preview via the temporary upload URL — this must be wired up. After save, the preview must switch to the persisted file's URL (e.g. `Storage::url($schoolProfile->logo_path)`), not remain blank — re-render the component's logo property from the saved model after the save completes, don't rely solely on the temporary upload preview persisting.

---

## CSV / Bulk Import Rules

These apply to every bulk import feature across the app (students, staff). The goal is that a user never has to guess what format the system expects.

**Template download — always required:**
- Every import UI has a prominent "Download Template" button before the upload input — the user downloads the template, fills it in, then uploads. Never present the upload input without the template download alongside it.
- Templates are generated server-side via `maatwebsite/excel` using a dedicated `Import` class that also defines the template headers — the template and the import validator share the same column definition, so they can never drift out of sync.
- Template files are named clearly: `schoolflow-students-import-template.xlsx`, `schoolflow-staff-import-template.xlsx`, etc.
- The first row is always the header row with exact column names. The second row is a locked/greyed example row showing sample data (e.g. "John Doe", "Grade 5", "A") so the user understands the expected format.

**Upload and validation:**
- Accept `.xlsx` and `.csv` — no other formats.
- Validate every row before importing any of them — never partial imports where some rows succeed and others silently fail.
- If any row fails validation, abort the entire import and return a clear error report: which rows failed and why (e.g. "Row 4: Class 'Grade 99' does not exist in the system", "Row 7: Guardian contact is required").
- Show the error report inline on the page — never just a generic "Import failed" message.
- On success, show a summary: "47 students imported successfully."
- Never import duplicate records silently — if a row's key fields (e.g. student admission number, or staff email) already exist, report it as an error in the row-level validation, not a silent skip.

**UX pattern:**
```
[Download Template ↓]    [Choose File] [Import]
```
Always in this order — download first, then upload. Never show just an upload button with no template nearby.

## Controllers

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\Tenant\Student;

final class StudentController extends Controller
{
    public function store(StoreStudentRequest $request): RedirectResponse
    {
        try {
            Student::create($request->validated());

            return redirect()->route('students.index')
                ->with('success', 'Student added successfully.');
        } catch (\Throwable $e) {
            \Log::error('[students.store] ' . $e->getMessage());

            return back()->with('error', 'Could not add student. Please try again.');
        }
    }
}
```

- Every controller method that performs a write wraps it in try/catch
- Errors are logged with the route/action as prefix: `[students.store]`
- User-facing errors are always human readable — never expose raw exception messages
- Always return a redirect with a flash message (`success` / `error`) after a write

---

## Services

```php
<?php

declare(strict_types=1);

namespace App\Services;

final class PaystackService
{
    public function initializeTransaction(array $data): array
    {
        try {
            // call Paystack API
            return ['success' => true, 'data' => $result];
        } catch (\Throwable $e) {
            \Log::error('[PaystackService::initializeTransaction] ' . $e->getMessage());

            return ['success' => false, 'error' => 'Could not initialize payment.'];
        }
    }
}
```

- Every Service method returns an array shape: `['success' => bool, 'data' => mixed, 'error' => ?string]`
- Every Service method has a try/catch
- Services never throw to the caller — always return the error shape
- Services never touch `$_SERVER`, session, or request directly — pass needed data explicitly as parameters

---

## Error Handling

- Never use empty catch blocks — always log or handle
- Log messages always include context prefix: `[ClassName::methodName]`
- User-facing errors must be human readable — never expose raw exception messages or stack traces
- Queued job failures are logged and retried per Laravel's default retry policy — never silently swallowed
- API route (Sanctum, Phase 2) errors return `status: 500` with a generic message — never expose internals

---

## Environment Variables

All environment variables defined in `.env`. Never hardcode any key, URL, or secret anywhere in the codebase.

| Variable                  | Used In                          |
| -------------------------- | ----------------------------------- |
| `APP_URL`                 | Central app base URL                |
| `TENANCY_CENTRAL_DOMAINS`  | stancl/tenancy config               |
| `DB_HOST` / `DB_PORT`     | Central + tenant DB connections     |
| `PAYSTACK_PUBLIC_KEY`     | resources/views/fees, PaystackService |
| `PAYSTACK_SECRET_KEY`     | App\Services\PaystackService        |
| `MAIL_MAILER`, `MAIL_HOST`, etc. | Notifications                |
| `REDIS_HOST`              | Queue connection                    |

---

## Grading Scale Constant

The default grading scale is defined once and never hardcoded elsewhere.

```php
// config/schoolflow.php
return [
    'default_grading_scale' => [
        ['min' => 70, 'max' => 100, 'grade' => 'A'],
        ['min' => 60, 'max' => 69, 'grade' => 'B'],
        ['min' => 50, 'max' => 59, 'grade' => 'C'],
        ['min' => 45, 'max' => 49, 'grade' => 'D'],
        ['min' => 0, 'max' => 44, 'grade' => 'F'],
    ],

    'default_rate_per_student' => 5.00,
];
```

Schools can override this per-tenant later (Phase 2) — for MVP, every tenant uses this default, referenced via `config('schoolflow.default_grading_scale')`, never duplicated inline.

`default_rate_per_student` is the rate (in your billing currency) applied to `subscription_plans.rate_per_student` when a tenant is provisioned (Feature 03). Super Admin can override it per-school afterward (Feature 21) — never hardcode this value anywhere else.

---

## Receipt Number Generation

`receipt_number` (on `fee_payments`) is generated once per payment transaction by `ReceiptService`, never assembled inline in a controller or Livewire component.

```php
// app/Services/ReceiptService.php
public function generateReceiptNumber(): string
{
    $prefix = SchoolProfile::first()->receipt_prefix
        ?? Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', SchoolProfile::first()->school_name), 0, 4));

    $sequence = FeePayment::whereDate('paid_at', today())->distinct('receipt_number')->count() + 1;

    return sprintf('%s%s.%s.%02d', $prefix, now()->format('y'), now()->format('m'), $sequence);
}
```

This produces something like `RASN26.05.01` (prefix + 2-digit year + 2-digit month + daily sequence) — matching the reference receipt format. Every `fee_payments` row created within the same transaction (e.g. all components of a paid bundle) shares the same generated `receipt_number` — generate it once before the loop that creates the rows, never per-row.

Amount-to-words conversion (for "Nine Hundred Eighty Cedis, Zero Pesewas" on receipts) uses a small dedicated helper or an approved package — never hand-rolled inline string concatenation in a Blade view.

---

## Comments

- No comments explaining what the code does — code must be self-explanatory
- Comments only for why — explaining a non-obvious decision (e.g. why a tenant check is placed where it is)
- Service methods calling Paystack may have a brief comment explaining the verification step
- Never leave TODO comments in committed code

---

## Dependencies

Never install a new package without a clear reason. Before installing anything check:

1. Does Laravel already provide this functionality?
2. Does an already-approved package cover this?
3. Is there a simpler native solution?

Approved dependencies for this project:

- `stancl/tenancy` — multi-tenancy
- `laravel/breeze` — auth scaffolding
- `laravel/sanctum` — API tokens (Phase 2)
- `spatie/laravel-permission` — roles & permissions
- `livewire/livewire` — dynamic UI components
- `barryvdh/laravel-dompdf` — PDF generation
- `yabacon/paystack-php` (or official Paystack SDK) — payments
- `maatwebsite/excel` — bulk CSV/Excel import for students/staff
- `tailwindcss` — styling
- Flowbite or Preline (Tailwind component libraries) — UI components
- `lucide` icons (via Blade icon package or static SVGs)

Do not install any other packages without updating this list first.