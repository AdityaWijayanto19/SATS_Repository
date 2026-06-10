# MASTER_ML.md — Patient Deterioration Monitoring System

Dokumentasi lengkap untuk model Machine Learning sistem monitoring penurunan kondisi pasien.

---

## Daftar Isi

1. [Overview](#1-overview)
2. [Kategori Usia](#2-kategori-usia)
3. [Arsitektur Sistem](#3-arsitektur-sistem)
4. [Dataset](#4-dataset)
5. [Pipeline Data Processing](#5-pipeline-data-processing)
6. [Model Machine Learning](#6-model-machine-learning)
7. [Rule-Based Classification](#7-rule-based-classification)
8. [Real-Time Monitoring](#8-real-time-monitoring)
9. [Web App (Gradio)](#9-web-app-gradio)
10. [Cara Menjalankan](#10-cara-menjalankan)
11. [File Reference](#11-file-reference)

---

## 1. Overview

Sistem ini memonitor vital signs pasien secara real-time dan memprediksi **arah tren kondisi pasien** dalam 5 menit ke depan. Dirancang untuk simulasi monitor pasien di ambulans/ICU.

**Vital Signs yang dipantau:**
- Heart Rate (HR)
- Temperature
- Oxygen Saturation (SpO2)

**Parameter tambahan:**
- **Kategori Usia** — mempengaruhi threshold kritis dan prediksi model

**Output sistem:**
- Prediksi arah tren: `MEMBAIK` / `STABIL` / `MEMBURUK` + persentase confidence
- Klasifikasi kondisi saat ini: `NORMAL` / `WARNING` / `CRITICAL` (age-specific)
- Alert level: `Low Risk` / `Medium Risk` / `High Risk`

---

## 2. Kategori Usia

| Kategori   | Rentang Usia | HR Baseline | SpO2 Baseline | Temp Baseline |
|------------|-------------|-------------|---------------|---------------|
| Balita     | 1 - 5       | 100 bpm     | 97.5%         | 36.8°C        |
| Anak-anak  | 6 - 11      | 85 bpm      | 97.5%         | 36.7°C        |
| Dewasa     | 12 - 65     | 75 bpm      | 97.0%         | 36.7°C        |
| Lansia     | 65++        | 72 bpm      | 95.5%         | 36.5°C        |

**Mengapa usia penting?**
- HR 110 bpm → **normal** untuk Balita, tapi **WARNING** untuk Lansia
- SpO2 91% → **WARNING** untuk Dewasa, tapi **CRITICAL** untuk Lansia
- Model belajar pola vital yang berbeda per kategori usia

---

## 3. Arsitektur Sistem

```
┌─────────────────────────────────────────────────────┐
│                   DATA PIPELINE                      │
│                                                      │
│  generate_dataset.py  ──► vitals.csv                 │
│  (sintetis + age_group)     (1000 pasien × 24 langkah)│
│      │                                               │
│      ▼                                               │
│  create_labels.py  ──► vitals_with_labels.csv        │
│  (trend-based + age-specific)   (3-class label)      │
│      │                                               │
│      ▼                                               │
│  create_timeseries_dataset.py                        │
│  ──► X_timeseries.npy (n, 5, 3)                     │
│  ──► y_timeseries.npy                                │
│  ──► age_groups_timeseries.npy                       │
│      │                                               │
│      ▼                                               │
│  train_timeseries_model.py                           │
│  ──► patient_deterioration_model.pkl (19 fitur)      │
│  ──► scaler.pkl (fit 15 vital only)                  │
└─────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────┐
│                 INFERENCE / USE                       │
│                                                      │
│  app.py                   (Gradio web app, 2 tab)    │
│  predict_patient.py       (single prediction)        │
│  realtime_monitoring.py   (streaming simulation)     │
└─────────────────────────────────────────────────────┘
```

---

## 4. Dataset

### 4.1 Raw Dataset — `vitals.csv`

| Kolom               | Tipe   | Keterangan                     |
|---------------------|--------|--------------------------------|
| `patient_id`        | string | ID pasien (P0000–P0999)        |
| `age`               | int    | Usia pasien                    |
| `age_group`         | string | Kategori usia                  |
| `time_point`        | int    | Time step (0–23 per pasien)    |
| `heart_rate`        | float  | Heart rate (bpm)               |
| `temperature`       | float  | Suhu tubuh (°C)                |
| `oxygen_saturation` | float  | Saturasi oksigen (%)           |

**Statistik:**
- 1.000 pasien unik
- 24 time steps per pasien
- Total: 24.000 records

**Skenario Pasien (di-generate oleh `generate_dataset.py`):**

| Skenario              | Jumlah | Deskripsi                                    |
|-----------------------|--------|----------------------------------------------|
| Stable                | 350    | Vital signs tetap di rentang normal          |
| Deteriorating         | 200    | Normal → gradual ke critical                 |
| Recovering            | 150    | Critical → recovery ke normal                |
| Fluctuating           | 150    | Kondisi naik-turun (unstable)                |
| Critical Worsen       | 150    | Abnormal → makin parah                       |

**Distribusi usia:**
- Balita: ~16.6%
- Anak-anak: ~14.1%
- Dewasa: ~49.6%
- Lansia: ~19.7%

### 4.2 Labeled Dataset — `vitals_with_labels.csv`

Ditambahkan kolom:
- `critical_now` (0/1) — apakah pasien kritis saat ini (age-specific threshold)
- `condition_trend` (0/1/2) — **label utama multi-class**
  - `0` = Improving (Membaik)
  - `1` = Stable (Stabil)
  - `2` = Deteriorating (Memburuk)
- `future_deterioration` (0/1) — backward compat (1 jika condition_trend == 2)

**Distribusi label (trend-based):**
- Improving: ~22.4%
- Stable: ~40.0%
- Deteriorating: ~37.6%

### 4.3 Time-Series Dataset

- `X_timeseries.npy`: shape `(n_samples, 5, 3)` — sliding window 5 time steps, 3 vital signs
- `y_timeseries.npy`: shape `(n_samples,)` — label condition_trend
- `patient_ids_timeseries.npy`: shape `(n_samples,)` — patient ID per sample
- `age_groups_timeseries.npy`: shape `(n_samples,)` — age group per sample

---

## 5. Pipeline Data Processing

### Step 1: Dataset Generation (`generate_dataset.py`)

- 1000 pasien, 24 time steps
- Setiap pasien di-assign ke kategori usia (Balita/Anak-anak/Dewasa/Lansia)
- Baseline vital signs **per kategori usia** (HR, SpO2, Temp berbeda)
- 5 skenario: stable, deteriorating, recovering, fluctuating, critical_worsen

### Step 2: Labeling (`create_labels.py`)

**Age-specific critical thresholds:**

| Kategori   | SpO2 Critical | HR Critical | Temp Critical |
|------------|---------------|-------------|---------------|
| Balita     | < 92%         | > 160 bpm   | > 39.0°C      |
| Anak-anak  | < 92%         | > 140 bpm   | > 39.0°C      |
| Dewasa     | < 92%         | > 130 bpm   | > 39.0°C      |
| Lansia     | < 90%         | > 120 bpm   | > 38.5°C      |

**Trend-based labeling** (bukan hitung critical count):

| Label | Kondisi | Kriteria |
|-------|---------|----------|
| 0 | Improving | HR turun >8 bpm ATAU SpO2 naik >2% ATAU Temp turun >0.4°C |
| 1 | Stable | Tidak ada perubahan signifikan |
| 2 | Deteriorating | HR naik >8 bpm ATAU SpO2 turun >2% ATAU Temp naik >0.4°C |

Perbandingan dilakukan antara rata-rata vital signs di window masa lalu (5 menit) vs window masa depan (5 menit).

### Step 3: Time-Series Dataset (`create_timeseries_dataset.py`)

- Sliding window: 5 time steps
- Input shape per sample: `(5, 3)` → 5 menit × 3 vital signs (HR, Temp, SpO2)
- Label diambil dari time point setelah window
- Disimpan sebagai `.npy` untuk efisiensi

---

## 6. Model Machine Learning

### Model Utama (`train_timeseries_model.py`)

**Algoritma:** Random Forest Classifier

| Parameter          | Value      |
|--------------------|------------|
| `n_estimators`     | 1000       |
| `max_depth`        | 25         |
| `min_samples_leaf` | 2          |
| `class_weight`     | "balanced" |
| `random_state`     | 42         |

**Preprocessing:**
1. Flatten time-series: `(n, 5, 3)` → `(n, 15)`
2. One-hot encode age_group: `(n,)` → `(n, 4)` [is_balita, is_anak, is_dewasa, is_lansia]
3. `StandardScaler` **hanya pada 15 fitur vital** (one-hot tidak di-scale)
4. Concatenate: `(n, 15)` scaled + `(n, 4)` one-hot = `(n, 19)`
5. **Patient-based split**: 80/20, split berdasarkan patient_id

**Input model: 19 fitur:**
```
[HR1, Temp1, SpO2, HR2, Temp2, SpO2, ..., HR5, Temp5, SpO2, is_balita, is_anak, is_dewasa, is_lansia]
 ←──────────── 15 scaled vitals ────────────→  ←──────────── 4 one-hot (not scaled) ────────────→
```

**Evaluasi:**

| Metric          | Score           |
|-----------------|-----------------|
| Accuracy        | 63.4%           |
| CV Macro F1     | 0.6356 ± 0.0083 |
| Test Macro F1   | 0.6311          |
| Test Weighted F1| 0.6366          |

Per kelas:
| Kelas            | Precision | Recall | F1   |
|------------------|-----------|--------|------|
| Improving (0)    | 0.62      | 0.59   | 0.61 |
| Stable (1)       | 0.54      | 0.61   | 0.57 |
| Deteriorating (2)| 0.75      | 0.68   | 0.71 |

**Output:** Model dan scaler disimpan sebagai:
- `patient_deterioration_model.pkl`
- `scaler.pkl`

### Mengapa Akurasi Lebih Rendah dari Sebelumnya?

Labeling sebelumnya menggunakan hitung `critical_count` yang menghasilkan label salah (pasien kritis tetap dilabel "Membaik"). Akurasi tinggi (92%) tapi **salah secara semantik**.

Labeling baru menggunakan **trend-based** yang lebih akurat secara medis. Akurasi lebih rendah (63%) tapi **prediksi benar** — pasien yang memburuk benar diprediksi "Memburuk".

---

## 7. Rule-Based Classification

### Klasifikasi Kondisi Perangkat (`classify_status()`)

Klasifikasi kondisi pasien dilakukan **di perangkat** (bukan di ML API) menggunakan rule-based threshold per kategori usia. Threshold berdasarkan standar medis pediatri & dewasa.

**Rentang Normal per Kategori Usia:**

| Kategori   | HR (bpm)   | SpO2 (%)   | Suhu (°C)    |
|------------|------------|------------|--------------|
| Balita     | 80 – 130   | 95 – 100   | 36.5 – 37.5 |
| Anak-anak  | 70 – 110   | 95 – 100   | 36.5 – 37.5 |
| Dewasa     | 60 – 100   | 95 – 100   | 36.5 – 37.2 |
| Lansia     | 60 – 100   | 93 – 100   | 36.0 – 36.8 |

> **Catatan Lansia:** SpO2 93-94% masih normal untuk lansia dengan PPOK. Suhu baseline lebih rendah.

**Threshold WARNING (di luar normal, belum kritis):**

| Kategori   | HR (bpm)       | SpO2 (%) | Suhu (°C)  |
|------------|----------------|----------|------------|
| Balita     | < 70 atau > 150 | < 90     | ≥ 38.0    |
| Anak-anak  | < 60 atau > 130 | < 90     | ≥ 38.0    |
| Dewasa     | < 50 atau > 120 | < 90     | ≥ 38.0    |
| Lansia     | < 50 atau > 120 | < 90     | ≥ 37.5    |

**Threshold CRITICAL (bahaya, perlu intervensi segera):**

| Kategori   | HR (bpm)       | SpO2 (%) | Suhu (°C)  |
|------------|----------------|----------|------------|
| Balita     | < 60 atau > 170 | < 85     | ≥ 39.0    |
| Anak-anak  | < 50 atau > 150 | < 85     | ≥ 39.0    |
| Dewasa     | < 40 atau > 140 | < 85     | ≥ 39.0    |
| Lansia     | < 40 atau > 130 | < 85     | ≥ 38.5    |

**Prioritas klasifikasi:** CRITICAL > WARNING > normal (cek CRITICAL dulu)

### Risk Alert (`risk_level()`)

Berdasarkan probabilitas kelas "Memburuk":

```
Low Risk    → prob Memburuk < 0.3  (< 30%)
Medium Risk → prob Memburuk 0.3–0.6  (30–60%)
High Risk   → prob Memburuk > 0.6  (> 60%)
```

---

## 8. Real-Time Monitoring

### Simulasi Sensor (`realtime_monitoring.py`)

Sistem menyimulasikan data vital signs yang masuk setiap 2 detik.

**Fase simulasi per menit:**

| Menit    | Fase        | Distribusi Vital Signs              |
|----------|-------------|--------------------------------------|
| 1–8      | NORMAL      | HR~80, Temp~36.7, SpO2~97           |
| 9–16     | WARNING     | HR~100, Temp~37.5, SpO2~94          |
| 17–24    | CRITICAL    | HR~125, Temp~38.5, SpO2~88          |
| 25–32    | WARNING     | HR~105, Temp~37.6, SpO2~93          |
| 33+      | RECOVERY    | HR~88, Temp~37.0, SpO2~96           |

**Buffer:** `deque(maxlen=5)` — menyimpan 5 data terakhir sebagai sliding window.

---

## 9. Web App (Gradio)

### Tab 1: Manual Input

- User pilih **Kategori Usia** (dropdown)
- User input 5 data vital signs (HR, Temp, SpO2 per menit)
- Klik **Prediksi** → hasil muncul di kolom output

### Tab 2: Real-time Simulation

- User pilih **Kategori Usia** + **Senario** (Normal/Warning/Critical/Deteriorating)
- Klik **Start Simulasi** → data auto-generate setiap 2 detik
- Prediksi update otomatis
- Klik **Stop** untuk berhenti

**Senario:**

| Senario      | Pola                                              |
|-------------|---------------------------------------------------|
| Normal      | Vital stabil di baseline, noise kecil             |
| Warning     | HR naik perlahan, SpO2 turun perlahan             |
| Critical    | Mulai sedikit naik → deteriorasi cepat ke kritis  |
| Deteriorating | Normal → Warning → Critical (progresif ~24 menit) |

---

## 10. Cara Menjalankan

### Prerequisites

```
pip install numpy pandas scikit-learn joblib gradio
```

### Web App

```
python app.py
# Buka http://localhost:7860
```

### Real-Time Monitoring (Terminal)

```
python realtime_monitoring.py
# Tekan Ctrl+C untuk berhenti
```

### Prediksi Satu Kali

```
python predict_patient.py
```

### Retrain Model

```
python generate_dataset.py
python create_labels.py
python create_timeseries_dataset.py
python train_timeseries_model.py
```

---

## 11. File Reference

| File                          | Fungsi                                         |
|-------------------------------|------------------------------------------------|
| `generate_dataset.py`         | Generator data sintetis + age group             |
| `create_labels.py`            | Trend-based labeling + age-specific thresholds  |
| `feature_engineering.py`      | Fitur engineering (baseline)                    |
| `create_timeseries_dataset.py`| Dataset time-series + age_groups (.npy)         |
| `train_model.py`              | Training baseline (LogReg + RF)                 |
| `train_timeseries_model.py`   | Training model utama (RF, 19 fitur)             |
| `predict_patient.py`          | Prediksi satu sample                            |
| `realtime_monitoring.py`      | Simulasi monitoring real-time                   |
| `app.py`                      | Gradio web app (Manual + Real-time Simulation)  |
| `vitals.csv`                  | Dataset mentah                                  |
| `vitals_with_labels.csv`      | Dataset dengan label                            |
| `X_timeseries.npy`            | Input time-series                               |
| `y_timeseries.npy`            | Label time-series                               |
| `age_groups_timeseries.npy`   | Age groups per sample                           |
| `patient_deterioration_model.pkl` | Model terlatih                             |
| `scaler.pkl`                  | StandardScaler (15 vital only)                  |
| `DEPLOY.md`                   | Panduan deploy Hugging Face                     |
| `API_INTEGRATION.md`          | Panduan integrasi API                           |
