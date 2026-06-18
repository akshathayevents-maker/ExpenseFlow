#!/usr/bin/env bash
# ExpenseFlow production deploy
# Usage: bash deploy.sh [branch] [--rollback] [--safe] [--backend] [--hotfix] [--force-migrate]
set -Eeuo pipefail

# ── Config ────────────────────────────────────────────────────────────────────
BRANCH="main"
SAFE_MODE=0; ROLLBACK_MODE=0; DEPLOY_MODE="full"; FORCE_MIGRATE=0
for _a in "$@"; do
    case "${_a}" in
        --rollback)      ROLLBACK_MODE=1 ;;
        --safe)          SAFE_MODE=1 ;;
        --hotfix)        DEPLOY_MODE="hotfix" ;;
        --mode=*)        DEPLOY_MODE="${_a#--mode=}" ;;
        --force-migrate) FORCE_MIGRATE=1 ;;
        -*)              echo "[WARN] Unknown flag: ${_a}" >&2 ;;
        *)               BRANCH="${_a}" ;;
    esac
done
case "${DEPLOY_MODE}" in
    full|hotfix) ;;
    *) echo "[ERROR] Invalid --mode '${DEPLOY_MODE}'. Valid: full|hotfix" >&2; exit 1 ;;
esac
[[ "${DEPLOY_MODE}" == "hotfix" ]] && SAFE_MODE=1

REPO_URL="https://github.com/akshathayevents-maker/ExpenseFlow.git"
KEEP_RELEASES=5; KEEP_FAILED_RELEASES=3
SUPERVISOR_CONF="/etc/supervisor/conf.d/expenseflow-worker.conf"
SUPERVISOR_GROUP="expenseflow-worker"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
LOCK_FILE="/tmp/expenseflow_deploy.lock"
DEPLOY_START=$(date +%s)
T_COMPOSER_START=0; T_COMPOSER_END=0; T_MIGRATE_START=0; T_MIGRATE_END=0
export PATH="/usr/local/bin:/usr/bin:/bin:${PATH:-}"

# ── Logging ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BOLD='\033[1m'; NC='\033[0m'
_ts()      { date '+%H:%M:%S'; }
log_info() { echo -e "$(_ts) [INFO]    $*"; }
log_ok()   { echo -e "${GREEN}$(_ts) [SUCCESS]${NC} $*"; }
log_warn() { echo -e "${YELLOW}$(_ts) [WARN]${NC}    $*" >&2; }
log_err()  { echo -e "${RED}$(_ts) [ERROR]${NC}   $*" >&2; }
die()      { log_err "$*"; exit 1; }
die_class(){ local cls="$1"; shift; log_err "[${cls}] $*"; exit 1; }

safe_sudo() { [[ $EUID -eq 0 ]] && "$@" || sudo "$@"; }

# Read a single value from shared .env (requires SHARED_DIR to be set)
read_env() { grep -E "^${1}=" "${SHARED_DIR}/.env" 2>/dev/null | cut -d= -f2- | tr -d "\"'" | xargs 2>/dev/null || true; }

# ── Auto-detect: app directory ────────────────────────────────────────────────
detect_app_dir() {
    [[ -n "${APP_DIR_OVERRIDE:-}" && -d "${APP_DIR_OVERRIDE}" ]] && { echo "${APP_DIR_OVERRIDE}"; return; }
    local candidates=("/var/www/expenseflow" "/var/www/akshathayexpense" "/var/www/html/expenseflow" "/home/expenseflow")
    for d in "${candidates[@]}"; do [[ -d "${d}" ]] && { echo "${d}"; return; }; done
    local script_parent; script_parent="$(dirname "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)")"
    [[ -f "${script_parent}/artisan" ]] && { echo "${script_parent}"; return; }
    [[ -f "$(pwd)/artisan" ]] && { echo "$(pwd)"; return; }
    echo ""
}

# ── Auto-detect: PHP binary ───────────────────────────────────────────────────
detect_php() {
    [[ -n "${PHP_BIN:-}" ]] && command -v "${PHP_BIN}" &>/dev/null && { echo "${PHP_BIN}"; return; }
    for b in "php8.3" "php8.2" "php8.1" "php8.0" "php"; do
        command -v "${b}" &>/dev/null && { echo "${b}"; return; }
    done
    echo ""
}

# ── Auto-detect: Composer binary ─────────────────────────────────────────────
detect_composer() {
    [[ -n "${COMPOSER_BIN:-}" ]] && command -v "${COMPOSER_BIN}" &>/dev/null && { echo "${COMPOSER_BIN}"; return; }
    local candidates=("/usr/local/bin/composer" "/usr/bin/composer" "${HOME}/.composer/vendor/bin/composer" "${HOME}/composer.phar" "$(command -v composer 2>/dev/null || true)")
    for b in "${candidates[@]}"; do [[ -n "${b}" && -x "${b}" ]] && { echo "${b}"; return; }; done
    echo ""
}

# ── Auto-detect: php-fpm service ─────────────────────────────────────────────
detect_phpfpm_service() {
    for svc in "php8.3-fpm" "php8.2-fpm" "php8.1-fpm" "php8.0-fpm" "php-fpm"; do
        systemctl list-units --type=service --all 2>/dev/null | grep -q "^  ${svc}.service" && { echo "${svc}"; return; }
    done
    command -v "${PHP:-php}" &>/dev/null && echo "php$("${PHP:-php}" -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')-fpm" && return
    echo ""
}

# ── Heal: install Composer ────────────────────────────────────────────────────
heal_composer() {
    log_warn "Composer not found — attempting auto-install"
    local _php; _php=$(detect_php)
    [[ -z "${_php}" ]] && die "PHP not found. Install: sudo apt-get install php8.3-cli"
    local tmp; tmp=$(mktemp /tmp/composer-installer-XXXX.php)
    if command -v curl &>/dev/null; then curl -fsSL https://getcomposer.org/installer -o "${tmp}"
    elif command -v wget &>/dev/null; then wget -qO "${tmp}" https://getcomposer.org/installer
    else die "Neither curl nor wget available — install Composer manually"
    fi
    "${_php}" "${tmp}" --install-dir=/usr/local/bin --filename=composer 2>&1 | grep -v "^All settings" || true
    rm -f "${tmp}"
    command -v composer &>/dev/null || die "Composer install failed. See: https://getcomposer.org/download/"
}

# ── Heal: app directory structure ─────────────────────────────────────────────
heal_app_dir() {
    local app_dir="$1"
    log_warn "App directory missing — creating ${app_dir}"
    safe_sudo mkdir -p "${app_dir}"/{releases,shared,repo}
    safe_sudo mkdir -p "${app_dir}/shared/storage"/{app/public,framework/{cache,sessions,views},logs}
    safe_sudo mkdir -p "${app_dir}/shared/public"
    safe_sudo chown -R "$(whoami):$(whoami)" "${app_dir}" 2>/dev/null || true
}

# ── Heal: clone bare repo ─────────────────────────────────────────────────────
heal_repo() {
    local repo_dir="$1"
    log_warn "Bare repo missing — cloning ${REPO_URL}"
    [[ -z "${REPO_URL}" ]] && die "REPO_URL not set"
    command -v git &>/dev/null || safe_sudo apt-get install -y git &>/dev/null || die "Cannot install git"
    mkdir -p "$(dirname "${repo_dir}")"
    git clone --bare "${REPO_URL}" "${repo_dir}"
}

# ── Heal: locate and place .env ───────────────────────────────────────────────
heal_env() {
    local shared_env="$1" app_dir="$2" current_link="$3"
    [[ -f "${shared_env}" ]] && return 0
    log_warn ".env missing at ${shared_env} — searching common locations"
    local search_locations=("${app_dir}/.env" "${current_link}/.env" "${app_dir}/current/.env"
        "/var/www/expenseflow/.env" "/var/www/akshathayexpense/.env"
        "$(pwd)/.env" "$(dirname "${BASH_SOURCE[0]}")/../.env")
    for loc in "${search_locations[@]}"; do
        local real_loc; real_loc="$(realpath "${loc}" 2>/dev/null || echo "")"
        if [[ -n "${real_loc}" && -f "${real_loc}" && "${real_loc}" != "${shared_env}" ]]; then
            mkdir -p "$(dirname "${shared_env}")"
            cp "${real_loc}" "${shared_env}"
            log_ok ".env copied from ${real_loc}"
            return 0
        fi
    done
    log_err ".env not found. Fix: scp .env user@server:${shared_env}"
    die ".env required — deployment aborted"
}

# ── Heal: storage directories ─────────────────────────────────────────────────
heal_storage() {
    local s="$1"
    for d in "${s}/app/public" "${s}/framework/cache/data" "${s}/framework/sessions" "${s}/framework/views" "${s}/logs"; do
        mkdir -p "${d}"
    done
    chmod -R 775 "${s}"
    safe_sudo chown -R www-data:www-data "${s}" 2>/dev/null || chown -R "$(whoami):$(whoami)" "${s}" 2>/dev/null || true
    for d in "${s}/framework/cache" "${s}/framework/sessions" "${s}/framework/views" "${s}/logs"; do
        [[ -f "${d}/.gitignore" ]] || printf '*\n!.gitignore\n' > "${d}/.gitignore"
    done
}

# ── Reload php-fpm ────────────────────────────────────────────────────────────
reload_phpfpm() {
    local svc; svc=$(detect_phpfpm_service)
    [[ -z "${svc}" ]] && { log_warn "php-fpm service not detected — skip reload"; return; }
    if systemctl is-active --quiet "${svc}" 2>/dev/null; then
        safe_sudo systemctl reload "${svc}" \
            || log_warn "php-fpm reload failed (${svc}) — workers may use stale opcache"
    else
        log_warn "php-fpm (${svc}) not active — skip reload"
    fi
}

# ── Reload nginx ─────────────────────────────────────────────────────────────
reload_nginx() {
    command -v nginx &>/dev/null || return
    systemctl is-active --quiet nginx 2>/dev/null || { log_warn "nginx not running — skip reload"; return; }
    if nginx -t -q 2>/dev/null; then
        safe_sudo systemctl reload nginx || log_warn "nginx reload failed"
    else
        log_warn "nginx config test failed — skip reload to avoid outage"
    fi
}

# ── Ensure supervisor worker config ───────────────────────────────────────────
ensure_supervisor_conf() {
    [[ -f "${SUPERVISOR_CONF}" ]] && return
    log_warn "Supervisor config missing — creating ${SUPERVISOR_CONF}"
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
}

# ── Restart supervisor workers ────────────────────────────────────────────────
restart_workers() {
    command -v supervisorctl &>/dev/null || { log_warn "supervisorctl not installed — skip"; return; }
    systemctl is-active --quiet supervisor 2>/dev/null || \
        safe_sudo systemctl start supervisor 2>/dev/null || { log_warn "Cannot start supervisor"; return; }
    ensure_supervisor_conf
    safe_sudo supervisorctl reread 2>/dev/null || true
    safe_sudo supervisorctl update 2>/dev/null || true
    if safe_sudo supervisorctl status "${SUPERVISOR_GROUP}:" &>/dev/null 2>&1; then
        safe_sudo supervisorctl restart "${SUPERVISOR_GROUP}:" \
            && log_ok "Queue workers restarted" && return
    fi
    while IFS= read -r prog; do
        safe_sudo supervisorctl restart "${prog}" &>/dev/null 2>&1 && log_ok "Restarted: ${prog}"
    done < <(safe_sudo supervisorctl status 2>/dev/null | grep "${SUPERVISOR_GROUP}" | awk '{print $1}')
}

# ── Auto-rollback trap ────────────────────────────────────────────────────────
# On failure: restores previous release symlink only.
# DATABASE MIGRATIONS ARE FORWARD-ONLY IN PRODUCTION.
# Database state is NEVER automatically reverted — only the application release
# is rolled back. If a migration partially applied, investigate manually via
# php artisan migrate:status before attempting re-deploy.
DEPLOY_PREVIOUS_RELEASE=""
cleanup_on_failure() {
    local exit_code=$?
    rm -f "${LOCK_FILE}" 2>/dev/null || true
    [[ ${exit_code} -eq 0 ]] && return
    log_err "Deploy failed (exit ${exit_code}) — rolling back application release (DB unchanged)"
    [[ -d "${RELEASE_DIR:-}" ]] && touch "${RELEASE_DIR}/.deploy_failed" 2>/dev/null || true
    local log_file="${SHARED_DIR:-}/storage/logs/laravel.log"
    [[ -f "${log_file}" ]] && { log_err "── Last 30 lines laravel.log ──"; tail -n 30 "${log_file}" >&2 || true; }
    if [[ -n "${DEPLOY_PREVIOUS_RELEASE}" && -d "${DEPLOY_PREVIOUS_RELEASE}" ]]; then
        "${PHP:-php}" "${DEPLOY_PREVIOUS_RELEASE}/artisan" down 2>/dev/null || true
        ln -sfn "${DEPLOY_PREVIOUS_RELEASE}" "${CURRENT_LINK}" 2>/dev/null || true
        "${PHP:-php}" "${CURRENT_LINK}/artisan" up 2>/dev/null || true
        reload_phpfpm 2>/dev/null || true
        reload_nginx  2>/dev/null || true
        log_ok "Release rolled back → $(basename "${DEPLOY_PREVIOUS_RELEASE}") (database state preserved)"
    else
        log_warn "No previous release available — manual intervention required"
    fi
}
trap cleanup_on_failure EXIT

# =============================================================================
# PHASE 0 — RESOLVE ENVIRONMENT
# =============================================================================
PHP=$(detect_php)
[[ -z "${PHP}" ]] && die "PHP not found. Install: sudo apt-get install php8.3-cli php8.3-fpm php8.3-mbstring php8.3-xml php8.3-curl php8.3-pgsql php8.3-zip"

COMPOSER=$(detect_composer)
if [[ -z "${COMPOSER}" ]]; then
    heal_composer
    COMPOSER=$(detect_composer)
    [[ -z "${COMPOSER}" ]] && die "Composer not found after install attempt"
fi

APP_DIR=$(detect_app_dir)
[[ -z "${APP_DIR}" ]] && { APP_DIR="/var/www/expenseflow"; log_warn "Could not detect app dir — defaulting to ${APP_DIR}"; }

REPO_DIR="${APP_DIR}/repo"
RELEASES_DIR="${APP_DIR}/releases"
SHARED_DIR="${APP_DIR}/shared"
CURRENT_LINK="${APP_DIR}/current"
RELEASE_DIR="${RELEASES_DIR}/${TIMESTAMP}"

# Acquire deploy lock — prevent concurrent deploys
exec 200>"${LOCK_FILE}"
flock -n 200 2>/dev/null || die "Another deployment is running. Remove ${LOCK_FILE} to force."
echo $$ > "${LOCK_FILE}"

# Tee all output to timestamped log file
DEPLOY_LOG_DIR="${APP_DIR}/deploy_logs"
mkdir -p "${DEPLOY_LOG_DIR}" 2>/dev/null || true
DEPLOY_LOG_FILE="${DEPLOY_LOG_DIR}/deploy_${TIMESTAMP}.log"
exec > >(tee -a "${DEPLOY_LOG_FILE}") 2>&1
log_info "Deploy started — mode: ${DEPLOY_MODE}  branch: ${BRANCH}  log: ${DEPLOY_LOG_FILE}"

# =============================================================================
# PHASE 1 — SELF-HEALING PRE-DEPLOY CHECKS
# =============================================================================
[[ ! -d "${APP_DIR}" ]] && heal_app_dir "${APP_DIR}"
for _d in "${RELEASES_DIR}" "${SHARED_DIR}" "${SHARED_DIR}/storage"; do
    [[ ! -d "${_d}" ]] && mkdir -p "${_d}"
done
[[ ! -d "${REPO_DIR}" ]] && heal_repo "${REPO_DIR}"
heal_env "${SHARED_DIR}/.env" "${APP_DIR}" "${CURRENT_LINK}"
if [[ ! -d "${SHARED_DIR}/storage/framework/views" || ! -d "${SHARED_DIR}/storage/logs" || ! -w "${SHARED_DIR}/storage" ]]; then
    heal_storage "${SHARED_DIR}/storage"
fi
mkdir -p "${SHARED_DIR}/public/storage"

# Disk space
DISK_FREE_MB=$(( $(df -k "${APP_DIR}" 2>/dev/null | awk 'NR==2{print $4}' || echo 0) / 1024 ))
[[ "${DISK_FREE_MB}" -lt 256 ]] && die "Critically low disk space (${DISK_FREE_MB}MB)"

# Required .env variables
ENV_MISSING=0
for _v in APP_KEY APP_URL DB_DATABASE DB_USERNAME DB_PASSWORD; do
    [[ -z "$(read_env "${_v}")" ]] && { log_err ".env missing: ${_v}"; ENV_MISSING=1; }
done
[[ ${ENV_MISSING} -ne 0 ]] && die "Required .env variables missing — edit ${SHARED_DIR}/.env"

# Stale symlink warning
if [[ -L "${CURRENT_LINK}" ]]; then
    _STALE=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")
    [[ -z "${_STALE}" || ! -d "${_STALE}" ]] && \
        log_warn "Current symlink is stale (→ $(readlink "${CURRENT_LINK}")) — will be fixed by this deploy"
fi

# Snapshot current release for auto-rollback trap and log scanning
DEPLOY_PREVIOUS_RELEASE=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")
_LOG_FILE_SCAN="${SHARED_DIR}/storage/logs/laravel.log"
_LOG_PRE_SIZE=$(wc -c < "${_LOG_FILE_SCAN}" 2>/dev/null || echo 0)

# =============================================================================
# PHASE 2 — ROLLBACK
# =============================================================================
if [[ ${ROLLBACK_MODE} -eq 1 ]]; then
    mapfile -t _RELS < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null | head -5)
    [[ ${#_RELS[@]} -lt 2 ]] && die "No previous release to roll back to"
    PREV="${_RELS[1]%/}"
    log_info "Rolling back to $(basename "${PREV}")"
    ln -sfn "${PREV}" "${CURRENT_LINK}"
    reload_phpfpm; reload_nginx; restart_workers
    log_ok "Rollback complete → $(basename "${PREV}")"
    exit 0
fi

# =============================================================================
# PHASE 3 — FETCH AND CHECKOUT CODE
# =============================================================================
log_info "Fetching code (${BRANCH})"
git -C "${REPO_DIR}" fetch --prune origin "${BRANCH}"
COMMIT=$(git -C "${REPO_DIR}" rev-parse FETCH_HEAD)
DEPLOY_ID="${TIMESTAMP}-${COMMIT:0:8}"
log_info "Commit: ${COMMIT:0:12}  deploy_id: ${DEPLOY_ID}"

mkdir -p "${RELEASE_DIR}"
git -C "${REPO_DIR}" --work-tree="${RELEASE_DIR}" checkout -f FETCH_HEAD

cat > "${RELEASE_DIR}/release.json" << RELEOF
{
  "deploy_id":   "${DEPLOY_ID}",
  "timestamp":   "${TIMESTAMP}",
  "commit":      "${COMMIT}",
  "branch":      "${BRANCH}",
  "safe_mode":   ${SAFE_MODE},
  "hostname":    "$(hostname -f 2>/dev/null || hostname)",
  "deploy_user": "$(whoami 2>/dev/null)",
  "php_version": "$(${PHP} -r 'echo PHP_VERSION;' 2>/dev/null || echo n/a)"
}
RELEOF

# =============================================================================
# PHASE 4 — LINK SHARED RESOURCES
# =============================================================================
rm -rf "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/storage"        "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/public/storage" "${RELEASE_DIR}/public/storage"
ln -sfn "${SHARED_DIR}/.env"           "${RELEASE_DIR}/.env"
mkdir -p "${RELEASE_DIR}/bootstrap/cache"
chmod -R 775 "${RELEASE_DIR}/bootstrap/cache"

# =============================================================================
# PHASE 5 — PHP DEPENDENCIES
# =============================================================================
T_COMPOSER_START=$(date +%s)
COMPOSER_SKIP=0

# Fast path: hardlink vendor when composer.lock is identical
if [[ ${COMPOSER_SKIP} -eq 0 && -n "${DEPLOY_PREVIOUS_RELEASE}" && -d "${DEPLOY_PREVIOUS_RELEASE}/vendor" ]]; then
    LOCK_HASH_NEW=$(md5sum "${RELEASE_DIR}/composer.lock"            2>/dev/null | awk '{print $1}')
    LOCK_HASH_OLD=$(md5sum "${DEPLOY_PREVIOUS_RELEASE}/composer.lock" 2>/dev/null | awk '{print $1}')
    if [[ -n "${LOCK_HASH_NEW}" && "${LOCK_HASH_NEW}" == "${LOCK_HASH_OLD}" ]]; then
        cp -a "${DEPLOY_PREVIOUS_RELEASE}/vendor" "${RELEASE_DIR}/vendor"
        COMPOSER_SKIP=1
        log_info "composer.lock unchanged — vendor copied via hardlinks"
    fi
fi

if [[ ${COMPOSER_SKIP} -eq 0 ]]; then
    log_info "Installing composer dependencies"
    "${PHP}" "${COMPOSER}" install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
        --working-dir="${RELEASE_DIR}" \
        || die_class "COMPOSER_FAILURE" "composer install failed — check composer.json constraints"
fi
T_COMPOSER_END=$(date +%s)
log_ok "Composer done ($((T_COMPOSER_END - T_COMPOSER_START))s)"

# =============================================================================
# PHASE 6 — VALIDATE PREBUILT FRONTEND ASSETS
# Frontend assets are built locally and committed — no Node on production server.
# =============================================================================
if [[ ! -f "${RELEASE_DIR}/public/build/manifest.json" ]]; then
    log_err "Frontend assets missing. Fix: npm run build && git add public/build && git commit && git push"
    die "Prebuilt assets missing — build locally and commit before deploying"
fi
[[ ! -d "${RELEASE_DIR}/public/build/assets" ]] && die "public/build/assets/ missing — incomplete build"
ASSET_COUNT=$(find "${RELEASE_DIR}/public/build/assets" -type f 2>/dev/null | wc -l)
[[ ${ASSET_COUNT} -lt 1 ]] && die "No assets found in public/build/assets/ — build appears empty"

if command -v jq &>/dev/null; then
    jq empty "${RELEASE_DIR}/public/build/manifest.json" 2>/dev/null || die "manifest.json invalid JSON"
    jq -e "[ keys[] ] | any(. == \"resources/js/app.js\")" \
        "${RELEASE_DIR}/public/build/manifest.json" >/dev/null 2>&1 \
        || die "manifest.json missing resources/js/app.js"
    jq -e "[ keys[] ] | any(startswith(\"resources/css/app\"))" \
        "${RELEASE_DIR}/public/build/manifest.json" >/dev/null 2>&1 \
        || die "manifest.json missing resources/css/app.css"
else
    grep -q "resources/js/app.js"  "${RELEASE_DIR}/public/build/manifest.json" || die "manifest.json missing app.js"
    grep -q "resources/css/app"    "${RELEASE_DIR}/public/build/manifest.json" || die "manifest.json missing app.css"
fi

echo "${COMMIT}" > "${RELEASE_DIR}/public/build/version.txt"
log_info "Assets validated (${ASSET_COUNT} files)"

# Copy to flat-clone location for nginx configs that serve APP_DIR/public directly
_APP_PUB="$(realpath "${APP_DIR}/public"     2>/dev/null || echo "")"
_REL_PUB="$(realpath "${RELEASE_DIR}/public" 2>/dev/null || echo "")"
if [[ -n "${_APP_PUB}" && "${_APP_PUB}" != "${_REL_PUB}" ]]; then
    rm -rf "${APP_DIR}/public/build"
    cp -r "${RELEASE_DIR}/public/build" "${APP_DIR}/public/build"
fi

# =============================================================================
# PHASE 6c — PWA SERVICE WORKER CACHE VERSION
# Bumping CACHE_VERSION forces existing clients to discard stale offline caches.
#
# IMPORTANT: This only works if public/sw.js is a hand-authored source file
# committed to git (not generated by Vite). If sw.js is a Vite output, the
# generated hash in public/build/ is what nginx serves — this sed has no effect
# on that file and should be removed. Check your nginx root and Vite config to
# confirm which case applies before enabling this in production.
# =============================================================================
SW_FILE="${RELEASE_DIR}/public/sw.js"
if [[ -f "${SW_FILE}" ]]; then
    NEW_CACHE_VERSION="ef-v${TIMESTAMP}"
    sed -i "s/const CACHE_VERSION\s*=\s*['\"][^'\"]*['\"]/const CACHE_VERSION = '${NEW_CACHE_VERSION}'/" "${SW_FILE}"
    grep -q "const CACHE_VERSION = '${NEW_CACHE_VERSION}'" "${SW_FILE}" \
        || log_warn "SW CACHE_VERSION bump failed — stale PWA caches will not be invalidated"
    # Sync to flat-clone copy if nginx serves APP_DIR/public rather than current/public
    _APP_PUB2="$(realpath "${APP_DIR}/public"     2>/dev/null || echo "")"
    _REL_PUB2="$(realpath "${RELEASE_DIR}/public" 2>/dev/null || echo "")"
    FLAT_SW="${APP_DIR}/public/sw.js"
    if [[ -f "${FLAT_SW}" && "${_APP_PUB2}" != "${_REL_PUB2}" ]]; then
        cp "${SW_FILE}" "${FLAT_SW}"
    fi
fi

# =============================================================================
# PHASE 6b — ARTISAN BOOT CHECK (before maintenance mode)
# artisan about boots the full app — catches broken providers before traffic is affected.
# =============================================================================
"${PHP}" "${RELEASE_DIR}/artisan" about --no-interaction 2>&1 \
    || die_class "BOOT_FAILURE" "artisan about failed on new release — broken provider/syntax/binding. Aborted before maintenance mode."

# =============================================================================
# PHASE 7 — MAINTENANCE MODE ON
# =============================================================================
CURRENT_ACTIVE=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")
if [[ -n "${CURRENT_ACTIVE}" && -d "${CURRENT_ACTIVE}" ]]; then
    "${PHP}" "${CURRENT_ACTIVE}/artisan" down \
        --render="errors.503" --retry=30 --refresh=15 \
        --secret="$(openssl rand -hex 16)" 2>/dev/null \
        || log_warn "Could not enable maintenance mode on current release"
fi

# =============================================================================
# PHASE 8a — DATABASE BACKUP
# =============================================================================
DB_BACKUP_DIR="${SHARED_DIR}/storage/backups/db"
mkdir -p "${DB_BACKUP_DIR}"
if command -v pg_dump &>/dev/null; then
    _DB_NAME=$(read_env DB_DATABASE); _DB_USER=$(read_env DB_USERNAME)
    _DB_HOST=$(read_env DB_HOST);     _DB_PORT=$(read_env DB_PORT)
    _DB_PASS=$(read_env DB_PASSWORD)
    BACKUP_FILE="${DB_BACKUP_DIR}/pre-deploy-${TIMESTAMP}.sql.gz"
    set +e
    PGPASSWORD="${_DB_PASS}" pg_dump \
        -h "${_DB_HOST:-127.0.0.1}" -p "${_DB_PORT:-5432}" -U "${_DB_USER}" -d "${_DB_NAME}" \
        --no-owner --no-acl 2>/dev/null | gzip > "${BACKUP_FILE}"
    BACKUP_EXIT=$?
    set -e
    if [[ ${BACKUP_EXIT} -eq 0 && -s "${BACKUP_FILE}" ]]; then
        _BACKUP_SIZE=$(du -sh "${BACKUP_FILE}" 2>/dev/null | cut -f1)
        log_ok "DB backup: ${BACKUP_FILE} (${_BACKUP_SIZE})"
        ls -1t "${DB_BACKUP_DIR}"/pre-deploy-*.sql.gz 2>/dev/null | tail -n +4 | xargs rm -f 2>/dev/null || true
    else
        rm -f "${BACKUP_FILE}" 2>/dev/null || true
        log_warn "DB backup failed — proceeding without backup (check pg_dump permissions)"
    fi
else
    log_warn "pg_dump not found — skipping DB backup. Install: sudo apt-get install postgresql-client"
fi

# =============================================================================
# PHASE 8 — DATABASE MIGRATIONS
# Forward-only: only `migrate --force` is permitted. No rollback, reset, fresh,
# or seed commands are ever run. Database state is treated as immutable production
# data. If a migration fails, only the application release is rolled back — the
# database is left as-is for manual investigation.
# --isolated acquires a DB-level lock preventing concurrent migration runs.
# Escape hatch: --force-migrate runs migrations even in safe/hotfix mode.
# =============================================================================

# Verify DB is reachable before entering maintenance mode with schema changes
if ! "${PHP}" -r "
    \$env = parse_ini_file('${SHARED_DIR}/.env');
    \$dsn = 'pgsql:host=' . (\$env['DB_HOST'] ?? '127.0.0.1') . ';port=' . (\$env['DB_PORT'] ?? 5432) . ';dbname=' . (\$env['DB_DATABASE'] ?? '');
    try { new PDO(\$dsn, \$env['DB_USERNAME'] ?? '', \$env['DB_PASSWORD'] ?? ''); echo 'ok'; }
    catch(Exception \$e) { echo 'fail'; exit(1); }
" 2>/dev/null | grep -q "^ok"; then
    die "DB not reachable — aborting before migration. Verify DB_* in ${SHARED_DIR}/.env"
fi

MIGRATE_STATUS_OUTPUT=$("${PHP}" "${RELEASE_DIR}/artisan" migrate:status --no-interaction 2>/dev/null || true)
PENDING_COUNT=$(echo "${MIGRATE_STATUS_OUTPUT}" | grep -c "Pending" || true)

_SKIP_MIGRATE=0
[[ ${SAFE_MODE} -eq 1 ]] && _SKIP_MIGRATE=1
[[ ${FORCE_MIGRATE} -eq 1 && ${_SKIP_MIGRATE} -eq 1 ]] && {
    _SKIP_MIGRATE=0
    log_warn "--force-migrate: running migrations in safe/hotfix mode"
}

if [[ ${_SKIP_MIGRATE} -eq 1 ]]; then
    if [[ "${PENDING_COUNT:-0}" -gt 0 ]]; then
        echo "${MIGRATE_STATUS_OUTPUT}" | grep "Pending" | while IFS= read -r line; do log_err "PENDING: ${line}"; done
        die_class "SCHEMA_MISMATCH" "${PENDING_COUNT} pending migration(s) in ${DEPLOY_MODE}/safe mode. Options: (1) bash deploy.sh ${BRANCH}  (2) --force-migrate  (3) php artisan migrate --force on server first"
    fi
    log_info "safe/hotfix mode — no pending migrations, schema aligned"
else
    log_info "Running migrations (${PENDING_COUNT} pending)"
    T_MIGRATE_START=$(date +%s)
    "${PHP}" "${RELEASE_DIR}/artisan" migrate --force --no-interaction --isolated 2>&1 \
        | while IFS= read -r line; do echo "  migrate: ${line}"; done
    MIGRATE_EXIT=${PIPESTATUS[0]}
    T_MIGRATE_END=$(date +%s)
    # On failure: release rolls back, database does NOT. See migration comment above.
    [[ ${MIGRATE_EXIT} -ne 0 ]] && \
        die_class "DB_FAILURE" "Migrations failed (exit ${MIGRATE_EXIT}) — release rolled back, database preserved. Check: php artisan migrate:status"
    log_ok "Migrations complete ($((T_MIGRATE_END - T_MIGRATE_START))s)"

    # Post-migration: confirm zero pending remain
    STILL_PENDING=$("${PHP}" "${RELEASE_DIR}/artisan" migrate:status --no-interaction 2>/dev/null | grep -c "Pending" || true)
    [[ "${STILL_PENDING:-0}" -gt 0 ]] && \
        die_class "SCHEMA_MISMATCH" "${STILL_PENDING} migration(s) still pending after migrate — investigate before serving traffic"
fi

# =============================================================================
# PHASE 9 — ACTIVATE RELEASE
# =============================================================================
log_info "Activating release ${TIMESTAMP}"
ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}"
# Reset OPcache so workers don't serve stale bytecode from old release
"${PHP}" -r "if(function_exists('opcache_reset')){opcache_reset();}" 2>/dev/null || true

# =============================================================================
# PHASE 10 — LARAVEL OPTIMIZATIONS
# =============================================================================
ARTISAN="${PHP} ${CURRENT_LINK}/artisan"

${ARTISAN} optimize:clear || log_warn "optimize:clear failed — stale caches may persist"
${ARTISAN} optimize || log_warn "optimize failed — framework bootstrap caches may be incomplete"
${ARTISAN} storage:link 2>/dev/null || true
[[ ! -L "${RELEASE_DIR}/public/storage" ]] && log_warn "public/storage symlink missing — file uploads may be inaccessible"

${ARTISAN} config:cache --no-interaction \
    || die_class "CACHE_FAILURE" "config:cache failed. Run: php artisan config:cache on server for details"
${ARTISAN} route:clear --no-interaction \
    || log_warn "route:clear failed — stale route cache may persist"
${ARTISAN} route:cache --no-interaction \
    || die_class "CACHE_FAILURE" "route:cache failed. Run: php artisan route:list on server for details"
${ARTISAN} view:cache --no-interaction \
    || die_class "CACHE_FAILURE" "view:cache failed. Run: php artisan view:cache on server for details"
${ARTISAN} event:cache 2>/dev/null || log_warn "event:cache failed"
${ARTISAN} icons:cache 2>/dev/null || true

# Verify app still boots after cache rebuild
if ! ABOUT_OUT=$("${PHP}" "${CURRENT_LINK}/artisan" about --no-interaction 2>&1); then
    echo "${ABOUT_OUT}" | tail -20 >&2
    die_class "BOOT_FAILURE" "artisan about failed after cache rebuild — app cannot boot. Check service provider / config syntax."
fi
echo "${ABOUT_OUT}" | grep -qiE "Debug.*(true|enabled|on)" && \
    log_warn "APP_DEBUG appears enabled in artisan about output"

if [[ ${SAFE_MODE} -eq 0 ]]; then
    ${ARTISAN} queue:restart || log_warn "queue:restart failed"
    ${ARTISAN} horizon:terminate 2>/dev/null || true
fi

# =============================================================================
# PHASE 11 — MAINTENANCE MODE OFF
# =============================================================================
${ARTISAN} up
log_ok "App online"

# =============================================================================
# PHASE 11b — APPLICATION ERROR SCAN
# Only scans log content written during this deploy.
# =============================================================================
if [[ -f "${_LOG_FILE_SCAN}" ]]; then
    _NEW_LOG=$(tail -c "+$((_LOG_PRE_SIZE + 1))" "${_LOG_FILE_SCAN}" 2>/dev/null || true)
    APP_ERROR_FOUND=0
    for _pat in "PHP Fatal error" "FatalThrowableError" "BindingResolutionException" \
                "Target class .* does not exist" "Class .* not found" "SQLSTATE" "Syntax error"; do
        echo "${_NEW_LOG}" | grep -qiE "${_pat}" 2>/dev/null && { log_err "Fatal log pattern: ${_pat}"; APP_ERROR_FOUND=1; }
    done
    [[ ${APP_ERROR_FOUND} -eq 1 ]] && {
        echo "${_NEW_LOG}" | tail -n 30 >&2
        die_class "APP_ERROR" "Fatal errors in laravel.log after deploy — auto-rolling back (deploy_id: ${DEPLOY_ID})"
    }
fi

# =============================================================================
# PHASE 12 — PERMISSIONS
# =============================================================================
chmod -R 775 "${SHARED_DIR}/storage" "${RELEASE_DIR}/bootstrap/cache"
safe_sudo chown -R www-data:www-data \
    "${SHARED_DIR}/storage" "${RELEASE_DIR}/bootstrap/cache" "${RELEASE_DIR}/public/build" 2>/dev/null \
    || log_warn "Could not chown — run: sudo chown -R www-data:www-data ${SHARED_DIR}/storage"
_APP_PUB3="$(realpath "${APP_DIR}/public"     2>/dev/null || echo "")"
_REL_PUB3="$(realpath "${RELEASE_DIR}/public" 2>/dev/null || echo "")"
if [[ -d "${APP_DIR}/public/build" && "${_APP_PUB3}" != "${_REL_PUB3}" ]]; then
    safe_sudo chown -R www-data:www-data "${APP_DIR}/public/build" 2>/dev/null || true
fi

# =============================================================================
# PHASE 13 — RELOAD SERVICES
# =============================================================================
log_info "Restarting services"
reload_phpfpm; reload_nginx; restart_workers
_FPM_SVC=$(detect_phpfpm_service)
if [[ -n "${_FPM_SVC}" ]]; then
    sleep 1
    systemctl is-active --quiet "${_FPM_SVC}" 2>/dev/null \
        || log_warn "php-fpm (${_FPM_SVC}) NOT active after reload. Fix: sudo systemctl start ${_FPM_SVC} && sudo journalctl -u ${_FPM_SVC} -n 50"
fi

# =============================================================================
# PHASE 14 — POST-DEPLOY VALIDATION
# =============================================================================
VALIDATION_FAILED=0
_chk() { local desc="$1"; shift; "$@" &>/dev/null 2>&1 || { log_warn "FAIL: ${desc}"; VALIDATION_FAILED=1; }; }

_chk "Laravel boots"         "${PHP}" "${CURRENT_LINK}/artisan" list
_chk "manifest.json present" test -f "${CURRENT_LINK}/public/build/manifest.json"

APP_URL=$(read_env APP_URL)
if [[ -n "${APP_URL}" ]] && command -v curl &>/dev/null; then
    HTTP_CODE=$(curl -sL -o /dev/null -w "%{http_code}" --max-time 15 "${APP_URL}" 2>/dev/null || echo "000")
    case "${HTTP_CODE}" in
        200|301|302) ;;
        *) log_warn "HTTP ${HTTP_CODE} from ${APP_URL}"; VALIDATION_FAILED=1 ;;
    esac
fi

for _svc in nginx "php.*fpm"; do
    pgrep -f "${_svc}" &>/dev/null || { log_warn "Process not running: ${_svc}"; VALIDATION_FAILED=1; }
done

# =============================================================================
# PHASE 15 — CACHE WARMUP
# =============================================================================
if [[ -n "${APP_URL:-}" ]] && command -v curl &>/dev/null; then
    for _wurl in "${APP_URL}" "${APP_URL%/}/admin/dashboard" "${APP_URL%/}/hall/bookings/calendar"; do
        curl -sL -o /dev/null --max-time 15 "${_wurl}" 2>/dev/null || true
    done
fi

# =============================================================================
# PHASE 16 — PRUNE OLD RELEASES
# =============================================================================
mapfile -t RELEASES_ALL < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null)
_PRUNE_SUCCESS=0; _PRUNE_FAILED=0
for _rel in "${RELEASES_ALL[@]}"; do
    _rel="${_rel%/}"; [[ -d "${_rel}" ]] || continue
    if [[ -f "${_rel}/.deploy_failed" ]]; then
        _PRUNE_FAILED=$((_PRUNE_FAILED+1))
        [[ ${_PRUNE_FAILED} -gt ${KEEP_FAILED_RELEASES} ]] && rm -rf "${_rel}"
    else
        _PRUNE_SUCCESS=$((_PRUNE_SUCCESS+1))
        [[ ${_PRUNE_SUCCESS} -gt ${KEEP_RELEASES} ]] && rm -rf "${_rel}"
    fi
done

# =============================================================================
# SUMMARY
# =============================================================================
DEPLOY_END=$(date +%s); DEPLOY_DURATION=$(( DEPLOY_END - DEPLOY_START ))
_T_COMPOSER=$(( T_COMPOSER_END - T_COMPOSER_START ))
_T_MIGRATE=$(( T_MIGRATE_END - T_MIGRATE_START ))
_DEPLOY_RESULT="success"; [[ ${VALIDATION_FAILED} -ne 0 ]] && _DEPLOY_RESULT="success_with_warnings"

_HISTORY_FILE="${SHARED_DIR}/storage/logs/deployments.log"
printf '%s | %-22s | %-20s | %s | %-12s | %-12s | %4ds\n' \
    "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "${_DEPLOY_RESULT}" "${DEPLOY_ID}" \
    "${COMMIT:0:12}" "${BRANCH}" "$(whoami 2>/dev/null)" "${DEPLOY_DURATION}" \
    >> "${_HISTORY_FILE}" 2>/dev/null || true

if [[ ${VALIDATION_FAILED} -ne 0 ]]; then
    log_warn "Deployment completed with warnings — review output above"
    log_warn "Rollback: bash deployment/deploy.sh --rollback"
else
    log_ok "Deployment completed — ${DEPLOY_ID}  commit: ${COMMIT:0:12}  ${DEPLOY_DURATION}s"
fi
[[ -n "${APP_URL:-}" ]] && echo "  ${APP_URL}"
