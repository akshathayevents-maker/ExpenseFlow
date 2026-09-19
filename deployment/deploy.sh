#!/usr/bin/env bash
# ExpenseFlow — hardened FLAT production deploy: git working tree at $EF_APP_DIR, served by Apache.
#
# Usage: sudo bash deployment/deploy.sh [branch] [options]
#   branch                   branch to deploy (default: main)
#   --check                  READ-ONLY preflight: verifies everything a deploy depends on and reports. Changes nothing:
#                            no fetch, no lock file, no mkdir/chown, no patch files, no maintenance, no migrations,
#                            no reloads/restarts. Exit 0 = a real deploy would pass its preflight.
#   --commit SHA             deploy an exact commit already present in the local repo (used by rollback.sh)
#   --force                  redeploy even if HEAD already equals the target
#   --maintenance            force maintenance mode for this deploy
#   --discard-local-changes  allow overwriting tracked-file edits outside storage/fonts (a patch is saved first)
#   --fix-ownership          chown ONLY paths under storage/, bootstrap/cache, vendor that the app user does not own
#   --skip-system-checks     skip Apache/Supervisor inspection, reload and worker restart (sandbox/CI only)
#   --no-http-health         skip HTTP checks in the post-deploy health check (sandbox/CI only)
#
# Fail-closed: Apache must serve $EF_APP_DIR/public for this site, Supervisor programs must run from this tree,
# build assets must be committed and complete, apache2ctl configtest must pass — otherwise NOTHING is changed.
# After the checkout, failure restores the previous commit — EXCEPT once migrations have run (see below).
# After migrations: no automatic code rollback (old code vs new schema is unsafe); the app is held in maintenance.
# Nothing is ever deleted or `git clean`ed; no chmod is ever used.
set -Eeuo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=lib.sh
source "$SCRIPT_DIR/lib.sh"

# ── Arguments ──────────────────────────────────────────────────────────────────
BRANCH=""; TARGET_SHA=""; CHECK_ONLY=0; FORCE=0; FORCE_MAINT=0; DISCARD=0; FIX_OWN=0
SKIP_SYSTEM="${EF_SKIP_SYSTEM_CHECKS:-0}"; HTTP_HEALTH=1; ROLLBACK_MODE=0
while [[ $# -gt 0 ]]; do
    case "$1" in
        --commit) TARGET_SHA="${2:?--commit needs a SHA}"; shift 2 ;;
        --check) CHECK_ONLY=1; shift ;;
        --force) FORCE=1; shift ;;
        --maintenance) FORCE_MAINT=1; shift ;;
        --discard-local-changes) DISCARD=1; shift ;;
        --fix-ownership) FIX_OWN=1; shift ;;
        --skip-system-checks) SKIP_SYSTEM=1; shift ;;
        --no-http-health) HTTP_HEALTH=0; shift ;;
        --rollback-mode) ROLLBACK_MODE=1; shift ;;   # set by rollback.sh (history labelling only)
        -h|--help) sed -n 2,23p "${BASH_SOURCE[0]}"; exit 0 ;;
        -*) echo "Unknown option: $1 (see --help)" >&2; exit 2 ;;
        *)  [[ -z "$BRANCH" ]] || { echo "Only one branch argument allowed" >&2; exit 2; }; BRANCH="$1"; shift ;;
    esac
done
[[ -z "$BRANCH" || "$BRANCH" =~ ^[A-Za-z0-9._/-]+$ ]] || { echo "Invalid branch name: $BRANCH" >&2; exit 2; }
[[ -z "$TARGET_SHA" || "$TARGET_SHA" =~ ^[0-9a-f]{7,40}$ ]] || { echo "Invalid --commit SHA" >&2; exit 2; }
[[ -n "$BRANCH" ]] || BRANCH="main"

APP="$EF_APP_DIR"
DEPLOY_START=$(date +%s)
# init → preflight → planned → maintenance → applying → applied → migrating → done
STAGE="init"
PREV_COMMIT=""; COMMIT=""; TEE_PID=""; PHP=""; COMPOSER=""; BR_NAME=""; RUN_DIR=""; HEALTH=""; COMPOSER_VER=""; REMOTE_SHA=""; CHANGED=""
MAINT_ON=0          # maintenance mode entered BY THIS RUN
MAINT_PRE=0         # maintenance was already on when we started (left by a failed deploy) — adopted, lifted on success
MIGRATED=0          # migrations were applied by this run
NEEDS_MAINT=0; WHY=""; DIRTY=""; DIRTY_BAD=""; PENDING=0
MARKER="$LOG_DIR/deploy-in-progress"   # exists only while a deploy is between "planned" and "finished/handled"
MARKER_SET=0; INTERRUPTED=0; ADOPTED_LIFTED=0   # ADOPTED_LIFTED: this run lifted a maintenance mode it did not enter
export PATH="${PATH:-/usr/bin:/bin}:/usr/local/bin:/usr/bin:/bin"
# Never let git/ssh prompt (a prompt on a terminal is exactly how a deploy "hangs").
export GIT_TERMINAL_PROMPT=0 GIT_SSH_COMMAND="${GIT_SSH_COMMAND:-ssh -o BatchMode=yes -o ConnectTimeout=20}"
export GIT_CONFIG_COUNT=1 GIT_CONFIG_KEY_0=safe.directory GIT_CONFIG_VALUE_0="$APP"

# ── Signals & exit handling ────────────────────────────────────────────────────
# Ctrl+Z/SIGTSTP/SIGTTIN/SIGTTOU are IGNORED for this whole process tree: a stopped deploy holding the lock is impossible
# via the terminal (proven with a real interactive shell in tests/). Ctrl+C / TERM / HUP abort cleanly via on_exit.
# SIGINT ignored at start (deploy launched as a background job / nohup / cron) means Ctrl+C can never work — say so.
_sigign="$(awk '/^SigIgn:/{print $2}' /proc/$$/status 2>/dev/null || true)"
if [[ -n "$_sigign" ]] && (( (16#$_sigign) & 2 )); then
    warn "SIGINT is ignored in this shell (started as a background job or via nohup): Ctrl+C will not stop this deploy. Stop it with: kill -TERM $$"
fi
ignore_job_control_signals
trap '' PIPE     # a closed terminal/pipe must not kill the script before on_exit can restore; log writes are best-effort (lib.sh)
on_signal() { err "Received SIG$1 — aborting cleanly"; forward_signal_to_child; exit "$2"; }
trap 'on_signal INT 130'  INT
trap 'on_signal TERM 143' TERM
trap 'on_signal HUP 129'  HUP
trap 'err "line $LINENO: \`$BASH_COMMAND\` failed"' ERR

# Local git calls: bounded, no stdin, lock fd closed (git may spawn a detached `gc --auto`). gitro never writes the index.
git_()  { timeout -k 5 "${GIT_TIMEOUT:-120}" git -C "$APP" "$@" </dev/null 200>&-; }
gitro() { timeout -k 5 "${GIT_TIMEOUT:-120}" git --no-optional-locks -C "$APP" "$@" </dev/null 200>&-; }
art()   { run_step "${ART_TIMEOUT:-120}" "${APP_RUN[@]}" "$PHP" "$APP/artisan" "$@" --no-interaction; }

# ── Preflight ──────────────────────────────────────────────────────────────────
count_foreign() { find "$1" -not -user "$APP_USER" 2>/dev/null | awk 'END{print NR}'; }   # awk reads everything: no SIGPIPE under pipefail

analyze_dirty() {
    DIRTY="$(gitro -c core.fileMode=false status --porcelain --untracked-files=no 2>/dev/null || true)"
    DIRTY_BAD=""
    local path
    while read -r _ path; do
        [[ -n "$path" ]] || continue
        [[ "$path" == storage/fonts/* ]] || DIRTY_BAD+="$path "
    done <<<"$DIRTY"
}

preflight_system() {
    echo "== Preflight: system =="
    if [[ $EUID -eq 0 || "${EF_ALLOW_NONROOT:-0}" == "1" ]]; then chk_ok "running as $(id -un); runtime user for composer/artisan: $APP_USER"
    else chk_fail "must run as root (sudo bash deployment/deploy.sh ...) — needs reload/supervisor rights and drops to $APP_USER itself"; fi

    local t; for t in git timeout flock find awk pgrep; do command -v "$t" >/dev/null 2>&1 || chk_fail "required tool missing: $t"; done
    command -v curl >/dev/null 2>&1 || chk_warn "curl not installed (HTTP health checks will be skipped)"
    if [[ $EUID -eq 0 && "$APP_USER" != "root" ]]; then
        id "$APP_USER" >/dev/null 2>&1 || chk_fail "app user '$APP_USER' does not exist (override: EF_APP_USER)"
        command -v runuser >/dev/null 2>&1 || chk_fail "runuser not found"
    fi

    PHP="$(detect_php || true)"
    if [[ -n "$PHP" ]]; then chk_ok "PHP $("$PHP" -r 'echo PHP_VERSION;') ($PHP)"; else chk_fail "PHP not found"; fi
    COMPOSER="$(detect_composer || true)"
    if [[ -n "$COMPOSER" && -n "$PHP" ]]; then
        # Bounded, no stdin/TTY: a version probe must never block a deploy.
        COMPOSER_VER="$(COMPOSER_NO_INTERACTION=1 COMPOSER_ALLOW_SUPERUSER=1 timeout -k 5 20 "$PHP" "$COMPOSER" --version --no-interaction </dev/null 2>/dev/null | head -n1 || true)"
        if [[ -n "$COMPOSER_VER" ]]; then chk_ok "$COMPOSER_VER ($COMPOSER)"; else chk_fail "composer probe failed/timed out ($COMPOSER)"; fi
    else
        chk_fail "Composer not found (/usr/local/bin/composer or /usr/bin/composer); the deploy never self-installs software"
    fi

    # application + git
    if [[ -f "$APP/artisan" ]]; then chk_ok "application directory $APP"; else chk_fail "not a Laravel app: $APP/artisan missing"; return 0; fi
    if [[ -r "$APP/.env" ]]; then chk_ok ".env present and readable ($(stat -c '%U:%G %a' "$APP/.env"))"; else chk_fail "$APP/.env missing or unreadable"; fi
    if [[ "$(gitro rev-parse --show-toplevel 2>/dev/null || true)" == "$(readlink -f "$APP")" ]]; then chk_ok "git repository root is $APP"; else chk_fail "$APP is not the root of a git repository"; return 0; fi
    local remote; remote="$(gitro remote get-url origin 2>/dev/null || true)"
    if [[ -n "$remote" ]]; then chk_ok "remote origin: $(sanitize_url "$remote")"; else chk_fail "no 'origin' remote"; fi
    BR_NAME="$(gitro symbolic-ref --short -q HEAD || true)"
    PREV_COMMIT="$(gitro rev-parse HEAD 2>/dev/null || true)"
    chk_info "branch: ${BR_NAME:-(detached)}   HEAD: ${PREV_COMMIT:0:7}   deploying branch: $BRANCH"
    BR_NAME="${BR_NAME:-$BRANCH}"
    if [[ -e "$APP/.git/index.lock" ]]; then chk_fail ".git/index.lock exists (an aborted git left it). Verify no git is running (pgrep -a git), then remove that one file"; else chk_ok "no stale .git/index.lock"; fi

    if [[ -z "$TARGET_SHA" && -n "$remote" ]]; then
        local out; out="$(mktemp)"
        if run_step 30 git -C "$APP" ls-remote --exit-code origin "refs/heads/$BRANCH" >"$out" 2>&1; then
            REMOTE_SHA="$(cut -f1 "$out" | head -n1)"
            chk_ok "remote reachable; origin/$BRANCH = ${REMOTE_SHA:0:7}"
        else
            REMOTE_SHA=""; chk_fail "cannot reach origin or branch '$BRANCH' is missing (credentials/network for $(id -un)): $(tail -n1 "$out" | cut -c1-160 | redact)"
        fi
        rm -f "$out"
    fi

    if [[ -e "$MARKER" ]]; then
        local mprev; mprev="$(sed -nE 's/.*[[:space:]]prev=([0-9a-f]{40}).*/\1/p' "$MARKER" 2>/dev/null | head -n1 || true)"
        if [[ $CHECK_ONLY -eq 1 ]] && ! probe_lock; then chk_info "a deploy is running right now (marker $MARKER)"
        else
            INTERRUPTED=1
            chk_warn "a previous deploy did NOT finish ($(tr '\n' ' ' <"$MARKER" | cut -c1-140)) — this run re-applies the target even if HEAD already equals it"
            if [[ -n "$mprev" ]] && gitro cat-file -e "$mprev^{commit}" 2>/dev/null; then PREV_COMMIT="$mprev"; chk_info "rollback target stays the commit before the interrupted deploy: ${mprev:0:7}"; fi
        fi
    else chk_ok "no interrupted previous deploy"; fi

    analyze_dirty
    if [[ -z "$DIRTY" ]]; then chk_ok "no tracked-file modifications (mode-only changes ignored)"
    elif [[ -z "$DIRTY_BAD" ]]; then chk_warn "$(wc -l <<<"$DIRTY") tracked path(s) modified, all dompdf font caches under storage/fonts (runtime-generated; a patch is saved, then they are restored)"
    elif [[ $DISCARD -eq 1 ]]; then chk_warn "local code edits will be discarded (--discard-local-changes): $DIRTY_BAD"
    else chk_fail "local edits outside storage/fonts would be overwritten: ${DIRTY_BAD}(deploy refuses; review with 'git diff', then --discard-local-changes)"; fi
    if gitro -c core.fileMode=true diff --summary 2>/dev/null | grep -q 'mode change'; then
        chk_info "mode-only changes present (leftover of the old recursive permission change): a checkout restores tracked modes; these scripts never change permissions"
    fi

    # ownership / writability (the "root-owned Laravel files" problem)
    local d f n crit=0 foreign=0 paths=(storage bootstrap/cache)
    [[ -d "$APP/vendor" ]] && paths+=(vendor)
    for d in "${paths[@]}"; do n="$(count_foreign "$APP/$d")"; foreign=$((foreign+n)); done
    for d in storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache vendor; do
        [[ -d "$APP/$d" ]] || continue
        as_app test -w "$APP/$d" || { crit=$((crit+1)); chk_info "not writable by $APP_USER: $d"; }
    done
    for f in bootstrap/cache/config.php storage/logs/laravel.log; do
        [[ -e "$APP/$f" ]] || continue
        as_app test -w "$APP/$f" || { crit=$((crit+1)); chk_info "not writable by $APP_USER: $f"; }
    done
    if [[ $foreign -eq 0 && $crit -eq 0 ]]; then chk_ok "storage/, bootstrap/cache, vendor/ are owned by and writable for $APP_USER"
    elif [[ $CHECK_ONLY -eq 1 || $FIX_OWN -eq 1 ]]; then
        chk_warn "$foreign path(s) under storage/bootstrap/vendor not owned by $APP_USER, $crit critical path(s) not writable — a real deploy needs --fix-ownership (chowns only those paths)"
    elif [[ $crit -gt 0 ]]; then chk_fail "$crit critical path(s) not writable by $APP_USER (root-owned caches/logs/vendor). Re-run with --fix-ownership"
    else chk_warn "$foreign path(s) not owned by $APP_USER (not critical); --fix-ownership would fix them"; fi

    # Apache + Supervisor: fail closed unless they demonstrably serve/run THIS tree
    if [[ $SKIP_SYSTEM -eq 1 ]]; then
        chk_info "Apache/Supervisor inspection skipped (--skip-system-checks)"
    else
        local ws host roots="" r rr found=0 badroots="" other=""
        ws="$(detect_webserver)"
        if [[ "$ws" != "apache2" ]]; then chk_fail "apache2 is not active (found: $ws) — production is served by Apache"
        else
            chk_ok "apache2 active"
            if apache_configtest; then chk_ok "apache2ctl configtest passes (a reload would succeed)"; else chk_fail "apache2ctl configtest FAILS — fix the Apache config first; a deploy would fail at reload after the code changed"; fi
            host="$(url_host "$(env_value "$APP/.env" APP_URL)")"
            if [[ -n "$host" ]]; then roots="$(apache_roots_for_host "$host")"; chk_info "site host from APP_URL: $host"
            else chk_warn "APP_URL host unknown; checking every vhost"; roots="$(apache_vhosts | cut -d'|' -f2 | sort -u)"; fi
            while read -r r; do
                [[ -n "$r" ]] || continue
                rr="$(readlink -m "$r")"
                if [[ "$rr" == "$(readlink -m "$APP/public")" ]]; then found=1
                elif [[ "$rr" == "$(readlink -m "$APP/current")"* || "$rr" == "$(readlink -m "$APP/releases")"* ]]; then badroots+="$r "
                else other+="$r "; fi
            done <<<"$roots"
            if [[ $found -eq 1 && -z "$badroots" ]]; then chk_ok "Apache DocumentRoot for ${host:-the site} is $APP/public"
            elif [[ -z "$roots" ]]; then chk_fail "no <VirtualHost> in the enabled Apache config names ${host:-the site} (ServerName/ServerAlias) — cannot prove Apache serves $APP/public; run deployment/diagnose-server.sh"
            else chk_fail "Apache does not serve $APP/public for ${host:-the site} (roots: $(echo "$roots" | tr '\n' ' ')${badroots:+; release-layout roots: $badroots}) — the deploy would not reach users"; fi
            [[ -z "$other" ]] || chk_warn "other vhost roots naming this host (e.g. a :80 redirect vhost): $other"
            if apache_has_modphp; then chk_info "PHP handler: mod_php (no php-fpm reload needed)"
            elif [[ -n "$(detect_fpm_units)" ]]; then chk_info "PHP handler: php-fpm ($(detect_fpm_units | tr '\n' ' '))"
            else chk_warn "no mod_php module and no active php-fpm unit detected"; fi
        fi

        if command -v supervisorctl >/dev/null 2>&1; then
            local defs name dir usr cmd tree sout s rest running=0 total=0
            defs="$(supervisor_program_defs)"
            if [[ -z "$defs" ]]; then chk_fail "no [program:expenseflow*] in the supervisor config — queue workers are not managed; cannot restart them after a deploy"
            else
                while IFS='|' read -r name dir usr cmd; do
                    tree="$dir"
                    if [[ -z "$tree" ]]; then tree="$(dirname "$(grep -oE '/[^ ]*/artisan' <<<"$cmd" | head -n1)")"; fi
                    if [[ -n "$tree" && "$(readlink -m "$tree")" == "$(readlink -m "$APP")" ]]; then chk_ok "supervisor program $name runs from $APP"
                    else chk_fail "supervisor program $name runs from '${tree:-unknown}', not $APP (wrong application tree)"; fi
                    if [[ -n "$usr" && "$usr" != "$APP_USER" ]]; then chk_warn "supervisor program $name runs as '$usr', not $APP_USER — a root worker creates root-owned storage/cache files"; fi
                done <<<"$defs"
            fi
            sout="$(privt "${EF_PRIV_TIMEOUT:-60}" supervisorctl status 2>&1 || true)"
            while read -r name s rest; do
                [[ "$name" == expenseflow* ]] || continue
                total=$((total+1)); [[ "$s" == "RUNNING" ]] && running=$((running+1))
            done <<<"$sout"
            if [[ $total -gt 0 && $running -eq $total ]]; then chk_ok "queue workers RUNNING ($running/$total)"; else chk_warn "queue workers: $running/$total RUNNING"; fi
        else
            chk_fail "supervisorctl not installed — workers cannot be managed"
        fi
    fi

    # database (read-only): boots the CURRENT tree
    if [[ -n "$PHP" && -f "$APP/vendor/autoload.php" ]]; then
        local mo; mo="$(mktemp)"
        if run_step 120 "${APP_RUN[@]}" "$PHP" "$APP/artisan" migrate:status --pending --no-interaction >"$mo" 2>&1; then
            if grep -q "No pending migrations" "$mo"; then chk_ok "database reachable; no pending migrations"; else chk_ok "database reachable; there ARE pending migrations"; fi
        else chk_warn "cannot query the database with the current tree ($(tail -n1 "$mo" | cut -c1-120)); the deploy re-checks after building"; fi
        rm -f "$mo"
    else chk_warn "vendor/autoload.php missing — database not checked (the deploy re-checks after composer)"; fi

    # build assets in the current tree, public/storage, maintenance
    local miss; miss="$(manifest_missing "$APP")"
    if [[ -z "$miss" ]]; then chk_ok "current tree: public/build/manifest.json valid, all Vite entries and assets present"; else chk_warn "current tree build problem: $miss (the deploy validates the TARGET commit)"; fi
    if [[ -L "$APP/public/storage" ]]; then
        if [[ "$(readlink -f "$APP/public/storage")" == "$(readlink -f "$APP/storage/app/public")" ]]; then chk_ok "public/storage symlink correct"; else chk_warn "public/storage symlink points elsewhere ($(readlink "$APP/public/storage")); the deploy repairs it"; fi
    elif [[ -e "$APP/public/storage" ]]; then chk_fail "public/storage is a real file/dir, not a symlink — the deploy will not delete it; move it manually"
    else chk_warn "public/storage missing (the deploy creates it)"; fi
    if [[ -e "$APP/storage/framework/down" ]]; then MAINT_PRE=1; chk_warn "application is ALREADY in maintenance mode (left by a failed deploy?) — a successful deploy will lift it"; else chk_ok "application is not in maintenance mode"; fi

    # concurrency + triggers + stale artefacts
    if [[ $CHECK_ONLY -eq 1 ]]; then
        if probe_lock; then chk_ok "no deploy holds the lock"; else chk_fail "another deploy operation holds the lock ($LOCK_FILE) — see: sudo fuser -v $LOCK_FILE"; fi
    else chk_ok "this run holds the deploy lock"; fi
    local cp; cp="$(conflicting_processes)"
    if [[ -z "$cp" ]]; then chk_ok "no conflicting deploy/composer/migrate/git processes"; else chk_fail "conflicting processes running: $(echo "$cp" | head -n 3 | tr '\n' ';' | cut -c1-200 | redact)"; fi
    local trg; trg="$(deploy_trigger_scan)"
    if [[ -z "$trg" ]]; then chk_ok "no cron/systemd deploy triggers found"; else chk_warn "possible duplicate deploy triggers: $(echo "$trg" | head -n 3 | tr '\n' ';' | cut -c1-200 | redact)"; fi
    local cr; cr="$(cron_artisan_as_root)"
    [[ -z "$cr" ]] || chk_warn "cron runs artisan as root (creates root-owned storage files): $(echo "$cr" | head -n 2 | tr '\n' ';' | cut -c1-200 | redact)"
    for d in current releases shared repo; do
        if [[ -e "$APP/$d" ]]; then chk_info "stale leftover from the old release design (unused, NOT touched): $APP/$d"; fi
    done
    if compgen -G "$APP/deployment/deploy.sh.bak*" >/dev/null; then chk_warn "old script backups exist in deployment/ (deploy.sh.bak*): never run them"; fi
    if [[ -d "$APP/deployment/legacy-release-based" && -n "$(find "$APP/deployment/legacy-release-based" -type f -perm /111 2>/dev/null | head -n 1)" ]]; then
        chk_warn "executable scripts in deployment/legacy-release-based (they must never be run)"
    fi
    local avail; avail="$(df -Pm "$APP" | awk 'NR==2{print $4}')"
    if [[ "${avail:-0}" -ge 512 ]]; then chk_ok "disk space: ${avail} MiB free"; else chk_fail "less than 512 MiB free on $APP (${avail:-?} MiB)"; fi
}

preflight_target() {   # needs $COMMIT
    echo "== Preflight: target commit ${COMMIT:0:7} =="
    local f miss
    for f in artisan composer.lock public/build/manifest.json; do
        gitro cat-file -e "$COMMIT:$f" 2>/dev/null || chk_fail "commit ${COMMIT:0:7} does not contain $f"
    done
    miss="$(manifest_missing_commit "$COMMIT")"
    if [[ -z "$miss" ]]; then chk_ok "committed build is complete: manifest valid, every Vite entry and referenced asset is tracked in git"
    else chk_fail "committed build is INCOMPLETE ($miss) — run the Vite build locally and commit public/build"; fi
    CHANGED="$(gitro diff --name-only "$PREV_COMMIT" "$COMMIT" 2>/dev/null || true)"
    NEEDS_MAINT=0; WHY=""
    if [[ $FORCE_MAINT -eq 1 ]]; then NEEDS_MAINT=1; WHY="--maintenance"; fi
    if grep -qE '^composer\.(json|lock)$' <<<"$CHANGED"; then NEEDS_MAINT=1; WHY+="${WHY:+, }composer files changed (vendor rewrite)"; fi
    if grep -qE '^database/migrations/' <<<"$CHANGED"; then
        NEEDS_MAINT=1; WHY+="${WHY:+, }migrations changed"
        chk_warn "migration files changed: TAKE A DATABASE BACKUP FIRST — after migrations run there is no safe automatic code rollback"
    fi
    chk_info "$(grep -c . <<<"$CHANGED" || true) file(s) differ between ${PREV_COMMIT:0:7} and ${COMMIT:0:7}; maintenance mode: $([[ $NEEDS_MAINT -eq 1 ]] && echo "yes ($WHY)" || echo no)"
}

summarize_preflight() {   # returns non-zero when a deploy must not proceed
    echo
    echo "Preflight: $CHK_PASS ok, $CHK_WARN warning(s), $CHK_FAIL failure(s)"
    [[ $CHK_FAIL -eq 0 ]]
}

# ── Build / restore machinery ──────────────────────────────────────────────────
fix_ownership_paths() {   # only unowned paths, only under the three runtime trees
    local d
    for d in storage bootstrap/cache vendor; do
        [[ -d "$APP/$d" ]] || continue
        find "$APP/$d" -not -user "$APP_USER" -exec chown -h "$APP_USER:$APP_GROUP" {} +
    done
}
# Tracked files under storage/ and bootstrap/cache (dompdf fonts, .gitignore) are recreated by a root checkout;
# hand them back to the app user so PDF generation can keep rewriting its font cache.
fix_tracked_runtime_ownership() {
    if [[ $EUID -eq 0 && "$APP_USER" != "root" ]]; then
        git_ ls-files -z -- storage bootstrap/cache | xargs -0 -r chown -h "$APP_USER:$APP_GROUP" || warn "could not hand some tracked runtime files to $APP_USER (PDF font cache may not be writable)"
    fi
}

build_tree() {
    local c miss
    miss="$(manifest_missing "$APP")"
    [[ -z "$miss" ]] || { err "public/build is incomplete in the checked-out tree: $miss"; return 1; }
    ok "public/build verified (manifest, Vite entries, every referenced asset present)"
    info "Composer install (timeout ${EF_COMPOSER_TIMEOUT:-600}s, non-interactive, as $APP_USER)"
    as_app mkdir -p "$COMPOSER_HOME_DIR" || return 1
    local cenv=(COMPOSER_HOME="$COMPOSER_HOME_DIR" COMPOSER_CACHE_DIR="$COMPOSER_HOME_DIR/cache" COMPOSER_NO_INTERACTION=1 COMPOSER_MEMORY_LIMIT=-1)
    [[ "$APP_USER" == "root" ]] && cenv+=(COMPOSER_ALLOW_SUPERUSER=1)
    run_step "${EF_COMPOSER_TIMEOUT:-600}" "${APP_RUN[@]}" env "${cenv[@]}" "$PHP" "$COMPOSER" install \
        --working-dir="$APP" --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-progress || { err "composer install failed"; return 1; }
    ok "Composer done"
    if [[ -L "$APP/public/storage" && "$(readlink -f "$APP/public/storage")" == "$(readlink -f "$APP/storage/app/public")" ]]; then
        skip "public/storage link already correct"
    else
        ensure_symlink "$APP/storage/app/public" "$APP/public/storage" || return 1
        ok "public/storage link ensured"
    fi
    for c in config:cache route:cache view:cache event:cache; do
        art "$c" >/dev/null || { err "artisan $c failed"; return 1; }
    done
    ok "Caches built (config, route, view, event) — all required"
}

enter_maintenance() {   # returns non-zero on failure (callers decide; it is also used from the exit handler)
    local args=(down --retry=60)
    if [[ -f "$APP/resources/views/errors/503.blade.php" ]]; then args+=(--render=errors.503)
    else info "errors/503.blade.php is not in the currently deployed tree yet — using the framework's default maintenance page for this one deploy (documented, not a suppressed error)"; fi
    art "${args[@]}" || { err "Failed to enter maintenance mode"; return 1; }
    [[ -e "$APP/storage/framework/down" ]] || { err "artisan down reported success but storage/framework/down is missing"; return 1; }
    MAINT_ON=1
}

restore_previous() {   # failure BEFORE any migration ran: put the previous commit back
    err "Restoring previous commit ${PREV_COMMIT:0:7}"
    if [[ $NEEDS_MAINT -eq 1 && $MAINT_ON -eq 0 && ! -e "$APP/storage/framework/down" ]]; then
        info "vendor/schema differ between the commits — holding the app in maintenance during the restore"
        enter_maintenance || err "could not enter maintenance mode for the restore (continuing: the previous commit must be put back)"
    fi
    GIT_TIMEOUT=300 git_ checkout -f -q -B "$BR_NAME" "$PREV_COMMIT" || { err "git checkout of the previous commit FAILED — manual: sudo bash $SCRIPT_DIR/rollback.sh"; return 1; }
    fix_tracked_runtime_ownership
    build_tree || { err "rebuild of the previous commit failed — app may be down; see $LOG_DIR"; return 1; }
    if [[ $SKIP_SYSTEM -eq 0 ]]; then reload_web_stack || err "reload failed during restore"; restart_workers || err "worker restart failed during restore"; fi
    if [[ $MAINT_ON -eq 1 ]]; then
        art up || err "artisan up failed after the restore"
        if [[ -e "$APP/storage/framework/down" ]]; then err "STILL IN MAINTENANCE MODE — run: sudo -u $APP_USER $PHP $APP/artisan up"; else MAINT_ON=0; fi
    elif [[ $MAINT_PRE -eq 1 ]]; then
        err "the application was ALREADY in maintenance mode before this deploy (left by an earlier failed run) and STAYS in it."
        err "Lift it only when sure the schema matches this code:  sudo -u $APP_USER $PHP $APP/artisan up"
    fi
    local hc=(--expect-commit "$PREV_COMMIT" --wait-workers "${EF_WORKER_WAIT:-60}" --workers-since "$DEPLOY_START")
    [[ $SKIP_SYSTEM -eq 1 ]] && hc+=(--no-system)
    [[ $HTTP_HEALTH -eq 0 ]] && hc+=(--no-http)
    if run_step 300 bash "$HEALTH" "${hc[@]}"; then
        err "RESTORED ${PREV_COMMIT:0:7}; service is healthy on the previous commit."
    else
        err "RESTORED ${PREV_COMMIT:0:7} but the health check still FAILS — investigate immediately."
    fi
}

on_exit() {
    local rc=$?
    trap - EXIT ERR
    # The restore/hold logic below must run to completion: a second Ctrl+C / TERM / HUP is ignored from here on
    # (children are bounded by timeouts; only SIGKILL can interrupt it).
    trap '' INT TERM HUP
    set +e +u
    if [[ $rc -ne 0 && "$STAGE" != "done" ]]; then
        err "DEPLOY FAILED at stage '$STAGE'${FAIL_REASON:+: $FAIL_REASON}"
        if [[ $MIGRATED -eq 1 || $ADOPTED_LIFTED -eq 1 || "$STAGE" == "migrating" ]]; then
            # Migrations ran / were running, or the app was already held in maintenance by an earlier failure (schema state
            # unknown): old code against that schema is unsafe, so NO automatic code rollback — hold maintenance instead.
            [[ -e "$APP/storage/framework/down" ]] || { err "holding the application in maintenance mode (fail closed)"; enter_maintenance || err "COULD NOT ENTER MAINTENANCE MODE — the app is live on new code: sudo -u $APP_USER $PHP $APP/artisan down"; }
            err "The application is HELD IN MAINTENANCE MODE. Code is at ${COMMIT:0:7}; the database may already use its schema."
            err "Options: (1) fix forward and re-run:  sudo bash $SCRIPT_DIR/deploy.sh $BRANCH"
            err "         (2) inspect:  sudo bash $SCRIPT_DIR/health-check.sh"
            err "         (3) only after confirming the OLD code works with the CURRENT schema (or restoring the DB backup):"
            err "             sudo bash $SCRIPT_DIR/rollback.sh --to ${PREV_COMMIT:0:7}   then   sudo -u $APP_USER $PHP $APP/artisan up"
        else
            case "$STAGE" in
                applying|applied)
                    if [[ -n "$PREV_COMMIT" ]]; then restore_previous; else err "No previous commit recorded; tree left at ${COMMIT:0:7}."; fi ;;
                maintenance|planned|preflight|init)
                    if [[ $MAINT_ON -eq 1 ]]; then art up && info "Maintenance mode lifted (nothing had been changed)."; fi ;;
            esac
        fi
    fi
    cd / 2>/dev/null
    if [[ $MARKER_SET -eq 1 ]]; then rm -f -- "$MARKER"; fi    # our own state file; only reached when the failure was HANDLED above
    if [[ "$RUN_DIR" == /tmp/ef-deploy.* && -d "$RUN_DIR" ]]; then rm -rf -- "$RUN_DIR"; fi
    if [[ -n "$TEE_PID" ]]; then
        exec >&- 2>&-
        # bounded: an orphaned grandchild that inherited our stdout would keep tee alive forever
        for _ in $(seq 1 50); do kill -0 "$TEE_PID" 2>/dev/null || break; sleep 0.2; done
    fi
    exit "$rc"
}
trap on_exit EXIT

# ── Environment & logging ──────────────────────────────────────────────────────
[[ -d "$APP" ]] || die "App directory not found: $APP"
# Composer home/cache: NOT a predictable world-writable /tmp path (cache poisoning by a local user). Created below, owner-checked.
if [[ $EUID -eq 0 ]]; then COMPOSER_HOME_DIR="${EF_COMPOSER_HOME:-/var/cache/expenseflow-composer}"; else COMPOSER_HOME_DIR="${EF_COMPOSER_HOME:-${XDG_CACHE_HOME:-$HOME/.cache}/expenseflow-composer}"; fi
if [[ $CHECK_ONLY -eq 0 ]]; then
    # Health checks must run the SAME script version this deploy started with, whatever commit the tree is on.
    RUN_DIR="$(mktemp -d /tmp/ef-deploy.XXXXXX)"
    cp "$SCRIPT_DIR/lib.sh" "$SCRIPT_DIR/health-check.sh" "$RUN_DIR/"
    HEALTH="$RUN_DIR/health-check.sh"
    install -d -m 0750 "$LOG_DIR" 2>/dev/null || true       # logs/patches: not world-readable
    if [[ $EUID -eq 0 && "$APP_USER" != "root" ]]; then install -d -m 0700 -o "$APP_USER" -g "$APP_GROUP" "$COMPOSER_HOME_DIR" || die "cannot create $COMPOSER_HOME_DIR"
    else install -d -m 0700 "$COMPOSER_HOME_DIR" || die "cannot create $COMPOSER_HOME_DIR"; fi
    [[ "$(stat -c %U "$COMPOSER_HOME_DIR")" == "$APP_USER" ]] || die "$COMPOSER_HOME_DIR is not owned by $APP_USER (pre-created by someone else?) — refusing to use it as the Composer cache"
    if [[ -d "$LOG_DIR" && -w "$LOG_DIR" ]]; then
        LOG_FILE="$LOG_DIR/deploy-$(date +%Y%m%d-%H%M%S).log"
        # --output-error=warn: a dead terminal (dropped SSH) must never break the pipe and kill the deploy mid-way
        # The logger ignores INT/HUP/QUIT: Ctrl+C or a dropped SSH session signals the whole foreground process group, and
        # a dead tee would make the script's next write raise SIGPIPE and die instantly — with no cleanup or restore.
        exec > >(trap '' INT HUP QUIT; exec tee --output-error=warn -a "$LOG_FILE") 2>&1
        TEE_PID=$!
    fi
    acquire_lock "$@"
    # We hold the deploy lock, so no other run can be using a scratch dir: sweep those left by killed runs (kill -9 / OOM).
    # Only our own pattern, only directories owned by this user, only if untouched for 5+ minutes.
    find /tmp -maxdepth 1 -type d -name 'ef-deploy.??????' -user "$(id -u)" -mmin +5 ! -path "$RUN_DIR" -exec rm -rf -- {} + 2>/dev/null \
        || warn "could not sweep stale /tmp/ef-deploy.* scratch dirs"
fi
info "ExpenseFlow deploy$([[ $CHECK_ONLY -eq 1 ]] && echo ' (--check: read-only)') — app: $APP  user: $(id -un)  app-user: $APP_USER${LOG_FILE:+  log: $LOG_FILE}"
STAGE="preflight"

preflight_system

# ── Resolve the target commit ──────────────────────────────────────────────────
if [[ -n "$TARGET_SHA" ]]; then
    COMMIT="$(gitro rev-parse --verify "$TARGET_SHA^{commit}" 2>/dev/null || true)"
    [[ -n "$COMMIT" ]] || chk_fail "commit $TARGET_SHA is not in the local repository"
elif [[ $CHECK_ONLY -eq 1 ]]; then
    # read-only: no fetch. Judge what a deploy of the ALREADY-FETCHED origin/$BRANCH would do.
    COMMIT="$(gitro rev-parse --verify "refs/remotes/origin/$BRANCH^{commit}" 2>/dev/null || true)"
    if [[ -z "$COMMIT" ]]; then chk_fail "no local origin/$BRANCH ref — run: git fetch origin $BRANCH"
    elif [[ -n "$REMOTE_SHA" && "$REMOTE_SHA" != "$COMMIT" ]]; then chk_warn "origin/$BRANCH on the remote is ${REMOTE_SHA:0:7} but the local ref is ${COMMIT:0:7} — a real deploy fetches first (or run: git fetch origin $BRANCH)"; fi
elif [[ $CHK_FAIL -eq 0 ]]; then
    info "Fetching origin/$BRANCH"
    run_step 180 git -C "$APP" fetch --prune origin "+refs/heads/$BRANCH:refs/remotes/origin/$BRANCH" || die "git fetch failed (network/credentials for $(id -un)?)"
    COMMIT="$(git_ rev-parse --verify "refs/remotes/origin/$BRANCH^{commit}")" || die "Cannot resolve origin/$BRANCH"
fi
[[ -z "$COMMIT" ]] || preflight_target

if [[ $CHECK_ONLY -eq 1 ]]; then
    if ! summarize_preflight; then err "PREFLIGHT FAILED — do not deploy. Failures:"; printf '   - %s\n' "${CHK_FAILS[@]}" >&2; STAGE="done"; exit 1; fi
    ok "PREFLIGHT PASSED. A real deploy would take ${PREV_COMMIT:0:7} → ${COMMIT:0:7}. Nothing was changed."
    STAGE="done"; exit 0
fi
if ! summarize_preflight; then printf '   - %s\n' "${CHK_FAILS[@]}" >&2; die "Preflight failed — nothing was changed"; fi

if [[ "$COMMIT" == "$PREV_COMMIT" && $FORCE -eq 0 && $INTERRUPTED -eq 0 ]]; then
    skip "HEAD is already ${COMMIT:0:7} (use --force to redeploy)"
    STAGE="done"; exit 0
fi
STAGE="planned"

# Tracked-file edits: saved as a patch BEFORE anything is overwritten
if [[ -n "$DIRTY" ]]; then
    PATCH="$LOG_DIR/dirty-$(date +%Y%m%d-%H%M%S).patch"
    ( umask 077; gitro -c core.fileMode=false diff --binary HEAD > "$PATCH" 2>/dev/null ) || warn "could not write $PATCH"   # may contain sensitive tracked content: owner-only
    info "Tracked-file edits saved as $PATCH before any overwrite"
fi

# From here on state may change: leave a marker so a kill -9 / power loss is detected by the next run
install -d -m 0750 "$LOG_DIR" && printf 'pid=%s started=%s prev=%s target=%s\n' "$$" "$(date -Is)" "$PREV_COMMIT" "$COMMIT" > "$MARKER" && MARKER_SET=1

# Ownership is fixed only when asked, only for paths the app user does not own
if [[ $FIX_OWN -eq 1 ]]; then fix_ownership_paths; ok "Ownership fixed for unowned paths under storage/, bootstrap/cache, vendor/"; fi
as_app mkdir -p "$APP/storage/app/public" "$APP/storage/framework/cache/data" "$APP/storage/framework/sessions" \
                "$APP/storage/framework/views" "$APP/storage/logs" "$APP/bootstrap/cache" || die "Cannot create runtime directories as $APP_USER"

# ── Maintenance (only when files change in a way a live request could trip over) ──
if [[ $MAINT_PRE -eq 1 ]]; then info "Adopting the pre-existing maintenance mode (it will be lifted after a verified deploy)"; fi
if [[ $NEEDS_MAINT -eq 1 && $MAINT_PRE -eq 0 ]]; then
    STAGE="maintenance"
    info "Entering maintenance mode ($WHY)"
    enter_maintenance || die "Failed to enter maintenance mode — nothing was changed"
elif [[ $NEEDS_MAINT -eq 0 ]]; then
    skip "Maintenance mode not needed (no composer/migration changes; code-only swap)"
fi

# ── Apply ──────────────────────────────────────────────────────────────────────
STAGE="applying"
info "Checking out ${COMMIT:0:7} on branch $BR_NAME (no git clean; untracked files untouched)"
GIT_TIMEOUT=300 git_ checkout -f -q -B "$BR_NAME" "$COMMIT" || die "git checkout failed"
fix_tracked_runtime_ownership
[[ "$(git_ rev-parse HEAD)" == "$COMMIT" ]] || die "HEAD is not at the target commit after checkout"
STAGE="applied"
build_tree || die "build failed"

# migrations are decided from the NEW tree so new migration files are seen
MIG_OUT="$(mktemp)"
if run_step 120 "${APP_RUN[@]}" "$PHP" "$APP/artisan" migrate:status --pending --no-interaction >"$MIG_OUT" 2>&1; then
    if grep -q "No pending migrations" "$MIG_OUT"; then PENDING=0; else PENDING=1; fi
elif grep -q "Migration table not found" "$MIG_OUT"; then
    PENDING=1; info "Database has no migrations table yet — treating as pending"
else
    tail -n 5 "$MIG_OUT" >&2; rm -f "$MIG_OUT"
    die "Cannot read migration status (database unreachable or app fails to boot)"
fi
rm -f "$MIG_OUT"
if [[ $PENDING -eq 1 ]]; then
    if [[ $MAINT_ON -eq 0 && $MAINT_PRE -eq 0 ]]; then info "Pending migrations detected — entering maintenance mode"; enter_maintenance || die "Failed to enter maintenance mode before migrating"; fi
    STAGE="migrating"
    run_step "${EF_MIGRATE_TIMEOUT:-900}" "${APP_RUN[@]}" "$PHP" "$APP/artisan" migrate --force --no-interaction || die "migrate failed"
    MIGRATED=1; STAGE="applied"
    ok "Migrations applied (from here on there is no automatic code rollback)"
else
    skip "No pending migrations"
fi

if [[ $SKIP_SYSTEM -eq 1 ]]; then
    skip "web-stack reload and worker restart (--skip-system-checks)"
else
    reload_web_stack || die "web-stack reload failed"
    restart_workers  || die "worker restart failed"
fi
if [[ $MAINT_ON -eq 1 || $MAINT_PRE -eq 1 ]]; then
    art up || die "artisan up failed"
    [[ $MAINT_PRE -eq 1 ]] && ADOPTED_LIFTED=1
    MAINT_ON=0; MAINT_PRE=0; ok "Maintenance mode lifted"
fi

# ── Verify: only after every check passes may we say "complete" ────────────────
info "Running post-deploy health check"
HC=(--expect-commit "$COMMIT" --wait-workers "${EF_WORKER_WAIT:-90}" --workers-since "$DEPLOY_START")
[[ $SKIP_SYSTEM -eq 1 ]] && HC+=(--no-system)
[[ $HTTP_HEALTH -eq 0 ]] && HC+=(--no-http)
run_step 600 bash "$HEALTH" "${HC[@]}" || die "post-deploy health check failed"

STAGE="done"
if install -d -m 0750 "$LOG_DIR" 2>/dev/null; then
    printf '%s\t%s\t%s\t%s\t%s\t%s\n' "$(date -Is)" "$PREV_COMMIT" "$COMMIT" "$BR_NAME" "$(id -un)" "$([[ $ROLLBACK_MODE -eq 1 ]] && echo ROLLBACK || echo DEPLOY)" >> "$HISTORY_FILE" 2>/dev/null || true
fi
ok "Deploy complete — ${PREV_COMMIT:0:7} → ${COMMIT:0:7} — $(( $(date +%s) - DEPLOY_START ))s"
info "Rollback (code only): sudo bash $SCRIPT_DIR/rollback.sh"
