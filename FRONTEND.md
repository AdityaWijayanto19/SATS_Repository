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
    navbar.blade.php          # Top nav (logo, user name, logout)
    sidebar.blade.php         # Sidebar nakes (hardcoded, belum ada superadmin)
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
    superadmin/               # <<< BELUM ADA, PERLU DIBUAT
```

---

## Sistem Role

| Role       | URL Prefix       | Middleware         | Status         |
|------------|------------------|--------------------|----------------|
| nakes      | `/nakes/*`       | `role:nakes`       | Sudah ada view |
| superadmin | `/superadmin/*`  | `role:superadmin`  | View belum ada |

**Pembeda nakes umum & dokter:** Belum diimplementasikan. Saat ini semua nakes diperlakukan sama.

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

### Superadmin (auth + role:superadmin)
| Method | URI                            | Name                      |
|--------|--------------------------------|---------------------------|
| GET    | `/superadmin/dashboard`        | `superadmin.dashboard`    |
| GET    | `/superadmin/input-data-pasien`| `superadmin.input-data-pasien` |
| GET    | `/superadmin/laporan`          | `superadmin.laporan`      |

---

## Kondisi Saat Ini

### Sudah Dikerjakan
- [x] Halaman login + image slider
- [x] Sistem auth (login, logout, forgot/reset password)
- [x] Role middleware (nakes & superadmin)
- [x] Dashboard nakes (hardcoded data, chart, vital sign)
- [x] Input data pasien (UI form, belum ada backend POST)
- [x] Laporan (HTML + PDF, data dummy)
- [x] Navbar & sidebar nakes

### Belum Dikerjakan
- [ ] **Dashboard superadmin** (view belum ada sama sekali)
- [ ] **Sidebar superadmin** (saat ini sidebar hardcoded untuk nakes)
- [ ] Pembeda role nakes umum & dokter
- [ ] Fitur komentar/saran dokter
- [ ] Fitur respon nakes (dropdown respon)
- [ ] Model & migration: Pasien, VitalSign, RiwayatKondisi
- [ ] Backend untuk input data pasien (POST route + controller)
- [ ] Integrasi API (data real dari database)
- [ ] Seeder user dengan role

---

## Rencana Hari Ini (09 Mei 2026)

### Fokus: Dashboard Superadmin

**Target:** Membuat halaman dashboard superadmin dari nol.

**Yang perlu dikerjakan:**
1. Buat folder `resources/views/pages/superadmin/`
2. Buat view dashboard superadmin (sesuai mockup/design)
3. Buat sidebar khusus superadmin (atau buat sidebar dinamis berdasarkan role)
4. Sesuaikan `DashboardController` jika diperlukan
5. Pastikan route `/superadmin/dashboard` bisa diakses dengan benar

> **Catatan:** Detail halaman & fitur superadmin akan diisi setelah melihat mockup/design.

---

## Notes

- Semua data di dashboard & laporan masih **hardcoded/dummy**
- Sidebar `components/sidebar.blade.php` hanya untuk nakes, perlu dipisah atau dibuat dinamis
- `config/roles.php` tidak konsisten dengan route (ada `admin` & `dokter` tapi tidak dipakai, `superadmin` tidak ada di config)
- Form input data pasien belum punya backend handler
