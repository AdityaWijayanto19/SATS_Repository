# SATS - Dokumentasi Sistem Laporan & Monitoring Session

## Overview

Dokumentasi ini menjelaskan desain sistem laporan dan monitoring session untuk SATS. Pembahasan berawal dari problem fundamental: **device IoT tidak tahu data vital yang dikirim milik siapa**. Ketika satu device digunakan untuk banyak pasien secara bergantian, diperlukan mekanisme untuk mengikat data sensor ke pasien yang tepat.

**Konteks:**
- Nakes di ambulans membawa pasien, menyalakan perangkat, monitoring berjalan
- Pasien tiba di RS, perangkat dimatikan
- Nakes dapat pasien baru, menyalakan perangkat lagi
- Semua data sensor dari device yang sama → bagaimana membedakan milik pasien mana?

**Solusi:** Monitoring session — setiap kali device ON/OFF adalah batas antar sesi pasien.

---

## Interval Pengiriman Data

> **PENTING:** Device mengirim data setiap **1-2 detik**, bukan 5 detik.

| Aspek | Keterangan |
|-------|------------|
| Interval | 1-2 detik per pengiriman |
| Data per pengiriman | heart_rate, spo2, temperature |
| Volume per menit | ~30-60 data points per device |
| Volume per sesi (30 menit) | ~900-1800 data points |

Dampak pada desain:
- Tabel `sensor_data` akan sangat cepat terisi
- Proses copy ke `sensor_readings` saat finalize harus efisien
- Query laporan perlu index yang tepat (`device_id` + `created_at`)
- Realtime dashboard tidak masalah (sudah WebSocket, zero polling)

---

## Desain Sistem

### 1. Monitoring Session

Setiap kali nakes mengaktifkan device, sistem membuat **monitoring session**. Session ini adalah "wadah" yang mengikat data sensor ke pasien.

#### Status Session

| Status | Arti | Kapan |
|--------|------|-------|
| `active` | Sedang berjalan | Device ON, data masuk |
| `pending` | Baru dimatikan, menunggu konfirmasi | Device OFF, grace period 10 menit |
| `completed` | Sudah difinalisasi | Data sudah dipindah ke sensor_readings, ada data pasien |
| `cancelled` | Dibatalkan | Tidak ada pasien / data dibuang |

#### Grace Period (Toggle Tidak Sengaja)

```
Nakes salah matikan device
  → Session masuk status "pending" (bukan langsung finalize)
  → Timer 10 menit mulai
  → Nakes nyalakan device lagi dalam 10 menit
    → Session dilanjutkan (status: active), bukan sesi baru
  → Timer habis (10 menit)
    → Session difinalisasi otomatis
```

#### Toggle ON — Konfirmasi Pasien

```
Device ON (dari perangkat)
  → Server buat monitoring_sessions (status: active)
  → Dashboard muncul popup: "Ada pasien yang dimonitoring?"
    → "Ya" → Form input data pasien muncul
    → "Tidak" → Session tetap active, data masuk tapi tanpa identitas pasien
  → Device tetap kirim data (tidak berhenti menunggu konfirmasi)
  → Data masuk ke sensor_data (ditampung dulu)
  → Setelah nakes konfirmasi → data di-link ke pasien
```

#### Toggle OFF — Finalisasi

```
Device OFF (dari perangkat)
  → Session masuk status "pending"
  → Dashboard muncul konfirmasi: "Akhiri monitoring?"
    → "Ya" → Finalize session:
      1. Copy sensor_data → sensor_readings (dengan session_id)
      2. Update session → status: completed
      3. Kosongkan sensor_data untuk sesi ini
    → "Batal" → Session kembali active, device tetap mati
                 (nakes bisa nyalakan lagi, session dilanjutkan)
```

---

### 2. Alur End-to-End

```
┌─────────────────────────────────────────────────────────────┐
│                                                             │
│  1. Nakes tekan ON di perangkat                             │
│     │                                                       │
│     ▼                                                       │
│  2. Device POST status: active → Server                     │
│     → Buat monitoring_sessions (status: active)             │
│     → Broadcast ke dashboard (WebSocket)                    │
│     │                                                       │
│     ▼                                                       │
│  3. Dashboard muncul popup: "Ada pasien?"                   │
│     → [Ya] → Form input data pasien                         │
│     → [Tidak] → Lanjut monitoring tanpa identitas           │
│     │                                                       │
│     ▼                                                       │
│  4. Device kirim data setiap 1-2 detik                      │
│     → Masuk sensor_data (raw, temporary)                    │
│     → Dashboard tampilkan realtime (WebSocket)              │
│     │                                                       │
│     ▼                                                       │
│  5. [Monitoring berjalan...]                                │
│     │                                                       │
│     ▼                                                       │
│  6. Pasien tiba di RS → Nakes tekan OFF di perangkat        │
│     │                                                       │
│     ▼                                                       │
│  7. Device POST status: inactive → Server                   │
│     → Session masuk status: pending                         │
│     → Dashboard muncul konfirmasi: "Akhiri monitoring?"      │
│     │                                                       │
│     ▼                                                       │
│  8. Nakes klik "Ya, Akhiri"                                 │
│     → Copy sensor_data → sensor_readings                    │
│     → Session → status: completed                           │
│     → sensor_data dikosongkan                               │
│     │                                                       │
│     ▼                                                       │
│  9. Laporan siap diunduh                                    │
│     → Filter: pilih pasien/sesi + rentang waktu             │
│     → Pilih vital sign yang diunduh                         │
│     → Download PDF (nama file = nomor rekam medis)          │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

---

### 3. Nomor Rekam Medis

#### Format: Auto Generate

```
RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}
```

| Komponen | Contoh | Keterangan |
|----------|--------|------------|
| Prefix | `RM` | Tetap |
| Device ID | `DEVICE_01` | Dari device yang digunakan |
| Tanggal | `20260524` | Tanggal sesi dimulai |
| Sequence | `001` | Urutan sesi hari itu |

**Contoh:**
```
RM-DEVICE_01-20260524-001  ← Sesi pertama hari itu
RM-DEVICE_01-20260524-002  ← Sesi kedua
RM-DEVICE_01-20260524-003  ← Sesi ketiga
RM-DEVICE_02-20260524-001  ← Device lain, sequence mulai dari 001 lagi
```

#### Kenapa Auto Generate?

| Opsi | Kelebihan | Kekurangan |
|------|-----------|------------|
| **Auto Generate** | Konsisten, tidak mungkin duplikat, nakes tidak perlu mikir | Format mungkin tidak sesuai standar RS |
| Manual (nakes isi) | Sesuai standar RS, fleksibel | Bisa salah input, bisa duplikat, wajib validasi |

**Pilihan: Auto Generate** — nakes di ambulance sedang sibuk, tidak perlu ingat nomor. Jika RS punya format sendiri, bisa diubah di config nanti.

#### Data Pasien

| Field | Wajib? | Keterangan |
|-------|--------|------------|
| Nomor Rekam Medis | **Auto** | Di-generate sistem |
| Nama | Opsional | Tapi warning jika kosong saat download |
| NIK | Opsional | Tapi warning jika kosong saat download |
| Jenis Kelamin | Opsional | |
| Umur | Opsional | |
| Penyakit/Alergi | Opsional | |
| Catatan | Opsional | |

**Aturan:** Data pasien opsional, tapi jika kosong → warning saat download laporan.

---

### 4. Ownership & Pencegahan Konflik

#### Masalah

Dokter dan nakes bisa input data pasien di waktu yang sama untuk sesi yang sama → data duplikat.

#### Solusi: Ownership by Session

```
monitoring_sessions:
  created_by    → user_id (nakes yang toggle ON)
  patient_id    → FK ke patients (bisa null awalnya)
```

#### Aturan

| Siapa | Bisa | Tidak Bisa |
|-------|------|------------|
| **Nakes** | Buat sesi, input data pasien, download laporan | - |
| **Dokter** | Lihat laporan, verifikasi data, tambah catatan medis | Edit data identitas pasien |
| **Superadmin** | Lihat semua laporan | Edit data pasien |

#### Mencegah Duplikasi

1. **Satu sesi = satu data pasien** — `patient_id` unique per session
2. **Hanya nakes yang input** — dokter tidak bisa edit identitas
3. **Dokter bisa verifikasi** — field `patient_verified_by` (opsional)
4. **Kalau dokter juga mau input** — redirect ke halaman yang sama, tampilkan data yang sudah ada, bukan form baru

---

### 5. Filter Laporan

#### UI Filter

```
┌─────────────────────────────────────────────────────┐
│  Filter Laporan                                     │
│                                                     │
│  Pilih Pasien / Sesi:                               │
│  ┌─────────────────────────────────────────────┐    │
│  │ ▸ Pasien A — 24 Mei 2026 (08:00 - 08:45)   │    │
│  │   Pasien B — 24 Mei 2026 (09:00 - 09:30)   │    │
│  │   Tanpa Identitas — 24 Mei 2026 (10:00-10:20)│   │
│  └─────────────────────────────────────────────┘    │
│                                                     │
│  Atau pilih manual:                                 │
│  Dari: [tanggal] [jam]  Sampai: [tanggal] [jam]    │
│                                                     │
│  [Terapkan Filter]                                  │
└─────────────────────────────────────────────────────┘
```

#### Filter yang Tersedia

| Filter | Tipe | Keterangan |
|--------|------|------------|
| Pasien/Sesi | Dropdown | Daftar sesi berdasarkan device nakes |
| Rentang Waktu | Date + Time picker | Manual override jika tidak pilih sesi |
| Device | Dropdown (otomatis) | Hanya device yang ditugaskan ke nakes |

---

### 6. Variasi Download Laporan

#### Pilih Vital Sign

```
┌─────────────────────────────────────────────┐
│  Pilih Data Vital yang Akan Diunduh:        │
│                                             │
│  ○ Semua Data (lengkap)                     │
│  ○ Pilih Manual:                            │
│     ☑ Heart Rate (BPM)                      │
│     ☑ SpO2 (%)                              │
│     ☐ Suhu (°C)                             │
│                                             │
│  Format Laporan:                            │
│  ○ Lengkap (identitas + grafik + tabel)     │
│  ○ Ringkas (tabel data saja)                │
│                                             │
│  [Download PDF]                             │
└─────────────────────────────────────────────┘
```

#### Opsi Format

| Format | Isi | Cocok Untuk |
|--------|-----|-------------|
| **Lengkap** | Identitas pasien + grafik vital sign + tabel data + prediksi ML | Arsip medis, laporan RS |
| **Ringkas** | Tabel data saja (waktu, HR, SpO2, Temp, status) | Cepat, keperluan internal |

#### Nama File Default

```
Laporan-{nomor_rekam_medis}.pdf
```

**Contoh:**
```
Laporan-RM-DEVICE_01-20260524-001.pdf
```

**Jika belum ada data pasien:**
```
Laporan-DEVICE_01-20260524-TanpaIdentitas.pdf
```

---

### 7. Warning & Konfirmasi vs Device

#### Prinsip: Device Tidak Perlu Tahu Popup Dashboard

| Komponen | Tugas |
|----------|-------|
| **Device** | Kirim data sensor, kirim status ON/OFF. Selesai. |
| **Server** | Terima data, deteksi warning, simpan, broadcast ke dashboard |
| **Dashboard** | Tampilkan popup/warning, nakes konfirmasi, simpan ke server |

#### Analogi

```
Thermometer mengukur suhu 39°C → angkanya naik
Thermometer TIDAK perlu tahu bahwa dokter sudah
melihat angka itu dan mengatakan "saya tangani"

Thermometer hanya mengukur. Keputusan ada di manusia.
```

#### Flow Warning

```
Device → kirim data (HR: 150, SpO2: 85) setiap 1-2 detik
        |
        v
Server → deteksi: ini WARNING/CRITICAL
        |→ simpan ke sensor_data
        |→ broadcast ke dashboard (WebSocket)
        |
        v
Dashboard → tampilkan warning banner/card
        |→ Popup: "Ada pasien yang dimonitoring?"
        |→ Nakes konfirmasi
        |
        v
Server → simpan konfirmasi (siapa, kapan, catatan)
```

#### Data Saat Popup Muncul

```
Device ON → kirim data terus (tidak berhenti)
  → Data masuk sensor_data (ditampung)

Nakes klik "Ya, ada pasien" → form input → data di-link ke pasien
Nakes klik "Tidak ada" → session cancelled, data dihapus/diabaikan

Device TIDAK perlu berhenti. Server yang atur data mau diapakan.
```

---

## Struktur Tabel Baru

### `monitoring_sessions`

```sql
CREATE TABLE monitoring_sessions (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    device_id       VARCHAR(50) NOT NULL,           -- FK → devices.device_id
    patient_id      BIGINT UNSIGNED NULL,           -- FK → patients.id (bisa null)
    medical_record_number VARCHAR(50) NOT NULL,     -- Auto generate: RM-DEVICE-DATE-SEQ
    created_by      BIGINT UNSIGNED NOT NULL,       -- FK → users.id (nakes yang toggle ON)
    started_at      TIMESTAMP NOT NULL,             -- Waktu device ON
    ended_at        TIMESTAMP NULL,                 -- Waktu device OFF + finalize
    status          ENUM('active','pending','completed','cancelled') DEFAULT 'active',
    total_readings  INT DEFAULT 0,                  -- Jumlah data sensor di sesi ini
    notes           TEXT NULL,                      -- Catatan nakes (opsional)
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_device_status (device_id, status),
    INDEX idx_created_by (created_by),
    INDEX idx_medical_record (medical_record_number),
    FOREIGN KEY (device_id) REFERENCES devices(device_id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
```

### `sensor_readings`

```sql
CREATE TABLE sensor_readings (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id      BIGINT UNSIGNED NOT NULL,       -- FK → monitoring_sessions.id
    heart_rate      INT NULL,                       -- BPM
    spo2            INT NULL,                       -- Saturasi oksigen (%)
    temperature     FLOAT NULL,                     -- Suhu (Celsius)
    status          ENUM('normal','warning','critical') NULL,
    recorded_at     TIMESTAMP NOT NULL,             -- Waktu asli dari sensor_data

    INDEX idx_session (session_id),
    INDEX idx_recorded_at (recorded_at),
    FOREIGN KEY (session_id) REFERENCES monitoring_sessions(id) ON DELETE CASCADE
);
```

### Relasi dengan Tabel yang Ada

```
devices (1) ──── (N) monitoring_sessions
patients (1) ──── (N) monitoring_sessions
users (1) ──── (N) monitoring_sessions (created_by)
monitoring_sessions (1) ──── (N) sensor_readings
```

### Alur Data

```
sensor_data (raw, temporary, untuk realtime dashboard)
    │
    │  Saat session finalized (device OFF):
    │  1. COPY data ke sensor_readings (dengan session_id)
    │  2. DELETE SEMUA sensor_data milik device
    │
    ▼
sensor_readings (finalized, untuk laporan)
    │
    ▼
monitoring_sessions (metadata: pasien, waktu, status)
```

> **Catatan:** `finalizeSession()` menghapus **semua** `sensor_data` milik device, bukan hanya yang dalam rentang waktu session. Ini mencegah orphan data dari session sebelumnya.

---

## Dampak ke Kode yang Ada

### Status Implementasi

| File | Perubahan | Status |
|------|-----------|--------|
| `MonitoringSessionService.php` | Service: create, finalize, linkPatient, auto-generate RM | ✅ Selesai |
| `ReportService.php` | Service: getReportData, getHistoryForChart, getSessionStats, getLatestReading | ✅ Selesai |
| `LaporanController.php` | Query dari `monitoring_sessions` + `sensor_readings`, AJAX session data | ✅ Selesai |
| `PatientController.php` | Input data pasien (dari halaman input-data-pasien & modal laporan) | ✅ Selesai |
| `DashboardController.php` | Session info di dashboard + input-data-pasien page | ✅ Selesai |
| `DeviceDataController.php` | Auto-create session ON, finalize session OFF | ✅ Selesai |
| `nakes/laporan.blade.php` | AJAX session dropdown, checkbox vital sign, modal input pasien | ✅ Selesai |
| `nakes/laporan-pdf.blade.php` | Template modular dengan data real | ✅ Selesai |
| `nakes/inputdata.blade.php` | Form input pasien + info active session | ✅ Selesai |
| `nakes/dashboard.blade.php` | Session banner (active/ended) + close button | ✅ Selesai |
| `dokter/laporan.blade.php` | Filter dropdown device + session (read-only) | ✅ Selesai |
| `dokter/laporan-pdf.blade.php` | Template modular dengan data real | ✅ Selesai |
| `SuperadminLaporanController.php` | Query dari `monitoring_sessions` + `sensor_readings` | ⏳ Belum |

### Yang Tidak Berubah

| Komponen | Alasan |
|----------|--------|
| `sensor_data` tabel | Tetap digunakan untuk realtime dashboard |
| WebSocket broadcasting | Tetap seperti sekarang |
| Device API endpoints | Tetap seperti sekarang |
| ML prediction | Tetap di tabel `devices` |

---

## Prioritas Implementasi

| Step | Task | Estimasi | Status |
|------|------|----------|--------|
| 1 | Migration: `monitoring_sessions` + `sensor_readings` + update `patients` | 15 menit | ✅ |
| 2 | Model: `MonitoringSession` + `SensorReading` + relasi | 15 menit | ✅ |
| 3 | Service: `MonitoringSessionService` (create, finalize, copy data) | 30 menit | ✅ |
| 4 | Auto generate nomor rekam medis | 10 menit | ✅ |
| 5 | Update `DeviceDataController`: auto-create session ON, finalize OFF | 30 menit | ✅ |
| 6 | Update `LaporanController`: query dari session + readings + AJAX | 30 menit | ✅ |
| 7 | Update `SuperadminLaporanController`: query dari session + readings | 20 menit | ⏳ |
| 8 | Update view laporan nakes: AJAX dropdown + checkbox vital sign + modal | 45 menit | ✅ |
| 9 | Update view PDF: template modular | 30 menit | ✅ |
| 10 | Input data pasien (halaman + modal di laporan) | 20 menit | ✅ |
| 11 | Dashboard nakes: session banner active/ended | 20 menit | ✅ |
| 12 | Update view laporan dokter: filter device + session | 20 menit | ✅ |
| 13 | Testing end-to-end | 30 menit | ⏳ |

---

## Catatan Teknis

- `sensor_data` tetap menjadi tabel temporary untuk realtime dashboard
- Proses copy `sensor_data` → `sensor_readings` saat finalize menggunakan bulk insert
- `finalizeSession()` menghapus **semua** `sensor_data` milik device setelah copy (bukan hanya rentang session)
- Index pada `sensor_readings` (`session_id`, `recorded_at`) penting untuk performa query laporan
- Auto generate nomor rekam medis menggunakan sequence per device per hari
- Format nomor rekam medis: `RM-{DEVICE_ID}-{YYYYMMDD}-{SEQ}`
- Data pasien bersifat opsional — tombol "Input Data Pasien" muncul di laporan jika belum ada
- Device mengirim data setiap 1-2 detik, volume data cukup besar → perlu perhatian performa
- AJAX session selection: dropdown sesi di halaman laporan nakes tidak me-refresh halaman
- Data Alpine.js di-pass via `window.__laporanInit` (bukan inline di atribut HTML) untuk hindari parsing error

### Fitur Halaman Laporan Nakes

1. **Auto-detect device** — tidak perlu dropdown perangkat, sistem cari dari `NakesDeviceConfig`
2. **Session dropdown** — daftar completed sessions, load via AJAX tanpa refresh
3. **Vital sign checkbox** — pilih heart_rate, SpO2, temperature yang akan ditampilkan
4. **Input Data Pasien** — tombol muncul jika session belum ada data pasien, buka modal popup
5. **Grafik vital signs** — Chart.js line chart, re-init setelah AJAX load
6. **Tabel riwayat** — semua sensor readings untuk session tersebut
7. **Sidebar** — info session + tombol download PDF
8. **PDF download** — generate dengan DomPDF + QuickChart.io untuk grafik

---

*Dibuat: 24 Mei 2026 — Hasil diskusi desain sistem laporan*
*Updated: 24 Mei 2026 — Implementasi selesai (session, AJAX laporan, modal input pasien, PDF)*
