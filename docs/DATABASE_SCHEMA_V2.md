# 📊 Database Schema — SATS (Smart Ambulance Tracking System)

> **Generated:** 2026-06-08
> **Total Tables:** 21 (16 application + 5 Laravel framework)
> **Total Migrations:** 28
> **Total Foreign Keys:** 23

---

## Daftar Isi

- [Entity Relationship Diagram (ERD)](#entity-relationship-diagram-erd)
- [UML Class Diagram (Methods per Table)](#uml-class-diagram)
- [Tabel Aplikasi (16)](#tabel-aplikasi)
  - [1. users](#1-users)
  - [2. devices](#2-devices)
  - [3. sensor_datas](#3-sensor_datas)
  - [4. system_statuses](#4-system_statuses)
  - [5. api_keys](#5-api_keys)
  - [6. patients](#6-patients)
  - [7. medical_records](#7-medical_records)
  - [8. instructions](#8-instructions)
  - [9. activity_log](#9-activity_log)
  - [10. failed_sensor_datas](#10-failed_sensor_datas)
  - [11. nakes_device_configs](#11-nakes_device_configs)
  - [12. device_monitorings](#12-device_monitorings)
  - [13. monitoring_sessions](#13-monitoring_sessions)
  - [14. sensor_readings](#14-sensor_readings)
- [Tabel Laravel Framework (5)](#tabel-laravel-framework)
- [Relasi Antar Tabel](#relasi-antar-tabel)
- [Eloquent Model Relationships](#eloquent-model-relationships)
- [Catatan Arsitektur](#catatan-arsitektur)

---

## Entity Relationship Diagram (ERD)

```
┌──────────────────────┐       ┌──────────────────────┐
│        users         │       │     password_reset    │
│──────────────────────│       │       _tokens         │
│ PK  id               │       │──────────────────────│
│     name             │       │     email (idx)       │
│     email (UQ)       │       │     token             │
│     password         │       │     created_at        │
│     role             │       └──────────────────────┘
│     photo            │
│     last_activity    │       ┌──────────────────────┐
│     email_verified_at│       │      sessions         │
│     remember_token   │       │──────────────────────│
│     created_at       │       │ PK  id               │
│     updated_at       │       │ FK  user_id ──────────┼──┐
└──────────┬───────────┘       │     ip_address        │  │
           │                   │     user_agent        │  │
           │                   │     payload           │  │
           │                   │     last_activity     │  │
           │                   └──────────────────────┘  │
           │                                             │
           │  ┌──────────────────────────────────────────┘
           │  │
           ▼  ▼
┌──────────────────────┐
│       devices        │◄─────────────────────────────────────────────┐
│──────────────────────│                                              │
│ PK  device_id (str)  │                                              │
│     status (enum)    │                                              │
│ FK  monitored_by ────┼──► users.id (SET NULL)                      │
│     ml_prediction    │                                              │
│     ml_condition     │                                              │
│     ml_risk_level    │                                              │
│     ml_probabilities │                                              │
│     ml_predicted_at  │                                              │
│     last_seen        │                                              │
│     created_at       │                                              │
│     updated_at       │                                              │
└──┬───┬───┬───┬───┬───┘                                              │
   │   │   │   │   │                                                  │
   │   │   │   │   │   ┌─────────────────────┐                        │
   │   │   │   │   └──►│   system_statuses   │                        │
   │   │   │   │       │─────────────────────│                        │
   │   │   │   │       │ PK  device_id ──────┼──► devices.device_id   │
   │   │   │   │       │     monitoring_status│     (CASCADE)         │
   │   │   │   │       │     battery_level   │                        │
   │   │   │   │       │     signal_strength │                        │
   │   │   │   │       │     updated_at      │                        │
   │   │   │   │       └─────────────────────┘                        │
   │   │   │   │                                                      │
   │   │   │   │       ┌─────────────────────┐                        │
   │   │   │   └──────►│     api_keys        │                        │
   │   │   │           │─────────────────────│                        │
   │   │   │           │ PK  id              │                        │
   │   │   │           │ FK  device_id ──────┼──► devices.device_id   │
   │   │   │           │     key_hash (UQ)   │     (CASCADE)          │
   │   │   │           │     name            │                        │
   │   │   │           │     is_active       │                        │
   │   │   │           │     rate_limit      │                        │
   │   │   │           │     last_used       │                        │
   │   │   │           │     last_used_ip    │                        │
   │   │   │           │     expires_at      │                        │
   │   │   │           └─────────────────────┘                        │
   │   │   │                                                          │
   │   │   │       ┌─────────────────────┐                            │
   │   │   └──────►│   sensor_datas      │                            │
   │   │           │─────────────────────│                            │
   │   │           │ PK  id              │                            │
   │   │           │ FK  device_id ──────┼──► devices.device_id       │
   │   │           │     heart_rate      │     (CASCADE)              │
   │   │           │     spo2            │                            │
   │   │           │     temperature     │                            │
   │   │           │     status (enum)   │                            │
   │   │           │     created_at      │                            │
   │   │           └─────────────────────┘                            │
   │   │                                                              │
   │   │       ┌─────────────────────┐    ┌─────────────────────┐     │
   │   └──────►│     patients        │    │   nakes_device      │     │
   │           │─────────────────────│    │     _configs        │     │
   │           │ PK  id              │    │─────────────────────│     │
   │           │     no_rekam_medis  │    │ PK  id              │     │
   │           │ FK  device_id ──────┼───►│ FK  user_id ────────┼────►│
   │           │     nama            │    │ FK  device_id ──────┼────►│
   │           │     nik (UQ)        │    │     created_at      │     │
   │           │     tanggal_lahir   │    │     updated_at      │     │
   │           │     jenis_kelamin   │    └─────────────────────┘     │
   │           │     umur            │                                │
   │           │     penyakit_alergi │    ┌─────────────────────┐     │
   │           │     catatan_tambahan│    │  device_monitorings │     │
   │           │ FK  nakes_id ───────┼───►│─────────────────────│     │
   │           │     created_at      │    │ PK  id              │     │
   │           │     updated_at      │    │ FK  device_id ──────┼────►│
   │           └──────┬──────────────┘    │ FK  dokter_id ──────┼──►users
   │                  │                   │     (UQ composite)  │     │
   │                  │                   └─────────────────────┘     │
   │                  │                                                │
   │   ┌──────────────┼──────────────┐                                │
   │   │              │              │                                  │
   │   ▼              ▼              ▼                                  │
┌──┴────────────┐ ┌─────────────────────┐ ┌──────────────────────┐    │
│medical_records│ │  monitoring_sessions│ │    instructions      │    │
│───────────────│ │─────────────────────│ │──────────────────────│    │
│PK  id         │ │ PK  id              │ │ PK  id               │    │
│FK  patient_id │ │ FK  device_id ──────┼►│ FK  device_id ───────┼───►│
│FK  device_id  │ │ FK  patient_id ─────┼►│ FK  dokter_id ───────┼──►users
│   heart_rate  │ │     medical_record  │ │ FK  nakes_id ────────┼──►users
│   spo2        │ │       _number (UQ)  │ │     instruksi_dokter │    │
│   temperature │ │ FK  created_by ─────┼►│     respon_nakes     │    │
│   status      │ │ FK  dokter_id ──────┼►│     laporan_nakes    │    │
│   prediction  │ │     started_at      │ │     is_completed     │    │
│   created_at  │ │     ended_at        │ │     completed_at     │    │
└───────────────┘ │     status (enum)   │ │ FK  completed_by ────┼──►users
                  │     total_readings  │ │     created_at       │    │
                  │     notes           │ └──────────────────────┘    │
                  │     created_at      │                              │
                  │     updated_at      │  ┌──────────────────────┐   │
                  └────────┬────────────┘  │  failed_sensor_datas │   │
                           │               │──────────────────────│   │
                           │               │ PK  id               │   │
                           │               │ FK  device_id ───────┼───┘
                           │               │     payload (JSON)   │
                           │               │     error_message    │
                           ▼               │     retry_count      │
                  ┌─────────────────────┐  │     last_retry_at    │
                  │   sensor_readings   │  │     failed_at        │
                  │─────────────────────│  └──────────────────────┘
                  │ PK  id              │
                  │ FK  session_id ─────┼──► monitoring_sessions.id
                  │     heart_rate      │
                  │     spo2            │
                  │     temperature     │
                  │     status (enum)   │
                  │     recorded_at     │
                  └─────────────────────┘

┌─────────────────────┐
│    activity_log      │  (soft reference — no FK)
│─────────────────────│
│ PK  id              │
│     type            │
│     message         │
│     user_name       │
│     user_role       │
│     icon            │
│     device_id*      │  * soft ref to devices.device_id
│     created_at      │
└─────────────────────┘
```

---

## UML Class Diagram

> Diagram ini menunjukkan **semua method** yang berkaitan dengan setiap tabel, berasal dari Model, Service, dan Controller.

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                                User                                        │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + name: string                                                            │
│   + email: string (UQ)                                                      │
│   + password: string (hashed)                                               │
│   + role: string (superadmin|dokter|nakes)                                  │
│   + photo: string?                                                          │
│   + last_activity: timestamp?                                               │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + patients(): hasMany → Patient                                           │
│   + deviceConfig(): hasOne → NakesDeviceConfig                              │
│─────────────────────────────────────────────────────────────────────────────│
│ AuthService:                                                                │
│   + login(credentials, request): array                                      │
│   + logout(request): void                                                   │
│   + generateResetToken(email): ?string                                      │
│   + sendPasswordResetEmail(email, token): bool                              │
│   + validateResetToken(email, token): ?User                                 │
│   + resetPassword(email, token, password): array                            │
│   - redirectByRole(role): string                                            │
│─────────────────────────────────────────────────────────────────────────────│
│ UserService:                                                                │
│   + createUser(data): User                                                  │
│   + updateUser(user, data): User                                            │
│   + deleteUser(user): void                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ ProfileService:                                                             │
│   + getProfileData(user): array                                             │
│   + updateProfile(user, data): void                                         │
│   + getAvatarsForRole(role): array                                          │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                               Devices                                       │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + device_id: string(50) (PK, non-incrementing)                            │
│   + status: enum (online|offline)                                           │
│   + monitored_by: bigint? (FK → users.id)                                   │
│   + ml_prediction: text?                                                    │
│   + ml_condition: string?                                                   │
│   + ml_risk_level: string?                                                  │
│   + ml_probabilities: text?                                                 │
│   + ml_predicted_at: timestamp?                                             │
│   + last_seen: timestamp?                                                   │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + sensorData(): hasMany → SensorData                                      │
│   + systemStatus(): hasOne → SystemStatus                                   │
│   + apiKeys(): hasMany → ApiKey                                             │
│   + patients(): hasMany → Patient                                           │
│   + medicalRecords(): hasMany → MedicalRecord                               │
│   + monitoredBy(): belongsTo → User                                         │
│   + monitoredByDokters(): belongsToMany → User (via device_monitorings)     │
│   + monitoringSessions(): hasMany → MonitoringSession                       │
│   + activeSession(): hasOne → MonitoringSession (filtered status=active)    │
│─────────────────────────────────────────────────────────────────────────────│
│ DeviceManagementService:                                                    │
│   + getAllDevices(): Collection                                              │
│   + registerDevice(deviceId, nama): array                                   │
│   + getDeviceDetail(deviceId): array                                        │
│   + deleteDevice(deviceId): void                                            │
│─────────────────────────────────────────────────────────────────────────────│
│ DeviceService:                                                              │
│   + storeSystemStatus(data): SystemStatus                                   │
│   + getSystemStatus(deviceId): ?SystemStatus                                │
│   + getDeviceDetail(deviceId): Devices                                      │
│   - clearSystemStatusCache(deviceId): void                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ DashboardService:                                                           │
│   + toggleDeviceStatus(status, config): array                               │
│   + selectDevice(deviceId): void                                            │
│   + deselectDevice(deviceId): void                                          │
│   + deselectAllDevices(): void                                              │
│   + getDevicesApi(minutes): Collection                                      │
│   + getDevicesWithActiveSession(): Collection                               │
│   + getDashboardData(): array                                               │
│   - getDevicesWithLatestData(): Collection                                  │
│   - getSuperadminStats(): array                                             │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                             SensorData                                      │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + device_id: string(50) (FK → devices.device_id)                          │
│   + heart_rate: integer?                                                    │
│   + spo2: integer?                                                          │
│   + temperature: float?                                                     │
│   + status: enum (normal|warning|critical)?                                 │
│   + created_at: timestamp?                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + device(): belongsTo → Devices                                           │
│─────────────────────────────────────────────────────────────────────────────│
│ Scopes:                                                                     │
│   + scopeLatest(deviceId): Builder                                          │
│   + scopeWithinRange(deviceId, from, to): Builder                           │
│   + scopeOnlyVitals(): Builder                                              │
│─────────────────────────────────────────────────────────────────────────────│
│ Accessors:                                                                  │
│   + statusBadge: Attribute (getter)                                         │
│─────────────────────────────────────────────────────────────────────────────│
│ SensorService:                                                              │
│   + storeSensorData(data): SensorData                                       │
│   + storeSensorDataBatch(readings): int                                     │
│   + getLatestSensorData(deviceId): ?SensorData                              │
│   - triggerPredictionIfNeeded(deviceId): void                               │
│   - runPrediction(deviceId): void                                           │
│   - clearLatestDataCache(deviceId): void                                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                           SystemStatus                                      │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + device_id: string(50) (PK, non-incrementing)                            │
│   + monitoring_status: enum (active|inactive)?                              │
│   + battery_level: integer? (0-100)                                         │
│   + signal_strength: integer? (RSSI)                                        │
│   + updated_at: timestamp?                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + device(): belongsTo → Devices                                           │
│─────────────────────────────────────────────────────────────────────────────│
│ Methods:                                                                    │
│   + isBatteryLow(): bool                                                    │
│   + isSignalWeak(): bool                                                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                              ApiKey                                         │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + device_id: string(50) (FK → devices.device_id)                          │
│   + key_hash: string (UQ)                                                   │
│   + name: string                                                            │
│   + is_active: boolean (default: true)                                      │
│   + rate_limit_per_minute: integer (default: 60)                            │
│   + last_used: timestamp?                                                   │
│   + last_used_ip: string?                                                   │
│   + expires_at: timestamp?                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + device(): belongsTo → Devices                                           │
│─────────────────────────────────────────────────────────────────────────────│
│ Static Methods:                                                             │
│   + hashKey(plainKey): string                                               │
│   + findValidKey(plainKey, deviceId): ?ApiKey                               │
│─────────────────────────────────────────────────────────────────────────────│
│ Instance Methods:                                                           │
│   + verifyKey(plainKey): bool                                               │
│   + updateLastUsed(ip): void                                                │
│   + isValid(): bool                                                         │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                              Patient                                        │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + no_rekam_medis: string(50) (UQ)                                         │
│   + device_id: string(50) (FK → devices.device_id)                          │
│   + nama: string                                                            │
│   + nik: string(20)? (UQ)                                                   │
│   + tanggal_lahir: date?                                                    │
│   + jenis_kelamin: string                                                   │
│   + umur: integer                                                           │
│   + penyakit_alergi: string?                                                │
│   + catatan_tambahan: text?                                                 │
│   + nakes_id: bigint (FK → users.id)                                        │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + device(): belongsTo → Devices                                           │
│   + nakes(): belongsTo → User                                               │
│   + medicalRecords(): hasMany → MedicalRecord                               │
│   + monitoringSessions(): hasMany → MonitoringSession                       │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                          MedicalRecord                                      │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + patient_id: bigint (FK → patients.id)                                   │
│   + device_id: string(50) (FK → devices.device_id)                          │
│   + heart_rate: integer                                                     │
│   + spo2: integer                                                           │
│   + temperature: float                                                      │
│   + status: enum (normal|warning|critical)                                  │
│   + prediction: string?                                                     │
│   + created_at: timestamp?                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + patient(): belongsTo → Patient                                          │
│   + device(): belongsTo → Devices                                           │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                           Instruction                                       │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + device_id: string(50) (FK → devices.device_id)                          │
│   + dokter_id: bigint? (FK → users.id)                                      │
│   + nakes_id: bigint? (FK → users.id)                                       │
│   + instruksi_dokter: text?                                                 │
│   + respon_nakes: text?                                                     │
│   + laporan_nakes: text?                                                    │
│   + is_completed: boolean (default: false)                                  │
│   + completed_at: timestamp?                                                │
│   + completed_by: bigint? (FK → users.id)                                   │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + dokter(): belongsTo → User                                              │
│   + nakes(): belongsTo → User                                               │
│   + device(): belongsTo → Devices                                           │
│─────────────────────────────────────────────────────────────────────────────│
│ InstructionService:                                                         │
│   + getInstructions(deviceId): Collection                                   │
│   + storeInstruction(data): array                                           │
│   + completeInstruction(instruction, respon): Instruction                   │
│   + updateInstruction(instruction, data): Instruction                       │
│   + storeReport(data): array                                                │
│   - formatSingleInstruction(item): array                                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                           ActivityLog                                       │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + type: string(50)                                                        │
│   + message: text                                                           │
│   + user_name: string(255)?                                                 │
│   + user_role: string(20)?                                                  │
│   + icon: string(20)                                                        │
│   + device_id: string(50)? (soft ref → devices.device_id)                   │
│   + created_at: timestamp?                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Constants:                                                                  │
│   + ICON_MAP: array (17 tipe aktivitas → warna icon)                        │
│─────────────────────────────────────────────────────────────────────────────│
│ Static Methods:                                                             │
│   + log(type, message, userName?, userRole?, deviceId?): ActivityLog        │
│─────────────────────────────────────────────────────────────────────────────│
│ Tipe Aktivitas:                                                             │
│   user.login, user.logout, user.added, user.deleted                         │
│   password.reset_request, password.reset_success                            │
│   device.online, device.offline, device.added, device.deleted               │
│   monitoring.started, monitoring.stopped, monitoring.completed              │
│   patient.registered, patient.warning, patient.critical                     │
│   instruction.sent, instruction.completed                                   │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                        FailedSensorData                                     │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + device_id: string (FK → devices.device_id)                              │
│   + payload: json (cast: array)                                             │
│   + error_message: text?                                                    │
│   + retry_count: integer (default: 0)                                       │
│   + last_retry_at: timestamp?                                               │
│   + failed_at: timestamp?                                                   │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + device(): belongsTo → Devices                                           │
│─────────────────────────────────────────────────────────────────────────────│
│ Methods:                                                                    │
│   + incrementRetry(): void                                                  │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                       NakesDeviceConfig                                     │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + user_id: bigint (UQ, FK → users.id)                                     │
│   + device_id: string(50) (FK → devices.device_id)                          │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + user(): belongsTo → User                                                │
│   + device(): belongsTo → Devices                                           │
│─────────────────────────────────────────────────────────────────────────────│
│ DashboardService:                                                           │
│   + saveDeviceConfig(data): array                                           │
│   + resetDeviceConfig(): void                                               │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                       DeviceMonitoring                                      │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + device_id: string (FK → devices.device_id)                              │
│   + dokter_id: bigint (FK → users.id)                                       │
│   + created_at: timestamp?                                                  │
│   + updated_at: timestamp?                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + device(): belongsTo → Devices                                           │
│   + dokter(): belongsTo → User                                              │
│─────────────────────────────────────────────────────────────────────────────│
│ DashboardService:                                                           │
│   + selectDevice(deviceId): void (create pivot)                             │
│   + deselectDevice(deviceId): void (delete pivot)                           │
│   + deselectAllDevices(): void (delete all for dokter)                      │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                      MonitoringSession                                      │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + device_id: string(50) (FK → devices.device_id)                          │
│   + patient_id: bigint? (FK → patients.id)                                  │
│   + medical_record_number: string(50) (UQ)                                  │
│   + created_by: bigint (FK → users.id)                                      │
│   + dokter_id: bigint? (FK → users.id)                                      │
│   + started_at: timestamp                                                   │
│   + ended_at: timestamp?                                                    │
│   + status: enum (active|pending|completed|cancelled)                       │
│   + total_readings: integer (default: 0)                                    │
│   + notes: text?                                                            │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + device(): belongsTo → Devices                                           │
│   + patient(): belongsTo → Patient                                          │
│   + creator(): belongsTo → User (created_by)                                │
│   + dokter(): belongsTo → User (dokter_id)                                  │
│   + sensorReadings(): hasMany → SensorReading                               │
│   + latestReading(): hasOne → SensorReading (latestOfMany)                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Scopes:                                                                     │
│   + scopeActive(): Builder                                                  │
│   + scopeCompleted(): Builder                                               │
│   + scopeForDevice(deviceId): Builder                                       │
│─────────────────────────────────────────────────────────────────────────────│
│ MonitoringSessionService:                                                   │
│   + createSession(deviceId, userId): MonitoringSession                      │
│   + finalizeSession(sessionId): ?MonitoringSession                          │
│   + cancelSession(sessionId): ?MonitoringSession                            │
│   + linkPatient(sessionId, patientData, dokterId?): MonitoringSession       │
│   + getSessionsForDevice(deviceId): Collection                              │
│   + getCompletedSessionsForDevice(deviceId): Collection                     │
│   + getActiveSession(deviceId): ?MonitoringSession                          │
│   + generateMedicalRecordNumber(deviceId): string                           │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                          SensorReading                                      │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   + id: bigint (PK)                                                         │
│   + session_id: bigint (FK → monitoring_sessions.id)                        │
│   + heart_rate: integer?                                                    │
│   + spo2: integer?                                                          │
│   + temperature: float?                                                     │
│   + status: enum (normal|warning|critical)?                                 │
│   + recorded_at: timestamp                                                  │
│─────────────────────────────────────────────────────────────────────────────│
│ Relationships:                                                              │
│   + session(): belongsTo → MonitoringSession                                │
│─────────────────────────────────────────────────────────────────────────────│
│ Accessors:                                                                  │
│   + statusBadge: Attribute (getter: Kritis|Peringatan|Normal)               │
│─────────────────────────────────────────────────────────────────────────────│
│ ReportService:                                                              │
│   + getReportData(sessionId, vitalSigns): ?MonitoringSession                │
│   + getLatestReading(sessionId): ?SensorReading                             │
│   + getHistoryForChart(sessionId, vitalSigns, startTime?, endTime?): array  │
│   + getSessionStats(sessionId, startTime?, endTime?): array                 │
│   + getRekamMedisList(dokterId): Collection                                 │
│   + getSessionSummaries(deviceId): Collection                               │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                     PatientMonitoringService                                │
│                    (ML Integration — Hugging Face)                           │
│─────────────────────────────────────────────────────────────────────────────│
│ Properties:                                                                 │
│   - apiUrl: string (HF Spaces endpoint)                                     │
│─────────────────────────────────────────────────────────────────────────────│
│ Methods:                                                                    │
│   + predict(vitalSigns): ?array                                              │
│   + getVitalSignsForDevice(deviceId): ?array                                │
│   + getPredictionForDevice(deviceId): ?array                                │
│─────────────────────────────────────────────────────────────────────────────│
│ Input: 15 angka (5 menit × 3 vital signs: HR, Temp, SpO2)                   │
│ Output: prediction, condition, risk_level, probabilities                    │
└─────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────┐
│                              <<class>>                                      │
│                      SuperadminReportService                                │
│─────────────────────────────────────────────────────────────────────────────│
│ Constants:                                                                  │
│   - KATEGORI_MAP: array (6 kategori → event types)                          │
│─────────────────────────────────────────────────────────────────────────────│
│ Methods:                                                                    │
│   + getOperasionalData(dari, sampai, deviceId?): array                      │
│   + getAuditData(dari, sampai, kategori?, deviceId?): array                 │
│   + getAllDevices(): Collection                                              │
│   + getKategoriList(): array                                                │
│   + generatePdf(dari, sampai, exportType, deviceId?, kategori?): Pdf        │
│   - generateTrenChartBase64(trenPerHari): ?string                           │
│   - fetchQuickChart(chartConfig, width, height): ?string                    │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Ringkasan Method per Layer

| Layer | Jumlah Class | Total Method | Keterangan |
|---|---|---|---|
| **Model** | 14 | 38 | Relationships, Scopes, Accessors |
| **Service** | 12 | 52 | Business logic & integrasi ML |
| **Controller** | 13 | 41 | HTTP request handlers |
| **Total** | **39** | **131** | |

### Distribusi Method per Tabel

| Tabel | Model Methods | Service Methods | Total |
|---|---|---|---|
| `users` | 2 (relationships) | 9 (AuthService + UserService + ProfileService) | 11 |
| `devices` | 9 (relationships) | 11 (DeviceService + DeviceManagement + Dashboard) | 20 |
| `sensor_datas` | 5 (3 scopes, 1 accessor, 1 rel) | 6 (SensorService) | 11 |
| `system_statuses` | 3 (1 rel, 2 methods) | 2 (DeviceService) | 5 |
| `api_keys` | 3 (1 rel, 5 methods) | 0 | 8 |
| `patients` | 4 (relationships) | 0 (via MonitoringSessionService) | 4 |
| `medical_records` | 2 (relationships) | 0 | 2 |
| `instructions` | 3 (relationships) | 6 (InstructionService) | 9 |
| `activity_log` | 1 (static log) | 0 (dipanggil dari service lain) | 1 |
| `failed_sensor_datas` | 2 (1 rel, 1 method) | 0 | 2 |
| `nakes_device_configs` | 2 (relationships) | 2 (DashboardService) | 4 |
| `device_monitorings` | 2 (relationships) | 3 (DashboardService) | 5 |
| `monitoring_sessions` | 6 (4 rel, 3 scopes) | 8 (MonitoringSessionService) | 14 |
| `sensor_readings` | 2 (1 rel, 1 accessor) | 6 (ReportService) | 8 |
| *(cross-cutting)* | — | 3 (PatientMonitoringService) | 3 |
| *(cross-cutting)* | — | 7 (SuperadminReportService) | 7 |

---

## Tabel Aplikasi

### 1. `users`

Tabel utama untuk semua pengguna sistem. Mendukung 3 role: `superadmin`, `dokter`, `nakes`.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `name` | varchar | NO | — | Nama lengkap |
| `email` | varchar | NO | — | **UNIQUE** — Login identifier |
| `email_verified_at` | timestamp | YES | — | Waktu verifikasi email |
| `password` | varchar | NO | — | Hashed password |
| `role` | varchar | NO | — | `superadmin` / `dokter` / `nakes` |
| `photo` | varchar | YES | — | Path foto profil |
| `last_activity` | timestamp | YES | — | Aktivitas terakhir |
| `remember_token` | varchar | YES | — | Token "remember me" |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), UQ(`email`)
**Foreign Keys:** —
**Seeder:** 7 users (1 superadmin, 3 dokter, 3 nakes)

---

### 2. `devices`

Tabel perangkat IoT (wearable). Menggunakan `device_id` string sebagai primary key (bukan auto-increment).

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `device_id` | varchar(50) | NO | — | **PRIMARY KEY** — contoh: `DEV_01` |
| `status` | enum(`online`, `offline`) | NO | `'offline'` | Status koneksi |
| `monitored_by` | bigint unsigned | YES | — | FK → `users.id` (dokter yang monitor) |
| `ml_prediction` | text | YES | — | Hasil prediksi ML |
| `ml_condition` | varchar | YES | — | Kondisi hasil ML |
| `ml_risk_level` | varchar | YES | — | Level risiko ML |
| `ml_probabilities` | text | YES | — | Probabilitas ML (JSON) |
| `ml_predicted_at` | timestamp | YES | — | Waktu prediksi ML |
| `last_seen` | timestamp | YES | — | Terakhir terlihat online |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`device_id`)
**Foreign Keys:**
- `monitored_by` → `users.id` ON DELETE **SET NULL**

**Seeder:** 3 devices (`DEV_01`, `DEV_02`, `DEV_03`)

---

### 3. `sensor_datas`

Data sensor real-time mentah dari perangkat. Data final per session ada di `sensor_readings`.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `device_id` | varchar(50) | NO | — | FK → `devices.device_id` |
| `heart_rate` | integer | YES | — | Detak jantung (BPM) |
| `spo2` | integer | YES | — | Saturasi oksigen (%) |
| `temperature` | float | YES | — | Suhu tubuh (°C) |
| `status` | enum(`normal`, `warning`, `critical`) | YES | — | Status vital signs |
| `created_at` | timestamp | YES | — | Indexed |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), IDX(`device_id`), IDX(`created_at`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE** ON UPDATE **CASCADE**

---

### 4. `system_statuses`

Status sistem perangkat (battery, signal, monitoring).

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `device_id` | varchar(50) | NO | — | **PRIMARY KEY** — FK → `devices.device_id` |
| `monitoring_status` | enum(`active`, `inactive`) | YES | — | Status monitoring |
| `battery_level` | integer | YES | — | Level baterai (0-100) |
| `signal_strength` | integer | YES | — | Kekuatan sinyal (RSSI) |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`device_id`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE** ON UPDATE **CASCADE**

**Catatan:** `$timestamps = false` di model (hanya `updated_at`)

---

### 5. `api_keys`

API key untuk autentikasi perangkat IoT.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `device_id` | varchar(50) | NO | — | FK → `devices.device_id` |
| `key_hash` | varchar | NO | — | **UNIQUE** — Hash dari API key |
| `name` | varchar | NO | — | Nama friendly |
| `is_active` | boolean | NO | `true` | Status aktif |
| `rate_limit_per_minute` | integer | NO | `60` | Batas request per menit |
| `last_used` | timestamp | YES | — | Terakhir digunakan |
| `last_used_ip` | varchar | YES | — | IP terakhir |
| `expires_at` | timestamp | YES | — | Waktu kadaluarsa |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), UQ(`key_hash`), IDX(`device_id`), IDX(`is_active`), IDX(`created_at`), IDX(`last_used`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE** ON UPDATE **CASCADE**

---

### 6. `patients`

Data pasien yang terdaftar.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `no_rekam_medis` | varchar(50) | NO | — | **UNIQUE** — Nomor rekam medis |
| `device_id` | varchar(50) | NO | — | FK → `devices.device_id` |
| `nama` | varchar | NO | — | Nama pasien |
| `nik` | varchar(20) | YES | — | **UNIQUE** — NIK |
| `tanggal_lahir` | date | YES | — | Tanggal lahir |
| `jenis_kelamin` | varchar | NO | — | Jenis kelamin |
| `umur` | integer | NO | — | Umur |
| `penyakit_alergi` | varchar | YES | — | Riwayat alergi |
| `catatan_tambahan` | text | YES | — | Catatan tambahan |
| `nakes_id` | bigint unsigned | NO | — | FK → `users.id` (nakes yang input) |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), UQ(`no_rekam_medis`), UQ(`nik`), IDX(`device_id`), IDX(`nakes_id`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE** ON UPDATE **CASCADE**
- `nakes_id` → `users.id` ON DELETE **CASCADE** ON UPDATE **CASCADE**

---

### 7. `medical_records`

Catatan rekam medis pasien.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `patient_id` | bigint unsigned | NO | — | FK → `patients.id` |
| `device_id` | varchar(50) | NO | — | FK → `devices.device_id` |
| `heart_rate` | integer | NO | — | Detak jantung |
| `spo2` | integer | NO | — | Saturasi oksigen |
| `temperature` | float | NO | — | Suhu tubuh |
| `status` | enum(`normal`, `warning`, `critical`) | NO | — | Status vital signs |
| `prediction` | varchar | YES | — | Hasil prediksi |
| `created_at` | timestamp | YES | — | Indexed |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), IDX(`patient_id`), IDX(`device_id`), IDX(`created_at`)
**Foreign Keys:**
- `patient_id` → `patients.id` ON DELETE **CASCADE** ON UPDATE **CASCADE**
- `device_id` → `devices.device_id` ON DELETE **CASCADE** ON UPDATE **CASCADE**

---

### 8. `instructions`

Instruksi dari dokter ke nakes selama monitoring.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `device_id` | varchar(50) | NO | — | FK → `devices.device_id` |
| `dokter_id` | bigint unsigned | YES | — | FK → `users.id` (pembuat instruksi) |
| `nakes_id` | bigint unsigned | YES | — | FK → `users.id` (penerima instruksi) |
| `instruksi_dokter` | text | YES | — | Isi instruksi dokter |
| `respon_nakes` | text | YES | — | Respon dari nakes |
| `laporan_nakes` | text | YES | — | Laporan dari nakes |
| `is_completed` | boolean | NO | `false` | Status selesai |
| `completed_at` | timestamp | YES | — | Waktu selesai |
| `completed_by` | bigint unsigned | YES | — | FK → `users.id` (yang menyelesaikan) |
| `created_at` | timestamp | YES | — | Indexed |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), IDX(`device_id`, `is_completed`), IDX(`dokter_id`), IDX(`nakes_id`), IDX(`created_at`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE** ON UPDATE **CASCADE**
- `dokter_id` → `users.id` ON DELETE **CASCADE**
- `nakes_id` → `users.id` ON DELETE **SET NULL**
- `completed_by` → `users.id` ON DELETE **SET NULL**

---

### 9. `activity_log`

Log aktivitas sistem (tidak menggunakan FK agar log tetap ada walau device dihapus).

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `type` | varchar(50) | NO | — | Tipe aktivitas |
| `message` | text | NO | — | Pesan log |
| `user_name` | varchar(255) | YES | — | Nama user |
| `user_role` | varchar(20) | YES | — | Role user |
| `icon` | varchar(20) | NO | — | Ikon untuk UI |
| `device_id` | varchar(50) | YES | — | **SOFT REF** → `devices.device_id` (tanpa FK) |
| `created_at` | timestamp | YES | — | |

**Indexes:** PK(`id`)
**Foreign Keys:** — (soft reference only)
**Catatan:** `$timestamps = false` di model (hanya `created_at` dikelola manual)

---

### 10. `failed_sensor_datas`

Penyimpanan data sensor yang gagal diproses untuk retry.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `device_id` | varchar | NO | — | FK → `devices.device_id` |
| `payload` | json | NO | — | Data asli (cast ke array) |
| `error_message` | text | YES | — | Pesan error |
| `retry_count` | integer | NO | `0` | Jumlah percobaan |
| `last_retry_at` | timestamp | YES | — | Waktu retry terakhir |
| `failed_at` | timestamp | YES | — | Waktu gagal |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), IDX(`device_id`, `failed_at`), IDX(`retry_count`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE**

---

### 11. `nakes_device_configs`

Konfigurasi pairing antara nakes (user) dan perangkat.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `user_id` | bigint unsigned | NO | — | **UNIQUE** — FK → `users.id` |
| `device_id` | varchar(50) | NO | — | FK → `devices.device_id` |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), UQ(`user_id`), IDX(`device_id`)
**Foreign Keys:**
- `user_id` → `users.id` ON DELETE **CASCADE** ON UPDATE **CASCADE**
- `device_id` → `devices.device_id` ON DELETE **CASCADE** ON UPDATE **CASCADE**

**Catatan:** Kolom `wifi_name` dan `wifi_password` sudah di-drop. WiFi config sekarang ditangani oleh ESP32 Captive Portal.

---

### 12. `device_monitorings`

Pivot table untuk relasi many-to-many antara dokter dan device yang dipantau.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `device_id` | varchar | NO | — | FK → `devices.device_id` |
| `dokter_id` | bigint unsigned | NO | — | FK → `users.id` |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), UQ(`device_id`, `dokter_id`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE**
- `dokter_id` → `users.id` ON DELETE **CASCADE**

---

### 13. `monitoring_sessions`

Sesi monitoring aktik yang mengikat device, pasien, dan dokter.

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `device_id` | varchar(50) | NO | — | FK → `devices.device_id` |
| `patient_id` | bigint unsigned | YES | — | FK → `patients.id` |
| `medical_record_number` | varchar(50) | NO | — | **UNIQUE** — No. rekam medis session |
| `created_by` | bigint unsigned | NO | — | FK → `users.id` (nakes pembuat) |
| `dokter_id` | bigint unsigned | YES | — | FK → `users.id` (dokter penanggung jawab) |
| `started_at` | timestamp | NO | — | Waktu mulai |
| `ended_at` | timestamp | YES | — | Waktu selesai |
| `status` | enum(`active`, `pending`, `completed`, `cancelled`) | NO | `'active'` | Status session |
| `total_readings` | integer | NO | `0` | Total pembacaan sensor |
| `notes` | text | YES | — | Catatan session |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), UQ(`medical_record_number`), IDX(`device_id`, `status`), IDX(`created_by`), IDX(`dokter_id`)
**Foreign Keys:**
- `device_id` → `devices.device_id` ON DELETE **CASCADE**
- `patient_id` → `patients.id` ON DELETE **SET NULL**
- `created_by` → `users.id` ON DELETE **CASCADE**
- `dokter_id` → `users.id` ON DELETE **SET NULL**

---

### 14. `sensor_readings`

Data sensor final per session (disalin dari `sensor_datas` saat session selesai).

| Kolom | Tipe | Nullable | Default | Keterangan |
|---|---|---|---|---|
| `id` | bigint unsigned, AI | NO | — | PRIMARY KEY |
| `session_id` | bigint unsigned | NO | — | FK → `monitoring_sessions.id` |
| `heart_rate` | integer | YES | — | Detak jantung (BPM) |
| `spo2` | integer | YES | — | Saturasi oksigen (%) |
| `temperature` | float | YES | — | Suhu tubuh (°C) |
| `status` | enum(`normal`, `warning`, `critical`) | YES | — | Status vital signs |
| `recorded_at` | timestamp | NO | — | Waktu perekaman |
| `created_at` | timestamp | YES | — | |
| `updated_at` | timestamp | YES | — | |

**Indexes:** PK(`id`), IDX(`session_id`), IDX(`recorded_at`)
**Foreign Keys:**
- `session_id` → `monitoring_sessions.id` ON DELETE **CASCADE**

---

## Tabel Laravel Framework

> Tabel-tabel ini dibawa oleh Laravel secara default. Bukan bagian dari logika bisnis aplikasi.

| # | Tabel | Keterangan | File Migrasi |
|---|---|---|---|
| 15 | `password_reset_tokens` | Token reset password | `0001_01_01_000000` |
| 16 | `sessions` | Session management (DB driver) | `0001_01_01_000000` |
| 17 | `cache` | Cache storage | `0001_01_01_000001` |
| 18 | `cache_locks` | Cache locking | `0001_01_01_000001` |
| 19 | `jobs` | Queue jobs | `0001_01_01_000002` |
| 20 | `job_batches` | Batched jobs | `0001_01_01_000002` |
| 21 | `failed_jobs` | Failed queue jobs | `0001_01_01_000002` |

---

## Relasi Antar Tabel

### Foreign Key Lengkap

| # | Tabel Sumber | Kolom FK | Tabel Target | Kolom Target | ON DELETE | ON UPDATE |
|---|---|---|---|---|---|---|
| 1 | `devices` | `monitored_by` | `users` | `id` | SET NULL | — |
| 2 | `sensor_datas` | `device_id` | `devices` | `device_id` | CASCADE | CASCADE |
| 3 | `system_statuses` | `device_id` | `devices` | `device_id` | CASCADE | CASCADE |
| 4 | `api_keys` | `device_id` | `devices` | `device_id` | CASCADE | CASCADE |
| 5 | `patients` | `device_id` | `devices` | `device_id` | CASCADE | CASCADE |
| 6 | `patients` | `nakes_id` | `users` | `id` | CASCADE | CASCADE |
| 7 | `medical_records` | `patient_id` | `patients` | `id` | CASCADE | CASCADE |
| 8 | `medical_records` | `device_id` | `devices` | `device_id` | CASCADE | CASCADE |
| 9 | `instructions` | `device_id` | `devices` | `device_id` | CASCADE | CASCADE |
| 10 | `instructions` | `dokter_id` | `users` | `id` | CASCADE | — |
| 11 | `instructions` | `nakes_id` | `users` | `id` | SET NULL | — |
| 12 | `instructions` | `completed_by` | `users` | `id` | SET NULL | — |
| 13 | `failed_sensor_datas` | `device_id` | `devices` | `device_id` | CASCADE | — |
| 14 | `nakes_device_configs` | `user_id` | `users` | `id` | CASCADE | CASCADE |
| 15 | `nakes_device_configs` | `device_id` | `devices` | `device_id` | CASCADE | CASCADE |
| 16 | `device_monitorings` | `device_id` | `devices` | `device_id` | CASCADE | — |
| 17 | `device_monitorings` | `dokter_id` | `users` | `id` | CASCADE | — |
| 18 | `monitoring_sessions` | `device_id` | `devices` | `device_id` | CASCADE | — |
| 19 | `monitoring_sessions` | `patient_id` | `patients` | `id` | SET NULL | — |
| 20 | `monitoring_sessions` | `created_by` | `users` | `id` | CASCADE | — |
| 21 | `monitoring_sessions` | `dokter_id` | `users` | `id` | SET NULL | — |
| 22 | `sensor_readings` | `session_id` | `monitoring_sessions` | `id` | CASCADE | — |
| 23 | `sessions` | `user_id` | `users` | `id` | (implicit) | (implicit) |

### Soft Reference (tanpa FK constraint)

| Tabel | Kolom | Referensi | Alasan |
|---|---|---|---|
| `activity_log` | `device_id` | `devices.device_id` | Log tetap ada walau device dihapus |

### Ringkasan Cascade Strategy

| Strategi | Digunakan di | Alasan |
|---|---|---|
| **CASCADE** | Sebagian besar FK | Data child otomatis terhapus saat parent dihapus |
| **SET NULL** | `devices.monitored_by`, `monitoring_sessions.patient_id`, `monitoring_sessions.dokter_id`, `instructions.nakes_id`, `instructions.completed_by` | Data child tetap ada, FK di-null-kan |

---

## Eloquent Model Relationships

### User Model

| Method | Type | Related | FK | Keterangan |
|---|---|---|---|---|
| `patients()` | hasMany | Patient | `nakes_id` | Pasien yang diinput nakes |
| `deviceConfig()` | hasOne | NakesDeviceConfig | `user_id` | Konfigurasi device nakes |

### Devices Model

| Method | Type | Related | FK | Keterangan |
|---|---|---|---|---|
| `sensorData()` | hasMany | SensorData | `device_id` | Data sensor mentah |
| `systemStatus()` | hasOne | SystemStatus | `device_id` | Status sistem |
| `apiKeys()` | hasMany | ApiKey | `device_id` | API keys |
| `patients()` | hasMany | Patient | `device_id` | Pasien terkait |
| `medicalRecords()` | hasMany | MedicalRecord | `device_id` | Rekam medis |
| `monitoredBy()` | belongsTo | User | `monitored_by` | Dokter yang monitor |
| `monitoredByDokters()` | belongsToMany | User | pivot: `device_monitorings` | Semua dokter yang monitor |
| `monitoringSessions()` | hasMany | MonitoringSession | `device_id` | Semua session |
| `activeSession()` | hasOne | MonitoringSession | `device_id` | Session aktif (latest) |

### SensorData Model

| Method | Type | Related | FK |
|---|---|---|---|
| `device()` | belongsTo | Devices | `device_id` |

### SystemStatus Model

| Method | Type | Related | FK |
|---|---|---|---|
| `device()` | belongsTo | Devices | `device_id` |

### ApiKey Model

| Method | Type | Related | FK |
|---|---|---|---|
| `device()` | belongsTo | Devices | `device_id` |

### Patient Model

| Method | Type | Related | FK | Keterangan |
|---|---|---|---|---|
| `device()` | belongsTo | Devices | `device_id` | |
| `nakes()` | belongsTo | User | `nakes_id` | Nakes yang input |
| `medicalRecords()` | hasMany | MedicalRecord | `patient_id` | |
| `monitoringSessions()` | hasMany | MonitoringSession | `patient_id` | |

### MedicalRecord Model

| Method | Type | Related | FK |
|---|---|---|---|
| `patient()` | belongsTo | Patient | `patient_id` |
| `device()` | belongsTo | Devices | `device_id` |

### Instruction Model

| Method | Type | Related | FK | Keterangan |
|---|---|---|---|---|
| `dokter()` | belongsTo | User | `dokter_id` | Pembuat instruksi |
| `nakes()` | belongsTo | User | `nakes_id` | Penerima instruksi |
| `device()` | belongsTo | Devices | `device_id` | |

### FailedSensorData Model

| Method | Type | Related | FK |
|---|---|---|---|
| `device()` | belongsTo | Devices | `device_id` |

### NakesDeviceConfig Model

| Method | Type | Related | FK |
|---|---|---|---|
| `user()` | belongsTo | User | `user_id` |
| `device()` | belongsTo | Devices | `device_id` |

### DeviceMonitoring Model

| Method | Type | Related | FK |
|---|---|---|---|
| `device()` | belongsTo | Devices | `device_id` |
| `dokter()` | belongsTo | User | `dokter_id` |

### MonitoringSession Model

| Method | Type | Related | FK | Keterangan |
|---|---|---|---|---|
| `device()` | belongsTo | Devices | `device_id` | |
| `patient()` | belongsTo | Patient | `patient_id` | |
| `creator()` | belongsTo | User | `created_by` | Nakes pembuat |
| `dokter()` | belongsTo | User | `dokter_id` | Dokter PJ |
| `sensorReadings()` | hasMany | SensorReading | `session_id` | |
| `latestReading()` | hasOne | SensorReading | `session_id` | latestOfMany |

### SensorReading Model

| Method | Type | Related | FK |
|---|---|---|---|
| `session()` | belongsTo | MonitoringSession | `session_id` |

---

## Catatan Arsitektur

### 1. Non-Standard PK pada `devices`
Tabel `devices` menggunakan `device_id` (string 50) sebagai primary key, bukan auto-increment integer. Semua tabel yang berelasi menggunakan string FK ini.

### 2. Dual Sensor Storage
- **`sensor_datas`** — Data real-time mentah dari perangkat IoT
- **`sensor_readings`** — Data final per session (disalin saat session selesai)

### 3. ML Predictions pada Level Device
Hasil prediksi ML (`ml_prediction`, `ml_condition`, `ml_risk_level`, `ml_probabilities`, `ml_predicted_at`) disimpan di tabel `devices`, bukan per data point.

### 4. WiFi Config Dihapus dari `nakes_device_configs`
Kolom `wifi_name` dan `wifi_password` sudah di-drop. Konfigurasi WiFi sekarang ditangani langsung oleh ESP32 Captive Portal.

### 5. Soft Reference pada `activity_log`
Kolom `device_id` di `activity_log` tidak memiliki FK constraint, sehingga log tetap tersimpan meskipun device dihapus.

### 6. Cascade Strategy
- **CASCADE** — Sebagian besar relasi (data child ikut terhapus)
- **SET NULL** — Untuk relasi opsional: `monitored_by`, `patient_id`, `dokter_id`, `nakes_id`, `completed_by`

---

> **Source:** `database/migrations/` (28 files), `app/Models/` (14 files), `database/seeders/` (3 files)

---

## Klasifikasi Relasi (One-to-One, One-to-Many, Many-to-Many)

### One-to-One (1:1)

> Satu record di tabel A hanya berelasi dengan **satu** record di tabel B, dan sebaliknya.

| Parent | Child | Kolom FK | Eloquent (Parent) | Eloquent (Child) | Keterangan |
|---|---|---|---|---|---|
| `users` | `nakes_device_configs` | `user_id` (UQ) | `hasOne(NakesDeviceConfig)` | `belongsTo(User)` | 1 user nakes punya 1 config device |
| `devices` | `system_statuses` | `device_id` (PK) | `hasOne(SystemStatus)` | `belongsTo(Devices)` | 1 device punya 1 status sistem |
| `devices` | `monitoring_sessions` | `device_id` | `hasOne(MonitoringSession)` | — | 1 device punya 1 session aktif (filtered `status=active`) |
| `monitoring_sessions` | `sensor_readings` | `session_id` | `hasOne(SensorReading)` | — | 1 session punya 1 reading terbaru (`latestOfMany`) |

**Total: 4 relasi One-to-One**

---

### One-to-Many (1:N)

> Satu record di tabel parent bisa berelasi dengan **banyak** record di tabel child.

| Parent | Child | Kolom FK | Eloquent (Parent) | Eloquent (Child) |
|---|---|---|---|---|
| `users` | `patients` | `nakes_id` | `hasMany(Patient)` | `belongsTo(User)` |
| `devices` | `sensor_datas` | `device_id` | `hasMany(SensorData)` | `belongsTo(Devices)` |
| `devices` | `api_keys` | `device_id` | `hasMany(ApiKey)` | `belongsTo(Devices)` |
| `devices` | `patients` | `device_id` | `hasMany(Patient)` | `belongsTo(Devices)` |
| `devices` | `medical_records` | `device_id` | `hasMany(MedicalRecord)` | `belongsTo(Devices)` |
| `devices` | `monitoring_sessions` | `device_id` | `hasMany(MonitoringSession)` | `belongsTo(Devices)` |
| `devices` | `failed_sensor_datas` | `device_id` | — | `belongsTo(Devices)` |
| `patients` | `medical_records` | `patient_id` | `hasMany(MedicalRecord)` | `belongsTo(Patient)` |
| `patients` | `monitoring_sessions` | `patient_id` | `hasMany(MonitoringSession)` | `belongsTo(Patient)` |
| `monitoring_sessions` | `sensor_readings` | `session_id` | `hasMany(SensorReading)` | `belongsTo(MonitoringSession)` |
| `users` (dokter) | `instructions` | `dokter_id` | — | `belongsTo(User)` |
| `users` (nakes) | `instructions` | `nakes_id` | — | `belongsTo(User)` |
| `users` | `instructions` | `completed_by` | — | `belongsTo(User)` |
| `devices` | `instructions` | `device_id` | — | `belongsTo(Devices)` |
| `devices` | `nakes_device_configs` | `device_id` | — | `belongsTo(Devices)` |
| `devices` | `device_monitorings` | `device_id` | — | `belongsTo(Devices)` |
| `users` (dokter) | `device_monitorings` | `dokter_id` | — | `belongsTo(User)` |
| `users` | `monitoring_sessions` | `created_by` | — | `belongsTo(User)` |
| `users` (dokter) | `monitoring_sessions` | `dokter_id` | — | `belongsTo(User)` |
| `users` | `devices` | `monitored_by` | — | `belongsTo(User)` |

**Total: 20 relasi One-to-Many**

---

### Many-to-Many (M:N)

> Banyak record di tabel A bisa berelasi dengan **banyak** record di tabel B, melalui **pivot table**.

| Tabel A | Tabel B | Pivot Table | Kolom Pivot | Eloquent (A) | Eloquent (B) |
|---|---|---|---|---|---|
| `devices` | `users` (dokter) | `device_monitorings` | `device_id`, `dokter_id` | `belongsToMany(User)` | `belongsToMany(Devices)` |

```
┌──────────┐       ┌─────────────────────┐       ┌──────────┐
│  devices │ 1   N │  device_monitorings  │ N   1 │  users   │
│          ├───────┤    (pivot table)     ├───────┤ (dokter) │
│          │       │  - device_id (FK)    │       │          │
│          │       │  - dokter_id (FK)    │       │          │
└──────────┘       └─────────────────────┘       └──────────┘
```

**Total: 1 relasi Many-to-Many**

---

### Belongs To (Referensi dari Child ke Parent)

> Semua relasi `belongsTo` di Eloquent — child menyimpan FK ke parent.

| Child Table | Child FK | Parent Table | Eloquent Method | ON DELETE |
|---|---|---|---|---|
| `devices` | `monitored_by` | `users` | `monitoredBy()` | SET NULL |
| `sensor_datas` | `device_id` | `devices` | `device()` | CASCADE |
| `system_statuses` | `device_id` | `devices` | `device()` | CASCADE |
| `api_keys` | `device_id` | `devices` | `device()` | CASCADE |
| `patients` | `device_id` | `devices` | `device()` | CASCADE |
| `patients` | `nakes_id` | `users` | `nakes()` | CASCADE |
| `medical_records` | `patient_id` | `patients` | `patient()` | CASCADE |
| `medical_records` | `device_id` | `devices` | `device()` | CASCADE |
| `instructions` | `device_id` | `devices` | `device()` | CASCADE |
| `instructions` | `dokter_id` | `users` | `dokter()` | CASCADE |
| `instructions` | `nakes_id` | `users` | `nakes()` | SET NULL |
| `instructions` | `completed_by` | `users` | — | SET NULL |
| `failed_sensor_datas` | `device_id` | `devices` | `device()` | CASCADE |
| `nakes_device_configs` | `user_id` | `users` | `user()` | CASCADE |
| `nakes_device_configs` | `device_id` | `devices` | `device()` | CASCADE |
| `device_monitorings` | `device_id` | `devices` | `device()` | CASCADE |
| `device_monitorings` | `dokter_id` | `users` | `dokter()` | CASCADE |
| `monitoring_sessions` | `device_id` | `devices` | `device()` | CASCADE |
| `monitoring_sessions` | `patient_id` | `patients` | `patient()` | SET NULL |
| `monitoring_sessions` | `created_by` | `users` | `creator()` | CASCADE |
| `monitoring_sessions` | `dokter_id` | `users` | `dokter()` | SET NULL |
| `sensor_readings` | `session_id` | `monitoring_sessions` | `session()` | CASCADE |

**Total: 22 relasi Belongs To**

---

### Soft Reference (Tanpa FK Constraint)

> Relasi yang tidak didefinisikan secara formal di database, tapi direferensikan secara logis.

| Tabel | Kolom | Referensi ke | Alasan tidak pakai FK |
|---|---|---|---|
| `activity_log` | `device_id` | `devices.device_id` | Log harus tetap ada walau device dihapus |

**Total: 1 soft reference**

---

### Ringkasan Total Relasi

| Tipe Relasi | Jumlah | Keterangan |
|---|---|---|
| **One-to-One (1:1)** | 4 | 1 parent → 1 child (UNIQUE FK atau filtered) |
| **One-to-Many (1:N)** | 20 | 1 parent → banyak child |
| **Many-to-Many (M:N)** | 1 | Melalui pivot table `device_monitorings` |
| **Soft Reference** | 1 | Tanpa FK constraint |
| **Total** | **26** | |

---

### Diagram Relasi (Simplified)

```
                              ┌───────────────────┐
                              │       users       │
                              │───────────────────│
                              │ PK  id            │
                              │     role          │
                              └──────┬──┬──┬──┬───┘
                        ┌────────────┘  │  │  │
                        │               │  │  │
          ┌─────────────┼───────────────┘  │  └──────────────────────┐
          │             │                  │                         │
          ▼             ▼                  ▼                         ▼
┌─────────────────┐  ┌──────────┐  ┌──────────────┐  ┌──────────────────────┐
│nakes_device_    │  │ patients │  │ instructions │  │  monitoring_sessions │
│    configs      │  │──────────│  │──────────────│  │──────────────────────│
│─────────────────│  │ FK device│  │ FK device    │  │ FK device            │
│ FK user (UQ)    │  │ FK nakes │  │ FK dokter    │  │ FK patient (opt)     │
│ FK device       │  └────┬──┬──┘  │ FK nakes     │  │ FK created_by        │
└────────┬────────┘       │  │     │ FK completed │  │ FK dokter (opt)      │
         │                │  │     └──────┬───────┘  └─────────┬────────────┘
         │                │  │            │                     │
         └────────────────┼──┼────────────┼─────────────────────┘
                          │  │            │
                          ▼  ▼            ▼
                   ┌──────────────┐  ┌───────────────┐
                   │  medical_    │  │ sensor_readings│
                   │   records    │  │───────────────│
                   │──────────────│  │ FK session_id │
                   │ FK patient   │  └───────────────┘
                   │ FK device    │
                   └──────┬───────┘
                          │
                          ▼
                   ┌──────────────────────────────────────────────┐
                   │                  devices                     │
                   │──────────────────────────────────────────────│
                   │ PK  device_id (string)                      │
                   │ FK  monitored_by → users (opt)              │
                   │     ml_prediction, ml_condition, ...        │
                   └──┬───┬───┬───┬───┬───┬───┬───┬──────────────┘
                      │   │   │   │   │   │   │   │
                      ▼   ▼   ▼   ▼   ▼   ▼   ▼   ▼
              sensor  system api  failed  device_  activity
              _datas  _status keys sensor _monitor  _log
                                     datas   ings   (soft ref)
                              ┌─────────────────────┐
                              │  device_monitorings  │ ← pivot (M:N)
                              │─────────────────────│   devices ↔ users
                              │ FK device_id         │
                              │ FK dokter_id         │
                              └─────────────────────┘
```
