# Plan: Fitur Hubungi Superadmin & Inbox

## Overview

Fitur "Hubungi Superadmin" adalah form pelaporan kendala dan request akun yang bisa diakses dari halaman login (tanpa perlu login). Pesan yang masuk akan diterima superadmin melalui menu Inbox di dashboard.

**Tujuan:**
- Menyediakan jalur komunikasi untuk user yang belum punya akun (request akun baru)
- Menampung laporan kendala perangkat dan aplikasi dari semua pihak
- Mempermudah superadmin mengelola permintaan masuk

---

## Form "Hubungi Superadmin"

### Lokasi
- Halaman login (`/login`) — tombol "Hubungi Superadmin" sudah ada
- Bisa diakses tanpa login (public/guest)

### Field Form

| Field | Tipe | Wajib? | Kondisi Muncul | Catatan |
|-------|------|--------|----------------|---------|
| Kategori | Dropdown | Ya | Selalu | Kendala Perangkat, Kendala Aplikasi, Request Akun Baru, Lainnya |
| ID Perangkat | Text | Ya* | Kategori = Kendala Perangkat | Validasi format device ID |
| Role Diminta | Dropdown | Ya* | Kategori = Request Akun Baru | Pilihan: Nakes, Dokter |
| Instansi | Text | Ya* | Kategori = Request Akun Baru | Nama RS/organisasi |
| Nama Lengkap | Text | Ya | Selalu | |
| Email | Email | Ya | Selalu | Untuk follow-up |
| No. HP / WhatsApp | Text | Opsional | Selalu | Untuk urgensi darurat |
| Urgensi | Radio Button | Ya | Selalu | Rendah / Sedang / Darurat |
| Detail Kendala | Textarea | Ya | Selalu | Max 1000 karakter |
| Upload Bukti | File | Opsional | Kategori = Kendala Perangkat / Kendala Aplikasi | Max 2MB, jpg/png |

### Conditional Logic (Alpine.js)
- Field "ID Perangkat" hanya muncul jika kategori = "Kendala Perangkat"
- Field "Role Diminta" dan "Instansi" hanya muncul jika kategori = "Request Akun Baru"
- Field "Upload Bukti" hanya muncul jika kategori = "Kendala Perangkat" atau "Kendala Aplikasi"

### Validasi
- Semua field wajib harus terisi (kecuali yang opsional)
- Email harus valid format
- Upload max 2MB, hanya jpg/jpeg/png
- ID Perangkat divalidasi formatnya (opsional: cek ke database)
- Rate limit: max 5 submit per email per hari (cegah spam)

---

## Database: Tabel `reports`

```sql
CREATE TABLE reports (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category ENUM('kendala_perangkat', 'kendala_aplikasi', 'request_akun', 'lainnya') NOT NULL,
    device_id VARCHAR(50) NULL,              -- hanya untuk kategori kendala_perangkat
    role_requested ENUM('nakes', 'dokter') NULL, -- hanya untuk request_akun
    institution VARCHAR(255) NULL,           -- hanya untuk request_akun
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    urgency ENUM('rendah', 'sedang', 'darurat') NOT NULL DEFAULT 'sedang',
    detail TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,       -- path file upload
    status ENUM('baru', 'diproses', 'selesai') NOT NULL DEFAULT 'baru',
    admin_notes TEXT NULL,                   -- catatan internal superadmin
    responded_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## Backend

### Migration
- `database/migrations/xxxx_create_reports_table.php`

### Model
- `app/Models/Report.php`
  - `$fillable`: semua field kecuali `id`, `admin_notes`, `responded_at`
  - `$casts`: `created_at`, `updated_at` → datetime

### Controller
- `app/Http/Controllers/ReportController.php`
  - `store(Request $request)` — validasi & simpan report + upload file
  - Public access (tidak perlu auth)

- `app/Http/Controllers/SuperadminInboxController.php`
  - `index()` — tampilkan halaman inbox dengan filter & pagination
  - `show(Report $report)` — detail satu report
  - `update(Report $request)` — update status + admin_notes
  - `destroy(Report $report)` — hapus report

### Routes (tambahkan di `routes/web.php`)

```php
// Public (tanpa auth)
Route::post('/report', [ReportController::class, 'store'])->name('report.store');

// Superadmin (auth + role:superadmin)
Route::prefix('superadmin')->middleware('role:superadmin')->group(function () {
    Route::get('/inbox', [SuperadminInboxController::class, 'index'])->name('superadmin.inbox');
    Route::get('/inbox/{report}', [SuperadminInboxController::class, 'show'])->name('superadmin.inbox.show');
    Route::patch('/inbox/{report}', [SuperadminInboxController::class, 'update'])->name('superadmin.inbox.update');
    Route::delete('/inbox/{report}', [SuperadminInboxController::class, 'destroy'])->name('superadmin.inbox.destroy');
});
```

### Form Request
- `app/Http/Requests/StoreReportRequest.php`
  - Validasi conditional berdasarkan `category`

### Service
- `app/Services/ReportService.php`
  - `store(array $data)` — handle upload file + simpan
  - `markAsProcessed(Report $report)` — update status
  - `markAsCompleted(Report $report)` — update status
  - `getFilteredReports($filters)` — query dengan filter

---

## Frontend

### Halaman Login — Form Modal
- File: `resources/views/pages/login.blade.php`
- Tombol "Hubungi Superadmin" buka modal (Alpine.js)
- Modal berisi form dengan conditional fields
- Submit via AJAX (fetch), tampilkan success/error toast
- File: buat partial `resources/views/components/report-modal.blade.php`

### Superadmin Inbox
- File: `resources/views/pages/superadmin/inbox.blade.php`
- **Tabel Inbox:**
  - Kolom: Waktu, Nama, Kategori (badge), Urgensi (badge warna), Status (badge)
  - Filter dropdown: Kategori, Urgensi, Status
  - Search: nama atau email
  - Pagination
- **Detail Modal:**
  - Semua info report
  - Preview uploaded image (jika ada)
  - Tombol ubah status: Baru → Dalam Proses → Selesai
  - Textarea "Catatan Admin" (internal, tidak terlihat user)
  - Untuk kategori "Request Akun": tombol shortcut "Buat Akun" → redirect ke Manajemen User dengan pre-filled data
- **Badge Warna:**
  - Kategori: Kendala Perangkat (biru), Kendala Aplikasi (ungu), Request Akun (hijau), Lainnya (abu)
  - Urgensi: Rendah (abu), Sedang (kuning), Darurat (merah)
  - Status: Baru (biru), Dalam Proses (kuning), Selesai (hijau)

### Sidebar Superadmin
- Tambah menu "Inbox" dengan badge counter (jumlah report status = baru)

---

## Alur Kerja

```
User (guest) di halaman login
        |
        v
Klik "Hubungi Superadmin" → Modal form muncul
        |
        v
Isi form (conditional fields berdasarkan kategori)
        |
        v
Submit → POST /report → simpan ke tabel reports
        |
        v
Superadmin melihat badge "Inbox (3)" di sidebar
        |
        v
Buka Inbox → filter, baca detail, update status
        |
        v
[Request Akun] → tombol "Buat Akun" → redirect ke Manajemen User
[Kendala]      → catatan admin, tandai selesai
```

---

## Prioritas Implementasi

| Step | Task | Estimasi |
|------|------|----------|
| 1 | Migration + Model `Report` | 10 menit |
| 2 | Form Request + Service | 15 menit |
| 3 | `ReportController` (store) | 10 menit |
| 4 | Modal form di halaman login (Alpine.js + conditional fields) | 30 menit |
| 5 | `SuperadminInboxController` (index, show, update) | 15 menit |
| 6 | Halaman Inbox superadmin (tabel + filter + detail modal) | 45 menit |
| 7 | Sidebar badge counter | 10 menit |
| 8 | Shortcut "Buat Akun" dari inbox | 15 menit |
| 9 | Upload file handling + storage link | 10 menit |
| 10 | Testing end-to-end | 15 menit |
| **Total** | | **~3 jam** |

---

## Catatan Teknis

- Form di halaman login harus bisa diakses tanpa auth → CSRF token tetap diperlukan
- Upload file disimpan di `storage/app/public/reports/` → perlu `php artisan storage:link`
- Rate limit bisa pakai Laravel's `RateLimiter::for()` di middleware atau di controller
- Badge counter di sidebar bisa pakai query count di sidebar component (cached)
- "Buat Akun" shortcut: redirect ke `/superadmin/manajemen-user?prefill_nama=X&prefill_email=Y&prefill_role=Z`
- Untuk email notifikasi ke superadmin saat ada report baru → bisa ditambahkan nanti (queue mail)

---

*Draft: 24 Mei 2026 — Siap diimplementasikan di progres berikutnya*
