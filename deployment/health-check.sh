#!/usr/bin/env bash
# =============================================================================
# ExpenseFlow — Health Check Script
# Verifies the full application stack post-deploy or at any time.
#
# Usage:
#   bash health-check.sh                — full check, prints results
#   bash health-check.sh --quiet        — silent except on failure (CI use)
#   bash health-check.sh --json         — output results as JSON
#   bash health-check.sh --url=https:// — override APP_URL
#
# Exit codes:
#   0 = all checks passed
#   1 = one or more checks failed
#   2 = critical infrastructure check failed (app may be down)
# =============================================================================
set -Eeuo pipefail

# ── Colour helpers ─────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'
_ts()      { date '+%Y-%m-%d %H:%M:%S'; }
log_pass() { echo -e "  ${GREEN}✓${NC} $*"; }
log_fail() { echo -e "  ${RED}✗${NC} $*"; }
log_warn() { echo -e "  ${YELLOW}⚠${NC} $*"; }
log_info() { echo -e "  ${BLUE}·${NC} $*"; }
die()      { echo -e "${RED}$(_ts) [ERROR]${NC}   $*" >&2; exit 2; }

# ── Parse args ────────────────────────────────────────────────────────────────
QUIET=0
JSON_OUTPUT=0
URL_OVERRIDE=""
for _a in "$@"; do
    case "${_a}" in
        --quiet)    QUIET=1 ;;
        --json)     JSON_OUTPUT=1 ;;
        --url=*)    URL_OVERRIDE="${_a#--url=}" ;;
        *) echo "Unknown arg: ${_a}" >&2; exit 1 ;;
    esac
done

# ── Detect paths ──────────────────────────────────────────────────────────────
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(dirname "${SCRIPT_DIR}")"
for d in /var/www/expenseflow /var/www/akshathayexpense; do
    [[ -d "${APP_DIR}/releases" ]] && break
    [[ -d "${d}/releases" ]] && APP_DIR="${d}" && break
done

SHARED_DIR="${APP_DIR}/shared"
CURRENT_LINK="${APP_DIR}/current"
CURRENT_REAL=$(readlink -f "${CURRENT_LINK}" 2>/dev/null || echo "")

PHP_BIN=""
for _b in php8.3 php8.2 php8.1 php; do
    command -v "${_b}" &>/dev/null && PHP_BIN="${_b}" && break
done

ENV_FILE="${SHARED_DIR}/.env"
APP_URL="${URL_OVERRIDE}"
if [[ -z "${APP_URL}" && -f "${ENV_FILE}" ]]; then
    APP_URL=$(grep -E '^APP_URL=' "${ENV_FILE}" | cut -d= -f2 | tr -d '"' | tr -d "'" | head -1 || true)
fi

# ── Check registry ────────────────────────────────────────────────────────────
declare -A CHECK_STATUS   # PASS | FAIL | WARN | SKIP
declare -A CHECK_MESSAGE
declare -a CHECK_ORDER

register_check() {
    local name="$1"
    CHECK_ORDER+=("${name}")
    CHECK_STATUS["${name}"]="SKIP"
    CHECK_MESSAGE["${name}"]="not evaluated"
}
set_check() {
    local name="$1" status="$2" msg="$3"
    CHECK_STATUS["${name}"]="${status}"
    CHECK_MESSAGE["${name}"]="${msg}"
}

# Register all checks
register_check "release_exists"
register_check "env_symlink"
register_check "storage_symlink"
register_check "public_storage_symlink"
register_check "vite_manifest"
register_check "css_asset_size"
register_check "sw_js_exists"
register_check "web_manifest_exists"
register_check "bootstrap_cache"
register_check "config_cache"
register_check "storage_writable"
register_check "logs_writable"
register_check "db_connectivity"
register_check "http_response"
register_check "ssl_certificate"
register_check "css_asset_http"
register_check "sw_http"
register_check "webmanifest_http"
register_check "queue_workers"
register_check "scheduler_cron"
register_check "php_fpm_running"
register_check "nginx_running"
register_check "disk_space"
register_check "signed_url_key"

# ── Check: release exists ─────────────────────────────────────────────────────
if [[ -n "${CURRENT_REAL}" && -d "${CURRENT_REAL}" ]]; then
    set_check "release_exists" "PASS" "Current release: $(basename "${CURRENT_REAL}")"
else
    set_check "release_exists" "FAIL" "No current release symlink or target missing: ${CURRENT_LINK}"
fi

# ── Check: .env symlink ───────────────────────────────────────────────────────
if [[ -f "${CURRENT_LINK}/.env" ]]; then
    set_check "env_symlink" "PASS" ".env resolves"
else
    set_check "env_symlink" "FAIL" ".env missing at ${CURRENT_LINK}/.env"
fi

# ── Check: storage symlink ────────────────────────────────────────────────────
if [[ -L "${CURRENT_LINK}/storage" && -d "${CURRENT_LINK}/storage" ]]; then
    set_check "storage_symlink" "PASS" "storage → $(readlink "${CURRENT_LINK}/storage" 2>/dev/null)"
else
    set_check "storage_symlink" "FAIL" "storage symlink missing or broken"
fi

# ── Check: public/storage symlink ────────────────────────────────────────────
if [[ -L "${CURRENT_LINK}/public/storage" ]]; then
    set_check "public_storage_symlink" "PASS" "public/storage symlink exists"
else
    set_check "public_storage_symlink" "WARN" "public/storage symlink missing — file uploads may not be accessible"
fi

# ── Check: Vite manifest ─────────────────────────────────────────────────────
MANIFEST="${CURRENT_LINK}/public/build/manifest.json"
if [[ -f "${MANIFEST}" ]]; then
    set_check "vite_manifest" "PASS" "manifest.json present"
else
    set_check "vite_manifest" "FAIL" "manifest.json missing — Vite build not deployed"
fi

# ── Check: CSS asset size ─────────────────────────────────────────────────────
if [[ -f "${MANIFEST}" ]]; then
    CSS_FILE=$(python3 -c "
import json, sys
try:
    m = json.load(open('${MANIFEST}'))
    k = [k for k in m if 'app.css' in k]
    print(m[k[0]]['file'] if k else '')
except Exception as e:
    sys.exit(0)
" 2>/dev/null || true)
    if [[ -n "${CSS_FILE}" ]]; then
        CSS_PATH="${CURRENT_LINK}/public/build/${CSS_FILE}"
        if [[ -f "${CSS_PATH}" ]]; then
            CSS_BYTES=$(wc -c < "${CSS_PATH}" 2>/dev/null || echo 0)
            if [[ ${CSS_BYTES} -lt 10240 ]]; then
                set_check "css_asset_size" "FAIL" "CSS only ${CSS_BYTES}B (< 10KB) — incomplete build"
            else
                CSS_KB=$(( CSS_BYTES / 1024 ))
                set_check "css_asset_size" "PASS" "CSS asset: ${CSS_KB} KB (${CSS_FILE})"
            fi
        else
            set_check "css_asset_size" "FAIL" "CSS file referenced in manifest not found: ${CSS_FILE}"
        fi
    else
        set_check "css_asset_size" "WARN" "Could not parse CSS entry from manifest.json"
    fi
fi

# ── Check: sw.js ─────────────────────────────────────────────────────────────
if [[ -f "${CURRENT_LINK}/public/sw.js" ]]; then
    SW_VERSION=$(grep -o "CACHE_VERSION = '[^']*'" "${CURRENT_LINK}/public/sw.js" 2>/dev/null | head -1 || true)
    set_check "sw_js_exists" "PASS" "sw.js exists (${SW_VERSION:-version unknown})"
else
    set_check "sw_js_exists" "WARN" "sw.js not found — PWA offline support disabled"
fi

# ── Check: manifest.webmanifest / site.webmanifest ───────────────────────────
WEBMANIFEST=$(find "${CURRENT_LINK}/public" -maxdepth 1 \
    -name "manifest.webmanifest" -o -name "site.webmanifest" 2>/dev/null | head -1)
if [[ -n "${WEBMANIFEST}" ]]; then
    set_check "web_manifest_exists" "PASS" "$(basename "${WEBMANIFEST}") present"
else
    set_check "web_manifest_exists" "WARN" "manifest.webmanifest not found — PWA installability may be broken"
fi

# ── Check: bootstrap/cache writable ──────────────────────────────────────────
if [[ -w "${CURRENT_LINK}/bootstrap/cache" ]]; then
    set_check "bootstrap_cache" "PASS" "bootstrap/cache writable"
else
    set_check "bootstrap_cache" "FAIL" "bootstrap/cache not writable — config/route cache will fail"
fi

# ── Check: config cache ───────────────────────────────────────────────────────
if [[ -f "${CURRENT_LINK}/bootstrap/cache/config.php" ]]; then
    set_check "config_cache" "PASS" "config.php cache present"
else
    set_check "config_cache" "WARN" "config.php cache missing — run php artisan config:cache"
fi

# ── Check: storage writable ───────────────────────────────────────────────────
if [[ -w "${SHARED_DIR}/storage" ]]; then
    set_check "storage_writable" "PASS" "shared storage writable"
else
    set_check "storage_writable" "FAIL" "shared storage not writable — file uploads will fail"
fi

# ── Check: logs writable ─────────────────────────────────────────────────────
if [[ -w "${SHARED_DIR}/storage/logs" ]]; then
    set_check "logs_writable" "PASS" "storage/logs writable"
else
    set_check "logs_writable" "FAIL" "storage/logs not writable — logging disabled"
fi

# ── Check: DB connectivity ────────────────────────────────────────────────────
if [[ -n "${PHP_BIN}" && -f "${ENV_FILE}" ]]; then
    DB_RESULT=$(${PHP_BIN} -r "
        \$env = parse_ini_file('${ENV_FILE}');
        \$dsn = 'pgsql:host=' . (\$env['DB_HOST'] ?? '127.0.0.1') . ';port=' . (\$env['DB_PORT'] ?? 5432) . ';dbname=' . (\$env['DB_DATABASE'] ?? '');
        try { new PDO(\$dsn, \$env['DB_USERNAME'] ?? '', \$env['DB_PASSWORD'] ?? ''); echo 'ok'; }
        catch(Exception \$e) { echo 'fail:' . \$e->getMessage(); }
    " 2>/dev/null || echo "fail:php-error")
    if [[ "${DB_RESULT}" == "ok" ]]; then
        DB_NAME=$(grep -E '^DB_DATABASE=' "${ENV_FILE}" 2>/dev/null | cut -d= -f2 | tr -d '"' | tr -d "'" | head -1 || echo "?")
        set_check "db_connectivity" "PASS" "PostgreSQL connected (${DB_NAME})"
    else
        set_check "db_connectivity" "FAIL" "DB connection failed: ${DB_RESULT#fail:}"
    fi
else
    set_check "db_connectivity" "SKIP" "PHP or .env not available"
fi

# ── Check: HTTP response ──────────────────────────────────────────────────────
if [[ -n "${APP_URL}" ]] && command -v curl &>/dev/null; then
    HTTP_CODE=$(curl -sL -o /dev/null -w "%{http_code}" --max-time 15 "${APP_URL}" 2>/dev/null || echo "000")
    case "${HTTP_CODE}" in
        200|301|302) set_check "http_response" "PASS" "HTTP ${HTTP_CODE} from ${APP_URL}" ;;
        503)         set_check "http_response" "FAIL" "HTTP 503 — app may be in maintenance mode" ;;
        000)         set_check "http_response" "FAIL" "No response from ${APP_URL} (timeout or connection refused)" ;;
        *)           set_check "http_response" "WARN" "HTTP ${HTTP_CODE} from ${APP_URL}" ;;
    esac

    # ── Check: SSL certificate ────────────────────────────────────────────────
    if [[ "${APP_URL}" == https://* ]]; then
        DOMAIN=$(echo "${APP_URL}" | sed 's|https://||; s|/.*||')
        SSL_INFO=$(echo | openssl s_client -connect "${DOMAIN}:443" -servername "${DOMAIN}" 2>/dev/null | \
                   openssl x509 -noout -enddate 2>/dev/null || true)
        if [[ -n "${SSL_INFO}" ]]; then
            EXPIRY_STR=$(echo "${SSL_INFO}" | sed 's/notAfter=//')
            EXPIRY_EPOCH=$(date -d "${EXPIRY_STR}" +%s 2>/dev/null || echo 0)
            NOW_EPOCH=$(date +%s)
            DAYS_LEFT=$(( (EXPIRY_EPOCH - NOW_EPOCH) / 86400 ))
            if [[ ${DAYS_LEFT} -lt 0 ]]; then
                set_check "ssl_certificate" "FAIL" "SSL certificate EXPIRED ${DAYS_LEFT#-} days ago"
            elif [[ ${DAYS_LEFT} -lt 14 ]]; then
                set_check "ssl_certificate" "WARN" "SSL expires in ${DAYS_LEFT} days — renew soon"
            else
                set_check "ssl_certificate" "PASS" "SSL valid, ${DAYS_LEFT} days remaining"
            fi
        else
            set_check "ssl_certificate" "WARN" "Could not verify SSL cert (openssl unavailable or unreachable)"
        fi
    else
        set_check "ssl_certificate" "SKIP" "HTTP (not HTTPS)"
    fi

    # ── Check: CSS asset HTTP ─────────────────────────────────────────────────
    if [[ -f "${MANIFEST}" ]]; then
        MANIFEST_CSS=$(python3 -c "
import json
m = json.load(open('${MANIFEST}'))
k = [k for k in m if 'app.css' in k]
print(m[k[0]]['file'] if k else '')
" 2>/dev/null || true)
        if [[ -n "${MANIFEST_CSS}" ]]; then
            CSS_URL="${APP_URL%/}/build/${MANIFEST_CSS}"
            CSS_HTTP=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${CSS_URL}" 2>/dev/null || echo "000")
            if [[ "${CSS_HTTP}" == "200" ]]; then
                set_check "css_asset_http" "PASS" "CSS asset HTTP 200 (${MANIFEST_CSS})"
            else
                set_check "css_asset_http" "FAIL" "CSS asset HTTP ${CSS_HTTP} — nginx may point to wrong public dir"
            fi
        fi
    fi

    # ── Check: sw.js HTTP ─────────────────────────────────────────────────────
    SW_HTTP=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${APP_URL%/}/sw.js" 2>/dev/null || echo "000")
    if [[ "${SW_HTTP}" == "200" ]]; then
        set_check "sw_http" "PASS" "sw.js HTTP 200"
    else
        set_check "sw_http" "WARN" "sw.js HTTP ${SW_HTTP} — PWA install will fail"
    fi

    # ── Check: webmanifest HTTP ───────────────────────────────────────────────
    for _mf in manifest.webmanifest site.webmanifest; do
        MF_HTTP=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "${APP_URL%/}/${_mf}" 2>/dev/null || echo "000")
        if [[ "${MF_HTTP}" == "200" ]]; then
            set_check "webmanifest_http" "PASS" "${_mf} HTTP 200"
            break
        fi
    done
    [[ "${CHECK_STATUS[webmanifest_http]}" != "PASS" ]] && \
        set_check "webmanifest_http" "WARN" "webmanifest HTTP ${MF_HTTP:-0} — PWA install/update may fail"
else
    for _c in http_response ssl_certificate css_asset_http sw_http webmanifest_http; do
        set_check "${_c}" "SKIP" "APP_URL not set or curl unavailable"
    done
fi

# ── Check: queue workers ──────────────────────────────────────────────────────
if command -v supervisorctl &>/dev/null; then
    WORKER_STATUS=$(supervisorctl status 2>/dev/null | grep "expenseflow" || true)
    RUNNING_COUNT=$(echo "${WORKER_STATUS}" | grep -c "RUNNING" 2>/dev/null || echo 0)
    TOTAL_COUNT=$(echo "${WORKER_STATUS}" | grep -c "." 2>/dev/null || echo 0)
    if [[ ${RUNNING_COUNT} -gt 0 ]]; then
        set_check "queue_workers" "PASS" "${RUNNING_COUNT}/${TOTAL_COUNT} workers RUNNING"
    else
        set_check "queue_workers" "FAIL" "No workers running (supervisorctl shows: $(echo "${WORKER_STATUS}" | head -1))"
    fi
else
    # Fallback: check if queue:work processes exist
    WQ_PIDS=$(pgrep -f "artisan queue:work" 2>/dev/null | wc -l || echo 0)
    if [[ "${WQ_PIDS}" -gt 0 ]]; then
        set_check "queue_workers" "PASS" "${WQ_PIDS} queue:work process(es) running"
    else
        set_check "queue_workers" "WARN" "supervisorctl not found and no queue:work processes — queued jobs will not be processed"
    fi
fi

# ── Check: scheduler in crontab ──────────────────────────────────────────────
CRON_HAS_SCHEDULER=0
crontab -l 2>/dev/null | grep -q "schedule:run" && CRON_HAS_SCHEDULER=1
if [[ -f /etc/cron.d/expenseflow ]]; then
    grep -q "schedule:run" /etc/cron.d/expenseflow 2>/dev/null && CRON_HAS_SCHEDULER=1
fi
if [[ ${CRON_HAS_SCHEDULER} -eq 1 ]]; then
    set_check "scheduler_cron" "PASS" "schedule:run found in crontab/cron.d"
else
    set_check "scheduler_cron" "WARN" "schedule:run NOT in crontab — scheduled jobs will not run"
fi

# ── Check: PHP-FPM ───────────────────────────────────────────────────────────
FPM_RUNNING=0
for _svc in php8.3-fpm php8.2-fpm php8.1-fpm php-fpm; do
    if systemctl is-active --quiet "${_svc}" 2>/dev/null; then
        set_check "php_fpm_running" "PASS" "${_svc} active"
        FPM_RUNNING=1
        break
    fi
done
[[ ${FPM_RUNNING} -eq 0 ]] && set_check "php_fpm_running" "FAIL" "No PHP-FPM service active — app not serving PHP"

# ── Check: nginx ─────────────────────────────────────────────────────────────
if systemctl is-active --quiet nginx 2>/dev/null; then
    set_check "nginx_running" "PASS" "nginx active"
else
    set_check "nginx_running" "FAIL" "nginx not running — app unreachable"
fi

# ── Check: disk space ─────────────────────────────────────────────────────────
DISK_FREE_MB=$(df -m "${APP_DIR}" 2>/dev/null | awk 'NR==2{print $4}' || echo 9999)
if [[ ${DISK_FREE_MB} -lt 512 ]]; then
    set_check "disk_space" "FAIL" "Only ${DISK_FREE_MB} MB free — deploys will fail"
elif [[ ${DISK_FREE_MB} -lt 2048 ]]; then
    set_check "disk_space" "WARN" "${DISK_FREE_MB} MB free — getting low, clean up releases"
else
    set_check "disk_space" "PASS" "${DISK_FREE_MB} MB free"
fi

# ── Check: APP_KEY / signed URL validity ─────────────────────────────────────
if [[ -n "${PHP_BIN}" && -n "${CURRENT_REAL}" && -d "${CURRENT_REAL}" ]]; then
    SIGN_RESULT=$(${PHP_BIN} "${CURRENT_REAL}/artisan" tinker \
        --execute="echo strlen(config('app.key')) > 20 ? 'ok' : 'fail';" 2>/dev/null | grep -E "^(ok|fail)$" | head -1 || echo "skip")
    case "${SIGN_RESULT}" in
        ok)   set_check "signed_url_key" "PASS" "APP_KEY present and loadable" ;;
        fail) set_check "signed_url_key" "FAIL" "APP_KEY missing or too short — signed URLs broken" ;;
        *)    set_check "signed_url_key" "SKIP" "Could not verify via artisan tinker" ;;
    esac
else
    set_check "signed_url_key" "SKIP" "PHP or release not available"
fi

# =============================================================================
# OUTPUT
# =============================================================================
FAIL_COUNT=0
WARN_COUNT=0
PASS_COUNT=0
SKIP_COUNT=0

if [[ ${JSON_OUTPUT} -eq 1 ]]; then
    echo "{"
    echo '  "timestamp": "'"$(date -u '+%Y-%m-%dT%H:%M:%SZ')"'",'
    echo '  "release": "'"$(basename "${CURRENT_REAL}")"'",'
    echo '  "checks": {'
    FIRST=1
    for chk in "${CHECK_ORDER[@]}"; do
        [[ ${FIRST} -eq 0 ]] && echo ","
        echo -n "    \"${chk}\": {\"status\": \"${CHECK_STATUS[${chk}]}\", \"message\": \"${CHECK_MESSAGE[${chk}]}\"}"
        FIRST=0
    done
    echo ""
    echo "  }"
    echo "}"
else
    if [[ ${QUIET} -eq 0 ]]; then
        echo ""
        echo -e "${BOLD}ExpenseFlow Health Check${NC}  [$(_ts)]"
        echo -e "  Release: ${CYAN}$(basename "${CURRENT_REAL}")${NC}"
        echo -e "  URL:     ${APP_URL:-not set}"
        echo ""

        # Group by category
        echo -e "${BOLD}Infrastructure${NC}"
        for chk in php_fpm_running nginx_running disk_space queue_workers scheduler_cron; do
            case "${CHECK_STATUS[${chk}]}" in
                PASS) log_pass "${chk}: ${CHECK_MESSAGE[${chk}]}" ; PASS_COUNT=$(( PASS_COUNT + 1 )) ;;
                FAIL) log_fail "${chk}: ${CHECK_MESSAGE[${chk}]}" ; FAIL_COUNT=$(( FAIL_COUNT + 1 )) ;;
                WARN) log_warn "${chk}: ${CHECK_MESSAGE[${chk}]}" ; WARN_COUNT=$(( WARN_COUNT + 1 )) ;;
                SKIP) log_info "${chk}: ${CHECK_MESSAGE[${chk}]}" ; SKIP_COUNT=$(( SKIP_COUNT + 1 )) ;;
            esac
        done

        echo ""
        echo -e "${BOLD}Application${NC}"
        for chk in release_exists env_symlink storage_symlink public_storage_symlink bootstrap_cache config_cache storage_writable logs_writable db_connectivity signed_url_key; do
            case "${CHECK_STATUS[${chk}]}" in
                PASS) log_pass "${chk}: ${CHECK_MESSAGE[${chk}]}" ; PASS_COUNT=$(( PASS_COUNT + 1 )) ;;
                FAIL) log_fail "${chk}: ${CHECK_MESSAGE[${chk}]}" ; FAIL_COUNT=$(( FAIL_COUNT + 1 )) ;;
                WARN) log_warn "${chk}: ${CHECK_MESSAGE[${chk}]}" ; WARN_COUNT=$(( WARN_COUNT + 1 )) ;;
                SKIP) log_info "${chk}: ${CHECK_MESSAGE[${chk}]}" ; SKIP_COUNT=$(( SKIP_COUNT + 1 )) ;;
            esac
        done

        echo ""
        echo -e "${BOLD}Assets & PWA${NC}"
        for chk in vite_manifest css_asset_size sw_js_exists web_manifest_exists; do
            case "${CHECK_STATUS[${chk}]}" in
                PASS) log_pass "${chk}: ${CHECK_MESSAGE[${chk}]}" ; PASS_COUNT=$(( PASS_COUNT + 1 )) ;;
                FAIL) log_fail "${chk}: ${CHECK_MESSAGE[${chk}]}" ; FAIL_COUNT=$(( FAIL_COUNT + 1 )) ;;
                WARN) log_warn "${chk}: ${CHECK_MESSAGE[${chk}]}" ; WARN_COUNT=$(( WARN_COUNT + 1 )) ;;
                SKIP) log_info "${chk}: ${CHECK_MESSAGE[${chk}]}" ; SKIP_COUNT=$(( SKIP_COUNT + 1 )) ;;
            esac
        done

        echo ""
        echo -e "${BOLD}HTTP & Network${NC}"
        for chk in http_response ssl_certificate css_asset_http sw_http webmanifest_http; do
            case "${CHECK_STATUS[${chk}]}" in
                PASS) log_pass "${chk}: ${CHECK_MESSAGE[${chk}]}" ; PASS_COUNT=$(( PASS_COUNT + 1 )) ;;
                FAIL) log_fail "${chk}: ${CHECK_MESSAGE[${chk}]}" ; FAIL_COUNT=$(( FAIL_COUNT + 1 )) ;;
                WARN) log_warn "${chk}: ${CHECK_MESSAGE[${chk}]}" ; WARN_COUNT=$(( WARN_COUNT + 1 )) ;;
                SKIP) log_info "${chk}: ${CHECK_MESSAGE[${chk}]}" ; SKIP_COUNT=$(( SKIP_COUNT + 1 )) ;;
            esac
        done

        echo ""
        echo -e "  ${BOLD}Passed: ${GREEN}${PASS_COUNT}${NC}  Warnings: ${YELLOW}${WARN_COUNT}${NC}  Failed: ${RED}${FAIL_COUNT}${NC}  Skipped: ${SKIP_COUNT}"
        echo ""
    else
        # quiet mode: tally counts silently
        for chk in "${CHECK_ORDER[@]}"; do
            case "${CHECK_STATUS[${chk}]}" in
                PASS) PASS_COUNT=$(( PASS_COUNT + 1 )) ;;
                FAIL) FAIL_COUNT=$(( FAIL_COUNT + 1 ))
                      echo -e "${RED}FAIL${NC} ${chk}: ${CHECK_MESSAGE[${chk}]}" ;;
                WARN) WARN_COUNT=$(( WARN_COUNT + 1 ))
                      echo -e "${YELLOW}WARN${NC} ${chk}: ${CHECK_MESSAGE[${chk}]}" ;;
                SKIP) SKIP_COUNT=$(( SKIP_COUNT + 1 )) ;;
            esac
        done
    fi
fi

# Exit 1 if any FAIL, 0 if all PASS/WARN/SKIP
[[ ${FAIL_COUNT} -gt 0 ]] && exit 1
exit 0
