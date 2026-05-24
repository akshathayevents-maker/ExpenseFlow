# ExpenseFlow — Deployment Checklist

## First-time server setup

### 1. Bootstrap server
```bash
# As root on a fresh Ubuntu 24.04 LTS server:
bash bootstrap.sh expenseflow.example.com <strong-db-password> deployer
```
Note the credentials saved to `/root/expenseflow-credentials.txt`.

### 2. Clone bare repository
```bash
# As deployer user:
git clone --bare https://github.com/your-org/expenseflow.git /var/www/expenseflow/repo
```

### 3. Configure production environment
```bash
cp deployment/.env.production.example /var/www/expenseflow/shared/.env
chmod 640 /var/www/expenseflow/shared/.env
chown deployer:www-data /var/www/expenseflow/shared/.env
# Edit and fill in all blank values
nano /var/www/expenseflow/shared/.env
```

**Required values to fill:**
- [ ] `APP_KEY` — run `php artisan key:generate --show` locally and paste
- [ ] `DB_PASSWORD` — from credentials file
- [ ] `MAIL_USERNAME` and `MAIL_PASSWORD` — Brevo SMTP credentials
- [ ] `APP_URL` — set to actual domain

### 4. Nginx
```bash
# Replace domain in config:
sed 's/expenseflow.example.com/yourdomain.com/g' deployment/nginx.conf > /tmp/expenseflow-nginx.conf
cp /tmp/expenseflow-nginx.conf /etc/nginx/sites-available/expenseflow
ln -s /etc/nginx/sites-available/expenseflow /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
```

### 5. SSL certificate
```bash
certbot --nginx -d yourdomain.com
# Auto-renewal is configured by certbot; verify:
systemctl status certbot.timer
```

### 6. Supervisor
```bash
cp deployment/supervisor.conf /etc/supervisor/conf.d/expenseflow.conf
supervisorctl reread
supervisorctl update
supervisorctl status
```

### 7. Cron (Laravel scheduler + DB backup + Certbot renewal)
```bash
# Copy the ready-made cron file (includes scheduler, pg_dump, certbot):
sudo cp deployment/cron.d.expenseflow /etc/cron.d/expenseflow
sudo chown root:root /etc/cron.d/expenseflow
sudo chmod 644 /etc/cron.d/expenseflow

# Create backup dir if bootstrap.sh wasn't run:
sudo mkdir -p /var/backups/expenseflow
sudo chown postgres:postgres /var/backups/expenseflow
sudo chmod 700 /var/backups/expenseflow

# Verify cron is loaded (no error output = good):
run-parts --test /etc/cron.d/
```

### 8. Add deploy SSH key
```bash
# Paste your CI/CD public key:
echo 'ssh-rsa AAAA...' >> /home/deployer/.ssh/authorized_keys
```

### 9. Allow deployer to reload services (sudoers)
```bash
cat > /etc/sudoers.d/expenseflow-deployer << 'EOF'
deployer ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php8.3-fpm
deployer ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload nginx
deployer ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart expenseflow-worker:*
deployer ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart expenseflow-ocr-worker
deployer ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart expenseflow:*
EOF
chmod 440 /etc/sudoers.d/expenseflow-deployer
visudo -c   # verify no syntax error
```

### 10. First deployment
```bash
bash deployment/deploy.sh main
```

---

## Every deployment

### Pre-deploy checks
- [ ] Tests pass on CI
- [ ] `php artisan migrate --pretend` reviewed if schema changes
- [ ] No breaking changes to shared `.env` keys
- [ ] Storage symlink intact: `/var/www/expenseflow/current/public/storage → shared/public/storage`

### Deploy modes

| Command | When to use |
|---------|-------------|
| `bash deployment/deploy.sh` | Full deploy (default — build + migrate + restart) |
| `bash deployment/deploy.sh --backend` | PHP/DB changes only — reuses previous Vite build |
| `bash deployment/deploy.sh --frontend` | CSS/JS changes only — skips composer + migrations |
| `bash deployment/deploy.sh --hotfix` | Config/template fix — no build, no migrate, no queue restart |

```bash
# Standard full deploy:
bash deployment/deploy.sh main

# Backend-only (PHP change, no front-end rebuild needed):
bash deployment/deploy.sh --backend

# Front-end only (CSS/JS change, no DB migration):
bash deployment/deploy.sh --frontend
```

All modes:
- Bump `CACHE_VERSION` in `sw.js` automatically (PWA cache invalidation)
- Tee all output to `deploy_logs/deploy_TIMESTAMP.log`
- Auto-rollback on any failure

### Post-deploy verification
- [ ] `curl -I https://yourdomain.com` returns 200
- [ ] Login works
- [ ] Submit a test expense request
- [ ] `supervisorctl status` — all workers RUNNING
- [ ] `tail -f /var/www/expenseflow/shared/storage/logs/laravel.log` — no errors

---

## Rollback

```bash
# List available releases:
bash deployment/rollback.sh --list

# Roll back to previous release (prompts for confirmation):
bash deployment/rollback.sh

# Roll back two releases (skip confirmation):
bash deployment/rollback.sh --release=2 --yes

# Legacy inline rollback (no confirmation, no caches rebuilt):
bash deployment/deploy.sh --rollback
```

Rollback: atomically switches symlink, rebuilds caches on rolled-back release, restarts workers.

> **Note:** Database migrations are NOT rolled back automatically. If the new migration is destructive, prepare a manual rollback migration.

---

## Health check

```bash
# Full interactive check (all 24 checks):
bash deployment/health-check.sh

# Quiet mode for CI/CD (prints only failures/warnings, exits 1 on fail):
bash deployment/health-check.sh --quiet

# JSON output for monitoring tools:
bash deployment/health-check.sh --json
```

Checks: HTTP response, SSL cert expiry, DB connectivity, queue workers, scheduler, sw.js, webmanifest, CSS asset HTTP, manifest.json, disk space, php-fpm, nginx, signed URL key.

---

## Cleanup

```bash
# Dry-run (see what would be deleted):
bash deployment/cleanup.sh

# Actually delete (keeps 5 releases, 30 days of logs, 7 days of DB backups):
bash deployment/cleanup.sh --force

# Keep only 3 releases:
bash deployment/cleanup.sh --force --keep=3
```

---

## Monitoring & maintenance

### Log files
| Log | Path |
|-----|------|
| Laravel app | `/var/www/expenseflow/shared/storage/logs/laravel-YYYY-MM-DD.log` |
| Queue workers | `/var/www/expenseflow/shared/storage/logs/worker.log` |
| OCR worker | `/var/www/expenseflow/shared/storage/logs/ocr-worker.log` |
| Scheduler (cron) | `/var/www/expenseflow/shared/storage/logs/cron.log` |
| Nginx access | `/var/log/nginx/expenseflow-access.log` |
| Nginx error | `/var/log/nginx/expenseflow-error.log` |
| PHP-FPM | `/var/log/php/expenseflow-fpm.log` |

### Failed jobs
```bash
php8.3 /var/www/expenseflow/current/artisan queue:failed
php8.3 /var/www/expenseflow/current/artisan queue:retry all
php8.3 /var/www/expenseflow/current/artisan queue:flush   # nuke all failed
```

### Database backup
Handled by `deployment/cron.d.expenseflow` (pg_dump at 2 AM, 30-day retention).

Restore from backup:
```bash
gunzip -c /var/backups/expenseflow/db-YYYYMMDD.sql.gz | psql -U expenseflow expenseflow_prod
```

### Releases management
```bash
# List releases:
ls -lth /var/www/expenseflow/releases/
# Current release:
readlink /var/www/expenseflow/current
```

### Maintenance mode
```bash
# On with secret bypass URL:
php8.3 /var/www/expenseflow/current/artisan down --secret=mysecrettoken
# Access app while down: https://yourdomain.com/mysecrettoken
# Off:
php8.3 /var/www/expenseflow/current/artisan up
```

---

## Security hardening checklist

- [ ] `APP_DEBUG=false` in production `.env`
- [ ] `APP_KEY` is unique (not the default or dev key)
- [ ] `/root/expenseflow-credentials.txt` is `chmod 600` and backed up offline
- [ ] SSH root login disabled (`PermitRootLogin no` in `/etc/ssh/sshd_config`)
- [ ] UFW active: `ufw status` shows 22/80/443 only
- [ ] Fail2ban active: `fail2ban-client status sshd`
- [ ] SSL Labs grade A: https://www.ssllabs.com/ssltest/
- [ ] `opcache.validate_timestamps=0` in PHP INI (already set by bootstrap)
- [ ] No `.env` or `composer.json` accessible via web (`nginx -t` and verify location blocks)
- [ ] Storage symlink points outside web root — bills and receipts not directly accessible

---

## Environment-specific notes

| Item | Value |
|------|-------|
| PHP version | 8.3 |
| PHP-FPM socket | `/run/php/php8.3-fpm-expenseflow.sock` |
| App root | `/var/www/expenseflow/current/public` |
| Shared storage | `/var/www/expenseflow/shared/storage` |
| Deploy user | `deployer` |
| DB user | `expenseflow` |
| DB name | `expenseflow_prod` |
| Tesseract langs | `eng`, `tam` |
| Python venv | `/opt/expenseflow-ocr` |
| Supervisor group | `expenseflow:*` |
| Cron file | `/etc/cron.d/expenseflow` |
