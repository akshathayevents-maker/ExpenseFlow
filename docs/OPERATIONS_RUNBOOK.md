# ExpenseFlow — Operations Runbook

Companion to [PROJECT_KNOWLEDGE.md](../PROJECT_KNOWLEDGE.md) (§21–22, §30–32) and
[deployment/DEPLOYMENT_CHECKLIST.md](../deployment/DEPLOYMENT_CHECKLIST.md).

**Evidence classes** (same as PROJECT_KNOWLEDGE.md top): `VERIFIED-REPO` (command/file exists in the repo) ·
`VERIFIED-TEST` (behaviour asserted by an existing automated test; for `deployment/*.sh` this means the *sandbox* suite with fake
apache2ctl/systemctl/supervisorctl/sudo, **not** the real server) · `PROD-VERIFIED` (proven on the live server, evidence recorded) ·
`INFERRED` · `UNKNOWN` · `REQUIRES-PRODUCTION-VERIFICATION`. An untagged production command is **not** a verified fact.

Never paste `.env` contents or credentials into tickets/logs. Never run anything from `deployment/legacy-release-based/`.

---

## 0. Environments

| Env | Where | Web server | DB | Notes |
|---|---|---|---|---|
| LOCAL | developer machine | `php artisan serve` | `.env` (git-ignored); this checkout uses `pgsql` locally, `.env.example` defaults to SQLite | queue/cache/session = `database` |
| TESTS | `phpunit.xml` | none | SQLite `:memory:` | `QUEUE_CONNECTION=sync`, `CACHE_STORE=array` |
| STAGING | **none defined in the repo (UNKNOWN whether one exists)** (`SecurityHeadersMiddleware` treats `staging` like production if `APP_ENV=staging`) | — | — | UNKNOWN whether one exists |
| PRODUCTION | `expense.akshathay.com` → `168.144.117.206` | Apache 2.4.58, tree `/var/www/akshathayexpense` | PostgreSQL | supervisor programs `expenseflow-worker:*` |

---

## 1. LOCAL

### 1.1 Setup `[VERIFIED-REPO]`
```bash
composer install
cp .env.example .env && php artisan key:generate     # or: composer setup   (also migrates and builds assets)
php artisan migrate
php artisan db:seed                                   # LOCAL ONLY: creates users with password "password" (admin@/manager@/employee@expenseflow.com)
npm install --ignore-scripts && npm run build
php artisan storage:link                              # public/storage -> storage/app/public
```
Requires PHP ≥ 8.3 (`composer.json`), Node per `.nvmrc`. Optional system tools for specific features: `python3` + OCR deps (`storage/app/ocr/INSTALL.md`), `google-chrome`/`chromium` (Tamil menu PDF), `gs` (menu letterhead).

### 1.2 Develop
```bash
composer dev            # server + queue:listen + pail + vite (concurrently)   [VERIFIED-REPO]
php artisan serve && npm run dev
php artisan route:list
php artisan schedule:list ; php artisan schedule:run     # exercise the scheduler
vendor/bin/pint --dirty                                  # formatter installed as dev dependency
```
Frontend rule: after changing `resources/css|js|scss` run `npm run build` and **commit `public/build/`** (production has no Node).

### 1.3 Test
```bash
php artisan test                                  # or: composer test
php artisan test --filter=OvertimeWorkflowTest
php artisan test tests/Feature/Menu/MenuComposerTest.php
bash deployment/tests/run-tests.sh                # deployment scripts in a sandbox, ~15 min, needs php composer git python3
```
SQLite skips Postgres-only migration blocks — a green run does not prove the Postgres CHECK constraints accept your values.
Baseline (executed 2026-09-19, VERIFIED-TEST): 825 tests, 824 pass, 1 fails (`AttendanceLeaveAdminViewTest` — "a present record dated today is reflected in the current months summary counts"; cause undiagnosed, likely date/timezone-dependent = INFERRED). Report only *new* failures against this baseline.

### 1.4 Local troubleshooting
| Symptom | Fix |
|---|---|
| Stale config/routes/views | `php artisan optimize:clear` |
| `QR image` 404 locally | `php artisan storage:link`; check `storage/app/public/qr-codes/` |
| Permission errors in `storage/` | fix ownership of your own checkout; do **not** `chmod -R 777` |
| Pest/DB errors | `php artisan config:clear` (composer `test` already does) |
| OCR fails | run `python3 storage/app/ocr/invoice_ocr.py <file> en` manually; see `INSTALL.md` |

---

## 2. STAGING
No staging environment or config is defined in the repository. If one is created: use `APP_ENV=staging`, `MAIL_MAILER=log`, its own DB, and the same flat/Apache layout; run `deploy.sh --check` first. Anything else is UNKNOWN.

---

## 3. PRODUCTION

Access: `ssh root@168.144.117.206` (DNS A record of `expense.akshathay.com`), fallback DigitalOcean web console `[IP/DNS: PROD-VERIFIED per maintainer notes 2026-09-19; root SSH user: INFERRED from DEPLOYMENT_CHECKLIST.md]`.
Run deploy scripts **as root**; they drop to `www-data` for composer/artisan. Never run `artisan`/`composer` as root inside `/var/www/akshathayexpense` (root-owned files break the app).

### 3.1 Before anything: read-only diagnosis `[VERIFIED-TEST (sandbox)]`
```bash
sudo bash /var/www/akshathayexpense/deployment/diagnose-server.sh        # writes nothing except optional tee output
# its lock section reads the real lock (/var/lock/expenseflow-deploy.lock) plus the in-progress marker; details in 3.4
sudo bash /var/www/akshathayexpense/deployment/deploy.sh main --check    # READ-ONLY preflight; must end "PREFLIGHT PASSED"
```

### 3.2 Deploy `[VERIFIED-TEST (sandbox)]`
Preconditions: `public/build` committed; DB backup if any `database/migrations/*` changed (the script warns but does not back up).
```bash
cd /var/www/akshathayexpense
sudo bash deployment/deploy.sh main             # routine
sudo bash deployment/deploy.sh main --force --fix-ownership   # first run / after root-owned files appeared
sudo bash deployment/health-check.sh --wait-workers 60        # exit 0 = healthy
```
Pipeline (details PROJECT_KNOWLEDGE §21.2): lock → preflight → fetch → verify build → maintenance if composer/migrations changed → checkout → composer install → config/route/view/event cache → migrate if pending → Apache reload → supervisor restart → `artisan up` → health check → history record.
Logs: `/var/log/expenseflow-deploy/deploy-*.log`, history `/var/log/expenseflow-deploy/history.tsv`, interrupted-deploy marker `/var/log/expenseflow-deploy/deploy-in-progress`.
> The hardened scripts were committed in `cd9227e`. Whether the server's checkout already has them is `REQUIRES-PRODUCTION-VERIFICATION`; if it does not, the first `git checkout -f -B main origin/main` on the server brings them (checklist "Rollout"). `deployment/.env.production.example` is a legacy template — do not treat it as the production `.env`.

### 3.3 Rollback (code only) `[VERIFIED-TEST (sandbox)]`
```bash
sudo bash deployment/rollback.sh --list
sudo bash deployment/rollback.sh                 # previous commit from history, asks to confirm
sudo bash deployment/rollback.sh --to <sha> --yes
```
**Migrations are never reverted.** If the bad deploy included migrations, either verify the old code works with the new schema or restore the pre-deploy DB backup (backup procedure: `[REQUIRES-PRODUCTION-VERIFICATION]`; the checklist suggests `sudo -u postgres pg_dump -Fc <database> > /var/backups/expenseflow-$(date +%F-%H%M).dump`, DB name unknown).
Held in maintenance after a failed migration: fix forward and re-run `deploy.sh main`, or after checking schema compatibility `rollback.sh --to <sha>` then `sudo -u www-data php /var/www/akshathayexpense/artisan up`.

### 3.4 Deployment lock issues `[VERIFIED-TEST (sandbox)]`
```bash
cat /var/lock/expenseflow-deploy.lock.info
sudo fuser -v /var/lock/expenseflow-deploy.lock
ps -o pid,stat,etime,args -p <PID>
kill -CONT <PID> && kill -TERM <PID>        # stopped holder: resume then abort cleanly (it restores)
# only if still alive after ~60 s: kill -KILL <PID>  (next deploy detects the unfinished marker and re-applies)
```
**Never delete `/var/lock/expenseflow-deploy.lock`** (kernel flock; deleting it while held allows two deploys). An unowned lock file is harmless.

### 3.5 Queue workers `[REQUIRES-PRODUCTION-VERIFICATION for the definition; restart logic VERIFIED-TEST (sandbox)]`
```bash
sudo supervisorctl status | grep expenseflow          # names: expenseflow-worker:expenseflow-worker-N_00
sudo supervisorctl restart expenseflow-worker:*       # deploy.sh already does this for every expenseflow* program
php artisan queue:failed / queue:retry all            # as www-data; only meaningful once jobs exist
```
The application currently dispatches **no jobs** (PROJECT_KNOWLEDGE §15). `queue:restart` is not used by the scripts (supervisor restart is used instead). Program definition file location/contents: `[REQUIRES-PRODUCTION-VERIFICATION]` (typically `/etc/supervisor/conf.d/`).

### 3.6 Scheduler `[REQUIRES-PRODUCTION-VERIFICATION]`
The app needs a per-minute `php artisan schedule:run` cron running as `www-data`. Repo evidence only: `routes/console.php` schedule and the retired template `deployment/legacy-release-based/cron.d.expenseflow` (`deployer` user, `current/` path — **do not copy verbatim**). Check on the server: `sudo crontab -u www-data -l; ls /etc/cron.d; grep -R schedule:run /etc/cron* /var/spool/cron 2>/dev/null`. Scheduler times are **UTC** (config timezone).
Manual run: `sudo -u www-data php artisan schedule:run` / a single command e.g. `sudo -u www-data php artisan app:check-stock`.

### 3.7 Cache clearing `[REQUIRES-PRODUCTION-VERIFICATION]`
`deploy.sh` rebuilds `config/route/view/event` caches itself. To clear manually: `sudo -u www-data php artisan optimize:clear` then re-run `deploy.sh main --force` (or `config:cache route:cache view:cache event:cache`). Running these as root creates root-owned cache files → later `--fix-ownership`. Application cache backend (database vs redis) `[REQUIRES-PRODUCTION-VERIFICATION]`.

### 3.8 Logs
- App: `/var/www/akshathayexpense/storage/logs/laravel.log` (or daily files if `LOG_STACK=daily`; `[REQUIRES-PRODUCTION-VERIFICATION]`).
- Deploy: `/var/log/expenseflow-deploy/`.
- Apache: `/var/log/apache2/*` `[REQUIRES-PRODUCTION-VERIFICATION]` (vhost-specific paths unknown).
- Payment/QR diagnostics: search `QR serve:` in `laravel.log`. OCR: `PaddleOCR:` / `InvoiceOCRService:`. Menu PDF: `[MenuPDF]`, `[MenuLetterhead]`.

### 3.9 Health checks
```bash
curl -s -o /dev/null -w '%{http_code}\n' https://expense.akshathay.com/up          # 200
curl -s https://expense.akshathay.com/build/manifest.json | sha256sum               # must equal: sha256sum public/build/manifest.json
sudo bash deployment/health-check.sh [--expect-commit <sha>] [--no-http] [--no-system]
git -C /var/www/akshathayexpense status --short                                     # expect empty (storage/fonts noise tolerated)
```

### 3.10 Common failures
| Symptom | Likely cause / action |
|---|---|
| Deploy: "Apache does not serve $APP/public" | Vhost DocumentRoot differs or `APP_URL` host mismatch → inspect with `diagnose-server.sh`; do not force |
| Deploy: "local edits outside storage/fonts would be overwritten" | Someone edited tracked files on the server; review `git diff`; a patch is saved before `--discard-local-changes` |
| Deploy: "committed build is INCOMPLETE" | Run `npm run build` locally, commit `public/build/`, push |
| Deploy: "N critical path(s) not writable" | Root-owned `storage/`, `bootstrap/cache`, `vendor/` → rerun with `--fix-ownership` (chowns only unowned paths there) |
| `.git/index.lock` exists | Confirm no git running (`pgrep -a git`), then remove that one file |
| 500 right after deploy | Check `laravel.log`; stale caches; `deploy.sh` migration mid-way (maintenance held); run `health-check.sh` |
| 403/404 for `/storage/...` | `public/storage` symlink (deploy repairs; `--check` reports); Apache `FollowSymLinks` config `[REQUIRES-PRODUCTION-VERIFICATION]` |
| Payment page image missing | `serveQr` logs; file under `storage/app/public/qr-codes/<id>/`; signed-URL expired (30 days) |
| Tamil menu PDF garbled | Chrome not installed → dompdf fallback (`[MenuPDF] Chrome not found` in log) |
| OCR "no parseable data" / timeout | Python deps missing or `OCR_TIMEOUT` (default 60) too low; runs in-request |
| Users can't log in | throttle 5/min per IP; session table/driver; `SESSION_*` env |
| Site in maintenance page | `storage/framework/down` exists → `sudo -u www-data php artisan up` **only after** confirming schema/code compatibility |
| Composer errors on deploy | Runs as `www-data` with `COMPOSER_HOME=/var/cache/expenseflow-composer (0700, owner-checked)`, 600 s timeout (`EF_COMPOSER_TIMEOUT`); check network/disk (≥512 MiB free required) |
| PHP errors | PHP 8.3.6 on prod (PROD-VERIFIED per maintainer notes 2026-09-19); handler mod_php vs fpm `[REQUIRES-PRODUCTION-VERIFICATION]` — `deploy.sh` logs which it detects |
| Database issues | `php artisan migrate:status` as `www-data`; `php artisan db:health-check --show-ok` (`--fix` would run `migrate` — do not use casually in prod) |
| Redis issues | Only relevant if prod `.env` selects redis for cache/session/queue `[REQUIRES-PRODUCTION-VERIFICATION]`; app code itself does not call Redis directly |
| Workers not RUNNING | `supervisorctl status`; health-check waits for workers restarted since deploy start; `deploy.sh` treats <all RUNNING as a warning |
| Duplicate deploy triggers | `deploy.sh --check` warns about cron/systemd deploy triggers; remove duplicates |

### 3.11 Stale legacy directories `[REQUIRES-PRODUCTION-VERIFICATION]`
`current/ releases/ shared/ repo/` under the app dir: follow `deployment/STALE_DIRS_CLEANUP.md` (prove unused → snapshot → quarantine by rename → delete only after ≥14 days by explicit human decision). Nothing in this documentation task touched them.

### 3.12 Must-nots
No `chmod -R 775`; no running artisan/composer as root in the tree; no deleting the lock file; no `kill -9` first; no editing tracked files on the server; no `--discard-local-changes` without reading the saved patch; no legacy scripts; no seeders; do not SSH to any address other than `168.144.117.206`.

---

## 4. Backups and restore
UNKNOWN / `[REQUIRES-PRODUCTION-VERIFICATION]`. No backup job exists in current scripts; the retired cron template had a nightly `pg_dump` (2 AM, 30-day retention) — nothing proves it is installed. File data to include: `storage/app/public` (QR images), `storage/app/private` (payment proofs, inventory bill scans), `.env` (securely).

## 5. Runbook maintenance
Update this file whenever deployment scripts, server topology, cron/supervisor setup, or backup practice changes. When a `[REQUIRES-PRODUCTION-VERIFICATION]` item is confirmed, replace the tag with `PROD-VERIFIED` plus the evidence (command, date). See PROJECT_KNOWLEDGE §39 for the full maintenance rules; every deployment/production change must update this file.
