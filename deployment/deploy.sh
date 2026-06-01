#!/usr/bin/env bash
# =============================================================================
# ExpenseFlow — Self-Healing Production Deploy Script
# Usage: bash deploy.sh [branch] [--rollback] [--safe]
#   --rollback  Restore previous release without building
#   --safe      Skip build/migrations/queue restart (hotfix/CSS-only deploys)
# =============================================================================
set -Eeuo pipefail

# ── Static config ─────────────────────────────────────────────────────────────
BRANCH="main"
SAFE_MODE=0
ROLLBACK_MODE=0
DEPLOY_MODE="full"            # full | backend | frontend | hotfix
FORCE_MIGRATE=0               # --force-migrate: run migrations even in safe/hotfix/frontend mode
for _a in "$@"; do
    case "${_a}" in
        --rollback)        ROLLBACK_MODE=1 ;;
        --safe)            SAFE_MODE=1 ;;
        --hotfix)          DEPLOY_MODE="hotfix" ;;
        --backend)         DEPLOY_MODE="backend" ;;
        --frontend)        DEPLOY_MODE="frontend" ;;
        --mode=*)          DEPLOY_MODE="${_a#--mode=}" ;;
        --force-migrate)   FORCE_MIGRATE=1 ;;   # explicit escape hatch to apply migrations in safe/hotfix mode
        -*)                echo "[WARN] Unknown flag: ${_a}" >&2 ;;
        *)                 BRANCH="${_a}" ;;
    esac
done
# Validate and normalise mode
case "${DEPLOY_MODE}" in
    full|backend|frontend|hotfix) ;;
    *) echo "[ERROR] Invalid --mode '${DEPLOY_MODE}'. Valid: full|backend|frontend|hotfix" >&2; exit 1 ;;
esac
[[ "${DEPLOY_MODE}" == "hotfix" ]] && SAFE_MODE=1
REPO_URL="https://github.com/akshathayevents-maker/ExpenseFlow.git"
KEEP_RELEASES=5
KEEP_FAILED_RELEASES=3
SUPERVISOR_CONF="/etc/supervisor/conf.d/expenseflow-worker.conf"
SUPERVISOR_GROUP="expenseflow-worker"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
LOCK_FILE="/tmp/expenseflow_deploy.lock"
DEPLOY_START=$(date +%s)
T_COMPOSER_START=0;  T_COMPOSER_END=0
T_NPM_CI_START=0;    T_NPM_CI_END=0    # npm ci only
T_NPM_BUILD_START=0; T_NPM_BUILD_END=0 # vite build only
T_BUILD_START=0;     T_BUILD_END=0     # total frontend phase
T_MIGRATE_START=0;   T_MIGRATE_END=0
FE_SKIP_REASON=""    # why frontend build was skipped (empty = was built)
NM_SKIP_REASON=""    # why npm ci was skipped (empty = was run)

# Ensure common binary paths are available in non-interactive deploy shells
export PATH="/usr/local/bin:/usr/bin:/bin:${PATH:-}"

# ── Logging ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

_ts()      { date '+%Y-%m-%d %H:%M:%S'; }
log_info() { echo -e "${BLUE}$(_ts) [INFO]${NC}    $*"; }
log_ok()   { echo -e "${GREEN}$(_ts) [SUCCESS]${NC} $*"; }
log_warn() { echo -e "${YELLOW}$(_ts) [WARN]${NC}    $*" >&2; }
log_err()  { echo -e "${RED}$(_ts) [ERROR]${NC}   $*" >&2; }
log_heal() { echo -e "${CYAN}$(_ts) [HEAL]${NC}    $*"; }
die()           { log_err "$*"; exit 1; }
die_class()     { local cls="$1"; shift; log_err "[${cls}] $*"; exit 1; }
step()     { echo -e "\n${CYAN}${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}";
             echo -e "${CYAN}${BOLD}  ▶  $*${NC}";
             echo -e "${CYAN}${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; }

# ── Helper: safe sudo ─────────────────────────────────────────────────────────
safe_sudo() {
    if [[ $EUID -eq 0 ]]; then "$@"; else sudo "$@"; fi
}

# ── AUTO-DETECT: App directory ────────────────────────────────────────────────
detect_app_dir() {
    # 1. Explicit env override
    if [[ -n "${APP_DIR_OVERRIDE:-}" && -d "${APP_DIR_OVERRIDE}" ]]; then
        echo "${APP_DIR_OVERRIDE}"; return
    fi

    # 2. Known server paths (most specific first)
    local candidates=(
        "/var/www/expenseflow"
        "/var/www/akshathayexpense"
        "/var/www/html/expenseflow"
        "/home/expenseflow"
    )
    for d in "${candidates[@]}"; do
        if [[ -d "${d}" ]]; then
            echo "${d}"; return
        fi
    done

    # 3. Derive from deploy.sh location — if script is at <app>/deployment/deploy.sh
    local script_dir
    script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
    local script_parent
    script_parent="$(dirname "${script_dir}")"
    if [[ -f "${script_parent}/artisan" ]]; then
        echo "${script_parent}"; return
    fi

    # 4. CWD fallback — if running from inside project root
    if [[ -f "$(pwd)/artisan" ]]; then
        echo "$(pwd)"; return
    fi

    # 5. Cannot determine — caller must create
    echo ""
}

# ── AUTO-DETECT: PHP binary ───────────────────────────────────────────────────
detect_php() {
    if [[ -n "${PHP_BIN:-}" ]] && command -v "${PHP_BIN}" &>/dev/null; then
        echo "${PHP_BIN}"; return
    fi
    local candidates=("php8.3" "php8.2" "php8.1" "php8.0" "php")
    for b in "${candidates[@]}"; do
        if command -v "${b}" &>/dev/null; then
            echo "${b}"; return
        fi
    done
    echo ""
}

# ── AUTO-DETECT: Composer binary ─────────────────────────────────────────────
detect_composer() {
    if [[ -n "${COMPOSER_BIN:-}" ]] && command -v "${COMPOSER_BIN}" &>/dev/null; then
        echo "${COMPOSER_BIN}"; return
    fi
    local candidates=(
        "/usr/local/bin/composer"
        "/usr/bin/composer"
        "${HOME}/.composer/vendor/bin/composer"
        "${HOME}/composer.phar"
        "$(command -v composer 2>/dev/null || true)"
    )
    for b in "${candidates[@]}"; do
        if [[ -n "${b}" && -x "${b}" ]]; then
            echo "${b}"; return
        fi
    done
    echo ""
}

# ── HEAL: Install Composer ────────────────────────────────────────────────────
heal_composer() {
    log_heal "Composer not found — attempting auto-install"
    if ! command -v php &>/dev/null && ! command -v php8.3 &>/dev/null; then
        die "PHP not found. Install PHP first: sudo apt-get install php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl php8.3-pgsql"
    fi
    local _php
    _php=$(detect_php)
    local tmp_installer
    tmp_installer=$(mktemp /tmp/composer-installer-XXXX.php)
    log_heal "Downloading Composer installer..."
    if command -v curl &>/dev/null; then
        curl -fsSL https://getcomposer.org/installer -o "${tmp_installer}"
    elif command -v wget &>/dev/null; then
        wget -qO "${tmp_installer}" https://getcomposer.org/installer
    else
        die "Neither curl nor wget available — cannot download Composer. Install manually."
    fi
    "${_php}" "${tmp_installer}" --install-dir=/usr/local/bin --filename=composer 2>&1 | grep -v "^All settings" || true
    rm -f "${tmp_installer}"
    if command -v composer &>/dev/null; then
        log_ok "Composer installed: $(composer --version 2>/dev/null | head -1)"
    else
        die "Composer install failed. Install manually: https://getcomposer.org/download/"
    fi
}

# ── HEAL: App directory structure ─────────────────────────────────────────────
heal_app_dir() {
    local app_dir="$1"
    log_heal "Creating app directory structure at ${app_dir}"
    safe_sudo mkdir -p "${app_dir}"/{releases,shared,repo}
    safe_sudo mkdir -p "${app_dir}/shared/storage"/{app/public,framework/{cache,sessions,views},logs}
    safe_sudo mkdir -p "${app_dir}/shared/public"
    safe_sudo chown -R "$(whoami):$(whoami)" "${app_dir}" 2>/dev/null || true
    log_ok "App directory structure created: ${app_dir}"
}

# ── HEAL: Clone bare repo ─────────────────────────────────────────────────────
heal_repo() {
    local repo_dir="$1"
    log_heal "Bare repo missing at ${repo_dir} — cloning from ${REPO_URL}"
    if [[ -z "${REPO_URL}" ]]; then
        die "REPO_URL is not set. Set it at the top of deploy.sh."
    fi
    if ! command -v git &>/dev/null; then
        log_heal "git not found — installing"
        safe_sudo apt-get install -y git &>/dev/null || die "Cannot install git"
    fi
    local parent_dir
    parent_dir="$(dirname "${repo_dir}")"
    mkdir -p "${parent_dir}"
    git clone --bare "${REPO_URL}" "${repo_dir}"
    log_ok "Bare repo cloned to ${repo_dir}"
}

# ── HEAL: Locate and link .env ────────────────────────────────────────────────
heal_env() {
    local shared_env="$1"
    local app_dir="$2"
    local current_link="$3"

    # Already exists at canonical location
    if [[ -f "${shared_env}" ]]; then
        return 0
    fi

    log_heal ".env missing at ${shared_env} — searching common locations"

    local search_locations=(
        "${app_dir}/.env"
        "${current_link}/.env"
        "${app_dir}/current/.env"
        "/var/www/expenseflow/.env"
        "/var/www/akshathayexpense/.env"
        "$(pwd)/.env"
        "$(dirname "${BASH_SOURCE[0]}")/../.env"
    )

    for loc in "${search_locations[@]}"; do
        # Resolve symlinks to avoid self-referential loops
        local real_loc
        real_loc="$(realpath "${loc}" 2>/dev/null || echo "")"
        if [[ -n "${real_loc}" && -f "${real_loc}" && "${real_loc}" != "${shared_env}" ]]; then
            log_heal "Found .env at: ${real_loc}"
            log_heal "Copying to canonical location: ${shared_env}"
            mkdir -p "$(dirname "${shared_env}")"
            cp "${real_loc}" "${shared_env}"
            log_ok ".env placed at ${shared_env}"
            return 0
        fi
    done

    # Not found anywhere — print actionable message
    log_err ".env not found in any of these locations:"
    for loc in "${search_locations[@]}"; do log_err "  - ${loc}"; done
    log_err ""
    log_err "Fix: Copy your .env file to the server:"
    log_err "  scp .env user@server:${shared_env}"
    log_err "  OR: cp /path/to/your/.env ${shared_env}"
    die ".env is required — deployment aborted"
}

# ── HEAL: Storage directories and permissions ─────────────────────────────────
heal_storage() {
    local shared_storage="$1"
    log_heal "Repairing storage at ${shared_storage}"

    local dirs=(
        "${shared_storage}/app/public"
        "${shared_storage}/framework/cache/data"
        "${shared_storage}/framework/sessions"
        "${shared_storage}/framework/views"
        "${shared_storage}/logs"
    )
    for d in "${dirs[@]}"; do
        mkdir -p "${d}"
    done

    chmod -R 775 "${shared_storage}"
    safe_sudo chown -R www-data:www-data "${shared_storage}" 2>/dev/null || \
        chown -R "$(whoami):$(whoami)" "${shared_storage}" 2>/dev/null || true

    # Create .gitignore inside storage dirs (prevents empty dir issues)
    for d in "${shared_storage}/framework/cache" "${shared_storage}/framework/sessions" \
              "${shared_storage}/framework/views" "${shared_storage}/logs"; do
        [[ -f "${d}/.gitignore" ]] || echo "*\n!.gitignore" > "${d}/.gitignore"
    done

    log_ok "Storage repaired: ${shared_storage}"
}

# ── Helper: auto-detect php-fpm service ──────────────────────────────────────
detect_phpfpm_service() {
    local candidates=("php8.3-fpm" "php8.2-fpm" "php8.1-fpm" "php8.0-fpm" "php-fpm")
    for svc in "${candidates[@]}"; do
        if systemctl list-units --type=service --all 2>/dev/null | grep -q "^  ${svc}.service"; then
            echo "${svc}"; return
        fi
    done
    if command -v "${PHP}" &>/dev/null; then
        local ver
        ver=$("${PHP}" -r "echo PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;")
        echo "php${ver}-fpm"; return
    fi
    echo ""
}

# ── Helper: reload php-fpm safely ────────────────────────────────────────────
reload_phpfpm() {
    local svc
    svc=$(detect_phpfpm_service)
    if [[ -z "${svc}" ]]; then
        log_warn "Could not detect php-fpm service — skip reload"; return
    fi
    if systemctl is-active --quiet "${svc}" 2>/dev/null; then
        safe_sudo systemctl reload "${svc}" \
            && log_ok "php-fpm reloaded (${svc})" \
            || log_warn "php-fpm reload failed (${svc}) — workers may use stale opcache"
    else
        log_warn "php-fpm (${svc}) not active — skip reload"
    fi
}

# ── Helper: reload nginx safely ───────────────────────────────────────────────
reload_nginx() {
    if ! command -v nginx &>/dev/null; then
        log_warn "nginx not installed — skip reload"; return
    fi
    if ! systemctl is-active --quiet nginx 2>/dev/null; then
        log_warn "nginx not running — skip reload"; return
    fi
    if nginx -t -q 2>/dev/null; then
        safe_sudo systemctl reload nginx \
            && log_ok "nginx reloaded" \
            || log_warn "nginx reload failed — check nginx logs"
    else
        log_warn "nginx config test failed — skip reload to avoid outage"
    fi
}

# ── Helper: ensure supervisor worker config ───────────────────────────────────
ensure_supervisor_conf() {
    if [[ -f "${SUPERVISOR_CONF}" ]]; then return; fi
    log_warn "Supervisor config missing at ${SUPERVISOR_CONF} — creating it"
    local artisan_path="${CURRENT_LINK}/artisan"
    local log_path="${SHARED_DIR}/storage/logs/worker.log"
    safe_sudo tee "${SUPERVISOR_CONF}" > /dev/null << SVCONF
; ExpenseFlow queue worker — auto-generated by deploy.sh
[group:${SUPERVISOR_GROUP}]
programs=${SUPERVISOR_GROUP}-1,${SUPERVISOR_GROUP}-2

[program:${SUPERVISOR_GROUP}-1]
process_name=%(program_name)s_%(process_num)02d
command=${PHP} ${artisan_path} queue:work --queue=mail,default --sleep=3 --tries=3 --max-time=3600 --memory=128
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=${log_path}
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=5
stopwaitsecs=3600
startsecs=3

[program:${SUPERVISOR_GROUP}-2]
process_name=%(program_name)s_%(process_num)02d
command=${PHP} ${artisan_path} queue:work --queue=default --sleep=3 --tries=3 --max-time=3600 --memory=128
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=${log_path}
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=5
stopwaitsecs=3600
startsecs=3
SVCONF
    log_ok "Supervisor config written: ${SUPERVISOR_CONF}"
}

# ── Helper: restart supervisor workers safely ─────────────────────────────────
restart_workers() {
    if ! command -v supervisorctl &>/dev/null; then
        log_warn "supervisorctl not installed — skip worker restart"; return
    fi
    if ! systemctl is-active --quiet supervisor 2>/dev/null; then
        log_warn "supervisor service not running — attempting start"
        safe_sudo systemctl start supervisor 2>/dev/null \
            || { log_warn "Could not start supervisor — skip worker restart"; return; }
    fi
    ensure_supervisor_conf
    safe_sudo supervisorctl reread 2>/dev/null  || log_warn "supervisorctl reread failed"
    safe_sudo supervisorctl update 2>/dev/null  || log_warn "supervisorctl update failed"
    if safe_sudo supervisorctl status "${SUPERVISOR_GROUP}:" &>/dev/null 2>&1; then
        safe_sudo supervisorctl restart "${SUPERVISOR_GROUP}:" \
            && log_ok "Queue workers restarted (group: ${SUPERVISOR_GROUP})" && return
    fi
    local restarted=0
    while IFS= read -r prog; do
        if safe_sudo supervisorctl restart "${prog}" &>/dev/null 2>&1; then
            log_ok "Restarted worker: ${prog}"; restarted=1
        fi
    done < <(safe_sudo supervisorctl status 2>/dev/null | grep "${SUPERVISOR_GROUP}" | awk '{print $1}')
    if [[ $restarted -eq 0 ]]; then
        log_warn "Could not restart workers — run: sudo supervisorctl status"
    fi
}

# ── Helper: compute a combined fingerprint of all frontend source files ───────
#
# Returns a single MD5 that changes when ANY of the following change:
#   package.json / package-lock.json  → triggers npm ci
#   vite / tailwind / postcss configs → triggers full rebuild
#   resources/js/**                   → JS source changed
#   resources/css/**                  → CSS/SCSS changed
#   resources/views/**/*.blade.php    → Tailwind class scanning
#   public/sw.js                      → service worker source
#
# If the fingerprint matches ${SHARED_BUILD_CACHE}/.fe_hash, the build
# output in ${SHARED_BUILD_CACHE}/build/ can be reused without running
# Vite at all — saving 2–5 min per backend-only deploy.
frontend_fingerprint() {
    local dir="$1"
    {
        # Config files — any change in these invalidates both npm ci and build
        for f in package.json package-lock.json \
                  vite.config.js vite.config.ts vite.config.mjs \
                  tailwind.config.js tailwind.config.ts tailwind.config.cjs \
                  postcss.config.js postcss.config.cjs \
                  tsconfig.json jsconfig.json; do
            [[ -f "${dir}/${f}" ]] && md5sum "${dir}/${f}" || true
        done
        # JS source tree
        find "${dir}/resources/js"  -type f 2>/dev/null | LC_ALL=C sort | \
            xargs md5sum 2>/dev/null || true
        # CSS / SCSS source tree
        find "${dir}/resources/css" -type f 2>/dev/null | LC_ALL=C sort | \
            xargs md5sum 2>/dev/null || true
        # Blade templates — Tailwind purges/scans these for class names
        find "${dir}/resources/views" -type f -name "*.blade.php" 2>/dev/null | \
            LC_ALL=C sort | xargs md5sum 2>/dev/null || true
        # Service worker source (deployed to public/ before sw.js is stamped)
        [[ -f "${dir}/public/sw.js" ]] && md5sum "${dir}/public/sw.js" || true
    } | md5sum | awk '{print $1}'
}

# ── Auto-rollback: restore previous release if deploy fails at any point ──────
DEPLOY_PREVIOUS_RELEASE=""
cleanup_on_failure() {
    local exit_code=$?

    # Always release deploy lock
    rm -f "${LOCK_FILE}" 2>/dev/null || true

    [[ ${exit_code} -eq 0 ]] && return

    log_err "Deploy failed (exit ${exit_code}) — initiating auto-rollback (deploy_id: ${DEPLOY_ID:-unknown})"
    # Mark the release directory as failed for retention policy
    [[ -d "${RELEASE_DIR:-}" ]] && touch "${RELEASE_DIR}/.deploy_failed" 2>/dev/null || true

    # Tail latest Laravel log to show the immediate cause
    local log_file="${SHARED_DIR:-}/storage/logs/laravel.log"
    if [[ -f "${log_file}" ]]; then
        log_err "── Last 30 lines of laravel.log ──────────────────────────────"
        tail -n 30 "${log_file}" >&2 || true
        log_err "──────────────────────────────────────────────────────────────"
    fi

    if [[ -n "${DEPLOY_PREVIOUS_RELEASE}" && -d "${DEPLOY_PREVIOUS_RELEASE}" ]]; then
        # Ensure app is in maintenance mode before swapping back (prevents half-deploy exposure)
        "${PHP:-php}" "${DEPLOY_PREVIOUS_RELEASE}/artisan" down 2>/dev/null || true
        ln -sfn "${DEPLOY_PREVIOUS_RELEASE}" "${CURRENT_LINK}" 2>/dev/null || true
        log_heal "Symlink restored → $(basename "${DEPLOY_PREVIOUS_RELEASE}")"
        "${PHP:-php}" "${CURRENT_LINK}/artisan" up 2>/dev/null || true
        reload_phpfpm 2>/dev/null || true
        reload_nginx  2>/dev/null || true
        log_ok "Auto-rollback complete — previous release is live again"
    else
        log_warn "No previous release available — manual intervention required"
    fi
}
trap cleanup_on_failure EXIT

# =============================================================================
# PHASE 0 — AUTO-DETECT AND RESOLVE PATHS
# =============================================================================
step "Auto-detecting environment"

# Detect PHP
PHP=$(detect_php)
if [[ -z "${PHP}" ]]; then
    log_err "PHP not found. Install with:"
    log_err "  sudo apt-get install php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl php8.3-pgsql php8.3-zip"
    die "PHP is required"
fi
log_ok "PHP binary: ${PHP} ($(${PHP} -r 'echo PHP_VERSION;'))"

# Detect / heal Composer
COMPOSER=$(detect_composer)
if [[ -z "${COMPOSER}" ]]; then
    heal_composer
    COMPOSER=$(detect_composer)
    [[ -z "${COMPOSER}" ]] && die "Composer still not found after install attempt"
fi
log_ok "Composer: ${COMPOSER} ($(${PHP} ${COMPOSER} --version 2>/dev/null | head -1 | awk '{print $1,$2,$3}'))"

# Detect app dir
APP_DIR=$(detect_app_dir)
if [[ -z "${APP_DIR}" ]]; then
    APP_DIR="/var/www/expenseflow"
    log_warn "Could not detect app dir — defaulting to ${APP_DIR}"
fi
log_ok "App directory: ${APP_DIR}"

# Derive paths
REPO_DIR="${APP_DIR}/repo"
RELEASES_DIR="${APP_DIR}/releases"
SHARED_DIR="${APP_DIR}/shared"
CURRENT_LINK="${APP_DIR}/current"
RELEASE_DIR="${RELEASES_DIR}/${TIMESTAMP}"

# ── Persistent shared caches (survive across ALL releases) ────────────────────
# node_modules_cache — stores the last npm-ci result indexed by package-lock hash.
#   Eliminates the 15–20 min npm ci on every deploy when deps haven't changed.
# build_cache        — stores the last Vite output (public/build) indexed by a
#   fingerprint of ALL frontend source files. Eliminates ~2–5 min Vite build
#   on backend-only deploys.
SHARED_NM_CACHE="${SHARED_DIR}/node_modules_cache"
SHARED_BUILD_CACHE="${SHARED_DIR}/build_cache"

# ── Acquire deploy lock (prevent concurrent deploys) ─────────────────────────
exec 200>"${LOCK_FILE}"
if ! flock -n 200 2>/dev/null; then
    LOCK_PID=$(cat "${LOCK_FILE}" 2>/dev/null || echo "unknown")
    die "Another deployment is running (PID: ${LOCK_PID}). Remove ${LOCK_FILE} to force."
fi
echo $$ > "${LOCK_FILE}"
log_ok "Deploy lock acquired (PID: $$)"

# ── Tee all deploy output to timestamped log file ─────────────────────────────
DEPLOY_LOG_DIR="${APP_DIR}/deploy_logs"
mkdir -p "${DEPLOY_LOG_DIR}" 2>/dev/null || true
DEPLOY_LOG_FILE="${DEPLOY_LOG_DIR}/deploy_${TIMESTAMP}.log"
exec > >(tee -a "${DEPLOY_LOG_FILE}") 2>&1
log_info "Deploy log: ${DEPLOY_LOG_FILE}  (mode: ${DEPLOY_MODE})"

# =============================================================================
# PHASE 1 — SELF-HEALING PRE-DEPLOY CHECKS
# =============================================================================
step "Self-healing pre-deploy checks"

# ── 1.1 App directory ─────────────────────────────────────────────────────────
if [[ ! -d "${APP_DIR}" ]]; then
    log_heal "App directory missing: ${APP_DIR}"
    heal_app_dir "${APP_DIR}"
else
    log_ok "App directory exists: ${APP_DIR}"
fi

# ── 1.2 Directory structure ───────────────────────────────────────────────────
for required_dir in "${RELEASES_DIR}" "${SHARED_DIR}" "${SHARED_DIR}/storage"; do
    if [[ ! -d "${required_dir}" ]]; then
        log_heal "Missing directory: ${required_dir} — creating"
        mkdir -p "${required_dir}"
        log_ok "Created: ${required_dir}"
    fi
done

# Ensure persistent cache directories exist (created once, survive all releases)
mkdir -p "${SHARED_NM_CACHE}"
mkdir -p "${SHARED_BUILD_CACHE}"
log_ok "Persistent caches: node_modules_cache + build_cache ready"

# ── 1.3 Bare git repo ─────────────────────────────────────────────────────────
if [[ ! -d "${REPO_DIR}" ]]; then
    heal_repo "${REPO_DIR}"
else
    log_ok "Bare repo exists: ${REPO_DIR}"
fi

# ── 1.4 .env file ─────────────────────────────────────────────────────────────
heal_env "${SHARED_DIR}/.env" "${APP_DIR}" "${CURRENT_LINK}"
log_ok ".env present at ${SHARED_DIR}/.env"

# ── 1.5 Storage directories and permissions ───────────────────────────────────
if [[ ! -d "${SHARED_DIR}/storage/framework/views" ]] || \
   [[ ! -d "${SHARED_DIR}/storage/logs" ]] || \
   [[ ! -w "${SHARED_DIR}/storage" ]]; then
    heal_storage "${SHARED_DIR}/storage"
else
    log_ok "Storage writable: ${SHARED_DIR}/storage"
fi

# ── 1.6 Shared public dir for storage:link ───────────────────────────────────
mkdir -p "${SHARED_DIR}/public/storage"

# ── 1.7 Node.js check (non-fatal, but version-validated) ─────────────────────
REQUIRED_NODE_MAJOR=20
if command -v node &>/dev/null; then
    NODE_ACTUAL=$(node --version)          # e.g. v20.20.2
    NODE_MAJOR=$(node -e "process.stdout.write(String(process.versions.node.split('.')[0]))")
    if [[ "${NODE_MAJOR}" -lt "${REQUIRED_NODE_MAJOR}" ]]; then
        log_warn "Node.js ${NODE_ACTUAL} found but v${REQUIRED_NODE_MAJOR}+ required (vite 8 / tailwind 3 need it)"
        log_warn "Upgrade: curl -fsSL https://deb.nodesource.com/setup_${REQUIRED_NODE_MAJOR}.x | sudo bash - && sudo apt-get install -y nodejs"
        log_warn "Frontend build will be attempted anyway — expect possible failures"
    else
        log_ok "Node.js ${NODE_ACTUAL} (>= v${REQUIRED_NODE_MAJOR} ✓)"
    fi
else
    log_err "Node.js not found — frontend build is required (package.json present)."
    log_err "Install Node.js ${REQUIRED_NODE_MAJOR} with:"
    log_err "  curl -fsSL https://deb.nodesource.com/setup_${REQUIRED_NODE_MAJOR}.x | sudo bash - && sudo apt-get install -y nodejs"
    die "Node.js ${REQUIRED_NODE_MAJOR}+ required. Aborting deploy."
fi

# ── 1.8 DB connectivity (non-fatal) ──────────────────────────────────────────
if [[ -f "${SHARED_DIR}/.env" ]]; then
    DB_HOST=$(grep -E '^DB_HOST=' "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"' | tr -d "'") || true
    DB_PORT=$(grep -E '^DB_PORT=' "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"' | tr -d "'") || true
    DB_HOST="${DB_HOST:-127.0.0.1}"
    DB_PORT="${DB_PORT:-5432}"
    if command -v nc &>/dev/null; then
        if nc -z -w3 "${DB_HOST}" "${DB_PORT}" 2>/dev/null; then
            log_ok "Database reachable at ${DB_HOST}:${DB_PORT}"
        else
            log_warn "Database unreachable at ${DB_HOST}:${DB_PORT} — verify DB_HOST/DB_PORT in .env"
        fi
    fi
fi

# ── 1.9 RAM check ────────────────────────────────────────────────────────────
if command -v free &>/dev/null; then
    MEM_FREE_MB=$(free -m 2>/dev/null | awk '/^Mem:/{print $7}')
    if [[ -n "${MEM_FREE_MB}" && "${MEM_FREE_MB}" -lt 700 ]]; then
        log_warn "Low available RAM: ${MEM_FREE_MB} MB free — Vite build may OOM-kill"
        log_warn "Add swap: sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile && sudo mkswap /swapfile && sudo swapon /swapfile"
    else
        log_ok "Available RAM: ${MEM_FREE_MB:-?} MB free"
    fi
fi

# ── 1.9b Disk space check ────────────────────────────────────────────────────
DISK_FREE_KB=$(df -k "${APP_DIR}" 2>/dev/null | awk 'NR==2{print $4}' || echo 0)
DISK_FREE_MB=$(( DISK_FREE_KB / 1024 ))
if [[ "${DISK_FREE_MB}" -lt 1024 ]]; then
    log_warn "Low disk space: ${DISK_FREE_MB} MB free on $(df -k "${APP_DIR}" | awk 'NR==2{print $1}')"
    log_warn "node_modules + Vite build need ~500 MB. Free space or add disk."
    [[ "${DISK_FREE_MB}" -lt 256 ]] && die "Critically low disk space (${DISK_FREE_MB} MB) — aborting to prevent corrupt build."
else
    log_ok "Disk space: ${DISK_FREE_MB} MB free"
fi

# ── 1.10 Deploy context diagnostics ──────────────────────────────────────────
log_info "Running as user: $(whoami 2>/dev/null || echo unknown)"
log_info "Current release: $(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo 'none (first deploy)')"
log_info "New release dir: ${RELEASE_DIR}"
log_info "Deploy mode: ${DEPLOY_MODE^^}$([[ ${SAFE_MODE} -eq 1 ]] && echo ' (safe/no-build)' || true)"

# Capture log file position so error scan only reads NEW content written by this deploy
_LOG_FILE_SCAN="${SHARED_DIR}/storage/logs/laravel.log"
_LOG_PRE_SIZE=$(wc -c < "${_LOG_FILE_SCAN}" 2>/dev/null || echo 0)

# ── 1.11 Required .env variables ─────────────────────────────────────────────
if [[ -f "${SHARED_DIR}/.env" ]]; then
    ENV_MISSING=0
    for required_var in APP_KEY APP_URL DB_DATABASE DB_USERNAME DB_PASSWORD; do
        val=$(grep -E "^${required_var}=" "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs 2>/dev/null)
        if [[ -z "${val}" ]]; then
            log_err ".env missing or empty: ${required_var}"
            ENV_MISSING=1
        fi
    done
    if [[ ${ENV_MISSING} -ne 0 ]]; then
        die "Required .env variables are unset — edit ${SHARED_DIR}/.env before deploying"
    fi
    log_ok ".env required variables present (APP_KEY, APP_URL, DB_*)"

    # ── 1.11b Security: APP_DEBUG must be false in production/stage ──────────
    # Exposing Laravel exception screens leaks stack traces, file paths, and
    # env variable values to anyone who can trigger a 500 error.
    _APP_ENV=$(grep -E "^APP_ENV=" "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs 2>/dev/null | tr '[:upper:]' '[:lower:]')
    _APP_DEBUG=$(grep -E "^APP_DEBUG=" "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs 2>/dev/null | tr '[:upper:]' '[:lower:]')

    if [[ "${_APP_ENV}" != "local" && "${_APP_DEBUG}" == "true" ]]; then
        log_err "SECURITY: APP_DEBUG=true in ${_APP_ENV} environment!"
        log_err "This exposes stack traces, file paths, and env values to all visitors."
        log_err "Set APP_DEBUG=false in ${SHARED_DIR}/.env before deploying to ${_APP_ENV}."
        die "Refusing to deploy with APP_DEBUG=true in non-local environment"
    fi
    if [[ "${_APP_DEBUG}" != "true" ]]; then
        log_ok "APP_DEBUG=false — exception screens suppressed (${_APP_ENV})"
    fi
fi

# ── 1.12 Stale symlink detection ─────────────────────────────────────────────
if [[ -L "${CURRENT_LINK}" ]]; then
    _STALE_TARGET=$(readlink "${CURRENT_LINK}" 2>/dev/null || echo "")
    _STALE_REAL=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")
    if [[ -z "${_STALE_REAL}" || ! -d "${_STALE_REAL}" ]]; then
        log_warn "Current symlink is stale (points to missing dir: ${_STALE_TARGET})"
        log_warn "Deploy will fix this — or manually: ln -sfn <release_dir> ${CURRENT_LINK}"
    fi
fi

log_ok "Pre-deploy checks complete"

# Snapshot current live release for auto-rollback trap
DEPLOY_PREVIOUS_RELEASE=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")
[[ -n "${DEPLOY_PREVIOUS_RELEASE}" ]] && log_info "Rollback target: ${DEPLOY_PREVIOUS_RELEASE}"

# =============================================================================
# PHASE 2 — ROLLBACK
# =============================================================================
if [[ ${ROLLBACK_MODE} -eq 1 || "${1:-}" == "--rollback" ]]; then
    step "ROLLBACK"
    mapfile -t RELEASES < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null | head -5)
    if [[ ${#RELEASES[@]} -lt 2 ]]; then
        die "No previous release to roll back to."
    fi
    PREV="${RELEASES[1]%/}"
    CURRENT_REL=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "none")
    log_info "Current:  ${CURRENT_REL}"
    log_info "Previous: ${PREV}"
    ln -sfn "${PREV}" "${CURRENT_LINK}"
    reload_phpfpm
    reload_nginx
    restart_workers
    log_ok "Rolled back to: $(basename "${PREV}")"
    exit 0
fi

# =============================================================================
# PHASE 3 — FETCH AND CHECKOUT CODE
# =============================================================================
step "Fetching code (branch: ${BRANCH})"
# Fetch the specific branch — bare repos store refs at refs/heads/* not refs/remotes/origin/*
git -C "${REPO_DIR}" fetch origin "${BRANCH}"
COMMIT=$(git -C "${REPO_DIR}" rev-parse FETCH_HEAD)
DEPLOY_ID="${TIMESTAMP}-${COMMIT:0:8}"
log_info "Deploying commit: ${COMMIT}"
log_info "Deploy ID: ${DEPLOY_ID}"

step "Creating release: ${TIMESTAMP}"
mkdir -p "${RELEASE_DIR}"
git -C "${REPO_DIR}" --work-tree="${RELEASE_DIR}" checkout -f FETCH_HEAD
log_ok "Code checked out to ${RELEASE_DIR}"

# Write release metadata — queryable from inside the app or during post-mortems
cat > "${RELEASE_DIR}/release.json" << RELEOF
{
  "deploy_id":    "${DEPLOY_ID}",
  "timestamp":    "${TIMESTAMP}",
  "commit":       "${COMMIT}",
  "branch":       "${BRANCH}",
  "safe_mode":    ${SAFE_MODE},
  "hostname":     "$(hostname -f 2>/dev/null || hostname)",
  "deploy_user":  "$(whoami 2>/dev/null)",
  "php_version":  "$(${PHP} -r 'echo PHP_VERSION;' 2>/dev/null || echo n/a)",
  "node_version": "$(node --version 2>/dev/null || echo n/a)"
}
RELEOF
log_ok "release.json written"

# =============================================================================
# PHASE 4 — LINK SHARED RESOURCES
# =============================================================================
step "Linking shared resources"
rm -rf "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/storage"        "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/public/storage" "${RELEASE_DIR}/public/storage"
ln -sfn "${SHARED_DIR}/.env"           "${RELEASE_DIR}/.env"
mkdir -p "${RELEASE_DIR}/bootstrap/cache"
chmod -R 775 "${RELEASE_DIR}/bootstrap/cache"
log_ok "Shared links created"

# =============================================================================
# PHASE 5 — PHP DEPENDENCIES
# =============================================================================
step "Installing Composer dependencies"
T_COMPOSER_START=$(date +%s)

# frontend mode: skip composer install entirely — reuse vendor from previous release
if [[ "${DEPLOY_MODE}" == "frontend" ]]; then
    if [[ -n "${DEPLOY_PREVIOUS_RELEASE}" && -d "${DEPLOY_PREVIOUS_RELEASE}/vendor" ]]; then
        cp -al "${DEPLOY_PREVIOUS_RELEASE}/vendor" "${RELEASE_DIR}/vendor"
        log_ok "frontend mode — vendor/ copied from previous release (composer install skipped)"
        T_COMPOSER_END=$(date +%s)
    else
        log_warn "frontend mode but no previous vendor found — falling through to composer install"
    fi
fi

# Fast deploy: reuse vendor/ via hardlinks when composer.lock is identical
COMPOSER_SKIP=0
if [[ -n "${DEPLOY_PREVIOUS_RELEASE}" && -d "${DEPLOY_PREVIOUS_RELEASE}/vendor" ]]; then
    LOCK_HASH_NEW=$(md5sum "${RELEASE_DIR}/composer.lock"           2>/dev/null | awk '{print $1}')
    LOCK_HASH_OLD=$(md5sum "${DEPLOY_PREVIOUS_RELEASE}/composer.lock" 2>/dev/null | awk '{print $1}')
    if [[ -n "${LOCK_HASH_NEW}" && "${LOCK_HASH_NEW}" == "${LOCK_HASH_OLD}" ]]; then
        cp -al "${DEPLOY_PREVIOUS_RELEASE}/vendor" "${RELEASE_DIR}/vendor"
        COMPOSER_SKIP=1
        log_ok "vendor/ copied via hardlinks (composer.lock unchanged) — composer install skipped"
    fi
fi
if [[ ${COMPOSER_SKIP} -eq 0 ]]; then
    "${PHP}" "${COMPOSER}" install \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --prefer-dist \
        --working-dir="${RELEASE_DIR}" \
        || die_class "COMPOSER_FAILURE" "composer install failed — check composer.json / package constraints"
fi

T_COMPOSER_END=$(date +%s)
log_ok "Composer done ($((T_COMPOSER_END - T_COMPOSER_START))s)"

# =============================================================================
# PHASE 6 — FRONTEND BUILD  (smart cache + zero-wasted-npm-ci architecture)
#
# THREE-LAYER CACHE STRATEGY:
#   Layer 1 — Frontend fingerprint:  if no source changed → skip Vite entirely
#   Layer 2 — Shared node_modules:   if package-lock unchanged → skip npm ci
#   Layer 3 — Previous release:      hardlink fallback for node_modules
#
# TARGET SAVINGS vs naïve "npm ci + vite build" every time:
#   Backend-only change   → 100% skipped (fingerprint hit) = saves 15-20 min
#   Frontend change only  → npm ci skipped (shared cache hit) = saves 15-20 min
#   Full dep upgrade      → full npm ci + vite build (unavoidable)
# =============================================================================
step "Building frontend assets"
T_BUILD_START=$(date +%s)

# ── No package.json → nothing to do ──────────────────────────────────────────
if [[ ! -f "${RELEASE_DIR}/package.json" ]]; then
    log_warn "No package.json — frontend build not applicable"
    T_BUILD_END=$(date +%s)

# ── Backend / hotfix / safe mode → skip build, copy cached artifacts ──────────
elif [[ ${SAFE_MODE} -eq 1 || "${DEPLOY_MODE}" == "backend" ]]; then
    log_warn "${DEPLOY_MODE^^} mode — frontend build skipped; restoring cached artifacts"
    FE_SKIP_REASON="${DEPLOY_MODE^^} mode (explicit)"
    # Prefer shared build cache (survives release pruning), fall back to previous release
    if [[ -d "${SHARED_BUILD_CACHE}/build" && -f "${SHARED_BUILD_CACHE}/build/manifest.json" ]]; then
        cp -r "${SHARED_BUILD_CACHE}/build" "${RELEASE_DIR}/public/build"
        log_ok "Build artifacts restored from shared cache"
    elif [[ -n "${DEPLOY_PREVIOUS_RELEASE}" && -d "${DEPLOY_PREVIOUS_RELEASE}/public/build" ]]; then
        cp -r "${DEPLOY_PREVIOUS_RELEASE}/public/build" "${RELEASE_DIR}/public/build"
        log_ok "Build artifacts copied from previous release (fallback)"
    else
        log_warn "No cached build found — frontend assets may be missing on this release"
    fi
    T_BUILD_END=$(date +%s)

# ── Node.js missing → cannot build ───────────────────────────────────────────
elif ! command -v node &>/dev/null; then
    log_err "Node.js not found — frontend build cannot run (package.json present)."
    log_err "Install: curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash - && sudo apt-get install -y nodejs"
    die "Node.js required. Aborting deploy."

# ── Full build path ────────────────────────────────────────────────────────────
else
    # ── LAYER 1: Frontend fingerprint check ───────────────────────────────────
    # Compute a combined hash of ALL frontend inputs (JS/CSS/Blade/config).
    # If the hash matches the shared build cache, Vite output is identical —
    # skip the build entirely and copy the cached public/build.
    log_info "Computing frontend source fingerprint..."
    FE_FINGERPRINT=$(frontend_fingerprint "${RELEASE_DIR}")
    FE_CACHED_HASH=$(cat "${SHARED_BUILD_CACHE}/.fe_hash" 2>/dev/null || echo "")

    if [[ -n "${FE_FINGERPRINT}" && \
          "${FE_FINGERPRINT}" == "${FE_CACHED_HASH}" && \
          -d "${SHARED_BUILD_CACHE}/build" && \
          -f "${SHARED_BUILD_CACHE}/build/manifest.json" ]]; then
        # ── FINGERPRINT HIT: reuse cached build, skip Vite entirely ──────────
        FE_SKIP_REASON="frontend source unchanged (fp: ${FE_FINGERPRINT:0:12})"
        log_ok "Frontend fingerprint matched — ${FE_SKIP_REASON}"
        log_ok "Skipping npm ci AND npm run build — restoring from shared build cache"
        cp -r "${SHARED_BUILD_CACHE}/build" "${RELEASE_DIR}/public/build"
        log_ok "Cached build artifacts restored to release"

        # Also restore node_modules from shared cache so artisan/php can reference
        # any node-based tooling if needed (non-critical, best-effort)
        CURRENT_PKG_HASH=$(md5sum "${RELEASE_DIR}/package-lock.json" 2>/dev/null | awk '{print $1}')
        CACHED_NM_PKG_HASH=$(cat "${SHARED_NM_CACHE}/.pkg_hash" 2>/dev/null || echo "")
        if [[ "${CURRENT_PKG_HASH}" == "${CACHED_NM_PKG_HASH}" && \
              -d "${SHARED_NM_CACHE}/node_modules" ]]; then
            cp -al "${SHARED_NM_CACHE}/node_modules" "${RELEASE_DIR}/node_modules" 2>/dev/null || true
            NM_SKIP_REASON="shared cache hit with fingerprint reuse"
            log_info "node_modules also restored from shared cache (best-effort)"
        fi
        T_BUILD_END=$(date +%s)

    else
        # ── FINGERPRINT MISS: frontend changed — need to run Vite ─────────────
        if [[ -z "${FE_CACHED_HASH}" ]]; then
            log_info "No cached build found (first deploy or cache cleared) — full build required"
        else
            log_info "Frontend sources changed (fp: ${FE_FINGERPRINT:0:12} ≠ cached: ${FE_CACHED_HASH:0:12})"
        fi

        cd "${RELEASE_DIR}"
        # Cap heap to avoid OOM-kill on low-memory VPS (512 MB is safe for Vite+Tailwind)
        export NODE_OPTIONS="--max-old-space-size=512"

        # ── LAYER 2: node_modules — shared persistent cache ───────────────────
        # Shared cache survives release pruning. It's indexed by package-lock.json hash.
        # If hash matches → restore via hardlinks (fast, zero extra disk usage).
        # If hash differs → full npm ci, then update the shared cache.
        CURRENT_PKG_HASH=$(md5sum "${RELEASE_DIR}/package-lock.json" 2>/dev/null | awk '{print $1}')
        CACHED_NM_PKG_HASH=$(cat "${SHARED_NM_CACHE}/.pkg_hash" 2>/dev/null || echo "")
        NM_INSTALLED=0

        if [[ -n "${CURRENT_PKG_HASH}" && \
              "${CURRENT_PKG_HASH}" == "${CACHED_NM_PKG_HASH}" && \
              -d "${SHARED_NM_CACHE}/node_modules" ]]; then
            # ── LAYER 2 HIT: restore from shared cache ────────────────────────
            log_info "Shared node_modules cache hit — restoring via hardlinks..."
            cp -al "${SHARED_NM_CACHE}/node_modules" "${RELEASE_DIR}/node_modules" \
                && NM_INSTALLED=1 \
                || log_warn "Hardlink restore failed (different filesystem?) — will fallback to npm ci"
            if [[ ${NM_INSTALLED} -eq 1 ]]; then
                NM_SKIP_REASON="shared cache hit (pkg-lock fp: ${CURRENT_PKG_HASH:0:12})"
                log_ok "node_modules from shared cache — npm ci SKIPPED (${NM_SKIP_REASON})"
            fi
        fi

        # ── LAYER 3: Fallback to previous release hardlinks ───────────────────
        if [[ ${NM_INSTALLED} -eq 0 && \
              -n "${DEPLOY_PREVIOUS_RELEASE}" && \
              -d "${DEPLOY_PREVIOUS_RELEASE}/node_modules" ]]; then
            PREV_PKG_HASH=$(md5sum "${DEPLOY_PREVIOUS_RELEASE}/package-lock.json" 2>/dev/null | awk '{print $1}')
            if [[ -n "${CURRENT_PKG_HASH}" && "${CURRENT_PKG_HASH}" == "${PREV_PKG_HASH}" ]]; then
                cp -al "${DEPLOY_PREVIOUS_RELEASE}/node_modules" "${RELEASE_DIR}/node_modules" \
                    && NM_INSTALLED=1 \
                    || log_warn "Hardlink from previous release failed — will run npm ci"
                if [[ ${NM_INSTALLED} -eq 1 ]]; then
                    NM_SKIP_REASON="previous release hardlinks (pkg-lock unchanged)"
                    log_ok "node_modules via hardlinks from previous release — npm ci SKIPPED"
                fi
            fi
        fi

        # ── Fresh npm ci (optimized flags) ────────────────────────────────────
        if [[ ${NM_INSTALLED} -eq 0 ]]; then
            log_info "Running: npm ci --prefer-offline --no-audit --no-fund --progress=false"
            log_info "(--prefer-offline reuses tarball cache; --no-audit skips slow security lookup)"
            T_NPM_CI_START=$(date +%s)
            npm ci --prefer-offline --no-audit --no-fund --progress=false 2>&1 \
                || die_class "VITE_FAILURE" "npm ci failed — check package.json/package-lock.json are in sync"
            T_NPM_CI_END=$(date +%s)
            NM_INSTALLED=1
            log_ok "npm ci complete ($((T_NPM_CI_END - T_NPM_CI_START))s)"

            # Populate shared node_modules cache for ALL future deploys
            log_info "Populating shared node_modules cache (${SHARED_NM_CACHE})..."
            rm -rf "${SHARED_NM_CACHE}/node_modules"
            mkdir -p "${SHARED_NM_CACHE}"
            cp -al "${RELEASE_DIR}/node_modules" "${SHARED_NM_CACHE}/node_modules" 2>/dev/null \
                || cp -r  "${RELEASE_DIR}/node_modules" "${SHARED_NM_CACHE}/node_modules"
            echo "${CURRENT_PKG_HASH}" > "${SHARED_NM_CACHE}/.pkg_hash"
            log_ok "Shared node_modules cache populated (next deploy skips npm ci automatically)"
        fi

        # ── Clear stale Vite OUTPUT (public/build) — always regenerate ────────
        # DO NOT clear node_modules/.vite — that's Vite's dep-optimisation cache
        # which is valid as long as node_modules haven't changed. Clearing it
        # would force Vite to re-bundle all dependencies from scratch on every build.
        rm -rf "${RELEASE_DIR}/public/build"
        log_info "Cleared stale public/build (Vite dep cache in node_modules/.vite preserved)"

        # ── Run Vite build ────────────────────────────────────────────────────
        log_info "Running: npm run build (timeout 600s)..."
        T_NPM_BUILD_START=$(date +%s)
        set +e
        timeout 600 npm run build 2>&1
        BUILD_EXIT=$?
        set -e
        T_NPM_BUILD_END=$(date +%s)

        if [[ ${BUILD_EXIT} -eq 124 ]]; then
            free -h 2>/dev/null || true
            die_class "VITE_FAILURE" "npm run build timed out after 600s — server may be OOM-killing. Add swap: sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile && sudo mkswap /swapfile && sudo swapon /swapfile"
        fi
        if [[ ${BUILD_EXIT} -ne 0 ]]; then
            if dmesg 2>/dev/null | tail -20 | grep -qi "oom\|killed process\|out of memory"; then
                log_err "OOM kill detected during npm build. Available RAM:"; free -h 2>/dev/null || true
                log_err "Fix: sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile && sudo mkswap /swapfile && sudo swapon /swapfile"
            fi
            die_class "VITE_FAILURE" "npm run build exited with code ${BUILD_EXIT}"
        fi

        # ── Build validation (all original checks preserved) ──────────────────
        if [[ ! -f "${RELEASE_DIR}/public/build/manifest.json" ]]; then
            log_err "Build appeared to succeed but manifest.json is missing."
            log_err "Expected: ${RELEASE_DIR}/public/build/manifest.json"
            if [[ -d "${RELEASE_DIR}/public/build" ]]; then
                log_err "Contents of public/build/:"; ls -la "${RELEASE_DIR}/public/build/" 2>/dev/null || true
            else
                log_err "public/build/ directory does not exist"
            fi
            log_err "Available RAM:"; free -h 2>/dev/null || true
            die "Frontend build incomplete — manifest.json not generated. Aborting deploy."
        fi

        CSS_GLOB="${RELEASE_DIR}/public/build/assets/*.css"
        grep -ql "\.flex"    ${CSS_GLOB} 2>/dev/null || die "Tailwind validation: .flex not found in compiled CSS"
        grep -ql "\.grid"    ${CSS_GLOB} 2>/dev/null || die "Tailwind validation: .grid not found in compiled CSS"
        grep -ql "\.text-sm" ${CSS_GLOB} 2>/dev/null || die "Tailwind validation: .text-sm not found in compiled CSS"
        log_ok "Tailwind utilities validated (.flex / .grid / .text-sm present)"

        if command -v jq &>/dev/null; then
            jq empty "${RELEASE_DIR}/public/build/manifest.json" 2>/dev/null \
                || die "manifest.json is not valid JSON — build is corrupt"
            jq -e '[keys[]] | any(. == "resources/js/app.js")' \
                "${RELEASE_DIR}/public/build/manifest.json" >/dev/null 2>&1 \
                || die "manifest.json missing resources/js/app.js entry"
            jq -e '[keys[]] | any(startswith("resources/css/app"))' \
                "${RELEASE_DIR}/public/build/manifest.json" >/dev/null 2>&1 \
                || die "manifest.json missing resources/css/app.css entry"
            log_ok "Manifest JSON valid — app.js + app.css entries confirmed"
        else
            grep -q '"resources/js/app.js"' "${RELEASE_DIR}/public/build/manifest.json" \
                || die "manifest.json missing resources/js/app.js entry"
            grep -q '"resources/css/app'    "${RELEASE_DIR}/public/build/manifest.json" \
                || die "manifest.json missing resources/css/app.css entry"
            log_ok "Manifest entries verified (grep fallback — install jq for JSON parsing)"
        fi

        EMPTY_ASSETS=$(find "${RELEASE_DIR}/public/build/assets" -type f -size 0 2>/dev/null | head -5)
        if [[ -n "${EMPTY_ASSETS}" ]]; then
            log_err "Zero-byte build assets detected (corrupt build):"
            echo "${EMPTY_ASSETS}" | while IFS= read -r f; do log_err "  ${f}"; done
            die "Empty assets found — build incomplete. Free RAM and retry."
        fi
        log_ok "No zero-byte assets detected"

        # Stamp with commit hash for traceability
        echo "${COMMIT}" > "${RELEASE_DIR}/public/build/version.txt"
        log_ok "Build stamped: ${COMMIT:0:12} → public/build/version.txt"

        # ── Populate shared build cache for next deploy ───────────────────────
        # This is the key to Layer 1: if next deploy has same frontend sources,
        # it will hit this cache and skip npm ci + Vite entirely.
        if [[ -n "${FE_FINGERPRINT}" ]]; then
            log_info "Populating shared build cache (${SHARED_BUILD_CACHE})..."
            rm -rf "${SHARED_BUILD_CACHE}/build"
            mkdir -p "${SHARED_BUILD_CACHE}"
            cp -r "${RELEASE_DIR}/public/build" "${SHARED_BUILD_CACHE}/build"
            echo "${FE_FINGERPRINT}" > "${SHARED_BUILD_CACHE}/.fe_hash"
            echo "${TIMESTAMP}"      > "${SHARED_BUILD_CACHE}/.build_ts"
            log_ok "Shared build cache populated (fp: ${FE_FINGERPRINT:0:12}) — next backend deploy skips Vite"
        fi

        # ── Flat-clone fallback for nginx setups pointing to APP_DIR/public ───
        APP_PUBLIC_REAL="$(realpath "${APP_DIR}/public" 2>/dev/null || echo "")"
        REL_PUBLIC_REAL="$(realpath "${RELEASE_DIR}/public" 2>/dev/null || echo "")"
        if [[ -n "${APP_PUBLIC_REAL}" && "${APP_PUBLIC_REAL}" != "${REL_PUBLIC_REAL}" ]]; then
            rm -rf "${APP_DIR}/public/build"
            cp -r "${RELEASE_DIR}/public/build" "${APP_DIR}/public/build"
            log_ok "Build artifacts also copied to ${APP_DIR}/public/build (nginx flat-clone fallback)"
        fi

        cd - >/dev/null
        T_BUILD_END=$(date +%s)
        log_ok "Frontend build done ($((T_NPM_BUILD_END - T_NPM_BUILD_START))s) — manifest verified"
    fi   # end fingerprint miss branch
fi       # end full build path

# =============================================================================
# PHASE 6c — PWA SERVICE WORKER CACHE VERSION BUMP
# IMPORTANT: must run AFTER the frontend build so sw.js is the deployed copy.
# Bumping CACHE_VERSION forces all existing PWA clients to discard their stale
# offline caches and re-fetch assets on next activation — prevents broken
# cached responses after every deploy.
# =============================================================================
step "Bumping PWA service worker cache version"
SW_FILE="${RELEASE_DIR}/public/sw.js"
if [[ -f "${SW_FILE}" ]]; then
    NEW_CACHE_VERSION="ef-v${TIMESTAMP}"
    # Match: const CACHE_VERSION = 'ef-v1';  (single or double-quoted)
    sed -i "s/const CACHE_VERSION\s*=\s*['\"][^'\"]*['\"]/const CACHE_VERSION = '${NEW_CACHE_VERSION}'/" "${SW_FILE}"
    # Verify the substitution landed
    if grep -q "const CACHE_VERSION = '${NEW_CACHE_VERSION}'" "${SW_FILE}"; then
        log_ok "SW cache version → ${NEW_CACHE_VERSION}"
    else
        log_warn "SW CACHE_VERSION sed replacement failed — stale PWA caches will NOT be invalidated"
        log_warn "Current sw.js CACHE_VERSION line:"
        grep "CACHE_VERSION" "${SW_FILE}" | head -3 >&2 || true
    fi
    # Verify the flat-clone copy (nginx fallback) also gets the updated sw.js
    FLAT_SW="${APP_DIR}/public/sw.js"
    if [[ -f "${FLAT_SW}" ]] && \
       [[ "$(realpath "${APP_DIR}/public" 2>/dev/null)" != "$(realpath "${RELEASE_DIR}/public" 2>/dev/null)" ]]; then
        cp "${SW_FILE}" "${FLAT_SW}"
        log_ok "Flat-clone public/sw.js also updated (nginx fallback)"
    fi
else
    log_warn "public/sw.js not found in release — PWA cache version not bumped"
    log_warn "Expected: ${SW_FILE}"
fi

# =============================================================================
# PHASE 6b — ARTISAN HEALTH CHECK (catches boot failures before maintenance mode)
# =============================================================================
step "Artisan health check (new release)"
ARTISAN_NEW="${PHP} ${RELEASE_DIR}/artisan"
# artisan about boots the entire application — catches broken providers, missing
# bindings, syntax errors, and missing .env keys before any traffic is affected
${ARTISAN_NEW} about --no-interaction 2>&1 \
    || die_class "BOOT_FAILURE" "artisan about failed on new release — broken provider, syntax error, or missing binding. Deploy aborted before maintenance mode."
# route:list verifies all route closures and controller constructors resolve
set +e
${ARTISAN_NEW} route:list --no-interaction >/dev/null 2>&1
ROUTE_EXIT=$?
set -e
if [[ ${ROUTE_EXIT} -ne 0 ]]; then
    log_warn "artisan route:list returned errors — route provider may have issues (non-fatal, continuing)"
fi
log_ok "Artisan boots cleanly on new release (deploy_id: ${DEPLOY_ID})"

# =============================================================================
# PHASE 7 — MAINTENANCE MODE ON
# =============================================================================
step "Enabling maintenance mode"
CURRENT_ACTIVE=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")
if [[ -n "${CURRENT_ACTIVE}" && -d "${CURRENT_ACTIVE}" ]]; then
    "${PHP}" "${CURRENT_ACTIVE}/artisan" down \
        --render="errors.503" \
        --retry=30 \
        --refresh=15 \
        --secret="$(openssl rand -hex 16)" \
        2>/dev/null || log_warn "Could not enable maintenance mode on current release"
else
    log_info "No current release — skipping maintenance mode"
fi

# =============================================================================
# PHASE 8a — DATABASE BACKUP
# =============================================================================
step "Pre-migration database backup (PostgreSQL)"
DB_BACKUP_DIR="${SHARED_DIR}/storage/backups/db"
mkdir -p "${DB_BACKUP_DIR}"
if command -v pg_dump &>/dev/null && [[ -f "${SHARED_DIR}/.env" ]]; then
    _DB_NAME=$(grep -E '^DB_DATABASE=' "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs 2>/dev/null)
    _DB_USER=$(grep -E '^DB_USERNAME=' "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs 2>/dev/null)
    _DB_HOST=$(grep -E '^DB_HOST='     "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs 2>/dev/null)
    _DB_PORT=$(grep -E '^DB_PORT='     "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | xargs 2>/dev/null)
    _DB_PASS=$(grep -E '^DB_PASSWORD=' "${SHARED_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'")
    _DB_HOST="${_DB_HOST:-127.0.0.1}"
    _DB_PORT="${_DB_PORT:-5432}"
    BACKUP_FILE="${DB_BACKUP_DIR}/pre-deploy-${TIMESTAMP}.sql.gz"
    set +e
    PGPASSWORD="${_DB_PASS}" pg_dump \
        -h "${_DB_HOST}" -p "${_DB_PORT}" -U "${_DB_USER}" -d "${_DB_NAME}" \
        --no-owner --no-acl 2>/dev/null | gzip > "${BACKUP_FILE}"
    BACKUP_EXIT=$?
    set -e
    if [[ ${BACKUP_EXIT} -eq 0 && -s "${BACKUP_FILE}" ]]; then
        BACKUP_SIZE=$(du -sh "${BACKUP_FILE}" 2>/dev/null | cut -f1)
        log_ok "DB backup: ${BACKUP_FILE} (${BACKUP_SIZE})"
        # Keep last 3 backups
        ls -1t "${DB_BACKUP_DIR}"/pre-deploy-*.sql.gz 2>/dev/null | tail -n +4 | xargs rm -f 2>/dev/null || true
    else
        rm -f "${BACKUP_FILE}" 2>/dev/null || true
        log_warn "DB backup failed or empty — proceeding without backup (check pg_dump permissions)"
    fi
else
    log_warn "pg_dump not found — skipping DB backup"
    log_warn "Install: sudo apt-get install postgresql-client"
fi

# =============================================================================
# PHASE 8 — DATABASE MIGRATIONS
# Schema alignment is the single most common cause of production outages.
# This phase enforces a strict contract:
#   "No deployment succeeds when application code requires DB changes that
#    have not been applied."
#
# Escape hatch: add --force-migrate to apply migrations even in safe/hotfix
# mode (e.g. when a migration-only hotfix is needed).
# =============================================================================
step "Database migration safety check"

# ── 8.1 Verify DB is reachable ───────────────────────────────────────────────
# A failed migration after maintenance mode is ON leaves the app down.
# Verify connectivity before touching anything.
if ! "${PHP}" -r "
    \$env = parse_ini_file('${SHARED_DIR}/.env');
    \$dsn = 'pgsql:host=' . (\$env['DB_HOST'] ?? '127.0.0.1') . ';port=' . (\$env['DB_PORT'] ?? 5432) . ';dbname=' . (\$env['DB_DATABASE'] ?? '');
    try { new PDO(\$dsn, \$env['DB_USERNAME'] ?? '', \$env['DB_PASSWORD'] ?? ''); echo 'ok'; }
    catch(Exception \$e) { echo 'fail:' . \$e->getMessage(); exit(1); }
" 2>/dev/null | grep -q "^ok"; then
    die "Database not reachable — aborting before migration to prevent broken deploy state.
Verify DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD in ${SHARED_DIR}/.env"
fi
log_ok "Database connection verified"

# ── 8.2 Detect pending migrations ────────────────────────────────────────────
# Run migrate:status against the RELEASE_DIR (new code) so we detect migrations
# that are new in this release even if they haven't been registered before.
log_info "[$(_ts)] Checking migration status..."
MIGRATE_STATUS_OUTPUT=$("${PHP}" "${RELEASE_DIR}/artisan" migrate:status \
    --no-interaction 2>/dev/null || true)

# Count rows that start with "Pending" (matches Laravel 10/11/12/13 output format)
PENDING_COUNT=$(echo "${MIGRATE_STATUS_OUTPUT}" | grep -c "Pending" || true)

if [[ "${PENDING_COUNT:-0}" -gt 0 ]]; then
    log_warn "──────────────────────────────────────────────────────────────────────────"
    log_warn "  PENDING MIGRATIONS DETECTED: ${PENDING_COUNT}"
    log_warn "──────────────────────────────────────────────────────────────────────────"
    echo "${MIGRATE_STATUS_OUTPUT}" | grep "Pending" | while IFS= read -r line; do
        log_warn "  PENDING ▶ ${line}"
    done
    log_warn "──────────────────────────────────────────────────────────────────────────"
else
    log_ok "Schema aligned — no pending migrations"
fi

# ── 8.3 Decide: run or abort ─────────────────────────────────────────────────
_SKIP_MIGRATE=0
_MIGRATE_REASON=""

if [[ ${SAFE_MODE} -eq 1 && "${DEPLOY_MODE}" == "frontend" ]]; then
    _SKIP_MIGRATE=1
    _MIGRATE_REASON="frontend mode"
elif [[ ${SAFE_MODE} -eq 1 ]]; then
    _SKIP_MIGRATE=1
    _MIGRATE_REASON="${DEPLOY_MODE} / safe mode"
fi

# --force-migrate overrides the skip decision
if [[ ${FORCE_MIGRATE} -eq 1 && ${_SKIP_MIGRATE} -eq 1 ]]; then
    _SKIP_MIGRATE=0
    log_warn "--force-migrate specified: migrations will run despite ${_MIGRATE_REASON}"
    _MIGRATE_REASON=""
fi

if [[ ${_SKIP_MIGRATE} -eq 1 ]]; then
    # ── BLOCKED: safe/hotfix/frontend mode with pending migrations ───────────
    if [[ "${PENDING_COUNT:-0}" -gt 0 ]]; then
        log_err "═══════════════════════════════════════════════════════════════════════"
        log_err "  DEPLOYMENT BLOCKED — PENDING MIGRATIONS IN ${_MIGRATE_REASON^^}"
        log_err "═══════════════════════════════════════════════════════════════════════"
        log_err ""
        log_err "  ${PENDING_COUNT} migration(s) are pending. Deploying this code without"
        log_err "  running migrations will cause SQLSTATE column-not-found errors."
        log_err ""
        log_err "  This is exactly how the meal_plans.deleted_at outage occurred:"
        log_err "    → Model used SoftDeletes (queries append WHERE deleted_at IS NULL)"
        log_err "    → Migration existed but safe/hotfix deploy skipped it"
        log_err "    → Column missing in DB → SQLSTATE[42703] → 500 errors for all users"
        log_err ""
        log_err "  YOUR OPTIONS:"
        log_err ""
        log_err "  1) Run a full deploy (runs migrations):"
        log_err "     bash deploy.sh ${BRANCH}"
        log_err ""
        log_err "  2) Run migrations first, then hotfix:"
        log_err "     php artisan migrate --force    # on the server"
        log_err "     bash deploy.sh ${BRANCH} --hotfix"
        log_err ""
        log_err "  3) Force migrations in this safe/hotfix deploy (use with caution):"
        log_err "     bash deploy.sh ${BRANCH} --hotfix --force-migrate"
        log_err ""
        log_err "  Pending migrations:"
        echo "${MIGRATE_STATUS_OUTPUT}" | grep "Pending" | while IFS= read -r line; do
            log_err "    ▶ ${line}"
        done
        log_err ""
        die_class "SCHEMA_MISMATCH" "Deploy aborted: pending migrations exist in ${_MIGRATE_REASON}. See options above."
    else
        log_ok "${_MIGRATE_REASON^^} — no pending migrations, schema is aligned, safe to deploy"
    fi

else
    # ── FULL / BACKEND mode: run migrations ──────────────────────────────────
    step "Running database migrations"

    if [[ "${PENDING_COUNT:-0}" -gt 0 ]]; then
        log_info "Applying ${PENDING_COUNT} migration(s):"
        echo "${MIGRATE_STATUS_OUTPUT}" | grep "Pending" | while IFS= read -r line; do
            log_info "  ▶ ${line}"
        done
    else
        log_info "No pending migrations — migrate will no-op (schema already current)"
    fi

    log_info "[$(_ts)] Starting: php artisan migrate --force --isolated"
    T_MIGRATE_START=$(date +%s)

    # --isolated: acquires DB-level lock so concurrent deploys can't run
    #             migrations simultaneously (prevents duplicate column errors)
    "${PHP}" "${RELEASE_DIR}/artisan" migrate --force --no-interaction --isolated 2>&1 \
        | while IFS= read -r line; do log_info "  migrate: ${line}"; done
    MIGRATE_EXIT=${PIPESTATUS[0]}

    T_MIGRATE_END=$(date +%s)
    _MIGRATE_ELAPSED=$(( T_MIGRATE_END - T_MIGRATE_START ))

    if [[ ${MIGRATE_EXIT} -ne 0 ]]; then
        log_err "═══════════════════════════════════════════════════════════════════════"
        log_err "  MIGRATION FAILED (exit ${MIGRATE_EXIT}) — ${_MIGRATE_ELAPSED}s"
        log_err "═══════════════════════════════════════════════════════════════════════"
        log_err ""
        log_err "  ROLLBACK GUIDANCE:"
        log_err ""
        log_err "  The deploy will auto-rollback to the previous release."
        log_err "  However, if any migration partially committed to PostgreSQL,"
        log_err "  you may need manual DB intervention:"
        log_err ""
        log_err "  1) Identify which migration failed:"
        log_err "     php artisan migrate:status"
        log_err ""
        log_err "  2) Check PostgreSQL logs:"
        log_err "     sudo tail -100 /var/log/postgresql/postgresql-*.log"
        log_err ""
        log_err "  3) If a migration partially ran, roll it back:"
        log_err "     php artisan migrate:rollback --step=1"
        log_err "     OR manually:"
        log_err "     psql -U \$DB_USER -d \$DB_NAME -c 'BEGIN; [your rollback SQL]; COMMIT;'"
        log_err ""
        log_err "  4) Fix the migration file, commit, and re-deploy"
        log_err ""
        log_err "  5) To skip a broken migration temporarily:"
        log_err "     INSERT INTO migrations (migration, batch) VALUES ('migration_name', 99);"
        log_err "     (Only do this if you've applied the schema change manually)"
        log_err ""
        die_class "DB_FAILURE" "Migrations failed — auto-rollback initiated. See rollback guidance above."
    fi

    log_ok "Migrations complete (${_MIGRATE_ELAPSED}s)"

    # ── 8.4 Post-migration verification: MUST be zero pending ────────────────
    # If any migrations are still pending after migrate, it means:
    #   - A migration silently failed and Laravel marked it as ran anyway (rare)
    #   - A new migration was added during this deploy (race condition)
    #   - --isolated lock prevented a concurrent migration from running
    log_info "[$(_ts)] Verifying post-migration schema alignment..."
    POST_STATUS=$("${PHP}" "${RELEASE_DIR}/artisan" migrate:status --no-interaction 2>/dev/null || true)
    STILL_PENDING=$(echo "${POST_STATUS}" | grep -c "Pending" || true)

    if [[ "${STILL_PENDING:-0}" -gt 0 ]]; then
        log_err "═══════════════════════════════════════════════════════════════════════"
        log_err "  POST-MIGRATION CHECK FAILED: ${STILL_PENDING} migration(s) still pending"
        log_err "═══════════════════════════════════════════════════════════════════════"
        echo "${POST_STATUS}" | grep "Pending" | while IFS= read -r line; do
            log_err "  STILL PENDING ▶ ${line}"
        done
        log_err ""
        log_err "  This usually means a migration silently failed (artisan exited 0"
        log_err "  but did not complete all operations). Investigate before traffic"
        log_err "  hits this release."
        log_err ""
        die_class "SCHEMA_MISMATCH" "Schema alignment failed: ${STILL_PENDING} migration(s) still pending after migrate. Aborting."
    fi

    log_ok "Post-migration schema verified — all migrations applied"
fi

# =============================================================================
# PHASE 9 — ACTIVATE RELEASE
# =============================================================================
step "Activating release"
ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}"
log_ok "Current → ${TIMESTAMP}"

# Reset OPcache so PHP-FPM workers don't serve stale compiled bytecode from old release
"${PHP}" -r "if(function_exists('opcache_reset')){opcache_reset();echo 'OPcache reset';}else{echo 'OPcache not available';}" 2>/dev/null | while IFS= read -r line; do log_info "${line}"; done || true
# Also signal php-fpm to gracefully reload workers (belt-and-suspenders)
safe_sudo kill -USR2 "$(cat /run/php/php8.3-fpm.pid 2>/dev/null || echo 0)" 2>/dev/null || true

# =============================================================================
# PHASE 10 — LARAVEL OPTIMIZATIONS
# =============================================================================
step "Running post-deploy optimizations"
ARTISAN="${PHP} ${CURRENT_LINK}/artisan"

# ── Flush ALL stale caches before rebuilding ────────────────────────────────
# Prevents config/routes/views from surviving from a previous release.
${ARTISAN} optimize:clear     && log_ok "All caches cleared" \
    || log_warn "optimize:clear failed — stale caches may persist"

${ARTISAN} storage:link 2>/dev/null || true
if [[ -L "${RELEASE_DIR}/public/storage" ]]; then
    log_ok "Storage link valid"
else
    log_warn "public/storage symlink missing after storage:link — file uploads may not be accessible"
fi

# ── Build application caches — each must succeed ────────────────────────────
# config:cache: compiles all config files. Failure = env vars missing or syntax error.
${ARTISAN} config:cache --no-interaction \
    && log_ok "Config cached" \
    || die_class "CACHE_FAILURE" "config:cache failed — check config files for syntax errors or missing env vars.
Tip: php artisan config:cache on the server to see the exact error."

# route:cache: compiles all routes. Failure = invalid route definition or missing controller.
${ARTISAN} route:cache --no-interaction \
    && log_ok "Routes cached" \
    || die_class "CACHE_FAILURE" "route:cache failed — check for invalid route definitions or missing controller classes.
Tip: php artisan route:list on the server to see which route fails."

# view:cache: pre-compiles all Blade templates. Failure = Blade syntax error.
${ARTISAN} view:cache --no-interaction \
    && log_ok "Views cached" \
    || die_class "CACHE_FAILURE" "view:cache failed — check Blade templates for syntax errors.
Tip: php artisan view:cache on the server to see which template fails."

${ARTISAN} event:cache        && log_ok "Events cached" \
    || log_warn "event:cache failed — event listeners may not load correctly"
${ARTISAN} icons:cache        2>/dev/null || true

# ── Application health snapshot via artisan about ───────────────────────────
# 'artisan about' boots the full application and reports env, drivers, caches.
# Failure here means the app cannot boot — catches class-not-found, bad config, etc.
log_info "[$(_ts)] Running artisan about (application boot verification)..."
if ABOUT_OUTPUT=$("${PHP}" "${CURRENT_LINK}/artisan" about --no-interaction 2>&1); then
    log_ok "artisan about — application boots correctly"
    # Extract and log key facts from about output
    echo "${ABOUT_OUTPUT}" | grep -E "Environment|Debug|URL|Database|Driver|Cache|Queue" \
        | while IFS= read -r line; do log_info "  ${line}"; done || true

    # Security: warn if APP_DEBUG shows as true in artisan about output
    if echo "${ABOUT_OUTPUT}" | grep -qiE "Debug.*(true|enabled|on)"; then
        log_warn "  ⚠ APP_DEBUG appears enabled — verify this is intentional for ${_APP_ENV:-unknown env}"
    fi
else
    log_err "artisan about failed — application cannot boot cleanly:"
    echo "${ABOUT_OUTPUT}" | tail -20 | while IFS= read -r line; do log_err "  ${line}"; done
    die_class "BOOT_FAILURE" "Application failed to boot (artisan about exit non-zero). Deploy aborted.
This means the new code has a fatal error that prevents the app from starting.
Common causes: missing config key, class not found, syntax error in a service provider."
fi

if [[ ${SAFE_MODE} -eq 1 ]]; then
    log_warn "SAFE MODE active — queue restart skipped"
else
    # Signal queue workers to restart gracefully — they finish current job then reload new code
    ${ARTISAN} queue:restart && log_ok "Queue workers signalled to restart" || log_warn "queue:restart failed"
    # Terminate Horizon if installed (it restarts itself via supervisor)
    ${ARTISAN} horizon:terminate 2>/dev/null && log_ok "Horizon terminated (will auto-restart)" || true
fi

# =============================================================================
# PHASE 11 — MAINTENANCE MODE OFF
# =============================================================================
step "Disabling maintenance mode"
${ARTISAN} up
log_ok "Application online"

# =============================================================================
# PHASE 11b — APPLICATION ERROR SCAN
# =============================================================================
step "Application error scan"
if [[ -f "${_LOG_FILE_SCAN}" ]]; then
    # Only scan content written DURING this deploy (captured pre-size earlier)
    _NEW_LOG=$(tail -c "+$((_LOG_PRE_SIZE + 1))" "${_LOG_FILE_SCAN}" 2>/dev/null || true)
    APP_ERROR_FOUND=0
    declare -A _SCAN_PATTERNS=(
        ["PHP Fatal error"]="PHP_FATAL"
        ["FatalThrowableError"]="PHP_FATAL"
        ["BindingResolutionException"]="BOOT_FAILURE"
        ["Target class .* does not exist"]="BOOT_FAILURE"
        ["Class .* not found"]="BOOT_FAILURE"
        ["SQLSTATE"]="DB_FAILURE"
        ["Vite manifest not found"]="VITE_FAILURE"
        ["Syntax error"]="PHP_FATAL"
    )
    for _pat in "${!_SCAN_PATTERNS[@]}"; do
        if echo "${_NEW_LOG}" | grep -qiE "${_pat}" 2>/dev/null; then
            log_err "  [${_SCAN_PATTERNS[${_pat}]}] Log pattern matched: ${_pat}"
            APP_ERROR_FOUND=1
        fi
    done
    if [[ ${APP_ERROR_FOUND} -eq 1 ]]; then
        log_err "New laravel.log entries since deploy start:"
        echo "${_NEW_LOG}" | tail -n 30 >&2
        die_class "APP_ERROR" "Fatal errors in laravel.log after deploy — auto-rolling back (deploy_id: ${DEPLOY_ID})"
    else
        log_ok "App error scan passed — no fatal patterns in new log entries"
    fi
else
    log_info "laravel.log not yet created — skipping error scan"
fi

# =============================================================================
# PHASE 12 — SET PERMISSIONS
# =============================================================================
step "Setting permissions"
chmod -R 775 "${SHARED_DIR}/storage"
chmod -R 775 "${RELEASE_DIR}/bootstrap/cache"
safe_sudo chown -R www-data:www-data "${SHARED_DIR}/storage" 2>/dev/null || \
    log_warn "Could not chown storage — run: sudo chown -R www-data:www-data ${SHARED_DIR}/storage"
safe_sudo chown -R www-data:www-data "${RELEASE_DIR}/bootstrap/cache" 2>/dev/null || true
# Ensure web server can read Vite build artifacts
safe_sudo chown -R www-data:www-data "${RELEASE_DIR}/public/build" 2>/dev/null || \
    log_warn "Could not chown public/build — web server may not serve assets correctly"
# Also fix flat-clone fallback copy
if [[ -d "${APP_DIR}/public/build" ]] && \
   [[ "$(realpath "${APP_DIR}/public" 2>/dev/null)" != "$(realpath "${RELEASE_DIR}/public" 2>/dev/null)" ]]; then
    safe_sudo chown -R www-data:www-data "${APP_DIR}/public/build" 2>/dev/null || true
fi
log_ok "Permissions set"

# =============================================================================
# PHASE 13 — RELOAD SERVICES
# =============================================================================
step "Reloading services"
reload_phpfpm
reload_nginx
restart_workers

# Verify php-fpm is alive after reload — a bad config can silently kill it
_FPM_SVC=$(detect_phpfpm_service)
if [[ -n "${_FPM_SVC}" ]]; then
    sleep 1
    if systemctl is-active --quiet "${_FPM_SVC}" 2>/dev/null; then
        log_ok "php-fpm (${_FPM_SVC}) active after reload"
    else
        log_warn "php-fpm (${_FPM_SVC}) is NOT active after reload — application is down"
        log_warn "Fix: sudo systemctl start ${_FPM_SVC}"
        log_warn "     sudo journalctl -u ${_FPM_SVC} -n 50"
    fi
fi

# =============================================================================
# PHASE 14 — POST-DEPLOY VALIDATION
# =============================================================================
step "Validating deployment"

VALIDATION_FAILED=0

_validate() {
    local desc="$1"; shift
    if "$@" &>/dev/null 2>&1; then
        log_ok "  ✓ ${desc}"
    else
        log_warn "  ✗ ${desc}"
        VALIDATION_FAILED=1
    fi
}

# Laravel application boots
_validate "Laravel boots (artisan list)" \
    "${PHP}" "${CURRENT_LINK}/artisan" list

# .env symlink resolves
_validate ".env symlink valid" \
    test -f "${CURRENT_LINK}/.env"

# Storage symlink valid
_validate "storage symlink valid" \
    test -L "${CURRENT_LINK}/storage"

# public/storage symlink valid
_validate "public/storage symlink valid" \
    test -L "${CURRENT_LINK}/public/storage"

# Vite manifest present
_validate "Vite manifest present" \
    test -f "${CURRENT_LINK}/public/build/manifest.json"

# CSS asset file exists and is non-trivially sized (derive from manifest)
if [[ -f "${CURRENT_LINK}/public/build/manifest.json" ]]; then
    CSS_FILE=$(python3 -c "
import json,sys
m=json.load(open('${CURRENT_LINK}/public/build/manifest.json'))
k=[k for k in m if 'app.css' in k]
print(m[k[0]]['file'] if k else '')
" 2>/dev/null || true)
    if [[ -n "${CSS_FILE}" ]]; then
        CSS_FULL="${CURRENT_LINK}/public/build/${CSS_FILE}"
        _validate "CSS asset exists (${CSS_FILE})" \
            test -f "${CSS_FULL}"
        # Abort if CSS is suspiciously small (< 10 KB = incomplete build)
        if [[ -f "${CSS_FULL}" ]]; then
            CSS_BYTES=$(wc -c < "${CSS_FULL}" 2>/dev/null || echo 0)
            if [[ ${CSS_BYTES} -lt 10240 ]]; then
                log_err "CSS asset is only ${CSS_BYTES} bytes — build appears incomplete (expected > 10 KB)"
                log_err "This usually means Vite built without source files or Tailwind generated nothing."
                VALIDATION_FAILED=1
            else
                log_ok "  ✓ CSS asset size: ${CSS_BYTES} bytes"
            fi

            # Ensure app-specific classes compiled — catches stale/wrong source build
            if ! grep -q "ef-cal-insight" "${CSS_FULL}" 2>/dev/null; then
                log_err "Custom ExpenseFlow classes missing from CSS (ef-cal-insight not found)"
                log_err "CSS was built from the wrong source or app.css was not included."
                VALIDATION_FAILED=1
            else
                log_ok "  ✓ ExpenseFlow custom classes present (ef-cal-insight)"
            fi
        fi
    fi
fi

# Bootstrap cache writable
_validate "bootstrap/cache writable" \
    test -w "${CURRENT_LINK}/bootstrap/cache"

# Storage logs writable
_validate "storage/logs writable" \
    test -w "${SHARED_DIR}/storage/logs"

# Config cache present
_validate "Config cache generated" \
    test -f "${CURRENT_LINK}/bootstrap/cache/config.php"

# Nginx serving (if curl available)
if command -v curl &>/dev/null; then
    APP_URL=$(grep -E '^APP_URL=' "${SHARED_DIR}/.env" | cut -d= -f2 | tr -d '"' | tr -d "'" | head -1) || true
    if [[ -n "${APP_URL}" ]]; then
        # Follow redirects (-L), verify SSL cert (no --insecure), fail on curl error
        HTTP_CODE=$(curl -sL -o /dev/null -w "%{http_code}" --max-time 15 "${APP_URL}" 2>/dev/null || echo "000")
        if [[ "${HTTP_CODE}" == "200" ]]; then
            log_ok "  ✓ HTTP response: ${HTTP_CODE} (${APP_URL})"
        elif [[ "${HTTP_CODE}" == "302" || "${HTTP_CODE}" == "301" ]]; then
            log_ok "  ✓ HTTP response: ${HTTP_CODE} redirect (${APP_URL})"
        else
            log_warn "  ✗ HTTP response: ${HTTP_CODE} (${APP_URL}) — nginx may not be serving correctly"
            VALIDATION_FAILED=1
        fi

        # Verify SSL certificate is valid (catches expired certs before users do)
        if [[ "${APP_URL}" == https://* ]]; then
            CURL_SSL_ERR=$(curl -sS --head --max-time 10 "${APP_URL}" 2>&1 | grep -i "ssl\|certificate\|expired" || true)
            if [[ -n "${CURL_SSL_ERR}" ]]; then
                log_warn "  ✗ SSL issue detected: ${CURL_SSL_ERR}"
                VALIDATION_FAILED=1
            else
                log_ok "  ✓ SSL certificate valid"
            fi
        fi

        # Verify the compiled CSS asset is actually served by nginx
        # A 404 here means nginx root points to flat-clone public/ not current/public/
        if [[ -f "${CURRENT_LINK}/public/build/manifest.json" ]]; then
            MANIFEST_CSS=$(python3 -c "
import json
m=json.load(open('${CURRENT_LINK}/public/build/manifest.json'))
k=[k for k in m if 'app.css' in k]
print(m[k[0]]['file'] if k else '')
" 2>/dev/null || true)
            if [[ -n "${MANIFEST_CSS}" ]]; then
                CSS_URL="${APP_URL%/}/build/${MANIFEST_CSS}"
                CSS_HTTP=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${CSS_URL}" 2>/dev/null || echo "000")
                if [[ "${CSS_HTTP}" == "200" ]]; then
                    log_ok "  ✓ CSS asset reachable: ${CSS_HTTP} (${MANIFEST_CSS})"
                else
                    log_err "  ✗ CSS asset ${CSS_HTTP}: ${CSS_URL}"
                    log_err "    Root cause: nginx document root likely points to ${APP_DIR}/public not ${CURRENT_LINK}/public"
                    log_err "    Fix nginx config: root ${CURRENT_LINK}/public;"
                    VALIDATION_FAILED=1
                fi
            fi
        fi
    fi
fi

# Security headers (informational — non-blocking)
_SEC_URL=$(grep -E '^APP_URL=' "${SHARED_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | head -1 || echo "")
if [[ -n "${_SEC_URL}" ]] && command -v curl &>/dev/null; then
    _SEC_HDRS=$(curl -sI --max-time 10 "${_SEC_URL}" 2>/dev/null | tr '[:upper:]' '[:lower:]')
    for _hdr in "x-frame-options" "x-content-type-options" "x-xss-protection"; do
        if echo "${_SEC_HDRS}" | grep -q "^${_hdr}:"; then
            log_ok "  ✓ Security header: ${_hdr}"
        else
            log_warn "  ✗ Security header missing: ${_hdr} (add to nginx config)"
        fi
    done
    if [[ "${_SEC_URL}" == https://* ]] && echo "${_SEC_HDRS}" | grep -q "^strict-transport-security:"; then
        log_ok "  ✓ Security header: strict-transport-security (HSTS)"
    elif [[ "${_SEC_URL}" == https://* ]]; then
        log_warn "  ✗ Security header missing: strict-transport-security"
    fi
fi

# Verify critical processes are running
for svc_check in nginx "php.*fpm"; do
    if pgrep -f "${svc_check}" &>/dev/null; then
        log_ok "  ✓ Process running: ${svc_check}"
    else
        log_warn "  ✗ Process not found: ${svc_check}"
        VALIDATION_FAILED=1
    fi
done

# Scheduler: warn if crontab has no schedule:run entry
if crontab -l 2>/dev/null | grep -q "schedule:run"; then
    log_ok "  ✓ Laravel scheduler in crontab"
else
    log_warn "  ✗ Laravel scheduler NOT in crontab — jobs will not run"
    log_warn "    Add: * * * * * cd ${CURRENT_LINK} && ${PHP} artisan schedule:run >> /dev/null 2>&1"
fi

if [[ ${VALIDATION_FAILED} -ne 0 ]]; then
    log_warn "Some validation checks failed — review warnings above"
    log_warn "To rollback: bash deployment/deploy.sh --rollback"
fi

# =============================================================================
# PHASE 15 — CACHE WARMUP
# =============================================================================
step "Cache warmup"
_WARM_URL=$(grep -E '^APP_URL=' "${SHARED_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | head -1 || echo "")
if [[ -n "${_WARM_URL}" ]] && command -v curl &>/dev/null; then
    for _wurl in \
        "${_WARM_URL}" \
        "${_WARM_URL%/}/admin/dashboard" \
        "${_WARM_URL%/}/hall/bookings/calendar"; do
        _wcode=$(curl -sL -o /dev/null -w "%{http_code}" --max-time 15 "${_wurl}" 2>/dev/null || echo "000")
        log_info "Warmed ${_wcode}: ${_wurl}"
    done
    log_ok "Cache warmup complete"
else
    log_info "Cache warmup skipped (APP_URL not set or curl unavailable)"
fi

# =============================================================================
# PHASE 16 — PRUNE OLD RELEASES
# =============================================================================
step "Pruning old releases (keep ${KEEP_RELEASES} successful, ${KEEP_FAILED_RELEASES} failed)"
mapfile -t RELEASES_ALL < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null)
_PRUNE_SUCCESS=0; _PRUNE_FAILED=0
for _rel in "${RELEASES_ALL[@]}"; do
    _rel="${_rel%/}"
    [[ -d "${_rel}" ]] || continue
    if [[ -f "${_rel}/.deploy_failed" ]]; then
        _PRUNE_FAILED=$(( _PRUNE_FAILED + 1 ))
        if [[ ${_PRUNE_FAILED} -gt ${KEEP_FAILED_RELEASES} ]]; then
            log_info "Removing failed release: $(basename "${_rel}")"
            rm -rf "${_rel}"
        fi
    else
        _PRUNE_SUCCESS=$(( _PRUNE_SUCCESS + 1 ))
        if [[ ${_PRUNE_SUCCESS} -gt ${KEEP_RELEASES} ]]; then
            log_info "Removing old release: $(basename "${_rel}")"
            rm -rf "${_rel}"
        fi
    fi
done
log_ok "Retention complete (kept up to ${KEEP_RELEASES} successful, ${KEEP_FAILED_RELEASES} failed releases)"

# =============================================================================
# SUMMARY
# =============================================================================
_CURRENT_REAL=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "${RELEASE_DIR}")
_APP_URL=$(grep -E '^APP_URL=' "${SHARED_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | head -1 || echo "")
_BUILD_VERSION=$(cat "${_CURRENT_REAL}/public/build/version.txt" 2>/dev/null | head -c 12 || echo "n/a")
DEPLOY_END=$(date +%s)
DEPLOY_DURATION=$(( DEPLOY_END - DEPLOY_START ))

# Compute all phase durations
_T_COMPOSER=$(( T_COMPOSER_END - T_COMPOSER_START ))
_T_BUILD=$(( T_BUILD_END > 0 ? T_BUILD_END - T_BUILD_START : 0 ))
_T_NPM_CI=$(( T_NPM_CI_END > T_NPM_CI_START ? T_NPM_CI_END - T_NPM_CI_START : 0 ))
_T_NPM_BUILD=$(( T_NPM_BUILD_END > T_NPM_BUILD_START ? T_NPM_BUILD_END - T_NPM_BUILD_START : 0 ))
_T_MIGRATE=$(( T_MIGRATE_END - T_MIGRATE_START ))

# Build human-readable labels for cache decisions
_NM_LABEL="${NM_SKIP_REASON:-ran (${_T_NPM_CI}s)}"
_FE_LABEL="${FE_SKIP_REASON:-built (${_T_NPM_BUILD}s)}"
[[ "${_T_NPM_CI}" -eq 0 && -z "${NM_SKIP_REASON}" ]] && _NM_LABEL="skipped (no package.json)"
[[ "${_T_NPM_BUILD}" -eq 0 && -z "${FE_SKIP_REASON}" ]] && _FE_LABEL="skipped (no package.json)"

# Append to persistent deployment history
_DEPLOY_RESULT="success"; [[ ${VALIDATION_FAILED} -ne 0 ]] && _DEPLOY_RESULT="success_with_warnings"
_HISTORY_FILE="${SHARED_DIR}/storage/logs/deployments.log"
mkdir -p "$(dirname "${_HISTORY_FILE}")"
printf '%s | %-22s | %-20s | %s | %-12s | %-12s | %4ds | npm-ci:%-3s | vite:%-3s\n' \
    "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "${_DEPLOY_RESULT}" "${DEPLOY_ID}" \
    "${COMMIT:0:12}" "${BRANCH}" "$(whoami 2>/dev/null)" "${DEPLOY_DURATION}" \
    "$([ ${_T_NPM_CI} -gt 0 ] && echo "${_T_NPM_CI}s" || echo skip)" \
    "$([ ${_T_NPM_BUILD} -gt 0 ] && echo "${_T_NPM_BUILD}s" || echo skip)" \
    >> "${_HISTORY_FILE}" 2>/dev/null || true

echo ""
if [[ ${VALIDATION_FAILED} -ne 0 ]]; then
echo -e "${YELLOW}${BOLD}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${YELLOW}${BOLD}║     DEPLOYED — WITH WARNINGS                         ║${NC}"
echo -e "${YELLOW}${BOLD}╚══════════════════════════════════════════════════════╝${NC}"
else
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}${BOLD}║     DEPLOYMENT SUCCESSFUL ✓                          ║${NC}"
echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════════════╝${NC}"
fi
echo ""
echo -e "  ${BOLD}Deploy ID${NC}   ${DEPLOY_ID}"
echo -e "  ${BOLD}Commit${NC}      ${COMMIT:0:12}  (build: ${_BUILD_VERSION})"
echo -e "  ${BOLD}Branch${NC}      ${BRANCH}  |  mode: ${DEPLOY_MODE^^}"
echo -e "  ${BOLD}App dir${NC}     ${APP_DIR}"
echo -e "  ${BOLD}Live at${NC}     ${_CURRENT_REAL}"
[[ -n "${_APP_URL}" ]] && echo -e "  ${BOLD}URL${NC}         ${_APP_URL}"
echo ""
echo -e "  ${BOLD}Build${NC}       $(test -f "${_CURRENT_REAL}/public/build/manifest.json" && echo "${GREEN}manifest.json ✓${NC}" || echo "${RED}manifest.json MISSING${NC}")"
echo -e "  ${BOLD}Storage${NC}     $(test -L "${_CURRENT_REAL}/public/storage" && echo "${GREEN}symlink ✓${NC}" || echo "${YELLOW}symlink missing${NC}")"
echo -e "  ${BOLD}Config${NC}      $(test -f "${_CURRENT_REAL}/bootstrap/cache/config.php" && echo "${GREEN}cached ✓${NC}" || echo "${YELLOW}not cached${NC}")"
echo ""
echo -e "  ${CYAN}${BOLD}── Timing breakdown ─────────────────────────────────${NC}"
printf  "  ${BOLD}%-14s${NC} %s\n"  "Total"     "${DEPLOY_DURATION}s"
printf  "  ${BOLD}%-14s${NC} %s\n"  "Composer"  "${_T_COMPOSER}s$([ ${COMPOSER_SKIP:-0} -eq 1 ] && echo ' (hardlinks — lock unchanged)' || true)"
printf  "  ${BOLD}%-14s${NC} %s\n"  "npm ci"    "${_NM_LABEL}"
printf  "  ${BOLD}%-14s${NC} %s\n"  "Vite build" "${_FE_LABEL}"
printf  "  ${BOLD}%-14s${NC} %s\n"  "Migrations" "${_T_MIGRATE}s$([ ${SAFE_MODE} -eq 1 ] && echo ' (skipped — safe/frontend mode)' || true)"
echo -e "  ${CYAN}${BOLD}─────────────────────────────────────────────────────${NC}"
echo ""
echo -e "  ${BOLD}NM cache${NC}    ${SHARED_NM_CACHE}"
echo -e "  ${BOLD}Build cache${NC} ${SHARED_BUILD_CACHE}  (fp: ${FE_FINGERPRINT:0:12})"
echo -e "  ${BOLD}History${NC}     ${_HISTORY_FILE}"
echo -e "  ${BOLD}DB backup${NC}   ${DB_BACKUP_DIR}/pre-deploy-${TIMESTAMP}.sql.gz"
[[ ${VALIDATION_FAILED} -ne 0 ]] && echo -e "\n  ${YELLOW}${BOLD}Warnings detected — review output above.${NC}"
echo -e "  ${BOLD}Rollback${NC}    bash deployment/deploy.sh --rollback"
echo ""
