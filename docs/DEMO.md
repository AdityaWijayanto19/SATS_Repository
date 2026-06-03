# SATS - Panduan Instalasi & Demo

Panduan lengkap untuk menginstal, menjalankan, dan mendemostrasikan sistem SATS dari nol.

---

## Daftar Isi

1. [Prasyarat](#prasyarat)
2. [Instalasi](#instalasi)
3. [Konfigurasi](#konfigurasi)
4. [Menjalankan Sistem](#menjalankan-sistem)
5. [Menjalankan Simulator](#menjalankan-simulator)
6. [Alur Demo](#alur-demo)
7. [Akun Demo](#akun-demo)
8. [Troubleshooting](#troubleshooting)
9. [Catatan: Simulator vs IoT Asli](#catatan-simulator-vs-iot-asli)

---

## Prasyarat

| Software | Versi Minimum | Keterangan |
|----------|---------------|------------|
| PHP | 8.2+ | Sudah include di Laragon/XAMPP |
| Composer | 2.x | Dependency manager PHP |
| Node.js | 18+ | Untuk Vite & Tailwind CSS |
| MySQL | 8.x | Database utama |
| Redis | 6.x+ | Queue worker & caching |
| Git | - | Clone repository |
| Python | 3.8+ | Opsional, untuk menjalankan simulator IoT |

> **Rekomendasi (Windows):** Gunakan [Laragon](https://laragon.org/) karena sudah include PHP, MySQL, dan auto virtual host.

### Instalasi Redis (Windows)

Redis tidak tersedia native di Windows. Beberapa opsi:

**Opsi 1: Laragon (Paling Mudah)**
- Laragon sudah include Redis secara built-in
- Aktifkan: Menu → Redis → Enable
- Redis langsung berjalan di `127.0.0.1:6379`

**Opsi 2: WSL (Windows Subsystem for Linux)**
```bash
# Install WSL jika belum (PowerShell Admin)
wsl --install

# Di WSL terminal
sudo apt update
sudo apt install redis-server
sudo service redis-server start

# Cek Redis berjalan
redis-cli ping
# Response: PONG
```

**Opsi 3: Docker**
```bash
docker run -d --name redis -p 6379:6379 redis:7-alpine

# Cek Redis berjalan
docker exec -it redis redis-cli ping
# Response: PONG
```

> **Catatan:** Jika tidak ingin pakai Redis, bisa ganti ke `QUEUE_CONNECTION=database` di `.env` (lebih lambat tapi tidak perlu install Redis).

---

## Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/AdityaWijayanto19/SATS_Repository.git
cd SATS_Repository
```

### 2. Install Dependencies PHP

```bash
composer install
```

> Jika error, coba `composer update` atau pastikan PHP 8.2+ (`php -v`).

### 3. Install Dependencies JavaScript

```bash
npm install
```

> Jika error, pastikan Node.js 18+ (`node -v`), coba `npm install --force`.

### 4. Setup Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Konfigurasi Database

Buka file `.env`, ubah bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sats_db
DB_USERNAME=root
DB_PASSWORD=
```

> **Laragon:** Username default `root`, password kosong.
> **XAMPP:** Username default `root`, password kosong.

### 6. Buat Database

Buka phpMyAdmin atau MySQL CLI:

```sql
CREATE DATABASE sats_db;
```

> Di Laragon, database akan otomatis terbuat saat pertama kali diakses.

### 7. Jalankan Migrasi & Seeder

```bash
php artisan migrate --seed
```

Perintah ini akan:
- Membuat semua tabel (users, devices, sensor_datas, monitoring_sessions, sensor_readings, patients, dll.)
- Mengisi data awal: 3 akun user + 2 device + 2 API key

> Jika tabel sudah ada, gunakan `php artisan migrate:fresh --seed` untuk reset ulang.

---

## Konfigurasi

### Konfigurasi WebSocket (Reverb)

Pastikan file `.env` memiliki konfigurasi Reverb:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=sats-app
REVERB_APP_KEY=sats-key
REVERB_APP_SECRET=sats-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Konfigurasi Queue

```env
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
```

> Jika tidak ingin pakai Redis, bisa ganti ke `QUEUE_CONNECTION=database`.

---

## Menjalankan Sistem

### Pastikan Redis Berjalan

Sebelum menjalankan sistem, pastikan Redis sudah running:

```bash
# Cek Redis
redis-cli ping
# Response: PONG → Redis berjalan
# Connection refused → Redis belum running
```

- **Laragon:** Menu → Redis → Enable (otomatis jalan)
- **WSL:** `sudo service redis-server start`
- **Docker:** `docker start redis`

> Jika pakai `QUEUE_CONNECTION=database` di `.env`, Redis tidak diperlukan.

---

SATS membutuhkan **4 terminal** yang berjalan bersamaan:

### Terminal 1 — Vite (Hot Reload CSS & JS)

```bash
npm run dev
```

Vite akan berjalan dan me-reload otomatis saat ada perubahan file frontend.

### Terminal 2 — Laravel Server

```bash
php artisan serve
```

Server berjalan di `http://localhost:8000`.

> Jika pakai Laragon, akses langsung: `http://sats-repository.test`

### Terminal 3 — Queue Worker

```bash
php artisan queue:work
```

Memproses job queue: simpan sensor data ke database, trigger prediksi ML.

### Terminal 4 — WebSocket Server (Reverb)

```bash
php artisan reverb:start
```

WebSocket server berjalan di `localhost:8080`. Diperlukan untuk:
- Real-time update device status (nakes toggle → dokter langsung lihat)
- Real-time update data sensor (card + grafik sinkron)
- Instruksi dokter ↔ nakes real-time

### Ringkasan Terminal

```
Sebelumnya:  Pastikan Redis berjalan        → redis-cli ping (PONG)
Terminal 1:  npm run dev                    → Vite (CSS/JS hot reload)
Terminal 2:  php artisan serve              → Laravel HTTP server (port 8000)
Terminal 3:  php artisan queue:work         → Queue worker (proses data)
Terminal 4:  php artisan reverb:start       → WebSocket server (port 8080)
```

### Akses di Browser

```
http://localhost:8000
```

---

## Menjalankan Simulator

Simulator Python mengirim data sensor ke API untuk testing **tanpa hardware IoT**.

### Instalasi Simulator

```bash
cd simulasi_py
pip install -r requirements.txt
```

### Jalankan Simulator (1 Device)

```bash
python simulator.py --device DEVICE_01 --key test_key_device_01
```

Simulator akan mengirim data sensor setiap 5 detik ke API.

### Jalankan Banyak Device

Buka terminal terpisah untuk masing-masing device:

```bash
# Terminal 5 — Device 1 (Normal)
python simulator.py --device DEVICE_01 --key test_key_device_01

# Terminal 6 — Device 2 (Warning)
python simulator.py --device DEVICE_02 --key test_key_device_02
```

### Konfigurasi Simulator

Edit `simulasi_py/config.py` jika perlu mengubah:

```python
BASE_URL = "http://localhost:8000"   # URL Laravel
DEVICE_ID = "DEVICE_01"              # ID device
API_KEY = "test_key_device_01"       # API key device
SEND_INTERVAL = 5                    # Interval kirim data (detik)
```

### Multi-Device dengan `devices.json`

Untuk menjalankan 3 device paralel dengan profile berbeda:

```json
[
  {
    "device_id": "DEVICE_01",
    "api_key": "test_key_device_01",
    "profile": "normal",
    "interval": 2
  },
  {
    "device_id": "DEVICE_02",
    "api_key": "test_key_device_02",
    "profile": "warning",
    "interval": 2
  },
  {
    "device_id": "DEVICE_03",
    "api_key": "test_key_device_03",
    "profile": "critical",
    "interval": 2
  }
]
```

Profile simulator:

| Profile | Distribusi Data |
|---------|-----------------|
| `normal` | 95% normal, 3% warning, 2% critical |
| `warning` | 20% normal, 60% warning, 20% critical |
| `critical` | 10% normal, 20% warning, 70% critical |

---

## Alur Demo

### 1. Login sebagai Nakes (`rina@sats.id`)

1. Buka `http://localhost:8000/login`
2. Login dengan `rina@sats.id` / `password`
3. Di dashboard, pilih device dari dropdown
4. Klik **Aktifkan Perangkat** → device jadi online

### 2. Simulator Mulai Mengirim Data

- Simulator yang sebelumnya "menunggu nakes mengaktifkan" akan langsung mulai kirim data
- Di dashboard nakes, card vital sign dan grafik update **real-time** (zero delay)
- Card dan grafik **selalu sinkron** (satu WebSocket event update keduanya)

### 3. Login sebagai Dokter (`andi@sats.id`)

1. Buka tab baru, login dengan `andi@sats.id` / `password`
2. Di dashboard, dropdown menampilkan device yang online
3. Pilih device → data vital + grafik muncul **real-time**
4. Saat nakes toggle device offline, dokter dashboard **langsung kosong** (tanpa refresh)

### 4. Fitur Chat Widget (Instruksi Dokter ↔ Nakes)

1. Di dashboard dokter, klik tombol chat di pojok kanan bawah
2. Ketik instruksi medis → klik **Kirim**
3. Di tab nakes, instruksi muncul **secara real-time** (WebSocket)
4. Nakes pilih respon (quick reply) → kirim
5. Dokter melihat respon nakes **secara real-time**

### 5. Toggle Device Status

1. Di dashboard nakes, klik **Matikan Perangkat**
2. Simulator **langsung berhenti** mengirim data (zero delay, < 1 detik)
3. Di dashboard dokter, device **langsung hilang** dari dropdown
4. Klik **Aktifkan Perangkat** lagi → simulator langsung kirim data lagi

### 6. ML Prediction

1. Setelah 5 data sensor masuk, sistem otomatis trigger prediksi ML
2. Banner prediksi muncul di dashboard nakes & dokter
3. Probability card menampilkan: Membaik (%) / Stabil (%) / Memburuk (%)
4. Data di-update real-time via WebSocket

### 7. Monitoring Session & Laporan

#### a. Session Otomatis Dibuat

1. Nakes aktifkan perangkat → session otomatis dibuat (status: active)
2. Dashboard nakes menampilkan banner session aktif dengan nama pasien & nomor rekam medis
3. Data sensor masuk ke `sensor_data` (temporary) untuk realtime dashboard
4. Nakes matikan perangkat → session otomatis di-finalize:
   - Data `sensor_data` di-copy ke `sensor_readings` (dengan session_id)
   - Semua `sensor_data` untuk device dihapus
   - Session status → `completed`

#### b. Input Data Pasien

**Dari halaman Input Data Pasien (`/nakes/input-data-pasien`):**
1. Pilih perangkat dari dropdown (menampilkan device yang punya active session)
2. Isi form: nama, NIK, tanggal lahir, umur, jenis kelamin, penyakit/alergi, catatan
3. Data pasien di-link ke active session
4. Nomor rekam medis auto-generate: `RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}`

**Dari halaman Laporan (modal popup):**
1. Buka halaman laporan → pilih sesi yang belum ada data pasiennya
2. Klik tombol **"Input Data Pasien"** → modal popup muncul dengan background blur putih
3. Isi form → data pasien di-link ke session tersebut
4. Halaman otomatis update (AJAX, tanpa refresh)

#### c. Lihat & Unduh Laporan

1. Buka `/nakes/laporan`
2. Perangkat terdeteksi otomatis dari `NakesDeviceConfig` (tidak perlu dropdown)
3. Pilih sesi dari dropdown → data dimuat via **AJAX tanpa refresh halaman**
4. Centang vital sign yang ingin ditampilkan (Heart Rate, SpO2, Suhu)
5. Lihat: identitas pasien, grafik vital signs, nilai vital terbaru, statistik, tabel riwayat
6. Klik **"Unduh PDF"** → PDF di-generate dengan DomPDF + grafik dari QuickChart.io
7. Nama file: `Laporan_{nomor_rekam_medis}_{tanggal}.pdf`

#### d. Laporan Dokter (Read-Only)

1. Login sebagai dokter → buka `/dokter/laporan`
2. Pilih perangkat dari dropdown (device yang dipantau dokter)
3. Pilih sesi → data dimuat via AJAX
4. Dokter bisa lihat laporan tapi **tidak bisa edit** data pasien
5. Download PDF tersedia

---

## Akun Demo

| Role | Nama | Email | Password | Dashboard |
|------|------|-------|----------|-----------|
| Superadmin | Super Admin | `admin@sats.id` | `password` | `/superadmin/dashboard` |
| Dokter | dr. Andi | `andi@sats.id` | `password` | `/dokter/dashboard` |
| Nakes | Suster Rina | `rina@sats.id` | `password` | `/nakes/dashboard` |

### Device & API Key (dari Seeder)

| Device ID | API Key |
|-----------|---------|
| `DEVICE_01` | `test_key_device_01` |
| `DEVICE_02` | `test_key_device_02` |

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `composer install` error | Jalankan `composer update` atau pastikan PHP 8.2+ (`php -v`) |
| `npm install` error | Pastikan Node.js 18+ (`node -v`), coba `npm install --force` |
| `No application encryption key` | Jalankan `php artisan key:generate` |
| `SQLSTATE` connection error | Cek MySQL berjalan, cek konfigurasi DB di `.env` |
| `Connection refused [tcp://127.0.0.1:6379]` | Redis belum running. Laragon: Enable Redis. WSL: `sudo service redis-server start`. Docker: `docker start redis` |
| `Queue worker tidak jalan` | Pastikan Redis berjalan (`redis-cli ping` → PONG), lalu `php artisan queue:work` |
| `php artisan queue:work` error Predis | Install predis: `composer require predis/predis`, cek `REDIS_CLIENT=predis` di `.env` |
| Halaman kosong/blank | Cek `storage/logs/laravel.log`, pastikan `npm run dev` berjalan |
| CSS/JS tidak ter-load | Pastikan `npm run dev` berjalan di terminal terpisah |
| `Vite manifest not found` | Jalankan `npm run build` atau `npm run dev` |
| Migration error `table already exists` | Jalankan `php artisan migrate:fresh --seed` |
| Dashboard tidak update | Pastikan `php artisan reverb:start` + `queue:work` berjalan |
| `Pusher error: cURL error 7` | Jalankan `php artisan reverb:start` di terminal terpisah |
| ML prediction tidak muncul | Pastikan `queue:work` berjalan, cek log untuk "ML trigger" |
| Simulator `ModuleNotFoundError` | Jalankan `pip install -r requirements.txt` di folder `simulasi_py/` |
| Simulator `Connection refused` | Pastikan Laravel server berjalan di `http://localhost:8000` |
| Simulator `401 Unauthorized` | Cek API key di `config.py` cocok dengan yang di-seed |
| Laporan kosong / tidak ada sesi | Pastikan pernah aktifkan device & data masuk. Cek `monitoring_sessions` di DB |
| `sensor_data` masih ada isinya | Jalankan `php artisan tinker` → `SensorData::where('device_id','...')->delete()` |
| Modal input pasien tidak muncul | Cek console browser, pastikan tidak ada Alpine.js error |
| AJAX session load gagal | Cek Network tab di DevTools, pastikan `/nakes/laporan/session-data` return 200 |
| PDF download error | Pastikan `php artisan queue:work` berjalan & QuickChart.io bisa diakses |

---

## Catatan: Simulator vs IoT Asli

### Saat Ini: Python Simulator

Saat ini sistem menggunakan **simulator Python** (`simulasi_py/`) untuk mengirim data sensor ke API. Simulator ini meniru perilaku perangkat IoT asli:

- Mengirim data vital sign (heart rate, SpO2, temperature) setiap N detik
- Mengirim system status (battery, signal)
- Mengecek status device (online/offline) → berhenti/kirim otomatis
- Distribusi data realistis: 90% normal, 7% warning, 3% critical

**Kenapa pakai simulator?**
- Memudahkan testing tanpa hardware
- Bisa menjalankan banyak device paralel
- Data konsisten dan predictable untuk demo
- Tidak perlu koneksi WiFi atau perangkat fisik

### Nanti: Perangkat IoT Asli

Ketika sudah ada hardware IoT (Arduino/ESP32 + sensor), simulator bisa **langsung diganti** tanpa mengubah kode backend. Yang perlu dilakukan:

1. **Konfigurasi Arduino/ESP32** untuk mengirim HTTP POST ke endpoint yang sama:
   ```
   POST http://<server>/api/device/{device_id}/sensor-data
   Header: X-API-Key: <api_key>
   Body: {"heart_rate": 85, "spo2": 98, "temperature": 36.5}
   ```

2. **Daftarkan device baru** via dashboard superadmin → Manajemen Alat → Tambah Alat

3. **Gunakan API key** yang di-generate saat registrasi device

4. **Interval pengiriman**: 5-30 detik (sesuaikan dengan kebutuhan)

### Perbandingan

| Aspek | Simulator (Python) | IoT Asli (Arduino) |
|-------|-------------------|-------------------|
| Data source | Script Python (random + profile) | Sensor fisik (MAX30102, DS18B20) |
| Koneksi | HTTP ke localhost | HTTP/WiFi ke server |
| Multiple device | Terminal terpisah | Perangkat fisik terpisah |
| Battery simulation | Simulasi (angka random) | Pembacaan ADC real |
| Signal strength | Simulasi | Pembacaan RSSI WiFi real |
| Setup | `python simulator.py` | Flash Arduino + koneksi WiFi |
| Kapan dipakai | Development & demo | Produksi di ambulans |

### Endpoint yang Sama

Baik simulator maupun perangkat IoT asli menggunakan **endpoint yang sama persis**:

```
POST /api/device/{device_id}/sensor-data      ← Kirim vital sign
POST /api/device/{device_id}/system-status    ← Kirim battery/signal
GET  /api/device/{device_id}/status           ← Cek status (opsional)
```

Tidak ada perubahan kode backend yang diperlukan.

---

## Dokumentasi Terkait

| File | Deskripsi |
|------|-----------|
| [README.md](README.md) | Gambaran umum project |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Dokumentasi API lengkap + integrasi ML |
| [MASTER_BRIEF.md](MASTER_BRIEF.md) | Ringkasan lengkap project |
| [BACKEND.md](BACKEND.md) | Arsitektur backend |
| [DATABASE.md](DATABASE.md) | Struktur database |
| [FRONTEND.md](FRONTEND.md) | Struktur frontend |
| [LAPORAN_SYSTEM.md](LAPORAN_SYSTEM.md) | Desain & implementasi sistem laporan & monitoring session |

---

*Last updated: 24 Mei 2026*
