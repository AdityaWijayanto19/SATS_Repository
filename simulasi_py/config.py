# Konfigurasi Simulasi Perangkat SATS

BASE_URL = "http://localhost:8000/api"

# File konfigurasi device (isi API key dari dashboard superadmin)
DEVICES_FILE = "devices.json"

# Interval pengiriman data default (detik)
DEFAULT_INTERVAL = 2

# Kategori usia pasien (dikirim bersama data sensor untuk ML API)
KATEGORI_USIA = ["Balita", "Anak-anak", "Dewasa", "Lansia"]

# =============================================================================
# KONFIGURASI VITAL SIGNS PER KATEGORI USIA
# Sumber: standar medis pediatri & dewasa
# =============================================================================

# Range normal vital signs per kategori usia
NORMAL_RANGES = {
    "Balita": {
        "heart_rate":    {"min": 80,  "max": 130},
        "spo2":          {"min": 95,  "max": 100},
        "temperature":   {"min": 36.5, "max": 37.5},
    },
    "Anak-anak": {
        "heart_rate":    {"min": 70,  "max": 110},
        "spo2":          {"min": 95,  "max": 100},
        "temperature":   {"min": 36.5, "max": 37.5},
    },
    "Dewasa": {
        "heart_rate":    {"min": 60,  "max": 100},
        "spo2":          {"min": 95,  "max": 100},
        "temperature":   {"min": 36.5, "max": 37.2},
    },
    "Lansia": {
        "heart_rate":    {"min": 60,  "max": 100},
        "spo2":          {"min": 93,  "max": 100},  # 93-94% masih normal untuk lansia dengan PPOK
        "temperature":   {"min": 36.0, "max": 36.8},  # baseline lebih rendah
    },
}

# Threshold WARNING (di luar rentang normal tapi belum kritis)
WARNING_THRESHOLDS = {
    "Balita": {
        "heart_rate":    {"low": 70,  "high": 150},
        "spo2":          {"low": 90},
        "temperature":   {"high": 38.0},
    },
    "Anak-anak": {
        "heart_rate":    {"low": 60,  "high": 130},
        "spo2":          {"low": 90},
        "temperature":   {"high": 38.0},
    },
    "Dewasa": {
        "heart_rate":    {"low": 50,  "high": 120},
        "spo2":          {"low": 90},
        "temperature":   {"high": 38.0},
    },
    "Lansia": {
        "heart_rate":    {"low": 50,  "high": 120},
        "spo2":          {"low": 90},
        "temperature":   {"high": 37.5},  # lansia lebih sensitif
    },
}

# Threshold CRITICAL (bahaya, perlu intervensi segera)
CRITICAL_THRESHOLDS = {
    "Balita": {
        "heart_rate":    {"low": 60,  "high": 170},
        "spo2":          {"low": 85},
        "temperature":   {"high": 39.0},
    },
    "Anak-anak": {
        "heart_rate":    {"low": 50,  "high": 150},
        "spo2":          {"low": 85},
        "temperature":   {"high": 39.0},
    },
    "Dewasa": {
        "heart_rate":    {"low": 40,  "high": 140},
        "spo2":          {"low": 85},
        "temperature":   {"high": 39.0},
    },
    "Lansia": {
        "heart_rate":    {"low": 40,  "high": 130},
        "spo2":          {"low": 85},
        "temperature":   {"high": 38.5},  # lansia lebih sensitif
    },
}

# =============================================================================
# KONFIGURASI LAMA (untuk backward compatibility generate_sensor_data)
# =============================================================================

# Range normal default (Dewasa) — dipakai untuk generate data random
SENSOR_CONFIG = {
    "heart_rate": {"min": 60, "max": 100, "unit": "bpm"},
    "temperature": {"min": 36.0, "max": 37.5, "unit": "C"},
    "spo2": {"min": 95, "max": 100, "unit": "%"},
}

# Threshold default (Dewasa) — dipakai untuk generate data warning/critical
THRESHOLDS = {
    "heart_rate": {"warning_low": 50, "warning_high": 120, "critical_low": 40, "critical_high": 140},
    "temperature": {"warning_high": 38.0, "critical_high": 39.0},
    "spo2": {"warning_low": 90, "critical_low": 85},
}

# Distribusi data per profile
# Format: (prob_normal, prob_warning, prob_critical)
PROFILES = {
    "normal":   (0.95, 0.03, 0.02),
    "warning":  (0.20, 0.60, 0.20),
    "critical": (0.10, 0.20, 0.70),
}
