#!/usr/bin/env bash
# ExpenseFlow — code rollback for the flat deployment.
#
# Usage: sudo bash deployment/rollback.sh [--list] [--to <sha>] [--yes] [--skip-system-checks] [--no-http-health]
#   (no --to)  redeploy the commit that was live BEFORE the last successful deploy (from the deploy history).
#
# It re-runs the exact same pipeline as deploy.sh (same lock, same checks, maintenance rules, composer, caches,
# reload, workers, health check) for an older commit, so a rollback is as safe as a deploy.
# LIMITS (flat layout, be aware): (1) not atomic — with composer/migration changes the app is briefly in
# maintenance mode; (2) database migrations are NEVER reverted: if the deploy you are undoing added migrations,
# confirm the older code is compatible with the current schema, or restore the pre-deploy DB backup.
# Nothing is deleted. History: /var/log/expenseflow-deploy/history.tsv
set -Eeuo pipefail
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
# shellcheck source=lib.sh
source "$SCRIPT_DIR/lib.sh"

TO=""; LIST=0; YES=0; PASS=()
while [[ $# -gt 0 ]]; do
    case "$1" in
        --list) LIST=1; shift ;;
        --to)   TO="${2:?--to needs a commit SHA}"; shift 2 ;;
        --yes|-y) YES=1; shift ;;
        --skip-system-checks|--no-http-health) PASS+=("$1"); shift ;;
        -h|--help) sed -n 2,12p "${BASH_SOURCE[0]}"; exit 0 ;;
        *) echo "Unknown argument: $1" >&2; exit 2 ;;
    esac
done
export GIT_CONFIG_COUNT=1 GIT_CONFIG_KEY_0=safe.directory GIT_CONFIG_VALUE_0="$EF_APP_DIR"
ignore_job_control_signals   # inherited by the deploy.sh we exec below: no Ctrl+Z window even during the prompt
if [[ $EUID -ne 0 && "${EF_ALLOW_NONROOT:-0}" != "1" ]]; then echo "Run as root: sudo bash deployment/rollback.sh" >&2; exit 1; fi

CUR="$(git -C "$EF_APP_DIR" rev-parse HEAD)"
if [[ $LIST -eq 1 ]]; then
    echo "Current HEAD: ${CUR:0:7}"
    echo "Deploy history (newest last): date | from | to | branch | user | kind"
    if [[ -r "$HISTORY_FILE" ]]; then tail -n 15 "$HISTORY_FILE" | awk -F'\t' '{printf "  %s | %.7s | %.7s | %s | %s | %s\n",$1,$2,$3,$4,$5,$6}'; else echo "  (no history yet)"; fi
    echo "Recent commits on this tree:"; git -C "$EF_APP_DIR" log --oneline -n 8 | sed 's/^/  /'
    exit 0
fi

if [[ -z "$TO" ]]; then
    [[ -r "$HISTORY_FILE" ]] || die "No deploy history at $HISTORY_FILE — pass --to <sha> explicitly (see --list)"
    # last successful record whose destination is the current HEAD -> its source is the commit to restore
    TO="$(awk -F'\t' -v cur="$CUR" '$3==cur {p=$2} END{print p}' "$HISTORY_FILE")"
    [[ -n "$TO" ]] || die "History has no record that led to the current HEAD ${CUR:0:7} — pass --to <sha>"
fi
TO_FULL="$(git -C "$EF_APP_DIR" rev-parse --verify "$TO^{commit}" 2>/dev/null)" || die "Commit $TO is not in the local repository"
[[ "$TO_FULL" != "$CUR" ]] || die "Target ${TO_FULL:0:7} is already the current HEAD"

echo "Current : ${CUR:0:7}  $(git -C "$EF_APP_DIR" log -1 --format=%s "$CUR" | cut -c1-70)"
echo "Rollback: ${TO_FULL:0:7}  $(git -C "$EF_APP_DIR" log -1 --format=%s "$TO_FULL" | cut -c1-70)"
if [[ $YES -ne 1 ]]; then
    [[ -t 0 ]] || die "Not a terminal: pass --yes to confirm non-interactively"
    read -r -p "Roll back code to ${TO_FULL:0:7}? Database migrations are NOT reverted. [y/N] " ans
    [[ "$ans" == "y" || "$ans" == "Y" ]] || { echo "Aborted."; exit 1; }
fi
exec bash "$SCRIPT_DIR/deploy.sh" --commit "$TO_FULL" --force --rollback-mode "${PASS[@]}"
