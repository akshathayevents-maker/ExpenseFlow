#!/usr/bin/env bash
# =============================================================================
# ExpenseFlow — Zero-Downtime Deploy Script
# Run as the deploy user (deployer) from CI/CD or manually.
# Usage: bash deploy.sh [branch] [--rollback]
# =============================================================================
set -euo pipefail

BRANCH="${1:-main}"
APP_DIR="/var/www/expenseflow"
REPO_DIR="${APP_DIR}/repo"
RELEASES_DIR="${APP_DIR}/releases"
SHARED_DIR="${APP_DIR}/shared"
CURRENT_LINK="${APP_DIR}/current"
KEEP_RELEASES=5
PHP="php8.3"
COMPOSER="/usr/local/bin/composer"
TIMESTAMP=$(date +%Y%m%d%H%M%S)
RELEASE_DIR="${RELEASES_DIR}/${TIMESTAMP}"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
log()     { echo -e "${GREEN}[$(date '+%H:%M:%S')] $*${NC}"; }
info()    { echo -e "${BLUE}[$(date '+%H:%M:%S')] $*${NC}"; }
warn()    { echo -e "${YELLOW}[WARN] $*${NC}"; }
die()     { echo -e "${RED}[ERROR] $*${NC}"; exit 1; }
step()    { echo -e "\n${BLUE}━━━ $* ━━━${NC}"; }

# ── Rollback ──────────────────────────────────────────────────────────────────
if [[ "${1:-}" == "--rollback" ]]; then
    step "ROLLBACK"
    RELEASES=($(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null | head -5))
    if [[ ${#RELEASES[@]} -lt 2 ]]; then
        die "No previous release to roll back to."
    fi
    PREV="${RELEASES[1]%/}"
    CURRENT_REL=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "none")
    info "Current:  ${CURRENT_REL}"
    info "Previous: ${PREV}"
    ln -sfn "${PREV}" "${CURRENT_LINK}"
    sudo systemctl reload php8.3-fpm
    sudo systemctl reload nginx
    log "Rolled back to: $(basename "${PREV}")"
    exit 0
fi

# ── Pre-flight ─────────────────────────────────────────────────────────────────
step "Pre-flight checks"
[[ -d "${APP_DIR}" ]]      || die "App directory missing: ${APP_DIR}"
[[ -d "${REPO_DIR}" ]]     || die "Bare repo missing: ${REPO_DIR}. Run: git clone --bare <url> ${REPO_DIR}"
[[ -f "${SHARED_DIR}/.env" ]] || die ".env missing: ${SHARED_DIR}/.env"
command -v ${PHP}          >/dev/null || die "PHP not found"
command -v ${COMPOSER}     >/dev/null || die "Composer not found"
command -v node            >/dev/null || die "Node.js not found"
log "Pre-flight OK"

# ── Fetch latest code ──────────────────────────────────────────────────────────
step "Fetching code (branch: ${BRANCH})"
git -C "${REPO_DIR}" fetch --all --prune
COMMIT=$(git -C "${REPO_DIR}" rev-parse "origin/${BRANCH}")
info "Deploying commit: ${COMMIT}"

# ── Create release directory ───────────────────────────────────────────────────
step "Creating release: ${TIMESTAMP}"
mkdir -p "${RELEASE_DIR}"
git -C "${REPO_DIR}" --work-tree="${RELEASE_DIR}" checkout -f "origin/${BRANCH}"
log "Code checked out to ${RELEASE_DIR}"

# ── Symlink shared files/dirs ──────────────────────────────────────────────────
step "Linking shared resources"
rm -rf "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/storage"        "${RELEASE_DIR}/storage"
ln -sfn "${SHARED_DIR}/public/storage" "${RELEASE_DIR}/public/storage"
ln -sfn "${SHARED_DIR}/.env"           "${RELEASE_DIR}/.env"
log "Shared links created"

# ── PHP dependencies ──────────────────────────────────────────────────────────
step "Installing Composer dependencies"
${PHP} ${COMPOSER} install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --working-dir="${RELEASE_DIR}"
log "Composer done"

# ── Frontend assets ───────────────────────────────────────────────────────────
step "Building frontend assets"
cd "${RELEASE_DIR}"
npm ci --prefer-offline
npm run build
cd - >/dev/null
log "Frontend build done"

# ── Maintenance mode ON ────────────────────────────────────────────────────────
step "Enabling maintenance mode"
CURRENT_ACTIVE=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")
if [[ -n "${CURRENT_ACTIVE}" && -d "${CURRENT_ACTIVE}" ]]; then
    ${PHP} "${CURRENT_ACTIVE}/artisan" down \
        --render="errors.503" \
        --retry=30 \
        --refresh=15 \
        --secret="$(openssl rand -hex 16)" \
        2>/dev/null || warn "Could not enable maintenance mode on current release"
fi

# ── Database migrations ────────────────────────────────────────────────────────
step "Running migrations"
${PHP} "${RELEASE_DIR}/artisan" migrate --force --no-interaction
log "Migrations complete"

# ── Activate release (atomic symlink swap) ────────────────────────────────────
step "Activating release"
ln -sfn "${RELEASE_DIR}" "${CURRENT_LINK}"
log "Current → ${TIMESTAMP}"

# ── Laravel optimizations ──────────────────────────────────────────────────────
step "Running post-deploy optimizations"
ARTISAN="${PHP} ${CURRENT_LINK}/artisan"

${ARTISAN} storage:link       2>/dev/null || true
${ARTISAN} config:cache
${ARTISAN} route:cache
${ARTISAN} view:cache
${ARTISAN} event:cache
${ARTISAN} icons:cache        2>/dev/null || true   # blade-icons if installed
log "Cache warmed"

# ── Maintenance mode OFF ───────────────────────────────────────────────────────
step "Disabling maintenance mode"
${ARTISAN} up
log "Application online"

# ── Reload services ───────────────────────────────────────────────────────────
step "Reloading services"
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx

# Restart queue workers via Supervisor
sudo supervisorctl restart expenseflow-worker:*  2>/dev/null || \
    sudo supervisorctl restart expenseflow-worker  2>/dev/null || \
    warn "Could not restart supervisor workers — restart manually"
log "Services reloaded"

# ── Set permissions ───────────────────────────────────────────────────────────
step "Setting permissions"
chmod -R 775 "${SHARED_DIR}/storage"
chmod -R 775 "${RELEASE_DIR}/bootstrap/cache"
chown -R www-data:www-data "${SHARED_DIR}/storage" 2>/dev/null || true
log "Permissions set"

# ── Prune old releases ────────────────────────────────────────────────────────
step "Pruning old releases (keeping ${KEEP_RELEASES})"
RELEASES_ALL=($(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null))
RELEASES_COUNT=${#RELEASES_ALL[@]}
if [[ ${RELEASES_COUNT} -gt ${KEEP_RELEASES} ]]; then
    TO_DELETE=("${RELEASES_ALL[@]:${KEEP_RELEASES}}")
    for OLD in "${TO_DELETE[@]}"; do
        info "Removing: ${OLD%/}"
        rm -rf "${OLD%/}"
    done
fi
log "Kept ${KEEP_RELEASES} most recent releases"

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
log "=============================="
log "  DEPLOYMENT SUCCESSFUL"
log "=============================="
log "  Release : ${TIMESTAMP}"
log "  Commit  : ${COMMIT:0:12}"
log "  Branch  : ${BRANCH}"
log "=============================="
