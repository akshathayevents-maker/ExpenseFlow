# ADR-0001 — Flat git-tree deployment served by Apache

**Status:** Accepted (production reality) · **Supersedes:** the release-based/nginx design in `deployment/legacy-release-based/`

## Context
Two deployment designs exist in history. The original scripts (`bootstrap.sh`, `cleanup.sh`, `nginx.conf`, `supervisor.conf`,
`cron.d.expenseflow`) assume `/var/www/expenseflow/{releases,current,shared}` behind nginx + php-fpm. Production
(`expense.akshathay.com`) is actually Apache 2.4.58 serving the git working tree `/var/www/akshathayexpense/public`.

## Decision
`deployment/deploy.sh` (rewritten flat in commit `f2cfea1`, 2026-06-24, later hardened) deploys by `git checkout -f -B <branch> <sha>` in place,
then composer, caches, migrations, Apache reload, supervisor worker restart, health check. Rollback re-deploys an older commit
(`rollback.sh`). The release-based files were moved to `legacy-release-based/` (committed in `cd9227e`, 2026-09-19).

## Evidence
`deployment/deploy.sh`, `lib.sh`, `rollback.sh`, `health-check.sh`; `deployment/legacy-release-based/README.md`; commit `f2cfea1`;
proof of production layout (live `/build/manifest.json` hash == commit `7068cb0`, `Server: Apache`) recorded in the maintainers' notes.

## Consequences
- Not atomic: deploys that change composer files or migrations use maintenance mode.
- Migrations are never reverted by rollback; after migrations no automatic code rollback.
- Fail-closed preflight (Apache DocumentRoot must equal `$APP/public`, supervisor programs must run from the tree).
- Stale `current/ releases/ shared/ repo/` may remain on the server (`STALE_DIRS_CLEANUP.md`).
- Scripts were validated only in a sandbox with fake binaries; real-server behaviour REQUIRES-PRODUCTION-VERIFICATION.

**Reason for choosing flat/Apache over nginx/releases: UNKNOWN** (the repo records that the mismatch caused deploy trouble, not why Apache was chosen).

**Evidence:** decision/scripts `VERIFIED-REPO`; sandbox behaviour `VERIFIED-TEST` (deployment/tests, not executed in review); Apache/flat tree on prod `PROD-VERIFIED` (maintainer notes 2026-09-19); script behaviour on the real server `REQUIRES-PRODUCTION-VERIFICATION`; why Apache `UNKNOWN`.
