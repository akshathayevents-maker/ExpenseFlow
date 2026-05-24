#!/usr/bin/env bash
# =============================================================================
# ExpenseFlow — Standalone Rollback Script
# Usage:
#   bash rollback.sh              — roll back to the previous release
#   bash rollback.sh --release=2  — roll back N releases (2 = one before previous)
#   bash rollback.sh --list       — list available releases, then exit
#   bash rollback.sh --yes        — skip confirmation prompt (CI/CD use)
# =============================================================================
set -Eeuo pipefail

# ── Colour helpers ─────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'
_ts()      { date '+%Y-%m-%d %H:%M:%S'; }
log_info() { echo -e "${BLUE}$(_ts) [INFO]${NC}    $*"; }
log_ok()   { echo -e "${GREEN}$(_ts) [SUCCESS]${NC} $*"; }
log_warn() { echo -e "${YELLOW}$(_ts) [WARN]${NC}    $*" >&2; }
log_err()  { echo -e "${RED}$(_ts) [ERROR]${NC}   $*" >&2; }
die()      { log_err "$*"; exit 1; }
step()     { echo -e "\n${CYAN}${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}";
             echo -e "${CYAN}${BOLD}  ▶  $*${NC}";
             echo -e "${CYAN}${BOLD}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"; }

safe_sudo() { if [[ $EUID -eq 0 ]]; then "$@"; else sudo "$@"; fi; }

# ── Parse args ────────────────────────────────────────────────────────────────
RELEASE_N=1       # 1 = previous, 2 = one before that …
LIST_ONLY=0
AUTO_YES=0
for _a in "$@"; do
    case "${_a}" in
        --release=*) RELEASE_N="${_a#--release=}" ;;
        --list)      LIST_ONLY=1 ;;
        --yes|-y)    AUTO_YES=1 ;;
        *) die "Unknown arg: ${_a}. Usage: bash rollback.sh [--release=N] [--list] [--yes]" ;;
    esac
done

# ── Detect paths ──────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "${SCRIPT_DIR}")"

# Verify we found the app dir (has an artisan or the releases dir)
if [[ ! -d "${APP_DIR}" ]]; then
    # Fallback: well-known server paths
    for d in /var/www/expenseflow /var/www/akshathayexpense; do
        [[ -d "${d}" ]] && APP_DIR="${d}" && break
    done
fi

RELEASES_DIR="${APP_DIR}/releases"
SHARED_DIR="${APP_DIR}/shared"
CURRENT_LINK="${APP_DIR}/current"
DEPLOY_LOG_DIR="${APP_DIR}/deploy_logs"

PHP_BIN=""
for _b in php8.3 php8.2 php8.1 php; do
    command -v "${_b}" &>/dev/null && PHP_BIN="${_b}" && break
done
[[ -z "${PHP_BIN}" ]] && die "PHP not found — cannot run artisan"

ARTISAN="${PHP_BIN} ${CURRENT_LINK}/artisan"

# ── Collect available releases ────────────────────────────────────────────────
mapfile -t ALL_RELEASES < <(ls -1dt "${RELEASES_DIR}"/*/  2>/dev/null | sed 's|/$||')

if [[ ${#ALL_RELEASES[@]} -eq 0 ]]; then
    die "No releases found in ${RELEASES_DIR}"
fi

CURRENT_REAL=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "none")

# ── --list ────────────────────────────────────────────────────────────────────
if [[ ${LIST_ONLY} -eq 1 ]]; then
    echo ""
    echo -e "${BOLD}Available releases (newest first):${NC}"
    echo ""
    for i in "${!ALL_RELEASES[@]}"; do
        rel="${ALL_RELEASES[$i]}"
        rel_name="$(basename "${rel}")"
        marker="  "
        label=""
        if [[ "${rel}" == "${CURRENT_REAL}" ]]; then
            marker="${GREEN}→${NC}"
            label=" ${GREEN}(current)${NC}"
        elif [[ $i -eq 1 ]]; then
            label=" ${YELLOW}(rollback target)${NC}"
        fi
        # Show commit if release.json exists
        commit=""
        if [[ -f "${rel}/release.json" ]]; then
            commit=$(python3 -c "import json; d=json.load(open('${rel}/release.json')); print(d.get('commit','')[:12])" 2>/dev/null || true)
            [[ -n "${commit}" ]] && commit=" ${CYAN}${commit}${NC}"
        fi
        # Show failed flag
        failed=""
        [[ -f "${rel}/.deploy_failed" ]] && failed=" ${RED}[FAILED]${NC}"
        echo -e "  ${marker} [${i}] ${rel_name}${commit}${label}${failed}"
    done
    echo ""
    exit 0
fi

# ── Identify target release ───────────────────────────────────────────────────
# ALL_RELEASES[0] is current (or newest). We want index = RELEASE_N.
TARGET_IDX="${RELEASE_N}"
if [[ ${TARGET_IDX} -ge ${#ALL_RELEASES[@]} ]]; then
    die "Not enough releases to roll back ${RELEASE_N} step(s). Only ${#ALL_RELEASES[@]} release(s) exist. Run: bash rollback.sh --list"
fi

TARGET_RELEASE="${ALL_RELEASES[${TARGET_IDX}]}"
TARGET_NAME="$(basename "${TARGET_RELEASE}")"

if [[ "${TARGET_RELEASE}" == "${CURRENT_REAL}" ]]; then
    die "Target release is already current (${TARGET_NAME}). Nothing to do."
fi

if [[ -f "${TARGET_RELEASE}/.deploy_failed" ]]; then
    log_warn "Target release ${TARGET_NAME} was marked as a FAILED deploy."
    [[ ${AUTO_YES} -eq 0 ]] && read -rp "Roll back to a failed release anyway? [y/N] " _confirm
    [[ ${AUTO_YES} -eq 0 && "${_confirm:-n}" != "y" ]] && { log_info "Rollback cancelled."; exit 0; }
fi

# ── Confirmation ──────────────────────────────────────────────────────────────
echo ""
echo -e "${BOLD}Rollback plan:${NC}"
echo -e "  Current:  ${CYAN}$(basename "${CURRENT_REAL}")${NC}"
echo -e "  Target:   ${YELLOW}${TARGET_NAME}${NC}"
echo ""

if [[ ${AUTO_YES} -eq 0 ]]; then
    read -rp "Roll back to ${TARGET_NAME}? [y/N] " _confirm
    [[ "${_confirm:-n}" != "y" ]] && { log_info "Rollback cancelled."; exit 0; }
fi

# ── Tee to rollback log ───────────────────────────────────────────────────────
ROLLBACK_TIMESTAMP=$(date +%Y%m%d%H%M%S)
mkdir -p "${DEPLOY_LOG_DIR}" 2>/dev/null || true
ROLLBACK_LOG="${DEPLOY_LOG_DIR}/rollback_${ROLLBACK_TIMESTAMP}.log"
exec > >(tee -a "${ROLLBACK_LOG}") 2>&1
log_info "Rollback log: ${ROLLBACK_LOG}"

# ── Put app in maintenance ────────────────────────────────────────────────────
step "Enabling maintenance mode"
${ARTISAN} down --render="errors.503" --retry=10 2>/dev/null \
    || log_warn "Could not enable maintenance mode — proceeding anyway"

# ── Atomic symlink switch ─────────────────────────────────────────────────────
step "Switching symlink → ${TARGET_NAME}"
ln -sfn "${TARGET_RELEASE}" "${CURRENT_LINK}"
log_ok "Symlink updated: current → ${TARGET_NAME}"

# ── Clear stale caches (critical — old config might reference new release paths) ──
step "Clearing and rebuilding caches on rolled-back release"
NEW_ARTISAN="${PHP_BIN} ${CURRENT_LINK}/artisan"

${NEW_ARTISAN} optimize:clear   && log_ok "All caches cleared"
${NEW_ARTISAN} config:cache     && log_ok "Config cached"
${NEW_ARTISAN} route:cache      && log_ok "Routes cached"
${NEW_ARTISAN} view:cache       && log_ok "Views cached"
${NEW_ARTISAN} event:cache      && log_ok "Events cached"

# ── Reload services ───────────────────────────────────────────────────────────
step "Reloading services"
# PHP-FPM
for _svc in php8.3-fpm php8.2-fpm php8.1-fpm php-fpm; do
    if systemctl is-active --quiet "${_svc}" 2>/dev/null; then
        safe_sudo systemctl reload "${_svc}" && log_ok "php-fpm reloaded (${_svc})" && break
    fi
done

# Nginx
if nginx -t -q 2>/dev/null; then
    safe_sudo systemctl reload nginx 2>/dev/null && log_ok "nginx reloaded" || log_warn "nginx reload failed"
fi

# Queue workers
if command -v supervisorctl &>/dev/null; then
    # Signal workers to pick up new artisan path
    ${NEW_ARTISAN} queue:restart 2>/dev/null && log_ok "Queue workers signalled to restart" || true
    safe_sudo supervisorctl restart expenseflow: 2>/dev/null \
        || safe_sudo supervisorctl restart expenseflow-worker: 2>/dev/null \
        || log_warn "Could not restart supervisor workers — run: sudo supervisorctl restart expenseflow:"
fi

# ── Bring app back up ─────────────────────────────────────────────────────────
step "Bringing application online"
${NEW_ARTISAN} up
log_ok "Application online"

# ── Quick health check ────────────────────────────────────────────────────────
step "Quick health verification"
APP_URL=$(grep -E '^APP_URL=' "${SHARED_DIR}/.env" 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | head -1 || true)
if [[ -n "${APP_URL}" ]] && command -v curl &>/dev/null; then
    HTTP_CODE=$(curl -sL -o /dev/null -w "%{http_code}" --max-time 15 "${APP_URL}" 2>/dev/null || echo "000")
    if [[ "${HTTP_CODE}" == "200" || "${HTTP_CODE}" == "302" || "${HTTP_CODE}" == "301" ]]; then
        log_ok "HTTP ${HTTP_CODE} — app responding at ${APP_URL}"
    else
        log_warn "HTTP ${HTTP_CODE} — app may not be serving correctly (${APP_URL})"
    fi
fi

# ── Log entry ────────────────────────────────────────────────────────────────
_HISTORY_FILE="${SHARED_DIR}/storage/logs/deployments.log"
mkdir -p "$(dirname "${_HISTORY_FILE}")" 2>/dev/null || true
printf '%s | %-22s | %-20s | %s | %-12s | %-12s | %4ds\n' \
    "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "rollback" \
    "rollback_${ROLLBACK_TIMESTAMP}" \
    "$(basename "${TARGET_RELEASE}")" "rollback" "$(whoami 2>/dev/null)" "0" \
    >> "${_HISTORY_FILE}" 2>/dev/null || true

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD}╔══════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}${BOLD}║     ROLLBACK SUCCESSFUL ✓                    ║${NC}"
echo -e "${GREEN}${BOLD}╚══════════════════════════════════════════════╝${NC}"
echo ""
echo -e "  ${BOLD}Was:${NC}   $(basename "${CURRENT_REAL}")"
echo -e "  ${BOLD}Now:${NC}   ${TARGET_NAME}"
echo -e "  ${BOLD}Log:${NC}   ${ROLLBACK_LOG}"
echo ""
echo -e "  ${YELLOW}NOTE: Database migrations are NOT reversed automatically.${NC}"
echo -e "  If the rolled-back release expects an older schema, apply a"
echo -e "  reverse migration manually before confirming the rollback is stable."
echo ""
