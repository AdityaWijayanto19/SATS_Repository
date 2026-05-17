# SATS - Panduan Demo

## Akun Demo

| Role | Nama | Email | Password |
|------|------|-------|----------|
| **Superadmin** | Super Admin | `admin@sats.id` | `password` |
| **Dokter** | Dr. Andi Wijaya | `andi@sats.id` | `password` |
| **Dokter** | Dr. Budi Santoso | `budi@sats.id` | `password` |
| **Dokter** | Dr. Citra Dewi | `citra@sats.id` | `password` |
| **Nakes** | Suster Rina | `rina@sats.id` | `password` |
| **Nakes** | Perawat Dian | `dian@sats.id` | `password` |
| **Nakes** | Perawat Eka | `eka@sats.id` | `password` |

> Semua password: `password`

---

## Persiapan (Sekali Saja)

### 1. Setup Database

```bash
php artisan migrate:fresh --seed
```

Perintah ini akan:
- Hapus semua tabel, buat ulang
- Jalankan seeder: 7 akun user + 3 device + 3 API key
- **Catat API key** yang muncul di terminal (untuk `devices.json`)

### 2. Update `simulasi_py/devices.json`

Setelah seeder, catat API key dari output terminal, lalu update `devices.json`:

```json
[
  {
    "device_id": "DEV_01",
    "api_key": "sats_xxxxxxxx",
    "profile": "normal",
    "interval": 2
  },
  {
    "device_id": "DEV_02",
    "api_key": "sats_yyyyyyyy",
    "profile": "warning",
    "interval": 2
  },
  {
    "device_id": "DEV_03",
    "api_key": "sats_zzzzzzzz",
    "profile": "critical",
    "interval": 2
  }
]
```

> Ganti `sats_xxxxxxxx` dengan API key asli dari output seeder.

---

## Menjalankan Demo

Buka **4 terminal** terpisah, jalankan urut:

### Terminal 1 — Laravel Server

```bash
php artisan serve
```

Server berjalan di `http://localhost:8000`

### Terminal 2 — Reverb WebSocket Server

```bash
php artisan reverb:start
```

WebSocket server berjalan di `localhost:8080`. Diperlukan untuk:
- Realtime update device status (nakes toggle → dokter langsung lihat)
- Realtime update data sensor (card + grafik sinkron)
- Instruksi dokter↔nakes realtime

### Terminal 3 — Queue Worker

```bash
php artisan queue:work
```

Memproses job queue (sensor data masuk ke database).

### Terminal 4 — Simulator IoT

```bash
cd simulasi_py
python simulator.py
```

Simulator mengirim data sensor setiap 2 detik untuk 3 device paralel:
- `DEV_01` (profile: normal) — data vital normal
- `DEV_02` (profile: warning) — data vital warning
- `DEV_03` (profile: critical) — data vital kritis

---

## Alur Demo

### 1. Login sebagai Nakes (`rina@sats.id`)

1. Buka `http://localhost:8000/login`
2. Login dengan `rina@sats.id` / `password`
3. Di dashboard, klik **Setup Perangkat**
4. Masukkan WiFi name, WiFi password, dan API key salah satu device
5. Setelah setup, tombol **Aktifkan Perangkat** muncul
6. Klik **Aktifkan Perangkat** → device jadi online

### 2. Simulator Mulai Mengirim Data

- Simulator yang sebelumnya "menunggu nakes mengaktifkan" akan langsung mulai kirim data
- Di dashboard nakes, card vital sign dan grafik update **realtime** (zero delay)
- Card dan grafik **selalu sinkron** (satu WebSocket event update keduanya)

### 3. Login sebagai Dokter (`andi@sats.id`)

1. Buka tab baru, login dengan `andi@sats.id` / `password`
2. Di dashboard, dropdown menampilkan device yang online
3. Pilih device → data vital + grafik muncul **realtime**
4. Saat nakes toggle device offline, dokter dashboard **langsung kosong** (tanpa refresh)

### 4. Fitur Instruksi Dokter→Nakes

1. Di dashboard dokter, scroll ke bawah ke panel **Instruksi**
2. Ketik instruksi medis → klik **Kirim**
3. Di tab nakes, instruksi muncul **secara realtime** (WebSocket)
4. Nakes pilih respon (dropdown) → centang instruksi
5. Dokter melihat respon nakes **secara realtime**

### 5. Toggle Device Status

1. Di dashboard nakes, klik **Matikan Perangkat**
2. Simulator **langsung berhenti** mengirim data (zero delay, < 1 detik)
3. Di dashboard dokter, device **langsung hilang** dari dropdown
4. Di manajemen alat superadmin, status device **langsung berubah** ke offline
5. Klik **Aktifkan Perangkat** lagi → simulator langsung kirim data lagi

### 6. Multi-Device (Opsional)

1. Login nakes berbeda (`dian@sats.id`, `eka@sats.id`) di tab terpisah
2. Setup device berbeda (DEV_02, DEV_03)
3. Aktifkan perangkat masing-masing
4. Login dokter → dropdown menampilkan semua device online
5. Switch antar device → data berbeda sesuai profile (normal/warning/critical)

---

## Profile Simulator

| Profile | Normal | Warning | Critical |
|---------|--------|---------|----------|
| `normal` | 95% | 3% | 2% |
| `warning` | 20% | 60% | 20% |
| `critical` | 10% | 20% | 70% |

---

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| Data tidak muncul di dashboard | Pastikan `php artisan queue:work` berjalan |
| WebSocket tidak konek | Pastikan `php artisan reverb:start` berjalan |
| Simulator "menunggu nakes" | Login nakes → setup device → klik Aktifkan |
| API key tidak valid | Jalankan `php artisan migrate:fresh --seed`, catat key baru, update `devices.json` |
| Grafik beda dengan card | Sudah di-fix: satu WebSocket event update keduanya |

---

## Ringkasan Terminal

```
Terminal 1:  php artisan serve              → HTTP server (port 8000)
Terminal 2:  php artisan reverb:start       → WebSocket server (port 8080)
Terminal 3:  php artisan queue:work         → Queue worker
Terminal 4:  cd simulasi_py && python simulator.py  → Simulator IoT
```
