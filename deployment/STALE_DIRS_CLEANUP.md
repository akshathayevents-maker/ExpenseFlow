# Stale `current/`, `releases/`, `shared/`, `repo/` — prove unused, then quarantine (separate from any deploy)

They are leftovers of the retired release-based design (`current -> releases/20260519172525`). The deploy scripts **never
touch them**. Do not delete anything until step 1 proves they are unused. All of step 1 is read-only.

## 1. Prove they are unused (run on the server, paste the output if unsure)
```bash
cd /var/www/akshathayexpense
readlink -f current; ls -la current releases shared repo 2>&1 | head -40

# a) Apache: no vhost/alias/directory references them
grep -RnE 'akshathayexpense/(current|releases|shared|repo)' /etc/apache2/ 2>/dev/null || echo "apache: no reference"
apache2ctl -S 2>/dev/null | head -20

# b) supervisor, cron, systemd: no reference
grep -RnE 'akshathayexpense/(current|releases|shared|repo)' /etc/supervisor/ /etc/cron* /var/spool/cron /etc/systemd/system 2>/dev/null || echo "supervisor/cron/systemd: no reference"

# c) no running process has them as cwd / open files
for p in $(pgrep -f 'php|apache2|supervisord'); do readlink /proc/$p/cwd 2>/dev/null; done | sort | uniq -c
lsof +D /var/www/akshathayexpense/releases /var/www/akshathayexpense/shared /var/www/akshathayexpense/repo 2>/dev/null | head || true

# d) not read/written recently (access times can be noatime — treat as a hint, not proof)
find current releases shared repo -maxdepth 3 -newermt '-14 days' 2>/dev/null | head

# e) the web root really is the flat tree (definitive)
curl -s https://expense.akshathay.com/build/manifest.json | sha256sum ; sha256sum public/build/manifest.json
sha256sum releases/*/public/build/manifest.json 2>/dev/null       # must NOT equal the live hash
```
Unused = (a),(b),(c) show no reference, (d) shows nothing recent, and (e) live hash == flat tree but != any release.
Do **not** print `shared/.env` (secrets). If `shared/.env` differs from `/var/www/akshathayexpense/.env`, it is an old copy.

## 2. Snapshot (so quarantine is reversible and reviewable)
```bash
ls -laR current releases shared repo > /root/stale-dirs-listing-$(date +%F).txt 2>&1
du -sh current releases shared repo
```

## 3. Quarantine — rename, do not delete (same filesystem: instant and reversible)
```bash
Q=/var/www/_expenseflow_stale_$(date +%F); mkdir "$Q"
mv /var/www/akshathayexpense/current  "$Q/"     # symlink
mv /var/www/akshathayexpense/releases "$Q/"
mv /var/www/akshathayexpense/shared   "$Q/"
mv /var/www/akshathayexpense/repo     "$Q/"
bash /var/www/akshathayexpense/deployment/deploy.sh main --check    # still PREFLIGHT PASSED
bash /var/www/akshathayexpense/deployment/health-check.sh           # still HEALTHY
```
Undo at any time: `mv "$Q"/* /var/www/akshathayexpense/`.

## 4. Delete only after ≥ 14 days with no problems — explicit human decision
`rm -rf "$Q"` (only that quarantine directory). Also review `deployment/deploy.sh.bak.*` (old script, quarantine it too).
Keep `/var/log/expenseflow-deploy/` and database backups.
