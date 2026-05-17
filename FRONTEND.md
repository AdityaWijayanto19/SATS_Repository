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
      dashboard.blade.php     # Monitoring device (chart, vital sign, prediksi ML)
      inputdata.blade.php     # Form input data pasien (belum ada backend)
      laporan.blade.php       # Laporan medis + chart + filter tanggal
      laporan-pdf.blade.php   # Template PDF laporan (DomPDF compatible)
      instruksi.blade.php     # Chat instruksi dokter + laporan nakes (API-connected)
    dokter/
      dashboard.blade.php     # Monitoring device (chart, vital sign)
      inputdata.blade.php     # Form input data pasien (sama seperti nakes)
      laporan.blade.php       # Laporan medis (sama seperti nakes, role-aware)
      laporan-pdf.blade.php   # Template PDF (sama seperti nakes, role-aware)
      instruksi.blade.php     # Chat instruksi medis + pantau laporan nakes (API-connected)
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
| GET    | `/nakes/instruksi`       | `nakes.instruksi`  |

### Dokter (auth + role:dokter)
| Method | URI                       | Name                    |
|--------|---------------------------|-------------------------|
| GET    | `/dokter/dashboard`       | `dokter.dashboard`      |
| GET    | `/dokter/input-data-pasien`| `dokter.input-data-pasien`|
| GET    | `/dokter/laporan`         | `dokter.laporan`        |
| GET    | `/dokter/laporan/pdf`     | `dokter.laporan.pdf`    |
| GET    | `/dokter/instruksi`       | `dokter.instruksi`      |

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
- [x] Dashboard nakes (realtime via WebSocket, zero polling)
- [x] Input data pasien nakes (UI form, belum ada backend POST)
- [x] Laporan nakes (HTML + PDF, data dummy)
- [x] Navbar & sidebar nakes (termasuk menu Instruksi)
- [x] Dashboard dokter (realtime via WebSocket, zero polling)
- [x] Input data pasien dokter (UI form, sama seperti nakes)
- [x] Laporan dokter (HTML + PDF, role-aware via LaporanController)
- [x] Halaman instruksi nakes (chat-style, laporan + konfirmasi instruksi dokter)
- [x] Halaman instruksi dokter (chat-style, instruksi medis + pantau laporan nakes)
- [x] Fitur instruksi dokter→nakes (terhubung ke API InstructionController)
- [x] Fitur laporan nakes→dokter (submit laporan kejadian)
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
- [x] Bug fix: polling interval seragam (5 detik)
- [x] Fitur: dropdown device auto-update tanpa refresh
- [x] Realtime updates via WebSocket (Reverb) — hapus semua polling dashboard
- [x] Device status toggle (nakes: aktifkan/matikan perangkat)
- [x] Optimistic update tombol toggle (langsung berubah, revert kalau gagal)
- [x] Card + grafik sinkron (satu WebSocket event update keduanya)
- [x] Dokter dashboard auto-kosong saat device offline
- [x] `updateCharts(history)` — terima data langsung, bukan fetch terpisah
- [x] ML prediction card di dashboard nakes/dokter
- [x] Probability card di dashboard nakes/dokter (Membaik/Stabil/Memburuk % dengan progress bar)
- [x] Chart.js initialization (HR, SpO2, Temperature)
- [x] Superadmin manajemen-alat: polling auto-refresh device status

### Belum Dikerjakan
- [ ] Backend untuk input data pasien (POST route + controller)
- [ ] Route untuk UserController (CRUD user belum terhubung ke UI)
- [ ] Laporan dari database (masih dummy data)
- [ ] Integrasi IoT real (simulator sudah ada, hardware belum)
- [ ] Notifikasi instruksi terkirim (toast/snackbar)
- [ ] Warning/highlight saat instruksi diselesaikan nakes

---

## Fitur Instruksi Dokter→Nakes

### Alur:
1. **Dokter** mengirim instruksi medis dari halaman instruksi (textarea + tombol Kirim)
2. **Nakes** melihat instruksi di halaman instruksi dengan opsi respon (dropdown):
   - Sudah dilakukan
   - Tidak bisa dilakukan
   - Tidak memungkinkan
   - Sedang diproses
   - Butuh konfirmasi ulang
3. **Nakes** bisa mengirim laporan kejadian ke dokter
4. Nakes bisa checklist instruksi yang sudah selesai (disembunyikan dari daftar aktif)
5. Dokter melihat respon nakes dan laporan di halaman instruksi (chat-style layout)

### Implementasi:
- Menggunakan Alpine.js (`instruksiDokter()` dan `instruksiNakes()`)
- **Terhubung ke API:** GET/POST `/api/instruction`, PATCH `/api/instruction/{id}/complete`, POST `/api/instruction/report`
- Polling instruksi setiap 5 detik
- State checklist nakes di-preserve saat polling
- Chat-style layout dengan scroll otomatis
- Broadcasting events: InstructionSent, InstructionStatusUpdated, InstructionReportSubmitted

---

## Notes

- Dashboard nakes & dokter **realtime via WebSocket** (Reverb, zero polling)
- Nakes dashboard: subscribe `device.{deviceId}` untuk status + sensor events
- Dokter dashboard: subscribe semua device channels untuk status + sensor events
- Superadmin manajemen-alat: polling `/api/devices` setiap 3 detik (satu-satunya polling)
- Sidebar `components/sidebar.blade.php` sudah dinamis berdasarkan role (nakes/dokter/superadmin)
- Menu Instruksi tersedia di sidebar nakes dan dokter
- Navbar menampilkan nama user + role (dengan `ucfirst()`)
- `LaporanController` sudah role-aware: cek `auth()->user()->role` untuk pilih view folder
- `DashboardController` sudah support 3 role di `getViewByRole()`
- Form input data pasien belum punya backend handler
- Manajemen alat: CRUD sudah terhubung ke backend
- Manajemen user: UI ada, backend ada, route belum didaftarkan
- Grafik di PDF laporan menggunakan QuickChart.io (curl, SSL verify disabled)
- Simulator Python tersedia di `simulasi_py/` untuk testing tanpa hardware
- `updateCharts()` di kedua dashboard menerima data langsung (bukan fetch terpisah)
- `toggleDevice()` pakai optimistic update (langsung berubah, revert kalau gagal)
