<div align="center">

# SATS

### Smart Ambulance Telemedicine System

Sistem telemedicine ambulans cerdas yang mengintegrasikan perangkat IoT dengan web dashboard untuk memantau tanda vital pasien secara real-time selama transportasi ambulans.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.2-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3-8BC0D0?style=for-the-badge&logo=alpine.js&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Redis](https://img.shields.io/badge/Redis-DC382D?style=for-the-badge&logo=redis&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.8+-3776AB?style=for-the-badge&logo=python&logoColor=white)

</div>

---

## Gambaran Umum

SATS dirancang untuk:

- **Memantau tanda vital pasien** (heart rate, SpO2, suhu tubuh) secara real-time selama perjalanan ambulans
- **Menghubungkan perawat (nakes) di ambulans dengan dokter** di rumah sakit tujuan melalui workflow instruksi
- **Prediksi kondisi pasien** menggunakan Machine Learning (Hugging Face Spaces)
- **Dashboard monitoring** dengan grafik interaktif dan notifikasi kondisi kritis
- **Laporan medis** dalam format HTML dan PDF

### Alur Sistem

```
Perangkat IoT (Sensor HR, SpO2, Suhu)
        |
        v
HTTP POST ke API Laravel
        |
        v
WebSocket Broadcast (real-time)  +  Queue Job (simpan ke DB)
        |
        v
Dashboard Nakes/Dokter (real-time, zero polling)
        |
        v
ML Prediksi (Hugging Face Spaces)
        |
        v
Dokter mengirim instruksi --> Nakes merespon
        |
        v
Laporan Medis + PDF
```

---

## Fitur Utama

### Monitoring Real-time
- Dashboard vital sign dengan grafik Chart.js
- Zero polling — menggunakan **Laravel Reverb WebSocket**
- Card dan grafik selalu sinkron (satu event update keduanya)
- Toggle device online/offline dari dashboard nakes

### Sistem Instruksi Dokter-Nakes
- Dokter mengirim instruksi medis dari dashboard
- Nakes merespon dengan dropdown opsi + checklist
- Real-time via WebSocket broadcasting
- Floating chat widget di pojok kanan bawah

### Machine Learning
- Prediksi kondisi pasien: **Normal / Warning / Critical**
- Probabilitas: Membaik / Stabil / Memburuk (%)
- Model di-hosting di **Hugging Face Spaces** (Gradio API)
- Trigger otomatis setiap 5 data sensor baru

### Manajemen Perangkat (Superadmin)
- CRUD perangkat IoT
- Auto-generate API key saat registrasi
- Monitoring status semua perangkat

### Laporan & PDF
- Laporan medis dengan chart dan filter tanggal
- Download PDF via DomPDF + QuickChart.io
- Role-aware (nakes, dokter, superadmin)

---

## Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Backend** | Laravel 12 (PHP 8.2+) |
| **Frontend** | Blade + Tailwind CSS v4.2 + Alpine.js v3 |
| **Charting** | Chart.js v4.4.1 |
| **WebSocket** | Laravel Reverb v1.0 |
| **Queue** | Redis (predis/predis v3.4) + Database driver |
| **PDF** | barryvdh/laravel-dompdf v3.1 |
| **Build** | Vite v6 |
| **Database** | MySQL 8.x |
| **ML** | Hugging Face Spaces (Gradio async API) |
| **IoT Simulator** | Python 3 + requests |

---

## Sistem Role

| Role | Akses | Fitur Utama |
|------|-------|-------------|
| **Nakes** (Perawat) | `/nakes/*` | Monitoring, respon instruksi, input data pasien, laporan |
| **Dokter** | `/dokter/*` | Monitoring, kirim instruksi, input data pasien, laporan |
| **Superadmin** | `/superadmin/*` | Dashboard admin, manajemen alat & user, laporan |

---

## Quick Start

### Prasyarat

| Software | Versi Minimum |
|----------|---------------|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| MySQL | 8.x |
| Python | 3.8+ (opsional, untuk simulator) |

### Instalasi

```bash
# 1. Clone repository
git clone https://github.com/AdityaWijayanto19/SATS_Repository.git
cd SATS_Repository

# 2. Install dependencies
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Konfigurasi database di .env
# DB_CONNECTION=mysql
# DB_DATABASE=sats_db
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Buat database
# CREATE DATABASE sats_db;

# 6. Jalankan migrasi & seeder
php artisan migrate --seed

# 7. Jalankan development server (4 terminal)
npm run dev                    # Terminal 1: Vite
php artisan serve              # Terminal 2: Laravel
php artisan queue:work         # Terminal 3: Queue worker
php artisan reverb:start       # Terminal 4: WebSocket server
```

### Jalankan Simulator (Opsional)

```bash
cd simulasi_py
pip install -r requirements.txt
python simulator.py --device DEVICE_01 --key test_key_device_01
```

---

## Akun Demo

| Role | Nama | Email | Password |
|------|------|-------|----------|
| Superadmin | Super Admin | `admin@sats.id` | `password` |
| Dokter | Dr. Andi | `andi@sats.id` | `password` |
| Nakes | Suster Rina | `rina@sats.id` | `password` |

---

## Struktur Project

```
app/
├── Http/Controllers/          # Web & API controllers
├── Models/                    # 9 Eloquent models
├── Services/                  # Business logic layer
├── Events/                    # WebSocket broadcast events
├── Jobs/                      # Queue processing
└── Middleware/                 # Auth, API key, rate limit

resources/views/
├── components/                # Navbar, Sidebar, Chat Widget
├── layouts/                   # App & Auth layouts
└── pages/
    ├── nakes/                 # Dashboard, Input, Laporan, Instruksi
    ├── dokter/                # Dashboard, Input, Laporan, Instruksi
    └── superadmin/            # Dashboard, Alat, User, Laporan

simulasi_py/                   # IoT Device Simulator (Python)
```

---

## API Endpoints

### Device API (API Key Auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/device/{id}/authenticate` | Autentikasi device |
| POST | `/api/device/{id}/sensor-data` | Kirim data sensor |
| GET | `/api/device/{id}/sensor-data/latest` | Data sensor terbaru |
| GET | `/api/device/{id}/sensor-data/history` | Riwayat sensor |
| POST | `/api/device/{id}/system-status` | Kirim status sistem |
| GET | `/api/device/{id}/system-status` | Ambil status sistem |

### Instruction API (Session Auth)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/instruction` | Ambil instruksi per device |
| POST | `/api/instruction` | Kirim instruksi (dokter) |
| POST | `/api/instruction/report` | Submit laporan (nakes) |
| PATCH | `/api/instruction/{id}/complete` | Selesaikan instruksi |

Dokumentasi API lengkap: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

---

## Dokumentasi

| File | Deskripsi |
|------|-----------|
| [MASTER_BRIEF.md](MASTER_BRIEF.md) | Ringkasan lengkap project, alur sistem, fitur, progress |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Dokumentasi API + integrasi ML Hugging Face |
| [BACKEND.md](BACKEND.md) | Arsitektur backend, service layer, simulator |
| [DATABASE.md](DATABASE.md) | Struktur database, ERD, relasi |
| [FRONTEND.md](FRONTEND.md) | Struktur frontend, routes, status fitur |

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `composer install` error | Jalankan `composer update`, pastikan PHP 8.2+ |
| `npm install` error | Pastikan Node.js 18+, coba `npm install --force` |
| `No application encryption key` | `php artisan key:generate` |
| Database connection error | Cek MySQL berjalan, cek `.env` |
| CSS/JS tidak ter-load | Jalankan `npm run dev` |
| Dashboard tidak update | Pastikan `php artisan reverb:start` + `queue:work` berjalan |
| ML prediction tidak muncul | Pastikan `queue:work` berjalan, cek log |
| Simulator error | `pip install -r requirements.txt` di folder `simulasi_py/` |

---

## Tim Pengembang

| Anggota | Role | Cakupan Kerja |
|---------|------|---------------|
| **Dalvero** | Frontend | UI/UX Blade templates, chart, layout, chat widget |
| **Aditya** | Backend | API, database, integrasi IoT, ML |

---

## License

Project ini dibuat sebagai bagian dari UAS (Ujian Akhir Semester).

---

<div align="center">

**Built with Laravel 12, Tailwind CSS, Alpine.js, and ❤️**

</div>
