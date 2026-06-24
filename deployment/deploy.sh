#!/usr/bin/env bash
# ExpenseFlow — flat deploy script
# Usage: bash deployment/deploy.sh [branch]
# Runs: git pull → composer → migrate → optimize → restart
set -Eeuo pipefail

# ── Config ────────────────────────────────────────────────────────────────────
APP_DIR="/var/www/akshathayexpense"
BRANCH="${1:-main}"
LOCK_FILE="/tmp/expenseflow_deploy.lock"
DEPLOY_START=$(date +%s)
export PATH="/usr/local/bin:/usr/bin:/bin:${PATH:-}"

# ── Colours ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
_ts()      { date '+%H:%M:%S'; }
log_info() { echo -e "$(_ts) [INFO]  $*"; }
log_ok()   { echo -e "${GREEN}$(_ts) [OK]${NC}    $*"; }
log_warn() { echo -e "${YELLOW}$(_ts) [WARN]${NC}  $*" >&2; }
die()      { echo -e "${RED}$(_ts) [ERROR]${NC} $*" >&2; exit 1; }

safe_sudo() { [[ $EUID -eq 0 ]] && "$@" || sudo "$@"; }

# ── Detect PHP ────────────────────────────────────────────────────────────────
detect_php() {
    for b in php8.3 php8.2 php8.1 php8.0 php; do
        command -v "$b" &>/dev/null && { echo "$b"; return; }
    done
    echo ""
}

# ── Detect Composer ───────────────────────────────────────────────────────────
detect_composer() {
    for b in /usr/local/bin/composer /usr/bin/composer \
              "${HOME}/.composer/vendor/bin/composer" "${HOME}/composer.phar"; do
        [[ -x "$b" ]] && { echo "$b"; return; }
    done
    command -v composer &>/dev/null && { echo "composer"; return; }
    echo ""
}

# ── Detect php-fpm service ────────────────────────────────────────────────────
detect_phpfpm() {
    for svc in php8.3-fpm php8.2-fpm php8.1-fpm php8.0-fpm php-fpm; do
        systemctl list-units --type=service --all 2>/dev/null | grep -q "${svc}.service" \
            && { echo "$svc"; return; }
    done
    echo ""
}

# ── Lock ──────────────────────────────────────────────────────────────────────
exec 200>"${LOCK_FILE}"
flock -n 200 2>/dev/null || die "Another deploy is running. Remove ${LOCK_FILE} to force."
echo $$ > "${LOCK_FILE}"
trap 'rm -f "${LOCK_FILE}" 2>/dev/null; true' EXIT

# ── Resolve binaries ──────────────────────────────────────────────────────────
PHP=$(detect_php)
[[ -z "$PHP" ]] && die "PHP not found. Install: sudo apt-get install php8.2-cli"

COMPOSER=$(detect_composer)
if [[ -z "$COMPOSER" ]]; then
    log_warn "Composer not found — installing"
    TMP=$(mktemp /tmp/composer-XXXX.php)
    curl -fsSL https://getcomposer.org/installer -o "$TMP" 2>/dev/null \
        || wget -qO "$TMP" https://getcomposer.org/installer \
        || die "Cannot download Composer installer (no curl/wget)"
    "$PHP" "$TMP" --install-dir=/usr/local/bin --filename=composer 2>&1 | tail -3
    rm -f "$TMP"
    COMPOSER=$(detect_composer)
    [[ -z "$COMPOSER" ]] && die "Composer install failed"
fi

log_info "PHP: $($PHP -r 'echo PHP_VERSION;')  Composer: $($PHP $COMPOSER --version 2>/dev/null | head -1)"

# ── Validate app dir ──────────────────────────────────────────────────────────
[[ -d "$APP_DIR" ]]     || die "App directory not found: $APP_DIR"
[[ -f "$APP_DIR/artisan" ]] || die "Not a Laravel app: $APP_DIR/artisan missing"
[[ -f "$APP_DIR/.env" ]] || die ".env not found: $APP_DIR/.env"

cd "$APP_DIR"
ARTISAN="$PHP $APP_DIR/artisan"

# ── Validate git repo ─────────────────────────────────────────────────────────
git -C "$APP_DIR" rev-parse --git-dir &>/dev/null \
    || die "$APP_DIR is not a git repository"

log_info "Deploy → $APP_DIR  branch: $BRANCH"
log_info "Current: $(git -C "$APP_DIR" rev-parse --short HEAD 2>/dev/null || echo 'unknown')"

# ── DB connectivity check ─────────────────────────────────────────────────────
log_info "Checking database connection"
if ! $ARTISAN db:monitor --no-interaction &>/dev/null 2>&1; then
    # Fallback: try tinker-less check via artisan
    $PHP -r "
        \$e = parse_ini_file('${APP_DIR}/.env');
        \$drv = \$e['DB_CONNECTION'] ?? 'mysql';
        \$host = \$e['DB_HOST'] ?? '127.0.0.1';
        \$port = \$e['DB_PORT'] ?? (\$drv === 'pgsql' ? 5432 : 3306);
        \$db   = \$e['DB_DATABASE'] ?? '';
        \$user = \$e['DB_USERNAME'] ?? '';
        \$pass = \$e['DB_PASSWORD'] ?? '';
        try {
            new PDO(\"\$drv:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
            echo 'ok';
        } catch(Exception \$ex) { echo 'fail: '.\$ex->getMessage(); exit(1); }
    " 2>/dev/null | grep -q "^ok" \
        || die "Database not reachable — check DB_* in $APP_DIR/.env"
fi
log_ok "Database reachable"

# ── Maintenance mode ON ───────────────────────────────────────────────────────
log_info "Enabling maintenance mode"
$ARTISAN down --render="errors.503" --retry=30 2>/dev/null \
    || log_warn "Could not enable maintenance mode (may already be down)"

# Auto-restore on failure
cleanup() {
    local code=$?
    rm -f "$LOCK_FILE" 2>/dev/null || true
    [[ $code -ne 0 ]] && {
        echo -e "${RED}$(_ts) [ERROR]${NC} Deploy failed — bringing app back online" >&2
        $ARTISAN up 2>/dev/null || true
    }
}
trap cleanup EXIT

# ── STEP 1: Git pull ──────────────────────────────────────────────────────────
log_info "Pulling $BRANCH"
git -C "$APP_DIR" fetch origin "$BRANCH" 2>&1 || die "git fetch failed"
git -C "$APP_DIR" reset --hard "origin/$BRANCH" 2>&1 || die "git reset failed"
NEW_COMMIT=$(git -C "$APP_DIR" rev-parse --short HEAD)
log_ok "Code updated → $NEW_COMMIT"

# ── STEP 2: Composer ──────────────────────────────────────────────────────────
log_info "Installing dependencies"
"$PHP" "$COMPOSER" install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist \
    --working-dir="$APP_DIR" \
    2>&1 || die "composer install failed"
log_ok "Composer done"

# ── STEP 3: Storage & permissions ─────────────────────────────────────────────
for d in storage/app/public storage/framework/{cache,sessions,views} storage/logs bootstrap/cache; do
    mkdir -p "$APP_DIR/$d"
done
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
safe_sudo chown -R www-data:www-data \
    "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null \
    || chown -R "$(whoami):$(whoami)" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null \
    || log_warn "Could not chown storage — run: sudo chown -R www-data:www-data $APP_DIR/storage"

# ── STEP 4: Migrate ───────────────────────────────────────────────────────────
PENDING=$($ARTISAN migrate:status --no-interaction 2>/dev/null | grep -c "Pending" || true)
if [[ "${PENDING:-0}" -gt 0 ]]; then
    log_info "Running $PENDING pending migration(s)"
    $ARTISAN migrate --force --no-interaction 2>&1 \
        || die "Migrations failed — app is in maintenance mode. Fix and re-run."
    log_ok "Migrations complete"
else
    log_info "No pending migrations"
fi

# ── STEP 5: Optimize ──────────────────────────────────────────────────────────
log_info "Clearing caches"
$ARTISAN optimize:clear --no-interaction 2>/dev/null || true

log_info "Rebuilding caches"
$ARTISAN config:cache  --no-interaction || die "config:cache failed"
$ARTISAN route:cache   --no-interaction || log_warn "route:cache failed — non-fatal"
$ARTISAN view:cache    --no-interaction || log_warn "view:cache failed — non-fatal"
$ARTISAN event:cache   --no-interaction 2>/dev/null || true
$ARTISAN icons:cache   --no-interaction 2>/dev/null || true
$ARTISAN storage:link  --no-interaction 2>/dev/null || true
$ARTISAN queue:restart --no-interaction 2>/dev/null || true

# OPcache reset
"$PHP" -r "if(function_exists('opcache_reset')){opcache_reset();}" 2>/dev/null || true
log_ok "Caches rebuilt"

# ── STEP 6: Boot check ────────────────────────────────────────────────────────
$PHP "$APP_DIR/artisan" about --no-interaction &>/dev/null \
    || die "App failed to boot after deploy — check config/provider errors"

# ── STEP 7: Maintenance mode OFF ─────────────────────────────────────────────
$ARTISAN up
log_ok "App online"

# ── STEP 8: Reload services ───────────────────────────────────────────────────
FPM=$(detect_phpfpm)
if [[ -n "$FPM" ]]; then
    if systemctl is-active --quiet "$FPM" 2>/dev/null; then
        safe_sudo systemctl reload "$FPM" 2>/dev/null \
            && log_ok "php-fpm reloaded ($FPM)" \
            || log_warn "php-fpm reload failed — workers may serve stale bytecode"
    else
        log_warn "php-fpm ($FPM) not active"
    fi
else
    log_warn "php-fpm service not detected — skip reload"
fi

if command -v nginx &>/dev/null && systemctl is-active --quiet nginx 2>/dev/null; then
    nginx -t -q 2>/dev/null \
        && safe_sudo systemctl reload nginx 2>/dev/null && log_ok "nginx reloaded" \
        || log_warn "nginx reload skipped (config test failed)"
fi

if command -v supervisorctl &>/dev/null; then
    safe_sudo supervisorctl reread 2>/dev/null || true
    safe_sudo supervisorctl update 2>/dev/null || true
    safe_sudo supervisorctl restart expenseflow-worker: 2>/dev/null \
        && log_ok "Queue workers restarted" \
        || log_warn "Supervisor worker restart skipped (group may not exist)"
fi

# ── Summary ───────────────────────────────────────────────────────────────────
DURATION=$(( $(date +%s) - DEPLOY_START ))
log_ok "Deploy complete — commit: $NEW_COMMIT  time: ${DURATION}s"
APP_URL=$(grep -E '^APP_URL=' "$APP_DIR/.env" 2>/dev/null | cut -d= -f2- | tr -d '"' | xargs 2>/dev/null || true)
[[ -n "$APP_URL" ]] && echo "  $APP_URL"
