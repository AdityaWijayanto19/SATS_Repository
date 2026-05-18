# Konfigurasi Simulasi Perangkat SATS
# Isi DEVICE_ID dan API_KEY setelah superadmin mendaftarkan alat di dashboard

BASE_URL = "http://localhost:8000/api"

# Ganti dengan data dari dashboard superadmin setelah tambah alat
DEVICE_ID = "Device_02"
API_KEY = "sats_hRmOgQ0d0lFE2HbyQLXASWPdcXKBvdQ3"

# Interval pengiriman data (detik)
SEND_INTERVAL = 1

# Konfigurasi sensor (range normal)
SENSOR_CONFIG = {
    "heart_rate": {"min": 60, "max": 100, "unit": "bpm"},
    "spo2": {"min": 95, "max": 100, "unit": "%"},
    "temperature": {"min": 36.0, "max": 37.5, "unit": "C"},
}

# Threshold untuk klasifikasi status
THRESHOLDS = {
    "heart_rate": {"warning_low": 50, "warning_high": 120, "critical_low": 40, "critical_high": 140},
    "spo2": {"warning_low": 90, "critical_low": 85},
    "temperature": {"warning_high": 38.0, "critical_high": 39.0},
}
