"""
Simulasi Perangkat SATS (Smart Ambulance Telemedicine System)

Script ini mensimulasikan alur perangkat IoT:
1. Autentikasi dengan API key
2. Kirim status sistem (battery, signal)
3. Kirim data sensor secara berkala (heart rate, SpO2, temperature)
4. Berhenti saat dihentikan (Ctrl+C)

Usage:
    python simulator.py
    python simulator.py --device DEVICE_01 --key your_api_key_here
"""

import argparse
import json
import random
import signal
import sys
import time
from datetime import datetime

import requests

from config import BASE_URL, DEVICE_ID, API_KEY, SEND_INTERVAL, SENSOR_CONFIG, THRESHOLDS


class SATSSimulator:
    def __init__(self, device_id: str, api_key: str):
        self.device_id = device_id
        self.api_key = api_key
        self.base_url = BASE_URL
        self.running = False
        self.data_count = 0

        self.headers = {
            "X-API-Key": self.api_key,
            "Content-Type": "application/json",
            "Accept": "application/json",
        }

    def log(self, message: str, level: str = "INFO"):
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{timestamp}] [{level}] {message}")

    def authenticate(self) -> bool:
        """Autentikasi perangkat dengan API key"""
        self.log(f"Mengautentikasi perangkat {self.device_id}...")
        try:
            url = f"{self.base_url}/device/{self.device_id}/authenticate"
            response = requests.post(url, headers=self.headers, json={
                "device_id": self.device_id,
            })

            if response.status_code == 200:
                self.log("Autentikasi berhasil!", "SUCCESS")
                return True
            else:
                self.log(f"Autentikasi gagal: {response.status_code} - {response.text}", "ERROR")
                return False
        except requests.ConnectionError:
            self.log("Tidak dapat terhubung ke server. Pastikan Laravel berjalan.", "ERROR")
            return False

    def send_system_status(self) -> bool:
        """Kirim status sistem (battery, signal)"""
        battery = random.randint(60, 100)
        signal_strength = random.randint(50, 100)

        self.log(f"Mengirim status sistem: battery={battery}%, signal={signal_strength}%")
        try:
            url = f"{self.base_url}/device/{self.device_id}/system-status"
            response = requests.post(url, headers=self.headers, json={
                "monitoring_status": "active",
                "battery_level": battery,
                "signal_strength": signal_strength,
            })

            if response.status_code == 201:
                self.log("Status sistem terkirim", "SUCCESS")
                return True
            else:
                self.log(f"Gagal kirim status: {response.status_code}", "ERROR")
                return False
        except Exception as e:
            self.log(f"Error: {e}", "ERROR")
            return False

    def generate_sensor_data(self) -> dict:
        """Generate data sensor realistis"""
        cfg = SENSOR_CONFIG

        # 90% normal, 7% warning, 3% critical
        roll = random.random()
        if roll < 0.90:
            # Normal
            heart_rate = random.randint(cfg["heart_rate"]["min"], cfg["heart_rate"]["max"])
            spo2 = random.randint(cfg["spo2"]["min"], cfg["spo2"]["max"])
            temperature = round(random.uniform(cfg["temperature"]["min"], cfg["temperature"]["max"]), 1)
            status = "normal"
        elif roll < 0.97:
            # Warning
            heart_rate = random.choice([
                random.randint(45, THRESHOLDS["heart_rate"]["warning_low"]),
                random.randint(THRESHOLDS["heart_rate"]["warning_high"], THRESHOLDS["heart_rate"]["critical_high"]),
            ])
            spo2 = random.randint(THRESHOLDS["spo2"]["critical_low"], THRESHOLDS["spo2"]["warning_low"])
            temperature = round(random.uniform(THRESHOLDS["temperature"]["warning_high"], THRESHOLDS["temperature"]["critical_high"]), 1)
            status = "warning"
        else:
            # Critical
            heart_rate = random.choice([
                random.randint(30, THRESHOLDS["heart_rate"]["critical_low"]),
                random.randint(THRESHOLDS["heart_rate"]["critical_high"], 180),
            ])
            spo2 = random.randint(70, THRESHOLDS["spo2"]["critical_low"] - 1)
            temperature = round(random.uniform(THRESHOLDS["temperature"]["critical_high"], 41.0), 1)
            status = "critical"

        return {
            "heart_rate": heart_rate,
            "spo2": spo2,
            "temperature": temperature,
            "status": status,
        }

    def send_sensor_data(self) -> bool:
        """Kirim data sensor ke server"""
        data = self.generate_sensor_data()
        self.data_count += 1

        self.log(
            f"[#{self.data_count}] HR={data['heart_rate']}bpm | "
            f"SpO2={data['spo2']}% | Temp={data['temperature']}C | "
            f"Status={data['status'].upper()}"
        )

        try:
            url = f"{self.base_url}/device/{self.device_id}/sensor-data"
            response = requests.post(url, headers=self.headers, json=data)

            if response.status_code == 201:
                return True
            elif response.status_code == 401:
                self.log("API key tidak valid atau expired!", "ERROR")
                self.running = False
                return False
            else:
                self.log(f"Gagal kirim data: {response.status_code} - {response.text}", "ERROR")
                return False
        except Exception as e:
            self.log(f"Error: {e}", "ERROR")
            return False

    def run(self):
        """Jalankan simulasi utama"""
        print("=" * 60)
        print("  SIMULASI PERANGKAT SATS")
        print(f"  Device: {self.device_id}")
        print(f"  Server: {self.base_url}")
        print(f"  Interval: {SEND_INTERVAL}s")
        print("=" * 60)
        print()

        # Step 1: Autentikasi
        if not self.authenticate():
            print("\nGagal autentikasi. Pastikan:")
            print(f"  1. Device '{self.device_id}' terdaftar di dashboard superadmin")
            print(f"  2. API key benar")
            print(f"  3. Server Laravel berjalan di {self.base_url}")
            sys.exit(1)

        # Step 2: Kirim status sistem
        self.send_system_status()

        # Step 3: Loop kirim data sensor
        self.running = True
        print()
        self.log(f"Mengirim data setiap {SEND_INTERVAL} detik... (Ctrl+C untuk berhenti)")
        print("-" * 60)

        try:
            while self.running:
                self.send_sensor_data()
                time.sleep(SEND_INTERVAL)
        except KeyboardInterrupt:
            print()
            self.log("Simulasi dihentikan oleh user", "WARN")
            self.log(f"Total data terkirim: {self.data_count}")

        print()
        print("=" * 60)
        print("  Simulasi selesai.")
        print("=" * 60)


def main():
    parser = argparse.ArgumentParser(description="Simulasi Perangkat SATS")
    parser.add_argument("--device", default=DEVICE_ID, help="Device ID")
    parser.add_argument("--key", default=API_KEY, help="API Key")
    parser.add_argument("--interval", type=int, default=SEND_INTERVAL, help="Interval pengiriman (detik)")
    parser.add_argument("--url", default=BASE_URL, help="Base URL API")

    args = parser.parse_args()

    simulator = SATSSimulator(args.device, args.key)
    simulator.base_url = args.url
    simulator.run()


if __name__ == "__main__":
    main()
