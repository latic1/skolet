# UI Tokens

Design tokens for SchoolFlow. All colors, typography, spacing, and component values are defined once and used throughout the codebase — never hardcode colors or use raw Tailwind color classes in views/components.

---

## How to Use

This project uses **Tailwind CSS v4**. All design tokens are defined using the `@theme` directive in `resources/css/app.css`. No `tailwind.config.js` needed for colors or tokens.

Tailwind v4 automatically generates utility classes from `@theme` variables:

- `--color-accent` → `bg-accent`, `text-accent`, `border-accent`
- `--color-surface` → `bg-surface`, `text-surface`, `border-surface`

```html
<!-- Correct — uses generated utility classes -->
<div class="bg-surface text-text-primary border-border">

<!-- Also correct — references CSS variable directly -->
<div style="color: var(--color-text-primary)">

<!-- Never — hardcoded hex values -->
<div class="bg-[#F6F7FB] text-[#101828]">

<!-- Never — raw Tailwind color classes -->
<div class="bg-purple-500 text-gray-600">
```

---

## app.css — Complete Token Definition

```css
@import "tailwindcss";

@theme {
  /* Font */
  --font-sans: "Inter", sans-serif;

  /* Page and surface backgrounds */
  --color-background: #f6f7fb;
  --color-surface: #ffffff;
  --color-surface-secondary: #f9fafb;
  --color-surface-tertiary: #f2f5f7;
  --color-surface-muted: #f4f5fb;

  /* Borders */
  --color-border: #e7eaf3;
  --color-border-light: #e5e7eb;
  --color-border-muted: #dfe1e7;

  /* Text */
  --color-text-primary: #101828;
  --color-text-secondary: #6a7282;
  --color-text-muted: #99a1af;
  --color-text-dark: #364153;
  --color-text-darker: #36394a;
  --color-text-darkest: #111827;
  --color-text-black: #131316;
  --color-text-slate: #272835;
  --color-text-slate-medium: #666d80;

  /* Primary accent — blue */
  --color-accent: #2563eb;
  --color-accent-dark: #1d4ed8;
  --color-accent-light: #dbeafe;
  --color-accent-muted: #eff6ff;
  --color-accent-foreground: #ffffff;

  /* Success — green (Present, Paid, Grade A/B) */
  --color-success: #10b981;
  --color-success-alt: #00bc7d;
  --color-success-dark: #007a55;
  --color-success-darker: #009966;
  --color-success-light: #d0fae5;
  --color-success-lightest: #ecfdf5;
  --color-success-foreground: #007a55;

  /* Info — cyan (Grade B, links) */
  --color-info: #06b6d4;
  --color-info-dark: #0891b2;
  --color-info-medium: #22d3ee;
  --color-info-light: #cffafe;
  --color-info-lightest: #ecfeff;
  --color-info-foreground: #0891b2;
  --color-info-muted: #94a2c5;

  /* Warning — orange (Late, Partial fee, Grade C) */
  --color-warning: #ff8904;
  --color-warning-foreground: #ffffff;

  /* Error — red (Absent, Overdue fee, Grade D/F) */
  --color-error: #ef4444;
  --color-error-light: #fef2f2;
  --color-error-foreground: #ffffff;

  /* Paystack brand */
  --color-paystack: #00c3f7;
  --color-paystack-light: #e6faff;
  --color-paystack-foreground: #ffffff;

  /* Dark overlays */
  --color-overlay: #111827;
  --color-overlay-dark: #131316;

  /* Border radius */
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-xl: 16px;
  --radius-full: 9999px;
}
```

Tailwind v4 generates utility classes automatically from every `--color-*` token above:

- `bg-accent`, `text-accent`, `border-accent`
- `bg-surface`, `text-surface-secondary`
- `bg-success-light`, `text-text-muted`
- etc.

---

## Color Usage Guide

### Page Layout

| Element           | Token                  |
| ----------------- | ---------------------- |
| Page background   | `bg-background`        |
| Card / surface    | `bg-surface`           |
| Secondary surface | `bg-surface-secondary` |
| Default border    | `border-border`        |
| Light border      | `border-border-light`  |

### Typography

| Element                | Token                           |
| ----------------------- | -------------------------------- |
| Headings, primary text   | `text-text-primary` (#101828)   |
| Secondary text, labels   | `text-text-secondary` (#6A7282) |
| Placeholder, muted       | `text-text-muted` (#99A1AF)     |
| Dark labels               | `text-text-dark` (#364153)      |

### Accent (Primary Purple)

Used for: primary buttons, active sidebar items, focus rings, logo

| Element                | Token                    |
| ----------------------- | ------------------------ |
| Button background        | `bg-accent`              |
| Button text               | `text-accent-foreground` |
| Light badge background    | `bg-accent-light`        |
| Active sidebar background | `bg-accent-muted`        |

### Attendance Status Colors

| Status  | Background             | Text             |
| ------- | ----------------------- | ----------------- |
| Present | `bg-success-lightest`   | `text-success-foreground` |
| Absent  | `bg-error-light`        | `text-error`      |
| Late    | light orange (`#FFF7ED`)| `text-warning`    |

### Fee Status Colors

| Status   | Background            | Text                      |
| -------- | ----------------------- | -------------------------- |
| Paid     | `bg-success-lightest`   | `text-success-foreground` |
| Unpaid   | `bg-error-light`        | `text-error`               |
| Overdue  | `bg-error-light`        | `text-error`               |
| Partial  | light orange (`#FFF7ED`)| `text-warning`             |

### Grade Colors (default grading scale)

| Grade | Score Range | Token                                  |
| ----- | ------------ | --------------------------------------- |
| A     | 70-100       | `text-success` / `bg-success-lightest` |
| B     | 60-69        | `text-info` / `bg-info-lightest`       |
| C     | 50-59        | `text-warning`                          |
| D / F | Below 50     | `text-error` / `bg-error-light`         |

### Source Badges (Payment Method)

| Source   | Background             | Text                  |
| -------- | ------------------------ | ---------------------- |
| Paystack | `bg-paystack-light`      | `text-paystack`        |
| Cash     | `bg-surface-secondary`   | `text-text-secondary`  |

---

## Typography

| Element              | Size | Weight | Line height | Color token           |
| --------------------- | ---- | ------ | ----------- | ----------------------- |
| Logo text             | 19px | 700    | 28px        | `text-text-darkest`    |
| Stat number           | 30px | 600    | 36px        | `text-text-primary`    |
| Section heading        | 16px | 600    | 24px        | `text-text-primary`    |
| Nav item (active)       | 14px | 500    | 20px        | `text-accent`          |
| Nav item (inactive)     | 14px | 500    | 20px        | `text-text-dark`       |
| Card label             | 14px | 500    | 20px        | `text-text-secondary`  |
| Body / activity text    | 14px | 500    | 20px        | `text-text-primary`    |
| Trend badge text        | 12px | 500    | 16px        | `text-success-darker`  |
| Timestamp / muted        | 12px | 400    | 16px        | `text-text-muted`      |
| Chart axis labels        | 12px | 400    | 15px        | `#9CA3AF`               |
| Stat subtitle          | 12px | 400    | 16px        | `text-text-muted`       |

Font family: **Inter** — loaded via Google Fonts in the root layout.

---

## Spacing

| Token       | Value      | Usage                  |
| ------------ | ----------- | ------------------------ |
| `gap-1`     | 4px        | Tight inline gaps       |
| `gap-2`     | 8px        | Badge and tag gaps       |
| `gap-3`     | 12px       | Form field gaps          |
| `gap-4`     | 16px       | Section internal gaps    |
| `gap-6`     | 24px       | Between sections          |
| `gap-8`     | 32px       | Page section gaps         |
| `p-4`       | 16px       | Card padding              |
| `p-6`       | 24px       | Large card padding        |
| `px-4 py-2` | 16px / 8px | Button padding            |
| `px-3 py-1` | 12px / 4px | Badge padding              |

---

## Component Tokens

### Cards

```
background: bg-surface
border: 1px solid var(--border)
border-radius: 16px (rounded-2xl in Tailwind)
padding: 24px (p-6)
box-shadow: 0px 1px 3px rgba(0,0,0,0.1), 0px 1px 2px -1px rgba(0,0,0,0.1)
```

### Buttons

**Primary:**

```
background: bg-accent
text: text-accent-foreground
border-radius: rounded-md
padding: px-4 py-2
font-weight: font-medium
```

**Secondary:**

```
background: bg-surface
border: border border-border
text: text-text-primary
border-radius: rounded-md
padding: px-4 py-2
```

**Ghost:**

```
background: transparent
text: text-text-secondary
hover: hover:bg-surface-secondary
border-radius: rounded-md
```

### Input Fields

```
background: bg-surface
border: border border-border
border-radius: rounded-md
padding: px-3 py-2
text: text-text-primary
placeholder: text-text-muted
focus: ring-1 ring-accent
```

### Badges

```
border-radius: rounded-full
padding: px-2 py-0.5
font-size: text-xs
font-weight: font-medium
```

### Score / Grade Bar

```
background track: bg-border-light
fill: varies by grade band (see Grade Colors above)
height: 4px
border-radius: rounded-full
```

### Trend Badges (stat cards)

```
background: #ECFDF5 (success-lightest)
text color: #009966 (success-darker)
border-radius: 4px (rounded-sm)
padding: 2px 8px
font-size: 12px
font-weight: 500
```

### Activity Dots

Each activity type has a specific dot color:

| Activity Type        | Outer ring                 | Inner dot                   |
| ---------------------- | ---------------------------- | ------------------------------ |
| Attendance marked       | `#DBEAFE` (accent-light)    | `#2563EB` (accent)            |
| Fee payment received    | `#D0FAE5` (success-light)   | `#00BC7D` (success-alt)        |
| Exam results published  | `#DBEAFE` (info-light)      | `#61A8FF` (info)               |
| Announcement posted     | light orange                 | `#FF8904` (warning)            |

Dot size: 8px inner, 16px outer with white border

### Dashboard Chart Colors

| Chart                              | Color                                                            |
| ------------------------------------ | ------------------------------------------------------------------ |
| Fee Collection Over Time (line)        | `#2563EB` stroke, 3px width, gradient fill rgba(37,99,235,0.2)   |
| Attendance Rate (bars)                  | `#06B6D4`                                                          |
| Grade Distribution (bars)               | `#10B981`                                                          |
| Chart grid lines                       | `1px dashed #E7EAF3`                                               |
| Chart axis labels                       | `#9CA3AF`, 12px                                                    |

### Logo

```
background: linear-gradient(45deg, #2563EB 0%, #1E3A8A 100%)
border-radius: 10px
size: 36x36px
```

Each tenant can override the logo image (uploaded) but the placeholder/default logo uses the gradient above.

---

## Invariants

- Never use hex values directly in views/components — always use CSS variables via Tailwind tokens
- Font is Inter — always loaded via Google Fonts, never use a fallback system font
- Never use raw Tailwind color classes like `bg-purple-500` or `text-gray-600` — use project tokens only
- `--accent` (#2563EB) is the only blue for primary actions/branding — never use Tailwind's built-in blue scale; the `--info` token (#06B6D4, cyan) is reserved for Grade B and links and must remain visually distinct from `--accent`
- Attendance, fee, and grade status colors always use the tokens defined above — never hardcoded
- Paystack badge always uses `--paystack` (#00C3F7) — never reuse `--accent` or `--info` for it
- All borders default to `--border` (#E7EAF3) — never use `border-gray-*`