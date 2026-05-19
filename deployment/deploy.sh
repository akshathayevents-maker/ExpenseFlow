#!/usr/bin/env bash
# =============================================================================
# ExpenseFlow — Self-Healing Production Deploy Script
# Usage: bash deploy.sh [branch] [--rollback]
# =============================================================================
set -euo pipefail

# ── Static config ─────────────────────────────────────────────────────────────
BRANCH="${1:-main}"
REPO_URL="https://github.com/akshathayevents-maker/ExpenseFlow.git"
KEEP_RELEASES=5
SUPERVISOR_CONF="/etc/supervisor/conf.d/expenseflow-worker.conf"
SUPERVISOR_GROUP="expenseflow-worker"
TIMESTAMP=$(date +%Y%m%d%H%M%S)

# ── Logging ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

_ts()      { date '+%Y-%m-%d %H:%M:%S'; }
log_info() { echo -e "${BLUE}$(_ts) [INFO]${NC}    $*"; }
log_ok()   { echo -e "${GREEN}$(_ts) [SUCCESS]${NC} $*"; }
log_warn() { echo -e "${YELLOW}$(_ts) [WARN]${NC}    $*" >&2; }
log_err()  { echo -e "${RED}$(_ts) [ERROR]${NC}   $*" >&2; }
log_heal() { echo -e "${CYAN}$(_ts) [HEAL]${NC}    $*"; }
die()      { log_err "$*"; exit 1; }
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

# ── 1.7 Node.js check (non-fatal) ────────────────────────────────────────────
if command -v node &>/dev/null; then
    log_ok "Node.js: $(node --version)"
else
    log_warn "Node.js not found — frontend build will be skipped"
    log_warn "Install: curl -fsSL https://deb.nodesource.com/setup_20.x | sudo bash - && sudo apt-get install -y nodejs"
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

log_ok "Pre-deploy checks complete"

# =============================================================================
# PHASE 2 — ROLLBACK
# =============================================================================
if [[ "${1:-}" == "--rollback" ]]; then
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
git -C "${REPO_DIR}" fetch --all --prune
COMMIT=$(git -C "${REPO_DIR}" rev-parse "origin/${BRANCH}")
log_info "Deploying commit: ${COMMIT}"

step "Creating release: ${TIMESTAMP}"
mkdir -p "${RELEASE_DIR}"
git -C "${REPO_DIR}" --work-tree="${RELEASE_DIR}" checkout -f "origin/${BRANCH}"
log_ok "Code checked out to ${RELEASE_DIR}"

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
"${PHP}" "${COMPOSER}" install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --working-dir="${RELEASE_DIR}"
log_ok "Composer done"

# =============================================================================
# PHASE 6 — FRONTEND BUILD (with OOM protection + validation)
# =============================================================================
step "Building frontend assets"
if [[ -f "${RELEASE_DIR}/package.json" ]] && command -v node &>/dev/null; then
    cd "${RELEASE_DIR}"

    # Cap heap to avoid OOM-kill on low-memory VPS
    export NODE_OPTIONS="--max-old-space-size=512"

    log_info "Running npm ci..."
    if ! npm ci 2>&1; then
        die "npm ci failed — check package.json and package-lock.json are in sync"
    fi

    log_info "Running npm run build..."
    set +e
    npm run build 2>&1
    BUILD_EXIT=$?
    set -e

    if [[ ${BUILD_EXIT} -ne 0 ]]; then
        # Check if OOM-killed
        if dmesg 2>/dev/null | tail -20 | grep -qi "oom\|killed process\|out of memory"; then
            log_err "OOM kill detected during npm build. Available RAM:"
            free -h 2>/dev/null || true
            log_err "Fix: Add swap space or increase server RAM:"
            log_err "  sudo fallocate -l 2G /swapfile && sudo chmod 600 /swapfile"
            log_err "  sudo mkswap /swapfile && sudo swapon /swapfile"
        fi
        die "npm run build exited with code ${BUILD_EXIT}"
    fi

    # Hard abort if manifest missing — OOM-kill may exit 0 but leave incomplete build
    if [[ ! -f "${RELEASE_DIR}/public/build/manifest.json" ]]; then
        log_err "Build appeared to succeed but manifest.json is missing."
        log_err "Expected: ${RELEASE_DIR}/public/build/manifest.json"
        if [[ -d "${RELEASE_DIR}/public/build" ]]; then
            log_err "Contents of public/build/:"
            ls -la "${RELEASE_DIR}/public/build/" 2>/dev/null || true
        else
            log_err "public/build/ directory does not exist"
        fi
        # Check RAM
        log_err "Available RAM at time of check:"
        free -h 2>/dev/null || true
        die "Frontend build incomplete — manifest.json not generated. Aborting deploy."
    fi

    cd - >/dev/null
    log_ok "Frontend build done — manifest.json verified"
elif [[ ! -f "${RELEASE_DIR}/package.json" ]]; then
    log_warn "No package.json — skipping frontend build"
else
    log_warn "Node.js not available — skipping frontend build (install nodejs to enable)"
fi

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
# PHASE 8 — DATABASE MIGRATIONS
# =============================================================================
step "Running migrations"
"${PHP}" "${RELEASE_DIR}/artisan" migrate --force --no-interaction
log_ok "Migrations complete"

# =============================================================================
# PHASE 9 — ACTIVATE RELEASE
# =============================================================================
step "Activating release"
ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}"
log_ok "Current → ${TIMESTAMP}"

# =============================================================================
# PHASE 10 — LARAVEL OPTIMIZATIONS
# =============================================================================
step "Running post-deploy optimizations"
ARTISAN="${PHP} ${CURRENT_LINK}/artisan"

${ARTISAN} storage:link       2>/dev/null || true
${ARTISAN} config:cache       && log_ok "Config cached"
${ARTISAN} route:cache        && log_ok "Routes cached"
${ARTISAN} view:cache         && log_ok "Views cached"
${ARTISAN} event:cache        && log_ok "Events cached"
${ARTISAN} icons:cache        2>/dev/null || true

# =============================================================================
# PHASE 11 — MAINTENANCE MODE OFF
# =============================================================================
step "Disabling maintenance mode"
${ARTISAN} up
log_ok "Application online"

# =============================================================================
# PHASE 12 — SET PERMISSIONS
# =============================================================================
step "Setting permissions"
chmod -R 775 "${SHARED_DIR}/storage"
chmod -R 775 "${RELEASE_DIR}/bootstrap/cache"
safe_sudo chown -R www-data:www-data "${SHARED_DIR}/storage" 2>/dev/null || \
    log_warn "Could not chown storage — run: sudo chown -R www-data:www-data ${SHARED_DIR}/storage"
safe_sudo chown -R www-data:www-data "${RELEASE_DIR}/bootstrap/cache" 2>/dev/null || true
log_ok "Permissions set"

# =============================================================================
# PHASE 13 — RELOAD SERVICES
# =============================================================================
step "Reloading services"
reload_phpfpm
reload_nginx
restart_workers

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

# CSS asset file exists (derive from manifest)
if [[ -f "${CURRENT_LINK}/public/build/manifest.json" ]]; then
    CSS_FILE=$(python3 -c "
import json,sys
m=json.load(open('${CURRENT_LINK}/public/build/manifest.json'))
k=[k for k in m if 'app.css' in k]
print(m[k[0]]['file'] if k else '')
" 2>/dev/null || true)
    if [[ -n "${CSS_FILE}" ]]; then
        _validate "CSS asset exists (${CSS_FILE})" \
            test -f "${CURRENT_LINK}/public/build/${CSS_FILE}"
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
        HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${APP_URL}" 2>/dev/null || echo "000")
        if [[ "${HTTP_CODE}" == "200" || "${HTTP_CODE}" == "302" ]]; then
            log_ok "  ✓ HTTP response: ${HTTP_CODE} (${APP_URL})"
        else
            log_warn "  ✗ HTTP response: ${HTTP_CODE} (${APP_URL}) — nginx may not be serving correctly"
            VALIDATION_FAILED=1
        fi
    fi
fi

if [[ ${VALIDATION_FAILED} -ne 0 ]]; then
    log_warn "Some validation checks failed — review warnings above"
    log_warn "To rollback: bash deployment/deploy.sh --rollback"
fi

# =============================================================================
# PHASE 15 — PRUNE OLD RELEASES
# =============================================================================
step "Pruning old releases (keeping ${KEEP_RELEASES})"
mapfile -t RELEASES_ALL < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null)
RELEASES_COUNT=${#RELEASES_ALL[@]}
if [[ ${RELEASES_COUNT} -gt ${KEEP_RELEASES} ]]; then
    TO_DELETE=("${RELEASES_ALL[@]:${KEEP_RELEASES}}")
    for OLD in "${TO_DELETE[@]}"; do
        log_info "Removing: ${OLD%/}"
        rm -rf "${OLD%/}"
    done
fi
log_ok "Kept ${KEEP_RELEASES} most recent releases"

# =============================================================================
# SUMMARY
# =============================================================================
echo ""
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════╗${NC}"
echo -e "${GREEN}${BOLD}║       DEPLOYMENT SUCCESSFUL          ║${NC}"
echo -e "${GREEN}${BOLD}╠══════════════════════════════════════╣${NC}"
echo -e "${GREEN}${BOLD}║${NC}  Release : ${TIMESTAMP}          ${GREEN}${BOLD}║${NC}"
echo -e "${GREEN}${BOLD}║${NC}  Commit  : ${COMMIT:0:12}                    ${GREEN}${BOLD}║${NC}"
echo -e "${GREEN}${BOLD}║${NC}  Branch  : ${BRANCH}                    ${GREEN}${BOLD}║${NC}"
echo -e "${GREEN}${BOLD}║${NC}  App     : ${APP_DIR}     ${GREEN}${BOLD}║${NC}"
if [[ ${VALIDATION_FAILED} -ne 0 ]]; then
echo -e "${YELLOW}${BOLD}║${NC}  Status  : DEPLOYED WITH WARNINGS     ${YELLOW}${BOLD}║${NC}"
else
echo -e "${GREEN}${BOLD}║${NC}  Status  : ALL CHECKS PASSED          ${GREEN}${BOLD}║${NC}"
fi
echo -e "${GREEN}${BOLD}╚══════════════════════════════════════╝${NC}"
