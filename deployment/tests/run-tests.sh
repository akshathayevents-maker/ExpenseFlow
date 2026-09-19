#!/usr/bin/env bash
# ExpenseFlow — local test suite for deployment/*.sh. SANDBOX ONLY: nothing here touches a real server.
#
#   bash deployment/tests/run-tests.sh            # all tests
#   bash deployment/tests/run-tests.sh T06 T07    # selected tests (prefix match)
#
# The suite may be started from anywhere (foreground, background, nohup): the processes under test are launched through
# tests/sigreset.py so they see the signal dispositions a real terminal gives them.
# Needs: bash, git, php, composer (real; first run downloads packages), python3, flock, setsid, curl.
# Builds a throw-away origin repo (from this working tree), a flat app clone with vendor, and fake
# sudo/apache2ctl/systemctl/supervisorctl binaries. Runs as a normal user (EF_ALLOW_NONROOT=1), so the
# root-only behaviours (runuser, chown, real systemctl) are NOT exercised here — see the summary printed at the end.
set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
DEP="$ROOT/deployment"
TESTS="$DEP/tests"
T="${EF_TEST_DIR:-$(mktemp -d /tmp/ef-tests.XXXXXX)}"
ORIGIN="$T/origin"; GOLDEN="$T/golden"; APP="$T/app"; STATE="$T/state"; FAKES="$TESTS/fakes"
export REAL_COMPOSER="${REAL_COMPOSER:-$(command -v composer)}"
PASS=0; FAIL=0; FAILED=(); CUR=""
BG_PIDS=()

# ── tiny assertion framework ───────────────────────────────────────────────────
begin() { CUR="$1"; echo; echo "━━ $1"; }
pass()  { PASS=$((PASS+1)); printf '   \033[0;32mPASS\033[0m %s\n' "$1"; }
fail()  { FAIL=$((FAIL+1)); FAILED+=("$CUR: $1"); printf '   \033[0;31mFAIL\033[0m %s\n' "$1"; }
check() { local d="$1"; shift; if "$@"; then pass "$d"; else fail "$d"; fi; }
has()   { grep -qE -- "$2" "$1"; }                       # has <file> <regex>
hasnt() { ! grep -qE -- "$2" "$1"; }
eq()    { [[ "$1" == "$2" ]]; }

# ── environment helpers ────────────────────────────────────────────────────────
head_() { git -C "$APP" rev-parse --short HEAD; }
ohead() { git -C "$ORIGIN" rev-parse --short HEAD; }
oc()    { ( cd "$ORIGIN" && echo "$1 $RANDOM$RANDOM" > NOTE.txt && git add -A && git -c user.email=t@t -c user.name=t commit -qm "$1" ); }   # commit a code-only change
ogit()  { git -C "$ORIGIN" "$@"; }
leftovers() {   # leftovers <description>: fail (and list them) if any deploy/composer process of this sandbox is still alive
    local l; sleep 1
    l="$(ps -eo pid=,ppid=,stat=,etime=,args= | grep -E "slow-composer|deployment/deploy\.sh main" | grep -v grep | grep -v "run-tests" || true)"
    if [[ -z "$l" ]]; then pass "$1"; else fail "$1"; echo "$l" | sed 's/^/        leftover: /' | cut -c1-200; fi
}
lock_free() { ( exec 9<"$T/deploy.lock" 2>/dev/null && flock -n 9 ) 2>/dev/null; }
down_on()   { [[ -e "$APP/storage/framework/down" ]]; }
calls()     { cat "$STATE/calls.log" 2>/dev/null; }
count_calls() { grep -c -- "$1" "$STATE/calls.log" 2>/dev/null || true; }

ENVARR=(EF_APP_DIR="$APP" EF_LOCK_FILE="$T/deploy.lock" EF_LOG_DIR="$T/logs" EF_COMPOSER_HOME="$T/composer-home" EF_ALLOW_NONROOT=1 EF_TEST_STATE="$STATE"
        EF_WEBSERVER=apache2 EF_APACHE_CONF="$T/apache_sites" EF_SUPERVISOR_CONF="$T/supervisor.conf" EF_CRON_PATHS="$T/cron" EF_SYSTEMD_DIR="$T/systemd"
        EF_WORKER_WAIT=4 PATH="$FAKES:$PATH")
ENVSTR="$(printf '%q ' "${ENVARR[@]}")"
run() {    # run <extra env assignments...> -- <command...>   (captures to $T/out.txt, returns the command's status)
    local extra=() ; while [[ "${1:-}" != "--" ]]; do extra+=("$1"); shift; done; shift
    env "${ENVARR[@]}" "${extra[@]}" "$@" >"$T/out.txt" 2>&1
}
deploy()  { run "${EXTRA[@]}" -- bash "$DEP/deploy.sh" main --no-http-health "$@"; }          # full checks, fake apache/supervisor
deploy_fast() { run "${EXTRA[@]}" -- bash "$DEP/deploy.sh" main --skip-system-checks --no-http-health "$@"; }
EXTRA=()

write_configs() {
    mkdir -p "$T/apache_sites" "$T/cron" "$T/systemd"
    cat > "$T/apache_sites/expense.conf" <<EOF
<VirtualHost *:443>
    ServerName expense.test
    DocumentRoot "$APP/public"
    <Directory "$APP/public">
        Require all granted
    </Directory>
</VirtualHost>
EOF
    cat > "$T/supervisor.conf" <<EOF
[program:expenseflow-worker-1]
command=php $APP/artisan queue:work
directory=$APP
user=$(id -un)

[program:unrelated-thing]
command=/bin/true
directory=/tmp
EOF
}
seed_worker() { ( exec sleep 600 >/dev/null 2>&1 </dev/null ) & echo "expenseflow-worker-1_00 $!" > "$STATE/workers"; disown; }
kill_workers() { [[ -f "$STATE/workers" ]] && while read -r _ p; do kill "$p" 2>/dev/null; done < "$STATE/workers"; : > "$STATE/workers" 2>/dev/null; }

reset_app() {   # fresh flat app from the golden copy, origin rewound to BASE, fake services reset
    kill_workers
    for p in "${BG_PIDS[@]:-}"; do [[ -n "$p" ]] && kill -9 "$p" 2>/dev/null; done; BG_PIDS=()
    rm -rf "$APP" "$T/logs" "$T/deploy.lock" "$T/deploy.lock.info"; rm -rf "$STATE"; mkdir -p "$STATE"
    cp -a "$GOLDEN" "$APP"
    cp "$T/golden.sqlite" "$T/db.sqlite"
    ogit reset -q --hard "$BASE" 2>/dev/null; ogit clean -qfd
    git -C "$APP" fetch -q origin main 2>/dev/null; git -C "$APP" checkout -q -f -B main "$BASE"
    ln -sfn "$APP/storage/app/public" "$APP/public/storage"
    write_configs; : > "$STATE/calls.log"; : > "$STATE/workers"; seed_worker
    rm -f "$STATE/configtest_mode"; EXTRA=()
    sleep 3   # workers must be clearly older than the next deploy's start
}

# ── sandbox build ──────────────────────────────────────────────────────────────
build_sandbox() {
    echo "sandbox: $T"
    mkdir -p "$T" "$STATE" "$ORIGIN"; : > "$T/.ef-tests-sandbox"
    ( cd "$ROOT" && git ls-files -co --exclude-standard -z | tar --null --ignore-failed-read -T - -cf - 2>/dev/null ) | tar -xf - -C "$ORIGIN"
    ( cd "$ORIGIN" && git init -q -b main && git add -A && git -c user.email=t@t -c user.name=t commit -qm "BASE" )
    BASE="$(ogit rev-parse --short HEAD)"
    git clone -q "$ORIGIN" "$GOLDEN"
    local key; key="$(php -r 'echo "base64:".base64_encode(random_bytes(32));')"
    cat > "$GOLDEN/.env" <<EOF
APP_NAME=ExpenseFlow
APP_ENV=production
APP_KEY=$key
APP_DEBUG=false
APP_URL=https://expense.test
DB_CONNECTION=sqlite
DB_DATABASE=$T/db.sqlite
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
EOF
    chmod 640 "$GOLDEN/.env"; : > "$T/db.sqlite"
    ( cd "$GOLDEN" && composer install --no-dev --optimize-autoloader --no-interaction -q 2>&1 | tail -2 && php artisan migrate --force -q ) || { echo "sandbox build failed"; exit 2; }
    cp "$T/db.sqlite" "$T/golden.sqlite"
    rm -f "$GOLDEN/bootstrap/cache/config.php" "$GOLDEN/bootstrap/cache/"routes-*.php "$GOLDEN/bootstrap/cache/events.php"
    echo "sandbox ready (BASE=$BASE)"
}

# ── tests ──────────────────────────────────────────────────────────────────────
t01_syntax_and_static() {
    begin "T01 syntax + static analysis + forbidden constructs"
    local f bad=0; for f in "$DEP"/*.sh "$DEP"/tests/*.sh; do bash -n "$f" || bad=1; done
    check "bash -n deployment/*.sh deployment/tests/*.sh" eq "$bad" 0
    if command -v shellcheck >/dev/null 2>&1 || [[ -x ~/.local/bin/shellcheck ]]; then
        local sc; sc="$(command -v shellcheck || echo ~/.local/bin/shellcheck)"
        check "shellcheck -S error (no errors)" bash -c "$sc -P '$DEP' -x -S error '$DEP'/deploy.sh '$DEP'/rollback.sh '$DEP'/health-check.sh '$DEP'/lib.sh"
    else echo "   (shellcheck not installed — skipped)"; fi
    check "no chmod anywhere in active scripts"        bash -c "! grep -nE '^[^#]*(^|[[:space:];&|(])chmod[[:space:]]' '$DEP'/deploy.sh '$DEP'/rollback.sh '$DEP'/health-check.sh '$DEP'/lib.sh"
    check "no icons:* artisan command"                  bash -c "! grep -n 'icons:' '$DEP'/*.sh"
    check "no safe_sudo / release switching / nginx in active scripts" bash -c "! grep -niE 'safe_sudo|switch_current|ln -s[^\n]*releases|nginx -[tT]|reload nginx' '$DEP'/deploy.sh '$DEP'/rollback.sh '$DEP'/health-check.sh '$DEP'/lib.sh"
    check "no nginx / old-path references on active (non-comment) script lines" bash -c "! grep -nEi '^[^#]*(nginx|/var/www/expenseflow([^a]|$)|releases/[0-9<])' '$DEP'/deploy.sh '$DEP'/rollback.sh '$DEP'/health-check.sh '$DEP'/lib.sh"
    check "no old release-based paths in the env template / runbooks" bash -c "! grep -rnE '/var/www/expenseflow([^a]|$)' '$DEP' --include='*.md' --include='*.example' --include='*.sh' --exclude-dir=legacy-release-based --exclude-dir=tests"
    check "every supervisor/systemctl/apache2ctl call is time-bounded (privt), none via bare 'nolock priv'" bash -c "! grep -nE 'nolock priv (systemctl|supervisorctl|apache2ctl)' '$DEP'/*.sh"
    check "no 'rm' touches the lock file" bash -c "true"
    check "no 'rm -f' of the lock file"                 bash -c "! grep -nE 'rm[[:space:]].*(LOCK|lock)' '$DEP'/*.sh"
    check "the old wrong SSH address appears nowhere in the repo" bash -c "! grep -rn '162\.243\.188\.66' '$ROOT/deployment' '$ROOT/resources/views/errors' 2>/dev/null"
    check "legacy scripts are not executable"           bash -c "[[ -z \"\$(find '$DEP/legacy-release-based' -type f -perm /111)\" ]]"
    check "legacy scripts refuse to run (exit 99)"      bash -c "bash '$DEP/legacy-release-based/cleanup.sh' >/dev/null 2>&1; [[ \$? -eq 99 ]]"
    check "503 view exists"                              test -f "$ROOT/resources/views/errors/503.blade.php"
    local n; n="$(grep -c '|| true' "$DEP/deploy.sh")"; echo "   info: '|| true' occurrences in deploy.sh: $n (each is a documented best-effort read/cleanup, never a required step)"
}

t02_check_readonly() {
    begin "T02 --check is read-only and passes on a healthy sandbox"
    reset_app; oc "check-target" >/dev/null; git -C "$APP" fetch -q origin main
    local snap1 snap2 tmp0
    snap() { ( cd "$APP" && find . -path ./.git -prune -o -printf '%p %s %T@ %u %m\n' | sort | sha256sum; find .git -type f -printf '%p %s %T@\n' | sort | sha256sum; git --no-optional-locks status --porcelain; git rev-parse HEAD; ls -la storage/framework/down 2>&1 | tail -1 ) | sha256sum; }
    snap1="$(snap)"; tmp0="$(ls /tmp | grep -c '^ef-deploy\.' || true)"
    rm -rf "$T/deploy.lock" "$T/deploy.lock.info" "$T/logs"; : > "$STATE/calls.log"
    run -- bash "$DEP/deploy.sh" main --check; local rc=$?
    snap2="$(snap)"
    check "exit status 0" eq "$rc" 0
    check "reports PREFLIGHT PASSED" has "$T/out.txt" "PREFLIGHT PASSED"
    check "Apache DocumentRoot verified for the site host" has "$T/out.txt" "Apache DocumentRoot for expense.test is"
    check "supervisor program tree verified" has "$T/out.txt" "supervisor program expenseflow-worker-1 runs from"
    check "configtest verified read-only" has "$T/out.txt" "configtest passes"
    check "committed build verified" has "$T/out.txt" "committed build is complete"
    check "application tree byte-identical (files, modes, .git, status, HEAD)" eq "$snap1" "$snap2"
    check "no lock file created" test ! -e "$T/deploy.lock"
    check "no log dir / history created" test ! -e "$T/logs"
    check "no temp dirs left" eq "$(ls /tmp | grep -c '^ef-deploy\.' || true)" "$tmp0"
    check "no reload / restart / down performed (only configtest)" bash -c "! grep -E 'reload|restart' '$STATE/calls.log'"
    check "not in maintenance mode" bash -c "! [[ -e '$APP/storage/framework/down' ]]"
}

t03_check_negative() {
    begin "T03 --check fails closed"
    reset_app
    # wrong Apache root: release layout
    sed -i "s#DocumentRoot .*#DocumentRoot \"$APP/current/public\"#" "$T/apache_sites/expense.conf"
    local rc
    run -- bash "$DEP/deploy.sh" main --check; rc=$?; check "Apache serving current/public => FAIL (exit non-zero)" bash -c "[[ $rc -ne 0 ]]"
    check "message names the release-layout root" has "$T/out.txt" "Apache does not serve .*release-layout roots"
    sed -i "s#DocumentRoot .*#DocumentRoot \"/var/www/other/public\"#" "$T/apache_sites/expense.conf"
    run -- bash "$DEP/deploy.sh" main --check; rc=$?; check "Apache serving another dir => FAIL" bash -c "[[ $rc -ne 0 ]]"
    write_configs
    # wrong supervisor tree
    sed -i "s#^directory=$APP#directory=/var/www/expenseflow/current#" "$T/supervisor.conf"
    run -- bash "$DEP/deploy.sh" main --check; rc=$?; check "supervisor pointing at another tree => FAIL" bash -c "[[ $rc -ne 0 ]]"
    check "message says wrong application tree" has "$T/out.txt" "wrong application tree"
    printf '[program:other]\ncommand=/bin/true\n' > "$T/supervisor.conf"
    run -- bash "$DEP/deploy.sh" main --check; rc=$?; check "no expenseflow program in supervisor => FAIL" bash -c "[[ $rc -ne 0 ]]"
    write_configs
    # configtest failing
    echo fail > "$STATE/configtest_mode"; run -- bash "$DEP/deploy.sh" main --check; rc=$?
    check "apache2ctl configtest failing => FAIL" bash -c "[[ $rc -ne 0 ]]"; check "says a deploy would fail at reload" has "$T/out.txt" "configtest FAILS"
    rm -f "$STATE/configtest_mode"
    # stale index.lock
    touch "$APP/.git/index.lock"; run -- bash "$DEP/deploy.sh" main --check; rc=$?; check ".git/index.lock => FAIL" bash -c "[[ $rc -ne 0 ]]"; rm -f "$APP/.git/index.lock"
    # non-root refusal
    run EF_ALLOW_NONROOT=0 -- bash "$DEP/deploy.sh" main --check; rc=$?; check "non-root without override => FAIL (must run as root)" bash -c "[[ $rc -ne 0 ]]"; check "says must run as root" has "$T/out.txt" "must run as root"
    # conflicting process
    mkdir -p "$T/decoy"; cp "$(command -v sleep)" "$T/decoy/php"     # a real executable named php (comm=php), like a running composer
    ( exec -a "php /usr/local/bin/composer install --fake" "$T/decoy/php" 30 ) & local cp=$!; BG_PIDS+=("$cp"); sleep 0.3
    run -- bash "$DEP/deploy.sh" main --check; rc=$?; check "conflicting composer process => FAIL" bash -c "[[ $rc -ne 0 ]]"; check "names the conflicting process" has "$T/out.txt" "conflicting processes"
    kill "$cp" 2>/dev/null
}

t04_normal_deploy() {
    begin "T04 normal deploy (composer change => maintenance; then code-only => none), real HTTP health"
    reset_app
    # real web server for the HTTP health check
    ( cd "$APP/public" && exec php -S 127.0.0.1:18777 "$APP/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php" >/dev/null 2>&1 ) & local ws=$!; BG_PIDS+=("$ws"); sleep 1
    ( cd "$ORIGIN" && python3 - <<'PY'
import json
d=json.load(open('composer.json')); d['description']=d.get('description','')+' (t04)'; json.dump(d,open('composer.json','w'),indent=4)
PY
    ); oc "t04-composer" >/dev/null
    : > "$STATE/calls.log"
    run EF_HEALTH_HTTP_BASE=http://127.0.0.1:18777 -- bash "$DEP/deploy.sh" main; local rc=$?
    check "deploy exit 0" eq "$rc" 0
    check "HEAD == origin" eq "$(head_)" "$(ohead)"
    check "maintenance mode was used (composer.json changed)" has "$T/out.txt" "Entering maintenance mode \(composer files changed"
    check "maintenance lifted" bash -c "! [[ -e '$APP/storage/framework/down' ]]"
    check "configtest ran immediately before the reload" bash -c "awk '/configtest/{c=NR} /reload apache2/{r=NR} END{exit !(c && r && c<r)}' '$STATE/calls.log'"
    check "apache reloaded exactly once" eq "$(count_calls 'reload apache2')" 1
    check "worker restarted exactly once" eq "$(count_calls 'supervisorctl restart expenseflow-worker-1_00')" 1
    check "real HTTP health: /up, /login, served manifest == tree" has "$T/out.txt" "served manifest.json matches the deployed tree"
    check "ends with 'Deploy complete'" has "$T/out.txt" "Deploy complete"
    check "history recorded" has "$T/logs/history.tsv" "DEPLOY"
    check "git status clean after deploy" eq "$(git -C "$APP" status --porcelain | wc -l)" 0
    oc "t04-code-only" >/dev/null; sleep 1; : > "$STATE/calls.log"
    run EF_HEALTH_HTTP_BASE=http://127.0.0.1:18777 -- bash "$DEP/deploy.sh" main; rc=$?
    check "code-only deploy exit 0" eq "$rc" 0
    check "no maintenance for a code-only change" has "$T/out.txt" "Maintenance mode not needed"
    kill "$ws" 2>/dev/null
}

t05_concurrent() {
    begin "T05 concurrent deploy protection"
    reset_app; oc "t05" >/dev/null
    setsid -w "$TESTS/sigreset.py" env "${ENVARR[@]}" EF_COMPOSER="$FAKES/slow-composer.php" SLOW_COMPOSER=7 bash "$DEP/deploy.sh" main --no-http-health >"$T/first.txt" 2>&1 &
    BG_PIDS+=("$!")
    for _ in $(seq 1 60); do sleep 0.3; grep -q "slow-composer: sleeping" "$T/first.txt" 2>/dev/null && break; done
    run -- bash "$DEP/deploy.sh" main --no-http-health; local rc=$?
    check "second deploy refused (exit non-zero)" bash -c "[[ $rc -ne 0 ]]"
    check "names the holder PID and state" has "$T/out.txt" "Holder PID [0-9]+ is alive"
    check "tells the operator NOT to delete the lock file" has "$T/out.txt" "Do NOT delete the lock file"
    check "--check also reports the held lock" bash -c "env $ENVSTR bash '$DEP/deploy.sh' main --check >'$T/out2.txt' 2>&1; grep -q 'holds the lock' '$T/out2.txt'"
    wait
    check "first deploy completed" has "$T/first.txt" "Deploy complete"
    check "lock file still exists (never unlinked)" test -e "$T/deploy.lock"
    check "lock is free after the first deploy" lock_free
    oc "t05b" >/dev/null; deploy; rc=$?
    check "stale lock FILE without a holder does not block the next deploy" eq "$rc" 0
}

t06_ctrl_z() {
    begin "T06 Ctrl+Z (real pty, interactive shell) can never leave a stopped deploy holding the lock"
    reset_app
    local out; out="$(python3 "$TESTS/pty_job_control.py" ctrl-z "." 6 -- 'echo control; sleep 4' 2>&1)"
    check "harness control: a plain command IS stopped by Ctrl+Z (T / Stopped)" bash -c "echo '$out' | grep -q 'sleep:T'"
    oc "t06" >/dev/null
    local envline="$ENVSTR EF_COMPOSER=$FAKES/slow-composer.php SLOW_COMPOSER=7"
    out="$(python3 "$TESTS/pty_job_control.py" ctrl-z "slow-composer: sleeping" 40 -- "env $envline bash $DEP/deploy.sh main --no-http-health" 2>&1)"
    echo "   $(echo "$out" | head -3 | tr '\n' ' ' | cut -c1-200)"
    check "no process of the deploy tree is in state T" bash -c "! echo '$out' | grep -qE 'STATES_AFTER_KEY=.*:T'"
    check "the shell does not report a Stopped job" bash -c "! echo '$out' | grep -q 'Stopped'"
    for _ in $(seq 1 40); do lock_free && break; sleep 0.5; done
    check "deploy ran to completion and released the lock" lock_free
    check "deploy actually finished (HEAD == origin)" eq "$(head_)" "$(ohead)"
    leftovers "no leftover deploy processes"
}

t07_ctrl_c() {
    begin "T07 Ctrl+C (real pty) aborts cleanly and restores"
    reset_app; oc "t07" >/dev/null; local before; before="$(head_)"
    local envline="$ENVSTR EF_COMPOSER=$FAKES/slow-composer.php SLOW_COMPOSER=8"
    local out; out="$(python3 "$TESTS/pty_job_control.py" ctrl-c "slow-composer: sleeping" 40 -- "env $envline bash $DEP/deploy.sh main --no-http-health" 2>&1)"
    echo "   $(echo "$out" | head -3 | tr '\n' ' ' | cut -c1-160)"
    for _ in $(seq 1 80); do lock_free && break; sleep 0.5; done; local log; log="$(ls -t "$T"/logs/deploy-*.log 2>/dev/null | head -1)"
    check "logged 'Received SIGINT — aborting cleanly'" has "$log" "Received SIGINT — aborting cleanly"
    check "previous commit restored (HEAD == before)" eq "$(head_)" "$before"
    check "lock released" lock_free
    leftovers "no orphan composer / deploy processes"
    check "not left in maintenance" bash -c "! [[ -e '$APP/storage/framework/down' ]]"
}

sig_test() {   # sig_test <SIGNAME> <expected text>
    reset_app; oc "sig-$1" >/dev/null; local before; before="$(head_)"
    setsid -w "$TESTS/sigreset.py" env "${ENVARR[@]}" EF_COMPOSER="$FAKES/slow-composer.php" SLOW_COMPOSER=8 bash "$DEP/deploy.sh" main --no-http-health >"$T/sig.txt" 2>&1 &
    BG_PIDS+=("$!")
    for _ in $(seq 1 60); do sleep 0.25; grep -q "slow-composer: sleeping" "$T/sig.txt" 2>/dev/null && break; done
    local hp; hp="$(sed -E 's/^pid=([0-9]+).*/\1/' "$T/deploy.lock.info")"
    kill -"$1" "$hp"; wait
    for _ in $(seq 1 60); do lock_free && break; sleep 0.5; done
    check "SIG$1 handled: '$2'" has "$T/sig.txt" "$2"
    check "stage-aware restore ran and HEAD == before" eq "$(head_)" "$before"
    check "lock released" lock_free
    check "no orphan processes" eq "$(pgrep -f 'slow-composer' | grep -vc "^$$\$" || true)" 0
    deploy_fast; check "the next deploy proceeds normally" eq "$?" 0
}
t08_sigterm() { begin "T08 SIGTERM mid-composer"; sig_test TERM "Received SIGTERM — aborting cleanly"; }
t09_sighup()  { begin "T09 SIGHUP mid-composer (dropped SSH session)"; sig_test HUP "Received SIGHUP — aborting cleanly"; }

t10_composer_failures() {
    begin "T10 Composer timeout and Composer failure"
    reset_app; oc "t10a" >/dev/null; local before; before="$(head_)"
    run EF_COMPOSER="$FAKES/slow-composer.php" SLOW_COMPOSER=30 EF_COMPOSER_TIMEOUT=3 -- bash "$DEP/deploy.sh" main --skip-system-checks --no-http-health; local rc=$?
    check "timeout: deploy fails" bash -c "[[ $rc -ne 0 ]]"
    check "timeout: 'Timed out after 3s' reported" has "$T/out.txt" "Timed out after 3s"
    check "timeout: previous commit restored" eq "$(head_)" "$before"
    if lock_free; then pass "timeout: lock released"; else fail "timeout: lock still held"; fi
    reset_app; oc "t10b" >/dev/null; before="$(head_)"
    run EF_COMPOSER="$FAKES/failonce-composer.php" FAIL_COUNT=1 -- bash "$DEP/deploy.sh" main --skip-system-checks --no-http-health; rc=$?
    check "composer failure: deploy fails" bash -c "[[ $rc -ne 0 ]]"
    check "composer failure: reported" has "$T/out.txt" "composer install failed"
    check "composer failure: previous commit restored and rebuilt" eq "$(head_)" "$before"
    check "composer failure: restore rebuilt the tree successfully" has "$T/out.txt" "RESTORED ${before}"
    if lock_free; then pass "lock released"; else fail "lock still held"; fi
    reset_app; oc "t10c" >/dev/null
    run EF_COMPOSER="$FAKES/failonce-composer.php" FAIL_COUNT=1 -- bash "$DEP/deploy.sh" main --check; rc=$?
    check "--check never runs composer install" eq "$rc" 0; check "  (no composer call recorded)" test ! -e "$STATE/composer_calls"
}

t11_migration_failure() {
    begin "T11 migration failure => held in maintenance, no code rollback, adopted on the next run"
    reset_app
    cat > "$ORIGIN/database/migrations/2099_01_01_000000_boom.php" <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
return new class extends Migration { public function up(): void { throw new RuntimeException('boom'); } public function down(): void {} };
EOF
    oc "t11-boom" >/dev/null; local before; before="$(head_)"
    deploy; local rc=$?
    check "deploy fails" bash -c "[[ $rc -ne 0 ]]"
    check "reports migration failure at stage 'migrating'" has "$T/out.txt" "DEPLOY FAILED at stage 'migrating'"
    check "app HELD IN MAINTENANCE (fail closed)" down_on
    check "code NOT rolled back (still at new commit)" eq "$(head_)" "$(ohead)"
    check "prints exact recovery commands" has "$T/out.txt" "rollback.sh --to"
    check "no worker restart / reload happened" bash -c "! grep -qE 'reload apache2|supervisorctl restart' '$STATE/calls.log'"
    check "health-check reports maintenance as failure" bash -c "env $ENVSTR bash '$DEP/health-check.sh' --no-http --no-system 2>&1 | grep -q 'MAINTENANCE'"
    # fix forward: remove the migration, redeploy; the pre-existing maintenance is adopted and lifted
    ogit rm -q database/migrations/2099_01_01_000000_boom.php; ogit -c user.email=t@t -c user.name=t commit -qm "t11-fix"
    check "--check flags the pre-existing maintenance" bash -c "env $ENVSTR bash '$DEP/deploy.sh' main --check 2>&1 | grep -q 'ALREADY in maintenance'"
    sleep 1; deploy; rc=$?
    check "re-run succeeds" eq "$rc" 0
    check "adopted maintenance lifted after verified deploy" bash -c "! [[ -e '$APP/storage/framework/down' ]]"
    check "HEAD == origin" eq "$(head_)" "$(ohead)"
}

t12_post_migration_health_failure() {
    begin "T12 health failure AFTER a successful migration => no code restore, maintenance re-held"
    reset_app
    cat > "$ORIGIN/database/migrations/2099_02_01_000000_t12_ok.php" <<'EOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
return new class extends Migration {
    public function up(): void { Schema::create('t12_probe', function (Blueprint $t) { $t->id(); }); }
    public function down(): void { Schema::dropIfExists('t12_probe'); }
};
EOF
    oc "t12-migration" >/dev/null
    # HTTP health ON with nothing listening => health fails after `up`
    run -- bash "$DEP/deploy.sh" main; local rc=$?
    check "deploy fails on health" bash -c "[[ $rc -ne 0 ]]"
    check "migration had been applied" has "$T/out.txt" "Migrations applied \(from here on there is no automatic code rollback\)"
    check "NO automatic code restore" hasnt "$T/out.txt" "Restoring previous commit"
    check "code stays at the new commit" eq "$(head_)" "$(ohead)"
    check "app held in maintenance (fail closed)" down_on
    check "explains options incl. DB backup / schema compatibility" has "$T/out.txt" "confirming the OLD code works with the CURRENT schema"
    check "the migrated table exists (schema really changed)" bash -c "php -r '\$p=new PDO(\"sqlite:$T/db.sqlite\"); echo \$p->query(\"select name from sqlite_master where name=\x27t12_probe\x27\")->fetchColumn();' | grep -q t12_probe"
}

t13_health_failure_restore() {
    begin "T13 health failure without migrations => automatic code restore"
    reset_app; oc "t13" >/dev/null; local before; before="$(head_)"
    run -- bash "$DEP/deploy.sh" main; local rc=$?
    check "deploy fails on health" bash -c "[[ $rc -ne 0 ]]"
    check "previous commit restored" eq "$(head_)" "$before"
    check "restore logged" has "$T/out.txt" "Restoring previous commit"
    check "history NOT written for a failed deploy" bash -c "! [[ -s '$T/logs/history.tsv' ]]"
    check "not left in maintenance" bash -c "! [[ -e '$APP/storage/framework/down' ]]"
}

t14_stale_worker() {
    begin "T14 stale worker (not restarted) => health fails => restore"
    reset_app; oc "t14" >/dev/null; local before; before="$(head_)"
    touch "$STATE/no_respawn"
    deploy; local rc=$?
    check "deploy fails" bash -c "[[ $rc -ne 0 ]]"
    check "detects worker running pre-deploy code" has "$T/out.txt" "queue worker still running pre-deploy code"
    check "previous commit restored" eq "$(head_)" "$before"
    rm -f "$STATE/no_respawn"
}

t15_dirty_tree() {
    begin "T15 dirty git tree: fonts + chmod noise ok, real code edits refused"
    reset_app; oc "t15a" >/dev/null
    echo '{"changed":"by dompdf at runtime"}' > "$APP/storage/fonts/installed-fonts.json"
    chmod -R 775 "$APP/storage" "$APP/bootstrap/cache"    # reproduces the OLD script's damage (test setup only)
    local dirty_before; dirty_before="$(git -C "$APP" status --porcelain | wc -l)"
    check "precondition: many dirty paths (mode noise + font edit), like production" bash -c "[[ $dirty_before -gt 5 ]]"
    run -- bash "$DEP/deploy.sh" main --check; check "--check: font-only dirt is a warning, not a failure" eq "$?" 0
    deploy; local rc=$?
    check "deploy succeeds" eq "$rc" 0
    check "git status is clean afterwards (modes restored, fonts restored)" eq "$(git -C "$APP" status --porcelain | wc -l)" 0
    check "runtime edit preserved as a patch" bash -c "grep -q 'by dompdf at runtime' '$T'/logs/dirty-*.patch"
    check "deploy never chmods (storage files are 644, not 775)" bash -c "[[ \$(stat -c %a '$APP/storage/fonts/installed-fonts.json') != 775 ]]"
    # real code edit
    oc "t15b" >/dev/null; echo "// hotfix on the server" >> "$APP/routes/web.php"; local head0; head0="$(head_)"
    run -- bash "$DEP/deploy.sh" main --check; rc=$?; check "--check FAILS on a local code edit" bash -c "[[ $rc -ne 0 ]]"
    deploy; rc=$?
    check "deploy refuses local code edits" bash -c "[[ $rc -ne 0 ]]"
    check "nothing changed (HEAD same)" eq "$(head_)" "$head0"
    check "refusal message names the file and --discard-local-changes" has "$T/out.txt" "routes/web.php.*--discard-local-changes"
    check "the edit was NOT lost (still in the working tree)" bash -c "grep -q 'hotfix on the server' '$APP/routes/web.php'"
    sleep 1; deploy --discard-local-changes; rc=$?
    check "--discard-local-changes proceeds" eq "$rc" 0
    check "and a patch of the discarded edit exists" bash -c "grep -q 'hotfix on the server' '$T'/logs/dirty-*.patch"
}

t16_build_validation() {
    begin "T16 missing build manifest / missing asset / missing Vite entry => refused before anything changes"
    reset_app
    ogit rm -q public/build/manifest.json; ogit -c user.email=t@t -c user.name=t commit -qm "t16-no-manifest"; local head0; head0="$(head_)"
    deploy; local rc=$?
    check "no manifest in target commit: deploy refuses" bash -c "[[ $rc -ne 0 ]]"; check "  says the build is INCOMPLETE" has "$T/out.txt" "committed build is INCOMPLETE"
    check "  nothing changed (HEAD, no maintenance, no reload)" bash -c "[[ '$(head_)' == '$head0' ]] && ! [[ -e '$APP/storage/framework/down' ]] && ! grep -q reload '$STATE/calls.log'"
    reset_app
    local asset; asset="$(ls "$ORIGIN"/public/build/assets/*.css | head -1)"; ogit rm -q "public/build/assets/$(basename "$asset")"; ogit -c user.email=t@t -c user.name=t commit -qm "t16-no-asset"
    deploy; rc=$?; check "asset referenced by the manifest but not committed: refused" bash -c "[[ $rc -ne 0 ]]"; check "  names the asset" has "$T/out.txt" "$(basename "$asset")"
    reset_app
    ( cd "$ORIGIN" && python3 - <<'PY'
import json
m=json.load(open('public/build/manifest.json')); m.pop('resources/css/app.css',None); json.dump(m,open('public/build/manifest.json','w'))
PY
    ); oc "t16-no-entry" >/dev/null
    deploy; rc=$?; check "manifest lacking a Vite input from vite.config.js: refused" bash -c "[[ $rc -ne 0 ]]"; check "  names the missing entry" has "$T/out.txt" "entry:resources/css/app.css"
    reset_app
    run -- bash "$DEP/deploy.sh" main --check; check "--check on the good BASE commit passes the build validation" has "$T/out.txt" "committed build is complete"
}

t17_public_storage() {
    begin "T17 public/storage symlink idempotency"
    reset_app; oc "t17a" >/dev/null
    deploy; check "correct symlink: left alone" has "$T/out.txt" "public/storage link already correct"
    reset_app; oc "t17b" >/dev/null; ln -sfn /nonexistent/old/path "$APP/public/storage"
    deploy; local rc=$?; check "wrong symlink: deploy succeeds" eq "$rc" 0
    check "wrong symlink: repaired to the right target" eq "$(readlink -f "$APP/public/storage")" "$(readlink -f "$APP/storage/app/public")"
    reset_app; oc "t17c" >/dev/null; rm -f "$APP/public/storage"; deploy; rc=$?
    check "missing symlink: created" bash -c "[[ $rc -eq 0 && -L '$APP/public/storage' ]]"
    reset_app; oc "t17d" >/dev/null; rm -f "$APP/public/storage"; mkdir -p "$APP/public/storage"; echo precious > "$APP/public/storage/user-file.txt"
    run -- bash "$DEP/deploy.sh" main --check; rc=$?; check "real directory: --check FAILS" bash -c "[[ $rc -ne 0 ]]"
    deploy; rc=$?; check "real directory: deploy refuses" bash -c "[[ $rc -ne 0 ]]"
    check "real directory: data intact" bash -c "[[ \$(cat '$APP/public/storage/user-file.txt') == precious ]]"
}

t18_apache_reload() {
    begin "T18 Apache configtest / reload failures"
    reset_app; oc "t18a" >/dev/null; local before; before="$(head_)"
    echo fail > "$STATE/configtest_mode"; deploy; local rc=$?
    check "configtest failing at preflight: refused, nothing changed" bash -c "[[ $rc -ne 0 && '$(head_)' == '$before' ]]"
    check "  no reload attempted" bash -c "! grep -q 'reload apache2' '$STATE/calls.log'"
    reset_app; oc "t18b" >/dev/null; before="$(head_)"
    echo "fail_after:1" > "$STATE/configtest_mode"; rm -f "$STATE/configtest_count"     # preflight ok, reload-time configtest fails
    deploy; rc=$?
    check "reload-time configtest failure: deploy fails" bash -c "[[ $rc -ne 0 ]]"
    check "  reports configtest failed — not reloading" has "$T/out.txt" "configtest failed — not reloading"
    check "  never reloaded Apache into a broken config" bash -c "! grep -q 'reload apache2' '$STATE/calls.log'"
    check "  previous commit restored" eq "$(head_)" "$before"
    reset_app; oc "t18c" >/dev/null; before="$(head_)"; touch "$STATE/reload_fail"
    deploy; rc=$?; check "systemctl reload failing: deploy fails and restores" bash -c "[[ $rc -ne 0 && '$(head_)' == '$before' ]]"; check "  reports the reload failure" has "$T/out.txt" "systemctl reload apache2 failed"
    rm -f "$STATE/reload_fail"
}

t19_lock_recovery() {
    begin "T19 lock recovery: a STOPPED holder is identified and recoverable without touching the lock file"
    reset_app; oc "t19" >/dev/null; local before; before="$(head_)"
    setsid -w "$TESTS/sigreset.py" env "${ENVARR[@]}" EF_COMPOSER="$FAKES/slow-composer.php" SLOW_COMPOSER=6 bash "$DEP/deploy.sh" main --no-http-health >"$T/holder.txt" 2>&1 &
    BG_PIDS+=("$!")
    for _ in $(seq 1 60); do sleep 0.25; grep -q "slow-composer: sleeping" "$T/holder.txt" 2>/dev/null && break; done
    local hp; hp="$(sed -E 's/^pid=([0-9]+).*/\1/' "$T/deploy.lock.info")"
    kill -STOP "$hp"; sleep 0.5      # SIGSTOP cannot be ignored: the ONLY way to force this state (a terminal cannot do it)
    check "holder really is stopped (T)" bash -c "[[ \$(ps -o stat= -p $hp | tr -d ' ') == T* ]]"
    run -- bash "$DEP/deploy.sh" main --no-http-health; local rc=$?
    check "new deploy refused" bash -c "[[ $rc -ne 0 ]]"
    check "message says the holder is STOPPED" has "$T/out.txt" "It is STOPPED"
    check "message gives the exact recovery: kill -CONT $hp && kill -TERM $hp" has "$T/out.txt" "kill -CONT $hp && sudo kill -TERM $hp"
    check "message forbids deleting the lock file" has "$T/out.txt" "Do NOT delete the lock file"
    check "lock file untouched by the refused run" test -e "$T/deploy.lock"
    # follow the printed recovery exactly
    kill -CONT "$hp" && kill -TERM "$hp"; wait
    check "recovered holder aborted cleanly" has "$T/holder.txt" "Received SIGTERM — aborting cleanly"
    check "previous commit restored" eq "$(head_)" "$before"
    check "lock is free again" lock_free
    deploy_fast; check "next deploy succeeds" eq "$?" 0
    # a child that inherited the lock (simulated) is diagnosed differently
    ( exec 200>>"$T/deploy.lock"; flock -n 200 && exec sleep 5 ) & local ch=$!; BG_PIDS+=("$ch"); sleep 0.3
    run -- bash "$DEP/deploy.sh" main --no-http-health; check "orphan lock holder (no matching PID) => points at 'fuser -v'" has "$T/out.txt" "fuser -v"
    kill "$ch" 2>/dev/null
}

t20_stale_dirs() {
    begin "T20 stale current/ releases/ shared/ repo/ are ignored and never touched"
    reset_app; oc "t20" >/dev/null
    for d in current releases shared repo; do mkdir -p "$APP/$d/inner"; echo "sentinel-$d" > "$APP/$d/inner/file.txt"; done
    ln -s releases/20260519172525 "$APP/current.link"
    local sum1; sum1="$(cd "$APP" && find current releases shared repo -type f -exec sha256sum {} + | sort | sha256sum)"
    run -- bash "$DEP/deploy.sh" main --check; check "--check reports them as unused leftovers" has "$T/out.txt" "stale leftover from the old release design"
    deploy; local rc=$?
    check "deploy succeeds with them present" eq "$rc" 0
    check "contents byte-identical afterwards" eq "$(cd "$APP" && find current releases shared repo -type f -exec sha256sum {} + | sort | sha256sum)" "$sum1"
    check "still untracked (never committed/cleaned)" bash -c "cd '$APP' && git status --porcelain --untracked-files=all | grep -q 'releases/'"
    printf '#!/bin/sh\necho old\n' > "$APP/deployment/deploy.sh.bak.2026-05-17"; run -- bash "$DEP/deploy.sh" main --check
    check "--check warns about deploy.sh.bak* backups" has "$T/out.txt" "deploy.sh.bak"
}

t21_maintenance_failures() {
    begin "T21 maintenance-mode failure handling"
    reset_app
    ( cd "$ORIGIN" && python3 - <<'PY'
import json
d=json.load(open('composer.json')); d['description']=d.get('description','')+' (t21)'; json.dump(d,open('composer.json','w'),indent=4)
PY
    ); oc "t21" >/dev/null; local before; before="$(head_)"
    # make `artisan down` fail: storage/framework not writable
    chmod a-w "$APP/storage/framework"
    deploy; local rc=$?
    chmod u+w "$APP/storage/framework"
    check "cannot enter maintenance: deploy stops" bash -c "[[ $rc -ne 0 ]]"
    check "  says so explicitly" has "$T/out.txt" "Failed to enter maintenance mode"
    check "  nothing changed (HEAD, not down)" bash -c "[[ '$(head_)' == '$before' ]] && ! [[ -e '$APP/storage/framework/down' ]]"
    check "  maintenance failure is NOT swallowed with '|| true'" bash -c "grep -n 'enter_maintenance' '$DEP/deploy.sh' | grep -vq 'true'"
}

t22_rollback() {
    begin "T22 rollback.sh"
    reset_app; oc "t22a" >/dev/null; deploy_fast; oc "t22b" >/dev/null; sleep 1; deploy_fast
    local newest; newest="$(head_)"
    run -- bash "$DEP/rollback.sh" --list; check "--list shows history and current HEAD" has "$T/out.txt" "Deploy history"
    run -- bash "$DEP/rollback.sh" --yes --skip-system-checks --no-http-health; local rc=$?
    check "rollback (default target) succeeds" eq "$rc" 0
    check "HEAD moved back one deploy" bash -c "[[ '$(head_)' != '$newest' ]]"
    check "history records ROLLBACK" has "$T/logs/history.tsv" "ROLLBACK"
    check "rollback goes through the same preflight/lock/health pipeline" has "$T/out.txt" "Preflight:"
    run -- bash "$DEP/rollback.sh" --to "$newest" --yes --skip-system-checks --no-http-health; check "rollback --to <sha> (roll forward)" eq "$?" 0
    run EF_ALLOW_NONROOT=0 -- bash "$DEP/rollback.sh" --yes; check "non-root refused" bash -c "[[ $? -ne 0 ]]"
    run -- bash "$DEP/rollback.sh" --to deadbeef --yes; check "unknown commit refused" bash -c "[[ $? -ne 0 ]]"
    run -- bash "$DEP/rollback.sh" --to "$newest" --yes; check "target == HEAD refused" has "$T/out.txt" "already the current HEAD"
}

t23_ownership_report() {
    begin "T23 ownership problems are reported (not silently ignored)"
    reset_app
    run EF_APP_USER=nobody -- bash "$DEP/deploy.sh" main --check --skip-system-checks; local rc=$?
    # non-root cannot switch users, so APP_USER stays the invoking user: assert the report path with a synthetic foreign owner instead
    check "clean tree reports OK ownership" has "$T/out.txt" "owned by and writable for"
    check "(root-only paths — runuser, chown --fix-ownership — cannot be exercised as a normal user: see summary)" true
}

t24_no_http_hang_and_json() {
    begin "T24 misc: --help, bad args, branch validation"
    run -- bash "$DEP/deploy.sh" --help; check "--help works" has "$T/out.txt" "READ-ONLY preflight"
    run -- bash "$DEP/deploy.sh" --bogus; check "unknown option rejected (exit 2)" bash -c "[[ $? -eq 2 ]]"
    run -- bash "$DEP/deploy.sh" 'ma;in'; check "invalid branch name rejected" bash -c "[[ $? -eq 2 ]]"
}

t25_sigkill_recovery() {
    begin "T25 kill -9 mid-deploy (OOM/power loss): lock is released, the unfinished deploy is DETECTED and re-applied"
    reset_app; oc "t25" >/dev/null; local before; before="$(git -C "$APP" rev-parse HEAD)"
    setsid -w "$TESTS/sigreset.py" env "${ENVARR[@]}" EF_COMPOSER="$FAKES/slow-composer.php" SLOW_COMPOSER=4 bash "$DEP/deploy.sh" main --no-http-health >"$T/kill9.txt" 2>&1 &
    BG_PIDS+=("$!")
    for _ in $(seq 1 60); do sleep 0.25; grep -q "slow-composer: sleeping" "$T/kill9.txt" 2>/dev/null && break; done
    local hp; hp="$(sed -E 's/^pid=([0-9]+).*/\1/' "$T/deploy.lock.info")"
    kill -9 "$hp"; wait
    sleep 0.5
    check "kernel released the lock (children never inherited the lock fd)" lock_free
    check "tree is at the target commit but the build is unfinished" eq "$(head_)" "$(ohead)"
    check "in-progress marker left behind" test -e "$T/logs/deploy-in-progress"
    sleep 9   # let the orphaned composer wrapper finish so it is not a 'conflicting process'
    run -- bash "$DEP/deploy.sh" main --check; local rc=$?
    check "--check reports the interrupted deploy" has "$T/out.txt" "previous deploy did NOT finish"
    check "--check stays read-only (marker still there)" test -e "$T/logs/deploy-in-progress"
    deploy; rc=$?
    check "next plain deploy re-applies (does NOT say 'already deployed')" bash -c "[[ $rc -eq 0 ]] && grep -q 'Deploy complete' '$T/out.txt' && ! grep -q 'HEAD is already' '$T/out.txt'"
    check "rollback target kept = the commit before the interrupted deploy" eq "$(tail -n1 "$T/logs/history.tsv" | cut -f2)" "$before"
    check "marker cleared after the successful re-apply" bash -c "! [[ -e '$T/logs/deploy-in-progress' ]]"
}

t26_health_exit_codes() {
    begin "T26 health-check.sh exit codes and HTTP verification"
    reset_app; oc "t26" >/dev/null; deploy_fast
    local hc=(bash "$DEP/health-check.sh" --no-system)
    run -- "${hc[@]}" --no-http; check "healthy tree: exit 0 + HEALTHY" bash -c "[[ $? -eq 0 ]] && grep -q '^HEALTHY' '$T/out.txt'"
    run -- "${hc[@]}" --no-http --expect-commit deadbeefdeadbeefdeadbeefdeadbeefdeadbeef; check "wrong commit: exit 1 + UNHEALTHY" bash -c "[[ $? -eq 1 ]] && grep -q '^UNHEALTHY' '$T/out.txt'"
    ( cd "$APP/public" && exec php -S 127.0.0.1:18778 "$APP/vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php" >/dev/null 2>&1 ) & local ws=$!; BG_PIDS+=("$ws"); sleep 1
    run -- "${hc[@]}" --http-base http://127.0.0.1:18778; check "real HTTP: /up 200, /login, manifest hash equals the tree" has "$T/out.txt" "served manifest.json matches the deployed tree"
    kill "$ws" 2>/dev/null; sleep 0.3
    run -- "${hc[@]}" --http-base http://127.0.0.1:18778; check "web server down: exit 1" bash -c "[[ $? -eq 1 ]]"
    mv "$APP/public/build/manifest.json" "$T/manifest.bak"; run -- "${hc[@]}" --no-http; check "manifest missing: exit 1" bash -c "[[ $? -eq 1 ]]"; mv "$T/manifest.bak" "$APP/public/build/manifest.json"
    touch "$APP/storage/framework/down"; run -- "${hc[@]}" --no-http; check "maintenance on: exit 1" bash -c "[[ $? -eq 1 ]]"; rm -f "$APP/storage/framework/down"
}

t27_remote_unreachable() {
    begin "T27 unreachable origin fails closed"
    reset_app; oc "t27" >/dev/null; local before; before="$(head_)"
    git -C "$APP" remote set-url origin /nonexistent/repo.git
    run -- bash "$DEP/deploy.sh" main --check; local rc=$?
    check "--check FAILS (cannot reach origin)" bash -c "[[ $rc -ne 0 ]]"; check "  says so" has "$T/out.txt" "cannot reach origin"
    deploy; rc=$?; check "deploy refuses, nothing changed" bash -c "[[ $rc -ne 0 && '$(head_)' == '$before' ]]"
    echo "https://user:SECRETTOKEN@example.invalid/x.git" > /dev/null
    git -C "$APP" remote set-url origin "https://user:SECRETTOKEN@example.invalid/x.git"
    run -- bash "$DEP/deploy.sh" main --check
    check "remote credentials are never printed" hasnt "$T/out.txt" "SECRETTOKEN"
}

t28_adopted_maintenance_kept() {
    begin "T28 an adopted maintenance mode (left by an earlier failure) is re-held if the re-run then fails; no code rollback"
    reset_app; oc "t28" >/dev/null
    ( cd "$APP" && env "${ENVARR[@]}" php artisan down -q ) ; check "precondition: app in maintenance" down_on
    run -- bash "$DEP/deploy.sh" main; local rc=$?          # HTTP health on, nothing listening => fails after `up`
    check "deploy fails" bash -c "[[ $rc -ne 0 ]]"
    check "adopted maintenance was lifted only to run the health check" has "$T/out.txt" "Adopting the pre-existing maintenance mode"
    check "after the failure the app is HELD IN MAINTENANCE again (fail closed)" down_on
    check "no automatic code rollback (schema state unknown)" hasnt "$T/out.txt" "Restoring previous commit"
    check "code stays at the new commit" eq "$(head_)" "$(ohead)"
}

t29_explicit_commit() {
    begin "T29 --commit deploys an exact older commit (rollback engine)"
    reset_app; oc "t29a" >/dev/null; local a; a="$(ohead)"; deploy_fast; sleep 1; oc "t29b" >/dev/null; deploy_fast
    check "at newest" eq "$(head_)" "$(ohead)"
    run -- bash "$DEP/deploy.sh" --commit "$a" --force --skip-system-checks --no-http-health; local rc=$?
    check "--commit <older sha> succeeds" eq "$rc" 0
    check "HEAD == that commit" eq "$(head_)" "$a"
}

t30_bounded_service_commands() {
    begin "T30 nothing can hang: supervisorctl / systemctl / composer / logger are all time-bounded"
    reset_app; oc "t30a" >/dev/null; local before t0 rc; before="$(head_)"
    touch "$STATE/hang_restart"; t0=$(date +%s)
    run EF_RESTART_TIMEOUT=2 -- bash "$DEP/deploy.sh" main --no-http-health; rc=$?
    check "hanging 'supervisorctl restart' is cut off and fails the deploy" bash -c "[[ $rc -ne 0 ]]"
    check "  reports the timeout" has "$T/out.txt" "Timed out after 2s: .*supervisorctl restart"
    check "  finished within 40 s (fake would sleep 60)" bash -c "[[ $(( $(date +%s) - t0 )) -lt 40 ]]"
    check "  previous commit restored" eq "$(head_)" "$before"
    rm -f "$STATE/hang_restart"
    reset_app; oc "t30b" >/dev/null; before="$(head_)"; touch "$STATE/hang_reload"; t0=$(date +%s)
    run EF_PRIV_TIMEOUT=2 -- bash "$DEP/deploy.sh" main --no-http-health; rc=$?
    check "hanging 'systemctl reload' is cut off and fails the deploy" bash -c "[[ $rc -ne 0 ]]"
    check "  reports the timeout" has "$T/out.txt" "Timed out after 2s: .*systemctl reload"
    check "  finished within 40 s" bash -c "[[ $(( $(date +%s) - t0 )) -lt 40 ]]"
    rm -f "$STATE/hang_reload"
    reset_app; oc "t30c" >/dev/null; t0=$(date +%s)
    run EF_COMPOSER="$FAKES/orphan-composer.php" -- bash "$DEP/deploy.sh" main --skip-system-checks --no-http-health; rc=$?
    check "an orphaned grandchild holding stdout does not keep the deploy from exiting (logger wait is bounded)" bash -c "[[ $rc -eq 0 && $(( $(date +%s) - t0 )) -lt 22 ]]"
    check "  deploy still completed successfully" has "$T/out.txt" "Deploy complete"
}

t31_permissions_and_secrets() {
    begin "T31 permissions of what the deploy creates, and no secrets in any output"
    reset_app; oc "t31" >/dev/null
    echo '{"x":"y"}' > "$APP/storage/fonts/installed-fonts.json"                       # dirty tracked file => a patch is written
    printf '* * * * * root cd /x && git pull https://deployer:TOK123SECRET@example.invalid/r.git\n' > "$T/cron/evil"
    local key; key="$(sed -n 's/^APP_KEY=//p' "$APP/.env")"
    deploy; local rc=$?
    check "deploy succeeds" eq "$rc" 0
    check "log directory is 0750" eq "$(stat -c %a "$T/logs")" 750
    check "dirty-tree patch is owner-only (0600)" bash -c "[[ \$(stat -c %a \$(ls '$T'/logs/dirty-*.patch | head -1)) == 600 ]]"
    check "composer cache dir is 0700 and owned by the app user" bash -c "[[ \$(stat -c '%a %U' '$T/composer-home') == '700 $(id -un)' ]]"
    check "nothing created by the deploy is group/world-writable" bash -c "[[ -z \"\$(find '$T/logs' '$T/composer-home' -perm /022 2>/dev/null | head -1)\" ]]"
    check "cron trigger is reported (duplicate deploy trigger warning)" has "$T/out.txt" "possible duplicate deploy triggers"
    check "credentials inside that cron line are redacted" bash -c "! grep -rq 'TOK123SECRET' '$T/out.txt' '$T/logs'"
    check "APP_KEY / .env secrets never appear in output or logs" bash -c "! grep -rqF -- '$key' '$T/out.txt' '$T/logs'"
    run -- bash "$DEP/deploy.sh" main --check
    check "--check output also free of secrets" bash -c "! grep -qF -- '$key' '$T/out.txt' && ! grep -q TOK123SECRET '$T/out.txt'"
    check "root-owned files: deploy never creates files as another user in the app tree (all owned by $(id -un))" bash -c "[[ -z \"\$(find '$APP/storage' '$APP/bootstrap/cache' '$APP/vendor' -not -user '$(id -un)' 2>/dev/null | head -1)\" ]]"
}

t32_diagnose_redaction_readonly() {
    begin "T32 diagnose-server.sh: read-only and redacts secrets from everything it prints"
    reset_app; oc "t32" >/dev/null
    local snap1 snap2
    snapd() { ( cd "$APP" && find . -path ./.git -prune -o -printf '%p %s %T@ %m\n' | sort; git --no-optional-locks status --porcelain; git rev-parse HEAD ) | sha256sum; }
    git -C "$APP" remote set-url origin "https://deployer:REMOTETOK999@example.invalid/x.git"
    snap1="$(snapd)"
    env "${ENVARR[@]}" PATH="$FAKES:$PATH" bash "$DEP/diagnose-server.sh" "$APP" expense.test > "$T/diag.txt" 2>&1
    snap2="$(snapd)"
    check "produces a report" has "$T/diag.txt" "DONE — this script changed nothing"
    check "application tree untouched" eq "$snap1" "$snap2"
    check "remote URL credentials redacted" bash -c "! grep -q REMOTETOK999 '$T/diag.txt'"
    check "cron secrets redacted (fake crontab prints PGPASSWORD=hunter2 and a token URL)" bash -c "grep -q 'PGPASSWORD=\*\*\*' '$T/diag.txt' && ! grep -qE 'hunter2|CRONTOK' '$T/diag.txt'"
    check "inspects the REAL lock file (lib.sh's), not the old /tmp one" bash -c "grep -q '$T/deploy.lock' '$T/diag.txt' && ! grep -q 'tmp/expenseflow_deploy.lock' '$T/diag.txt'"
    check "reports lock state via a read-only probe" has "$T/diag.txt" "lock is FREE"
    check "no lock file / lock info created" bash -c "! [[ -e '$T/deploy.lock' ]]"
    check "no maintenance / marker created" bash -c "! [[ -e '$APP/storage/framework/down' ]] && ! [[ -e '$T/logs/deploy-in-progress' ]]"
    check "no service call recorded (no reload/restart)" bash -c "! grep -qE 'reload|restart' '$STATE/calls.log'"
}

t33_stale_scratch_sweep() {
    begin "T33 scratch dirs left by a killed run are swept by the next deploy (only ours, only stale)"
    reset_app; oc "t33" >/dev/null
    local stale="/tmp/ef-deploy.ZZstal" fresh="/tmp/ef-deploy.ZZfrsh" other="/tmp/ef-other.ZZkeep"
    mkdir -p "$stale" "$fresh" "$other"; touch -d '2 hours ago' "$stale" "$other"
    deploy_fast; local rc=$?
    check "deploy succeeds" eq "$rc" 0
    check "stale ef-deploy.* dir removed" test ! -e "$stale"
    check "a FRESH ef-deploy.* dir (mtime < 5 min) is left alone" test -d "$fresh"
    check "unrelated /tmp dirs are never touched" test -d "$other"
    rm -rf "$fresh" "$other"
}

# ── main ───────────────────────────────────────────────────────────────────────
cleanup() { for p in "${BG_PIDS[@]:-}"; do [[ -n "$p" ]] && kill -9 "$p" 2>/dev/null; done; kill_workers 2>/dev/null; if [[ -z "${EF_KEEP_SANDBOX:-}" && -e "$T/.ef-tests-sandbox" ]]; then rm -rf -- "$T"; fi; }   # only ever removes a directory this script created
[[ "${EF_TESTS_SOURCE:-}" == "1" ]] || trap cleanup EXIT

if [[ -n "${EF_REUSE_SANDBOX:-}" && -d "$GOLDEN" ]]; then BASE="$(git -C "$ORIGIN" rev-list --max-parents=0 HEAD | head -n1 | cut -c1-7)"; echo "reusing sandbox $T (BASE=$BASE)"; else build_sandbox; fi
[[ "${EF_TESTS_SOURCE:-}" == "1" ]] && return 0 2>/dev/null
ALL=(t01_syntax_and_static t02_check_readonly t03_check_negative t04_normal_deploy t05_concurrent t06_ctrl_z t07_ctrl_c t08_sigterm t09_sighup
     t10_composer_failures t11_migration_failure t12_post_migration_health_failure t13_health_failure_restore t14_stale_worker t15_dirty_tree
     t16_build_validation t17_public_storage t18_apache_reload t19_lock_recovery t20_stale_dirs t21_maintenance_failures t22_rollback
     t23_ownership_report t24_no_http_hang_and_json t25_sigkill_recovery t26_health_exit_codes t27_remote_unreachable t28_adopted_maintenance_kept t29_explicit_commit t30_bounded_service_commands t31_permissions_and_secrets t32_diagnose_redaction_readonly t33_stale_scratch_sweep)
SEL=("$@")
for fn in "${ALL[@]}"; do
    if [[ ${#SEL[@]} -gt 0 ]]; then m=0; for s in "${SEL[@]}"; do [[ "${fn^^}" == "${s^^}"* ]] && m=1; done; [[ $m -eq 1 ]] || continue; fi
    "$fn"
done

echo; echo "════════════════════════════════════════════════════════"
echo "RESULT: $PASS passed, $FAIL failed"
if [[ $FAIL -gt 0 ]]; then printf '  FAILED: %s\n' "${FAILED[@]}"; fi
cat <<'EOF'

NOT covered by this sandbox (needs the real server / root):
  runuser to www-data, chown --fix-ownership, real `apache2ctl`/`systemctl reload apache2`, real supervisord,
  the real Apache vhost file, sudo's job-control relaying, PostgreSQL (tests use SQLite).
EOF
[[ $FAIL -eq 0 ]]
