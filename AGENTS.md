# AGENTS.md

Entry point for any AI agent (Claude Code or otherwise) working on SchoolFlow. Read this first, every session.

---

## Project

SchoolFlow — a multi-tenant SaaS school management system. Laravel 11 + Livewire + Tailwind v4, with `stancl/tenancy` providing per-school isolated databases on subdomains.

---

## Read These First, In Order

1. **`context/progress-tracker.md`** — what's done, what's in progress, what's next. Always check this before starting work.
2. **`context/project-overview.md`** — what SchoolFlow is, scope, roles, MVP feature list.
3. **`context/architecture.md`** — stack, folder structure, database schema, data flow, invariants.
4. **`context/build-plan.md`** — the phase-by-phase build order. Find the current feature here before implementing it.
5. **`context/code-standards.md`** — PHP/Laravel conventions, naming, error handling, approved dependencies.
6. **`context/ui-rules.md`** + **`context/ui-tokens.md`** — design system. Check before building any view.
7. **`context/ui-registry.md`** — existing components. Check before building a new one; update after building one.

---

## Workflow

- Build UI with mock/seeded data first, verify visually, then wire up logic — per `build-plan.md`'s core principle.
- Complete one feature fully (UI + logic + verified working) before moving to the next.
- After completing a feature: update `context/progress-tracker.md` (mark item done, update "Current Status", add any decisions/notes).
- After building a new UI component: add it to `context/ui-registry.md` with its file path and exact classes used.

---

## Documentation Links

- **Laravel 11** — https://laravel.com/docs/11.x
- **Tailwind CSS v4** — https://tailwindcss.com/docs (CSS-first config via `@theme` — no `tailwind.config.js`)
- **Flowbite** (UI component library, used for sidebar/dashboard/marketing blocks on top of ui-tokens.md) — https://flowbite.com/docs/getting-started/introduction/ — Flowbite has an official Laravel + Tailwind v4 guide: https://flowbite.com/docs/getting-started/laravel/
- **stancl/tenancy** — https://tenancyforlaravel.com/docs/v3/
- **Livewire** — https://livewire.laravel.com/docs
- **spatie/laravel-permission** — https://spatie.be/docs/laravel-permission
- **barryvdh/laravel-dompdf** — https://github.com/barryvdh/laravel-dompdf
- **Paystack** — https://paystack.com/docs/api/

When using Flowbite components, always restyle them to use the CSS variables from `ui-tokens.md` instead of Flowbite's default Tailwind color classes — Flowbite gives structure/behavior (markup + Alpine/JS interactions), but colors/spacing/radii must follow our tokens.

---



[NEEDS INPUT: list any Claude Code skills or MCP servers installed for this project — e.g. Laravel docs skill, Paystack MCP, filesystem/database MCP. If none yet, leave as "None configured yet — use official Laravel, stancl/tenancy, and Paystack documentation directly."]

---

## Key Commands

```bash
# Run the app locally (with Valet, subdomains resolve automatically)
valet park

# Or without Valet
php artisan serve

# Run migrations (central DB)
php artisan migrate

# Tenant-related artisan commands (after stancl/tenancy is configured)
php artisan tenants:list
php artisan tenants:migrate

# Run tests
php artisan test

# Tinker (inspect models/data)
php artisan tinker
```

---

## Non-Negotiables (see architecture.md and code-standards.md for full detail)

- Tenant routes (`routes/tenant.php`) vs central routes (`routes/web.php`) — never mix
- Every tenant database is isolated — no `school_id` columns, no cross-tenant queries in a request
- New tenants are only ever created via `TenantProvisioningService`
- Custom roles use `spatie/laravel-permission` — authorization checks permissions, not hardcoded role names
- No hardcoded colors — use `ui-tokens.md` CSS variables only
- Every Service method returns `['success' => bool, 'data' => mixed, 'error' => ?string]` and has a try/catch
- Paystack payments are always verified server-side before being recorded as paid