<div align="center">

# SATS

### Smart Ambulance Telemedicine System

Sistem telemedicine ambulans cerdas yang mengintegrasikan perangkat IoT dengan web dashboard untuk memantau tanda vital pasien secara real-time selama transportasi ambulans.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white)
![ESP32](https://img.shields.io/badge/ESP32-IoT-00979D?style=for-the-badge&logo=espressif&logoColor=white)

</div>

---

## Gambaran Umum

SATS adalah sistem monitoring vital sign pasien berbasis IoT yang dirancang untuk ambulans. Sistem ini menghubungkan data sensor fisik (heart rate, SpO2, suhu tubuh) dengan dashboard web secara real-time, memungkinkan tenaga medis memantau kondisi pasien selama perjalanan ke rumah sakit.

### Alur Sistem

```
ESP32 + Sensor (MAX30102, DS18B20)
        │
        ▼
HTTP POST → Laravel API
        │
        ├──► WebSocket Broadcast → Dashboard (real-time, zero delay)
        │
        ├──► Queue Job → Database
        │
        └──► ML Prediction (Hugging Face Spaces)
                │
                ▼
        Dokter kirim instruksi → Nakes merespon
                │
                ▼
        Rekam Medis + Laporan PDF
```

---

## Fitur Utama

### Monitoring Real-Time
- Dashboard vital sign dengan grafik Chart.js (mode terpisah & gabungan)
- Zero polling — menggunakan **Laravel Reverb WebSocket**
- Data sampai ke browser sebelum ditulis ke database (zero-latency broadcast)
- Auto-offline detection (scheduler 5 detik timeout)
- Auto-reactivate saat device mengirim data kembali

### Komunikasi Dokter ↔ Nakes
- Dokter mengirim instruksi medis dari dashboard
- Nakes merespon dengan quick reply buttons
- Real-time via WebSocket broadcasting
- Floating chat widget dengan foto profil

### Machine Learning
- Prediksi kondisi pasien 5 menit ke depan
- Probabilitas: Membaik / Stabil / Memburuk (%)
- Model Random Forest di-hosting di **Hugging Face Spaces**
- Trigger otomatis setiap 5 data valid baru
- Mendukung 4 kategori usia: Balita, Anak-anak, Dewasa, Lansia

### Manajemen (Superadmin)
- Dashboard ringkasan sistem dengan log aktivitas real-time
- Manajemen perangkat IoT (CRUD + auto-generate API key)
- Manajemen pengguna (dokter, nakes)
- Manajemen rekam medis (search, filter, delete, PDF)
- Inbox support untuk laporan dari pengguna

### Laporan & Rekam Medis
- Monitoring session otomatis (device ON → session aktif, device OFF → finalize)
- Medical record number auto-generate: `RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}`
- Download PDF via DomPDF + QuickChart.io
- Filter waktu untuk data spesifik

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Backend** | Laravel 12, PHP 8.3 |
| **Frontend** | Blade, Tailwind CSS v4, Alpine.js v3 |
| **Charting** | Chart.js v4.4.1 |
| **WebSocket** | Laravel Reverb |
| **Queue** | Laravel Queue (Redis driver) |
| **Cache** | Redis |
| **PDF** | DomPDF + QuickChart.io |
| **ML** | Hugging Face Spaces (Gradio) |
| **Database** | MySQL 8 |
| **Build** | Vite v6 |
| **Hardware** | ESP32, MAX30102, DS18B20 |

---

## Sistem Role

| Role | Akses | Fitur Utama |
|------|-------|-------------|
| **Nakes** | `/nakes/*` | Monitoring, setup device, input data pasien, instruksi, laporan |
| **Dokter** | `/dokter/*` | Monitoring multi-device, instruksi, rekam medis, laporan |
| **Superadmin** | `/superadmin/*` | Dashboard, manajemen alat/user/rekam medis, laporan, inbox |

---

## Quick Start

### Prasyarat

| Software | Versi |
|----------|-------|
| PHP | 8.3+ |
| Composer | 2.x |
| Node.js | 18+ |
| MySQL | 8.x |
| Redis | 6.x+ |

### Instalasi

```bash
# Clone repository
git clone https://github.com/AdityaWijayanto19/SATS_Repository.git
cd SATS_Repository

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Konfigurasi database di .env
# DB_DATABASE=sats_db
# DB_USERNAME=root
# DB_PASSWORD=

# Buat database & jalankan migrasi
CREATE DATABASE sats_db;
php artisan migrate --seed
```

### Jalankan Sistem (5 Terminal)

```bash
# Terminal 1: Vite (hot reload CSS/JS)
npm run dev

# Terminal 2: Laravel server
php artisan serve

# Terminal 3: Queue worker
php artisan queue:work

# Terminal 4: WebSocket server
php artisan reverb:start

# Terminal 5: Scheduler (auto-offline)
php artisan schedule:work
```

### Jalankan Simulator (Opsional)

```bash
cd simulasi_py
pip install -r requirements.txt
python simulator.py
```

---

## Akun Demo

| Role | Email | Password |
|------|-------|----------|
| Superadmin | admin@sats.id | password |
| Dokter | andi@sats.id | password |
| Nakes | rina@sats.id | password |

---

## Struktur Project

```
app/
├── Http/Controllers/      # 15 controllers (web + API)
├── Models/                # 15 Eloquent models
├── Services/              # 13 service classes
├── Events/                # 8 WebSocket events
├── Jobs/                  # 2 queue jobs
└── Middleware/             # 7 custom middleware

resources/views/
├── components/            # Sidebar, Navbar, Chat Widget
├── layouts/               # App, Auth, Landing
└── pages/
    ├── landing/           # 7 sections
    ├── nakes/             # Dashboard, Input, Laporan, Instruksi, Monitoring
    ├── dokter/            # Dashboard, Rekam Medis, Instruksi, Monitoring
    └── superadmin/        # Dashboard, Manajemen, Rekam Medis, Laporan, Inbox

simulasi_py/               # IoT Device Simulator (Python)
```

---

## Dokumentasi

| File | Deskripsi |
|------|-----------|
| [MASTER_DOCS.md](docs/MASTER_DOCS.md) | Dokumentasi teknis lengkap |
| [DEMO.md](docs/DEMO.md) | Panduan instalasi & demo |

---

## License

Project ini dibuat sebagai bagian dari tugas akhir universitas.

---

<div align="center">

**Built with Laravel 12, Tailwind CSS, Alpine.js, ESP32, and ❤️**

</div>
