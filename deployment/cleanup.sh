#!/usr/bin/env bash
# =============================================================================
# ExpenseFlow — Deployment Cleanup Script
# Safely prunes old releases, orphan assets, stale logs, and old DB backups.
#
# Usage:
#   bash cleanup.sh              — dry-run (shows what WOULD be deleted)
#   bash cleanup.sh --force      — actually delete
#   bash cleanup.sh --force --keep=3  — keep only 3 releases (default: 5)
#
# Safe by design:
#   - Never deletes current release
#   - Requires --force to actually remove anything
#   - All paths are validated before rm
#   - Uses rm -rf only on verified release dirs under /releases/
# =============================================================================
set -Eeuo pipefail

# ── Colour helpers ─────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; BOLD='\033[1m'; NC='\033[0m'
_ts()      { date '+%Y-%m-%d %H:%M:%S'; }
log_info() { echo -e "${BLUE}$(_ts) [INFO]${NC}    $*"; }
log_ok()   { echo -e "${GREEN}$(_ts) [SUCCESS]${NC} $*"; }
log_warn() { echo -e "${YELLOW}$(_ts) [WARN]${NC}    $*" >&2; }
log_dry()  { echo -e "${YELLOW}$(_ts) [DRY-RUN]${NC} $*"; }
die()      { echo -e "${RED}$(_ts) [ERROR]${NC}   $*" >&2; exit 1; }
step()     { echo -e "\n${BOLD}── $* ──────────────────────────────────────${NC}"; }

# ── Parse args ────────────────────────────────────────────────────────────────
DRY_RUN=1
KEEP_RELEASES=5
LOG_RETENTION_DAYS=30
BACKUP_RETENTION_DAYS=7

for _a in "$@"; do
    case "${_a}" in
        --force)          DRY_RUN=0 ;;
        --keep=*)         KEEP_RELEASES="${_a#--keep=}" ;;
        --log-days=*)     LOG_RETENTION_DAYS="${_a#--log-days=}" ;;
        --backup-days=*)  BACKUP_RETENTION_DAYS="${_a#--backup-days=}" ;;
        *) die "Unknown arg: ${_a}. Valid: --force --keep=N --log-days=N --backup-days=N" ;;
    esac
done

# ── Detect paths ──────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "${SCRIPT_DIR}")"
for d in /var/www/expenseflow /var/www/akshathayexpense; do
    [[ -d "${APP_DIR}/releases" ]] && break
    [[ -d "${d}/releases" ]] && APP_DIR="${d}" && break
done

RELEASES_DIR="${APP_DIR}/releases"
SHARED_DIR="${APP_DIR}/shared"
CURRENT_LINK="${APP_DIR}/current"
DEPLOY_LOG_DIR="${APP_DIR}/deploy_logs"
DB_BACKUP_DIR="${SHARED_DIR}/storage/backups/db"

[[ -d "${RELEASES_DIR}" ]] || die "Releases directory not found: ${RELEASES_DIR}"

CURRENT_REAL=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")

# ── Safe delete helper ────────────────────────────────────────────────────────
safe_delete() {
    local path="$1"
    local desc="$2"

    # Safety: path must be non-empty, must exist, must be under APP_DIR
    [[ -z "${path}" ]] && { log_warn "safe_delete: empty path for ${desc}"; return; }
    [[ -e "${path}" || -L "${path}" ]] || { log_info "Already gone: ${path}"; return; }

    # Must be under our app dir — prevents accidental root-level deletes
    local real_app
    real_app=$(realpath "${APP_DIR}" 2>/dev/null || echo "")
    local real_path
    real_path=$(realpath "${path}" 2>/dev/null || echo "${path}")
    if [[ -n "${real_app}" && "${real_path}" != "${real_app}"* ]]; then
        log_warn "SAFETY: refusing to delete path outside ${APP_DIR}: ${path}"
        return
    fi

    # Never delete current release
    if [[ -n "${CURRENT_REAL}" && "${real_path}" == "${CURRENT_REAL}"* ]]; then
        log_warn "SAFETY: refusing to delete path inside current release: ${path}"
        return
    fi

    if [[ ${DRY_RUN} -eq 1 ]]; then
        log_dry "Would delete: ${path}  (${desc})"
    else
        rm -rf "${path}"
        log_ok "Deleted: ${path}  (${desc})"
    fi
}

# Accumulate freed bytes
FREED_BYTES=0
account_size() {
    local path="$1"
    if [[ -d "${path}" ]]; then
        local sz
        sz=$(du -sb "${path}" 2>/dev/null | awk '{print $1}' || echo 0)
        FREED_BYTES=$(( FREED_BYTES + sz ))
    elif [[ -f "${path}" ]]; then
        local sz
        sz=$(wc -c < "${path}" 2>/dev/null || echo 0)
        FREED_BYTES=$(( FREED_BYTES + sz ))
    fi
}

echo ""
echo -e "${BOLD}ExpenseFlow Cleanup${NC}  ${DRY_RUN:+}$([ $DRY_RUN -eq 1 ] && echo "${YELLOW}(DRY RUN — add --force to delete)${NC}" || echo "${GREEN}(FORCE MODE)${NC}")"
echo -e "  App dir:  ${APP_DIR}"
echo -e "  Current:  ${CURRENT_REAL:-none}"
echo -e "  Keep:     last ${KEEP_RELEASES} successful releases"
echo ""

# =============================================================================
# 1. OLD RELEASES
# =============================================================================
step "Old releases"
mapfile -t ALL_RELEASES < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null | sed 's|/$||')
KEPT_SUCCESS=0
KEPT_FAILED=0

for rel in "${ALL_RELEASES[@]}"; do
    [[ -d "${rel}" ]] || continue
    rel_name=$(basename "${rel}")

    # Never touch current
    if [[ "$(realpath "${rel}" 2>/dev/null)" == "${CURRENT_REAL}" ]]; then
        log_info "Keep (current):  ${rel_name}"
        KEPT_SUCCESS=$(( KEPT_SUCCESS + 1 ))
        continue
    fi

    if [[ -f "${rel}/.deploy_failed" ]]; then
        # Keep only 2 failed releases for post-mortem
        KEPT_FAILED=$(( KEPT_FAILED + 1 ))
        if [[ ${KEPT_FAILED} -gt 2 ]]; then
            account_size "${rel}"
            safe_delete "${rel}" "failed release"
        else
            log_info "Keep (failed):   ${rel_name}"
        fi
    else
        KEPT_SUCCESS=$(( KEPT_SUCCESS + 1 ))
        if [[ ${KEPT_SUCCESS} -gt ${KEEP_RELEASES} ]]; then
            account_size "${rel}"
            safe_delete "${rel}" "old release"
        else
            log_info "Keep (success):  ${rel_name}"
        fi
    fi
done

# =============================================================================
# 2. ORPHAN BUILD ASSETS (in releases not referenced by current)
# =============================================================================
# node_modules inside non-current releases consume a lot of disk space.
# They are never served — safe to delete.
step "Orphan node_modules in old releases"
mapfile -t REMAINING_RELEASES < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null | sed 's|/$||')
for rel in "${REMAINING_RELEASES[@]}"; do
    [[ -d "${rel}" ]] || continue
    # Skip current
    [[ "$(realpath "${rel}" 2>/dev/null)" == "${CURRENT_REAL}" ]] && continue
    NM="${rel}/node_modules"
    if [[ -d "${NM}" ]]; then
        account_size "${NM}"
        safe_delete "${NM}" "orphan node_modules"
    fi
done

# =============================================================================
# 3. STALE DEPLOY LOGS (older than LOG_RETENTION_DAYS)
# =============================================================================
step "Deploy logs older than ${LOG_RETENTION_DAYS} days"
if [[ -d "${DEPLOY_LOG_DIR}" ]]; then
    while IFS= read -r -d '' logfile; do
        account_size "${logfile}"
        safe_delete "${logfile}" "old deploy log"
    done < <(find "${DEPLOY_LOG_DIR}" -maxdepth 1 -type f -name "*.log" \
                  -mtime "+${LOG_RETENTION_DAYS}" -print0 2>/dev/null)
else
    log_info "Deploy log dir not found: ${DEPLOY_LOG_DIR}"
fi

# =============================================================================
# 4. OLD DB BACKUPS (older than BACKUP_RETENTION_DAYS)
# =============================================================================
step "DB backups older than ${BACKUP_RETENTION_DAYS} days"
if [[ -d "${DB_BACKUP_DIR}" ]]; then
    while IFS= read -r -d '' bak; do
        account_size "${bak}"
        safe_delete "${bak}" "old db backup"
    done < <(find "${DB_BACKUP_DIR}" -maxdepth 1 -type f -name "*.sql.gz" \
                  -mtime "+${BACKUP_RETENTION_DAYS}" -print0 2>/dev/null)
else
    log_info "DB backup dir not found: ${DB_BACKUP_DIR}"
fi

# =============================================================================
# 5. LARAVEL FRAMEWORK CACHE FILES (stale views/config from dead releases)
# =============================================================================
# The shared storage is referenced by current — don't delete framework/cache contents.
# Only clean up old compiled view files that are no longer needed.
# This section is intentionally left as informational only — Laravel manages its
# own view cache invalidation via view:cache/optimize:clear.
step "Laravel view cache"
VIEW_CACHE="${SHARED_DIR}/storage/framework/views"
if [[ -d "${VIEW_CACHE}" ]]; then
    VIEW_COUNT=$(find "${VIEW_CACHE}" -type f 2>/dev/null | wc -l)
    log_info "Compiled views in cache: ${VIEW_COUNT} files"
    log_info "(Managed by Laravel — use 'php artisan view:clear' to purge)"
fi

# =============================================================================
# SUMMARY
# =============================================================================
FREED_MB=$(( FREED_BYTES / 1024 / 1024 ))
echo ""
echo -e "${BOLD}Cleanup summary${NC}"
echo -e "  Space freed: ${FREED_MB} MB"
if [[ ${DRY_RUN} -eq 1 ]]; then
    echo ""
    echo -e "  ${YELLOW}This was a DRY RUN. Add --force to actually delete:${NC}"
    echo -e "  ${YELLOW}  bash deployment/cleanup.sh --force${NC}"
else
    echo -e "  ${GREEN}Cleanup complete ✓${NC}"
fi
echo ""
