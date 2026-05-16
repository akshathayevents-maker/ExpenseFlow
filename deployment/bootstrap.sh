#!/usr/bin/env bash
# =============================================================================
# ExpenseFlow — Ubuntu 24.04 LTS Server Bootstrap
# Run once as root on a fresh server.
# Usage: bash bootstrap.sh [domain] [db_password] [app_user]
# =============================================================================
set -euo pipefail

DOMAIN="${1:-expenseflow.example.com}"
DB_PASS="${2:-$(openssl rand -base64 24 | tr -dc 'a-zA-Z0-9' | head -c 32)}"
APP_USER="${3:-deployer}"
APP_DIR="/var/www/expenseflow"
PHP_VER="8.3"
PG_VER="16"
NODE_VER="20"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
log()  { echo -e "${GREEN}[$(date '+%H:%M:%S')] $*${NC}"; }
warn() { echo -e "${YELLOW}[WARN] $*${NC}"; }
die()  { echo -e "${RED}[ERROR] $*${NC}"; exit 1; }

[[ $EUID -ne 0 ]] && die "Run as root."

log "=== ExpenseFlow Server Bootstrap ==="
log "Domain:  $DOMAIN"
log "AppUser: $APP_USER"
log "AppDir:  $APP_DIR"

# ── System update ─────────────────────────────────────────────────────────────
log "Updating system packages..."
apt-get update -qq
DEBIAN_FRONTEND=noninteractive apt-get upgrade -y -qq
apt-get install -y -qq \
    curl wget git unzip zip gnupg2 ca-certificates lsb-release \
    software-properties-common apt-transport-https \
    ufw fail2ban logrotate acl htop vim

# ── PHP 8.3 ───────────────────────────────────────────────────────────────────
log "Installing PHP $PHP_VER..."
add-apt-repository -y ppa:ondrej/php
apt-get update -qq
apt-get install -y -qq \
    php${PHP_VER}-fpm php${PHP_VER}-cli \
    php${PHP_VER}-pgsql php${PHP_VER}-mysql \
    php${PHP_VER}-mbstring php${PHP_VER}-xml php${PHP_VER}-curl \
    php${PHP_VER}-zip php${PHP_VER}-gd php${PHP_VER}-intl \
    php${PHP_VER}-tokenizer php${PHP_VER}-bcmath \
    php${PHP_VER}-fileinfo php${PHP_VER}-opcache \
    php${PHP_VER}-redis php${PHP_VER}-imagick

# PHP production settings
cat > /etc/php/${PHP_VER}/fpm/conf.d/99-expenseflow.ini << 'PHPINI'
; Production hardening
expose_php           = Off
display_errors       = Off
log_errors           = On
error_reporting      = E_ALL & ~E_DEPRECATED & ~E_STRICT
upload_max_filesize  = 12M
post_max_size        = 14M
max_execution_time   = 120
memory_limit         = 256M
max_input_vars       = 3000
date.timezone        = Asia/Kolkata

; OPcache
opcache.enable                 = 1
opcache.enable_cli             = 0
opcache.memory_consumption     = 128
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files  = 10000
opcache.validate_timestamps    = 0
opcache.revalidate_freq        = 0
opcache.save_comments          = 1
PHPINI

# PHP-FPM pool
cat > /etc/php/${PHP_VER}/fpm/pool.d/expenseflow.conf << PHPFPM
[expenseflow]
user  = www-data
group = www-data
listen = /run/php/php${PHP_VER}-fpm-expenseflow.sock
listen.owner = www-data
listen.group = www-data
listen.mode  = 0660

pm                   = dynamic
pm.max_children      = 20
pm.start_servers     = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.process_idle_timeout = 10s
pm.max_requests      = 500

php_admin_value[error_log] = /var/log/php/expenseflow-fpm.log
php_admin_flag[log_errors]  = on
php_value[session.save_handler] = files
php_value[session.save_path]    = /var/lib/php/sessions
PHPFPM

mkdir -p /var/log/php
systemctl restart php${PHP_VER}-fpm
systemctl enable php${PHP_VER}-fpm

# ── Composer ──────────────────────────────────────────────────────────────────
log "Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
composer self-update --stable

# ── PostgreSQL ────────────────────────────────────────────────────────────────
log "Installing PostgreSQL $PG_VER..."
curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /etc/apt/trusted.gpg.d/postgresql.gpg
echo "deb https://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list
apt-get update -qq
apt-get install -y -qq postgresql-${PG_VER} postgresql-client-${PG_VER}

systemctl start postgresql
systemctl enable postgresql

# Create DB + user
DB_NAME="expenseflow_prod"
DB_USER="expenseflow"
sudo -u postgres psql -c "CREATE USER ${DB_USER} WITH PASSWORD '${DB_PASS}';" 2>/dev/null || warn "DB user may already exist"
sudo -u postgres psql -c "CREATE DATABASE ${DB_NAME} OWNER ${DB_USER};" 2>/dev/null || warn "DB may already exist"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE ${DB_NAME} TO ${DB_USER};" 2>/dev/null

# PostgreSQL tuning for 4GB RAM server
cat >> /etc/postgresql/${PG_VER}/main/postgresql.conf << 'PGCONF'

# ExpenseFlow tuning
max_connections       = 100
shared_buffers        = 1GB
effective_cache_size  = 3GB
maintenance_work_mem  = 256MB
checkpoint_completion_target = 0.9
wal_buffers           = 16MB
default_statistics_target = 100
random_page_cost      = 1.1
work_mem              = 10MB
log_min_duration_statement = 1000
PGCONF

systemctl restart postgresql

# ── Nginx ─────────────────────────────────────────────────────────────────────
log "Installing Nginx..."
apt-get install -y -qq nginx
systemctl enable nginx

# ── Node.js ───────────────────────────────────────────────────────────────────
log "Installing Node.js $NODE_VER..."
curl -fsSL https://deb.nodesource.com/setup_${NODE_VER}.x | bash -
apt-get install -y -qq nodejs
npm install -g npm@latest

# ── Python + OCR ──────────────────────────────────────────────────────────────
log "Installing Python + OCR stack..."
apt-get install -y -qq \
    python3 python3-pip python3-venv python3-dev \
    tesseract-ocr tesseract-ocr-eng tesseract-ocr-tam \
    poppler-utils libjpeg-dev libpng-dev

# Create Python venv for the app
python3 -m venv /opt/expenseflow-ocr
/opt/expenseflow-ocr/bin/pip install --upgrade pip
/opt/expenseflow-ocr/bin/pip install pytesseract pillow pdf2image

# ── Supervisor ────────────────────────────────────────────────────────────────
log "Installing Supervisor..."
apt-get install -y -qq supervisor
systemctl enable supervisor

# ── Certbot (Let's Encrypt) ───────────────────────────────────────────────────
log "Installing Certbot..."
apt-get install -y -qq certbot python3-certbot-nginx

# ── App user & directories ────────────────────────────────────────────────────
log "Creating application user: $APP_USER..."
id "$APP_USER" &>/dev/null || useradd -m -s /bin/bash -G www-data "$APP_USER"

# SSH key for deployments (add your CI/CD public key here)
mkdir -p /home/${APP_USER}/.ssh
chmod 700 /home/${APP_USER}/.ssh
touch /home/${APP_USER}/.ssh/authorized_keys
chmod 600 /home/${APP_USER}/.ssh/authorized_keys
chown -R ${APP_USER}:${APP_USER} /home/${APP_USER}/.ssh

# App directory structure (Capistrano-style releases)
mkdir -p ${APP_DIR}/{releases,shared/{storage,public/storage},repo}
mkdir -p ${APP_DIR}/shared/storage/{app/{bills,ocr,public/{bills,temp-qr}},framework/{cache/data,sessions,views},logs}

chown -R ${APP_USER}:www-data ${APP_DIR}
chmod -R 775 ${APP_DIR}/shared/storage
setfacl -R -m u:www-data:rwx ${APP_DIR}/shared/storage

# DB backup directory
mkdir -p /var/backups/expenseflow
chown postgres:postgres /var/backups/expenseflow
chmod 700 /var/backups/expenseflow

# ── Firewall ──────────────────────────────────────────────────────────────────
log "Configuring UFW firewall..."
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp comment 'SSH'
ufw allow 80/tcp comment 'HTTP'
ufw allow 443/tcp comment 'HTTPS'
ufw allow from 127.0.0.1 to any port 5432 comment 'PostgreSQL local'
ufw --force enable

# ── Fail2ban ──────────────────────────────────────────────────────────────────
log "Configuring Fail2ban..."
cat > /etc/fail2ban/jail.local << 'F2B'
[DEFAULT]
bantime  = 3600
findtime = 600
maxretry = 5

[sshd]
enabled  = true

[nginx-http-auth]
enabled  = true

[nginx-botsearch]
enabled  = true
F2B
systemctl restart fail2ban
systemctl enable fail2ban

# ── Log rotation ──────────────────────────────────────────────────────────────
cat > /etc/logrotate.d/expenseflow << LOGROTATE
${APP_DIR}/shared/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0664 www-data www-data
    sharedscripts
    postrotate
        [ -f /run/php/php${PHP_VER}-fpm-expenseflow.sock ] && kill -USR1 \$(cat /run/php/php${PHP_VER}-fpm.pid 2>/dev/null) 2>/dev/null || true
    endscript
}
LOGROTATE

# ── Sudoers for deploy user ───────────────────────────────────────────────────
log "Configuring sudoers for ${APP_USER}..."
cat > /etc/sudoers.d/expenseflow-${APP_USER} << SUDOERS
${APP_USER} ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload php${PHP_VER}-fpm
${APP_USER} ALL=(ALL) NOPASSWD: /usr/bin/systemctl reload nginx
${APP_USER} ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart expenseflow-worker:*
${APP_USER} ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart expenseflow-ocr-worker
${APP_USER} ALL=(ALL) NOPASSWD: /usr/bin/supervisorctl restart expenseflow:*
SUDOERS
chmod 440 /etc/sudoers.d/expenseflow-${APP_USER}
visudo -c >/dev/null && log "Sudoers OK" || warn "Sudoers syntax error — check manually"

# ── Save credentials ──────────────────────────────────────────────────────────
CREDS_FILE="/root/expenseflow-credentials.txt"
cat > $CREDS_FILE << CREDS
=== ExpenseFlow Server Credentials ===
Generated: $(date)

Database:
  Host:     127.0.0.1
  Port:     5432
  Name:     ${DB_NAME}
  User:     ${DB_USER}
  Password: ${DB_PASS}

App Directory: ${APP_DIR}
Deploy User:   ${APP_USER}
Domain:        ${DOMAIN}

Next steps:
  1. Add deploy user's SSH key: echo 'YOUR_KEY' >> /home/${APP_USER}/.ssh/authorized_keys
  2. Copy nginx config:     cp /path/to/nginx.conf /etc/nginx/sites-available/expenseflow
  3. Copy supervisor config: cp /path/to/supervisor.conf /etc/supervisor/conf.d/expenseflow.conf
  4. Run: certbot --nginx -d ${DOMAIN}
  5. Run deploy.sh from CI/CD
CREDS
chmod 600 $CREDS_FILE

log "=== Bootstrap complete ==="
log "Credentials saved to: $CREDS_FILE"
log "IMPORTANT: chmod 600 $CREDS_FILE and store securely!"
