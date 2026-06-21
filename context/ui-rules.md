# UI Rules

Concise rules for building the SchoolFlow UI. These rules cover the most important patterns and constraints to keep the UI consistent without over-specifying every detail.

---

## Font

Always load Inter via Google Fonts in the root layout (`layouts/tenant.blade.php` and `layouts/central.blade.php`).

```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
```

The `--font-sans` variable is declared in `@theme` in `app.css`. Apply the font via the `font-sans` utility class on `<body>`. Never use system fonts as the primary font.

---

## Layout

- Page max-width: 1440px, centered
- Main content area padding: 32px on desktop, 16px on mobile (`p-4 lg:p-8`)
- Gap between page sections: 24px on desktop, 16px on mobile
- Sidebar width: 260px, fixed, full height — **desktop only** (≥1024px / `lg:`)
- Top bar height: 64px, full width, white background, padding `0 16px` on mobile, `0 24px` on desktop
- Tenant app uses sidebar + top bar on desktop. Central app (landing, pricing, register) uses top navbar only — no sidebar, at any breakpoint.

---

## Breakpoints

Use Tailwind v4's default breakpoints — no custom breakpoints needed.

| Breakpoint | Width    | Usage                                              |
| ----------- | -------- | ----------------------------------------------------- |
| (default)   | <768px   | Mobile — single column, sidebar hidden, bottom-sheet/drawer patterns |
| `md:`       | ≥768px   | Tablet — 2-column grids where desktop uses more       |
| `lg:`       | ≥1024px  | Desktop — sidebar visible, full multi-column layouts  |

Build mobile-first: base classes target the smallest screen, `md:`/`lg:` prefixes add desktop layout on top.

---

## Responsive Behavior

### Sidebar (Tenant App)

- **Desktop (`lg:` and up)**: sidebar sits in normal flow at 260px width, always visible, alongside content.
- **Mobile/tablet (below `lg:`)**: sidebar is hidden by default. A hamburger icon in the top bar toggles it open as a slide-in drawer over the content, with a semi-transparent backdrop. Tapping the backdrop or a nav item closes it.
- This is the **one approved exception** to "never use `position: fixed`" (see Do Nots) — the mobile drawer and its backdrop use fixed positioning since they must overlay all content regardless of scroll position. No other UI element uses `position: fixed`.

### Top Bar

- Mobile: hamburger icon (left) + school logo (center or left) + account icon (right). Notifications icon collapses into the account dropdown if space is tight.
- Desktop: school logo/name (left, since sidebar handles primary nav), notifications + account dropdown (right).

### Stat Cards (Dashboard)

- Mobile: single column, full width, stacked.
- Tablet (`md:`): 2-column grid.
- Desktop (`lg:`): 4-column grid.

### Tables (Students, Staff, Fees, Attendance lists)

- Wrap every table in `overflow-x-auto` so it scrolls horizontally on narrow screens rather than breaking layout.
- Keep the table itself at its natural width (don't force `w-full` columns to compress) — horizontal scroll is preferred over illegible squeezed columns.
- Do not convert tables into stacked "card per row" layouts for MVP — horizontal scroll is simpler and consistent across all list pages.

### Forms

- Mobile: all fields full width, single column, stacked.
- Desktop: related fields (e.g. first/last name, class/section) can sit in a 2-column grid (`md:grid-cols-2`).

### Attendance Marking UI

- Present/Absent/Late buttons must have a minimum touch target of 44x44px on mobile — teachers will mark attendance from phones.
- On mobile, stack the student name above the three action buttons if they don't fit on one row; on desktop, keep them on one row.

### Charts

- Charts resize to their container's width (responsive container, not a fixed pixel width).
- On mobile, stack charts vertically, one per row, full width.

### Public School Page

- Hero, announcements, and contact sections stack vertically on mobile (they already do, as cards).
- Top navbar: logo + "Login" button always visible; if more nav links are added later, collapse into a hamburger below `md:`.

---

## Sidebar Navigation

Nav items grouped by module, filtered by role:

```
Dashboard
Students
Staff
Attendance
Timetable
Exams
Fees
Announcements
Reports
```

- Active item: `color: #2563EB`, background `bg-accent-muted`, font-weight 500, 14px, rounded-md
- Inactive item: `color: #4A5565`, font-weight 500, 14px
- No underline — active state is background + color change
- Sidebar always white background

---

## Cards

Every content section lives in a card.

```
background: #FFFFFF
border: 1px solid #E7EAF3
border-radius: 16px
padding: 24px
box-shadow: 0px 1px 3px rgba(0,0,0,0.1), 0px 1px 2px -1px rgba(0,0,0,0.1)
```

Never use colored card backgrounds — always white. Color goes inside cards via badges, bars, and text, never on the card surface itself.

---

## Typography Hierarchy

Three levels used consistently throughout:

**Section headings** — card titles, page section titles

```
font-size: 16px
font-weight: 600
color: #101828
line-height: 24px
```

**Body / primary content text**

```
font-size: 14px
font-weight: 500
color: #101828
line-height: 20px
```

**Secondary / muted text** — labels, timestamps, subtitles

```
font-size: 12px
font-weight: 400
color: #99A1AF
line-height: 16px
```

Stat numbers on dashboard use 30px / weight 600 / color #101828.

---

## Badges

All badges use `border-radius: 9999px` (pill shape) unless specified otherwise.

```
padding: 2px 8px
font-size: 12px
font-weight: 500
```

Trend badges on stat cards use `border-radius: 4px` (not pill) with `#ECFDF5` background and `#009966` text.

Attendance status badges:

- Present — `bg-success-lightest` / `text-success-foreground`
- Absent — `bg-error-foreground`-on-light (use `#FEF2F2` background, `#EF4444` text)
- Late — `bg-warning`-light background, `#FF8904` text

Fee status badges:

- Paid — `bg-success-lightest` / `text-success-foreground`
- Unpaid / Overdue — light red background, `#EF4444` text
- Partial — light orange background, `#FF8904` text

---

## Buttons

**Primary button:**

```
background: #2563EB
color: #FFFFFF
border-radius: 8px
padding: 8px 16px
font-size: 14px
font-weight: 500
```

**Secondary button:**

```
background: #FFFFFF
border: 1px solid #E7EAF3
color: #101828
border-radius: 8px
padding: 8px 16px
```

---

## Form Inputs

```
background: #FFFFFF
border: 1px solid #E7EAF3
border-radius: 8px
padding: 8px 12px
font-size: 14px
color: #101828
placeholder color: #99A1AF
focus: ring-1 ring-accent border-accent
```

---

## Tables (Students, Staff, Fees, Attendance lists)

- No alternating row colors — white rows only, separated by border
- Row border: `1px solid #E7EAF3` between rows
- Column headers: uppercase, 12px, font-weight 500, color `#6A7282`
- Row text: 14px, color `#101828`
- Hover state: `background: #F9FAFB`
- Status columns (attendance, fee status) always rendered as badges, never plain text

---

## Attendance Marking UI

- Each student row shows name, admission number, and three quick-action buttons: Present / Absent / Late
- Selected status highlighted with the corresponding badge color
- "Mark all present" bulk action button above the list

---

## Report Card / Score Display

Inline progress bar or grade badge shown next to the numeric score.

```
height: 4px
border-radius: 9999px
background track: #E7EAF3
```

Fill color by grade band (using the default grading scale):

- 70-100 (A): `#10B981` (green)
- 60-69 (B): `#61A8FF` (blue)
- 50-59 (C): `#FF8904` (orange)
- Below 50 (D/F): `#EF4444` (red)

---

## Empty States

Every section that can be empty must have an empty state. Keep it minimal:

- Short descriptive text in `color: #99A1AF`
- Optional icon above text
- CTA button if there's a logical next action (e.g. "No students yet — Add Student")

---

## Public School Page

The auto-generated public page (`{school}.schoolflow.com/`) uses the same card and typography tokens as the dashboard, but with:

- A hero section showing the school's logo, name, and a short description (if provided)
- An announcements list using the same card pattern as the dashboard notice board
- A simple contact section (address, phone, email) pulled from school profile data
- No sidebar — top navbar with school logo/name only, and a "Login" button

---

## Tailwind v4 Note

This project uses Tailwind CSS v4. Tokens are defined with `@theme` in `resources/css/app.css` — no `tailwind.config.js` needed. Never define colors in a config file. Always use `@theme` for new tokens.

---

## Do Nots

- Never use Tailwind's built-in color classes (`bg-purple-500`, `text-gray-600`) — use project tokens only
- Never define colors in a config file — use `@theme` in `app.css`
- Never add gradients to card backgrounds
- Never use more than one font weight in a single UI element
- Never show raw error messages to users — always show human readable text
- Never stack more than 2 levels of border radius inside each other
- Never use `position: fixed` for UI elements — use normal flow layout (desktop sidebar uses `sticky`, not `fixed`). The only exception is the mobile sidebar drawer + backdrop (see Responsive Behavior).