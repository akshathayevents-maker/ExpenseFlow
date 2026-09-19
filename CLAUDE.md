# CLAUDE.md — ExpenseFlow

**Before any significant change, read [PROJECT_KNOWLEDGE.md](PROJECT_KNOWLEDGE.md)** (at minimum §1–5, §7.0, §13, §14, §21–22, §34, §40, §41),
then the relevant part of [docs/OPERATIONS_RUNBOOK.md](docs/OPERATIONS_RUNBOOK.md) and [docs/architecture/](docs/architecture/README.md).
Follow the phased protocol in PROJECT_KNOWLEDGE.md §40 for every task. The code is the source of truth: if the docs disagree with the code, fix the docs.

## This repo is not the monorepo described in `../CLAUDE.md`
ExpenseFlow is one Laravel 13 / PHP 8.3 monolith (Blade + Bootstrap/CDN + committed Vite build, PostgreSQL in production, Pest tests on SQLite).
There is no Lumen, Livewire, Sanctum, Docker, `event-workflow-app`, JSON API, or queued job code here. Ignore the parent file's guidance for this directory.

## Hard rules
- **Before touching anything under `deployment/` (or Apache/supervisor/cron/queue setup), you MUST read `deployment/DEPLOYMENT_CHECKLIST.md`, `deployment/legacy-release-based/README.md`, PROJECT_KNOWLEDGE.md §21–§22 and `docs/OPERATIONS_RUNBOOK.md` §3, and run `git status` first: other sessions/people may be editing the same scripts concurrently — never overwrite uncommitted deployment edits you did not make. Ctrl+Z suspends a process (it keeps holding the flock); it is never a successful deploy — use Ctrl+C.
- Do not commit, push, deploy, run migrations on production, or change server/infrastructure unless explicitly asked.
- Never print, log or commit secrets (`.env`, passwords, tokens). Never run seeders outside local development (they create users with password `password`).
- Production = Apache + flat git tree `/var/www/akshathayexpense` + supervisor. Do not reintroduce nginx / `releases/` / `current`; do not run `deployment/legacy-release-based/*`. Do not edit `deployment/*.sh` without being asked (they have a sandbox suite).
- Production has no Node: after changing `resources/css|js|scss`, run `npm run build` and include `public/build/` in the change.
- Reuse the single-source services (PayableDaysCalculator, LeaveBalanceService, MonthlyPayableService, InvoiceCalculationService, WalletService, InventoryService, AuditLogService, NotificationService, Setting). Do not duplicate business rules.
- Every new route needs middleware + policy/authorization + validation; public routes need throttling.
- Check UI at ≤390 px; reuse `--ef-*` tokens and existing components.
- Label claims as VERIFIED-REPO / VERIFIED-TEST / PROD-VERIFIED / INFERRED / UNKNOWN / REQUIRES-PRODUCTION-VERIFICATION. Never present production state as fact without evidence.
- After any architectural, schema, business-rule, env, frontend-convention or deployment change, update PROJECT_KNOWLEDGE.md, the relevant ADR and the runbook (§39).

## Commands
```bash
php artisan test                      # 825 tests as of 2026-09-19, 1 known failure (see PROJECT_KNOWLEDGE §26)
php artisan test --filter=<Name>
vendor/bin/pint --test --dirty
php artisan route:list
npm run build                         # then commit public/build/
bash deployment/tests/run-tests.sh    # deployment scripts, sandbox only (~15 min)
```
