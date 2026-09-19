# shellcheck shell=bash
# ExpenseFlow deployment library — sourced by deploy.sh, rollback.sh, health-check.sh. Not executable.
#
# Architecture (single, FLAT — proven on production):
#   Apache 2.4 serves DocumentRoot $EF_APP_DIR/public (default /var/www/akshathayexpense/public).
#   $EF_APP_DIR is the git working tree; .env, storage/, vendor/ live in place.
#   Deploy = preflight → fetch → verify commit → (maintenance if vendor/schema change) → checkout → composer →
#   caches → migrate → graceful Apache reload → workers → up → health. No nginx, no releases/current switching.

EF_APP_DIR="${EF_APP_DIR:-/var/www/akshathayexpense}"
# Logs live OUTSIDE the app tree so deploys never create root-owned files under storage/.
if [[ $EUID -eq 0 ]]; then LOG_DIR="${EF_LOG_DIR:-/var/log/expenseflow-deploy}"; else LOG_DIR="${EF_LOG_DIR:-$HOME/.expenseflow-deploy}"; fi
HISTORY_FILE="$LOG_DIR/history.tsv"
# The flock lives on a stable file that is NEVER unlinked. Exclusivity comes from flock(2), not from the file existing.
LOCK_FILE="${EF_LOCK_FILE:-/var/lock/expenseflow-deploy.lock}"

# Real deploys must run as root: it owns .git/objects fetches, systemctl reload, supervisorctl, and drops to the app
# user for composer/artisan. Non-root is for sandbox tests only (EF_ALLOW_NONROOT=1).
if [[ $EUID -eq 0 ]]; then
    APP_USER="${EF_APP_USER:-www-data}"
else
    APP_USER="$(id -un)"
fi
APP_GROUP="$(id -gn "$APP_USER" 2>/dev/null || id -gn)"
umask 022    # files created by root-run steps are 644/755 — never group/world-writable, never 775

# ── Logging: errors, warnings, skips and successes are visibly different ───────
if [[ -t 2 ]]; then RED=$'\033[0;31m'; GRN=$'\033[0;32m'; YEL=$'\033[1;33m'; CYN=$'\033[0;36m'; NC=$'\033[0m'; else RED=""; GRN=""; YEL=""; CYN=""; NC=""; fi
_ts()  { date '+%H:%M:%S'; }
# Log writes are best-effort BY DESIGN: if the terminal is gone (dropped SSH), a failed write must not abort a deploy
# half-way — the log file (via tee) still records everything and on_exit still runs its restore logic.
info() { printf '%s [INFO]    %s\n' "$(_ts)" "$*" || true; }
ok()   { printf '%s%s [OK]%s      %s\n' "$GRN" "$(_ts)" "$NC" "$*" || true; }
skip() { printf '%s%s [SKIPPED]%s %s\n' "$CYN" "$(_ts)" "$NC" "$*" || true; }
warn() { printf '%s%s [WARN]%s    %s\n' "$YEL" "$(_ts)" "$NC" "$*" >&2 || true; }
err()  { printf '%s%s [ERROR]%s   %s\n' "$RED" "$(_ts)" "$NC" "$*" >&2 || true; }
FAIL_REASON=""
die()  { FAIL_REASON="$*"; err "$*"; exit 1; }

# Preflight result collection (used by `deploy.sh --check` and by the real deploy's preflight).
CHK_PASS=0; CHK_WARN=0; CHK_FAIL=0; CHK_FAILS=()
chk_ok()   { CHK_PASS=$((CHK_PASS+1)); printf '  %s✓%s %s\n' "$GRN" "$NC" "$*" || true; }
chk_warn() { CHK_WARN=$((CHK_WARN+1)); printf '  %s⚠ WARN%s %s\n' "$YEL" "$NC" "$*" || true; }
chk_fail() { CHK_FAIL=$((CHK_FAIL+1)); CHK_FAILS+=("$*"); printf '  %s✗ FAIL%s %s\n' "$RED" "$NC" "$*" || true; }
chk_info() { printf '  %s·%s %s\n' "$CYN" "$NC" "$*" || true; }

# ── Privilege handling: deterministic, every command runs exactly once ─────────
# Prefix arrays (usable with `timeout`, which can only exec binaries) plus function wrappers. Exactly one branch runs.
if [[ $EUID -eq 0 ]]; then PRIV_RUN=(); else PRIV_RUN=(sudo -n); fi
if [[ $EUID -eq 0 && "$APP_USER" != "root" ]]; then APP_RUN=(runuser -u "$APP_USER" --); else APP_RUN=(); fi
priv()   { "${PRIV_RUN[@]}" "$@"; }   # run as root
as_app() { "${APP_RUN[@]}" "$@"; }     # run as the application user when we are root
nolock() { "$@" 200>&-; }   # never let a child inherit the flock fd (a daemonising child would hold the lock forever)

# ── Binaries ───────────────────────────────────────────────────────────────────
detect_php() {
    local b
    for b in "${EF_PHP:-}" php8.3 php8.4 php8.2 php; do
        [[ -n "$b" ]] && command -v "$b" >/dev/null 2>&1 && { command -v "$b"; return 0; }
    done
    return 1
}
detect_composer() {
    local b
    for b in "${EF_COMPOSER:-}" /usr/local/bin/composer /usr/bin/composer; do
        [[ -n "$b" && -x "$b" ]] && { echo "$b"; return 0; }
    done
    return 1
}

# ── Locking ────────────────────────────────────────────────────────────────────
# Opened in append mode (no truncation), never removed. The kernel drops the lock when the process dies.
acquire_lock() {
    local dir; dir="$(dirname "$LOCK_FILE")"
    [[ -d "$dir" ]] || die "Lock directory $dir does not exist"
    exec 200>>"$LOCK_FILE" || die "Cannot open lock file $LOCK_FILE (permissions? run as root)"
    if ! flock -n 200; then
        report_lock_holder
        exit 1
    fi
    printf 'pid=%s user=%s host=%s started=%s cmd=%s\n' "$$" "$(id -un)" "$(hostname)" "$(date -Is)" "${0##*/} $*" > "${LOCK_FILE}.info" 2>/dev/null || true
}
# Read-only probe for `--check`: never creates the lock file, never writes the info file, holds nothing.
probe_lock() {   # returns 0 = free, 1 = held
    [[ -e "$LOCK_FILE" ]] || return 0
    local rc=0
    ( exec 9<"$LOCK_FILE" && flock -n -s 9 ) 2>/dev/null || rc=$?
    return "$rc"
}
report_lock_holder() {
    local pid="" stat="" line
    err "Another deployment operation holds the lock ($LOCK_FILE)."
    if [[ -r "${LOCK_FILE}.info" ]]; then
        line="$(cat "${LOCK_FILE}.info" 2>/dev/null || true)"
        err "Holder info: $line"
        pid="${line#pid=}"; pid="${pid%% *}"
    fi
    if [[ "$pid" =~ ^[0-9]+$ ]] && kill -0 "$pid" 2>/dev/null; then
        stat="$(ps -o stat= -p "$pid" 2>/dev/null | tr -d ' ' || true)"
        err "Holder PID $pid is alive (state: ${stat:-?})."
        if [[ "$stat" == T* ]]; then
            err "It is STOPPED. A stopped process ignores SIGTERM until continued. Resume it, then ask it to abort cleanly:"
            err "    sudo kill -CONT $pid && sudo kill -TERM $pid"
        else
            err "It is running. Wait for it to finish, or ask it to abort cleanly:  sudo kill -TERM $pid"
        fi
    else
        err "The PID in the info file is not running, so a CHILD process must have inherited the lock. Find it:  sudo fuser -v $LOCK_FILE"
    fi
    err "Do NOT delete the lock file: removing it while a holder exists lets a second deploy run concurrently."
}

# ── Signals ────────────────────────────────────────────────────────────────────
# Ctrl+Z would leave a STOPPED process tree holding the lock (and a stopped process ignores SIGTERM).
# Job-control stop signals are ignored for the whole tree (ignored dispositions are inherited across exec).
# Ctrl+C / SIGTERM / SIGHUP stay fatal and are handled cleanly by the caller's traps.
ignore_job_control_signals() { trap '' TSTP TTIN TTOU; }

CHILD_PID=""
# run_step <timeout-seconds> <command...>   stdin=/dev/null, lock fd closed, bounded, forwards SIGTERM to the child.
run_step() {
    local t="$1" rc=0; shift
    timeout -k 15 "$t" "$@" </dev/null 200>&- &
    CHILD_PID=$!
    wait "$CHILD_PID" || rc=$?
    CHILD_PID=""
    if [[ $rc -eq 124 ]]; then err "Timed out after ${t}s: $*"; fi
    return "$rc"
}
forward_signal_to_child() {
    if [[ -n "$CHILD_PID" ]] && kill -0 "$CHILD_PID" 2>/dev/null; then
        kill -TERM "$CHILD_PID" 2>/dev/null || true
        wait "$CHILD_PID" 2>/dev/null || true
    fi
}

# ── Symlinks ───────────────────────────────────────────────────────────────────
# ensure_symlink <target> <link>: correct -> OK; missing -> create; wrong symlink -> repair atomically;
# real file/dir -> refuse (never delete data). Runs as the invoking user (root), so a root-owned public/ is fine.
ensure_symlink() {
    local target="$1" link="$2" cur tmp
    if [[ -L "$link" ]]; then
        cur="$(readlink "$link")"
        if [[ "$cur" == "$target" ]]; then return 0; fi
        warn "Repairing symlink $link ($cur -> $target)"
        tmp="${link}.tmp.$$"
        ln -s "$target" "$tmp" && mv -T "$tmp" "$link"
    elif [[ -e "$link" ]]; then
        err "$link exists and is NOT a symlink (expected -> $target). Refusing to delete it. Inspect/move it manually."
        return 1
    else
        ln -s "$target" "$link"
    fi
}

sanitize_url() { sed -E 's#(://)[^/@]*@#\1***@#' <<<"$1"; }   # never print credentials embedded in a remote URL
# redact: filter for anything echoed from git/cron/systemd output — strips URL credentials and token/password assignments.
redact() { sed -E 's#(://)[^/@[:space:]]*@#\1***@#g; s#((token|password|passwd|secret|api[_-]?key)[=:][[:space:]]*)[^[:space:];&]+#\1***#Ig'; }

# privt <seconds> <command...>: privileged command with a hard time limit, no stdin, lock fd closed. Exit 124 = timed out.
privt() {
    local t="$1" rc=0; shift
    timeout -k 5 "$t" "${PRIV_RUN[@]}" "$@" </dev/null 200>&- || rc=$?
    if [[ $rc -eq 124 ]]; then err "Timed out after ${t}s: $*"; fi
    return "$rc"
}

# ── Apache (the only supported web server) ─────────────────────────────────────
unit_active() { [[ "$(timeout -k 2 10 systemctl show -p ActiveState --value "$1" 2>/dev/null </dev/null || true)" == "active" ]]; }

detect_webserver() {   # prints apache2 | none   (override for tests: EF_WEBSERVER)
    if [[ -n "${EF_WEBSERVER:-}" ]]; then echo "$EF_WEBSERVER"; return 0; fi
    command -v systemctl >/dev/null 2>&1 || { echo none; return 0; }
    if unit_active apache2; then echo apache2; else echo none; fi
}

# One line per <VirtualHost>: "<space-separated ServerName/ServerAlias>|<DocumentRoot>"  (read-only)
apache_vhosts() {
    local f g files=()
    # shellcheck disable=SC2086
    for f in ${EF_APACHE_CONF:-/etc/apache2/sites-enabled}; do
        if [[ -d "$f" ]]; then for g in "$f"/*; do [[ -f "$g" ]] && files+=("$g"); done
        elif [[ -f "$f" ]]; then files+=("$f"); fi
    done
    [[ ${#files[@]} -gt 0 ]] || return 0
    awk '
        function d(s) { return tolower(s) }
        d($1) ~ /^<virtualhost/ { inv=1; names=""; root=""; next }
        d($1) ~ /^<\/virtualhost/ { if (inv) print names "|" root; inv=0; next }
        inv && d($1)=="servername"  { names = names " " $2 }
        inv && d($1)=="serveralias" { for (i=2;i<=NF;i++) names = names " " $i }
        inv && d($1)=="documentroot" { r=$2; gsub(/"/,"",r); root=r }
    ' "${files[@]}"
}
apache_roots_for_host() {   # apache_roots_for_host <host>  -> roots of vhosts naming that host (case-insensitive)
    local line names root h; h="$(tr '[:upper:]' '[:lower:]' <<<"$1")"
    while IFS='|' read -r names root; do
        [[ -n "$root" ]] || continue
        names=" $(tr '[:upper:]' '[:lower:]' <<<"$names") "
        if [[ "$names" == *" $h "* ]]; then echo "$root"; fi
    done < <(apache_vhosts)
}
apache_has_modphp() {
    command -v apache2ctl >/dev/null 2>&1 || return 1
    local mods; mods="$(privt 20 apache2ctl -M 2>/dev/null || true)"
    [[ "$mods" =~ php[0-9_]*_module ]]
}
detect_fpm_units() {   # prints ACTIVE php*-fpm units, one per line
    command -v systemctl >/dev/null 2>&1 || return 0
    local files u
    files="$(timeout -k 2 10 systemctl list-unit-files --type=service --no-legend --no-pager 'php*-fpm.service' 2>/dev/null </dev/null || true)"
    while read -r u _; do
        [[ -n "$u" ]] || continue
        if unit_active "$u"; then echo "$u"; fi
    done <<<"$files"
    return 0
}
apache_configtest() { privt "${EF_PRIV_TIMEOUT:-60}" apache2ctl configtest >/dev/null 2>&1; }

# Graceful reload (no dropped requests; resets opcache and the realpath cache). configtest FIRST: never reload into a
# broken config. A failed reload is a hard error for the caller.
reload_web_stack() {
    local ws u fpm
    ws="$(detect_webserver)"
    [[ "$ws" == "apache2" ]] || { err "apache2 is not active — refusing to continue (expected the production web server)"; return 1; }
    apache_configtest || { err "apache2ctl configtest failed — not reloading"; return 1; }
    privt "${EF_PRIV_TIMEOUT:-60}" systemctl reload apache2 || { err "systemctl reload apache2 failed"; return 1; }
    ok "Reloaded apache2 (graceful, after configtest)"
    fpm="$(detect_fpm_units)"
    for u in $fpm; do
        privt "${EF_PRIV_TIMEOUT:-60}" systemctl reload "$u" || { err "Failed to reload $u"; return 1; }
        ok "Reloaded $u"
    done
    if [[ -z "$fpm" ]]; then
        if apache_has_modphp; then info "PHP runs as mod_php inside apache2 — no php-fpm to reload (correct)"
        else warn "neither mod_php nor an active php-fpm unit found — verify how PHP is executed"; fi
    fi
    return 0
}

# ── Supervisor ─────────────────────────────────────────────────────────────────
# "name|directory|user|command" for every [program:expenseflow*] in the supervisor config (read-only).
supervisor_program_defs() {
    local f files=()
    # shellcheck disable=SC2086
    for f in ${EF_SUPERVISOR_CONF:-/etc/supervisor/conf.d/*.conf /etc/supervisor/supervisord.conf}; do [[ -f "$f" ]] && files+=("$f"); done
    [[ ${#files[@]} -gt 0 ]] || return 0
    awk '
        function flush() { if (name ~ /^expenseflow/) print name "|" dir "|" usr "|" cmd; name="" }
        function val(s) { sub(/^[^=]*=[[:space:]]*/, "", s); sub(/[[:space:]]*;.*$/, "", s); sub(/[[:space:]]+$/, "", s); return s }
        /^\[program:/ { flush(); name=$0; sub(/^\[program:/, "", name); sub(/\].*/, "", name); dir=""; usr=""; cmd=""; next }
        /^\[/ { flush(); next }
        name != "" && /^[[:space:]]*directory[[:space:]]*=/ { dir=val($0) }
        name != "" && /^[[:space:]]*user[[:space:]]*=/      { usr=val($0) }
        name != "" && /^[[:space:]]*command[[:space:]]*=/   { cmd=val($0) }
        END { flush() }
    ' "${files[@]}"
}
supervisor_workers() {   # prints full process names of expenseflow* programs
    command -v supervisorctl >/dev/null 2>&1 || return 0
    local out line name
    out="$(privt "${EF_PRIV_TIMEOUT:-60}" supervisorctl status 2>/dev/null || true)"
    while read -r name line; do
        [[ "$name" == expenseflow* ]] && echo "$name"
    done <<<"$out"
    return 0
}
# restart_workers: SIGTERM => Laravel finishes the current job; supervisor then starts a fresh worker.
restart_workers() {
    local names n
    command -v supervisorctl >/dev/null 2>&1 || { err "supervisorctl not installed — cannot restart workers"; return 1; }
    names="$(supervisor_workers)"
    [[ -n "$names" ]] || { err "No expenseflow* programs in supervisor — workers would keep running old code"; return 1; }
    for n in $names; do
        # restart waits for the worker to finish its job (supervisor stopwaitsecs, up to several minutes), hence the long limit
        privt "${EF_RESTART_TIMEOUT:-600}" supervisorctl restart "$n" || { err "supervisorctl restart $n failed"; return 1; }
    done
    ok "Restarted queue workers: $(echo "$names" | tr '\n' ' ')"
}

# ── Misc helpers ───────────────────────────────────────────────────────────────
# Minimal .env reader: prints one value, never logs it. Only used for non-secret keys (APP_URL).
env_value() {   # env_value <file> <KEY>
    local line
    line="$(grep -E "^$2=" "$1" 2>/dev/null | head -n1 || true)"
    line="${line#*=}"; line="${line%\"}"; line="${line#\"}"; line="${line%\'}"; line="${line#\'}"
    printf '%s' "$line"
}
url_host() { sed -E 's#^[a-zA-Z]+://([^/:]+).*#\1#' <<<"$1"; }

# Build-asset validation. Empty output = valid. Checks: manifest exists/parses/non-empty; every Vite input listed in
# vite.config.js has a manifest entry; every referenced file exists (working tree) or is TRACKED (commit).
_manifest_php='
    $m = json_decode((string) @file_get_contents($argv[1]), true);
    if (!is_array($m) || !$m) { echo "manifest-missing-invalid-or-empty"; exit; }
    $vite = (string) @file_get_contents($argv[2]);
    $miss = [];
    if (preg_match_all("#[\x27\"](resources/[^\x27\"]+\\.(?:css|js|scss|ts))[\x27\"]#", $vite, $mm)) {
        foreach ($mm[1] as $in) { if (!isset($m[$in])) $miss[] = "entry:" . $in; }
    }
    $tracked = null;
    if ($argv[3] === "-") { $tracked = array_flip(array_filter(array_map("trim", file($argv[4]) ?: []))); }
    foreach ($m as $e) {
        foreach (array_merge([$e["file"] ?? null], $e["css"] ?? [], $e["assets"] ?? []) as $f) {
            if (!$f) continue;
            $ok = $tracked !== null ? isset($tracked["public/build/" . $f]) : is_file($argv[3] . "/" . $f);
            if (!$ok) $miss[] = $f;
        }
    }
    echo implode(",", array_unique($miss));'
manifest_missing() {   # manifest_missing <app-dir>   (working tree)
    local php; php="$(detect_php)" || { echo "php-not-found"; return 0; }
    "$php" -r "$_manifest_php" "$1/public/build/manifest.json" "$1/vite.config.js" "$1/public/build" 2>&1 || echo "php-error"
}
manifest_missing_commit() {   # manifest_missing_commit <commit>   (reads the repo objects; checks assets are COMMITTED)
    local php t out; php="$(detect_php)" || { echo "php-not-found"; return 0; }
    t="$(mktemp -d /tmp/ef-manifest.XXXXXX)"
    timeout -k 5 60 git -C "$EF_APP_DIR" show "$1:public/build/manifest.json" > "$t/m.json" 2>/dev/null </dev/null 200>&- || : > "$t/m.json"
    timeout -k 5 60 git -C "$EF_APP_DIR" show "$1:vite.config.js" > "$t/v.js" 2>/dev/null </dev/null 200>&- || : > "$t/v.js"
    timeout -k 5 60 git -C "$EF_APP_DIR" ls-tree -r --name-only "$1" -- public/build > "$t/list.txt" 2>/dev/null </dev/null 200>&- || : > "$t/list.txt"
    out="$("$php" -r "$_manifest_php" "$t/m.json" "$t/v.js" - "$t/list.txt" 2>&1 || echo "php-error")"
    rm -rf -- "$t"
    printf '%s' "$out"
}

# Processes that would conflict with a deploy. Matches real executables (comm) + their arguments — NOT any process whose
# command line merely mentions these words — and skips this script, every ancestor (sudo, ssh shells) and its subshells.
conflicting_processes() {
    local anc=" " p="$$" n=0
    while [[ "$p" =~ ^[0-9]+$ && "$p" -gt 1 && $n -lt 20 ]]; do
        anc+="$p "
        p="$(ps -o ppid= -p "$p" 2>/dev/null | tr -d ' ' || true)"; n=$((n+1))
    done
    ps -eo pid=,ppid=,comm=,args= 2>/dev/null | awk -v anc="$anc" -v me="$$" '
        {
            pid=$1; ppid=$2; comm=$3; args=$0; sub(/^[[:space:]]*[0-9]+[[:space:]]+[0-9]+[[:space:]]+[^[:space:]]+[[:space:]]+/, "", args)
            if (index(anc, " " pid " ") || ppid == me) next
            hit = 0
            if (comm == "bash" && args ~ /^(\/usr)?(\/bin\/)?bash( -[A-Za-z]+)* [^ ]*deployment\/(deploy|rollback)\.sh/) hit = 1
            if (comm ~ /^php/ && args ~ /composer[^ ]* (install|update|require)/) hit = 1
            if (comm ~ /^php/ && args ~ /artisan (migrate|down|up)( |$)/) hit = 1
            if (comm == "git" && args ~ /^git( -[^ ]+ [^ ]+| -[^ ]+)* (checkout|reset|pull|fetch|gc)( |$)/) hit = 1
            if (hit) print pid " " comm ": " substr(args, 1, 100)
        }'
}

# Things that could trigger a deploy or run artisan as the wrong user (cron / systemd). Read-only; prints findings.
deploy_trigger_scan() {
    local paths="${EF_CRON_PATHS:-/etc/crontab /etc/cron.d /etc/cron.hourly /etc/cron.daily /etc/cron.weekly /var/spool/cron/crontabs}"
    # shellcheck disable=SC2086
    grep -RInE 'deployment/(deploy|rollback)\.sh|git[[:space:]]+(pull|reset|fetch)|composer[[:space:]]+(install|update)' $paths 2>/dev/null || true
    local sd="${EF_SYSTEMD_DIR:-/etc/systemd/system}"
    if [[ -d "$sd" ]]; then grep -RIlE 'deploy\.sh|akshathayexpense.*(git|composer)' "$sd" 2>/dev/null | sed 's/^/systemd unit: /' || true; fi
}
cron_artisan_as_root() {   # cron lines that run artisan as root (creates root-owned files under storage/)
    local paths="${EF_CRON_PATHS:-/etc/crontab /etc/cron.d /var/spool/cron/crontabs}"
    # shellcheck disable=SC2086
    grep -RInE '^[^#]*[[:space:]]root[[:space:]]+.*artisan' $paths 2>/dev/null || true
    if [[ -r /var/spool/cron/crontabs/root ]]; then grep -nE '^[^#].*artisan' /var/spool/cron/crontabs/root 2>/dev/null | sed 's#^#/var/spool/cron/crontabs/root:#' || true; fi
}
