# SATS — Infrastructure & Deployment Guide

> Dokumen ini berisi analisis kebutuhan infrastruktur, rekomendasi VM, strategi deployment, dan estimasi biaya untuk proyek SATS (Smart Ambulance Tracking System).

---

## Daftar Isi

- [1. Arsitektur Aplikasi](#1-arsitektur-aplikasi)
- [2. Kebutuhan Resource](#2-kebutuhan-resource)
- [3. Analisis VM Options](#3-analisis-vm-options)
- [4. Stack Deployment](#4-stack-deployment)
- [5. Strategi Alokasi RAM](#5-strategi-alokasi-ram)
- [6. Manajemen Background Process](#6-manajemen-background-process)
- [7. Konfigurasi Jaringan & DNS](#7-konfigurasi-jaringan--dns)
- [8. Estimasi Biaya](#8-estimasi-biaya)
- [9. Checklist Deployment](#9-checklist-deployment)

---

## 1. Arsitektur Aplikasi

### Overview

SATS adalah platform monitoring pasien berbasis IoT yang berjalan di atas Laravel 12. Sistem ini mengumpulkan data vital sign (heart rate, SpO2, temperature) dari perangkat IoT setiap 5 detik, menampilkannya secara real-time via WebSocket, dan menjalankan prediksi ML melalui API eksternal.

### Tech Stack

| Layer | Technology |
|---|---|
| Runtime | PHP 8.2+ |
| Framework | Laravel 12 |
| Database | MySQL 8 |
| Cache & Queue | Redis 7 |
| WebSocket | Laravel Reverb (self-hosted) |
| Web Server | Nginx |
| Frontend | Vite 6 + Tailwind CSS 4 + Laravel Echo |
| 3D Visualization | Three.js |
| PDF Generation | DomPDF |
| ML Integration | Hugging Face Spaces (Gradio API) |
| Process Manager | Supervisor |

### Proses yang Harus Berjalan 24/7

```
┌─────────────────────────────────────────────────┐
│  Proses yang SELALU hidup:                      │
│  ├── Nginx (web server, port 80/443)            │
│  ├── PHP-FPM (process manager untuk PHP)        │
│  ├── MySQL 8 (database server)                  │
│  ├── Redis 7 (cache + queue broker)             │
│  ├── Laravel Reverb (WebSocket, port 8080)      │
│  ├── Laravel Queue Worker (proses background)   │
│  └── Cron → Laravel Scheduler (setiap menit)    │
│                                                 │
│  Total: 7 proses yang harus selalu tersedia     │
└─────────────────────────────────────────────────┘
```

### Data Flow

```
IoT Device (tiap 5 detik)
    │
    ▼
POST /api/device/{id}/sensor-data
    │
    ├──► AuthenticateApiKey (Redis-cached, 30 min TTL)
    ├──► ThrottleApiRequests (60 req/min per device)
    ├──► IdempotentRequest (atomic lock + 24h cache)
    │
    ▼
SensorDataController
    │
    ├──► Broadcast SensorDataReceived (WebSocket, sinkron)
    ├──► Dispatch ProcessSensorData (queue, async)
    └──► Return HTTP 202 (non-blocking)
         │
         ▼
    Queue Worker memproses job
    ├──► INSERT ke sensor_datas
    ├──► Cek status (warning/critical)
    └──► Trigger ML prediction (setiap 5 data valid)
         │
         ▼
    Hugging Face API → Hasil prediksi → Broadcast ke dashboard
```

---

## 2. Kebutuhan Resource

### Kebutuhan Minimum

| Resource | Minimum | Rekomendasi |
|---|---|---|
| CPU | 1 vCPU | 2 vCPU |
| RAM | 1 GiB (sangat mepet) | 2-4 GiB |
| Storage | 20 GiB | 40-64 GiB |
| OS | Ubuntu 22.04/24.04 LTS | Ubuntu 24.04 LTS |
| Network | Port 22, 80, 443 | + port 8080 atau reverse proxy |
| Architecture | x64 atau ARM64 | Keduanya supported |

### Estimasi Penggunaan per Komponen

```
┌──────────────────────────────────────────────────────┐
│  Komponen             │ RAM Idle │ RAM Peak │ Disk    │
│─────────────────────────────────────────────────────│
│  OS + System Daemons  │ 150 MiB  │ 150 MiB  │ 4 GiB  │
│  Nginx                │ 15 MiB   │ 20 MiB   │ 10 MiB  │
│  PHP 8.2-FPM          │ 50 MiB   │ 400 MiB  │ 200 MiB │
│  MySQL 8              │ 250 MiB  │ 1.2 GiB  │ 1.2 GiB │
│  Redis 7              │ 40 MiB   │ 60 MiB   │ 20 MiB  │
│  Laravel Reverb       │ 80 MiB   │ 100 MiB  │ -       │
│  Queue Worker         │ 50 MiB   │ 80 MiB   │ -       │
│  Scheduler (cron)     │ 0 MiB    │ 40 MiB   │ -       │
│  Supervisor           │ 8 MiB    │ 10 MiB   │ 5 MiB   │
│  Laravel App (vendor) │ -        │ -        │ 400 MiB │
│  phpMyAdmin           │ -        │ -        │ 70 MiB  │
│─────────────────────────────────────────────────────│
│  TOTAL                │ ~643 MiB │ ~2.1 GiB │ ~6 GiB  │
│  Dengan buffer        │          │ ~2.5 GiB │ ~15 GiB │
└──────────────────────────────────────────────────────┘
```

### Estimasi Data Volume

```
Per device, dengan sampling interval 5 detik:
  ├── Sensor data per hari: ~17.280 baris
  ├── Storage per hari: ~2-3 MiB
  ├── Storage per bulan: ~60-90 MiB
  └── Storage per tahun: ~720 MiB - 1 GiB

Tabel sensor_datas bersifat EPHEMERAL:
  → Data dihapus setelah monitoring session selesai
  → Data dipindahkan ke sensor_readings (permanent archive)
  → Tabel sensor_datas tetap kecil

Untuk 5 device aktif, 1 bulan:
  → sensor_readings: ~300-450 MiB
  → Total database: ~500 MiB - 1 GiB
```

---

## 3. Analisis VM Options

### Azure VM

| SKU | vCPU | RAM | Harga/bulan | Awet dari $100 | Catatan |
|---|---|---|---|---|---|
| B1s | 1 | 1 GiB | ~$7.60 | ~13 bulan | Sangat mepet, butuh swap 2GB, tuning ekstrim |
| B2pts_v2 | 2 | 1 GiB | ~$8.47 | ~11.8 bulan | Disk 4 GiB (tidak cukup), RAM tetap 1 GiB |
| B2als_v2 (Indonesia Central) | 2 | 4 GiB | ~$38 | ~2.6 bulan | Terlalu mahal untuk budget $100 |
| B2s (region murah) | 2 | 4 GiB | ~$30 | ~3.3 bulan | Masih mahal |

**Kesimpulan Azure:** Opsi yang cukup untuk stack ini ($8/bulan) sangat mepet di RAM. Opsi yang nyaman (4 GiB) terlalu mahal ($30-38/bulan). Kredit $100 habis dalam 2-3 bulan.

### Alternatif: Proxmox di Laptop/PC Lokal

```
Keuntungan:
  ├── Biaya: $0/bulan (hanya listrik ~$1/bulan)
  ├── Kredit Azure utuh $100
  ├── Storage melimpah (tergantung hardware)
  └── Full control

Kekurangan:
  ├── Tidak ada public IP (perlu Cloudflare Tunnel)
  ├── Uptime tergantung hardware
  ├── Performa tergantung spesifikasi laptop/PC
  └── Perlu UPS untuk 24/7

Cocok untuk: Development, testing, demo skripsi
```

### Alternatif: VPS Lain (Di Luar Azure)

| Provider | Spesifikasi | Harga/bulan |
|---|---|---|
| Hetzner CX22 | 2 vCPU, 4 GiB RAM, 40 GiB | ~$5 |
| DigitalOcean Basic | 1 vCPU, 1 GiB RAM, 25 GiB | ~$6 |
| Oracle Cloud Free | 4 OCPU, 24 GiB RAM (ARM) | $0 (selalu gratis) |
| Vultr | 1 vCPU, 1 GiB RAM, 25 GiB | ~$5 |

**Oracle Cloud Always Free Tier** menawarkan ARM instance dengan 4 OCPU dan 24 GiB RAM secara gratis, tapi availability sangat terbatas dan setup lebih kompleks.

---

## 4. Stack Deployment

### Paket yang Diinstall

```bash
# Repository & PHP
sudo add-apt-repository ppa:ondrej/php
sudo apt update

# PHP 8.2 (eksplisit, JANGAN pakai meta-package php-fpm)
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
  php8.2-cli

# Web Server
sudo apt install -y nginx

# Database
sudo apt install -y mysql-server

# Cache & Queue Broker
sudo apt install -y redis-server

# Process Manager
sudo apt install -y supervisor

# phpMyAdmin (opsional)
sudo apt install -y phpmyadmin

# Utilities
sudo apt install -y git curl unzip certbot python3-certbot-nginx
```

### Urutan Instalasi

```
1. Swap setup (2 GiB, swappiness=10)
2. PHP 8.2 via Ondrej PPA (eksplisit tanpa PHP 8.3)
3. Nginx
4. MySQL 8 + hardening
5. Redis 7 + tuning
6. Supervisor
7. phpMyAdmin (opsional)
8. Clone repository Laravel
9. Composer install
10. Laravel .env configuration
11. Artisan migrate + seed
12. Nginx virtual host configuration
13. Supervisor workers configuration
14. Cron scheduler setup
15. SSL via Certbot (Let's Encrypt) atau Cloudflare Tunnel
16. Domain DNS pointing
17. Testing & verification
```

---

## 5. Strategi Alokasi RAM

### MySQL 8 Tuning

```ini
# /etc/mysql/mysql.conf.d/mysqld.cnf

[mysqld]
# Buffer pool: 25% dari total RAM server
# 1 GiB untuk server 4 GiB
# 512 MiB untuk server 2 GiB
# 128 MiB untuk server 1 GiB (sangat mepet)
innodb_buffer_pool_size = 1G

innodb_log_file_size = 256M
innodb_log_buffer_size = 16M

# Koneksi: sesuaikan kebutuhan riil
# Laravel (5-10) + phpMyAdmin (2-3) + headroom
max_connections = 30

# Kurangi dari default untuk hemat RAM
table_open_cache = 500
table_definition_cache = 400
key_buffer_size = 16M

# Nonaktifkan performance_schema (hemat ~50 MiB)
performance_schema = OFF
```

### PHP-FPM Tuning

```ini
; /etc/php/8.2/fpm/pool.d/www.conf

; Mode dynamic: balance antara responsivitas dan hemat RAM
pm = dynamic

; Max children: sesuaikan dengan RAM
; Formula: (Available RAM - Other services) / 50 MiB per child
; Server 4 GiB → max 15 children
; Server 2 GiB → max 8 children
; Server 1 GiB → max 2 children (sangat mepet)
pm.max_children = 15

pm.start_servers = 5
pm.min_spare_servers = 3
pm.max_spare_servers = 8

; Restart child setelah 500 request (cegah memory leak)
pm.max_requests = 500

; Bunuh child idle setelah 30 detik
pm.process_idle_timeout = 30s

; Unix socket (lebih cepat dari TCP)
listen = /run/php/php8.2-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
```

### Redis Tuning

```ini
# /etc/redis/redis.conf

# Memory limit
# Server 4 GiB → 128 MiB
# Server 2 GiB → 64 MiB
# Server 1 GiB → 48 MiB
maxmemory 128mb

# Eviction: hapus key yang paling jarang dipakai
maxmemory-policy allkeys-lru

# Nonaktifkan persistence (hemat I/O, queue data re-dispatchable)
save ""
appendonly no

# Bind localhost only
bind 127.0.0.1 ::1
protected-mode yes
```

### Swap Configuration

```bash
# Buat swap file 2 GiB
sudo fallocate -l 2G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# Set swappiness = 10 (prioritaskan RAM, swap hanya safety net)
echo 'vm.swappiness=10' | sudo tee -a /etc/sysctl.conf

# Persist di /etc/fstab
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab
```

---

## 6. Manajemen Background Process

### Supervisor Configuration

```ini
# /etc/supervisor/conf.d/laravel-queue.conf

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

```ini
# /etc/supervisor/conf.d/laravel-reverb.conf

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

### Cron Scheduler

```bash
# /etc/crontab

# Laravel Scheduler — setiap menit
* * * * * www-data cd /var/www/sats && php artisan schedule:run >> /dev/null 2>&1

# Nightly restart untuk cegah memory leak — jam 3 pagi
0 3 * * * root supervisorctl restart laravel-queue && supervisorctl restart laravel-reverb
```

### Mengapa Scheduler Pakai Cron (Bukan Supervisor)?

```
Supervisor + schedule:work:
  → Proses PHP berjalan 24/7
  → RAM konstan: ~40-60 MiB
  → Menambah proses yang perlu di-manage

System cron + schedule:run:
  → Tidak ada proses konstan
  → Spike ~40 MiB hanya 1 detik per menit
  → Lebih hemat, lebih bersih
  → Cron daemon sudah jalan di Ubuntu secara default
```

---

## 7. Konfigurasi Jaringan & DNS

### Port yang Dibutuhkan

| Port | Protokol | Layanan | Akses |
|---|---|---|---|
| 22 | TCP | SSH | Admin only |
| 80 | TCP | HTTP (Nginx) | Public (redirect ke 443) |
| 443 | TCP | HTTPS (Nginx + SSL) | Public |
| 8080 | TCP | Laravel Reverb (jika tidak di-proxy) | Public atau internal |

### Nginx Reverse Proxy untuk WebSocket

```nginx
# /etc/nginx/sites-available/sats

server {
    listen 80;
    server_name sats.engineer www.sats.engineer;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name sats.engineer www.sats.engineer;

    # SSL (Certbot atau Cloudflare)
    ssl_certificate /etc/letsencrypt/live/sats.engineer/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/sats.engineer/privkey.pem;

    root /var/www/sats/public;
    index index.php;

    # Laravel app
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # WebSocket reverse proxy ke Reverb
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

    # Prevent access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### DNS Configuration

```
Di registrar domain (name.com, Cloudflare, dll):

Type: A
Name: @
Value: <IP_PUBLIK_VM>
TTL: 300

Type: A
Name: www
Value: <IP_PUBLIK_VM>
TTL: 300

Hasil:
  sats.engineer      → IP VM
  www.sats.engineer  → IP VM
```

### Opsi Public Access untuk VM Lokal (Tanpa Public IP)

```
┌──────────────────────────────────────────────────────┐
│  Opsi 1: Cloudflare Tunnel (GRATIS, REKOMENDASI)     │
│  ├── Install cloudflared di server                    │
│  ├── Buat tunnel dari localhost:80                    │
│  ├── SSL otomatis dari Cloudflare                     │
│  ├── Tidak perlu buka port di router                  │
│  ├── IoT device → Cloudflare → server lokal           │
│  └── Setup: ~15 menit                                 │
│                                                      │
│  Opsi 2: Port Forwarding + DDNS                      │
│  ├── Buka port 80, 443 di router                      │
│  ├── Point DNS ke IP publik rumah                     │
│  ├── DDNS (No-IP/DuckDNS) untuk dynamic IP            │
│  ├── ⚠️ Security risk: expose port ke internet        │
│  └── ⚠️ IP publik bisa berubah                        │
│                                                      │
│  Opsi 3: Tailscale (GRATIS untuk personal)            │
│  ├── VPN mesh, akses dari mana saja                   │
│  ├── Tidak perlu public IP atau port forwarding       │
│  ├── ⚠️ Hanya device yang terinstall Tailscale        │
│  └── Cocok untuk development, bukan production        │
└──────────────────────────────────────────────────────┘
```

---

## 8. Estimasi Biaya

### Skenario Azure VM

```
┌──────────────────────────────────────────────────────┐
│  Azure B1s (1 vCPU, 1 GiB RAM):                     │
│  ├── Harga: ~$7.60/bulan                             │
│  ├── Kredit $100: awet ~13 bulan                     │
│  ├── ⚠️ RAM sangat mepet, tuning ekstrim             │
│  └── ⚠️ Swap 2GB wajib                               │
│                                                      │
│  Azure B2als_v2 Indonesia (2 vCPU, 4 GiB RAM):      │
│  ├── Harga: ~$38/bulan                               │
│  ├── Kredit $100: awet ~2.6 bulan                    │
│  └── ✅ RAM lega, tidak perlu tuning ekstrim          │
└──────────────────────────────────────────────────────┘
```

### Skenario Proxmox Lokal

```
┌──────────────────────────────────────────────────────┐
│  Proxmox di Laptop/PC:                               │
│  ├── Harga VM: $0                                    │
│  ├── Listrik: ~$1/bulan (15W × 24jam)                │
│  ├── Domain: ~$1/tahun (jika beli baru)               │
│  ├── Cloudflare Tunnel: $0                            │
│  ├── TOTAL: ~$1-2/bulan                              │
│  └── Kredit Azure: UTUH $100                         │
└──────────────────────────────────────────────────────┘
```

### Skenario VPS Lain

```
┌──────────────────────────────────────────────────────┐
│  Hetzner CX22 (2 vCPU, 4 GiB RAM):                  │
│  ├── Harga: ~$5/bulan                                │
│  └── ✅ Best value untuk VPS                          │
│                                                      │
│  Oracle Cloud Always Free (4 OCPU, 24 GiB ARM):      │
│  ├── Harga: $0 SELAMANYA                              │
│  └── ⚠️ Sulit dapat availability                     │
└──────────────────────────────────────────────────────┘
```

---

## 9. Checklist Deployment

### Pre-Deployment

- [ ] VM / server sudah dibuat dan bisa di-SSH
- [ ] Domain sudah diarahkan ke IP server (DNS A record)
- [ ] Port 22, 80, 443 sudah dibuka
- [ ] Swap 2 GiB sudah dibuat
- [ ] Sistem sudah di-update (`apt update && apt upgrade`)

### Installation

- [ ] PHP 8.2 terinstall via Ondrej PPA (tanpa PHP 8.3)
- [ ] Nginx terinstall dan berjalan
- [ ] MySQL 8 terinstall dan di-hardening
- [ ] Redis 7 terinstall dan di-tuning
- [ ] Supervisor terinstall
- [ ] phpMyAdmin terinstall (opsional)
- [ ] Composer terinstall global

### Laravel Deployment

- [ ] Repository di-clone ke `/var/www/sats`
- [ ] `composer install --no-dev --optimize-autoloader` selesai
- [ ] `.env` sudah dikonfigurasi (DB, Redis, Reverb, APP_URL)
- [ ] `php artisan key:generate` dijalankan
- [ ] `php artisan migrate --force` selesai
- [ ] `php artisan config:cache` dijalankan
- [ ] `php artisan route:cache` dijalankan
- [ ] `php artisan view:cache` dijalankan
- [ ] Storage symlink: `php artisan storage:link`

### Nginx Configuration

- [ ] Virtual host sudah dibuat di `/etc/nginx/sites-available/`
- [ ] Symlink ke `sites-enabled`
- [ ] `nginx -t` berhasil (test config)
- [ ] Nginx direstart
- [ ] WebSocket reverse proxy ke Reverb sudah dikonfigurasi

### Supervisor Configuration

- [ ] `laravel-queue.conf` sudah dibuat
- [ ] `laravel-reverb.conf` sudah dibuat
- [ ] `supervisorctl reread` dan `supervisorctl update` dijalankan
- [ ] Kedua proses berjalan: `supervisorctl status`

### Cron Configuration

- [ ] Laravel scheduler ditambahkan ke `/etc/crontab`
- [ ] Nightly restart ditambahkan ke `/etc/crontab`

### SSL & Domain

- [ ] SSL certificate didapatkan (Certbot atau Cloudflare)
- [ ] HTTPS berfungsi di `https://sats.engineer`
- [ ] WebSocket berfungsi di `wss://sats.engineer/app`
- [ ] HTTP redirect ke HTTPS

### Testing

- [ ] Landing page bisa diakses
- [ ] Login berfungsi untuk semua role (nakes, dokter, superadmin)
- [ ] IoT device bisa mengirim data ke API
- [ ] WebSocket real-time berfungsi di dashboard
- [ ] Queue worker memproses job dengan benar
- [ ] Scheduler berjalan (cek `php artisan schedule:list`)
- [ ] phpMyAdmin bisa diakses (jika diinstall)

---

## Troubleshooting Umum

### PHP-FPM Tidak Mau Start

```bash
# Cek error log
sudo tail -f /var/log/php8.2-fpm-error.log

# Cek apakah socket directory ada
sudo mkdir -p /run/php
sudo chown www-data:www-data /run/php
```

### MySQL Kebanyakan Koneksi

```bash
# Cek koneksi aktif
mysql -u root -p -e "SHOW STATUS LIKE 'Threads_connected';"

# Kurangi max_connections jika perlu
# Edit /etc/mysql/mysql.conf.d/mysqld.cnf
# max_connections = 20
```

### Redis Out of Memory

```bash
# Cek penggunaan memori
redis-cli info memory

# Kurangi maxmemory jika perlu
redis-cli CONFIG SET maxmemory 64mb
```

### Reverb WebSocket Tidak Terkoneksi

```bash
# Cek Reverb berjalan
supervisorctl status laravel-reverb

# Cek port 8080 listening
sudo ss -tlnp | grep 8080

# Cek Nginx WebSocket config
sudo nginx -t

# Cek firewall
sudo ufw status
```

### Queue Worker Lambat atau Stuck

```bash
# Restart worker
supervisorctl restart laravel-queue

# Cek queue depth
php artisan queue:monitor redis:default

# Cek failed jobs
php artisan queue:failed
```

---

*Dokumen ini dibuat berdasarkan analisis arsitektur SATS per Juni 2026.*
*Terakhir diperbarui: 2026-06-16*
