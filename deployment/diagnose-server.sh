#!/usr/bin/env bash
# ExpenseFlow — READ-ONLY production architecture diagnosis.
# Changes nothing: no writes, no restarts, no chown, no artisan commands that write.
# Usage:  sudo bash deployment/diagnose-server.sh [APP_DIR] [DOMAIN]
# Standalone: it needs nothing from the git tree. Works from the DigitalOcean web console when SSH is unavailable:
#   nano /tmp/ef-diagnose.sh   (paste this file)   then   sudo bash /tmp/ef-diagnose.sh 2>&1 | tee /tmp/ef-diagnosis.txt
# (tee writes only that one report file under /tmp; delete it afterwards. Nothing else is written.)
set -uo pipefail
# Read-only guarantees: no git index refresh (--no-optional-locks), no artisan/composer/npm,
# no writes to the app tree, no lock creation, no service restarts, .env values never printed.
GIT="git --no-optional-locks"
# root inspecting a www-data-owned repo trips "dubious ownership"; env config is per-process only, nothing is persisted
export GIT_CONFIG_COUNT=1 GIT_CONFIG_KEY_0=safe.directory GIT_CONFIG_VALUE_0='*'
DOMAIN="${2:-expense.akshathay.com}"

APP_DIR="${1:-/var/www/akshathayexpense}"
LOCK_FILE="${EF_LOCK_FILE:-/var/lock/expenseflow-deploy.lock}"   # same file lib.sh locks (never /tmp)
LOG_DIR="${EF_LOG_DIR:-/var/log/expenseflow-deploy}"

h() { printf '\n==================== %s ====================\n' "$*"; }
# Every line is passed through a redaction filter: cron/supervisor/process listings can contain passwords or tokens
# (e.g. PGPASSWORD=... in a backup job) and this output is meant to be pasted into tickets/chats.
_redact() { sed -E 's#(://)[^/@[:space:]]*@#\1***@#g; s#((token|password|passwd|secret|api[_-]?key|PGPASSWORD|APP_KEY|DB_PASSWORD)[=:][[:space:]]*)[^[:space:];&]+#\1***#Ig'; }
run() { printf '\n$ %s\n' "$*"; bash -c "$*" 2>&1 | _redact | head -n "${MAXLINES:-80}" || true; }

h "0. Identity / time"
run "id; date; hostname; uname -r"

h "A1. Which web server serves traffic? (production = Apache; anything else active is a finding)"
run "ss -ltnp 2>/dev/null | grep -E ':(80|443)\b'"
run "apachectl -S 2>&1 | head -20; grep -rnE 'DocumentRoot|SetHandler|ProxyPassMatch' /etc/apache2/sites-enabled/ 2>/dev/null"

h "A1b. Apache (production evidence: response header 'Server: Apache/2.4.58 (Ubuntu)') — vhosts, DocumentRoot, PHP handler"
run "apache2ctl -S 2>&1 | head -25"
run "apache2ctl -M 2>&1 | grep -E 'php|proxy_fcgi|proxy_module|rewrite|ssl|headers|mpm_'"
MAXLINES=120 run "grep -RnE '^\\s*(ServerName|ServerAlias|DocumentRoot|<Directory|AllowOverride|Options|Require|SetHandler|ProxyPassMatch|php_admin_value|SSLCertificateFile)' /etc/apache2/sites-enabled/ /etc/apache2/conf-enabled/ 2>/dev/null | grep -v SSLCertificateKeyFile"
run "ps -eo user,comm | grep -E '[a]pache2|[h]ttpd' | sort | uniq -c"
run "apache2ctl -V 2>&1 | grep -E 'Server MPM|SERVER_CONFIG_FILE|HTTPD_ROOT'"
run "php -r 'echo PHP_SAPI, \" \", PHP_VERSION, \"\\n\";'; ls /etc/php/*/apache2/php.ini 2>&1 | head -3; grep -E '^(opcache.enable|opcache.validate_timestamps|opcache.revalidate_freq|realpath_cache_ttl)' /etc/php/*/apache2/php.ini /etc/php/*/apache2/conf.d/*opcache* 2>/dev/null | head"

h "S0. WHICH MACHINE AM I ON? (the site expense.akshathay.com resolves to 168.144.117.206 — SSH must target that address)"
run "hostname; ip -4 -o addr show scope global | awk '{print \$2, \$4}'"
run "curl -s --max-time 4 http://169.254.169.254/metadata/v1/interfaces/public/0/ipv4/address; echo '  <- DigitalOcean metadata: this droplet public IPv4'"
run "curl -s --max-time 4 http://169.254.169.254/metadata/v1/id; echo '  <- droplet id'"
run "getent hosts expense.akshathay.com"

h "A2. Symlinks and layout under $APP_DIR"
run "ls -la $APP_DIR"
run "readlink -f $APP_DIR/current; readlink -f $APP_DIR/public; readlink -f $APP_DIR/.env; readlink -f $APP_DIR/current/.env"
run "ls -la $APP_DIR/releases $APP_DIR/shared $APP_DIR/repo 2>&1 | head -60"
run "du -sh $APP_DIR/releases $APP_DIR/shared $APP_DIR/repo $APP_DIR/current/ 2>&1"
run "stat -c '%n  mtime=%y  owner=%U:%G  mode=%a' $APP_DIR $APP_DIR/vendor $APP_DIR/current $APP_DIR/current/vendor $APP_DIR/.env $APP_DIR/storage $APP_DIR/bootstrap/cache $APP_DIR/public/build 2>&1"

h "A3. Which directory do the LIVE processes actually run from? (cwd of fpm + workers)"
run "for p in \$(pgrep -f 'php-fpm: (master|pool)' | head -5) \$(pgrep -f 'artisan queue:work' | head -6) \$(pgrep -f 'artisan schedule' | head -2); do echo \"pid=\$p user=\$(ps -o user= -p \$p) cwd=\$(readlink /proc/\$p/cwd) cmd=\$(ps -o args= -p \$p | cut -c1-110)\"; done"

h "F. PHP-FPM: what is installed, running, which pool/socket/user"
run "systemctl list-units --type=service --all --no-legend 2>/dev/null | grep -i fpm"
run "systemctl list-unit-files --no-legend 2>/dev/null | grep -i fpm"
run "ps -eo pid,user,args | grep -E '[p]hp-fpm'"
run "ls -l /run/php/ 2>&1"
run "grep -RnE '^\s*(\[|user|group|listen|listen.owner|pm\b|chdir)' /etc/php/*/fpm/pool.d/*.conf 2>/dev/null | head -40"
run "php -v | head -1; ls /usr/bin/php* 2>&1 | tr '\n' ' '"
run "php -r 'echo \"opcache.validate_timestamps=\", ini_get(\"opcache.validate_timestamps\"), \"  revalidate_freq=\", ini_get(\"opcache.revalidate_freq\"), \"\n\";'"

h "O. Supervisor: programs, users, directories"
run "supervisorctl status 2>&1"
run "ls -l /etc/supervisor/conf.d/ 2>&1"
run "grep -RnE '^\s*(\[program|\[group|command|directory|user|numprocs|process_name|stdout_logfile)' /etc/supervisor/conf.d/ 2>/dev/null"

h "B. Git: where does the repo live, is the tree dirty and why"
run "$GIT -C $APP_DIR rev-parse --show-toplevel --short HEAD 2>&1"
run "$GIT -C $APP_DIR remote -v | sed -E 's#(://)[^/@ ]*@#\\1***@#'; $GIT -C $APP_DIR config --get core.fileMode"
run "$GIT -C $APP_DIR status --short | head -40"
# Distinguish real content edits from pure permission-bit changes
run "$GIT -C $APP_DIR diff --numstat | head -20"
run "$GIT -C $APP_DIR -c core.fileMode=true diff --summary | head -20"
run "$GIT -C $APP_DIR ls-files storage/fonts | head -5"
run "$GIT -C $APP_DIR ls-files public/build | head; ls -la $APP_DIR/public/build | head"
run "for d in $APP_DIR/current $APP_DIR/releases/* ; do [ -e \$d/.git ] && echo \"\$d has .git: \$($GIT -C \$d rev-parse --short HEAD 2>&1)\"; done"

h "C/D. Maintenance mode + icons command (read-only checks)"
run "ls -la $APP_DIR/resources/views/errors/"
run "ls -la $APP_DIR/storage/framework/down 2>&1"
run "grep -n 'icons' $APP_DIR/deployment/deploy.sh $APP_DIR/composer.json 2>&1"
run "grep -c 'blade-ui-kit\\|blade-icons' $APP_DIR/composer.lock 2>&1   # 0 = no icons package installed"
run "sha256sum $APP_DIR/deployment/deploy.sh; $GIT -C $APP_DIR log -1 --format='deploy.sh last changed in %h %ci' -- deployment/deploy.sh"

h "E. storage link state"
run "ls -ld $APP_DIR/public/storage $APP_DIR/current/public/storage 2>&1; readlink -f $APP_DIR/public/storage"

h "G/N. Ownership and users"
run "ps -eo user,args | grep -E '[p]hp-fpm: pool' | awk '{print \$1}' | sort | uniq -c"
run "ls -ld ~/.composer ~/.cache/composer /root/.cache/composer /var/www/.cache/composer /var/www/.composer 2>&1"
run "find $APP_DIR -maxdepth 2 \\( -path $APP_DIR/node_modules -o -path $APP_DIR/releases -o -path $APP_DIR/.git \\) -prune -o -printf '%u:%g %m %p\n' 2>/dev/null | sort | awk '{print \$1}' | uniq -c"
run "find $APP_DIR/storage $APP_DIR/bootstrap/cache -not -user www-data -printf '%u:%g %m %p\n' 2>/dev/null | head -15"
run "find $APP_DIR/vendor -maxdepth 2 -not -user www-data -printf '%u:%g %p\n' 2>/dev/null | head -10"
run "find $APP_DIR/storage -type f -perm /111 2>/dev/null | wc -l   # executable regular files (chmod -R 775 side effect)"

h "H. Lock: is anything REALLY holding it? (a stale file alone means nothing)"
run "ls -l $LOCK_FILE $LOCK_FILE.info 2>&1; cat $LOCK_FILE.info 2>&1   # the lock file itself is empty; .info names the last holder"
run "( exec 9<$LOCK_FILE && flock -n -s 9 && echo 'lock is FREE' || echo 'lock is HELD' ) 2>&1   # read-only probe: holds nothing, creates nothing"
run "ls -l $LOG_DIR/deploy-in-progress 2>&1; cat $LOG_DIR/deploy-in-progress 2>&1   # present = a deploy was interrupted (kill -9/OOM/power)"
run "tail -n 5 $LOG_DIR/history.tsv 2>&1"
run "command -v flock fuser lsof"
run "fuser -v $LOCK_FILE 2>&1"
run "lsof $LOCK_FILE 2>&1 | head"
run "ps -eo pid,ppid,stat,etime,user,args | grep -E '[d]eploy\\.sh|[c]omposer|[a]rtisan (down|migrate|up)' "
run "ps -eo pid,stat,args | awk '\$2 ~ /T/ {print}'   # STOPPED (Ctrl+Z) processes"

h "A4. DEFINITIVE: is the flat tree what Apache serves? (no writes)"
run "for d in $APP_DIR $APP_DIR/current; do echo \"== \$d\"; stat -c 'index.php inode=%i mtime=%y' \$d/public/index.php 2>&1; sha256sum \$d/public/build/manifest.json 2>&1; $GIT -C \$d rev-parse --short HEAD 2>&1 | head -1; done"
run "echo 'Expected manifest sha256 (commit 7068cb0): fda9a21219b0c309f0939c8f229924f72e154ef9a7eb0ebdb8c85839ef28b07a'"
# Static files are served by Apache alone (no PHP, no session). 200 => that file exists under the SERVED root.
run "for f in app-BT9g_1j6.css app-DXs_tY9g.js event-request-BDWyu1xg.css event-request-public-CcvHkBaB.js; do printf '%s ' \$f; curl -sk -o /dev/null -I -w '%{http_code}\\n' --resolve $DOMAIN:443:127.0.0.1 https://$DOMAIN/build/assets/\$f; done"
run "curl -sk -o /dev/null -I -w 'manifest.json -> %{http_code}\\n' --resolve $DOMAIN:443:127.0.0.1 https://$DOMAIN/build/manifest.json"
run "curl -sk -I --resolve $DOMAIN:443:127.0.0.1 https://$DOMAIN/build/manifest.json | grep -iE '^(last-modified|etag|content-length)'"
run "curl -sk -o /dev/null -w 'GET /up -> %{http_code}\\n' --resolve $DOMAIN:443:127.0.0.1 https://$DOMAIN/up"

h "K/L. Build assets and app health (read-only)"
run "ls -la $APP_DIR/public/build $APP_DIR/public/build/assets 2>&1 | head -20"
run "php -r '\$m=json_decode(file_get_contents(\"$APP_DIR/public/build/manifest.json\"),true); foreach(\$m as \$k=>\$v){echo (is_file(\"$APP_DIR/public/build/\".\$v[\"file\"])?\"OK  \":\"MISSING \"), \$v[\"file\"], PHP_EOL;}' 2>&1"
run "stat -c '.env path=%n owner=%U:%G mode=%a size=%s' $APP_DIR/.env $APP_DIR/current/.env $APP_DIR/shared/.env 2>&1"
run "grep -E '^(APP_URL|APP_ENV|APP_DEBUG|QUEUE_CONNECTION|SESSION_DRIVER|DB_CONNECTION)=' $APP_DIR/.env 2>&1   # non-secret keys only"

h "M. Cron"
run "crontab -l 2>&1; ls /etc/cron.d/; cat /etc/cron.d/expenseflow 2>&1"

h "A5. Old release-based leftovers (informational only — NOT used by production)"
run "readlink -f $APP_DIR/current; cat $APP_DIR/current/REVISION 2>&1; ls -1d $APP_DIR/releases/* 2>&1 | tail -8"
run "for r in \$(ls -1d $APP_DIR/releases/* 2>/dev/null | tail -3); do printf '%s  REVISION=%s  env=%s  storage=%s\n' \$(basename \$r) \"\$(cat \$r/REVISION 2>/dev/null | cut -c1-7)\" \"\$(readlink \$r/.env 2>/dev/null)\" \"\$(readlink \$r/storage 2>/dev/null)\"; done"

h "I/J. Deployment processes and logs"
run "ps -eo pid,ppid,pgid,stat,etime,user,args | grep -E '[d]eploy\\.sh|[r]ollback\\.sh|[h]ealth-check\\.sh|[m]igrate-to-releases|[c]omposer (install|update)'"
run "ls -la $APP_DIR/deploy_logs 2>&1 | tail -8; ls -la /var/log/expenseflow* /var/log/deploy* 2>&1 | head"
run "ls -t $APP_DIR/deploy_logs/*.log 2>/dev/null | head -1 | xargs -r tail -n 25"
run "ls -la $APP_DIR/deployment/ 2>&1 | head -20"

h "L. Cron and systemd triggers that could run deploys/artisan"
run "crontab -l -u root 2>&1 | head -20; ls -la /etc/cron.d/; grep -rn 'artisan\|deploy\|expenseflow' /etc/cron.d/ /etc/cron.daily /etc/cron.hourly 2>/dev/null | head -20"
run "systemctl list-timers --all --no-pager 2>&1 | head -20"
run "systemctl list-unit-files --no-pager 2>&1 | grep -iE 'expense|deploy|laravel'"
run "grep -rln 'deploy.sh\|expenseflow' /etc/systemd/system /lib/systemd/system 2>/dev/null | head"

h "M. Disk, memory, load (exhaustion can make SSH and composer hang)"
run "uptime; free -m; df -hP / /var /tmp 2>&1; df -iP / 2>&1 | tail -2"
run "ps -eo pid,user,%mem,%cpu,etime,args --sort=-%mem | head -8 | cut -c1-140"
run "journalctl -k --no-pager 2>/dev/null | grep -iE 'out of memory|oom-kill|killed process' | tail -5"
run "swapon --show 2>&1; cat /proc/pressure/memory 2>&1 | head -2"

h "SSH connectivity diagnosis (read-only; SSH configuration is NOT modified)"
run "systemctl status ssh sshd --no-pager -l 2>&1 | head -25"
run "ss -ltnp 2>/dev/null | grep -E ':22\\b'"
run "ufw status verbose 2>&1 | head -15; iptables -S 2>&1 | head -12; nft list ruleset 2>&1 | head -12"
run "sshd -T 2>&1 | grep -iE '^(port|maxstartups|maxsessions|logingracetime|permitrootlogin|passwordauthentication|pubkeyauthentication|usedns|clientaliveinterval|clientalivecountmax|allowusers|allowgroups) '"
run "printf 'established ssh conns: '; ss -tn state established '( sport = :22 )' 2>/dev/null | tail -n +2 | wc -l; printf 'sshd processes: '; pgrep -c sshd; echo 'pre-auth/unauthenticated (MaxStartups pressure):'; ps -eo pid,etime,args | grep -E '[s]shd: .*(\\[accepted\\]|\\[priv\\]|unknown)' | head -15"
run "who; echo; w -h 2>&1 | head; echo; last -n 8 2>&1"
run "journalctl -u ssh -u sshd --no-pager -n 40 2>&1 | tail -40"
run "grep -E 'sshd' /var/log/auth.log 2>/dev/null | tail -20"
run "systemctl is-active fail2ban 2>&1; fail2ban-client status sshd 2>&1 | head -10"
run "ps -eo pid,ppid,stat,etime,args | awk '\$3 ~ /^D/ {print}' | head   # uninterruptible (D) processes: stuck I/O can wedge logins"
run "echo 'Login/PAM slowness check (DNS lookups on connect):'; grep -RnE '^\\s*(UseDNS|UsePAM|GSSAPIAuthentication)' /etc/ssh/sshd_config /etc/ssh/sshd_config.d/ 2>/dev/null"

h "DONE — this script changed nothing."
