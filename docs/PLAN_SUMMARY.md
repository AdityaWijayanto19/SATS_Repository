# PLAN_SUMMARY.md — Rencana Perbaikan & Pengembangan SATS

Dokumentasi ini berisi rencana implementasi untuk fix bug dan pengembangan fitur baru pada sistem SATS.

---

## Daftar Isi

1. [Fix Bug: Device Switching](#1-fix-bug-device-switching)
2. [Fitur Baru: Kategori Usia Pasien](#2-fitur-baru-kategori-usia-pasien)

---

## 1. Fix Bug: Device Switching

### Deskripsi Masalah

Ketika user nakes memasukkan API key Device 1 ke web dashboard, monitoring berjalan normal. Tapi ketika user nakes **berganti perangkat** (Ganti Perangkat → masukkan API key Device 2), dashboard tetap menampilkan monitoring **Device 1**, bukan Device 2.

### Root Cause Analysis

Bug ini disebabkan oleh **3 masalah yang saling terkait**:

#### Masalah 1: `getDevicesApi()` mengembalikan SEMUA device

**File:** `app/Services/DashboardService.php` → method `getDevicesApi()` (line ~187)

```php
// BUG: Mengembalikan semua device tanpa filter berdasarkan NakesDeviceConfig
return Devices::all()->map(function ($device) use ($from) { ... });
```

Method ini tidak memfilter device berdasarkan `NakesDeviceConfig` milik user nakes yang sedang login. Akibatnya, semua device selalu dikembalikan.

#### Masalah 2: Frontend selalu mengambil `data[0]`

**File:** `resources/views/pages/nakes/dashboard.blade.php` → Alpine `init()` (line ~444)

```javascript
// BUG: Selalu ambil device pertama dari array
const device = json.data[0];
```

Frontend tidak mencari device yang sesuai dengan `NakesDeviceConfig` user. Karena `getDevicesApi()` mengembalikan semua device, `data[0]` selalu merujuk ke device pertama yang terdaftar (Device 1), meskipun user sudah berganti ke Device 2.

#### Masalah 3: `getDevicesWithLatestData()` juga tidak filter

**File:** `app/Services/DashboardService.php` → method `getDevicesWithLatestData()` (line ~240)

Method ini dipakai oleh `getDashboardData()` untuk data server-rendered, dan juga mengembalikan semua device tanpa filter `NakesDeviceConfig`.

### Rencana Fix

| Step | Task | File | Estimasi |
|------|------|------|----------|
| 1 | Filter `getDevicesApi()` berdasarkan `NakesDeviceConfig` user nakes | `app/Services/DashboardService.php` | 15 menit |
| 2 | Filter `getDevicesWithLatestData()` berdasarkan `NakesDeviceConfig` user nakes | `app/Services/DashboardService.php` | 10 menit |
| 3 | Update frontend: cari device yang sesuai config, bukan `data[0]` | `resources/views/pages/nakes/dashboard.blade.php` | 10 menit |
| 4 | Pastikan "Ganti Perangkat" properly reset dan reload ke device baru | `resources/views/pages/nakes/dashboard.blade.php` | 5 menit |
| 5 | Testing end-to-end: switch device 1 → device 2 → device 1 | Manual | 15 menit |
| **Total** | | | **~55 menit** |

### Detail Implementasi

#### Step 1 & 2: Filter `DashboardService` berdasarkan NakesDeviceConfig

**File:** `app/Services/DashboardService.php`

**Perubahan pada `getDevicesApi()`:**
```php
// SEBELUM (bug):
return Devices::all()->map(function ($device) use ($from) { ... });

// SESUDAH (fix):
$user = Auth::user();
if ($user->role === 'nakes') {
    $config = NakesDeviceConfig::where('user_id', $user->id)->first();
    if ($config) {
        $devices = Devices::where('device_id', $config->device_id)->get();
    } else {
        $devices = collect(); // kosong, user belum setup device
    }
} else {
    $devices = Devices::all(); // dokter & superadmin tetap lihat semua
}
return $devices->map(function ($device) use ($from) { ... });
```

**Perubahan pada `getDevicesWithLatestData()`:**
- Terapkan filter yang sama: nakes hanya melihat device sesuai `NakesDeviceConfig`

#### Step 3: Update Frontend Dashboard

**File:** `resources/views/pages/nakes/dashboard.blade.php`

```javascript
// SEBELUM (bug):
const device = json.data[0];

// SESUDAH (fix):
// Karena backend sudah di-filter, data[0] sudah benar untuk nakes
// Tambahkan safety check:
if (!json.data || json.data.length === 0) {
    // Tampilkan pesan "Tidak ada perangkat terkonfigurasi"
    return;
}
const device = json.data[0]; // Sudah benar karena backend filter
```

#### Step 4: Pastikan "Ganti Perangkat" Berfungsi

Alur yang benar:
1. User klik "Ganti Perangkat" → POST ke `resetDeviceConfig()` → hapus `NakesDeviceConfig` → redirect
2. Dashboard detect tidak ada config → tampilkan halaman `setup-device`
3. User masukkan API key baru → `saveDeviceConfig()` buat `NakesDeviceConfig` baru → redirect
4. Dashboard fetch `/api/devices` → backend filter berdasarkan config baru → tampilkan device baru

### Testing Checklist

- [ ] Login nakes → setup Device 1 → dashboard tampilkan Device 1 monitoring
- [ ] Klik "Ganti Perangkat" → masukkan API key Device 2 → dashboard tampilkan Device 2 monitoring
- [ ] Klik "Ganti Perangkat" lagi → masukkan API key Device 1 → dashboard kembali ke Device 1
- [ ] Data sensor real-time berubah sesuai device yang aktif
- [ ] WebSocket subscribe channel yang benar setelah ganti device
- [ ] Dokter & superadmin tetap bisa lihat semua device (tidak terpengaruh fix ini)

---

## 2. Fitur Baru: Kategori Usia Pasien

### Deskripsi

Berdasarkan riset, **usia sangat berpengaruh pada hasil klasifikasi dan prediksi** kondisi pasien. Oleh karena itu, perlu ditambahkan fitur **kategori usia** yang wajib diinput oleh nakes sebelum monitoring dimulai.

### Konsep Kategori Usia

| Kategori | Rentang Umur | Keterangan |
|----------|-------------|------------|
| `bayi_baru_lahir` | 0 - 28 hari | Bayi baru lahir (neonatus) |
| `bayi` | 1 - 12 bulan | Bayi (infant) |
| `balita` | 1 - 5 tahun | Balita (toddler) |
| `anak` | 6 - 12 tahun | Anak-anak (child) |
| `remaja` | 13 - 18 tahun | Remaja (adolescent) |
| `dewasa` | 19 - 59 tahun | Dewasa (adult) |
| `lansia` | 60+ tahun | Lansia (elderly) |

### Alur Fitur

```
Nakes aktifkan perangkat
        |
        v
Monitoring session dibuat (status: active)
        |
        v
Dashboard muncul POPUP: "Pilih Kategori Usia Pasien"
  ┌─────────────────────────────────────────────┐
  │  Kategori Usia Pasien                       │
  │                                             │
  │  ○ Bayi Baru Lahir (0 - 28 hari)           │
  │  ○ Bayi (1 - 12 bulan)                     │
  │  ○ Balita (1 - 5 tahun)                    │
  │  ○ Anak (6 - 12 tahun)                     │
  │  ○ Remaja (13 - 18 tahun)                  │
  │  ○ Dewasa (19 - 59 tahun)                  │
  │  ○ Lansia (60+ tahun)                      │
  │                                             │
  │  [Mulai Monitoring]                         │
  └─────────────────────────────────────────────┘
  Background: putih transparan + blur
        |
        v
Nakes WAJIB pilih kategori → klik "Mulai Monitoring"
        |
        v
Data kategori usia tersimpan di database
Monitoring baru bisa dimulai
        |
        v
Kategori usia dikirim ke perangkat IoT → tampil di LCD
```

**Aturan penting:**
- Popup muncul SETIAP perangkat dinyalakan (setiap session baru)
- Nakes TIDAK BISA monitoring sebelum mengisi popup ini
- Saat perangkat mati lalu menyala lagi → popup muncul lagi

### Rencana Implementasi

#### A. Database

| Step | Task | File | Estimasi |
|------|------|------|----------|
| A1 | Buat migration: tambah kolom `age_category` di tabel `monitoring_sessions` | `database/migrations/xxxx_add_age_category_to_monitoring_sessions.php` | 10 menit |
| A2 | Buat migration: tambah kolom `age_category` di tabel `patients` | `database/migrations/xxxx_add_age_category_to_patients.php` | 10 menit |
| A3 | Update model `MonitoringSession` — tambah `age_category` ke `$fillable` dan `$casts` | `app/Models/MonitoringSession.php` | 5 menit |
| A4 | Update model `Patient` — tambah `age_category` ke `$fillable` | `app/Models/Patient.php` | 5 menit |
| **Subtotal** | | | **~30 menit** |

**Schema perubahan:**

```sql
-- monitoring_sessions: tambah kolom age_category
ALTER TABLE monitoring_sessions
ADD COLUMN age_category ENUM(
    'bayi_baru_lahir', 'bayi', 'balita', 'anak',
    'remaja', 'dewasa', 'lansia'
) NULL AFTER patient_id;

-- patients: tambah kolom age_category
ALTER TABLE patients
ADD COLUMN age_category ENUM(
    'bayi_baru_lahir', 'bayi', 'balita', 'anak',
    'remaja', 'dewasa', 'lansia'
) NULL AFTER umur;
```

#### B. Backend — Service & Controller

| Step | Task | File | Estimasi |
|------|------|------|----------|
| B1 | Update `MonitoringSessionService::createSession()` — terima parameter `age_category` | `app/Services/MonitoringSessionService.php` | 10 menit |
| B2 | Buat API endpoint: `PATCH /nakes/session/{id}/age-category` — update kategori usia session | `app/Http/Controllers/DashboardController.php` + `routes/web.php` | 15 menit |
| B3 | Update `DeviceDataController` — kirim `age_category` ke perangkat via response config | `app/Http/Controllers/Api/DeviceDataController.php` | 15 menit |
| B4 | Buat API endpoint: `GET /api/device/{id}/patient-category` — perangkat ambil kategori usia | `app/Http/Controllers/Api/DeviceDataController.php` + `routes/api.php` | 10 menit |
| B5 | Update `MonitoringSessionService::linkPatient()` — simpan `age_category` ke patient | `app/Services/MonitoringSessionService.php` | 10 menit |
| B6 | Buat Form Request: `UpdateAgeCategoryRequest` — validasi input kategori usia | `app/Http/Requests/UpdateAgeCategoryRequest.php` | 5 menit |
| B7 | Update `ReportService` — sertakan `age_category` di data laporan | `app/Services/ReportService.php` | 10 menit |
| **Subtotal** | | | **~75 menit** |

**Detail API baru:**

```
PATCH /nakes/session/{session_id}/age-category
Body: { "age_category": "remaja" }
Response: { "success": true, "data": { "session_id": 1, "age_category": "remaja" } }
```

```
GET /api/device/{device_id}/patient-category
Headers: X-API-Key: <key>
Response: {
    "success": true,
    "data": {
        "age_category": "remaja",
        "age_label": "Remaja (13 - 18 tahun)",
        "session_id": 1
    }
}
```

#### C. Frontend — Dashboard Nakes (Popup)

| Step | Task | File | Estimasi |
|------|------|------|----------|
| C1 | Buat component popup kategori usia (Alpine.js modal) | `resources/views/components/age-category-modal.blade.php` | 30 menit |
| C2 | Integrasikan popup ke dashboard nakes — muncul saat session baru aktif | `resources/views/pages/nakes/dashboard.blade.php` | 20 menit |
| C3 | Logic: popup muncul jika session `active` + `age_category` masih null | `resources/views/pages/nakes/dashboard.blade.php` | 15 menit |
| C4 | Submit popup → AJAX PATCH ke `/nakes/session/{id}/age-category` | `resources/views/pages/nakes/dashboard.blade.php` | 10 menit |
| C5 | Setelah submit: sembunyikan popup, lanjutkan monitoring | `resources/views/pages/nakes/dashboard.blade.php` | 5 menit |
| C6 | Styling: background putih transparan + blur, card popup di tengah | Tailwind CSS | 10 menit |
| **Subtotal** | | | **~90 menit** |

**Desain Popup:**

```
┌──────────────────────────────────────────────────────────┐
│                                                          │
│  ┌──────────────────────────────────────────────────┐    │
│  │  🏥 Pilih Kategori Usia Pasien                   │    │
│  │                                                  │    │
│  │  Pilih kategori usia pasien yang akan dimonitor. │    │
│  │  Data ini berpengaruh pada hasil klasifikasi     │    │
│  │  dan prediksi kondisi pasien.                    │    │
│  │                                                  │    │
│  │  ○ Bayi Baru Lahir    (0 - 28 hari)             │    │
│  │  ○ Bayi               (1 - 12 bulan)            │    │
│  │  ○ Balita             (1 - 5 tahun)             │    │
│  │  ○ Anak               (6 - 12 tahun)            │    │
│  │  ○ Remaja             (13 - 18 tahun)           │    │
│  │  ○ Dewasa             (19 - 59 tahun)           │    │
│  │  ○ Lansia             (60+ tahun)               │    │
│  │                                                  │    │
│  │         [ Mulai Monitoring ]                     │    │
│  └──────────────────────────────────────────────────┘    │
│                                                          │
│  Background: bg-white/70 backdrop-blur-sm                │
│  Popup: rounded-xl shadow-2xl p-8 max-w-md              │
│                                                          │
└──────────────────────────────────────────────────────────┘
```

**Alpine.js Logic:**

```javascript
// Di dashboard nakes, tambah state:
ageCategoryModal: false,
selectedAgeCategory: null,
currentSessionId: null,

// Logic munculnya popup:
// Saat WebSocket terima event device online + session baru dibuat
// → cek apakah session punya age_category
// → jika null → tampilkan popup (ageCategoryModal = true)
// → monitoring data TIDAK ditampilkan sampai popup diisi

// Submit popup:
async submitAgeCategory() {
    await fetch(`/nakes/session/${this.currentSessionId}/age-category`, {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '...' },
        body: JSON.stringify({ age_category: this.selectedAgeCategory })
    });
    this.ageCategoryModal = false;
    // Lanjutkan monitoring...
}
```

#### D. Frontend — Input Data Pasien & Laporan

| Step | Task | File | Estimasi |
|------|------|------|----------|
| D1 | Update form input data pasien: tambah dropdown kategori usia | `resources/views/pages/nakes/inputdata.blade.php` | 15 menit |
| D2 | Update modal input data pasien di halaman laporan | `resources/views/pages/nakes/laporan.blade.php` | 10 menit |
| D3 | Update partial `_laporan-patient.blade.php`: tampilkan kategori usia | `resources/views/pages/nakes/partials/_laporan-patient.blade.php` | 10 menit |
| D4 | Update `PatientController::store()` — terima dan simpan `age_category` | `app/Http/Controllers/PatientController.php` | 10 menit |
| D5 | Update template PDF: tampilkan kategori usia di laporan | `resources/views/pages/nakes/laporan-pdf.blade.php` + `dokter/laporan-pdf.blade.php` | 15 menit |
| D6 | Update laporan dokter: tampilkan kategori usia (read-only) | `resources/views/pages/dokter/laporan.blade.php` + `partials/` | 10 menit |
| **Subtotal** | | | **~70 menit** |

**Perubahan pada form input data pasien:**

```
Form yang ada:              Form yang diupdate:
─────────────               ──────────────────
- Nama                      - Nama
- NIK                       - NIK
- Tanggal Lahir             - Tanggal Lahir
- Umur                      - Umur
- Jenis Kelamin             - Jenis Kelamin
- Penyakit/Alergi           - Kategori Usia  ← BARU
- Catatan                   - Penyakit/Alergi
- Dokter                    - Catatan
                            - Dokter
```

**Perubahan pada partial laporan:**

```
Laporan Medis Pasien: RM-DEVICE_01-20260605-001
─────────────────────────────────────────────────
Nama Lengkap    : Budi Santoso      Penyakit/Alergi : Asma
NIK             : 3201234567890001  Catatan Tambahan: Pasien sadar
Umur            : 36                Kategori Usia   : Dewasa (19-59 thn)  ← BARU
Jenis Kelamin   : Laki-laki
```

#### E. Integrasi IoT — Kirim Kategori ke Perangkat LCD

| Step | Task | File | Estimasi |
|------|------|------|----------|
| E1 | Update response `GET /api/device/{id}/config` → include `age_category` | `app/Http/Controllers/Api/DeviceDataController.php` | 10 menit |
| E2 | Buat endpoint `GET /api/device/{id}/patient-category` (sudah ada di B4) | — | — |
| E3 | Update simulator Python: terima dan tampilkan kategori usia | `simulasi_py/simulator.py` | 15 menit |
| E4 | Dokumentasi: cara perangkat Arduino ambil kategori usia | `docs/API_DOCUMENTATION.md` | 10 menit |
| **Subtotal** | | | **~35 menit** |

**Alur komunikasi ke perangkat:**

```
┌─────────────┐         ┌─────────────┐         ┌─────────────┐
│  Dashboard  │         │   Server    │         │  Perangkat  │
│  (Browser)  │         │  (Laravel)  │         │  (Arduino)  │
└──────┬──────┘         └──────┬──────┘         └──────┬──────┘
       │                       │                       │
       │  1. Pilih kategori    │                       │
       │  ──────────────────►  │                       │
       │                       │                       │
       │                       │  2. Simpan ke DB      │
       │                       │  (monitoring_sessions │
       │                       │   .age_category)      │
       │                       │                       │
       │                       │  3. Perangkat poll    │
       │                       │  ──────────────────── │
       │                       │     GET /api/device/  │
       │                       │     {id}/patient-     │
       │                       │     category          │
       │                       │                       │
       │                       │  4. Response:         │
       │                       │  { age_category,      │
       │                       │    age_label }        │
       │                       │  ────────────────────►│
       │                       │                       │
       │                       │                       │  5. Tampilkan
       │                       │                       │  di LCD:
       │                       │                       │  "Kategori: Remaja"
```

**Opsi komunikasi perangkat → server:**

| Opsi | Metode | Interval | Kelebihan | Kekurangan |
|------|--------|----------|-----------|------------|
| **A. Polling** | GET `/api/device/{id}/patient-category` | Setiap 10 detik | Simpel, sama seperti sensor data | Ada delay max 10 detik |
| **B. Config Response** | Include di response `GET /config` | Saat authenticate | Tidak perlu endpoint baru | Hanya update saat reconnect |
| **C. WebSocket Push** | Broadcast saat kategori diisi | Real-time | Zero delay | Perlu WebSocket di Arduino (lebih kompleks) |

**Rekomendasi:** Opsi A (Polling) — konsisten dengan arsitektur yang sudah ada, Arduino sudah terbiasa polling endpoint.

### Ringkasan Total Estimasi

| Bagian | Estimasi |
|--------|----------|
| A. Database (migration + model) | 30 menit |
| B. Backend (service + controller + API) | 75 menit |
| C. Frontend Dashboard (popup) | 90 menit |
| D. Frontend Input Pasien & Laporan | 70 menit |
| E. Integrasi IoT (LCD) | 35 menit |
| **TOTAL** | **~5 jam** |

### Urutan Pengerjaan

```
Phase 1: Database & Backend (Basis Data)
  ├── A1, A2: Migration age_category
  ├── A3, A4: Update model
  ├── B1: Update MonitoringSessionService
  ├── B2: API endpoint update age_category
  └── B6: Form Request validation

Phase 2: Frontend Dashboard (Popup)
  ├── C1: Component popup
  ├── C2-C6: Integrasi + logic + styling
  └── Testing: popup muncul, submit, monitoring lanjut

Phase 3: Frontend Input Pasien & Laporan
  ├── D1-D2: Update form input pasien
  ├── D3: Update partial laporan
  ├── D4: Update PatientController
  └── D5-D6: Update PDF template

Phase 4: Integrasi IoT
  ├── B3-B4: API endpoint perangkat
  ├── E1: Update config response
  ├── E3: Update simulator
  └── E4: Update dokumentasi API

Phase 5: Testing End-to-End
  ├── Testing device switching (fix bug)
  ├── Testing popup kategori usia
  ├── Testing input data pasien dengan kategori
  ├── Testing laporan dengan kategori
  └── Testing komunikasi perangkat LCD
```

### Testing Checklist — Kategori Usia

- [ ] Popup muncul saat perangkat pertama kali dinyalakan
- [ ] Popup muncul lagi saat perangkat mati → nyala lagi (session baru)
- [ ] Nakes tidak bisa monitoring sebelum mengisi popup
- [ ] Background popup: putih transparan + blur
- [ ] Data kategori usia tersimpan di `monitoring_sessions`
- [ ] Data kategori usia tersimpan di `patients` (saat input data pasien)
- [ ] Kategori usia muncul di halaman laporan
- [ ] Kategori usia muncul di PDF laporan
- [ ] Kategori usia muncul di laporan dokter (read-only)
- [ ] API `GET /api/device/{id}/patient-category` mengembalikan data yang benar
- [ ] Simulator bisa ambil dan tampilkan kategori usia
- [ ] Semua 7 kategori usia bisa dipilih dan tersimpan

---

## Dokumentasi Terkait

| File | Keterangan |
|------|------------|
| [MASTER_BRIEF.md](MASTER_BRIEF.md) | Ringkasan lengkap project |
| [FRONTEND.md](FRONTEND.md) | Progress frontend |
| [BACKEND.md](BACKEND.md) | Progress backend |
| [DATABASE.md](DATABASE.md) | Struktur database |
| [API_DOCUMENTATION.md](API_DOCUMENTATION.md) | Dokumentasi API |
| [LAPORAN_SYSTEM.md](LAPORAN_SYSTEM.md) | Sistem laporan & monitoring session |

---

*Dibuat: 5 Juni 2026 — Rencana fix bug device switching + fitur kategori usia pasien*
