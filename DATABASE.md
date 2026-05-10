# SATS - Database Documentation

## Overview

Database `sats_db` digunakan oleh sistem SATS (Smart Ambulance Telemedicine System) untuk menyimpan data perangkat IoT, data sensor vital sign pasien, rekam medis, dan manajemen pengguna.

---

## Entity Relationship Diagram (ERD)

```
+-------------+       +----------------+       +------------------+
|   users     |       |   patients     |       |    devices       |
|-------------|       |----------------|       |------------------|
| user_id (PK)|<------+ nakes (FK)     |       | device_id (PK)   |
| username    |       | patient_id(PK) |       | status           |
| email       |       | device_id (FK) +------>| last_seen        |
| password    |       | nama           |       +------------------+
| role        |       | jenis_kelamin  |               |
+-------------+       | umur           |               |
                      | catatan_tambahan|               |
                      +----------------+               |
                              |                        |
                              v                        v
                      +------------------+    +-------------------+
                      | medical_records  |    |   sensor_data     |
                      |------------------|    |-------------------|
                      | med_records_id   |    | id (PK)           |
                      | patient_id (FK)  |    | device_id (FK)    |
                      | device_id (FK)   |    | heart_rate        |
                      | heart_rate       |    | spo2              |
                      | spo2             |    | temperature       |
                      | temperature      |    | status            |
                      | status           |    | prediction        |
                      | prediction       |    | created_at        |
                      | created_at       |    +-------------------+
                      +------------------+

+----------------+       +----------------+       +----------------+
| activity_log   |       |   commands     |       | system_status  |
|----------------|       |----------------|       |----------------|
| id (PK)        |       | id (PK)        |       | device_id (FK) |
| message        |       | device_id (FK) |       | monitoring_    |
| created_at     |       | command        |       |   status       |
+----------------+       | status         |       +----------------+
                         | created_at     |
                         +----------------+

+----------------+
|   api_keys     |
|----------------|
| id (PK)        |
| key            |
| name           |
| is_active      |
| created_at     |
| updated_at     |
+----------------+
```

---

## Struktur Tabel

### 1. `users`

Menyimpan data akun pengguna sistem (superadmin, dokter, nakes/perawat).

| Kolom     | Tipe    | Constraint      | Keterangan                    |
|-----------|---------|-----------------|-------------------------------|
| user_id   | int     | PK, auto-increm | ID unik pengguna              |
| username  | varchar | NOT NULL        | Nama lengkap pengguna         |
| email     | varchar | UNIQUE, NOT NULL| Email untuk login             |
| password  | varchar | NOT NULL        | Hash password (bcrypt)        |
| role      | enum    | NOT NULL        | `superadmin`, `dokter`, `nakes` |

---

### 2. `devices`

Menyimpan data perangkat SATS Wearable yang terdaftar.

| Kolom     | Tipe    | Constraint       | Keterangan                     |
|-----------|---------|------------------|--------------------------------|
| device_id | varchar | PK               | ID unik perangkat (e.g. DEV-001) |
| status    | enum    | NOT NULL         | `online`, `offline`            |
| last_seen | timestamp | NULL           | Terakhir perangkat mengirim data |

---

### 3. `patients`

Menyimpan data pasien yang terhubung dengan perangkat.

| Kolom            | Tipe    | Constraint  | Keterangan                              |
|------------------|---------|-------------|-----------------------------------------|
| patient_id       | int     | PK, auto-increm | ID unik pasien                     |
| device_id        | varchar | FK → devices | Perangkat yang terpasang pada pasien    |
| nama             | varchar | NOT NULL    | Nama lengkap pasien                     |
| jenis_kelamin    | varchar | NOT NULL    | Jenis kelamin pasien                    |
| umur             | int     | NOT NULL    | Umur pasien                             |
| catatan_tambahan | text    | NULL        | Riwayat penyakit / alergi / catatan     |
| nakes            | int     | FK → users  | Nakes/dokter yang menangani             |

---

### 4. `sensor_data`

Menyimpan data real-time dari sensor perangkat IoT (vital sign pasien).

| Kolom       | Tipe    | Constraint    | Keterangan                              |
|-------------|---------|---------------|-----------------------------------------|
| id          | int     | PK, auto-increm | ID unik data sensor                  |
| device_id   | varchar | FK → devices  | Perangkat pengirim data                 |
| heart_rate  | int     | NOT NULL      | Detak jantung (bpm)                     |
| spo2        | int     | NOT NULL      | saturasi oksigen (%)                    |
| temperature | float   | NOT NULL      | Suhu tubuh (Celsius)                    |
| status      | enum    | NOT NULL      | `normal`, `warning`, `critical`         |
| prediction  | varchar | NULL          | Hasil prediksi ML                       |
| created_at  | timestamp | auto        | Waktu data direkam                      |

---

### 5. `medical_records`

Menyimpan rekam medis pasien yang sudah diinput oleh nakes setelah pasien tiba di rumah sakit.

| Kolom          | Tipe    | Constraint     | Keterangan                              |
|----------------|---------|----------------|-----------------------------------------|
| med_records_id | int     | PK, auto-increm | ID unik rekam medis                   |
| patient_id     | int     | FK → patients  | Pasien terkait                          |
| device_id      | int     | FK → devices   | Perangkat yang digunakan                |
| heart_rate     | int     | NOT NULL       | Detak jantung terakhir (bpm)            |
| spo2           | int     | NOT NULL       | Saturasi oksigen terakhir (%)           |
| temperature    | float   | NOT NULL       | Suhu tubuh terakhir (Celsius)           |
| status         | enum    | NOT NULL       | `normal`, `warning`, `critical`         |
| prediction     | varchar | NULL           | Hasil prediksi ML                       |
| created_at     | timestamp | auto         | Waktu rekam medis dibuat                |

---

### 6. `commands`

Menyimpan perintah start/stop yang dikirim ke perangkat dari dashboard.

| Kolom      | Tipe    | Constraint    | Keterangan                        |
|------------|---------|---------------|-----------------------------------|
| id         | int     | PK, auto-increm | ID unik perintah                |
| device_id  | varchar | FK → devices  | Perangkat target perintah         |
| command    | enum    | NOT NULL      | `start`, `stop`                   |
| status     | enum    | NOT NULL      | `pending`, `done`                 |
| created_at | timestamp | auto        | Waktu perintah dibuat             |

---

### 7. `activity_log`

Menyimpan log aktivitas sistem untuk audit trail.

| Kolom      | Tipe      | Constraint       | Keterangan                    |
|------------|-----------|------------------|-------------------------------|
| id         | int       | PK, auto-increm  | ID unik log                   |
| message    | text      | NOT NULL         | Deskripsi aktivitas           |
| created_at | timestamp | auto             | Waktu aktivitas terjadi       |

---

### 8. `system_status`

Menyimpan status monitoring perangkat.

| Kolom             | Tipe    | Constraint   | Keterangan                       |
|-------------------|---------|--------------|----------------------------------|
| device_id         | varchar | FK → devices | Perangkat terkait                |
| monitoring_status | enum    | NOT NULL     | `active`, `inactive`             |

---

### 9. `api_keys`

Menyimpan API key untuk autentikasi integrasi IoT.

| Kolom      | Tipe    | Constraint       | Keterangan                       |
|------------|---------|------------------|----------------------------------|
| id         | bigint  | PK, auto-increm  | ID unik API key                  |
| key        | varchar | UNIQUE, NOT NULL | API key string                   |
| name       | varchar | NOT NULL         | Nama/pemilik key                 |
| is_active  | tinyint | NOT NULL, default 1 | Status aktif (1=ya, 0=tidak) |
| created_at | timestamp | auto           | Waktu key dibuat                 |
| updated_at | timestamp | auto           | Waktu key terakhir diupdate      |

---

## Relasi Antar Tabel

| Tabel Asal      | Kolom FK       | Tabel Tujuan | Tipe Relasi  | Keterangan                              |
|-----------------|----------------|--------------|--------------|-----------------------------------------|
| `patients`      | `device_id`    | `devices`    | Many-to-One  | Satu pasien terhubung ke satu perangkat |
| `patients`      | `nakes`        | `users`      | Many-to-One  | Satu nakes menangani banyak pasien      |
| `sensor_data`   | `device_id`    | `devices`    | Many-to-One  | Satu perangkat mengirim banyak data     |
| `medical_records`| `patient_id`  | `patients`   | Many-to-One  | Satu pasien punya banyak rekam medis    |
| `medical_records`| `device_id`   | `devices`    | Many-to-One  | Rekam medis terkait perangkat           |
| `commands`      | `device_id`    | `devices`    | Many-to-One  | Banyak perintah ke satu perangkat       |
| `system_status` | `device_id`    | `devices`    | One-to-One   | Satu perangkat punya satu status        |

---

## Alur Data (Flow Sistem)

```
Nakes memasang perangkat pada pasien
        |
        v
Perangkat dinyalakan (via perangkat / dashboard monitoring)
        |
        v
Perangkat mulai mengambil data sensor & mengirim ke database
        |--- data masuk ke tabel sensor_data
        |--- data ditampilkan real-time di dashboard
        |
        v
Nakes di RS tujuan memantau kondisi pasien via dashboard
        |
        v
Pasien tiba di RS tujuan --> Nakes mematikan perangkat
        |--- perintah "stop" masuk ke tabel commands
        |
        v
Nakes di ambulans menginput data pasien
        |--- data masuk ke tabel patients
        |
        v
Nakes melakukan cross-check di menu laporan
        |--- pilih rentang tanggal/jam atau data vital terbaru
        |
        v
Rekam medis ter-generate otomatis
        |--- no rekam medis muncul di laporan
        |--- data tersimpan di tabel medical_records
        |--- laporan siap diunduh sebagai PDF
```

---

## Tabel yang Sudah Ada (Migration Laravel Default)

Tabel berikut sudah ada dari migration default Laravel:

- `users` (sudah dikustomisasi dengan kolom `role`)
- `password_reset_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

---

## Seeder

| Seeder              | Tabel    | Data                                              |
|---------------------|----------|---------------------------------------------------|
| `UserSeeder.php`    | `users`  | 3 akun: superadmin, dokter, nakes                 |

---

## Catatan Implementasi

- **Tipe data `device_id`**: Menggunakan `varchar` karena ID perangkat berformat string (e.g. `DEV-001`), bukan auto-increment integer.
- **`sensor_data` vs `medical_records`**: `sensor_data` menyimpan data real-time dari IoT secara terus-menerus, sedangkan `medical_records` adalah ringkasan yang diinput nakes setelah pasien tiba.
- **`commands`**: Digunakan untuk mengontrol perangkat (start/stop) dari dashboard, berfungsi sebagai queue perintah.
- **`api_keys`**: Digunakan untuk autentikasi request dari perangkat IoT ke API Laravel.
- **Status klasifikasi**: `normal`, `warning`, `critical` digunakan di `sensor_data` dan `medical_records` berdasarkan rule-based classification dari perangkat IoT.

---

*Last updated: 10 Mei 2026*
