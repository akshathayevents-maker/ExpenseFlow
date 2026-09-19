# ExpenseFlow — Project Knowledge

> **Read this before any significant change.** It is a living map of the repository, written from a code inspection on
> **2026-09-19** at commit `7068cb0`, re-checked in a second-pass review at `cd9227e` ("Harden production deployment…", which the maintainer committed between the two passes; branch `main`). Docs added by these passes are untracked at the time of writing.
> **The code always wins.** If this file disagrees with the repository: trust the code, note the discrepancy, fix this
> file, then continue the task (§39).
>
> **Evidence classes (used consistently in this file, the runbook and the ADRs):**
>
> | Tag | Meaning |
> |---|---|
> | `VERIFIED-REPO` | Read directly in a file of this repository (code, config, migration, script, doc). Default for unmarked statements about code. |
> | `VERIFIED-TEST` | Behaviour is asserted by an automated test that exists in the repo. Add "(run YYYY-MM-DD)" only if the test was actually executed. |
> | `PROD-VERIFIED` | Proven on the real production server or against the live site, with the evidence recorded (only §22 facts from the maintainer's 2026-09-19 notes qualify). |
> | `INFERRED` | Deduced from code/comments/commit messages but not proven by a test or by production. Treat as a hypothesis. |
> | `UNKNOWN` | Nothing in the repo answers it. |
> | `REQUIRES-PRODUCTION-VERIFICATION` | Only the live server can answer it; do not act on it until checked. |
>
> Unused-code findings (§29) use a separate scale: CONFIRMED UNUSED / LIKELY UNUSED / POSSIBLY USED / UNKNOWN.
> Unmarked statements about code are `VERIFIED-REPO`; unmarked statements about production are **not** facts — see §41 Do Not Assume.

---

## 1. Document Purpose

Give future developers and AI agents a factual map of ExpenseFlow — what it does, how it is built, where each rule lives,
how it is deployed, and what is risky — so changes reuse existing logic instead of duplicating or contradicting it.

Companion documents:
- [CLAUDE.md](CLAUDE.md) — short entry point that makes every Claude session read this file first.
- [docs/OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md) — local/staging/production operations.
- [docs/architecture/README.md](docs/architecture/README.md) — architecture decision records (ADRs).
- [docs/hospitality-design-system.md](docs/hospitality-design-system.md) — pre-existing UI principles note.
- [deployment/DEPLOYMENT_CHECKLIST.md](deployment/DEPLOYMENT_CHECKLIST.md) — production deploy runbook (owned by the deploy scripts).

**Scope warning:** before `CLAUDE.md` was added to this repo, the only project instructions Claude loaded were from the parent directory `/home/qc7107-l/Projects/CLAUDE.md`, which describes a *different* monorepo (Lumen/Laravel
microservices, `event-workflow-app`, Livewire, Sanctum). **None of it applies to ExpenseFlow**; `ExpenseFlow/CLAUDE.md` overrides it for this repo. ExpenseFlow is a single
Laravel 13 application with no Livewire, no Sanctum, no Docker, and no `event-workflow-app` code.

---

## 2. Project Overview

ExpenseFlow is a **single-tenant, server-rendered Laravel monolith** for a hospitality business (venue/hall hire with
catering; brand shown on the site as "Akshathay", host `expense.akshathay.com`). Despite the name it is a broad internal
operations suite:

| Domain | What it covers |
|---|---|
| Expense & payments | Employees raise payment requests (with a UPI QR image), staff settle them; wallets; reimbursements; daily closing |
| Hall management | Hall/food/food-only bookings, availability, payments, GST invoices (PDF), kitchen summary, reports, calendar |
| Event Request Portal | Admin issues a public tokenised link; a client picks a menu; admin approves → auto-creates a booking |
| Kitchen | Recipe library (ingredients, SOP steps), employee-facing batch calculator |
| Menu Composer | Bilingual (English/Tamil) menu drafts/templates → PDF with letterhead |
| Meal Register | Daily planned/actual meal counts per client |
| Inventory | Items, stock ledger, alerts, purchase plans, OCR of supplier bills |
| HR self-service | Attendance (with half-day segments), regularizations, leave (paid/LOP, ledger), overtime, salary/payroll views, advances |
| Platform | Roles, audit log, in-app notifications, settings, PWA shell |

Stack (from `composer.json`, `package.json`): PHP `^8.3`, Laravel `^13.8`, `barryvdh/laravel-dompdf ^3.1`, Pest 4,
Breeze (dev), Vite 8, Tailwind 3 (+forms), Bootstrap 5.3 (npm dep **and** CDN), Alpine.js, axios, sass. Production DB is
PostgreSQL; production web server is Apache (see §22).

---

## 3. Product Capabilities

Per-module capability lists with their code entry points are in §7. Highlights that are easy to miss:

- **Public, unauthenticated surfaces** (only three): the signed payment page `/pay/{id}`, the QR image `/pay/{id}/qr`, and the
  event-request portal `/event-request/{token}`. Everything else needs login.
- **No outbound integrations at runtime** except a WhatsApp *share link* and browser-loaded CDN assets (§18).
- **No queued jobs exist in app code** (§15). Workers are provisioned in production but there is nothing to run on them today.
- Notifications are **in-app rows only** (`app_notifications`); no email/SMS/WhatsApp is sent by the app (§20).

---

## 4. Users / Roles / Permissions

Roles: `users.role` enum `admin | manager | employee` (migration `2026_05_15_123954`), default `employee`. `users.is_active`
boolean. Helpers `User::isAdmin()/isManager()/isEmployee()`.

**Role middleware** (aliases in [bootstrap/app.php](bootstrap/app.php)):

| Alias | Class | Allows |
|---|---|---|
| `role.admin` | `AdminMiddleware` | admin |
| `role.manager` | `ManagerMiddleware` | admin **or** manager (the name is misleading) |
| `role.hall` | `HallMiddleware` | admin or manager (`in_array(role, ['admin','manager'])`) |
| `attendance.marked` | `EnsureAttendanceMarked` | employee-only gate, see §13.7 |

**Route groups** ([routes/web.php](routes/web.php)): `admin/*` = `auth,verified,role.admin`; `manager/*` = `auth,verified,role.manager`;
`employee/*` = `auth,verified,attendance.marked` (**no role middleware** — any authenticated user, including admins, can open
`employee/*` URLs; the attendance gate only triggers for role employee); `hall/*`, `kitchen/*`, `admin/event-requests*` =
`role.hall`; `menu/*` = `role.admin`; `meal-register/*` = any authenticated (create/edit/delete need `role.hall`);
`notifications`, `profile` = any authenticated.

**Policies** (`app/Policies/`): `ExpenseRequestPolicy`, `EventRequestPolicy` (registered explicitly in `AppServiceProvider::boot`);
`EmployeeAdvancePolicy`, `EmployeeAttendanceRegularizationPolicy`, `EmployeeOvertimePolicy`, `LeaveRequestPolicy`, `UserPolicy`
(no explicit registration found — rely on Laravel's model→policy naming discovery; **INFERRED** by a test).

Key permission facts:

| Action | Who | Where |
|---|---|---|
| View expense | admin, manager, or requester | `ExpenseRequestPolicy::view` |
| Create expense | any **active** user | `ExpenseRequestPolicy::create` (`is_active`) |
| approve/reject expense | admin/manager **and** status `pending` | `ExpenseRequestPolicy::approve/reject` |
| markPaid | admin/manager and status `approved` or `pending_payment` | `ExpenseRequestPolicy::markPaid` |
| reject from pay page | admin/manager, not settled, not rejected | `ExpenseRequestPolicy::rejectFromPayPage` |
| markCompleted | admin and status `paid` | `ExpenseRequestPolicy::markCompleted` |
| delete expense | admin | `ExpenseRequestPolicy::delete` |
| decide event request | admin/manager and status ∈ {submitted, under_review, resubmitted} | `EventRequestPolicy::decide` |
| create overtime (employee) | active user **and** setting `employee_overtime_requests_enabled` true (default **false**) | `EmployeeOvertimePolicy::create` |
| record OT for someone else | admin | `EmployeeOvertimePolicy::recordForOther` |

`role` and `is_active` are **not** in `User::$fillable` (commit `0eb9628`); assign explicitly (`$user->role = …; save()`).

---

## 5. System Architecture

Components that **actually exist** in the repository/deployment:

```
 Browser (desktop / mobile PWA shell, sw.js)
    |  HTTPS
    v
 Apache 2.4.58 (production, proven)     DocumentRoot /var/www/akshathayexpense/public
    |  PHP handler: mod_php vs php-fpm = REQUIRES-PRODUCTION-VERIFICATION (deploy scripts handle both)
    v
 Laravel 13 monolith  (routes -> middleware -> controller -> [FormRequest] -> service -> Eloquent model)
    |
    +--- PostgreSQL (production; also local .env)         SQLite :memory: in tests
    |       sessions, cache, jobs tables exist (database drivers are the defaults)
    +--- Local filesystem  storage/app/{public,private}   (no S3 configured; s3 disk defined but unused)
    +--- Child processes: python3 (OCR), headless Chrome (menu PDF), ghostscript (letterhead)   [all synchronous, in-request]
    +--- dompdf (in-process) for invoices / fallback menu PDF
    +--- Scheduler: routes/console.php  (needs a `schedule:run` cron -> REQUIRES-PRODUCTION-VERIFICATION)
    +--- Supervisor: programs `expenseflow-worker:expenseflow-worker-N_00` run `queue:work`  (no app jobs dispatch to them)
 CDN (cdn.jsdelivr.net): Bootstrap, Bootstrap Icons, Chart.js, FullCalendar, Alpine (browser-side)
 WhatsApp: only a wa.me / api.whatsapp.com *share URL* built server-side and opened by the user's browser
```

Not present: Redis usage in code (config supports it; `.env.example` recommends it for production — **whether prod uses it is
REQUIRES-PRODUCTION-VERIFICATION**), payment gateway, email delivery (`MAIL_MAILER=log` locally; Brevo SMTP is only mentioned in
`.env.example` comments), SMS, S3, Docker, CI/CD, nginx (retired), monitoring/APM (no Telescope/Pulse/Sentry packages).

**Timezone split (verified):** `config/app.php` sets `'timezone' => 'UTC'`, while HR code hard-codes `Asia/Kolkata`
(`EmployeeAttendanceService::BUSINESS_TIMEZONE`). Everything else — `now()`, `today()`, `whereDate(... today())` in
`HallBookingController`/`HallDashboardController`, `now()->subHours()` in commands, stored timestamps and the **scheduler** — runs in UTC.
Around 00:00–05:30 IST the UTC date is still "yesterday", so "today" on the hall dashboard/kitchen summary can differ from the business day.

---

## 6. Repository Structure

```
app/Console/Commands/     7 artisan commands (§16)
app/Http/Controllers/     Admin/ Manager/ Employee/ Hall/ Kitchen/ Menu/ MealRegister/ EventRequest/ Auth/ + PaymentRequestController, NotificationController, ProfileController
app/Http/Middleware/      AdminMiddleware ManagerMiddleware HallMiddleware EnsureAttendanceMarked SecurityHeadersMiddleware
app/Http/Requests/        FormRequests grouped by domain (Admin, Advance, AttendanceRegularization, Auth, EventRequest, Expense, Inventory, Leave, Overtime)
app/Models/               63 Eloquent models (no Mongo, no repositories layer)
app/Policies/             7 policies
app/Providers/            AppServiceProvider only (registers 2 policies)
CLAUDE.md                 entry point for Claude sessions (points here)
app/Services/             business logic (+ EventRequest/, OCR/)
app/View/Components/      AppLayout, GuestLayout (class components)
bootstrap/app.php         routing, middleware aliases, health=/up
config/                   app auth cache database dompdf filesystems logging mail queue services session + custom: menu.php menu_categories.php ocr.php
database/migrations/      91 migration files (Postgres-specific raw SQL guarded by DB driver checks)
database/seeders/         Admin, Category, Database, EventRequestMenuCategory/Item, Hall, User seeders
database/factories/       User, MenuDraft, MenuItem, MenuTemplate
deployment/               flat/Apache deploy tooling (§21); legacy-release-based/ = retired design
docs/                     hospitality-design-system.md, OPERATIONS_RUNBOOK.md, architecture/
public/                   index.php, build/ (COMMITTED Vite output), sw.js, site.webmanifest, offline.html, icons, Menu_Letter_Head.pdf, storage -> symlink
resources/views/          Blade (§12); resources/css/app.css (4.3k lines), resources/js (event-request-public.js), resources/scss
routes/                   web.php (main), auth.php (Breeze), event_request.php (portal), console.php (schedule)
storage/app/ocr/          invoice_ocr.py + INSTALL.md (Python OCR script called by PHP)
tests/                    Pest feature tests (§26)
```

There is **no** `app/Jobs`, `app/Mail`, `app/Notifications`, `app/Listeners`, `app/Livewire`, `tests/Browser`, `.github/`, `Dockerfile`, `scripts/`.

---

## 7. Application Modules

Trace format: **route → controller → (request) → service → model → tables → side effects**. Route names in `code`.

### 7.0 Where to look (task → files)

| If the task mentions… | Start here (controller · service · model) | Tests to run (§26) |
|---|---|---|
| expense request, QR, WhatsApp, pay page, proof | `PaymentRequestController`, `Employee\|Admin\|Manager\ExpenseRequestController` · `ExpenseRequestService`, `ExpenseSettlementService`, `PaymentService` · `ExpenseRequest`, `ExpensePayment` | **none exist** — write them |
| wallet, reimbursement | `Admin\WalletController`, `Employee\WalletController` · `WalletService` · `Wallet`, `WalletTransaction` | none exist |
| daily closing | `Admin\DailyClosingController` · `DailyClosingCalculationService` · `DailyClosing*` | none exist |
| hall booking, invoice, GST, calendar, kitchen summary | `Hall\HallBookingController`, `HallDashboardController`, `HallReportController` · `InvoiceCalculationService` · `HallBooking`, `BookingPayment`, `MealPlan` | `HallBookingTypeTest`, `HallBookingPaymentDueTest`, `HallDashboardMonthFilterTest` |
| event request portal | `EventRequest\PublicEventRequestController`, `Admin\EventRequest\*` · `Services\EventRequest\*` · `EventRequest*` | none exist |
| recipes / kitchen calculator | `Kitchen\RecipeController`, `Employee\KitchenController` · — · `Recipe*` | `KitchenCalculatorMobileTest` (UI only) |
| menu composer / PDF / Tamil | `Menu\MenuComposerController`, `MenuItemController` · `MenuTranslationService`, `MenuLetterheadService` · `MenuItem/Draft/Template` | `tests/Feature/Menu/*` |
| meal register | `MealRegister\*` · — · `MealClient`, `MealEntry`, `MealEntryItem` | `tests/Feature/MealRegister/*` |
| inventory, stock, bills, OCR, purchase plans | `Admin\Inventory\*`, `PurchasePlanController` · `InventoryService`, `InvoiceOCRService`, `OCR\*`, `PurchasePlanningService` · `Inventory*`, `PurchasePlan*` | none exist |
| attendance, regularization, attendance gate | `Employee\AttendanceController`, `Admin\Attendance*` · `EmployeeAttendanceService` (read its docblock first), `AttendanceConflictChecker` · `EmployeeAttendance*` | `EmployeeAttendanceTest`, `AttendanceGateTest`, `AttendanceRegularizationTest`, `AttendanceLeaveConflictTest`, `AttendanceSegmentPayableTest`, `ReverseSelfMarkTest`, `OppositeHalfLeaveWorkflowTest`, `Admin\AttendanceLeaveAdminViewTest` |
| leave, policies, templates, allocations | `*\LeaveController`, `Admin\Leave*` · `LeaveService`, `LeaveAllocationService`, `LeaveBalanceService`, `LeavePolicyAssignmentService` · `LeaveRequest`, `EmployeeLeave*`, `LeavePolicyTemplate*` | `LeaveTest`, `LeaveDomainTest`, `LeavePolicyEffectiveDatingTest`, `Admin\LeavePolicyTemplateTest`, `Admin\LeaveManagementUiTest` |
| overtime | `*\OvertimeController` · `OvertimeService`, `OvertimeCalculationService` · `EmployeeOvertime*` | `Overtime*Test` (5 files) |
| salary, payroll, payable days, advances | `Admin\EmployeeSalaryController`, `*\AdvanceController` · `MonthlyPayableService`, `PayableDaysCalculator`, `EmployeeSalaryService`, `EmployeeAdvanceService`, `AdvanceEligibilityService` · `EmployeeSalary`, `EmployeeAdvance`, `AdvanceTransaction` | `SalaryDiscoveryAndPayableTest`, `PayableDaysCalculatorTest`, `Admin\MonthlyPayableTest`, `Admin\PayrollAdvanceEligibilityTest`, `AdvanceTest`, `AdvanceEligibilityTest`, `AdminEmployeeSalaryTest` |
| login, register, password, profile | `Auth\*`, `ProfileController` | `tests/Feature/Auth/*`, `ProfileTest` |
| roles, permissions | `app/Http/Middleware/*`, `app/Policies/*`, `bootstrap/app.php` aliases | attendance/leave/overtime tests cover their policies; others none |
| settings, audit log, notifications, reports, analytics | `Admin\SettingController`, `AuditLogController`, `NotificationController`, `ReportController`, `AnalyticsController` · `Setting`, `AuditLogService`, `NotificationService` | none exist |
| layout, colours, mobile nav, PWA | `resources/views/components/admin-layout.blade.php`, `resources/css/app.css`, `public/sw.js` | `AdminMobileLayoutTest`, `NavigationTest`, `Admin\*UiTest` |
| deploy, rollback, health, Apache, supervisor | `deployment/*.sh` | `bash deployment/tests/run-tests.sh` (sandbox) |

### 7.1 Expense / payment requests (core original module)
- Create: `employee.expense-requests.store` / `admin.…store` / `manager.…store` → `StoreExpenseRequestRequest` → `ExpenseRequestService::create()`
  → `expense_requests` (status **`pending_payment`**, category/vendor/priority left null) + optional QR image saved to public disk at
  `qr-codes/{id}/{slug}_{uniqid}.{ext}`. Success page sets `whatsapp_sent_at` on first view (it records *page shown*, not *message sent*).
- Share: `ExpenseRequest::whatsAppUrl()` builds `https://api.whatsapp.com/send?text=…` containing a 30-day signed `/pay/{id}` link.
- Settle: `PaymentRequestController` (public page, staff auth checked *inside* controller) or admin routes `settle-wallet`,
  `settle-direct`, `reimbursement-pending`, `reimburse`, `mark-completed` → `ExpenseSettlementService` (see §13.1).
- Legacy approval path: `approve`/`reject` (policy needs status `pending`) → `ExpenseRequestService`. **New requests never enter `pending`** (created as
  `pending_payment`), so approve/reject only applies to older rows. Dashboards still count `pending`.
- Files: `FileUploadService::storeBills()` (`ExpenseBill`, public disk `bills/{id}`) is injected into `ExpenseRequestService` but never called; no route uploads bills (**LIKELY UNUSED**, §29).

### 7.2 Wallets
`WalletService` (`getOrCreate`, `credit`, `debit`, `adjust`, `recordReimbursement`), each inside `DB::transaction` with `lockForUpdate()` on the wallet row,
writing `wallet_transactions` (balance_before/after) and an audit row. `debit` throws `RuntimeException` if `balance < amount`. Admin UI: `admin.wallets.*`; employee: `employee.wallet.show`.

### 7.3 Daily Closing (`admin.daily-closings.*`)
`DailyClosingController` + `DailyClosingCalculationService`. States `draft → verified → closed`(finalized). Expense lines are *snapshotted* into
`daily_closing_expenses` (`captureSnapshot`), adjustments in `daily_closing_adjustments`, field-level history in `daily_closing_audits`.
Finalized closings reject all modifications (422/`back()->with('error')`). Expense snapshot filter is `status NOT IN ('pending','rejected')` (so `pending_payment` counts).

### 7.4 Hall management (`hall.*`, admin+manager)
`HallBookingController` (703 lines), `HallDashboardController`, `MealPlanController`, `HallReportController`. Booking types `hall_only | hall_food | food_only`
(`HallBooking::bookingTypes()`); mixed food via `hall_booking_food_splits`; extras in `booking_additional_services`; payments in `booking_payments`.
Invoice: `InvoiceCalculationService::calculate()`, PDF via dompdf (`hall.bookings.invoice.pdf`). Employees get a read-only calendar (`employee.hall.bookings.calendar*`).

### 7.5 Event Request Portal (`routes/event_request.php`)
Admin creates a draft → `EventRequestTokenService::issue()` creates a 24-char random token (`Str::random(24)`, unique) → client opens `/event-request/{token}` (no auth),
selects menu items (`EventRequestPricingService::priceSelection`), submits → admin reviews → `approve` calls `EventRequestCalendarIntegrationService::createBookingForApprovedRequest()`
creating a **`food_only` `hall_bookings` row** and setting request status `scheduled`. Every step writes an `event_request_revisions` snapshot (JSON).

### 7.6 Kitchen
`kitchen.recipes.*` (`RecipeController`, `role.hall`): recipes + `recipe_ingredients` (optionally linked to `inventory_items`) + `recipe_sops`.
`employee.kitchen.calculator*` (`EmployeeKitchenController`): read-only, calculation is client-side (route comment). `hall.bookings.kitchen`: per-day meal/guest summary.

### 7.7 Menu Composer (`menu.*`, admin only)
`MenuItemController` (bilingual master items; `items.translate` uses `MenuTranslationService`, a **built-in dictionary**, no external API),
`MenuComposerController` (drafts/templates; `pdf.generate` POST → PDF). PDF: headless Chrome if `/usr/bin/{google-chrome,chromium-browser,chromium}` exists
(Tamil shaping), else dompdf fallback. Letterhead: `MenuLetterheadService` rasterises `public/Menu_Letter_Head.pdf` (or `MENU_LETTERHEAD_PATH`) with **ghostscript** to `storage/app/menu_letterhead_cache.jpg`.
Artisan `menu:install-font` downloads a font (see §16).

### 7.8 Meal Register (`meal-register.*`)
`MealClientController`, `DailyMealEntryController` (`entries.save` upserts), `MealRegisterReportController` (`reports.export`). Tables `meal_clients`, `meal_entries`, `meal_entry_items` (unique per client/date and entry/meal type).
Older twin tables/models `daily_meal_entries`/`daily_meal_entry_items` (`DailyMealEntry*`) still exist and `MealClient` references them (§29).

### 7.9 Inventory (`admin.inventory.*`, `admin.purchase-*`)
`InventoryService` (`addStock/deductStock/adjustStock` all `lockForUpdate` in a transaction; `deductStock` throws on insufficient stock; `checkAndCreateAlerts`, `resolveAlertsIfRestocked`, weighted `recalcAverageCost`).
`PurchasePlanningService` suggests quantities ordered by `current_stock / NULLIF(minimum_stock,0)`. Bills: `InventoryBillController` uploads (jpg/jpeg/png/pdf ≤10 MB, private disk `inventory-bills`, sha256 duplicate detection) → `InvoiceOCRService` → `PaddleOCRService` (Python) + `InvoiceParserService` → items imported to stock.

### 7.10 HR self-service
Attendance (`EmployeeAttendanceService`, 974 lines, the largest service), regularizations, leave (`LeaveService`, `LeaveAllocationService`, `LeaveBalanceService`, `LeavePolicyAssignmentService`), overtime (`OvertimeService`, `OvertimeCalculationService`),
salary (`EmployeeSalaryService`), payroll views (`MonthlyPayableService`, `PayableDaysCalculator`), advances (`EmployeeAdvanceService`, `AdvanceEligibilityService`). Rules in §13.

### 7.11 Cross-cutting
`AuditLogService::log()` (→ `audit_logs`), `NotificationService::send/sendToAdmins/sendToManagers` (→ `app_notifications`), `Setting::get/set` (key/value table), `admin.analytics.*`, `admin.reports.*`, `admin.audit-logs.index`, `admin.settings.*`.

---

## 8. Request Lifecycle

1. `public/index.php` → `bootstrap/app.php` (`withRouting`: `routes/web.php`, `routes/console.php`; health endpoint `GET /up`).
2. Global `web` group + appended `SecurityHeadersMiddleware` (CSP, X-Frame-Options DENY, nosniff, Referrer-Policy, Permissions-Policy, HSTS in production/staging on HTTPS; strips `X-Powered-By`/`Server` headers).
3. Route middleware (`auth`, `verified`, role alias, `attendance.marked`). `verified` passes for everyone because `User` does **not** implement `MustVerifyEmail` (**INFERRED** by a test).
4. Controller → `FormRequest` validation (or inline `$request->validate`) → `authorize()`/`Gate` → service → Eloquent → Blade view / redirect / JSON.
5. Cross-module notes: services call `AuditLogService` and `NotificationService` synchronously inside the request; nothing is dispatched to a queue.

Auth throttles (`routes/auth.php`): login `5/min`, register `10/min`, forgot/reset password `5/min`, verification `6/min`. **No throttle** on `/event-request/{token}` (public), `/pay/*` POSTs, or profile.

---

## 9. Database Architecture

Engine: **PostgreSQL in production** (raw `ALTER TABLE … CHECK` migrations are wrapped in `DB::getDriverName()==='pgsql'`), SQLite `:memory:` in tests
(`phpunit.xml`). `.env` (git-ignored) locally uses `pgsql` with database-backed session/cache/queue. Money = `decimal(12,2)`; stock quantities = `decimal(12,3)`; leave days `decimal(5,1)`.
Migrations use idempotency guards (commit `1297216`) and are mostly reversible (`down()` present).

### 9.1 Table catalogue (PK is `id` bigint unless noted)

**Platform**
| Table | Purpose / notes |
|---|---|
| `users` | Accounts. `email` unique, `role` enum, `is_active`, `phone`, `employment_start_date/end_date`, `leave_policy_template_id` (nullable FK, bookkeeping only). |
| `password_reset_tokens` (PK `email`), `sessions` (PK string; indexes on `user_id`,`last_activity`), `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` | Laravel infrastructure. `sessions` purged daily by a scheduled closure. |
| `settings` | `key` unique, `value` text, `type` (string/boolean/integer/json), `group`, `label`. Read by `Setting::get()` **uncached** (one query per call). Known keys: `weekly_off_days`, `standard_working_hours_per_day`, `ot_multipliers`, `employee_overtime_requests_enabled`, `overtime_allowance_mode`. |
| `app_notifications` | In-app notifications; indexes `(user_id, read_at)`, `created_at`. |
| `audit_logs` | `user_id` (restrictOnDelete), `action`, `module`, `reference_id`, `reference_label`, JSON old/new, ip, ua; indexes on `(module, reference_id)`, `user_id`, `created_at`. Grows without bound. |

**Expenses / money**
| Table | Purpose / notes |
|---|---|
| `expense_categories` | name unique, `is_active`. |
| `vendors` | name, phone, `is_active`. |
| `expense_requests` | Core request. FKs: `expense_category_id` (**nullable since QR migration**), `vendor_id` (null on delete), `requested_by` (restrict), `approved_by`. `amount` 12,2. `priority` (nullable), `status` (Postgres CHECK: pending, pending_payment, approved, rejected, paid, reimbursement_pending, reimbursed, completed), `settlement_type` (direct_payment/wallet_deduction/reimbursement), `qr_file_path`, `whatsapp_sent_at`, `rejection_reason`. **No index on `status`, `requested_by` beyond FK behaviour, or `created_at`** despite heavy filtering (§25). |
| `expense_bills` | Bill files per request (cascade). |
| `expense_payments` | One payment record per settlement: `payment_mode` enum (cash/upi/bank_transfer/wallet), `transaction_reference`, `proof_file_path` (private disk), `paid_by`, `paid_at`. Relationship is `hasOne` on the model though the schema allows many. |
| `wallets` | `user_id` unique, `balance`. |
| `wallet_transactions` | `type` credit/debit/adjustment/reimbursement, balance_before/after, `expense_request_id` (null on delete). Append-only ledger. |
| `daily_closings` | `date` unique, `status` (draft/verified/closed), totals, opening/closing balance, `finalized_at`, `snapshot_captured`. Index `(date,status)`. |
| `daily_closing_expenses` / `_adjustments` / `_audits` | Snapshot lines / credit-debit adjustments / field-level history (`created_at` only). |

**Inventory**
`inventory_categories`, `inventory_items` (`sku` unique nullable, `unit` enum, `current_stock`, `minimum_stock`, `average_cost`, `status`), `inventory_transactions` (ledger: type purchase/usage/adjustment/wastage/transfer, polymorphic-ish `reference_type/reference_id`),
`inventory_stock_alerts` (`alert_type` low_stock/out_of_stock, `is_resolved`), `purchase_plans` (status string draft/approved/ordered/completed/cancelled), `purchase_plan_items`, `inventory_bill_uploads` (OCR upload: `status`, `file_hash`, `extracted_json`), `inventory_bill_items`.

**Hall / catering**
| Table | Notes |
|---|---|
| `halls` | name, capacity, `is_active`. |
| `meal_plans` | price_per_person; **soft deletes** (`deleted_at`). |
| `hall_bookings` | `hall_id` **nullable** (food_only), `booking_type` (default `hall_food`), `service_location`, customer fields, `booking_date`, `start_time/end_time`, `number_of_people`, `has_breakfast/lunch/dinner`, `hall_cost`, `total_amount`, `advance_amount` (**legacy: real payments are in `booking_payments`**), `payment_status` (pending/partial/paid), `status` (confirmed/cancelled/completed), `invoice_number` unique nullable, `invoice_date`, `cgst_rate`/`sgst_rate` (see §13.4), `uses_mixed_food`, `review_requested_at`. **No index on `booking_date`, `hall_id`, or `status`** despite being the main filter. |
| `hall_booking_meals`, `booking_payments`, `booking_additional_services`, `hall_booking_food_splits` | Children (cascade delete). `booking_payments.paid_at` is a **date**. |
| `event_request_menu_categories`, `event_request_menu_items`, `event_requests`, `event_request_items` (price snapshots), `event_request_revisions` (JSON snapshot history), `event_request_public_tokens` (`token` unique 64, `is_active`, `expires_at`, `revoked_at`) | Portal. `event_requests.hall_booking_id` links to the created booking. |
| `recipes`, `recipe_ingredients`, `recipe_sops` | Kitchen library. |
| `menu_items` (bilingual), `menu_drafts` (JSON `content`), `menu_templates` (JSON) | Menu Composer. `menu_items` are a *different concept* from `event_request_menu_items`. |
| `meal_clients`, `meal_entries`, `meal_entry_items` | Meal register (current). `daily_meal_entries`, `daily_meal_entry_items` = earlier iteration (§29). |

**HR**
`employee_salaries` (effective-dated, `effective_to` null = open), `holidays` (`holiday_date` unique), `leave_types` (`code` unique, `is_paid`, carry-forward), `employee_leave_policies` (per-user, effective-dated),
`leave_policy_templates` + `leave_policy_template_items` (unique per template/type), `employee_leave_allocations` (unique period key), `employee_leave_ledger` (signed amounts; polymorphic reference),
`leave_requests` (`paid_leave_days`, `lop_days`, `lop_confirmed`), `employee_attendance` (unique `(user_id, attendance_date)`, `status`, `half_day_period`, `source`, correction fields, `leave_request_id`),
`employee_attendance_segments` (unique `(user_id, attendance_date, period)`; per-half rows), `employee_attendance_regularizations`, `employee_overtime` (+ `approved_amount`, `used_manual_override`), `employee_overtime_configs` (unique user; JSON multipliers),
`employee_advances` (two-axis `request_status` × `payment_status`; cached `outstanding_amount`), `advance_transactions` (append-only ledger).

### 9.2 Entity relationships (logical)

```
User 1─1 Wallet 1─* WalletTransaction *─1 ExpenseRequest
User 1─* ExpenseRequest(requested_by) ; approved_by → User
ExpenseRequest *─1 ExpenseCategory ; *─1 Vendor ; 1─* ExpenseBill ; 1─(1) ExpensePayment
DailyClosing 1─* DailyClosingExpense ─(original_expense_id)→ ExpenseRequest ; 1─* Adjustment ; 1─* Audit

Hall 1─* HallBooking *─1 MealPlan ; HallBooking 1─* {HallBookingMeal, BookingPayment, BookingAdditionalService, HallBookingFoodSplit}
EventRequest 1─* {EventRequestItem, EventRequestRevision, EventRequestPublicToken} ; EventRequest ─(hall_booking_id)→ HallBooking
EventRequestMenuCategory 1─* EventRequestMenuItem
MealClient 1─* MealEntry 1─* MealEntryItem
Recipe 1─* RecipeIngredient ─?→ InventoryItem ; Recipe 1─* RecipeSop
InventoryCategory 1─* InventoryItem 1─* {InventoryTransaction, InventoryStockAlert} ; PurchasePlan 1─* PurchasePlanItem *─1 InventoryItem
InventoryBillUpload 1─* InventoryBillItem ─?→ InventoryItem

User 1─* {EmployeeSalary, EmployeeAttendance, EmployeeAttendanceSegment, LeaveRequest, EmployeeLeavePolicy, EmployeeLeaveAllocation,
          EmployeeLeaveLedger, EmployeeOvertime, EmployeeAdvance, EmployeeAttendanceRegularization} ; User 1─1 EmployeeOvertimeConfig
EmployeeAdvance 1─* AdvanceTransaction ; LeavePolicyTemplate 1─* Item ; User *─1 LeavePolicyTemplate
```

### 9.3 Database hazards
- **Postgres-only SQL** in migrations (`2026_05_15_130402…settlement`, `2026_05_16_100000…qr`, `2026_08_12_145528…gst default`) — all guarded by driver check, but they mean **SQLite (tests) and Postgres (prod) diverge**: e.g. `hall_bookings.cgst_rate/sgst_rate` default is `3.00` on SQLite and `2.50` on Postgres after the last migration; the status CHECK constraint exists only on Postgres. Tests cannot detect status values the CHECK would reject.
- `expense_requests.status` CHECK list must be updated (new raw migration) when adding a status; Laravel `enum()->change()` was abandoned (commit `d89b64a`).
- `hall_bookings.advance_amount` and `daily_closings.stock_*` fields look vestigial; verify before relying on them.
- `restrictOnDelete` on many `users` FKs means users with history cannot be hard-deleted; the app uses `is_active` instead.
- Audit/notification/ledger tables are unbounded — no pruning job exists.
- Two unrelated "menu item" concepts and two meal-entry table families exist (§29). Check which one a feature uses before editing.

---

## 10. Entity Relationships
See §9.2 (diagram) — kept next to the schema so they change together.

---

## 11. API Architecture

ExpenseFlow has **no JSON API layer**. It is server-rendered with form posts; some routes return JSON for in-page AJAX (calendar events, availability check, menu search/translate, daily-closing line edits, notification count, attendance gate 409). No API tokens, no `routes/api.php`, no Sanctum, no versioning.

### 11.1 Public / unauthenticated
| Method URL | Controller@method | Guard | Effect |
|---|---|---|---|
| GET `/up` | framework | none | health (200) — used by `health-check.sh` |
| GET `/`, `/login`, `/register`, `/forgot-password`, `/reset-password/{token}` | Auth controllers | `guest` | see §14 |
| GET `/pay/{id}` | `PaymentRequestController@show` | **signed URL** (30-day HMAC) | shows QR + status; staff controls only if session user is admin/manager |
| GET `/pay/{id}/qr` | `@serveQr` | signed | streams QR image from public disk with realpath containment + `image/*` MIME check |
| POST `/pay/{id}/mark-paid`, `/reject`, `/proof` | `@markPaid/@reject/@uploadProof` | **no route middleware**; role/Gate checked inside; CSRF via web group | mark paid (creates `expense_payments`, notifies requester), reject, upload proof (jpg/png/webp/pdf ≤5 MB → private disk) |
| GET `/pay-login?return=` | closure | none | stores `url.intended` only if `return` starts with `url('/pay/')`, then → login |
| GET `/event-request/{token}`, POST `/event-request/{token}` | `PublicEventRequestController` | token (`EventRequestTokenService::resolve`: active, not expired/revoked) | wizard / submit; invalid → 410 view |
| `public/storage/*` | Apache | symlink to `storage/app/public` | QR images are reachable by direct URL (used for og:image); file names random |

### 11.2 Authenticated, by prefix (all `auth`+`verified`)
| Prefix | Middleware | Controllers (namespace `App\Http\Controllers`) | Notes |
|---|---|---|---|
| `/admin/*` | `role.admin` | `Admin\*` (Employee, EmployeeSalary, Category, Vendor, ExpenseRequest, Overtime, Attendance, AttendanceRegularization, Advance, LeaveType, LeavePolicy, LeavePolicyTemplate, Leave, Wallet, Payment, Report, Analytics, AuditLog, DailyClosing, Setting, Purchase*, Inventory\*) | ~285 `Route::` definitions in `routes/web.php` (resource routes expand further); PATCH actions for state changes |
| `/manager/*` | `role.manager` (admin OR manager) | `Manager\*` | expense approve/reject, overtime, regularizations, advances (incl. disburse/repayment), leave |
| `/employee/*` | `attendance.marked` | `Employee\*` | expenses, attendance, regularizations, advances, leave, overtime, wallet, hall calendar, kitchen calculator |
| `/hall/*`, `/kitchen/*`, `/admin/event-requests*`, `/admin/event-request-menu*` | `role.hall` | Hall\*, Kitchen\*, Admin\EventRequest\* | |
| `/menu/*` | `role.admin` | `Menu\*` | `POST /menu/pdf` streams a PDF |
| `/meal-register/*` | any auth (+`role.hall` on client/entry mutations) | `MealRegister\*` | |
| `/notifications*` | any auth | `NotificationController` | `GET notifications/count` returns JSON (consumer not found in Blade) |
| `/profile` | `auth` only (no `verified`) | `ProfileController` | includes account **destroy** |

Side effects worth remembering: expense settlement (wallet debit + payment row + audit + notification in one transaction), event approval (creates `hall_bookings`), inventory bill import (stock movements), leave approval (ledger + attendance rows), overtime approval (amount calc), advance disburse (ledger).
External calls from any endpoint: **none** other than local child processes (OCR, Chrome, ghostscript).

To list the exact current routes: `php artisan route:list` (do not trust a static copy).

---

## 12. Frontend Architecture

- **Rendering:** Blade only; no SPA. Interactivity is inline `<script>` blocks in Blade (hence CSP `'unsafe-inline'`), `fetch()` in ~11 views, Chart.js (`hall/reports`), FullCalendar (`hall/bookings/calendar`).
- **Two coexisting asset strategies:**
  1. **Primary UI** — `resources/views/components/admin-layout.blade.php` (used via `<x-admin-layout>` by ~135 views) loads **Bootstrap 5.3.3 + Bootstrap Icons from `cdn.jsdelivr.net`** and contains a large inline style/script block; registers `/sw.js` (line ~1583). Guest pages use `<x-guest-layout>` (`layouts/guest.blade.php`, also CDN Bootstrap).
  2. **Vite bundle** — `resources/css/app.css` (Tailwind directives + the `--ef-*` design tokens + ~4.3k lines of `ef-*` classes) and `resources/js/app.js`, `event-request.scss`, `event-request-public.js`. Vite inputs are the four in `vite.config.js`. `resources/scss/hall-dashboard.scss` is **not** a Vite input (grep found no reference — **LIKELY UNUSED**).
- **Build output is committed** (`public/build/`, `.gitignore` explicitly un-ignores it; commits `14d9259`, `85caabd`). Production has no Node; `deploy.sh` *verifies* the committed manifest and every referenced asset are tracked. **Any change to `resources/css|js|scss` requires `npm run build` locally and committing `public/build/`.**
- **PWA:** `public/sw.js` (cache `ef-v1`; stale-while-revalidate for static assets, network-first HTML with `/offline.html` fallback; bypasses login/register/pay POST paths). Comment says *bump `CACHE_VERSION` on every deploy* — nothing automates that. `public/site.webmanifest` (standalone).
- **Components** (`resources/views/components/`): `ds/{card,hero,kpi-card,section-head}` (used by ~79 files), `premium/{card,chip,field}`, `status-badge`, `priority-badge`, `overtime-status-badge`, `booking-type-badge`, `mobile-nav`, `modal`, `dropdown*`, form primitives (Breeze), `kitchen/*`, `event-request/*`, `errors/*`. Error pages: 403/404/419/429/500/503 (`errors/503.blade.php` is used by `deploy.sh` via `artisan down --render=errors.503`).
- **Design language (real, from `app.css` `:root` and `docs/hospitality-design-system.md`):** warm off-white `--ef-bg #f6f4ef`, surface `#fffdfa`, ink `#141412`, muted `#77736a`, border `#e7e1d8`; brand gold `#B8893E`, emerald `#0F7B5F` (hover `#0D9E78`, dark `#0D5C43`), amber `#D89A3D`, danger `#C84B44`, info `#2F6FED`; radii 14 px (cards) / 10 px (controls); soft shadows; font stack Inter → system UI (Tailwind config still lists Figtree — inconsistent); dark emerald "hero" gradients. Prefixes: `ef-*` classes, modifiers like `.ef-btn-xs.--danger`, `.ef-ds-btn.--primary`. Status colours via model helpers (`ExpenseRequest::statusColors()` etc.) mapped to Bootstrap contextual names (incl. non-Bootstrap `teal`).
  Guidance: reuse `--ef-*` tokens and existing `ef-*`/`x-ds.*`/`x-premium.*` components; do not add a new CSS framework or palette.
- **Mobile:** mobile-first work is explicit in history (bottom nav, iOS safe-area, Android `overflow-x: clip` scroll fix in `app.css`). Tests `AdminMobileLayoutTest`, `KitchenCalculatorMobileTest` guard some of it. Always check ≤ 390 px width for UI changes.
- **Vestigial Breeze layer:** `layouts/app.blade.php` + `layouts/navigation.blade.php` + `App\View\Components\AppLayout` (no `<x-app-layout>` usages found), `resources/views/dashboard.blade.php`, `welcome.blade.php` (§29).

Trace example — Hall calendar: `GET hall/bookings/calendar` → `HallBookingController@calendar` → `hall/bookings/calendar.blade.php` (FullCalendar from CDN) → `fetch(route('hall.bookings.calendar-events'))` → `@calendarEvents` (JSON; financial fields stripped for employees) .

---

## 13. Business Rules

Format: **RULE** — *where implemented*.

### 13.1 Expense lifecycle
Statuses: `pending`, `pending_payment`, `approved`, `rejected`, `paid`, `reimbursement_pending`, `reimbursed`, `completed` (`ExpenseRequest`; DB CHECK on Postgres).
- New request → `pending_payment` (`ExpenseRequestService::create`).
- `isSettled()` = status ∈ {paid, reimbursement_pending, reimbursed, completed}. Any settle action on a settled request → `RuntimeException('Request already settled.')` (`ExpenseSettlementService`).
- `settleViaWallet`: debit wallet by `amount` (fails if insufficient), record `wallet` payment, status `paid`, `settlement_type=wallet_deduction` — one transaction.
- `settleViaDirect`: record payment, status `paid`, `direct_payment`.
- `markReimbursementPending`: status `reimbursement_pending`, `settlement_type=reimbursement`.
- `reimburse`: only from `reimbursement_pending`; records payment; **credits the wallet as a record** for non-admin requesters (`WalletService::recordReimbursement`); status `reimbursed`.
- `markCompleted`: route runs `authorize('markCompleted')` (admin **and** status `paid`) *before* the service, which would also accept `reimbursed` — so via the UI only `paid` requests can be completed; `reimbursed` ones cannot.
- Public pay page `markPaid`: requires Gate `markPaid` (admin/manager, status approved|pending_payment), not already settled; sets `paid`, `direct_payment`, `approved_by/at`, creates payment (mode default `upi`); `payment_mode=wallet` is accepted there but **does not debit the wallet** (§24).
- Reject: policy `approve/reject` need `pending`; pay-page reject needs not settled & not rejected and a reason of 3–300 chars.

### 13.2 Wallet
Balance changes only via `WalletService` under row lock; every change writes a `wallet_transactions` row with before/after. `Wallet::isLow()/isNegative()` drive UI hints; `debit` also raises a low-balance notification (`WalletService` ~line 110).

### 13.3 Inventory
Stock changes only via `InventoryService` (row lock). `deductStock` throws when `before < quantity`. Alerts created when stock ≤ minimum (`checkAndCreateAlerts`), resolved when restocked. Average cost recomputed on purchases.

### 13.4 Hall bookings & invoices
- Conflict check applies **only** to `hall_only`/`hall_food`; `food_only` never conflicts. Overlap = `whereBetween(start)`, `whereBetween(end)`, or enclosing — **inclusive**, so back-to-back slots (end == start) conflict (`HallBookingController@store/update/checkAvailability`).
- Booking `status` validation: `confirmed|cancelled|completed`; `payment_status`: `pending|partial|paid`.
- `addPayment`: creates `booking_payments`; recomputes `payment_status` (`paid` if Σpayments ≥ `total_amount`, `partial` if > 0, else `pending`); if `paid` and status `confirmed` → `completed`. (A branch for status `pending` is dead — `pending` is not an allowed status.) Not wrapped in a transaction; overpayment, and payments on cancelled bookings, are not blocked.
- `HallBooking::balance_amount` = 0 if cancelled else `total_amount − Σpayments` (pre-tax).
- Meal cost = Σ food splits if `uses_mixed_food`, else `meal_plan.price_per_person × number_of_people`.
- Invoice (`InvoiceCalculationService::calculate`): `subtotal = total_amount`; `CGST = subtotal×cgst_rate/100`, `SGST` likewise; `grand_total = subtotal + tax`; `balance_due = max(0, grand − Σpayments)`. Rates: booking column, fallback constant `2.50/2.50`. Note `balance_amount` (pre-tax) ≠ invoice `balance_due` (post-tax) — different numbers on different screens by design/accident (**INFERRED** which is intended).
- Invoice number: manual entry, unique, regex `letters/numbers/- / _ .`; fallback display `INV-####` from id (`effective_invoice_number`). Booking reference `BK-####`.

### 13.5 Event requests
Statuses (`EventRequest::statuses()`): draft, submitted, under_review, need_changes, resubmitted, approved, rejected, scheduled. Flow: draft → submitted → under_review → (need_changes ⇄ resubmitted) → scheduled | rejected. `approved` is declared but **never set** (`approve()` sets `scheduled`). The client may edit/submit only in `EventRequest::editableStatuses()` = **draft, need_changes** (not `resubmitted`).
`submitFromClient` reprices from *current* menu item prices and stores snapshots; admin edit flips `submitted → under_review`; `approve` requires policy `decide`, creates a `food_only` booking with fixed meal-time windows (breakfast 07–09, lunch 12–15, dinner 19–22, reception 18–21, high_tea 16–18, default 10–13), `total_amount = estimated_total`, `payment_status=pending`, status `confirmed`. `reject` deactivates tokens. Regenerating a link revokes active tokens. **Approval does not check hall/kitchen availability or duplicates.**

### 13.6 Daily closing
`draft → verified → closed`; finalized closings are immutable (all edit routes return 422/redirect error); only non-finalized *draft* closings can be deleted; snapshot pulls expense requests whose status ∉ {pending, rejected} for the date. One closing per date (unique).

### 13.7 Attendance (`EmployeeAttendanceService` header is the authoritative spec)
- "Today" is server-side in `Asia/Kolkata`; clients never send dates.
- Holidays/weekly-offs are **not markable**; work on them goes through Overtime.
- Display priority: Holiday > Weekly Off > attendance row > approved leave > pending leave > Not Marked.
- Employee attendance gate: for role `employee`, any `employee/*` route except `employee.attendance.*`, `employee.attendance-regularizations.*`, `employee.leave.*` redirects to attendance until today is marked when `needsAttendanceToday()`; JSON callers get HTTP 409 with a redirect hint. Admins/managers are exempt.
- Half-day support via `employee_attendance_segments` (first/second half) and `half_day_period`.
- Regularization: only `present|half_day`; rejected at submit **and** approve time for holiday/weekly-off or dates covered by approved/pending leave; approval atomically updates attendance.

### 13.8 Leave
`LeaveService` (paid/LOP split; balances from the **ledger** via `LeaveBalanceService`; day counts from `PayableDaysCalculator`). If days > paid balance the first submit fails validation until `lop_confirmed=1`. Statuses pending/approved/rejected/cancelled; approve/cancel write ledger + attendance. Allocation via `LeaveAllocationService`/artisan `leave:generate-allocations` (idempotent via unique constraint). Templates (`LeavePolicyTemplate`) are copied into per-user `employee_leave_policies`; only the latter are read by services.

### 13.9 Overtime
Employee request creation is **disabled by default** (`employee_overtime_requests_enabled=0`). Approval takes a multiplier (from the employee's `EmployeeOvertimeConfig.allowed_multipliers`) or a manual override amount; `hourly = (salary ÷ applicable working days in the OT month) ÷ standard_working_hours_per_day`; `approved_amount` is what payroll adds. `overtime_allowance_mode='single'` limits admin-recorded OT to one per employee per month.

### 13.10 Payroll / advances
`MonthlyPayableService`: `payable_salary = Σ(segment daily rate × payable days)`, `daily rate = salary ÷ applicable working days`, `+ approved OT = net_payable`. **No deductions** for advances (deliberately; see class docblock). Payable-day weights: present 1, half_day 0.5, leave 1 (paid), half_day_leave 0.5, lop 0, half_day_lop 0.5, absent 0; holidays/weekly-offs excluded from the denominator. Salary rows must be contiguous/non-overlapping (`EmployeeSalaryService`).
Advances: `eligible = max(0, earned_salary − previous_repaid_advances − outstanding)` (`AdvanceEligibilityService`); one pending request at a time; `outstanding_amount` is a cached column recomputed from `advance_transactions` (advance − recovery) in the same transaction. There is **no rejection-reason column** (documented limitation). Known documented gap: approved-but-undisbursed advances are not counted in eligibility.

### 13.11 Event/Meal/Menu misc.
Meal entries are unique per client+date; items unique per entry+meal_type (`breakfast|lunch|evening_snacks|dinner`). Menu PDFs need Chrome for correct Tamil; dompdf cannot shape Tamil (comment in `MenuComposerController`).

---

## 14. Authentication & Authorization

- Session auth (Breeze controllers, `web` guard, `users` provider). Local `.env`: `SESSION_DRIVER=database`, `SESSION_LIFETIME=43200` (30 days), `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=false`; `.env.example` differs (120 min, no encrypt) — **production values REQUIRES-PRODUCTION-VERIFICATION**.
- **Open self-registration:** `GET/POST /register` is enabled for guests (`RegisteredUserController@store`, throttle 10/min). New accounts get role `employee`, `is_active` true (DB defaults) and are auto-logged-in. See §24 (High).
- **`is_active` is not enforced at login or by middleware** (no reference in Auth controllers/`LoginRequest`/middleware). It is enforced only by specific policies (`ExpenseRequestPolicy::create`, advance/regularization/overtime policies, `UserPolicy`). A deactivated employee can still authenticate and reach non-policy-gated pages (**verify with a manual test before relying on either behaviour**).
- Password reset & email verification routes exist; users do not implement `MustVerifyEmail`, so `verified` never blocks.
- Authorization layers: route middleware (role), `authorize()`/`Gate` policies, inline role checks (public pay page), and service-level guards. Always check all layers when adding a route.
- Seeded accounts: `AdminSeeder` and `UserSeeder` hash the literal password `password` (§24 S17). Do not run them outside local development.

---

## 15. Queue & Jobs

| Item | Fact |
|---|---|
| Job classes | **None.** `grep` for `dispatch(`, `ShouldQueue`, `Mail::`, `Notification::send` finds nothing in `app/` or `routes/`. |
| Queue config | `config/queue.php` default from `QUEUE_CONNECTION` (`database`; `.env.example` says use `redis` in production). Tables `jobs`, `job_batches`, `failed_jobs` exist. `sync` in tests. |
| Production workers | Supervisor programs named `expenseflow-worker:expenseflow-worker-N_00` exist and run `queue:work` from `/var/www/akshathayexpense` (memory note + `deploy.sh` checks). Exact command line, queue names, timeout, numprocs, user: **REQUIRES-PRODUCTION-VERIFICATION** (`deployment/legacy-release-based/supervisor.conf` is a *retired template*, not proof). |
| Deploy interaction | `deploy.sh` restarts every `expenseflow*` supervisor program after each deploy and health-check waits for workers restarted since deploy start. |
| Consequence | Workers are currently idle. Anything slow (OCR, PDF) runs **synchronously in the web request** — `OCR_TIMEOUT` (default 60 s in `config/ocr.php`) must be below Apache/PHP limits. Adding the first queued job requires deciding queue name, retries, timeout, failed-job handling and confirming the production worker command (see §40 Phase 3). |

---

## 16. Scheduler / Cron

Defined in [routes/console.php](routes/console.php):

| Schedule | Command / closure | What it does |
|---|---|---|
| every 2 h | `app:check-stock` (`CheckLowStock`) | for active critical items → `InventoryService::checkAndCreateAlerts` using the **first admin user** as actor (errors if none) |
| daily 21:00 | `app:daily-summary` (`SendDailySummary`) | in-app notification to admins: today's expense count/total, pending count, unresolved low-stock alerts |
| 10:00 & 16:00 | `app:remind-pending` (`RemindPendingApprovals`) | notify managers of `pending` expenses older than 24 h |
| hourly | `app:cleanup-temp-qr` (`CleanupTempQr`) | deletes `temp-qr/*` older than **48 h** (description/comment say 24 h) and nulls DB paths for missing QR files; never touches `qr-codes/{id}/` |
| daily 01:00 | `leave:generate-allocations` | idempotent allocation generation (chunks of 100 users with active leave policies) |
| daily 03:00 | closure `sessions:gc` (`withoutOverlapping`) | deletes `sessions` older than `SESSION_LIFETIME` minutes |

Unscheduled commands: `db:health-check [--fix] [--show-ok]` (schema check; `--fix` runs `migrate`), `menu:install-font [--force]`.
All times use the app timezone, which is **UTC** (`config/app.php`): `21:00` = 02:30 IST, `10:00/16:00` = 15:30/21:30 IST, `01:00` = 06:30 IST, `03:00` = 08:30 IST (unless the server/PHP overrides it — unlikely). **Whether a `schedule:run` cron exists on production is UNKNOWN / REQUIRES-PRODUCTION-VERIFICATION**; `deploy.sh` only *warns* if a cron runs artisan as root. Because new requests are created as `pending_payment`, `remind-pending` and the "pending" figure in `daily-summary` only concern legacy `pending` rows.

---

## 17. Storage / Files

Disks (`config/filesystems.php`): `local` → `storage/app/private` (`serve=true`); `private` → `storage/app/private`; `public` → `storage/app/public` (linked at `public/storage`, created/repaired by `deploy.sh`); `s3` defined, unused.

| Content | Disk / path | Access control |
|---|---|---|
| Expense QR images | `public`: `qr-codes/{expenseId}/…`; legacy `temp-qr/` | signed route `pay/{id}/qr` **and** reachable directly at `/storage/qr-codes/…` (used for og:image) |
| Payment proofs | `local`(=private): `payment-proofs/{id}/{random}.ext` | `pay/{id}/proof` GET, admin/manager only |
| Inventory bill scans | `private`: `inventory-bills/…` | `admin.inventory.bills.file` (role.admin) |
| Expense bills | `public`: `bills/{id}` (`FileUploadService`) | no active route (LIKELY UNUSED) |
| Event menu item images | `event_request_menu_items.image_path` is a free-text string entered by admin (`MenuItemRequest` validates `string`); no upload code; rendered as `asset('storage/'.$image_path)` | whatever is placed in `storage/app/public` |
| dompdf fonts | `storage/fonts` — **tracked in git**, rewritten at runtime | `deploy.sh` treats edits under `storage/fonts` as benign |
| Letterhead cache | `storage/app/menu_letterhead_cache.jpg` (git-ignored via `storage/app/.gitignore`) | |
| OCR script | `storage/app/ocr/invoice_ocr.py` (tracked) | executed by `PaddleOCRService` |
| Logs | `storage/logs`, deploy logs in `/var/log/expenseflow-deploy/` (root) | |

Backups of `storage/app` and the database: **UNKNOWN / REQUIRES-PRODUCTION-VERIFICATION** (the retired cron template had a `pg_dump` job; nothing proves it runs).

---

## 18. External Integrations

| Service | Purpose | Config | Auth | Calling code | Failure/retry |
|---|---|---|---|---|---|
| WhatsApp share link | Employee shares payment page | none | none (client-side navigation to `api.whatsapp.com/send?text=`) | `ExpenseRequest::whatsAppUrl()` | n/a; no API, no webhook, no delivery tracking |
| cdn.jsdelivr.net | Bootstrap, Bootstrap Icons, Chart.js, FullCalendar, Alpine (browser) | CSP allows this origin only | none | layouts / views | If the CDN is blocked the UI loses styling (no local fallback) |
| Python OCR (PaddleOCR/Tesseract stack) | Parse supplier bills | `config/ocr.php`: `OCR_PYTHON_BIN`, `OCR_LANGUAGE`, `OCR_TIMEOUT`, `OCR_SCRIPT_PATH` | local process | `PaddleOCRService` (`proc_open`, output JSON extraction, timeout kill) → `InvoiceOCRService` | returns failure array; bill stays re-runnable (`bills.rerun-ocr`); **installation on prod: REQUIRES-PRODUCTION-VERIFICATION** (`storage/app/ocr/INSTALL.md`) |
| Headless Chrome | Tamil-correct menu PDF | binaries probed in `MenuComposerController::chromeBin()` | local | `exec()` | Falls back to dompdf with a warning |
| Ghostscript | Letterhead PDF → JPEG | `config/menu.php` | local | `MenuLetterheadService` (`exec`, `shell_exec('which …')`) | logs and returns null (no letterhead) |
| Brevo SMTP | Transactional mail | only in `.env.example` comments | — | **no code sends mail** | — |
| AWS S3 / SES / Postmark / Resend / Slack | boilerplate in `config/services.php`, `filesystems.php` | unused | — | none | — |

Font download (`menu:install-font`) fetches from the network at run time — inspect `InstallMenuFont.php` before running on a server.

---

## 19. Payment Architecture

There is **no payment gateway**. "Payment" means *recording* that a person paid (UPI QR shown to staff, cash, bank transfer).
Flow: employee creates request with QR image → shares WhatsApp link → staff opens `/pay/{id}` (signed) → scans QR in their UPI app → logs in (via `pay-login` helper, session may be missing inside WhatsApp's in-app browser) → **Mark as paid** (+ optional reference/note) and/or **upload proof** → `expense_payments` row, status `paid`, requester notified.
Payment verification is **manual**: the app never confirms money movement with a bank/UPI provider. No webhooks, no callbacks, no idempotency keys (replay protection = `isSettled()` check).
Hall booking payments (`booking_payments`) and advance repayments (`advance_transactions`) are also manual ledger entries.

---

## 20. Notifications

`NotificationService` writes `app_notifications` (`type`, `title`, `body`, `link`, JSON `data`, `read_at`). Types seen: `expense_approved`, `expense_rejected`, `expense_settled`, `expense_paid`, `low_stock`, `wallet_low`, daily summary, pending reminders. UI: `notifications.index`, `notifications.count` (polled), read/read-all. No email, SMS, push, or WhatsApp delivery.

---

## 21. Deployment Architecture

### 21.1 INTENDED architecture (what the repo's *current* scripts target) — FLAT + Apache
```
Apache 2.4 → DocumentRoot $EF_APP_DIR/public   (default /var/www/akshathayexpense, a git working tree)
Supervisor programs expenseflow* → artisan queue:work from the same tree
PHP handler: mod_php or php-fpm — both supported by reload logic
```
Files: `deployment/deploy.sh` (537 lines), `rollback.sh`, `health-check.sh`, `lib.sh` (349), `diagnose-server.sh` (read-only), `tests/run-tests.sh` (+ fakes), `DEPLOYMENT_CHECKLIST.md`, `STALE_DIRS_CLEANUP.md`.
**These files are committed in `cd9227e` (2026-09-19, VERIFIED-REPO).** Whether the server's checkout already contains that commit is `REQUIRES-PRODUCTION-VERIFICATION` (the first server-side `git checkout` brings the new scripts — see the checklist "Rollout"). Also committed: `resources/views/errors/503.blade.php`, `deployment/tests/sigreset.py`, and a legacy `deployment/.env.production.example` (see §23/§29).

### 21.2 Deploy flow (as implemented in `deploy.sh`)
0. Args: `[branch=main] [--check] [--commit SHA] [--force] [--maintenance] [--discard-local-changes] [--fix-ownership] [--skip-system-checks] [--no-http-health]`. Must run as root (drops to `www-data` for composer/artisan via `runuser`).
1. Copy `lib.sh`+`health-check.sh` to a temp dir (health check uses the same script version as the deploy); log to `/var/log/expenseflow-deploy/deploy-<ts>.log`.
2. **Acquire lock**: `flock -n` on `/var/lock/expenseflow-deploy.lock` (fd 200; never delete the file). Ignores SIGTSTP/TTIN/TTOU; INT/TERM/HUP abort via `on_exit`.
3. **Preflight (fail-closed)**: tools present; PHP 8.x, Composer probe; `.env` readable; repo root == app dir; `origin` reachable; `.git/index.lock`; interrupted-deploy marker; dirty tracked files (only `storage/fonts/*` tolerated unless `--discard-local-changes`, patch saved first); ownership/writability of `storage`, `bootstrap/cache`, `vendor`; **Apache active, `apache2ctl configtest` OK, DocumentRoot for the `APP_URL` host == `$APP/public`**; supervisor programs run from this tree; DB reachable (`migrate:status --pending`); `public/build/manifest.json` complete; `public/storage` symlink; leftover maintenance; conflicting processes; cron/systemd triggers; ≥512 MiB free. `--check` stops here (read-only).
4. `git fetch --prune origin +refs/heads/<branch>`; resolve target; verify target commit contains `artisan`, `composer.lock`, `public/build/manifest.json` and that every manifest entry/asset is **tracked**.
5. Decide maintenance: needed if composer files or `database/migrations/` changed (or `--maintenance`). Warns to **back up the DB first** when migrations changed.
6. Write in-progress marker; optional `--fix-ownership`; create runtime dirs; `artisan down --retry=60 --render=errors.503` if needed.
7. `git checkout -f -B <branch> <sha>` (no `git clean`); re-own tracked runtime files.
8. `composer install --no-dev --optimize-autoloader --prefer-dist` as `www-data`; ensure `public/storage`; `config:cache route:cache view:cache event:cache` (all required).
9. `migrate:status --pending` → if pending: enter maintenance, `migrate --force` (`EF_MIGRATE_TIMEOUT` 900 s). After migrations: **no automatic code rollback**.
10. `apache2ctl configtest` + `systemctl reload apache2` (+ php-fpm units if active); `supervisorctl restart` each `expenseflow*` program.
11. `artisan up` if it entered/adopted maintenance.
12. Health check (`health-check.sh --expect-commit … --wait-workers 90`): HEAD==commit, build assets, DB, caches, `GET /up` 200, `/login`, served `manifest.json` hash equals repo, workers restarted after deploy start.
13. Append `history.tsv`; print rollback hint.
Failure handling: before migrations → `restore_previous` (checkout previous commit, rebuild, reload, health check); after migrations → hold maintenance and print options. No node/npm on the server; no releases pruning (there are no releases).
**Not done by the script:** database backup, cache/queue clearing beyond `*:cache`, `queue:restart` (uses supervisor restart instead), certbot, OS packages, scheduler cron setup.

### 21.3 Rollback (`rollback.sh`)
`--list`, `--to <sha>`, `--yes`; default target = previous commit from `history.tsv`. Re-runs `deploy.sh --commit <sha> --force --rollback-mode`. **Never reverts migrations.**

### 21.4 Retired design (do not use)
`deployment/legacy-release-based/` (`bootstrap.sh`, `cleanup.sh`, `nginx.conf`, `supervisor.conf`, `cron.d.expenseflow`, README): `releases/<ts>` + `current` symlink + nginx + dedicated php-fpm. `cd9227e` moved the old top-level copies into that folder (renames). Stale directories `current/ releases/ shared/ repo/` may still exist under the production app dir (`STALE_DIRS_CLEANUP.md`); scripts never touch them.

---

## 22. Production Server Architecture (what is proven vs. not)

**`PROD-VERIFIED` (maintainer notes 2026-09-19: outside probing of the live site + earlier console work; not re-checked in this review):** host `expense.akshathay.com`; DNS A → `168.144.117.206` (an earlier IP was stale); Apache 2.4.58 (Ubuntu); code = flat git tree `/var/www/akshathayexpense`, DocumentRoot `…/public`; live `/build/manifest.json` hash equalled commit `7068cb0`; DB PostgreSQL; PHP 8.3.6; Composer 2.7.1; supervisor programs `expenseflow-worker:expenseflow-worker-N_00`.
**`REQUIRES-PRODUCTION-VERIFICATION` / `UNKNOWN`:** PHP handler (mod_php vs fpm); queue/cache/session drivers in prod `.env`; Redis presence; DB name/user/backups; cron for `schedule:run`; TLS/certbot; log rotation; whether Python OCR, Chrome, ghostscript are installed; whether the deploy scripts have ever run end-to-end on the real server (they were tested only in a sandbox with fake `apache2ctl/systemctl/supervisorctl/sudo` — memory note + `deployment/tests`); the contents of `.env` on the server.
Run `sudo bash deployment/diagnose-server.sh` (read-only) and `deploy.sh main --check` before changing anything.

---

## 23. Configuration

Runtime config is `.env` (git-ignored; never print values). Keys present in `.env.example`: `APP_*`, `LOG_*`, `DB_*` (sqlite default; pgsql example), `SESSION_*`, `QUEUE_CONNECTION`, `CACHE_STORE`, `BROADCAST_CONNECTION`, `FILESYSTEM_DISK`, `REDIS_*`, `MAIL_*`, `AWS_*`, `VITE_APP_NAME`. Additional keys consumed by code: `OCR_PYTHON_BIN`, `OCR_LANGUAGE`, `OCR_TIMEOUT`, `OCR_SCRIPT_PATH`, `MENU_LETTERHEAD_PATH`, `SESSION_COOKIE/SECURE_COOKIE/HTTP_ONLY/SAME_SITE`, `LOG_DAILY_DAYS`. Custom config files: `config/ocr.php`, `config/menu.php`, `config/menu_categories.php`. **`deployment/.env.production.example` is a LEGACY template** (VERIFIED-REPO, from commit `52133e6`, release-layout paths `/var/www/expenseflow/shared/…`): it recommends redis for cache/session/queue, Brevo SMTP, `SESSION_SECURE_COOKIE=true`, and lists keys **no code reads**: `APP_TIMEZONE` (config timezone is hard-coded `'UTC'`), `OCR_ENABLED`, `OCR_TESSERACT_BIN`, `OCR_TEMP_DIR`, `ASSET_MODE`, `SANCTUM_STATEFUL_DOMAINS`, `DB_SCHEMA`, `LOG_DAYS`, `MAIL_ENCRYPTION` (grep of `app config routes resources bootstrap`). Setting them has no effect; it is not evidence of production's actual `.env`. Deploy-script env overrides: `EF_APP_DIR, EF_APP_USER, EF_LOG_DIR, EF_LOCK_FILE, EF_COMPOSER_HOME, EF_COMPOSER_TIMEOUT, EF_MIGRATE_TIMEOUT, EF_WORKER_WAIT, EF_ALLOW_NONROOT, EF_SKIP_SYSTEM_CHECKS, EF_HEALTH_HTTP_BASE, EF_WEBSERVER`.
Runtime-tunable business settings live in the `settings` table (§9.1) and are edited at `admin.settings.*`.
`APP_URL` must match the Apache vhost host: `deploy.sh` derives the site host from it.

---

## 24. Security

Severity: H/M/L. "Action" = recommendation only; nothing was changed.

| # | Sev | Location | Why it matters | Current behaviour | Recommended future action |
|---|---|---|---|---|---|
| S1 | **H** | `routes/auth.php` `register`; `RegisteredUserController@store` | Anyone on the internet can create an account and log in as `employee` with access to employee pages (kitchen calculator, hall calendar, meal-register views, wallet/attendance pages, expense creation and the QR upload path). For an internal ERP this is unintended exposure. | Open; throttled 10/min | Confirm intent with owner; if unintended, remove/guard the route (admin-created users already exist via `admin.employees.*`). Needs a test. |
| S2 | **H** | `AuthenticatedSessionController`/`LoginRequest`/middleware | `is_active=false` users can still log in; only some policies check it. | No login-time check found | Verify manually; if confirmed add a check in login + a middleware; add test. |
| S3 | M | `/pay/{id}/mark-paid`, `/reject`, `/proof` | Routes have no `auth` middleware by design; controller checks role. Correct today but a future edit that drops the in-controller check would expose payment actions. Also `payment_mode=wallet` accepted without wallet debit (data inconsistency). | Role/Gate checks inside each method | Keep checks; add regression tests; reject `wallet` mode on this route. |
| S4 | M | `serveProof` | Any admin/manager can fetch any expense's proof by id (no per-object check). Acceptable for role model; note enumeration by integer id. | role check only | Accept or scope; log access. |
| S5 | M | `SecurityHeadersMiddleware` | CSP has `script-src 'unsafe-inline'` (inline Blade scripts) — weak XSS mitigation. `$isRelaxed` computed but unused. | as described | Long-term: nonces; remove dead variable. |
| S6 | M | `/event-request/{token}` | Public endpoint with 24-char random token, no rate limit; submissions create/overwrite request items. Token entropy is fine; brute force is impractical but the POST has no throttle/CAPTCHA. | none | Add `throttle`; keep 410 for invalid. |
| S7 | M | QR images | `qrOgImageUrl()` exposes `/storage/qr-codes/...` publicly (documented trade-off; random filenames). | public disk | Accept; do not store anything sensitive in that path. |
| S8 | M | `exec`/`proc_open`/`shell_exec` uses | Chrome/ghostscript/OCR: arguments are `escapeshellarg`/`escapeshellcmd`-protected and paths come from config or temp files; HTML for Chrome is built from user-editable menu content and rendered with `--allow-file-access-from-files --no-sandbox`, so hostile menu HTML could read local files into the PDF. Only admins reach `menu/*`. | as described | Sanitise/escape menu content; consider `--allow-file-access-from-files` removal. |
| S9 | M | `.claude/settings.json` (tracked in git) | Large allow-list of shell permissions (includes `chmod`, `nginx -t`); not app-runtime but affects agent safety and reflects the retired nginx era. | present | Review/prune. |
| S17 | **H if seeded in prod** | `database/seeders/AdminSeeder.php`, `UserSeeder.php` | Seed users are created with the password `password` (`AdminSeeder`: admin@/manager@/employee@expenseflow.com with roles admin/manager/employee; `UserSeeder`: the same three emails). If either seeder ever ran on production those accounts are guessable. | Not known whether run | REQUIRES-PRODUCTION-VERIFICATION: check `users` for the seeded emails; rotate/disable. Never run seeders in production. |
| S10 | M | Deploy scripts | Run as root; use `runuser`; no `chmod`; `chown` scoped to 3 trees; `git checkout -f`. Only sandbox-tested. | see §21 | Run `--check` first; never run legacy scripts. |
| S11 | L | Sessions | 30-day lifetime locally (production unknown); `SESSION_SECURE_COOKIE=false` locally. | env | Verify prod `.env`: secure cookie true under HTTPS. |
| S12 | L | Mass assignment | `User` protected; other models use explicit `$fillable` (spot-checked `ExpenseRequest`, `HallBooking`). Not all ~70 models reviewed. | — | Review new models. |
| S13 | L | SQL injection | Raw SQL uses bindings or static strings (checked `orderByRaw`, `selectRaw` sites); `selectRaw` uses Postgres-only functions (`TO_CHAR`, `EXTRACT`). No user-concatenated SQL found. | — | Keep bindings. |
| S14 | L | XSS | Blade `{{ }}` default escaping; `{!! !!}` found in 4 files (one is `json_encode` of a filter value in `menu/composer/index.blade.php` — verify the others). | — | Audit the 4 files. |
| S15 | L | CSRF | Global `web` group; the sole intentional exemptions do not exist (pay POSTs still need a CSRF token). | — | — |
| S16 | L | `.env` in repo dir | `.env` is git-ignored and was **not** printed during this analysis. | — | — |

Credentials/secrets: no real secrets found committed; seeders contain the default password `password` (S17). `ef-diagnosis.txt` is an empty untracked file (`git status`).

---

## 25. Performance (evidence from code only; no measurements exist)

- **Missing indexes (from migrations):** `hall_bookings` has no index on `booking_date`, `hall_id`, `status`, `payment_status` while dashboards, calendar, availability and kitchen queries filter on them (`HallBookingController`, `HallDashboardController`). `expense_requests` has none on `status`, `requested_by` (FK constraint only; Postgres does **not** auto-index FKs), `created_at`, though reports/dashboards filter/group by them (`ReportController`, `AnalyticsController`, `SendDailySummary`, admin index summary `SUM(CASE WHEN status…)`). `booking_payments.hall_booking_id` and other FK columns are un-indexed for the same reason.
- **Uncached settings:** `Setting::get()` runs a query on every call; it is called from `PayableDaysCalculator::weeklyOffDays()` (per calculation), `OvertimeCalculationService`, policies. `Cache` is imported in `Setting` but never used. Payroll/attendance pages loop over dates and employees → repeated queries.
- **Synchronous heavy work:** OCR (Python, up to 60 s), Chrome PDF, ghostscript rasterisation, dompdf invoices all run inside the web request; no queue.
- **Full-table aggregations:** `AnalyticsController`, `ReportController`, `WalletController` (SUM/COUNT over all wallets), `Setting::all()`, `Setting::grouped()`.
- **Notification count:** `User::unreadNotificationsCount()` issues a COUNT each call and `notifications.count` exists as a JSON route; the layout's call frequency was **not verified** (no `notifications/count` string found under `resources/`).
- **N+1 risk:** attendance/leave/payroll services iterate per user/date (`EmployeeAttendanceService::getMonthlyHistory/getMonthlySummary`, `PayableDaysCalculator`, `Manager\DashboardController->each(...)`); 32 `paginate(` sites and many `->get()` list loads. Eager loading is used in places (`with([...])`) — check each list when changing relations.
- **Unbounded growth:** `audit_logs`, `app_notifications`, `wallet_transactions`, `inventory_transactions`, `sessions` (pruned nightly).
- **Frontend:** `app.css` is ~4.3k lines plus a large inline block in `admin-layout`; Bootstrap/Icons loaded from CDN per page; `sw.js` caches static assets.
No Redis/caching layer is used by app code today.

---

## 26. Testing

Framework: Pest 4 (`tests/Pest.php`) + PHPUnit config (`phpunit.xml`: SQLite `:memory:`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array`, `SESSION_DRIVER=array`). Run: `php artisan test` / `composer test`. **Executed 2026-09-19 (second-pass review, local `php artisan test`, SQLite in-memory): 825 tests, 824 passed, 1 failed** (2,077 assertions, ~18 s). Failing test: `tests/Feature/Admin/AttendanceLeaveAdminViewTest.php` "a present record dated today is reflected in the current months summary counts" (line 78, message `Failed asserting that 18 is identical to 0`). Cause not diagnosed; it is a month-summary/date-dependent assertion in a timezone-sensitive area (app UTC vs Asia/Kolkata) — `INFERRED` date/timezone-dependent. Re-run before trusting a green baseline: `php artisan test --filter=AttendanceLeaveAdminViewTest`.

Covered (feature tests, ~13.7k lines): auth (Breeze), profile; **HR-heavy** — attendance (mark, gate, segments, regularization, conflicts, reverse self-mark), leave (domain, workflow, opposite-half, policy dating/templates), overtime (workflow, calculation, feature flag, schema, UI), advances + eligibility, payable days/monthly payable/salary; several admin UI-consistency and mobile-layout tests; hall booking type/payment-due/dashboard month filter; meal register (clients, entries); menu composer/items.
Test map per module: §7.0.
**Not covered (no tests found):** expense lifecycle & settlement (`ExpenseSettlementService`), `PaymentRequestController` (public pay page, signed URLs, proof upload), wallets, daily closing, inventory & OCR pipeline, purchase plans, event-request portal (token, submit, approve→booking), invoice calculation/PDF, kitchen recipes, notifications, scheduler commands, settings, audit log, security headers, deployment shell scripts against the real server (sandbox suite `deployment/tests/run-tests.sh`: 27 scenarios T01–T29 with T08/T09 absent, ~15 min, fake binaries, `sigreset.py`/`pty_job_control.py` helpers; **not executed in this review**), browser/E2E, Postgres-specific migrations (SQLite skips them).
`tests/Unit` contains only the example test.

---

## 27. Known Issues
1. Open registration (S1) and login without `is_active` check (S2).
2. `remind-pending`, `daily-summary` pending counts, approve/reject UI assume a `pending` state that new requests never enter (`pending_payment` instead).
3. `reimbursed` expense requests can never be marked `completed` via the UI (policy requires `paid`; service would allow both).
4. Public pay page accepts `payment_mode=wallet` without touching the wallet.
5. `CleanupTempQr` cuts at 48 h while its description/comment/schedule note say 24 h.
6. `HallBookingController@addPayment` has a dead `pending` status branch, no transaction, allows overpayment and payments on cancelled bookings.
7. Booking conflict check is inclusive on boundaries; back-to-back bookings are rejected.
8. SQLite/Postgres schema divergence (§9.3) hides bugs in tests.
9. Event request approval ignores capacity/availability.
10. `whatsapp_sent_at` records page view, not sending.
11. `sw.js` `CACHE_VERSION` is manual.
12. Tailwind font (`Figtree`) vs actual CSS font (`Inter`); 12a. timezone split: app UTC, HR logic Asia/Kolkata (§5).
13. `resources/scss/hall-dashboard.scss` not built.
14. Production's checkout may predate commit `cd9227e` (the flat/hardened deploy scripts); first real run of them on the server has not been recorded.
15. One test fails at baseline (§26, `AttendanceLeaveAdminViewTest`, run 2026-09-19); cause undiagnosed.
16. (fixed) `diagnose-server.sh` now inspects the real lock `/var/lock/expenseflow-deploy.lock` and the in-progress marker.
17. `DEPLOYMENT_CHECKLIST.md` overstated the deployment test suite size (fixed: 27 scenarios).

---

## 28. Technical Debt
- Fat controllers (`HallBookingController` 703 lines, `DailyClosingController` 512, `MenuComposerController` 493, `InventoryBillController` 397) hold business logic that should be in services.
- `EmployeeAttendanceService` (974 lines) is a monolith with dense embedded specification; treat its header comment as the contract.
- Duplicated role-checking in controllers/policies/views; middleware naming (`role.manager` includes admin).
- Inline scripts/styles in Blade → unsafe-inline CSP, hard to test.
- Two front-end stacks (CDN Bootstrap vs Vite Tailwind) and unused Breeze layer.
- No CI, no static analysis config (Pint is installed; no `pint.json` found), no test for deployment on a real server.
- Two menu-item concepts, two meal-entry table generations, `advance_amount` vs payment ledger.
- Raw Postgres migrations guarded per-driver instead of a single supported DB engine for tests.

---

## 29. Legacy / Suspicious Code

Classification key: CONFIRMED UNUSED / LIKELY UNUSED / POSSIBLY USED / UNKNOWN. Method = repository-wide `grep` for references (routes, views, app, config), git history, and file location. **Nothing was deleted.**

| Item | Class | How determined |
|---|---|---|
| `deployment/legacy-release-based/*` (nginx, release `bootstrap.sh`, `cleanup.sh`, supervisor/cron templates) | **CONFIRMED UNUSED by design** (retired) | README in that dir; production is Apache/flat (memory note + live probe) |
| Server dirs `current/ releases/ shared/ repo/` under `/var/www/akshathayexpense` | **UNKNOWN** (REQUIRES-PRODUCTION-VERIFICATION) | `STALE_DIRS_CLEANUP.md` gives the proof procedure; never inspected here |
| `deployment/.env.production.example` | **LIKELY UNUSED / misleading** | release-layout template; several keys unread by code (§23); production `.env` is not derived from it as far as the repo shows |
| `deployment/diagnose-server.sh` lock path | **STALE reference (VERIFIED-REPO)** | its `LOCK_FILE="/tmp/expenseflow_deploy.lock"` (line 17) differs from the real lock `/var/lock/expenseflow-deploy.lock` in `lib.sh`; the lock section of the diagnosis inspects the wrong file. Documentation-only finding; script not modified (frozen). |
| `deployment/deploy.sh.bak*` | UNKNOWN (only referenced by warnings; none in repo) | grep + `git ls-files` |
| `layouts/app.blade.php`, `layouts/navigation.blade.php`, `App\View\Components\AppLayout`, `resources/views/dashboard.blade.php`, `welcome.blade.php` | **LIKELY UNUSED** | no `<x-app-layout>` / `@extends` / `view('dashboard'\|'welcome')` references; Breeze scaffolding. `<x-guest-layout>` IS used |
| `layouts/admin.blade.php` | POSSIBLY USED | no `x-`/`@extends` reference found; the live admin layout is `components/admin-layout.blade.php` |
| `FileUploadService` / `ExpenseBill` / `expense_bills` | LIKELY UNUSED | no route/controller calls `storeBills`; table exists |
| `daily_meal_entries`, `daily_meal_entry_items` + models `DailyMealEntry*` | POSSIBLY USED | routes/controllers use `MealEntry`; the controller name `DailyMealEntryController` is misleading; `MealClient` and `MealClientController` still reference the old models — trace before removal |
| `resources/scss/hall-dashboard.scss` | LIKELY UNUSED | not a Vite input; no reference |
| `hall_bookings.advance_amount`, `daily_closings.stock_additions/stock_deductions` | POSSIBLY USED | columns retained; usage not traced |
| `PurchasePlanningService`, `AttendanceConflictChecker`, `MenuLetterheadService` | USED | referenced by controllers/services |
| `app/Console/Commands/DbHealthCheck`, `InstallMenuFont` | POSSIBLY USED | manual commands, not scheduled |
| `config/services.php` postmark/resend/ses/slack; `s3` disk; `jobs`/`job_batches` | POSSIBLY USED | framework defaults; no app usage |
| `generate_favicons.py`, `ef-diagnosis.txt` (empty, untracked), `.phpunit.result.cache`, `storage/app/bg_test2.pdf`, `storage/app/menu_letterhead_cache.png` | LIKELY UNUSED / temp artefacts | root-level or ignored files with no references |
| `.agents/`, `.codex/` (empty dirs), `.claude/` | AI-tooling folders, not application code | — |
| `storage/fonts/*.ufm.json` | USED (dompdf cache) | tracked but rewritten at runtime; expect dirty diffs |
| `.env` variables `MEMCACHED_HOST`, etc. | UNKNOWN | present in local `.env`, no code uses them |

---

## 30. Operational Runbook
See [docs/OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md). Minimal production loop: `sudo bash deployment/deploy.sh main --check` → `sudo bash deployment/deploy.sh main` → `sudo bash deployment/health-check.sh`.

## 31. Troubleshooting
Symptom → first checks are in the runbook §"Common failures". Fast pointers: 403 on `/storage/...` → `public/storage` symlink (deploy repairs it); QR image 404 → path/realpath check in `PaymentRequestController@serveQr` and logs `QR serve:`; 500 after deploy → stale route/config cache (`deploy.sh` rebuilds; `php artisan optimize:clear` as `www-data` locally); OCR "no parseable data" → Python deps (`storage/app/ocr/INSTALL.md`); Tamil PDF garbled → Chrome missing (dompdf fallback); stuck deploy → lock/marker section of `DEPLOYMENT_CHECKLIST.md`.

## 32. Rollback Procedure
Code only: `sudo bash deployment/rollback.sh --list`, then `rollback.sh [--to <sha>] [--yes]`. Migrations are never reverted — restore a DB backup or ensure old code tolerates the new schema. Details in runbook and §21.3.

---

## 33. Development Rules
- Follow the existing style: controllers thin-ish, logic in `app/Services`, validation in `app/Http/Requests` (or inline where the surrounding code does), policies for object access.
- Money: `decimal` columns, string/`decimal:2` casts, `round(…, 2)`; ledger tables are append-only; balance mutations under `lockForUpdate()` in a transaction.
- Dates: business date in `Asia/Kolkata`; never trust client dates for attendance.
- Single sources of truth: `PayableDaysCalculator` (working/payable days), `LeaveBalanceService` (leave balance), `MonthlyPayableService` (payable salary), `InvoiceCalculationService` (invoice math), `WalletService`/`InventoryService` (balances), `AuditLogService`, `NotificationService`, `Setting`. Call them; do not re-implement.
- Migrations: idempotent guards (`Schema::hasTable/hasColumn`), driver guard for raw SQL, working `down()`.
- Frontend: build locally, commit `public/build/`.
- Format with Pint (`vendor/bin/pint --dirty`) — installed dev dependency; no config file found.

---

## 34. RULES FOR FUTURE AI AGENTS

1. Read this file first; verify any claim you rely on against the code. If they disagree, code wins — fix this file.
2. Inspect the existing implementation (`grep -r`, follow route → controller → service → model) before modifying anything.
3. Never invent tables, columns, routes, services, config keys or env vars. Confirm with `php artisan route:list`, migrations, and `config/`.
4. Never duplicate business logic: use the single-source services in §33.
5. Search the whole repo (including Blade, `resources/js`, `tests`, `deployment`) before adding functionality or deleting anything.
6. Trace dependants of shared code (`PayableDaysCalculator`, `WalletService`, `Setting`, layouts, CSS tokens) before editing it.
7. Database: check the migration history, both drivers (SQLite tests **and** Postgres prod), FK behaviour, indexes for high-volume filters, and the `expense_requests.status` CHECK when touching statuses.
8. Preserve backwards compatibility of URLs/route names (WhatsApp links already in the wild point at `/pay/{id}` signed URLs; the PWA caches pages).
9. Queues: today no jobs exist. Before adding one, decide queue/retries/timeout, confirm the production worker command, and remember `deploy.sh` restarts workers.
10. Deployment: production = **flat tree + Apache + supervisor**. Do not reintroduce nginx/`releases/`/`current`. Never edit `deploy.sh`/`lib.sh` casually — they have a sandbox test suite (`deployment/tests/run-tests.sh`). Run nothing from `legacy-release-based/`.
11. Never run destructive/production commands (`migrate`, `db:wipe`, `rm -rf`, `git clean`, `git reset --hard`, `chown -R`, `chmod -R`, `supervisorctl restart`, `systemctl`) without explicit confirmation. Never `chmod -R 775` and never run artisan/composer as root in the app tree.
12. Never print, log or commit secrets (`.env`, tokens, passwords, DB credentials). Redact in output.
13. Every functional change needs a Pest test; expense/payment/wallet/event-portal areas currently have none — add tests when you touch them.
14. Every new route needs: middleware group check, policy/`authorize`, FormRequest/validation, CSRF-safe method, throttle if public.
15. Public-facing pages (`/pay`, `/event-request`) must keep signed/tokenised access; do not loosen.
16. Mobile-first: test UI at ≤390 px; reuse `--ef-*` tokens and `ef-*`/`x-ds`/`x-premium` components; rebuild and commit `public/build`.
17. Do not delete "old" code unless proven unused (references + git history + production check). Record the proof in the change.
18. Keep migrations reversible where practical; add indexes for new high-volume filters; avoid Postgres-only SQL without a driver guard.
19. Money rules: decimals, rounding at the boundary, transactions + row locks, audit + notification via the existing services.
20. Business-date rules: Asia/Kolkata; holiday/weekly-off logic only in `PayableDaysCalculator`.
21. Update this document, the runbook and the ADRs when architecture, deployment, schema or business rules change.
22. State clearly what you did **not** verify (production state, tests not run). Do not claim deployment success without the health check.
23. Do not commit or push unless asked; use the attribution lines required by the session.
24. The parent `../CLAUDE.md` does not describe this repo — ignore its Lumen/Livewire/Sanctum guidance.
25. When a Blade/JS/CSS change is made, remember production has **no Node**: without a committed build the change does not ship.

---

## 35. Change Implementation Protocol
Superseded by the phased **§40 Future AI Agent Operating Protocol** (which absorbs the earlier 16-step list, including its
project-specific items: `public/build` rebuild, `sw.js` cache version, `expense_requests.status` CHECK, DB backup before migrations,
worker/cron implications). Do not maintain a second copy here.

## 36. Verification Checklist (before saying "done")
Condensed form of §40 Phase 6–8; if the two differ, §40 wins.
- [ ] Route middleware + policy + validation present for every new/changed route
- [ ] Money/balance code uses transactions + locks, audit + notification via services
- [ ] Migrations guarded/reversible; SQLite tests still pass; Postgres CHECK/indexes considered
- [ ] Tests added/updated and run (`php artisan test` output quoted, or "not run" stated)
- [ ] `public/build` rebuilt and committed if frontend changed
- [ ] `.env.example` updated for new keys; no secrets in diff
- [ ] Deployment/scheduler/worker implications stated
- [ ] This document updated
- [ ] `git status --short` / `git diff --stat` reviewed

---

## 37. Architecture Decision Records
See [docs/architecture/README.md](docs/architecture/README.md): ADR-0001 flat Apache deployment, ADR-0002 committed Vite build, ADR-0003 Postgres prod / SQLite tests with guarded raw SQL, ADR-0004 public access via signed URLs / tokens, ADR-0005 single-source domain services for HR/payroll, ADR-0006 in-app notifications instead of mail.

---

## 38. Open Questions / Unknowns
1. Is open self-registration intended? Is login supposed to reject `is_active=false`?
2. Production `.env` values: `QUEUE_CONNECTION`, `CACHE_STORE`, `SESSION_*`, `APP_DEBUG`, `APP_URL`, mail driver.
3. Supervisor program definition (command, queues, numprocs, user) and why workers exist while no jobs do.
4. Is `schedule:run` in cron? Under which user? Are scheduled commands actually running (logs)?
5. PHP handler (mod_php vs fpm), PHP limits (`max_execution_time`, upload size) vs 10 MB bills / 60 s OCR.
6. Are Python OCR deps, Chrome, ghostscript installed on production?
7. DB backups, retention, restore tests; `storage/app` backups.
8. Have `deploy.sh`/`rollback.sh` ever run on the real server? (Tested only in sandbox.)
9. Do stale `current/ releases/ shared/ repo/` dirs still exist on the server?
10. Intended difference between `hall_bookings.balance_amount` (pre-tax) and invoice `balance_due` (post-tax)?
11. Is `HallBooking.advance_amount` still used anywhere?
12. How are event menu item images provisioned (only a free-text `image_path` exists)?
13. Is UTC (`config/app.php`) intentional for `today()`-based hall dashboards and the scheduler, given the Asia/Kolkata business day?
14. Test-suite status at HEAD (not run here).
16. Why does `AttendanceLeaveAdminViewTest` (month summary `not_marked`/counts) fail on 2026-09-19 — real bug, date-dependent test, or timezone issue?
15. Were `AdminSeeder`/`UserSeeder` (password `password`) ever run on production?

---

## 39. Documentation Maintenance Rules

1. **Code is always the source of truth.** This file is a map, never an authority over the repository. If they disagree, trust the code.
2. **Correct the documentation when code changes invalidate it** — in the same change, before reporting done. Fix the discrepancy first, then continue the task.
3. **Never invent business rules.** A rule may be written down only with its source (`file`, `Class::method`, test, or a stated business decision). If the source is a comment or docblock rather than enforced code, say so.
4. **Never infer "unused" without evidence.** Use the §29 scale; evidence = repo-wide `grep` (routes, Blade, config, tests, `deployment/`), git history, and — for server-side things — production verification.
5. **Never remove functionality merely because it looks old.** Removal needs proven non-use, a stated reason, and a user request or approval.
6. **Every significant architectural change updates the relevant ADR** in `docs/architecture/` (or adds a new one, and marks the old one Superseded).
7. **Every production/deployment change updates `docs/OPERATIONS_RUNBOOK.md`** (and `deployment/DEPLOYMENT_CHECKLIST.md` if the deploy procedure changes).
8. **Every new business rule documents its source of truth** (where it is enforced, and which test covers it) in §13.
9. **Every new module documents its entry points**: routes/prefix + middleware, controller, service, model(s), views, tests — add a row to §7.0 and a subsection in §7.
10. **Every new database table/column documents purpose and relationships** in §9 (and §9.2 diagram), including indexes, Postgres-only SQL, and CHECK constraints.
11. **Every new environment variable is documented without exposing secrets**: name, purpose, default, where read, in `.env.example` and §23. Never write real values.
12. **Every frontend architecture or design-system change is documented** in §12 (layout used, tokens, components, build requirement).
13. **Every feature change identifies the tests covering the behaviour** (§7.0 test map / §26); if none exist, say so and add them.
14. **Tag evidence** with the classes at the top of this file. Upgrade a tag only with recorded evidence (command + date). Downgrade a claim to `INFERRED`/`UNKNOWN` the moment you cannot re-prove it.
15. **No duplicates:** one home per fact. Other sections link to it (see §10, §35). Remove or fix stale text instead of appending a contradiction.
16. **No secrets, tokens, `.env` values or credentials** in any doc. Production IPs/hostnames only where the deploy docs already record them.
17. Review this file whenever `deployment/`, `routes/`, a migration, `config/`, a policy or a service docblock changes.

---

## 40. Future AI Agent Operating Protocol

Follow these phases, in order, for **every** task — including "small" ones. Skipping a phase must be stated in the final report with the reason.
Hard limits for all phases: **do not commit, push, deploy, run migrations against production, or make destructive infrastructure changes unless the user explicitly asks.** Never print or store secrets.

### PHASE 1 — UNDERSTAND
1. Read this file (at least §1–§5, §7.0, §13, §14, §21–§22, §34, §41) and the relevant parts of `docs/OPERATIONS_RUNBOOK.md` and the ADR index.
2. Restate the requirement in one or two sentences, including which **role(s)** it affects (admin / manager / employee / public).
3. List ambiguities; ask the user when the answer changes money, attendance/leave/payroll, permissions, or data retention. Otherwise pick the conventional default and say so.

### PHASE 2 — DISCOVER (read-only)
1. Use §7.0 to find the module, then `grep -rn` the domain nouns across `app routes resources tests database config deployment`.
2. Trace route → middleware → controller → FormRequest → service → model → tables → views (`php artisan route:list --name=<x>`).
3. Read the owning service's **docblock** (locked business rules live there — attendance, leave, overtime, payroll, advances) and the model's status helpers.
4. Find existing tests (§7.0 map), existing UI components (`x-ds.*`, `x-premium.*`, `ef-*` classes) and the migrations that shaped the tables (including raw Postgres `ALTER` and CHECK constraints).
5. Record what is `VERIFIED-REPO` vs `INFERRED` vs `UNKNOWN`.

### PHASE 3 — IMPACT ANALYSIS
Answer each line (write "none" explicitly):
- **Backend:** which services/controllers change; can an existing single-source service (§33) be reused?
- **Database:** new table/column/index? Postgres-only SQL + driver guard? update `expense_requests.status` CHECK? backfill? reversible `down()`? big tables (`audit_logs`, `expense_requests`, `hall_bookings`)? **DB backup needed before deploy** (deploy.sh only warns).
- **Auth/authz:** route group middleware, policy/Gate, in-controller checks, `is_active` (§14), public/signed access (ADR-0004).
- **Frontend:** which layout (`x-admin-layout`); tokens/components to reuse; CDN vs Vite; inline-script CSP implications; `public/build` rebuild + commit; `sw.js` cache version.
- **Mobile:** verify at ≤390 px; bottom-nav/safe-area rules; existing mobile tests.
- **API/routes:** route names/URLs that external links depend on (`/pay/{id}` in WhatsApp messages, PWA cache); JSON consumers.
- **Queue/workers:** app has no jobs today (§15); adding one needs queue name/retries/timeout, prod worker command (`REQUIRES-PRODUCTION-VERIFICATION`), failed-job handling; deploy restarts workers.
- **Scheduler:** `routes/console.php`; UTC vs Asia/Kolkata; whether prod cron exists (`REQUIRES-PRODUCTION-VERIFICATION`).
- **Deployment:** composer/migration change ⇒ maintenance mode; new env vars; new system dependency (Chrome, gs, Python); Apache/supervisor implications; rollback limits (migrations are never reverted).
- **Security:** mass assignment, validation, upload rules, authorization on every new route, throttle for public routes, no secrets.
- **Performance:** indexes for new filters, N+1, uncached `Setting::get`, synchronous heavy work, unbounded tables.

### PHASE 4 — PLAN (present before editing when the change is non-trivial)
State: files to change and **why each**; tests to add/update and to run; migration/deployment risks; rollback considerations (code-only rollback vs irreversible migration); anything you will **not** do. Get user confirmation for destructive, ambiguous or production-affecting steps.

### PHASE 5 — IMPLEMENT
- Reuse existing patterns and single-source services; do not duplicate rules.
- Preserve existing behaviour and URLs unless told otherwise; keep migrations guarded and reversible.
- Follow the design system (§12: `--ef-*` tokens, `ef-*` classes, existing components) and security conventions (§24, §34).
- Keep diffs minimal; no drive-by refactors, no reformatting of unrelated files; no debug code.

### PHASE 6 — VERIFY
1. Targeted tests: `php artisan test --filter=<Name>` for the modules in §7.0; add tests for new behaviour (SQLite in-memory).
2. Relevant full run: `php artisan test` (baseline: 825 tests, 1 known failure recorded in §26 — report any *new* failures separately).
3. Static/lint: `php -l <changed files>`, `vendor/bin/pint --test --dirty`; for shell changes `bash -n` and `bash deployment/tests/run-tests.sh` (sandbox, ~15 min) — never against a real server unless asked.
4. Routes: `php artisan route:list` if routes changed. Migrations: run on a **local/throwaway** DB only, check `down()`; remember SQLite skips Postgres-only blocks.
5. Frontend: `npm run build` and confirm `public/build/manifest.json` changed and is staged-ready; check ≤390 px.
6. `git status --short` and `git diff --stat`; read the whole diff; look for secrets, `dd()`/`dump()`/`console.log`, stray files (`storage/fonts`, `ef-diagnosis.txt`), unintended edits to `deployment/` files.

### PHASE 7 — DOCUMENT
Apply §39: update PROJECT_KNOWLEDGE.md (modules §7/§7.0, business rules §13, schema §9, env §23, frontend §12, known issues §27), the relevant ADR, `docs/OPERATIONS_RUNBOOK.md` for any ops/deploy change, `.env.example` for new keys. Fix any stale statement you noticed, even if unrelated to your change.

### PHASE 8 — REPORT
Always include: **what changed · why · files changed · tests run (with result) · tests NOT run (and why) · database impact · deployment impact (incl. env vars, maintenance mode, workers, cron, backup) · known limitations · remaining risks · docs updated**. Label claims with the evidence classes; never say "deployed"/"verified in production" without evidence. End with `git status --short` and `git diff --stat` when files changed.

---

## 41. Do Not Assume

Unless you have **just** verified it (and can cite how), treat each of these as unknown:

| Area | Do not assume | Where the truth is |
|---|---|---|
| Production infrastructure | Apache/flat tree is `PROD-VERIFIED` (§22, 2026-09-19); everything else about the box (OS packages, TLS, firewall, log paths, PHP handler, PHP limits) is not | `deployment/diagnose-server.sh` (read-only), `deploy.sh --check` |
| Database state | that migrations are applied, that prod data matches the seeders/tests, table sizes, existing rows violating new constraints, that the Postgres CHECK allows a new status | `migrate:status`, direct read-only queries with user approval |
| Environment variables | that local `.env`, `.env.example` and production `.env` agree (they differ: session lifetime/encryption, drivers). Never read secrets aloud | ask the user / `env_value` in `deployment/lib.sh` reads only specific keys |
| User roles | that a role can/can't do something because of its name (`role.manager` includes admin; `employee/*` has no role middleware; `is_active` is not checked at login) | §4, §14, route file, policies |
| Business rules | that a rule exists because it "makes sense" (attendance, leave, payroll, GST, refunds — there is no refund workflow) | §13 with its source file; the service docblocks; ask the user |
| Unused code | that old/odd files are dead (`layouts/admin.blade.php`, `DailyMealEntry*`, `ExpenseBill`, workers) | §29 + your own grep + production check |
| External integrations | that any API/webhook/mail/SMS/WhatsApp *sending* exists (only a share link; no gateway, no mail code) | §18 |
| Scheduled jobs | that the scheduler actually runs in production, or in which timezone (config is UTC) | §16; cron on the server |
| Queue configuration | the queue driver, the worker command/queues/timeout, or that jobs exist (none do) | §15; supervisor config on server |
| Filesystem paths | that `/var/www/expenseflow`, `current/`, `releases/`, `shared/`, nginx paths from the legacy templates exist; real app dir is `/var/www/akshathayexpense` (flat). Storage disks: `local`=`storage/app/private`, `public`=`storage/app/public` | §17, §21 |
| Server software | nginx, Docker, Redis, Node, Chrome, ghostscript, Python OCR on prod | §22 |
| Tests | that a green suite proves Postgres behaviour or deployment on the real server | §26, ADR-0003 |
| Deploy scripts | that the deploy scripts on the server equal `HEAD` (they were only committed in `cd9227e`) or that they were ever run for real | §21 |
| This documentation | that it is current — check the code for anything you rely on | §39 |
