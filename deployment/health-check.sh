#!/usr/bin/env bash
# ExpenseFlow — post-deploy / on-demand health check for the flat deployment.
# Read-only: it inspects and requests; it never modifies the app, services or config.
#
# Usage: health-check.sh [--expect-commit SHA] [--http-base URL] [--host HOST] [--wait-workers SECS]
#                        [--workers-since EPOCH] [--no-http] [--no-system]
# Exit:  0 = healthy, 1 = at least one FAIL (WARN/SKIP do not fail).
#
# HTTP checks go to the LOCAL web server (127.0.0.1:443 with the site's Host header), so they do not depend on
# DNS, a CDN, or any external service being reachable from the server.
set -Eeuo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=lib.sh
source "$SCRIPT_DIR/lib.sh"

EXPECT_COMMIT=""; HTTP_BASE="${EF_HEALTH_HTTP_BASE:-}"; HOST_HDR=""; WAIT_WORKERS=0; WORKERS_SINCE=0; DO_HTTP=1; DO_SYSTEM=1
while [[ $# -gt 0 ]]; do
    case "$1" in
        --expect-commit)  EXPECT_COMMIT="${2:?}"; shift 2 ;;
        --http-base)      HTTP_BASE="${2:?}"; shift 2 ;;
        --host)           HOST_HDR="${2:?}"; shift 2 ;;
        --wait-workers)   WAIT_WORKERS="${2:?}"; shift 2 ;;
        --workers-since)  WORKERS_SINCE="${2:?}"; shift 2 ;;
        --no-http)        DO_HTTP=0; shift ;;
        --no-system)      DO_SYSTEM=0; shift ;;
        -h|--help)        sed -n 2,12p "${BASH_SOURCE[0]}"; exit 0 ;;
        *) echo "Unknown argument: $1" >&2; exit 2 ;;
    esac
done

APP="$EF_APP_DIR"
export GIT_CONFIG_COUNT=1 GIT_CONFIG_KEY_0=safe.directory GIT_CONFIG_VALUE_0="$APP"
PASS=0; FAILS=0; WARNS=0
pass() { PASS=$((PASS+1));   printf '  %s✓%s %s\n' "$GRN" "$NC" "$*"; }
fail() { FAILS=$((FAILS+1)); printf '  %s✗ FAIL%s %s\n' "$RED" "$NC" "$*"; }
wrn()  { WARNS=$((WARNS+1)); printf '  %s⚠ WARN%s %s\n' "$YEL" "$NC" "$*"; }
skp()  { printf '  %s- SKIP%s %s\n' "$CYN" "$NC" "$*"; }

PHP="$(detect_php || true)"
echo "ExpenseFlow health check — app: $APP"

# 1. Commit
if [[ -f "$APP/artisan" ]]; then pass "app directory present"; else fail "no Laravel app at $APP"; echo "UNHEALTHY"; exit 1; fi
HEAD_SHA="$(timeout -k 2 30 git -C "$APP" rev-parse HEAD 2>/dev/null </dev/null || true)"
if [[ -z "$HEAD_SHA" ]]; then wrn "cannot read git HEAD"
elif [[ -n "$EXPECT_COMMIT" && "$HEAD_SHA" != "$EXPECT_COMMIT" ]]; then fail "HEAD is ${HEAD_SHA:0:7}, expected ${EXPECT_COMMIT:0:7}"
else pass "commit ${HEAD_SHA:0:7}"; fi

# 2. Links / files
if [[ -r "$APP/.env" ]]; then pass ".env readable"; else fail ".env missing/unreadable"; fi
if [[ -L "$APP/public/storage" && -d "$APP/public/storage" ]]; then pass "public/storage link resolves"; else fail "public/storage link missing or broken"; fi

# 3. Build assets (committed public/build)
MANIFEST="$APP/public/build/manifest.json"
if [[ -r "$MANIFEST" ]]; then
    MISSING="$(manifest_missing "$APP")"
    if [[ -z "$MISSING" ]]; then pass "public/build/manifest.json and all referenced assets exist"; else fail "build manifest problem: $MISSING"; fi
else
    fail "public/build/manifest.json missing"
fi

# 4. Laravel boots, DB reachable, caches present, not in maintenance
if [[ -n "$PHP" ]]; then
    if OUT="$(timeout -k 5 "${EF_HEALTH_ARTISAN_TIMEOUT:-60}" "${APP_RUN[@]}" "$PHP" "$APP/artisan" migrate:status --pending --no-interaction </dev/null 2>&1)"; then
        pass "Laravel boots and database is reachable"
        if [[ "$OUT" != *"No pending migrations"* ]]; then wrn "there are pending migrations"; fi
    else
        fail "artisan/database check failed: $(echo "$OUT" | tail -n 2 | tr '\n' ' ')"
    fi
fi
if [[ -f "$APP/bootstrap/cache/config.php" ]]; then pass "config cache present"; else fail "config cache missing (bootstrap/cache/config.php)"; fi
if compgen -G "$APP/bootstrap/cache/routes-*.php" >/dev/null; then pass "route cache present"; else fail "route cache missing"; fi
if [[ -e "$APP/storage/framework/down" ]]; then fail "application is in MAINTENANCE mode (storage/framework/down exists)"; else pass "not in maintenance mode"; fi

# 5. HTTP through the local web server
if [[ $DO_HTTP -eq 1 ]]; then
    if ! command -v curl >/dev/null 2>&1; then
        wrn "curl not installed; HTTP checks skipped"
    else
        APP_URL="$(env_value "$APP/.env" APP_URL)"
        if [[ -z "$HTTP_BASE" ]]; then
            HOST_HDR="${HOST_HDR:-$(printf '%s' "$APP_URL" | sed -E 's#^[a-z]+://([^/:]+).*#\1#')}"
            if [[ -z "$HOST_HDR" ]]; then fail "cannot determine host: APP_URL empty (use --host/--http-base)"; fi
            HTTP_BASE="https://$HOST_HDR"
            CURL_OPTS=(-sk --max-time 15 --resolve "$HOST_HDR:443:127.0.0.1")
        else
            CURL_OPTS=(-s --max-time 15)
            if [[ -n "$HOST_HDR" ]]; then CURL_OPTS+=(-H "Host: $HOST_HDR"); fi
        fi
        code() { local c; c="$(curl "${CURL_OPTS[@]}" -o /dev/null -w '%{http_code}' "$HTTP_BASE$1" 2>/dev/null || true)"; echo "${c:-000}"; }
        c="$(code /up)";    if [[ "$c" == 200 ]]; then pass "GET /up -> 200"; else fail "GET /up -> $c"; fi
        c="$(code /login)"; if [[ "$c" == 200 || "$c" == 302 ]]; then pass "GET /login -> $c (PHP response)"; else fail "GET /login -> $c"; fi
        c="$(code /build/manifest.json)"
        if [[ "$c" == 200 ]]; then
            served="$(curl "${CURL_OPTS[@]}" "$HTTP_BASE/build/manifest.json" 2>/dev/null | sha256sum | cut -d' ' -f1)"
            local_sha="$(sha256sum "$MANIFEST" | cut -d' ' -f1)"
            if [[ "$served" == "$local_sha" ]]; then pass "served manifest.json matches the deployed tree"; else fail "served manifest.json differs from $APP (the web server serves a different directory?)"; fi
        else
            fail "GET /build/manifest.json -> $c"
        fi
        asset="$(grep -o '"file": *"[^"]*\.css"' "$MANIFEST" 2>/dev/null | head -n1 | sed -E 's/.*"([^"]+)"$/\1/' || true)"
        if [[ -n "$asset" ]]; then c="$(code "/build/$asset")"; if [[ "$c" == 200 ]]; then pass "GET /build/$asset -> 200"; else fail "GET /build/$asset -> $c"; fi; fi
    fi
else
    skp "HTTP checks disabled (--no-http)"
fi

# 6. System services
if [[ $DO_SYSTEM -eq 1 ]]; then
    WS="$(detect_webserver)"
    if [[ "$WS" == "apache2" ]]; then pass "web server active: apache2"; else wrn "apache2 is not active (found: $WS)"; fi
    FPM="$(detect_fpm_units)"
    if [[ -n "$FPM" ]]; then pass "php-fpm active: $(echo "$FPM" | tr '\n' ' ')"; else skp "no php-fpm unit active (PHP via mod_php or none)"; fi
    if command -v supervisorctl >/dev/null 2>&1; then
        deadline=$(( $(date +%s) + WAIT_WORKERS ))
        while :; do
            status="$(privt "${EF_PRIV_TIMEOUT:-60}" supervisorctl status 2>&1 || true)"
            total=0; running=0; stale=0; stale_msg=""
            while read -r name state rest; do
                [[ "$name" == expenseflow* ]] || continue
                total=$((total+1))
                if [[ "$state" == "RUNNING" ]]; then
                    running=$((running+1))
                    pid="${rest#pid }"; pid="${pid%%,*}"
                    if [[ "$WORKERS_SINCE" -gt 0 && "$pid" =~ ^[0-9]+$ ]]; then
                        et="$(ps -o etimes= -p "$pid" 2>/dev/null | tr -d ' ' || true)"
                        if [[ "$et" =~ ^[0-9]+$ ]] && [[ $(( $(date +%s) - et )) -lt $(( WORKERS_SINCE - 1 )) ]]; then stale=$((stale+1)); stale_msg="$name (pid $pid started before this deploy)"; fi
                    fi
                fi
            done <<<"$status"
            if [[ $total -gt 0 && $running -eq $total && $stale -eq 0 ]]; then break; fi
            if [[ $(date +%s) -ge $deadline ]]; then break; fi
            sleep 3
        done
        if [[ $total -eq 0 ]]; then fail "no expenseflow* supervisor programs found"
        elif [[ $running -ne $total ]]; then fail "queue workers: $running/$total RUNNING"
        elif [[ $stale -gt 0 ]]; then fail "queue worker still running pre-deploy code: $stale_msg"
        else pass "queue workers: $running/$total RUNNING"; fi
    else
        wrn "supervisorctl not installed; queue workers not verified"
    fi
else
    skp "system checks disabled (--no-system)"
fi

echo
echo "Result: $PASS passed, $FAILS failed, $WARNS warning(s)"
if [[ $FAILS -gt 0 ]]; then echo "UNHEALTHY"; exit 1; fi
echo "HEALTHY"
