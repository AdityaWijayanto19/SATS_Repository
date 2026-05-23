# Konfigurasi Simulasi Perangkat SATS

BASE_URL = "http://localhost:8000/api"

# File konfigurasi device (isi API key dari dashboard superadmin)
DEVICES_FILE = "devices.json"

# Interval pengiriman data default (detik)
DEFAULT_INTERVAL = 2

# Konfigurasi sensor (range normal) — 3 vital signs untuk ML API
# Urutan: HR, Temp, SpO2 (sesuai API_INTEGRATION.md)
SENSOR_CONFIG = {
    "heart_rate": {"min": 60, "max": 100, "unit": "bpm"},
    "temperature": {"min": 36.0, "max": 37.5, "unit": "C"},
    "spo2": {"min": 95, "max": 100, "unit": "%"},
}

# Threshold untuk klasifikasi status
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
