# SATS - Frontend Documentation

## Tech Stack

- **Framework:** Laravel 12 (PHP 8.2+)
- **CSS:** Tailwind CSS v4.2 (via `@tailwindcss/vite`)
- **JS:** Alpine.js v3 (CDN), Chart.js v4.4.1 (CDN)
- **Build Tool:** Vite v6
- **PDF:** barryvdh/laravel-dompdf v3.1
- **Database:** MySQL (`sats_db`)

---

## Struktur Project (Frontend)

```
resources/views/
  components/
    navbar.blade.php          # Top nav (logo, user name + role, logout)
    sidebar.blade.php         # Sidebar dinamis (nakes/dokter/superadmin)
  layouts/
    app.blade.php             # Layout utama (navbar + sidebar + yield)
    auth.blade.php            # Layout halaman auth (login, forgot/reset password)
  pages/
    login.blade.php           # Halaman login + image slider
    auth/
      forgot-password.blade.php
      reset-password.blade.php
    nakes/
      dashboard.blade.php     # Monitoring device (chart, vital sign, prediksi ML, respon komentar)
      inputdata.blade.php     # Form input data pasien (belum ada backend)
      laporan.blade.php       # Laporan medis + chart + filter tanggal
      laporan-pdf.blade.php   # Template PDF laporan (DomPDF compatible)
    dokter/
      dashboard.blade.php     # Monitoring device + container komentar untuk nakes
      inputdata.blade.php     # Form input data pasien (sama seperti nakes)
      laporan.blade.php       # Laporan medis (sama seperti nakes, role-aware)
      laporan-pdf.blade.php   # Template PDF (sama seperti nakes, role-aware)
    superadmin/
      dashboard.blade.php     # Dashboard superadmin (stat cards, tabel kritis, log)
      manajemen-alat.blade.php # Inventaris alat + modal tambah & detail alat
      manajemen-user.blade.php # Manajemen user + modal tambah & detail user
      laporan.blade.php       # Laporan superadmin (filter, stat cards, chart, tabel sensor)
      laporan-pdf.blade.php   # Template PDF laporan superadmin (landscape A4)
```

---

## Sistem Role

| Role       | URL Prefix       | Middleware         | Status         |
|------------|------------------|--------------------|----------------|
| nakes      | `/nakes/*`       | `role:nakes`       | Sudah ada view |
| dokter     | `/dokter/*`      | `role:dokter`      | Sudah ada view |
| superadmin | `/superadmin/*`  | `role:superadmin`  | Sudah ada view |

**Pembeda nakes & dokter:** Halaman identik, dipisah route & folder untuk fitur tambahan (komentar dokter→nakes).

---

## Routes Saat Ini

### Public
| Method | URI                | Name               |
|--------|--------------------|--------------------|
| GET    | `/login`           | `login`            |
| POST   | `/login`           | `login.process`    |
| GET    | `/forgot-password` | `password.forgot`  |
| POST   | `/forgot-password` | `password.email`   |
| GET    | `/reset-password`  | `password.reset`   |
| POST   | `/reset-password`  | `password.update`  |

### Nakes (auth + role:nakes)
| Method | URI                      | Name               |
|--------|--------------------------|--------------------|
| GET    | `/nakes/dashboard`       | `dashboard`        |
| GET    | `/nakes/input-data-pasien`| `input-data-pasien`|
| GET    | `/nakes/laporan`         | `laporan.index`    |
| GET    | `/nakes/laporan/pdf`     | `laporan.pdf`      |

### Dokter (auth + role:dokter)
| Method | URI                       | Name                    |
|--------|---------------------------|-------------------------|
| GET    | `/dokter/dashboard`       | `dokter.dashboard`      |
| GET    | `/dokter/input-data-pasien`| `dokter.input-data-pasien`|
| GET    | `/dokter/laporan`         | `dokter.laporan`        |
| GET    | `/dokter/laporan/pdf`     | `dokter.laporan.pdf`    |

### Superadmin (auth + role:superadmin)
| Method | URI                            | Name                        |
|--------|--------------------------------|-----------------------------|
| GET    | `/superadmin/dashboard`        | `superadmin.dashboard`      |
| GET    | `/superadmin/manajemen-alat`   | `superadmin.manajemen-alat` |
| GET    | `/superadmin/manajemen-user`   | `superadmin.manajemen-user` |
| GET    | `/superadmin/input-data-pasien`| `superadmin.input-data-pasien` |
| GET    | `/superadmin/laporan`          | `superadmin.laporan`        |
| GET    | `/superadmin/laporan/pdf`      | `superadmin.laporan.pdf`    |

---

## Kondisi Saat Ini

### Sudah Dikerjakan
- [x] Halaman login + image slider
- [x] Sistem auth (login, logout, forgot/reset password)
- [x] Role middleware (nakes, dokter, superadmin)
- [x] Dashboard nakes (terhubung ke API real, polling sensor data + komentar)
- [x] Input data pasien nakes (UI form, belum ada backend POST)
- [x] Laporan nakes (HTML + PDF, data dummy)
- [x] Navbar & sidebar nakes
- [x] Dashboard dokter (terhubung ke API real, polling sensor data + komentar)
- [x] Input data pasien dokter (UI form, sama seperti nakes)
- [x] Laporan dokter (HTML + PDF, role-aware via LaporanController)
- [x] Fitur komentar dokter→nakes (terhubung ke API CommentController)
- [x] Fitur respon nakes (dropdown 5 opsi respon + checklist instruksi)
- [x] Dashboard superadmin (stat cards, tabel kritis, log aktivitas)
- [x] Sidebar dinamis (nakes/dokter/superadmin)
- [x] Navbar menampilkan nama + role user
- [x] Manajemen alat (CRUD terhubung ke backend, auto-generate API key)
- [x] Manajemen user (tabel user + modal tambah & detail user)
- [x] Laporan superadmin (filter, stat cards, chart 3 axis, tabel sensor, PDF)
- [x] Seeder user dengan 3 role (`UserSeeder.php`)
- [x] Auth redirect berdasarkan role (termasuk dokter)
- [x] Bug fix: chart flickering (skip update jika data sama)
- [x] Bug fix: komentar checklist reset (preserve state saat poll)
- [x] Bug fix: polling interval seragam (5 detik)
- [x] Fitur: dropdown device auto-update tanpa refresh

### Belum Dikerjakan
- [ ] Backend untuk input data pasien (POST route + controller)
- [ ] Route untuk UserController (CRUD user belum terhubung ke UI)
- [ ] Laporan dari database (masih dummy data)
- [ ] Integrasi IoT real (simulator sudah ada, hardware belum)
- [ ] Machine Learning (prediksi kondisi pasien)
- [ ] Notifikasi komentar terkirim (toast/snackbar)
- [ ] Warning/highlight saat komentar dichecklist nakes

---

## Fitur Komentar Dokter→Nakes

### Alur:
1. **Dokter** mengirim komentar/instruksi dari dashboard (textarea + tombol Kirim)
2. **Nakes** melihat instruksi di dashboard dengan opsi respon (dropdown):
   - Sudah dilakukan
   - Tidak bisa dilakukan
   - Tidak memungkinkan
   - Sedang diproses
   - Butuh konfirmasi ulang
3. Nakes bisa checklist instruksi yang sudah selesai (disembunyikan dari daftar aktif)
4. Dokter melihat respon nakes di bawah komentarnya (green border styling)

### Implementasi:
- Menggunakan Alpine.js (`komentarDokter()` dan `komentarNakes()`)
- **Terhubung ke API:** GET/POST `/api/comments`, PATCH `/api/comments/{id}/respond`
- Polling komentar setiap 5 detik
- State checklist nakes di-preserve saat polling (menggunakan `_checkedIds` Set)
- Dokter melihat respon nakes secara realtime

---

## Notes

- Dashboard nakes & dokter **terhubung ke API real** (polling 5 detik)
- Dropdown device **auto-update** tanpa refresh halaman (polling `/api/devices` 10 detik)
- Sidebar `components/sidebar.blade.php` sudah dinamis berdasarkan role (nakes/dokter/superadmin)
- Navbar menampilkan nama user + role (dengan `ucfirst()`)
- `LaporanController` sudah role-aware: cek `auth()->user()->role` untuk pilih view folder
- `DashboardController` sudah support 3 role di `getViewByRole()`
- Form input data pasien belum punya backend handler
- Manajemen alat: CRUD sudah terhubung ke backend
- Manajemen user: UI ada, backend ada, route belum didaftarkan
- Grafik di PDF laporan menggunakan QuickChart.io (curl, SSL verify disabled)
- Simulator Python tersedia di `simulasi_py/` untuk testing tanpa hardware
