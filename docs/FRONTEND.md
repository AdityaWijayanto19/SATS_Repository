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
    sidebar.blade.php         # Sidebar dinamis (nakes/dokter/superadmin), logo → landing page
    chat-widget.blade.php     # Floating chat widget (nakes & dokter dashboard)
    landing-navbar.blade.php  # Navbar halaman landing (publik + auth)
    landing-footer.blade.php  # Footer halaman landing
    profile-dropdown.blade.php # Dropdown profil di navbar (edit profile, logout)
  layouts/
    app.blade.php             # Layout utama (navbar + sidebar + yield)
    auth.blade.php            # Layout halaman auth (login, forgot/reset password)
    landing.blade.php         # Layout halaman landing page
  pages/
    landing.blade.php         # Landing page (publik)
    login.blade.php           # Halaman login + image slider
    auth/
      forgot-password.blade.php
      reset-password.blade.php
    profile/
      edit.blade.php          # Edit profil (nama, email, password, foto avatar)
    nakes/
      dashboard.blade.php     # Monitoring device (chart toggle terpisah/gabungan, vital sign, prediksi ML, session banner)
      inputdata.blade.php     # Form input data pasien (terhubung ke backend)
      laporan.blade.php       # Laporan medis + AJAX session selection + modal input pasien
      laporan-pdf.blade.php   # Template PDF laporan (DomPDF compatible, real data)
      instruksi.blade.php     # Chat instruksi dokter + laporan nakes (API-connected)
      monitoring.blade.php    # Halaman monitoring
      partials/
        _laporan-patient.blade.php   # Partial: identitas pasien + tombol input data
        _laporan-content.blade.php   # Partial: ML banner, chart, vital signs, stats, tabel
        _laporan-sidebar.blade.php   # Partial: info session + tombol download PDF
    dokter/
      dashboard.blade.php     # Monitoring device (chart toggle terpisah/gabungan, vital sign)
      inputdata.blade.php     # Form input data pasien (sama seperti nakes)
      laporan.blade.php       # Laporan medis (sama seperti nakes, role-aware)
      laporan-pdf.blade.php   # Template PDF (sama seperti nakes, role-aware)
      instruksi.blade.php     # Chat instruksi medis + pantau laporan nakes (API-connected)
      monitoring.blade.php    # Halaman monitoring
      monitor-3d.blade.php    # Monitoring 3D
    superadmin/
      dashboard.blade.php     # Dashboard superadmin (stat cards, tabel kritis, log)
      manajemen-alat.blade.php # Inventaris alat + modal tambah & detail alat
      manajemen-user.blade.php # Manajemen user + modal tambah & detail user
      laporan.blade.php       # Laporan superadmin (filter, stat cards, chart, tabel sensor)
      laporan-pdf.blade.php   # Template PDF laporan superadmin (landscape A4)
    landing/
      sections/
        hero.blade.php        # Section hero/beranda
        tentang.blade.php     # Section tentang SATS
        fitur.blade.php       # Section fitur
        alat.blade.php        # Section alat/perangkat
        cara-kerja.blade.php  # Section cara kerja
        faq.blade.php         # Section FAQ (accordion)
        closing.blade.php     # Section CTA penutup
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
| GET    | `/`                | (landing page)     |
| GET    | `/login`           | `login`            |
| POST   | `/login`           | `login.process`    |
| GET    | `/forgot-password` | `password.forgot`  |
| POST   | `/forgot-password` | `password.email`   |
| GET    | `/reset-password`  | `password.reset`   |
| POST   | `/reset-password`  | `password.update`  |

### Profile (auth, semua role)
| Method | URI              | Name            |
|--------|------------------|-----------------|
| GET    | `/profile/edit`  | `profile.edit`  |
| PUT    | `/profile`       | `profile.update`|

### Nakes (auth + role:nakes)
| Method | URI                      | Name               |
|--------|--------------------------|--------------------|
| GET    | `/nakes/dashboard`       | `dashboard`        |
| GET    | `/nakes/input-data-pasien`| `input-data-pasien`|
| POST   | `/nakes/input-data-pasien`| `input-data-pasien.store`|
| GET    | `/nakes/laporan`         | `laporan.index`    |
| GET    | `/nakes/laporan/session-data` | `laporan.session-data` (AJAX) |
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
- [x] Dashboard nakes (realtime via WebSocket, zero polling, session banner)
- [x] Input data pasien nakes (UI form + backend POST + link ke session)
- [x] Input data pasien modal di halaman laporan (popup dengan background blur)
- [x] Laporan nakes (real data dari monitoring_sessions + sensor_readings)
- [x] AJAX session selection di laporan (tanpa refresh halaman)
- [x] Partial views laporan (_laporan-patient, _laporan-content, _laporan-sidebar)
- [x] Chart.js re-init setelah AJAX content swap
- [x] Vital sign checkbox selection di laporan
- [x] Navbar & sidebar nakes (termasuk menu Instruksi)
- [x] Dashboard dokter (realtime via WebSocket, zero polling)
- [x] Laporan dokter (real data, role-aware, read-only untuk data pasien)
- [x] Halaman instruksi nakes (chat-style, laporan + konfirmasi instruksi dokter)
- [x] Halaman instruksi dokter (chat-style, instruksi medis + pantau laporan nakes)
- [x] Fitur instruksi dokter→nakes (terhubung ke API InstructionController)
- [x] Fitur laporan nakes→dokter (submit laporan kejadian)
- [x] Fitur respon nakes (dropdown 5 opsi respon + checklist instruksi)
- [x] Dashboard superadmin (stat cards, tabel kritis, log aktivitas realtime)
- [x] Sidebar dinamis (nakes/dokter/superadmin)
- [x] Sidebar logo clickable → landing page
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
- [x] Chart toggle terpisah/gabungan di dashboard nakes & dokter
- [x] Superadmin manajemen-alat: polling auto-refresh device status
- [x] Edit profil (nama, email, password, foto avatar per role)
- [x] Floating chat widget (nakes & dokter dashboard)
- [x] Chat: alignment pesan berdasarkan role (pesan sendiri = kanan, pesan lawan = kiri)
- [x] Chat: foto profil pengganti inisial role (DR/NK → foto avatar)
- [x] Landing page + 7 section (hero, tentang, fitur, alat, cara kerja, FAQ, closing)
- [x] Activity log realtime via WebSocket (PrivateChannel + Alpine double-init fix)
- [x] Dashboard nakes: fetchActiveSession() saat device online (tanpa refresh)
- [x] PDF download dengan data real (DomPDF + QuickChart.io, nama file = nomor rekam medis)

### Belum Dikerjakan
- [ ] Route untuk UserController (CRUD user belum terhubung ke UI)
- [ ] Laporan superadmin dari database (masih dummy data)
- [ ] Integrasi IoT real (simulator sudah ada, hardware belum)
- [ ] Notifikasi instruksi terkirim (toast/snackbar)
- [ ] Warning/highlight saat instruksi diselesaikan nakes
- [ ] **Fitur Hubungi Superadmin + Inbox** — form pelaporan kendala & request akun dari halaman login, inbox di dashboard superadmin (plan: `docs/plan-hubungi-superadmin.md`)

---

## Fitur Instruksi Dokter→Nakes (Floating Chat Widget)

### Alur:
1. **Dokter** mengirim instruksi medis dari floating chat widget di dashboard
2. **Nakes** melihat instruksi di floating chat widget dengan 9 quick reply buttons:
   - Sudah dilakukan, Dalam proses, Alat tidak tersedia, Obat sudah diberikan,
     Pasien stabil, Pasien kritis, Butuh bantuan, Gagal, Monitoring lanjutan
3. **Nakes** bisa mengirim laporan kejadian ke dokter (free text)
4. Nakes bisa konfirmasi instruksi yang sudah selesai
5. Dokter melihat respon nakes dan laporan di chat widget (chat-style layout)

### Implementasi:
- Component: `resources/views/components/chat-widget.blade.php` (Alpine.js `chatWidget()`)
- **Terhubung ke API:** GET/POST `/api/instruction`, PATCH `/api/instruction/{id}/complete`, POST `/api/instruction/report`
- Real-time via Laravel Reverb WebSocket (zero polling)
- Chat alignment: pesan sendiri = kanan, pesan lawan = kiri (role-aware)
- Avatar: foto profil user (dari tabel `users.photo`), fallback ke inisial role
- Broadcasting events: InstructionSent, InstructionStatusUpdated, InstructionReportSubmitted
- Broadcast payload include `user_photo` dan `nakes_photo`

---

## Notes

- Dashboard nakes & dokter **realtime via WebSocket** (Reverb, zero polling)
- Nakes dashboard: subscribe `device.{deviceId}` untuk status + sensor events
- Dokter dashboard: subscribe semua device channels untuk status + sensor events
- Superadmin manajemen-alat: polling `/api/devices` setiap 3 detik (satu-satunya polling)
- Sidebar `components/sidebar.blade.php` sudah dinamis berdasarkan role (nakes/dokter/superadmin)
- Sidebar logo clickable → landing page (`url('/')`)
- Menu Instruksi tersedia di sidebar nakes dan dokter
- Navbar menampilkan nama user + role (dengan `ucfirst()`)
- `LaporanController` sudah role-aware: cek `auth()->user()->role` untuk pilih view folder
- `DashboardController` sudah support 3 role di `getViewByRole()`
- Form input data pasien terhubung ke backend (PatientController + MonitoringSessionService)
- Manajemen alat: CRUD sudah terhubung ke backend
- Manajemen user: UI ada, backend ada, route terdaftar
- Grafik di PDF laporan menggunakan QuickChart.io (curl, SSL verify disabled)
- Simulator Python tersedia di `simulasi_py/` untuk testing tanpa hardware
- `updateCharts()` di kedua dashboard menerima data langsung (bukan fetch terpisah)
- `toggleDevice()` pakai optimistic update (langsung berubah, revert kalau gagal)
- Superadmin activity log realtime via WebSocket (PrivateChannel `superadmin.dashboard`)
- Alpine.js double-init issue: CDN `collapse` plugin + main Alpine bikin `init()` dipanggil 2x
- Fix: `window._superadminRef` global reference + `window._superadminEchoBound` guard
- Chart toggle di dashboard nakes & dokter: mode "Terspisah" (3 chart) dan "Gabungan" (1 chart, 3 Y-axis)
- Floating chat widget: pesan sendiri di kanan, pesan lawan di kiri (role-aware)
- Chat avatar: foto profil dari `users.photo`, fallback ke inisial (DR/NK)
- Edit profil: `ProfileController` + migration `photo` column + role-based default avatars
- Landing page: 7 sections (hero, tentang, fitur, alat, cara kerja, FAQ, closing)
- Redis server wajib jalan untuk autentikasi API key device (middleware `AuthenticateApiKey`)
- Redis tersedia di `redis_server/redis-server.exe`
- AJAX laporan: partial HTML di-render server, Chart.js re-init setelah content swap
- Alpine.js di partials: gunakan `onclick` global function (Alpine directives tidak work di innerHTML)
- Alpine.js data passing: `window.__laporanInit` global variable (hindari `@json()` di atribut HTML)
- Modal input pasien: di dalam scope `x-data` pada `<main>` (Alpine.js scope requirement)
