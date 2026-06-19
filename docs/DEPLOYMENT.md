# SATS — Deployment Guide

> Panduan lengkap deployment SATS dari 0% hingga production, berdasarkan proses deployment yang dilakukan pada 16 Juni 2026.

---

## Daftar Isi

- [1. Ringkasan Infrastruktur](#1-ringkasan-infrastruktur)
- [2. Pre-Deployment](#2-pre-deployment)
- [3. Setup VM](#3-setup-vm)
- [4. Konfigurasi Sistem](#4-konfigurasi-sistem)
- [5. Instalasi Stack](#5-instalasi-stack)
- [6. Konfigurasi MySQL](#6-konfigurasi-mysql)
- [7. Konfigurasi Redis](#7-konfigurasi-redis)
- [8. Instalasi Composer & phpMyAdmin](#8-instalasi-composer--phpmyadmin)
- [9. Deploy Laravel](#9-deploy-laravel)
- [10. Konfigurasi Nginx](#10-konfigurasi-nginx)
- [11. Supervisor (Background Process)](#11-supervisor-background-process)
- [12. Cron Scheduler](#12-cron-scheduler)
- [13. SSL Certificate](#13-ssl-certificate)
- [14. Verifikasi & Testing](#14-verifikasi--testing)
- [15. Troubleshooting](#15-troubleshooting)
- [16. Rekomendasi & Saran](#16-rekomendasi--saran)

---

## 1. Ringkasan Infrastruktur

```
┌──────────────────────────────────────────────────────┐
│  Provider:    AWS (Amazon Web Services)              │
│  Region:      ap-southeast-3 (Jakarta)               │
│  Instance:    t3.small                               │
│  ├── vCPU:    2 (burstable)                          │
│  ├── RAM:     1 GiB (tersedia ~1.9 GiB)              │
│  └── Storage: 30 GiB gp3                             │
│                                                      │
│  OS:          Ubuntu 24.04 LTS (Noble)               │
│  Domain:      sats.engineer                          │
│  IP Publik:   15.232.95.20                           │
│                                                      │
│  Stack:                                              │
│  ├── Web Server:    Nginx 1.24                       │
│  ├── PHP:           8.2.31 (Ondrej PPA)              │
│  ├── Database:      MySQL 8                          │
│  ├── Cache/Queue:   Redis 7                          │
│  ├── WebSocket:     Laravel Reverb                   │
│  ├── Process Mgr:   Supervisor                       │
│  └── SSL:           Let's Encrypt (Certbot)          │
│                                                      │
│  Biaya:       ~$17.79/bulan                          │
│  Kredit:      $100 (awet ~5.6 bulan)                 │
└──────────────────────────────────────────────────────┘
```

---

## 2. Pre-Deployment

### 2.1 Kebutuhan Sebelum Memulai

- [ ] Akun AWS dengan credits ($100)
- [ ] Domain (sats.engineer dari name.com)
- [ ] SSH key pair (`key-sats`) sudah dibuat di AWS Console
- [ ] Repository GitHub SATS sudah accessible
- [ ] `.env` lokal sudah dicatat (terutama `REVERB_APP_SECRET`)

### 2.2 Pointing DNS

Di name.com, tambahkan DNS record:

```
Type: A       Host: @      Answer: <IP_PUBLIK_VM>    TTL: 300
Type: A       Host: www    Answer: <IP_PUBLIK_VM>    TTL: 300
```

Propagasi: 1-5 menit (bisa sampai 1x24 jam).

### 2.3 Security Group (AWS)

Buka port berikut di AWS EC2 Security Group:

| Port | Protokol | Kegunaan |
|------|----------|----------|
| 22   | TCP      | SSH      |
| 80   | TCP      | HTTP     |
| 443  | TCP      | HTTPS    |

---

## 3. Setup VM

### 3.1 SSH ke Server

```bash
ssh -i key-sats.pem ubuntu@<IP_PUBLIK_VM>
```

### 3.2 Buat Swap File (2 GiB)

```bash
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
echo 'vm.swappiness=10' | sudo tee -a /etc/sysctl.conf
sudo sysctl -p
```

Verifikasi:
```bash
free -h
# Harusnya ada Swap: 2.0Gi
```

### 3.3 Update Sistem

```bash
sudo apt update && sudo apt upgrade -y
```

---

## 4. Konfigurasi Sistem

### 4.1 Install PHP 8.2 via Ondrej PPA

**⚠️ PENTING:** Ubuntu 24.04 defaultnya PHP 8.3. Kita harus install PHP 8.2 secara eksplisit.

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

sudo apt install -y \
  php8.2-fpm \
  php8.2-mysql \
  php8.2-redis \
  php8.2-mbstring \
  php8.2-xml \
  php8.2-curl \
  php8.2-zip \
  php8.2-bcmath \
  php8.2-gd \
  php8.2-intl \
  php8.2-cli \
  php8.2-dom
```

Set PHP 8.2 sebagai default CLI:
```bash
sudo update-alternatives --set php /usr/bin/php8.2
```

Verifikasi:
```bash
php -v
# Harusnya PHP 8.2.x

dpkg -l | grep php8.3
# Harusnya kosong (tidak ada PHP 8.3 yang terinstall)
```

### 4.2 Install Web Server & Services

```bash
sudo apt install -y nginx mysql-server redis-server supervisor
```

Verifikasi semua service berjalan:
```bash
sudo systemctl status nginx --no-pager
sudo systemctl status mysql --no-pager
sudo systemctl status redis-server --no-pager
sudo systemctl status supervisor --no-pager
```

---

## 5. Instalasi Stack

### 5.1 PHP-FPM Tuning

Edit config PHP-FPM:
```bash
sudo nano /etc/php/8.2/fpm/pool.d/www.conf
```

Pastikan konfigurasi:
```ini
pm = dynamic
pm.max_children = 15
pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 8
pm.max_requests = 500
pm.process_idle_timeout = 30s

listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
```

Restart PHP-FPM:
```bash
sudo systemctl restart php8.2-fpm
```

---

## 6. Konfigurasi MySQL

### 6.1 Secure MySQL

```bash
sudo mysql_secure_installation
```

Jawab:
- VALIDATE PASSWORD: N
- New root password: **CATAT PASSWORD INI!**
- Remove anonymous users: Y
- Disallow root login remotely: Y
- Remove test database: Y
- Reload privilege tables: Y

### 6.2 Buat Database & User

```bash
sudo mysql -u root -p
```

Di dalam MySQL shell:
```sql
CREATE DATABASE sats;
CREATE USER 'sats_kelompok_6'@'localhost' IDENTIFIED BY 'Sats!345345';
GRANT ALL PRIVILEGES ON sats.* TO 'sats_kelompok_6'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

**⚠️ CATAT USERNAME DAN PASSWORD!** Nanti dipakai di `.env` Laravel.

### 6.3 MySQL Tuning

Edit config MySQL:
```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Tambahkan di bagian `[mysqld]`:
```ini
innodb_buffer_pool_size = 512M
innodb_log_file_size = 128M
innodb_log_buffer_size = 16M
max_connections = 30
table_open_cache = 500
table_definition_cache = 400
key_buffer_size = 16M
performance_schema = OFF
```

Restart MySQL:
```bash
sudo systemctl restart mysql
```

---

## 7. Konfigurasi Redis

Edit config Redis:
```bash
sudo nano /etc/redis/redis.conf
```

Ubah konfigurasi:
```ini
# Bind localhost only
bind 127.0.0.1 -::1

# Memory limit
maxmemory 64mb

# Eviction policy
maxmemory-policy allkeys-lru

# Nonaktifkan persistence (hemat I/O)
save ""
appendonly no
```

Restart Redis:
```bash
sudo systemctl restart redis
```

Verifikasi:
```bash
redis-cli info memory | grep used_memory_human
# Harusnya ~893.09K (sangat ringan)
```

---

## 8. Instalasi Composer & phpMyAdmin

### 8.1 Install Composer

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

### 8.2 Install phpMyAdmin

```bash
sudo apt install -y phpmyadmin
```

Saat installer jalan:
- Web server: **JANGAN pilih apa-apa** → Tab ke OK → Enter
- Configure database: **Yes**
- Password: **kosongkan** → Enter
- Password confirmation: **kosongkan** → Enter
- Error database: Pilih **ignore**

---

## 9. Deploy Laravel

### 9.1 Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/AdityaWijayanto19/SATS_Repository.git sats
sudo chown -R ubuntu:ubuntu /var/www/sats
cd /var/www/sats
```

### 9.2 Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

Jika error extension missing, pastikan PHP 8.2 adalah default:
```bash
php -v
# Harusnya PHP 8.2.x
```

### 9.3 Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```bash
nano .env
```

Konfigurasi:
```env
APP_NAME=SATS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sats.engineer

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sats
DB_USERNAME=sats_kelompok_6
DB_PASSWORD=Sats!345345

SESSION_DRIVER=database
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

BROADCAST_CONNECTION=reverb

REVERB_APP_ID=142793
REVERB_APP_KEY=m4gykmamifp7ufmuoxoh
REVERB_APP_SECRET=wzb9ac5bmsmkgdofwggu
REVERB_HOST=sats.engineer
REVERB_PORT=443
REVERB_SCHEME=https

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 9.4 Build Frontend & Migrate

```bash
npm install
npm run build
php artisan config:cache
php artisan migrate --force
php artisan storage:link
```

### 9.5 Fix Permissions

```bash
sudo chown -R www-data:www-data /var/www/sats/storage
sudo chown -R www-data:www-data /var/www/sats/bootstrap/cache
sudo chmod -R 775 /var/www/sats/storage
sudo chmod -R 775 /var/www/sats/bootstrap/cache
```

---

## 10. Konfigurasi Nginx

### 10.1 Buat Virtual Host

```bash
sudo nano /etc/nginx/sites-available/sats
```

Paste konfigurasi:
```nginx
server {
    listen 80;
    server_name sats.engineer www.sats.engineer;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name sats.engineer www.sats.engineer;

    ssl_certificate /etc/letsencrypt/live/sats.engineer/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sats.engineer/privkey.pem;

    root /var/www/sats/public;
    index index.php;

    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location /sats-db-6a9f {
        alias /usr/share/phpmyadmin;
        index index.php;

        location ~ ^/sats-db-6a9f/(.+\.php)$ {
            alias /usr/share/phpmyadmin/$1;
            fastcgi_pass unix:/run/php/php8.2-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $request_filename;
            include fastcgi_params;
        }

        location ~* ^/sats-db-6a9f/(.+\.(css|js|jpg|jpeg|gif|png|ico|svg|ttf|woff|woff2))$ {
            alias /usr/share/phpmyadmin/$1;
        }
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 10.2 Buat Self-Signed SSL (Sementara)

```bash
sudo mkdir -p /etc/nginx/ssl
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/nginx/ssl/sats.key \
  -out /etc/nginx/ssl/sats.crt \
  -subj "/CN=sats.engineer"
sudo chmod 600 /etc/nginx/ssl/sats.key
sudo chmod 644 /etc/nginx/ssl/sats.crt
```

### 10.3 Enable Site

```bash
sudo ln -sf /etc/nginx/sites-available/sats /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t
sudo systemctl restart nginx
```

---

## 11. Supervisor (Background Process)

### 11.1 Laravel Queue Worker

```bash
sudo nano /etc/supervisor/conf.d/laravel-queue.conf
```

```ini
[program:laravel-queue]
command=php /var/www/sats/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --max-jobs=1000 --memory=128
process_name=%(program_name)s_%(process_num)02d
numprocs=1
autostart=true
autorestart=true
stopwaitsecs=10
stdout_logfile=/var/log/supervisor/laravel-queue.log
stdout_logfile_maxbytes=5MB
stdout_logfile_backups=3
redirect_stderr=true
```

### 11.2 Laravel Reverb (WebSocket)

```bash
sudo nano /etc/supervisor/conf.d/laravel-reverb.conf
```

```ini
[program:laravel-reverb]
command=php /var/www/sats/artisan reverb:start
process_name=%(program_name)s_%(process_num)02d
numprocs=1
autostart=true
autorestart=true
stopwaitsecs=10
stdout_logfile=/var/log/supervisor/laravel-reverb.log
stdout_logfile_maxbytes=5MB
stdout_logfile_backups=3
redirect_stderr=true
```

### 11.3 Aktifkan Supervisor Workers

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

Output yang diharapkan:
```
laravel-queue:laravel-queue_00     RUNNING
laravel-reverb:laravel-reverb_00   RUNNING
```

---

## 12. Cron Scheduler

Edit crontab:
```bash
sudo nano /etc/crontab
```

Tambahkan di akhir file:
```bash
# Laravel Scheduler — setiap menit
* * * * * www-data cd /var/www/sats && php artisan schedule:run >> /dev/null 2>&1

# Nightly restart untuk cegah memory leak — jam 3 pagi UTC
0 3 * * * root supervisorctl restart laravel-queue && supervisorctl restart laravel-reverb
```

---

## 13. SSL Certificate

### 13.1 Install Certbot

```bash
sudo apt install -y certbot python3-certbot-nginx
```

### 13.2 Generate Let's Encrypt Certificate

```bash
sudo certbot --nginx -d sats.engineer -d www.sats.engineer
```

Jawab:
- Email: masukkan email aktif
- Terms of Service: Y
- Share email with EFF: N (opsional)
- Redirect HTTP to HTTPS: **2** (Yes)

### 13.3 Verifikasi Auto-Renewal

```bash
sudo certbot renew --dry-run
```

Harusnya tidak ada error. Certbot otomatis renew sebelum expired.

---

## 14. Verifikasi & Testing

### 14.1 Cek Semua Service

```bash
# Supervisor
sudo supervisorctl status

# Nginx
sudo systemctl status nginx

# MySQL
sudo systemctl status mysql

# Redis
sudo systemctl status redis-server

# PHP-FPM
sudo systemctl status php8.2-fpm
```

### 14.2 Cek RAM

```bash
free -h
```

Expected:
```
               total        used        free      shared  buff/cache   available
Mem:           1.9Gi       ~750Mi      ~100Mi     ~25Mi    ~1.1Gi      ~1.1Gi
Swap:          2.0Gi       ~0B         ~2.0Gi
```

### 14.3 Cek Website

- [ ] https://sats.engineer — Landing page muncul
- [ ] https://sats.engineer/sats-db-6a9f — phpMyAdmin bisa login
- [ ] Login sebagai nakes, dokter, superadmin berfungsi
- [ ] WebSocket berfungsi (cek di dashboard, data real-time muncul)
- [ ] Tidak ada "Not Secure" di browser

### 14.4 Cek dari Command Line

```bash
# Test HTTP redirect
curl -I http://sats.engineer
# Harusnya 301 → https://

# Test HTTPS
curl -I https://sats.engineer
# Harusnya 200 OK

# Test Laravel artisan
cd /var/www/sats
php artisan route:list
```

---

## 15. Troubleshooting

### Error: "Vite manifest not found"

```bash
cd /var/www/sats
npm install
npm run build
```

### Error: "REVERB_APP_SECRET is null"

```bash
# Cek .env
grep REVERB_APP_SECRET /var/www/sats/.env

# Jika kosong, tambahkan
nano /var/www/sats/.env
# Tambahkan: REVERB_APP_SECRET=<nilai_dari_env_lokal>

# Re-cache config
php artisan config:clear
php artisan config:cache
```

### Error: "Permission denied" pada storage

```bash
sudo chown -R www-data:www-data /var/www/sats/storage
sudo chown -R www-data:www-data /var/www/sats/bootstrap/cache
sudo chmod -R 775 /var/www/sats/storage
sudo chmod -R 775 /var/www/sats/bootstrap/cache
sudo systemctl restart php8.2-fpm
```

### Error: "Could not open input file: artisan"

Pastikan Anda di direktori yang benar:
```bash
cd /var/www/sats
php artisan ...
```

### Error: phpMyAdmin "File not found"

Pastikan URL di Nginx config konsisten antara `location` dan regex di dalamnya:
```nginx
# BENAR:
location /sats-db-6a9f {
    location ~ ^/sats-db-6a9f/(.+\.php)$ {

# SALAH:
location /sats-db-6a9f {
    location ~ ^/phpmyadmin/(.+\.php)$ {
```

### Error: Composer menggunakan PHP versi salah

```bash
# Cek versi PHP CLI
php -v

# Jika bukan 8.2, set sebagai default
sudo update-alternatives --set php /usr/bin/php8.2
```

### MySQL "password does not satisfy policy"

Gunakan password yang memenuhi syarat:
- Minimal 8 karakter
- Ada huruf besar, kecil, angka, dan simbol

Contoh: `Sats!345345`

Atau turunkan policy:
```sql
SET GLOBAL validate_password.policy = LOW;
SET GLOBAL validate_password.length = 6;
```

### Supervisor tidak jalan

```bash
# Cek status
sudo supervisorctl status

# Restart manual
sudo supervisorctl restart laravel-queue
sudo supervisorctl restart laravel-reverb

# Cek log
sudo tail -20 /var/log/supervisor/laravel-queue.log
sudo tail -20 /var/log/supervisor/laravel-reverb.log
```

---

## 16. Rekomendasi & Saran

### 16.1 Keamanan

```
┌──────────────────────────────────────────────────────┐
│  YANG SUDAH BAIK:                                    │
│  ✅ SSL aktif (Let's Encrypt)                        │
│  ✅ HTTP redirect ke HTTPS                           │
│  ✅ phpMyAdmin URL tersembunyi                       │
│  ✅ MySQL hanya bisa diakses dari localhost           │
│  ✅ Redis hanya bisa diakses dari localhost           │
│  ✅ APP_DEBUG=false di production                    │
│  ✅ API key authentication untuk IoT endpoints       │
│  ✅ Rate limiting per device (60 req/min)            │
│  ✅ Idempotency key untuk mencegah duplikasi data    │
│                                                      │
│  YANG BISA DITAMBAHKAN:                              │
│  ⬜ Firewall (UFW) — blokir port selain 22, 80, 443│
│  ⬜ Fail2ban — cegah brute force SSH                │
│  ⬜ SSH key only — disable password authentication   │
│  ⬜ Automatic security updates (unattended-upgrades)│
│  ⬜ Database backup otomatis (mysqldump + cron)      │
│  ⬜ Log rotation untuk Laravel logs                  │
│  ⬜ Monitoring (UptimeRobot / Healthchecks.io gratis)│
└──────────────────────────────────────────────────────┘
```

### 16.2 Keamanan Firewall (UFW)

```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
sudo ufw status
```

### 16.3 Fail2ban (Anti Brute Force)

```bash
sudo apt install -y fail2ban
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 16.4 Automatic Security Updates

```bash
sudo apt install -y unattended-upgrades
sudo dpkg-reconfigure -plow unattended-upgrades
# Pilih Yes
```

### 16.5 Database Backup Otomatis

Buat script backup:
```bash
sudo nano /usr/local/bin/mysql-backup.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/mysql"
mkdir -p $BACKUP_DIR
mysqldump -u sats_kelompok_6 -p'Sats!345345' sats | gzip > $BACKUP_DIR/sats_$DATE.sql.gz
find $BACKUP_DIR -name "sats_*.sql.gz" -mtime +7 -delete
```

```bash
sudo chmod +x /usr/local/bin/mysql-backup.sh
```

Tambahkan ke cron (setiap jam 2 pagi):
```bash
sudo nano /etc/crontab
```
```
0 2 * * * root /usr/local/bin/mysql-backup.sh
```

### 16.6 Monitoring Gratis

Gunakan layanan monitoring gratis untuk cek uptime:

- **UptimeRobot** (https://uptimerobot.com) — 50 monitor gratis, cek tiap 5 menit
- **Healthchecks.io** (https://healthchecks.io) — cron job monitoring gratis

Setup:
1. Buat akun di UptimeRobot
2. Tambahkan monitor: `https://sats.engineer`
3. Set alert ke email Anda

### 16.7 Optimasi Performa

```
┌──────────────────────────────────────────────────────┐
│  SUDAH DITUNING:                                     │
│  ✅ MySQL: buffer pool 512M, max 30 connections      │
│  ✅ Redis: maxmemory 64M, no persistence             │
│  ✅ PHP-FPM: dynamic, max 15 children               │
│  ✅ Swap: 2 GiB, swappiness=10                      │
│  ✅ Supervisor: queue + reverb + nightly restart     │
│  ✅ Cron: scheduler + nightly restart                │
│                                                      │
│  BISA DIOPTIMASI:                                    │
│  ⬜ Nginx: enable gzip compression                   │
│  ⬜ Nginx: add cache headers untuk static assets     │
│  ⬜ Laravel: route cache, config cache, view cache   │
│  ⬜ OPcache: sudah aktif by default di PHP 8.2       │
│  ⬜ MySQL: query cache (jika ada query berulang)     │
└──────────────────────────────────────────────────────┘
```

### 16.8 Nginx Gzip Compression

Tambahkan di blok `server` (sebelum `location /`):
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
gzip_min_length 256;
gzip_vary on;
```

### 16.9 Nginx Cache Headers untuk Static Assets

Tambahkan di blok `server`:
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|woff|woff2|ttf|svg)$ {
    expires 30d;
    add_header Cache-Control "public, immutable";
}
```

### 16.10 Laravel Production Optimization

```bash
cd /var/www/sats
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 16.11 Cost Management

```
┌──────────────────────────────────────────────────────┐
│  STRATEGI HEMAT KREDIT $100:                         │
│                                                      │
│  1. Monitor penggunaan credit                        │
│     → AWS Console → Billing → Credit balance         │
│                                                      │
│  2. Jika tidak dipakai 24/7:                         │
│     → Stop instance saat malam (hemat ~50%)          │
│     → AWS CLI: aws ec2 stop-instances --instance-ids │
│     → AWS CLI: aws ec2 start-instances --instance-ids│
│                                                      │
│  3. Jika credit mau habis:                           │
│     → Migrate ke VPS lebih murah (Hetzner $5/bulan) │
│     → Atau Oracle Cloud Free Tier ($0)               │
│                                                      │
│  4. Set billing alert:                               │
│     → AWS Console → Billing → Budgets                │
│     → Alert saat 80% credit terpakai                 │
└──────────────────────────────────────────────────────┘
```

---

## Checklist Deployment Final

- [x] VM AWS EC2 t3.small created
- [x] Swap 2 GiB configured (swappiness=10)
- [x] PHP 8.2 installed (Ondrej PPA, tanpa PHP 8.3)
- [x] Nginx installed & configured
- [x] MySQL 8 installed & tuned
- [x] Redis 7 installed & tuned
- [x] Supervisor installed (queue + reverb)
- [x] Cron scheduler configured
- [x] Laravel deployed & migrated
- [x] Frontend built (Vite)
- [x] Storage permissions fixed
- [x] SSL certificate (Let's Encrypt)
- [x] HTTP → HTTPS redirect
- [x] phpMyAdmin accessible (hidden URL)
- [x] WebSocket (Reverb) running
- [x] DNS pointing (sats.engineer → 15.232.95.20)

---

*Dokumen ini dibuat berdasarkan proses deployment SATS pada 16 Juni 2026.*
*Terakhir diperbarui: 2026-06-16*
