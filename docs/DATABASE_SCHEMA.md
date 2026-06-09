# Dokumentasi Struktur Database — SATS (Smart Ambulance Tracking System)

> **Dokumen ini dihasilkan dari analisis seluruh file migrasi pada `database/migrations/`.**
> Tanggal: 3 Juni 2026
> Total migrasi dianalisis: 27 file
> Total tabel: 21 tabel

---

## 1. Ringkasan Skema (Database Overview)

Sistem ini merupakan platform **Smart Ambulance Tracking System (SATS)** yang dirancang untuk memantau kondisi vital pasien secara real-time melalui perangkat IoT. Database terdiri dari **21 tabel** yang dapat dikelompokkan menjadi beberapa domain:

| Domain | Tabel yang Termasuk |
| :--- | :--- |
| **Autentikasi & Pengelolaan Pengguna** | `users`, `password_reset_tokens`, `sessions`, `api_keys` |
| **Perangkat IoT & Monitoring** | `devices`, `device_monitorings`, `nakes_device_configs`, `system_statuses` |
| **Data Sensor & Pembacaan** | `sensor_datas`, `sensor_readings`, `failed_sensor_datas` |
| **Pasien & Rekam Medis** | `patients`, `medical_records`, `monitoring_sessions` |
| **Instruksi Medis** | `instructions` |
| **Logging & Aktivitas** | `activity_log` |
| **Cache & Queue (Laravel Framework)** | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` |

---

## 2. Detail Struktur Setiap Tabel

> **Keterangan Atribut:**
> - **PK** = Primary Key
> - **FK** = Foreign Key
> - **UQ** = Unique
> - **NN** = Not Null (wajib diisi)
> - **AI** = Auto Increment
> - **IDX** = Indexed
> - **Nullable** = Boleh bernilai NULL

---

### 2.1. `users`
* **Deskripsi**: Menyimpan data seluruh pengguna sistem (admin, dokter, nakes/tenaga kesehatan). Digunakan sebagai entitas utama untuk autentikasi dan otorisasi.
* **Sumber migrasi**: `0001_01_01_000000`, `2026_05_23_083102`, `2026_05_23_162626`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI, Unsigned | Primary Key utama |
| `name` | String | NN | Nama lengkap pengguna |
| `email` | String | NN, UQ | Alamat email, digunakan untuk login |
| `email_verified_at` | Timestamp | Nullable | Waktu verifikasi email |
| `password` | String | NN | Password (hashed) |
| `role` | String | NN | Peran pengguna (e.g., `admin`, `dokter`, `nakes`) |
| `photo` | String | Nullable | Path/URL foto profil pengguna |
| `last_activity` | Timestamp | Nullable | Timestamp aktivitas terakhir pengguna |
| `remember_token` | String | — | Token untuk fitur "Remember Me" |
| `created_at` | Timestamp | Auto | Waktu pembuatan record |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.2. `password_reset_tokens`
* **Deskripsi**: Menyimpan token untuk proses reset password pengguna.
* **Sumber migrasi**: `0001_01_01_000000`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `email` | String | NN, IDX | Email pengguna yang melakukan reset |
| `token` | String | NN | Token reset password (hashed) |
| `created_at` | Timestamp | Nullable | Waktu pembuatan token |

---

### 2.3. `sessions`
* **Deskripsi**: Menyimpan data sesi aktif pengguna untuk manajemen autentikasi berbasis cookie.
* **Sumber migrasi**: `0001_01_01_000000`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | String | PK | ID sesi unik |
| `user_id` | BigInteger | FK, Nullable, IDX | Menghubungkan ke tabel `users(id)` |
| `ip_address` | String(45) | Nullable | Alamat IP pengguna |
| `user_agent` | Text | Nullable | Browser/device user agent |
| `payload` | LongText | NN | Data sesi yang di-serialize |
| `last_activity` | Integer | IDX | Timestamp aktivitas terakhir (Unix) |

---

### 2.4. `cache`
* **Deskripsi**: Tabel cache internal Laravel untuk menyimpan data cache di database.
* **Sumber migrasi**: `0001_01_01_000001`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `key` | String | PK | Cache key unik |
| `value` | MediumText | NN | Nilai cache yang di-serialize |
| `expiration` | Integer | NN | Waktu kedaluwarsa (Unix timestamp) |

---

### 2.5. `cache_locks`
* **Deskripsi**: Mengelola lock pada cache untuk mencegah race condition.
* **Sumber migrasi**: `0001_01_01_000001`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `key` | String | PK | Lock key unik |
| `owner` | String | NN | Identifier pemilik lock |
| `expiration` | Integer | NN | Waktu kedaluwarsa lock |

---

### 2.6. `jobs`
* **Deskripsi**: Antrian job untuk pemrosesan asinkron (queue worker).
* **Sumber migrasi**: `0001_01_01_000002`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `queue` | String | IDX | Nama queue tujuan |
| `payload` | LongText | NN | Data job dalam format JSON |
| `attempts` | UnsignedTinyInteger | NN | Jumlah percobaan eksekusi |
| `reserved_at` | UnsignedInteger | Nullable | Timestamp saat job diproses |
| `available_at` | UnsignedInteger | NN | Timestamp kapan job tersedia |
| `created_at` | UnsignedInteger | NN | Timestamp pembuatan job |

---

### 2.7. `job_batches`
* **Deskripsi**: Menyimpan informasi batch job untuk pemrosesan massal.
* **Sumber migrasi**: `0001_01_01_000002`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | String | PK | ID batch unik |
| `name` | String | NN | Nama batch |
| `total_jobs` | Integer | NN | Total jumlah job dalam batch |
| `pending_jobs` | Integer | NN | Jumlah job yang belum selesai |
| `failed_jobs` | Integer | NN | Jumlah job yang gagal |
| `failed_job_ids` | LongText | NN | Daftar ID job yang gagal |
| `options` | MediumText | Nullable | Opsi batch dalam format JSON |
| `cancelled_at` | Integer | Nullable | Timestamp pembatalan batch |
| `created_at` | Integer | NN | Timestamp pembuatan batch |
| `finished_at` | Integer | Nullable | Timestamp penyelesaian batch |

---

### 2.8. `failed_jobs`
* **Deskripsi**: Menyimpan job yang gagal dieksekusi untuk keperluan debug dan retry.
* **Sumber migrasi**: `0001_01_01_000002`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `uuid` | String | NN, UQ | UUID unik job |
| `connection` | Text | NN | Nama koneksi queue |
| `queue` | Text | NN | Nama queue |
| `payload` | LongText | NN | Data job dalam format JSON |
| `exception` | LongText | NN | Detail exception/error |
| `failed_at` | Timestamp | Default: current | Waktu kegagalan |

---

### 2.9. `devices`
* **Deskripsi**: Menyimpan data perangkat IoT (monitor vital signs) yang terdaftar dalam sistem. Termasuk status koneksi, siapa yang memantau, dan hasil prediksi ML terakhir.
* **Sumber migrasi**: `2026_04_29_093738`, `2026_05_17_070307`, `2026_05_17_120333`, `2026_05_17_122745`, `2026_05_23_082400`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `device_id` | String(50) | PK | Identifier unik perangkat (bukan auto-increment) |
| `status` | Enum | Default: `'offline'` | Status koneksi: `online` / `offline` |
| `monitored_by` | BigInteger | FK, Nullable | Menghubungkan ke tabel `users(id)`, ON DELETE SET NULL |
| `ml_prediction` | Text | Nullable | Hasil prediksi teks dari ML (e.g., "Pasien akan MEMBURUK…") |
| `ml_condition` | String | Nullable | Kondisi dari ML: `NORMAL` / `WARNING` / `CRITICAL` |
| `ml_risk_level` | String | Nullable | Level risiko ML: `Low` / `Medium` / `High Risk` |
| `ml_probabilities` | Text | Nullable | Probabilitas prediksi ML (JSON string) |
| `ml_predicted_at` | Timestamp | Nullable | Waktu terakhir prediksi ML dilakukan |
| `last_seen` | Timestamp | Nullable | Terakhir perangkat terdeteksi aktif |
| `created_at` | Timestamp | Auto | Waktu pendaftaran perangkat |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.10. `system_statuses`
* **Deskripsi**: Menyimpan status sistem perangkat IoT seperti level baterai dan kekuatan sinyal.
* **Sumber migrasi**: `2026_04_29_094330`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `device_id` | String(50) | PK, FK | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `monitoring_status` | Enum | Nullable | Status monitoring: `active` / `inactive` |
| `battery_level` | Integer | Nullable | Level baterai (0–100) |
| `signal_strength` | Integer | Nullable | Kekuatan sinyal (RSSI) |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.11. `api_keys`
* **Deskripsi**: Mengelola API key yang digunakan oleh perangkat IoT untuk autentikasi ke server.
* **Sumber migrasi**: `2026_04_30_000000`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `device_id` | String(50) | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `key_hash` | String | NN, UQ | Hash dari API key (disimpan secara aman) |
| `name` | String | NN | Nama deskriptif (e.g., "Device SATS #1") |
| `is_active` | Boolean | Default: `true` | Status aktif/nonaktif key |
| `rate_limit_per_minute` | Integer | Default: `60` | Batas request per menit (throttle) |
| `last_used` | Timestamp | Nullable, IDX | Terakhir key digunakan |
| `last_used_ip` | String | Nullable | IP terakhir yang menggunakan key |
| `expires_at` | Timestamp | Nullable | Waktu kedaluwarsa key |
| `created_at` | Timestamp | Auto, IDX | Waktu pembuatan key |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.12. `sensor_datas`
* **Deskripsi**: Menyimpan data sensor vital signs yang diterima dari perangkat IoT. Setelah migrasi `drop_unused_columns`, kolom yang tersisa hanya data vital dasar tanpa prediksi ML (prediksi ML dipindahkan ke tabel `devices`).
* **Sumber migrasi**: `2026_04_29_094119`, `2026_05_16_100000`, `2026_05_17_120000`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `device_id` | String(50) | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `heart_rate` | Integer | Nullable | Detak jantung (BPM) |
| `spo2` | Integer | Nullable | Saturasi oksigen (%) |
| `temperature` | Float | Nullable | Suhu tubuh (°C) |
| `status` | Enum | Nullable | Status klinis: `normal` / `warning` / `critical` |
| `created_at` | Timestamp | Auto, IDX | Waktu penerimaan data |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

> **Catatan**: Kolom `systolic_bp`, `diastolic_bp`, `respiratory_rate`, `prediction`, `ml_prediction`, `ml_condition`, dan `ml_risk_level` telah dihapus pada migrasi `2026_05_17_120000`.

---

### 2.13. `patients`
* **Deskripsi**: Menyimpan data identitas pasien yang terhubung dengan perangkat IoT dan tenaga kesehatan (nakes).
* **Sumber migrasi**: `2026_05_10_062534`, `2026_05_24_100002`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `no_rekam_medis` | String(50) | NN, UQ | Nomor rekam medis unik pasien |
| `device_id` | String(50) | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `nama` | String | NN | Nama lengkap pasien |
| `nik` | String(20) | Nullable, UQ | Nomor Induk Kependudukan |
| `jenis_kelamin` | String | NN | Jenis kelamin pasien |
| `tanggal_lahir` | Date | Nullable | Tanggal lahir pasien |
| `umur` | Integer | NN | Usia pasien (tahun) |
| `penyakit_alergi` | String | Nullable | Riwayat penyakit/alergi |
| `catatan_tambahan` | Text | Nullable | Catatan medis tambahan |
| `nakes_id` | BigInteger | FK, IDX | Menghubungkan ke tabel `users(id)`, ON DELETE CASCADE |
| `created_at` | Timestamp | Auto | Waktu pendaftaran pasien |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.14. `medical_records`
* **Deskripsi**: Menyimpan rekam medis pasien berupa data vital signs yang dicatat dari perangkat IoT.
* **Sumber migrasi**: `2026_05_10_062611`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `patient_id` | BigInteger | FK, IDX | Menghubungkan ke tabel `patients(id)`, ON DELETE CASCADE |
| `device_id` | String(50) | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `heart_rate` | Integer | NN | Detak jantung (BPM) |
| `spo2` | Integer | NN | Saturasi oksigen (%) |
| `temperature` | Float | NN | Suhu tubuh (°C) |
| `status` | Enum | NN | Status klinis: `normal` / `warning` / `critical` |
| `prediction` | String | Nullable | Hasil prediksi klinis |
| `created_at` | Timestamp | Auto, IDX | Waktu pencatatan |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.15. `monitoring_sessions`
* **Deskripsi**: Menyimpan sesi monitoring pasien yang diinisiasi oleh nakes/dokter. Setiap sesi terhubung ke satu perangkat dan satu pasien, dengan nomor rekam medis unik.
* **Sumber migrasi**: `2026_05_24_100000`, `2026_05_25_074414`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `device_id` | String(50) | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `patient_id` | BigInteger | FK, Nullable | Menghubungkan ke tabel `patients(id)`, ON DELETE SET NULL |
| `medical_record_number` | String(50) | NN, UQ, IDX | Nomor rekam medis unik untuk sesi ini |
| `created_by` | BigInteger | FK, IDX | Menghubungkan ke tabel `users(id)` — nakes yang membuat sesi, ON DELETE CASCADE |
| `dokter_id` | BigInteger | FK, Nullable, IDX | Menghubungkan ke tabel `users(id)` — dokter yang bertanggung jawab, ON DELETE SET NULL |
| `started_at` | Timestamp | NN | Waktu mulai sesi monitoring |
| `ended_at` | Timestamp | Nullable | Waktu berakhir sesi monitoring |
| `status` | Enum | Default: `'active'` | Status sesi: `active` / `pending` / `completed` / `cancelled` |
| `total_readings` | Integer | Default: `0` | Total pembacaan sensor dalam sesi |
| `notes` | Text | Nullable | Catatan sesi monitoring |
| `created_at` | Timestamp | Auto | Waktu pembuatan record |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.16. `sensor_readings`
* **Deskripsi**: Menyimpan setiap pembacaan sensor vital signs yang terikat pada sesi monitoring tertentu.
* **Sumber migrasi**: `2026_05_24_100001`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `session_id` | BigInteger | FK, IDX | Menghubungkan ke tabel `monitoring_sessions(id)`, ON DELETE CASCADE |
| `heart_rate` | Integer | Nullable | Detak jantung (BPM) |
| `spo2` | Integer | Nullable | Saturasi oksigen (%) |
| `temperature` | Float | Nullable | Suhu tubuh (°C) |
| `status` | Enum | Nullable | Status klinis: `normal` / `warning` / `critical` |
| `recorded_at` | Timestamp | IDX | Waktu perekaman data sensor |
| `created_at` | Timestamp | Auto | Waktu pembuatan record |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.17. `instructions`
* **Deskripsi**: Menyimpan instruksi medis dari dokter kepada nakes, termasuk respons dan laporan dari nakes.
* **Sumber migrasi**: `2026_05_10_141646`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `device_id` | String(50) | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `dokter_id` | BigInteger | FK, Nullable, IDX | Menghubungkan ke tabel `users(id)` — dokter pemberi instruksi, ON DELETE CASCADE |
| `nakes_id` | BigInteger | FK, Nullable, IDX | Menghubungkan ke tabel `users(id)` — nakes pelaksana, ON DELETE SET NULL |
| `instruksi_dokter` | Text | Nullable | Isi instruksi dari dokter |
| `respon_nakes` | Text | Nullable | Respons dari nakes |
| `laporan_nakes` | Text | Nullable | Laporan pelaksanaan dari nakes |
| `is_completed` | Boolean | Default: `false` | Status penyelesaian instruksi |
| `completed_at` | Timestamp | Nullable | Waktu instruksi diselesaikan |
| `completed_by` | BigInteger | FK, Nullable | Menghubungkan ke tabel `users(id)` — yang menyelesaikan, ON DELETE SET NULL |
| `created_at` | Timestamp | Auto, IDX | Waktu pembuatan instruksi |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.18. `failed_sensor_datas`
* **Deskripsi**: Menyimpan data sensor yang gagal diproses, untuk keperluan retry dan debugging.
* **Sumber migrasi**: `2026_05_14_000000`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `device_id` | String | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `payload` | JSON | NN | Data sensor asli dalam format JSON |
| `error_message` | Text | Nullable | Pesan error saat pemrosesan gagal |
| `retry_count` | Integer | Default: `0`, IDX | Jumlah percobaan ulang |
| `last_retry_at` | Timestamp | Nullable | Waktu percobaan ulang terakhir |
| `failed_at` | Timestamp | Nullable, IDX | Waktu kegagalan pertama |
| `created_at` | Timestamp | Auto | Waktu pembuatan record |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.19. `nakes_device_configs`
* **Deskripsi**: Menyimpan konfigurasi perangkat IoT milik setiap tenaga kesehatan (nakes). Setiap nakes hanya memiliki satu konfigurasi. Kredensial WiFi dihandle secara lokal oleh Captive Portal pada ESP32.
* **Sumber migrasi**: `2026_05_16_000000`, `2026_06_05_000000` (hapus wifi columns)

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `user_id` | BigInteger | FK, UQ, IDX | Menghubungkan ke tabel `users(id)` — satu nakes satu config, ON DELETE CASCADE |
| `device_id` | String(50) | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `created_at` | Timestamp | Auto | Waktu pembuatan config |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

---

### 2.20. `device_monitorings`
* **Deskripsi**: Tabel pivot yang menghubungkan dokter dengan perangkat yang dipantau. Satu dokter dapat memantau beberapa perangkat, dan satu perangkat dapat dipantau oleh beberapa dokter.
* **Sumber migrasi**: `2026_05_23_085031`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `device_id` | String | FK, IDX | Menghubungkan ke tabel `devices(device_id)`, ON DELETE CASCADE |
| `dokter_id` | BigInteger | FK, IDX | Menghubungkan ke tabel `users(id)`, ON DELETE CASCADE |
| `created_at` | Timestamp | Auto | Waktu pendaftaran monitoring |
| `updated_at` | Timestamp | Auto | Waktu pembaruan terakhir |

> **Constraint**: Kolom (`device_id`, `dokter_id`) memiliki constraint **UNIQUE** — satu dokter tidak bisa mendaftarkan perangkat yang sama dua kali.

---

### 2.21. `activity_log`
* **Deskripsi**: Mencatat seluruh aktivitas penting dalam sistem untuk keperluan audit trail.
* **Sumber migrasi**: `2026_05_10_062617`, `2026_05_23_091533`

| Nama Kolom | Tipe Data | Atribut / Modifiers | Keterangan / Relasi |
| :--- | :--- | :--- | :--- |
| `id` | BigInteger | PK, AI | Primary Key utama |
| `type` | String(50) | NN | Tipe aktivitas (e.g., `login`, `monitoring`, `instruction`) |
| `message` | Text | NN | Deskripsi aktivitas |
| `user_name` | String(255) | Nullable | Nama pengguna yang melakukan aktivitas |
| `user_role` | String(20) | Nullable | Peran pengguna saat aktivitas |
| `icon` | String(20) | NN | Ikon untuk tampilan UI |
| `device_id` | String(50) | Nullable | Perangkat terkait aktivitas (soft reference, tanpa FK) |
| `created_at` | Timestamp | Auto | Waktu aktivitas terjadi |

---

## 3. Relasi Antar Tabel (Entity Relationships)

### 3.1. Ringkasan Relasi

| Relasi | Tipe | Kolom FK | Referensi | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| `sessions` → `users` | Many-to-One | `sessions.user_id` | `users.id` | Sesi dimiliki oleh pengguna |
| `devices` → `users` | Many-to-One | `devices.monitored_by` | `users.id` | Perangkat dipantau oleh seorang user (dokter), ON DELETE SET NULL |
| `sensor_datas` → `devices` | Many-to-One | `sensor_datas.device_id` | `devices.device_id` | Data sensor berasal dari perangkat, ON DELETE CASCADE |
| `system_statuses` → `devices` | One-to-One | `system_statuses.device_id` | `devices.device_id` | Status sistem milik satu perangkat, ON DELETE CASCADE |
| `api_keys` → `devices` | Many-to-One | `api_keys.device_id` | `devices.device_id` | API key milik perangkat, ON DELETE CASCADE |
| `patients` → `devices` | Many-to-One | `patients.device_id` | `devices.device_id` | Pasien terhubung ke perangkat, ON DELETE CASCADE |
| `patients` → `users` | Many-to-One | `patients.nakes_id` | `users.id` | Pasien ditangani oleh nakes, ON DELETE CASCADE |
| `medical_records` → `patients` | Many-to-One | `medical_records.patient_id` | `patients.id` | Rekam medis milik pasien, ON DELETE CASCADE |
| `medical_records` → `devices` | Many-to-One | `medical_records.device_id` | `devices.device_id` | Rekam medis dicatat oleh perangkat, ON DELETE CASCADE |
| `monitoring_sessions` → `devices` | Many-to-One | `monitoring_sessions.device_id` | `devices.device_id` | Sesi monitoring menggunakan perangkat, ON DELETE CASCADE |
| `monitoring_sessions` → `patients` | Many-to-One | `monitoring_sessions.patient_id` | `patients.id` | Sesi monitoring untuk pasien, ON DELETE SET NULL |
| `monitoring_sessions` → `users` (created_by) | Many-to-One | `monitoring_sessions.created_by` | `users.id` | Sesi dibuat oleh nakes, ON DELETE CASCADE |
| `monitoring_sessions` → `users` (dokter_id) | Many-to-One | `monitoring_sessions.dokter_id` | `users.id` | Sesi diawasi oleh dokter, ON DELETE SET NULL |
| `sensor_readings` → `monitoring_sessions` | Many-to-One | `sensor_readings.session_id` | `monitoring_sessions.id` | Pembacaan sensor terikat sesi, ON DELETE CASCADE |
| `instructions` → `devices` | Many-to-One | `instructions.device_id` | `devices.device_id` | Instruksi terkait perangkat, ON DELETE CASCADE |
| `instructions` → `users` (dokter_id) | Many-to-One | `instructions.dokter_id` | `users.id` | Instruksi diberikan oleh dokter, ON DELETE CASCADE |
| `instructions` → `users` (nakes_id) | Many-to-One | `instructions.nakes_id` | `users.id` | Instruksi dilaksanakan oleh nakes, ON DELETE SET NULL |
| `instructions` → `users` (completed_by) | Many-to-One | `instructions.completed_by` | `users.id` | Instruksi diselesaikan oleh user, ON DELETE SET NULL |
| `failed_sensor_datas` → `devices` | Many-to-One | `failed_sensor_datas.device_id` | `devices.device_id` | Data gagal milik perangkat, ON DELETE CASCADE |
| `nakes_device_configs` → `users` | One-to-One | `nakes_device_configs.user_id` | `users.id` | Config perangkat milik satu nakes (UNIQUE), ON DELETE CASCADE |
| `nakes_device_configs` → `devices` | Many-to-One | `nakes_device_configs.device_id` | `devices.device_id` | Config terkait perangkat, ON DELETE CASCADE |
| `device_monitorings` → `devices` | Many-to-One | `device_monitorings.device_id` | `devices.device_id` | Relasi monitoring ke perangkat, ON DELETE CASCADE |
| `device_monitorings` → `users` | Many-to-One | `device_monitorings.dokter_id` | `users.id` | Relasi monitoring ke dokter, ON DELETE CASCADE |

---

### 3.2. Diagram Relasi (Entity Relationship)

```
┌─────────────────────┐       ┌──────────────────────┐
│       users          │       │  password_reset_tokens│
│─────────────────────│       │──────────────────────│
│ PK  id              │       │    email (indexed)    │
│     name            │       │    token              │
│ UQ  email           │       │    created_at         │
│     email_verified_at│      └──────────────────────┘
│     password        │
│     role            │       ┌──────────────────────┐
│     photo           │       │      sessions         │
│     last_activity   │       │──────────────────────│
│     remember_token  │       │ PK  id               │
│     created_at      │◄──────│ FK  user_id (nullable)│
│     updated_at      │       │     ip_address        │
└────────┬────────────┘       │     user_agent        │
         │                    │     payload           │
         │                    │     last_activity     │
         │                    └──────────────────────┘
         │
         │ (monitored_by)     ┌──────────────────────────────────────┐
         ├───────────────────►│              devices                  │
         │                    │──────────────────────────────────────│
         │                    │ PK  device_id (String 50)            │
         │                    │     status (online/offline)           │
         │                    │ FK  monitored_by → users.id           │
         │                    │     ml_prediction                     │
         │                    │     ml_condition                      │
         │                    │     ml_risk_level                     │
         │                    │     ml_probabilities                  │
         │                    │     ml_predicted_at                   │
         │                    │     last_seen                         │
         │                    │     created_at / updated_at           │
         │                    └──────┬───────┬───────┬───────┬───────┘
         │                           │       │       │       │
         │           ┌───────────────┘       │       │       └──────────────────┐
         │           ▼                       ▼       ▼                          ▼
         │  ┌────────────────┐    ┌──────────────┐ ┌───────────────┐  ┌─────────────────┐
         │  │  sensor_datas  │    │ system_       │ │   api_keys    │  │  medical_records │
         │  │────────────────│    │ statuses      │ │───────────────│  │─────────────────│
         │  │ PK  id         │    │──────────────│ │ PK  id        │  │ PK  id          │
         │  │ FK  device_id  │    │ PK/FK dev_id │ │ FK  device_id │  │ FK  patient_id  │
         │  │    heart_rate  │    │    mon_status │ │ UQ  key_hash  │  │ FK  device_id   │
         │  │    spo2        │    │    battery    │ │    name       │  │    heart_rate   │
         │  │    temperature │    │    signal     │ │    is_active  │  │    spo2         │
         │  │    status      │    │    updated_at │ │    rate_limit │  │    temperature  │
         │  │    created_at  │    └──────────────┘ │    last_used  │  │    status       │
         │  └────────────────┘                     │    expires_at │  │    prediction   │
         │                                         └───────────────┘  └────────┬────────┘
         │                                                                      │
         │  ┌────────────────────┐    ┌─────────────────────┐                  │
         │  │failed_sensor_datas │    │    patients          │◄─────────────────┘
         │  │────────────────────│    │─────────────────────│
         │  │ PK  id             │    │ PK  id              │
         │  │ FK  device_id      │    │ UQ  no_rekam_medis  │
         │  │    payload (JSON)  │    │ FK  device_id       │
         │  │    error_message   │    │ FK  nakes_id → users│
         │  │    retry_count     │    │     nama            │
         │  │    failed_at       │    │ UQ  nik             │
         │  └────────────────────┘    │     jenis_kelamin   │
         │                            │     tanggal_lahir   │
         │                            │     umur            │
         │                            │     penyakit_alergi │
         │                            │     catatan_tambahan│
         │                            └──────────┬──────────┘
         │                                       │
         │  ┌──────────────────────┐             │
         │  │  nakes_device_configs │             │
         │  │──────────────────────│             │
         │  │ PK  id               │             │
         │  │ UQ  user_id → users  │             ▼
         │  │ FK  device_id        │    ┌─────────────────────────┐
         │  │                      │    │   monitoring_sessions    │
         │  │                      │    │─────────────────────────│
         │  └──────────────────────┘    │ PK  id                  │
         │                              │ FK  device_id → devices │
         │                              │ FK  patient_id → patients│
         │                              │ UQ  medical_record_number│
         │                              │ FK  created_by → users  │
         │                              │ FK  dokter_id → users   │
         │                              │     started_at / ended_at│
         │                              │     status              │
         │                              │     total_readings      │
         │                              │     notes               │
         │                              └────────────┬────────────┘
         │                                           │
         │  ┌──────────────────────┐                 │
         │  │ device_monitorings   │                 ▼
         │  │──────────────────────│    ┌─────────────────────────┐
         │  │ PK  id               │    │    sensor_readings       │
         │  │ FK  device_id        │    │─────────────────────────│
         │  │ FK  dokter_id → users│    │ PK  id                  │
         │  │ UQ (device_id,       │    │ FK  session_id          │
         │  │     dokter_id)       │    │     heart_rate          │
         │  └──────────────────────┘    │     spo2                │
         │                              │     temperature         │
         │  ┌──────────────────────┐    │     status              │
         │  │    instructions       │    │     recorded_at         │
         │  │──────────────────────│    └─────────────────────────┘
         │  │ PK  id               │
         │  │ FK  device_id        │    ┌─────────────────────────┐
         │  │ FK  dokter_id → users│    │     activity_log        │
         │  │ FK  nakes_id → users │    │─────────────────────────│
         │  │     instruksi_dokter │    │ PK  id                  │
         │  │     respon_nakes     │    │     type                │
         │  │     laporan_nakes    │    │     message             │
         │  │     is_completed     │    │     user_name           │
         │  │     completed_at     │    │     user_role           │
         │  │ FK  completed_by     │    │     icon                │
         │  └──────────────────────┘    │     device_id (no FK)   │
         │                              │     created_at          │
         │                              └─────────────────────────┘
         │
         │  ┌──────────────────────────────────────────────────────┐
         │  │                 Laravel Framework Tables              │
         │  │  cache | cache_locks | jobs | job_batches | failed_jobs│
         │  └──────────────────────────────────────────────────────┘
         │
```

---

### 3.3. Penjelasan Kardinalitas Detail

#### Hubungan Pusat: `devices`
Tabel `devices` adalah **entitas pusat** dalam sistem ini. Perangkat IoT menjadi penghubung utama antara data sensor, pasien, dan tenaga kesehatan.

- **One device → Many `sensor_datas`**: Satu perangkat menghasilkan banyak data sensor mentah.
- **One device → Many `sensor_readings`** (via `monitoring_sessions`): Data sensor yang terikat sesi.
- **One device → One `system_statuses`**: Setiap perangkat memiliki satu record status sistem.
- **One device → Many `api_keys`**: Perangkat dapat memiliki beberapa API key.
- **One device → Many `patients`**: Perangkat dapat digunakan untuk memantau beberapa pasien (berbeda waktu).
- **One device → Many `medical_records`**: Perangkat menghasilkan banyak rekam medis.
- **One device → Many `monitoring_sessions`**: Perangkat dapat digunakan dalam banyak sesi monitoring.
- **One device → Many `failed_sensor_datas`**: Data sensor yang gagal diproses.
- **One device → Many `instructions`**: Banyak instruksi terkait satu perangkat.
- **One device → Many `nakes_device_configs`**: Konfigurasi perangkat.
- **Many devices ↔ Many `users`** (via `device_monitorings`): Relasi many-to-many antara dokter dan perangkat yang dipantau.

#### Hubungan Pengguna: `users`
Tabel `users` memiliki multi-peran (admin, dokter, nakes) dan terhubung ke berbagai entitas:

- **One user → Many `sessions`**: Satu pengguna memiliki banyak sesi login.
- **One user → Many `patients`** (via `nakes_id`): Nakes menangani banyak pasien.
- **One user → Many `monitoring_sessions`** (via `created_by`): Nakes membuat banyak sesi.
- **One user → Many `monitoring_sessions`** (via `dokter_id`): Dokter mengawasi banyak sesi.
- **One user → Many `instructions`** (via `dokter_id`): Dokter memberikan banyak instruksi.
- **One user → Many `instructions`** (via `nakes_id`): Nakes menerima banyak instruksi.
- **One user → One `nakes_device_configs`** (UNIQUE): Setiap nakes memiliki satu konfigurasi perangkat.
- **Many users ↔ Many `devices`** (via `device_monitorings`): Dokter memantau banyak perangkat.
- **One user → Many `devices`** (via `monitored_by`): Satu user memantau banyak perangkat.

#### Hubungan Pasien: `patients`
- **One patient → Many `medical_records`**: Satu pasien memiliki banyak rekam medis.
- **One patient → Many `monitoring_sessions`**: Satu pasien dapat memiliki banyak sesi monitoring.
- **One patient ↔ One `devices`** (saat monitoring): Pasien terhubung ke perangkat melalui `device_id`.

#### Hubungan Sesi Monitoring: `monitoring_sessions`
- **One session → Many `sensor_readings`**: Satu sesi menghasilkan banyak pembacaan sensor.
- **One session → One `devices`**: Setiap sesi menggunakan satu perangkat.
- **One session → One `patients`**: Setiap sesi untuk satu pasien.
- **One session → One `users` (created_by)**: Dibuat oleh satu nakes.
- **One session → One `users` (dokter_id)**: Diawasi oleh satu dokter.

---

### 3.4. Catatan Implementasi

1. **Primary Key Non-Standard**: Tabel `devices` menggunakan `device_id` (String 50) sebagai primary key, bukan auto-increment BigInteger. Semua tabel yang berelasi dengan `devices` menggunakan `device_id` sebagai foreign key.

2. **Tidak Ada Struktur Inheritance/Generalization**: Dari analisis seluruh migrasi, **tidak ditemukan implementasi inheritance** (seperti Transaksi → TransaksiPeminjaman / TransaksiPengembalian). Sistem ini menggunakan pendekatan flat dengan peran pengguna yang dibedakan melalui kolom `role` pada tabel `users`.

3. **Soft Reference pada `activity_log`**: Kolom `device_id` pada tabel `activity_log` **tidak memiliki foreign key constraint** — ini adalah referensi longgar (soft reference) yang memungkinkan logging tetap berjalan meskipun perangkat dihapus.

4. **Dual Sensor Data**: Sistem memiliki dua jalur penyimpanan data sensor:
   - `sensor_datas`: Data mentah langsung dari perangkat IoT (real-time stream).
   - `sensor_readings`: Data sensor yang terikat pada sesi monitoring terstruktur.

5. **ML Prediction pada Level Device**: Hasil prediksi machine learning disimpan pada tabel `devices` (bukan pada data sensor), menunjukkan bahwa prediksi dilakukan secara agregat per perangkat, bukan per data point individual.

6. **Cascade Strategy**: Sebagian besar relasi menggunakan `ON DELETE CASCADE`, kecuali:
   - `devices.monitored_by` → `ON DELETE SET NULL` (perangkat tetap ada walau user dihapus)
   - `monitoring_sessions.patient_id` → `ON DELETE SET NULL`
   - `monitoring_sessions.dokter_id` → `ON DELETE SET NULL`
   - `instructions.nakes_id` → `ON DELETE SET NULL`
   - `instructions.completed_by` → `ON DELETE SET NULL`

---

## 4. UML Class Diagram — Atribut & Metode Operasi

> Setiap kelas merepresentasikan satu tabel database beserta metode-metode yang tersedia
> berdasarkan implementasi Controller, Model, dan Service pada kodebase.

---

### 4.1. `User`

```
+----------------------------------------------------+
|                      User                           |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - name          : String                            |
| - email         : String            {UQ}            |
| - email_verified_at : Timestamp     {Nullable}      |
| - password      : String            {hashed}        |
| - role          : String                            |
| - photo         : String            {Nullable}      |
| - last_activity : Timestamp         {Nullable}      |
| - remember_token : String                           |
| - created_at    : Timestamp                         |
| - updated_at    : Timestamp                         |
+----------------------------------------------------+
| + login(credentials: array): bool                   |
| + logout(): void                                    |
| + createUser(data: array): User                     |
| + updateUser(user: User, data: array): User         |
| + deleteUser(user: User): void                      |
| + updateProfile(data: array): User                  |
| + updateLastActivity(): void                        |
| + generateResetToken(email: string): string|null    |
| + resetPassword(email, token, password): bool       |
| + validateResetToken(email, token): User|null       |
| + getProfileData(): array                           |
| + patients(): HasMany                               |
| + deviceConfig(): HasOne                            |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `login()` | `AuthController` | Autentikasi pengguna, update `last_activity`, catat activity log |
| `logout()` | `AuthController` | Hapus sesi autentikasi, catat activity log |
| `createUser()` | `UserController` → `UserService` | Membuat user baru (oleh superadmin) |
| `updateUser()` | `UserController` → `UserService` | Memperbarui data user |
| `deleteUser()` | `UserController` → `UserService` | Menghapus user dari sistem |
| `updateProfile()` | `ProfileController` → `ProfileService` | User memperbarui profil sendiri (termasuk foto) |
| `updateLastActivity()` | `AuthController` | Memperbarui timestamp aktivitas terakhir |
| `generateResetToken()` | `AuthController` → `AuthService` | Membuat token untuk reset password |
| `resetPassword()` | `AuthController` → `AuthService` | Mereset password menggunakan token |
| `validateResetToken()` | `AuthController` → `AuthService` | Memvalidasi token reset password |
| `getProfileData()` | `ProfileController` → `ProfileService` | Mengambil data profil untuk halaman edit |
| `patients()` | `Model` | Relasi: satu nakes menangani banyak pasien |
| `deviceConfig()` | `Model` | Relasi: satu nakes memiliki satu konfigurasi perangkat |

---

### 4.2. `Device`

```
+-------------------------------------------------------+
|                      Device                            |
+-------------------------------------------------------+
| - device_id      : String(50)         {PK}            |
| - status         : Enum                               |
| - monitored_by   : BigInteger         {FK → users}    |
| - ml_prediction  : Text              {Nullable}       |
| - ml_condition   : String            {Nullable}       |
| - ml_risk_level  : String            {Nullable}       |
| - ml_probabilities : Text            {Nullable}       |
| - ml_predicted_at : Timestamp         {Nullable}      |
| - last_seen      : Timestamp          {Nullable}      |
| - created_at     : Timestamp                          |
| - updated_at     : Timestamp                          |
+-------------------------------------------------------+
| + registerDevice(deviceId, name): Device               |
| + deleteDevice(deviceId): void                         |
| + getAllDevices(): Collection                           |
| + getDeviceDetail(deviceId): Device                    |
| + updateStatus(status: string): Device                 |
| + toggleDeviceStatus(status: string): Device           |
| + updateMLPrediction(data: array): Device              |
| + getActiveSession(): MonitoringSession|null           |
| + getDevicesApi(minutes: int): Collection              |
| + sensorData(): HasMany                                |
| + systemStatus(): HasOne                               |
| + apiKeys(): HasMany                                   |
| + patients(): HasMany                                  |
| + medicalRecords(): HasMany                            |
| + monitoredBy(): BelongsTo                             |
| + monitoredByDokters(): BelongsToMany                  |
| + monitoringSessions(): HasMany                        |
| + activeSession(): HasOne                              |
+-------------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `registerDevice()` | `ManajemenAlatController` → `DeviceManagementService` | Mendaftarkan perangkat baru + generate API key |
| `deleteDevice()` | `ManajemenAlatController` → `DeviceManagementService` | Menghapus perangkat dari sistem |
| `getAllDevices()` | `ManajemenAlatController` → `DeviceManagementService` | Mengambil daftar semua perangkat |
| `getDeviceDetail()` | `ManajemenAlatController` → `DeviceManagementService` | Mengambil detail satu perangkat |
| `updateStatus()` | `DeviceDataController` (API) | Mengubah status online/offline dari perangkat IoT |
| `toggleDeviceStatus()` | `DashboardController` → `DashboardService` | Toggle status perangkat dari dashboard nakes |
| `updateMLPrediction()` | `PatientMonitoringService` | Memperbarui hasil prediksi ML pada perangkat |
| `getActiveSession()` | `MonitoringSessionService` | Mengambil sesi monitoring aktif untuk perangkat |
| `getDevicesApi()` | `DashboardController` → `DashboardService` | API polling untuk data grafik dashboard |
| `sensorData()` | `Model` | Relasi: satu perangkat menghasilkan banyak data sensor |
| `systemStatus()` | `Model` | Relasi: satu perangkat memiliki satu status sistem |
| `apiKeys()` | `Model` | Relasi: satu perangkat memiliki banyak API key |
| `patients()` | `Model` | Relasi: satu perangkat memantau banyak pasien |
| `medicalRecords()` | `Model` | Relasi: satu perangkat menghasilkan banyak rekam medis |
| `monitoredBy()` | `Model` | Relasi: perangkat dipantau oleh satu user |
| `monitoredByDokters()` | `Model` | Relasi M:N: perangkat dipantau oleh banyak dokter |
| `monitoringSessions()` | `Model` | Relasi: satu perangkat memiliki banyak sesi monitoring |
| `activeSession()` | `Model` | Relasi: satu sesi aktif terbaru untuk perangkat |

---

### 4.3. `SensorData`

```
+----------------------------------------------------+
|                   SensorData                        |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - device_id     : String(50)        {FK → devices} |
| - heart_rate    : Integer           {Nullable}     |
| - spo2          : Integer           {Nullable}     |
| - temperature   : Float             {Nullable}     |
| - status        : Enum              {Nullable}     |
| - created_at    : Timestamp                        |
| - updated_at    : Timestamp                        |
+----------------------------------------------------+
| + storeSensorData(deviceId, data): JsonResponse     |
| + storeSensorDataBatch(deviceId, readings): JSON    |
| + getLatestSensorData(deviceId): SensorData|null    |
| + getSensorDataHistory(deviceId): Collection        |
| + getPrediction(deviceId): array|null               |
| + device(): BelongsTo                               |
| + scopeLatest(deviceId): Builder                    |
| + scopeWithinRange(deviceId, from, to): Builder     |
| + scopeOnlyVitals(): Builder                        |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `storeSensorData()` | `SensorDataController` (API) | Menerima satu data sensor, broadcast WebSocket, queue DB write |
| `storeSensorDataBatch()` | `SensorDataController` (API) | Menerima batch data sensor untuk efisiensi high-frequency |
| `getLatestSensorData()` | `SensorDataController` (API) | Mengambil data sensor terbaru per perangkat |
| `getSensorDataHistory()` | `SensorDataController` (API) | Mengambil riwayat data sensor dengan paginasi (maks 24 jam) |
| `getPrediction()` | `SensorDataController` (API) → `PatientMonitoringService` | Mengambil prediksi ML untuk perangkat (cache 2 menit) |
| `device()` | `Model` | Relasi: data sensor milik satu perangkat |
| `scopeLatest()` | `Model` | Scope: data sensor terbaru untuk device tertentu |
| `scopeWithinRange()` | `Model` | Scope: data sensor dalam rentang waktu |
| `scopeOnlyVitals()` | `Model` | Scope: hanya kolom vital signs |

---

### 4.4. `SensorReading`

```
+----------------------------------------------------+
|                  SensorReading                      |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - session_id    : BigInteger        {FK → sessions}|
| - heart_rate    : Integer           {Nullable}     |
| - spo2          : Integer           {Nullable}     |
| - temperature   : Float             {Nullable}     |
| - status        : Enum              {Nullable}     |
| - recorded_at   : Timestamp                        |
| - created_at    : Timestamp                        |
| - updated_at    : Timestamp                        |
+----------------------------------------------------+
| + session(): BelongsTo                              |
| + getStatusBadgeAttribute(): string                 |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `session()` | `Model` | Relasi: pembacaan sensor terikat pada satu sesi monitoring |
| `getStatusBadgeAttribute()` | `Model` (Accessor) | Mengembalikan label status dalam bahasa Indonesia (Normal/Peringatan/Kritis) |

---

### 4.5. `SystemStatus`

```
+----------------------------------------------------+
|                  SystemStatus                       |
+----------------------------------------------------+
| - device_id       : String(50)      {PK, FK}       |
| - monitoring_status : Enum          {Nullable}     |
| - battery_level   : Integer         {Nullable}     |
| - signal_strength : Integer         {Nullable}     |
| - updated_at      : Timestamp                      |
+----------------------------------------------------+
| + storeSystemStatus(deviceId, data): JsonResponse   |
| + getSystemStatus(deviceId): SystemStatus|null      |
| + device(): BelongsTo                               |
| + isBatteryLow(): bool                              |
| + isSignalWeak(): bool                              |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `storeSystemStatus()` | `DeviceDataController` (API) | Menerima & menyimpan status sistem perangkat (battery, signal) |
| `getSystemStatus()` | `DeviceDataController` (API) | Mengambil status sistem perangkat |
| `device()` | `Model` | Relasi: status sistem milik satu perangkat |
| `isBatteryLow()` | `Model` | Mengecek apakah baterai di bawah 20% |
| `isSignalWeak()` | `Model` | Mengecek apakah sinyal lemah (RSSI < 30) |

---

### 4.6. `ApiKey`

```
+----------------------------------------------------+
|                     ApiKey                          |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - device_id     : String(50)        {FK → devices} |
| - key_hash      : String            {UQ}           |
| - name          : String                           |
| - is_active     : Boolean           {default: true}|
| - rate_limit_per_minute : Integer   {default: 60}  |
| - last_used     : Timestamp         {Nullable}     |
| - last_used_ip  : String            {Nullable}     |
| - expires_at    : Timestamp         {Nullable}     |
| - created_at    : Timestamp                        |
| - updated_at    : Timestamp                        |
+----------------------------------------------------+
| + hashKey(plainKey: string): string                 |
| + verifyKey(plainKey: string): bool                 |
| + findValidKey(plainKey, deviceId): ApiKey|null     |
| + updateLastUsed(ip: string): void                  |
| + isValid(): bool                                   |
| + device(): BelongsTo                               |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `hashKey()` | `Model` (Static) | Meng-hash plain text API key menggunakan bcrypt |
| `verifyKey()` | `Model` | Memverifikasi plain text key terhadap hash yang tersimpan |
| `findValidKey()` | `Model` (Static) | Mencari API key yang valid (aktif, belum expired, hash cocok) |
| `updateLastUsed()` | `Model` | Memperbarui timestamp dan IP terakhir key digunakan |
| `isValid()` | `Model` | Mengecek apakah key masih aktif dan belum expired |
| `device()` | `Model` | Relasi: API key milik satu perangkat |

---

### 4.7. `Patient`

```
+----------------------------------------------------+
|                    Patient                          |
+----------------------------------------------------+
| - id              : BigInteger      {PK, AI}       |
| - no_rekam_medis  : String(50)      {UQ}           |
| - device_id       : String(50)      {FK → devices} |
| - nama            : String                          |
| - nik             : String(20)      {UQ, Nullable} |
| - jenis_kelamin   : String                          |
| - tanggal_lahir   : Date            {Nullable}     |
| - umur            : Integer                         |
| - penyakit_alergi : String          {Nullable}     |
| - catatan_tambahan : Text           {Nullable}     |
| - nakes_id        : BigInteger      {FK → users}   |
| - created_at      : Timestamp                      |
| - updated_at      : Timestamp                      |
+----------------------------------------------------+
| + store(data: array): MonitoringSession             |
| + device(): BelongsTo                               |
| + nakes(): BelongsTo                                |
| + medicalRecords(): HasMany                         |
| + monitoringSessions(): HasMany                     |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `store()` | `PatientController` → `MonitoringSessionService` | Mendaftarkan pasien baru & menghubungkan ke sesi monitoring aktif |
| `device()` | `Model` | Relasi: pasien terhubung ke satu perangkat |
| `nakes()` | `Model` | Relasi: pasien ditangani oleh satu nakes |
| `medicalRecords()` | `Model` | Relasi: satu pasien memiliki banyak rekam medis |
| `monitoringSessions()` | `Model` | Relasi: satu pasien memiliki banyak sesi monitoring |

---

### 4.8. `MedicalRecord`

```
+----------------------------------------------------+
|                 MedicalRecord                       |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - patient_id    : BigInteger        {FK → patients}|
| - device_id     : String(50)        {FK → devices} |
| - heart_rate    : Integer                          |
| - spo2          : Integer                          |
| - temperature   : Float                            |
| - status        : Enum                             |
| - prediction    : String           {Nullable}      |
| - created_at    : Timestamp                        |
| - updated_at    : Timestamp                        |
+----------------------------------------------------+
| + patient(): BelongsTo                              |
| + device(): BelongsTo                               |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `patient()` | `Model` | Relasi: rekam medis milik satu pasien |
| `device()` | `Model` | Relasi: rekam medis dicatat oleh satu perangkat |

---

### 4.9. `MonitoringSession`

```
+-----------------------------------------------------------+
|                  MonitoringSession                         |
+-----------------------------------------------------------+
| - id                   : BigInteger       {PK, AI}        |
| - device_id            : String(50)       {FK → devices}  |
| - patient_id           : BigInteger       {FK → patients} |
| - medical_record_number : String(50)      {UQ}            |
| - created_by           : BigInteger       {FK → users}    |
| - dokter_id            : BigInteger       {FK → users}    |
| - started_at           : Timestamp                        |
| - ended_at             : Timestamp        {Nullable}      |
| - status               : Enum                             |
| - total_readings       : Integer          {default: 0}    |
| - notes                : Text             {Nullable}      |
| - created_at           : Timestamp                        |
| - updated_at           : Timestamp                        |
+-----------------------------------------------------------+
| + createSession(deviceId, userId): MonitoringSession       |
| + finalizeSession(sessionId): MonitoringSession            |
| + linkPatient(sessionId, data, dokterId): MonitoringSession|
| + getActiveSession(deviceId): MonitoringSession|null       |
| + getCompletedSessionsForDevice(deviceId): Collection      |
| + getReportData(sessionId, vitalSigns): MonitoringSession  |
| + getHistoryForChart(sessionId, vitalSigns): array         |
| + getLatestReading(sessionId): SensorReading|null          |
| + getSessionStats(sessionId): array                        |
| + device(): BelongsTo                                      |
| + patient(): BelongsTo                                     |
| + creator(): BelongsTo                                     |
| + dokter(): BelongsTo                                      |
| + sensorReadings(): HasMany                                |
| + latestReading(): HasOne                                  |
| + scopeActive(): Builder                                   |
| + scopeCompleted(): Builder                                |
| + scopeForDevice(deviceId): Builder                        |
+-----------------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `createSession()` | `MonitoringSessionService` | Membuat sesi monitoring baru + generate nomor rekam medis |
| `finalizeSession()` | `MonitoringSessionService` | Menyelesaikan sesi (set status `completed`, catat `ended_at`) |
| `linkPatient()` | `MonitoringSessionService` | Menghubungkan data pasien ke sesi monitoring + assign dokter |
| `getActiveSession()` | `MonitoringSessionService` | Mengambil sesi aktif untuk perangkat tertentu |
| `getCompletedSessionsForDevice()` | `MonitoringSessionService` | Mengambil daftar sesi yang sudah selesai untuk perangkat |
| `getReportData()` | `ReportService` | Mengambil data laporan lengkap untuk sesi |
| `getHistoryForChart()` | `ReportService` | Mengambil data historis vital signs untuk grafik |
| `getLatestReading()` | `ReportService` | Mengambil pembacaan sensor terbaru dalam sesi |
| `getSessionStats()` | `ReportService` | Menghitung statistik sesi (min/max/avg vital signs) |
| `device()` | `Model` | Relasi: sesi menggunakan satu perangkat |
| `patient()` | `Model` | Relasi: sesi untuk satu pasien |
| `creator()` | `Model` | Relasi: sesi dibuat oleh satu nakes |
| `dokter()` | `Model` | Relasi: sesi diawasi oleh satu dokter |
| `sensorReadings()` | `Model` | Relasi: satu sesi memiliki banyak pembacaan sensor |
| `latestReading()` | `Model` | Relasi: satu pembacaan sensor terbaru |
| `scopeActive()` | `Model` | Scope: filter sesi dengan status `active` |
| `scopeCompleted()` | `Model` | Scope: filter sesi dengan status `completed` |
| `scopeForDevice()` | `Model` | Scope: filter sesi berdasarkan device_id |

---

### 4.10. `Instruction`

```
+----------------------------------------------------+
|                  Instruction                        |
+----------------------------------------------------+
| - id              : BigInteger      {PK, AI}       |
| - device_id       : String(50)      {FK → devices} |
| - dokter_id       : BigInteger      {FK → users}   |
| - nakes_id        : BigInteger      {FK → users}   |
| - instruksi_dokter : Text           {Nullable}     |
| - respon_nakes    : Text            {Nullable}     |
| - laporan_nakes   : Text            {Nullable}     |
| - is_completed    : Boolean         {default: false}|
| - completed_at    : Timestamp       {Nullable}     |
| - completed_by    : BigInteger      {FK → users}   |
| - created_at      : Timestamp                      |
| - updated_at      : Timestamp                      |
+----------------------------------------------------+
| + index(deviceId): Collection                       |
| + store(data: array): Instruction                   |
| + storeReport(data: array): Instruction             |
| + complete(instruction, respon): Instruction        |
| + update(instruction, data): Instruction            |
| + dokter(): BelongsTo                               |
| + nakes(): BelongsTo                                |
| + device(): BelongsTo                               |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `index()` | `InstructionController` (API) → `InstructionService` | Mengambil daftar instruksi berdasarkan device_id |
| `store()` | `InstructionController` (API) → `InstructionService` | Membuat instruksi baru dari dokter ke nakes |
| `storeReport()` | `InstructionController` (API) → `InstructionService` | Nakes mengirimkan laporan pelaksanaan instruksi |
| `complete()` | `InstructionController` (API) → `InstructionService` | Menyelesaikan instruksi (set `is_completed`, catat waktu) |
| `update()` | `InstructionController` (API) → `InstructionService` | Memperbarui isi instruksi |
| `dokter()` | `Model` | Relasi: instruksi diberikan oleh satu dokter |
| `nakes()` | `Model` | Relasi: instruksi dilaksanakan oleh satu nakes |
| `device()` | `Model` | Relasi: instruksi terkait satu perangkat |

---

### 4.11. `FailedSensorData`

```
+----------------------------------------------------+
|               FailedSensorData                     |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - device_id     : String            {FK → devices} |
| - payload       : JSON                             |
| - error_message : Text             {Nullable}      |
| - retry_count   : Integer          {default: 0}    |
| - last_retry_at : Timestamp         {Nullable}     |
| - failed_at     : Timestamp         {Nullable}     |
| - created_at    : Timestamp                        |
| - updated_at    : Timestamp                        |
+----------------------------------------------------+
| + incrementRetry(): void                            |
| + device(): BelongsTo                               |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `incrementRetry()` | `Model` | Menambah hitungan retry dan memperbarui `last_retry_at` |
| `device()` | `Model` | Relasi: data gagal milik satu perangkat |

---

### 4.12. `NakesDeviceConfig`

```
+----------------------------------------------------+
|              NakesDeviceConfig                      |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - user_id       : BigInteger        {FK, UQ}       |
| - device_id     : String(50)        {FK → devices} |
| - created_at    : Timestamp                        |
| - updated_at    : Timestamp                        |
+----------------------------------------------------+
| + saveDeviceConfig(data: array): NakesDeviceConfig  |
| + resetDeviceConfig(): void                         |
| + user(): BelongsTo                                 |
| + device(): BelongsTo                               |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `saveDeviceConfig()` | `DashboardController` → `DashboardService` | Menyimpan/memperbarui konfigurasi WiFi perangkat nakes |
| `resetDeviceConfig()` | `DashboardController` → `DashboardService` | Menghapus konfigurasi perangkat (untuk ganti perangkat) |
| `user()` | `Model` | Relasi: konfigurasi milik satu nakes |
| `device()` | `Model` | Relasi: konfigurasi untuk satu perangkat |

---

### 4.13. `DeviceMonitoring`

```
+----------------------------------------------------+
|              DeviceMonitoring                       |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - device_id     : String            {FK → devices} |
| - dokter_id     : BigInteger        {FK → users}   |
| - created_at    : Timestamp                        |
| - updated_at    : Timestamp                        |
+----------------------------------------------------+
| + selectDevice(deviceId): void                      |
| + deselectDevice(deviceId): void                    |
| + deselectAllDevices(): void                        |
| + device(): BelongsTo                               |
| + dokter(): BelongsTo                               |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `selectDevice()` | `DashboardController` → `DashboardService` | Dokter memilih perangkat untuk dipantau |
| `deselectDevice()` | `DashboardController` → `DashboardService` | Dokter berhenti memantau perangkat tertentu |
| `deselectAllDevices()` | `DashboardController` → `DashboardService` | Dokter berhenti memantau semua perangkat (saat logout) |
| `device()` | `Model` | Relasi: monitoring terkait satu perangkat |
| `dokter()` | `Model` | Relasi: monitoring dilakukan oleh satu dokter |

---

### 4.14. `ActivityLog`

```
+----------------------------------------------------+
|                  ActivityLog                        |
+----------------------------------------------------+
| - id            : BigInteger        {PK, AI}       |
| - type          : String(50)                       |
| - message       : Text                             |
| - user_name     : String(255)       {Nullable}     |
| - user_role     : String(20)        {Nullable}     |
| - icon          : String(20)                       |
| - device_id     : String(50)        {Nullable}     |
| - created_at    : Timestamp                        |
+----------------------------------------------------+
| + log(type, message, userName, userRole, deviceId)  |
|   : ActivityLog                                     |
+----------------------------------------------------+
```

**Keterangan Metode:**
| Metode | Asal | Deskripsi |
| :--- | :--- | :--- |
| `log()` | `Model` (Static) | Mencatat aktivitas ke log + broadcast WebSocket ke dashboard |

**Tipe Aktivitas yang Terdaftar:**
| Tipe | Ikon (Warna) | Deskripsi |
| :--- | :--- | :--- |
| `user.login` | Blue | Pengguna berhasil login |
| `user.logout` | Red | Pengguna logout |
| `user.added` | Emerald | Superadmin menambahkan user baru |
| `user.deleted` | Red | Superadmin menghapus user |
| `password.reset_request` | Gray | Permintaan reset password |
| `password.reset_success` | Gray | Password berhasil direset |
| `device.online` | Emerald | Perangkat diaktifkan |
| `device.offline` | Red | Perangkat dinonaktifkan |
| `device.added` | Emerald | Perangkat baru didaftarkan |
| `device.deleted` | Red | Perangkat dihapus |
| `monitoring.started` | Violet | Sesi monitoring dimulai |
| `monitoring.stopped` | Violet | Sesi monitoring dihentikan |
| `monitoring.completed` | Emerald | Sesi monitoring selesai |
| `patient.registered` | Teal | Data pasien terdaftar |
| `patient.warning` | Amber | Kondisi pasien warning |
| `patient.critical` | Red | Kondisi pasien kritis |
| `instruction.sent` | Indigo | Instruksi dikirim ke nakes |
| `instruction.completed` | Green | Instruksi diselesaikan |

---

### 4.15. Framework Tables (Laravel Internal)

Tabel-tabel berikut merupakan tabel internal Laravel dan **tidak memiliki operasi bisnis** sendiri. Dikelola otomatis oleh framework.

```
+---------------------------+    +---------------------------+    +---------------------------+
|       Session             |    |    PasswordResetToken     |    |          Cache            |
+---------------------------+    +---------------------------+    +---------------------------+
| - id         : String {PK}|    | - email      : String     |    | - key        : String {PK}|
| - user_id    : FK→users   |    | - token      : String     |    | - value      : MediumText |
| - ip_address : String     |    | - created_at : Timestamp  |    | - expiration : Integer    |
| - user_agent : Text       |    +---------------------------+    +---------------------------+
| - payload    : LongText   |
| - last_activity : Integer |    +---------------------------+    +---------------------------+
+---------------------------+    |       CacheLock           |    |           Job             |
                                 +---------------------------+    +---------------------------+
+---------------------------+    | - key        : String {PK}|    | - id         : PK, AI     |
|       JobBatch            |    | - owner      : String     |    | - queue      : String     |
+---------------------------+    | - expiration : Integer    |    | - payload    : LongText   |
| - id         : String {PK}|    +---------------------------+    | - attempts   : TinyInt    |
| - name       : String     |                                     | - reserved_at: UInt       |
| - total_jobs : Integer    |    +---------------------------+    | - available_at: UInt      |
| - pending_jobs: Integer   |    |       FailedJob           |    | - created_at : UInt       |
| - failed_jobs: Integer    |    +---------------------------+    +---------------------------+
| - failed_job_ids: LongText|    | - id         : PK, AI     |
| - options    : MediumText |    | - uuid       : String {UQ}|
| - cancelled_at: Integer   |    | - connection : Text       |
| - created_at : Integer    |    | - queue      : Text       |
| - finished_at: Integer    |    | - payload    : LongText   |
+---------------------------+    | - exception  : LongText   |
                                 | - failed_at  : Timestamp  |
                                 +---------------------------+
```

---

## 5. Ringkasan Operasi per Role

### 5.1. Superadmin
| Operasi | Controller | Deskripsi |
| :--- | :--- | :--- |
| Kelola User (CRUD) | `UserController` | Membuat, mengedit, menghapus akun pengguna |
| Kelola Perangkat (CRUD) | `ManajemenAlatController` | Mendaftarkan, melihat detail, menghapus perangkat |
| Laporan Operasional & Audit | `SuperadminLaporanController` | Melihat & export PDF laporan operasional dan audit trail |

### 5.2. Dokter
| Operasi | Controller | Deskripsi |
| :--- | :--- | :--- |
| Pilih/Hentikan Pantau Perangkat | `DashboardController` | Memilih perangkat untuk dipantau, berhenti memantau |
| Lihat Dashboard | `DashboardController` | Melihat status perangkat dan data vital real-time |
| Kirim Instruksi ke Nakes | `InstructionController` (API) | Membuat instruksi medis untuk nakes |
| Lihat Rekam Medis | `RekamMedisController` | Melihat daftar dan detail rekam medis pasien |
| Export PDF Rekam Medis | `RekamMedisController` | Generate & download PDF rekam medis |
| Lihat Laporan | `LaporanController` | Melihat & export PDF laporan monitoring |

### 5.3. Nakes (Tenaga Kesehatan)
| Operasi | Controller | Deskripsi |
| :--- | :--- | :--- |
| Setup/Konfigurasi Perangkat | `DashboardController` | Mengatur WiFi & menghubungkan perangkat IoT |
| Toggle Status Perangkat | `DashboardController` | Mengaktifkan/menonaktifkan perangkat |
| Input Data Pasien | `PatientController` | Mendaftarkan pasien & menghubungkan ke sesi monitoring |
| Lihat Dashboard | `DashboardController` | Melihat status perangkat dan data vital real-time |
| Lihat Laporan | `LaporanController` | Melihat & export PDF laporan monitoring |
| Respon Instruksi | `InstructionController` (API) | Menanggapi instruksi dokter, mengirim laporan |

### 5.4. Perangkat IoT (API)
| Operasi | Endpoint | Deskripsi |
| :--- | :--- | :--- |
| Register Perangkat | `POST /api/device/register` | Mendaftarkan perangkat baru + dapat API key |
| Kirim Data Sensor | `POST /api/device/{id}/sensor` | Mengirim data vital signs (single/batch) |
| Kirim Status Sistem | `POST /api/device/{id}/status` | Mengirim info baterai, sinyal, status monitoring |
| Update Status | `PATCH /api/device/{id}/status` | Mengubah status online/offline |
| Autentikasi | `POST /api/device/auth` | Autentikasi perangkat dengan API key |
