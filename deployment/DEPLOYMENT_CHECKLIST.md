# ExpenseFlow — Production Deployment Runbook (flat layout, Apache)

## Architecture (single, proven)

```
Internet ─▶ Apache 2.4 ─▶ DocumentRoot /var/www/akshathayexpense/public      site: expense.akshathay.com → 168.144.117.206
                          git working tree = /var/www/akshathayexpense        (.env, storage/, vendor/ live in place)
Supervisor: expenseflow-worker:* ─▶ artisan queue:work from the same tree
```
Evidence: the live `/build/manifest.json` hash equals the newest commit's manifest (only the flat tree contains it) and the
response header says `Apache/2.4.58`. The nginx / `releases/` / `current/` design is retired
(`deployment/legacy-release-based/`, disabled, not executable).

Scripts (`deployment/`): `deploy.sh`, `rollback.sh`, `health-check.sh`, `lib.sh`, `diagnose-server.sh` (read-only), `tests/`.
Server access: `ssh root@168.144.117.206` (or the DigitalOcean web console). Run scripts as **root**; they drop to `www-data` for Composer/artisan themselves.

## What the deploy guarantees

| Concern | Behaviour |
|---|---|
| Fail closed | Apache must serve `/var/www/akshathayexpense/public` **for this site's host**, Supervisor programs must run from this tree, `apache2ctl configtest` must pass, the committed build must be complete — else **nothing is changed** |
| Lock | `flock` on `/var/lock/expenseflow-deploy.lock`; the file is never deleted; children never inherit the lock fd |
| Ctrl+Z | ignored by the whole process tree (SIGTSTP/TTIN/TTOU) — it cannot leave a stopped deploy holding the lock. Ctrl+C / SIGTERM / SIGHUP abort **cleanly** (restore) |
| Composer | non-interactive, no stdin/TTY, bounded (600 s), runs as `www-data`; version probe bounded (20 s); cache in `/var/cache/expenseflow-composer` (0700, owner-checked — never a predictable `/tmp` path) |
| No hangs | every external command is time-bounded: git (120/300 s), `ls-remote` 30 s, fetch 180 s, artisan 120 s, migrate 900 s, `configtest`/`reload`/`supervisorctl status` 60 s, `supervisorctl restart` 600 s (waits for jobs), HTTP 15 s; the logger wait at exit is bounded too |
| Scratch files | only in `mktemp` 0700 dirs (`/tmp/ef-deploy.*`, removed on exit); dirs left by a killed run are swept by the next deploy (own pattern, own user, ≥5 min old) |
| Secrets | `.env` is never read except `APP_URL`; remote URLs, cron/systemd lines and the diagnostic's output are credential-redacted; logs `/var/log/expenseflow-deploy` are 0750, dirty-tree patches 0600 |
| Ownership | no `chmod`, ever. `--fix-ownership` chowns only paths under `storage/`, `bootstrap/cache`, `vendor/` that `www-data` does not own |
| Tracked files | dompdf rewrites tracked `storage/fonts`; edits are saved to a patch, then restored. Real code edits → refused |
| Maintenance | only when composer files or migrations change; uses `errors/503.blade.php`; failure to enter it stops the deploy |
| Failure after checkout, **no migrations run** | previous commit restored automatically, rebuilt, workers restarted, health-checked |
| Failure **after migrations** (or after adopting a leftover maintenance) | **no** automatic code rollback (old code vs new schema is unsafe) — app is held in maintenance, exact options printed |
| Interrupted (kill -9, OOM, power) | in-progress marker detected; next run re-applies even if `HEAD` already equals the target |
| Success | only after health check passes: commit, build assets, DB, caches, `/up`, `/login`, served `manifest.json` hash, workers restarted after the deploy started |

## Rollout

**LOCAL**
```bash
git status && git diff --stat
git log -1 --oneline            # the deployment commit (see the hash reported with this change)
git push origin main
```

**SERVER** (`ssh root@168.144.117.206`)
```bash
cd /var/www/akshathayexpense
git status --short | head; git diff > /root/server-local-changes-$(date +%F).patch   # safety copy of any local edits (dompdf fonts, mode noise)
git fetch origin main
git checkout -f -B main origin/main          # brings the new scripts; changes only tracked files
git ls-files -z -- storage bootstrap/cache | xargs -0 -r chown -h www-data:www-data   # a root checkout re-creates tracked font files root-owned; hand them back

bash deployment/deploy.sh main --check       # READ-ONLY preflight. Must end with: PREFLIGHT PASSED
```
`--check` never fetches, takes/creates no lock, changes no file, enters no maintenance, runs no migration, restarts nothing.
It verifies: app dir, git repo/remote/branch, remote reachability, Apache DocumentRoot + `configtest`, Supervisor program tree
and user, PHP, Composer, database (read-only), committed build (manifest, Vite entries, tracked assets), local git
modifications, ownership/writability, `public/storage`, leftover maintenance, lock/conflicting processes, cron/systemd
triggers, stale artefacts. Any FAIL stops here. WARNs are explained (ownership warning ⇒ use `--fix-ownership`).

**Only if `--check` passes:**
```bash
bash deployment/deploy.sh main --force --fix-ownership     # first run exercises the whole pipeline once
bash deployment/health-check.sh --wait-workers 60          # HEALTHY, exit 0
```
Routine deploys afterwards: `bash deployment/deploy.sh main` (add `--check` first if in doubt).

If migration files changed, the check warns: **take a database backup first**, e.g.
`sudo -u postgres pg_dump -Fc <database> > /var/backups/expenseflow-$(date +%F-%H%M).dump` (your DB name/creds — not stored here).

## Verification
```bash
curl -s https://expense.akshathay.com/up -o /dev/null -w '%{http_code}\n'               # 200
curl -s https://expense.akshathay.com/build/manifest.json | sha256sum                   # == sha256sum public/build/manifest.json
git -C /var/www/akshathayexpense status --short                                          # empty
supervisorctl status | grep expenseflow                                                  # RUNNING, small uptime after a deploy
tail -n 40 /var/log/expenseflow-deploy/deploy-*.log | tail -n 40; cat /var/log/expenseflow-deploy/history.tsv
```

## Rollback (code only)
```bash
bash deployment/rollback.sh --list
bash deployment/rollback.sh                    # to the commit before the last deploy (asks to confirm)
bash deployment/rollback.sh --to <sha> --yes
```
Runs the same preflight/lock/maintenance/composer/reload/health pipeline. **Never reverts migrations** — verify schema
compatibility or restore the DB backup. Not atomic (brief maintenance when vendor/schema differ).

## Recovery: a deploy is stuck / was interrupted
```bash
cat /var/lock/expenseflow-deploy.lock.info               # holder PID + command
fuser -v /var/lock/expenseflow-deploy.lock               # who really has it (also finds an inherited fd)
ps -o pid,stat,etime,args -p <PID>                       # T = stopped, S/R = running
kill -CONT <PID> && kill -TERM <PID>                     # stopped holder: resume, then ask it to abort cleanly (it restores)
# wait ~60 s. Only if it is still alive:  kill -KILL <PID>   (the next run detects the unfinished deploy and re-applies)
bash deployment/deploy.sh main --check                   # reports leftovers: interrupted deploy, maintenance, dirty tree
```
* **Never delete `/var/lock/expenseflow-deploy.lock`** — it is only a name for the kernel lock; removing it while a holder
  exists allows a second concurrent deploy. A lock file with no holder is harmless.
* Held in maintenance after "migration failed": fix forward and re-run `deploy.sh main`, or (after checking schema compatibility)
  `rollback.sh --to <sha>` then `sudo -u www-data php /var/www/akshathayexpense/artisan up`.

## Must NOT be done again
* `chmod -R 775 …` anywhere; running `artisan`/`composer` as root in the app tree (creates root-owned files).
* Deleting the lock file; `kill -9` as a first resort; Ctrl+Z as a way to "pause" (it is ignored now — use Ctrl+C).
* Running anything from `deployment/legacy-release-based/` or any `deploy.sh.bak*` (disabled/old).
* Editing tracked files on the server; deploying with `--discard-local-changes` without reading the saved patch.
* Deleting `current/`, `releases/`, `shared/`, `repo/` before `STALE_DIRS_CLEANUP.md` proves them unused.
* SSH to any address other than `168.144.117.206`.

## Tests (sandbox only — never touches a server)
`bash deployment/tests/run-tests.sh [T05 T06 ...]` (≈20 min for everything; needs php, composer, git, python3). 33 scenarios,
T01–T33, including real-pty Ctrl+Z / Ctrl+C; the final `RESULT: N passed, M failed` line is the count (do not trust a number
written in a document). It runs unprivileged with fake `sudo`/`apache2ctl`/`systemctl`/`supervisorctl`/`crontab`; root-only paths
(`runuser`, `chown`, real Apache/Supervisor, PostgreSQL) are exercised only by the first real `--check` / deploy.

## Evidence status of this document (added by the documentation review)
Statements about the production server in this file are `PROD-VERIFIED` only where PROJECT_KNOWLEDGE.md §22 says so
(Apache 2.4.58, flat tree, PostgreSQL, PHP 8.3.6, Composer 2.7.1, supervisor names, DNS A record). The scripts themselves are
`VERIFIED-TEST` only against the sandbox suite (fake binaries); their behaviour on the real server is
`REQUIRES-PRODUCTION-VERIFICATION`.
