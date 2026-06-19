"""
Simulasi Perangkat SATS (Smart Ambulance Telemedicine System)

Script ini mensimulasikan beberapa perangkat IoT secara paralel:
1. Baca konfigurasi dari devices.json
2. Setiap device: autentikasi, cek status, kirim data sensor
3. Device harus online (diaktifkan nakes dari dashboard) sebelum kirim data
4. Berhenti saat dihentikan (Ctrl+C)

Usage:
    python simulator.py
    python simulator.py --url http://localhost:8000/api
"""

import json
import random
import secrets
import signal
import sys
import threading
import time
from datetime import datetime

import requests

# Interval cek status oleh monitor thread (detik)
STATUS_CHECK_INTERVAL = 1

from config import (
    BASE_URL, DEFAULT_INTERVAL, SENSOR_CONFIG, THRESHOLDS, PROFILES,
    DEVICES_FILE, KATEGORI_USIA, NORMAL_RANGES, WARNING_THRESHOLDS, CRITICAL_THRESHOLDS,
)


class SATSSimulator:
    def __init__(self, device_id: str, api_key: str, profile: str = "normal", interval: int = DEFAULT_INTERVAL, kategori_usia: str = "Dewasa"):
        self.device_id = device_id
        self.api_key = api_key
        self.profile = profile
        self.interval = interval
        self.kategori_usia = kategori_usia
        self.base_url = BASE_URL
        self.stop_event = threading.Event()
        self.data_count = 0

        self.headers = {
            "X-API-Key": self.api_key,
            "Content-Type": "application/json",
            "Accept": "application/json",
        }

    def log(self, message: str, level: str = "INFO"):
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        print(f"[{timestamp}] [{self.device_id}] [{level}] {message}")

    def authenticate(self) -> bool:
        self.log(f"Mengautentikasi...")
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
            self.log("Tidak dapat terhubung ke server.", "ERROR")
            return False

    def check_status(self) -> str:
        """Cek status perangkat (online/offline) dari server"""
        try:
            url = f"{self.base_url}/device/{self.device_id}/status"
            response = requests.get(url, timeout=5)
            if response.status_code == 200:
                data = response.json()
                return data.get("data", {}).get("status", "offline")
        except Exception:
            pass
        return "offline"

    def wait_for_online(self):
        """Tunggu sampai nakes mengaktifkan perangkat dari dashboard"""
        self.log("Perangkat offline. Menunggu nakes mengaktifkan...", "WARN")
        while True:
            time.sleep(3)
            status = self.check_status()
            if status == "online":
                self.log("Perangkat diaktifkan oleh nakes!", "SUCCESS")
                return

    def send_system_status(self) -> bool:
        battery = random.randint(60, 100)
        signal_strength = random.randint(50, 100)

        try:
            url = f"{self.base_url}/device/{self.device_id}/system-status"
            headers = {**self.headers, "Idempotency-Key": self.generate_idempotency_key()}
            response = requests.post(url, headers=headers, json={
                "monitoring_status": "active",
                "battery_level": battery,
                "signal_strength": signal_strength,
            })

            if response.status_code in (200, 201, 202):
                self.log(f"Status sistem: battery={battery}%, signal={signal_strength}%", "SUCCESS")
                return True
            else:
                self.log(f"Gagal kirim status: {response.status_code} - {response.text}", "ERROR")
                return False
        except Exception as e:
            self.log(f"Error: {e}", "ERROR")
            return False

    def classify_status(self, heart_rate: float, temperature: float, spo2: int) -> str:
        """
        Klasifikasi kondisi pasien berdasarkan rule-based threshold per kategori usia.

        Returns: "normal", "warning", atau "critical"
        """
        usia = self.kategori_usia
        normal = NORMAL_RANGES.get(usia, NORMAL_RANGES["Dewasa"])
        warning = WARNING_THRESHOLDS.get(usia, WARNING_THRESHOLDS["Dewasa"])
        critical = CRITICAL_THRESHOLDS.get(usia, CRITICAL_THRESHOLDS["Dewasa"])

        # Cek CRITICAL dulu (prioritas tertinggi)
        if (heart_rate <= critical["heart_rate"]["low"] or heart_rate >= critical["heart_rate"]["high"]
                or spo2 <= critical["spo2"]["low"]
                or temperature >= critical["temperature"]["high"]):
            return "critical"

        # Cek WARNING
        if (heart_rate <= warning["heart_rate"]["low"] or heart_rate >= warning["heart_rate"]["high"]
                or spo2 <= warning["spo2"]["low"]
                or temperature >= warning["temperature"]["high"]):
            return "warning"

        # Normal
        return "normal"

    def generate_sensor_data(self) -> dict:
        cfg = SENSOR_CONFIG
        prob_normal, prob_warning, prob_critical = PROFILES.get(self.profile, PROFILES["normal"])

        roll = random.random()
        if roll < prob_normal:
            heart_rate = random.randint(cfg["heart_rate"]["min"], cfg["heart_rate"]["max"])
            temperature = round(random.uniform(cfg["temperature"]["min"], cfg["temperature"]["max"]), 1)
            spo2 = random.randint(cfg["spo2"]["min"], cfg["spo2"]["max"])
        elif roll < prob_normal + prob_warning:
            heart_rate = random.choice([
                random.randint(45, THRESHOLDS["heart_rate"]["warning_low"]),
                random.randint(THRESHOLDS["heart_rate"]["warning_high"], THRESHOLDS["heart_rate"]["critical_high"]),
            ])
            temperature = round(random.uniform(THRESHOLDS["temperature"]["warning_high"], THRESHOLDS["temperature"]["critical_high"]), 1)
            spo2 = random.randint(THRESHOLDS["spo2"]["critical_low"], THRESHOLDS["spo2"]["warning_low"])
        else:
            heart_rate = random.choice([
                random.randint(30, THRESHOLDS["heart_rate"]["critical_low"]),
                random.randint(THRESHOLDS["heart_rate"]["critical_high"], 180),
            ])
            temperature = round(random.uniform(THRESHOLDS["temperature"]["critical_high"], 41.0), 1)
            spo2 = random.randint(70, THRESHOLDS["spo2"]["critical_low"] - 1)

        # Klasifikasi status berdasarkan threshold per kategori usia
        status = self.classify_status(heart_rate, temperature, spo2)

        return {
            "heart_rate": heart_rate,
            "temperature": temperature,
            "spo2": spo2,
            "status": status,
            "kategori_usia": self.kategori_usia,
        }

    def generate_idempotency_key(self) -> str:
        """Generate unique idempotency key (32 hex chars)"""
        return secrets.token_hex(16)

    def send_sensor_data(self) -> bool:
        data = self.generate_sensor_data()
        self.data_count += 1

        self.log(
            f"[#{self.data_count}] HR={data['heart_rate']}bpm | "
            f"Temp={data['temperature']}C | SpO2={data['spo2']}% | "
            f"Status={data['status'].upper()} | Usia={data['kategori_usia']}"
        )

        try:
            url = f"{self.base_url}/device/{self.device_id}/sensor-data"
            headers = {**self.headers, "Idempotency-Key": self.generate_idempotency_key()}
            response = requests.post(url, headers=headers, json=data)

            if response.status_code in (200, 201, 202):
                return True
            elif response.status_code == 401:
                self.log("API key tidak valid!", "ERROR")
                self.stop_event.set()
                return False
            else:
                self.log(f"Gagal kirim data: {response.status_code}", "ERROR")
                return False
        except Exception as e:
            self.log(f"Error: {e}", "ERROR")
            return False

    def _status_monitor(self):
        """Thread terpisah: cek status setiap detik, hentikan jika offline"""
        while not self.stop_event.is_set():
            self.stop_event.wait(STATUS_CHECK_INTERVAL)
            if self.stop_event.is_set():
                break
            status = self.check_status()
            if status != "online":
                self.log("Perangkat dimatikan oleh nakes.", "WARN")
                self.stop_event.set()
                return

    def run(self):
        print(f"  [{self.device_id}] Profile: {self.profile} | Interval: {self.interval}s | Usia: {self.kategori_usia}")

        # Step 1: Autentikasi
        if not self.authenticate():
            self.log("Gagal autentikasi. Lewati device ini.", "ERROR")
            return

        # Step 2: Tunggu sampai online
        status = self.check_status()
        if status != "online":
            self.wait_for_online()

        # Step 3: Kirim status sistem
        self.send_system_status()

        # Step 4: Loop kirim data sensor dengan monitor thread
        self.stop_event.clear()
        self.log(f"Mengirim data setiap {self.interval}s...")

        try:
            while not self.stop_event.is_set():
                # Mulai monitor thread
                monitor = threading.Thread(target=self._status_monitor, daemon=True)
                monitor.start()

                # Kirim data selama device online
                while not self.stop_event.is_set():
                    self.send_sensor_data()
                    # Tunggu interval, tapi langsung bangun jika stop_event di-set
                    if self.stop_event.wait(self.interval):
                        break

                # Monitor detect offline — tunggu sampai online lagi
                monitor.join(timeout=2)
                self.wait_for_online()
                self.send_system_status()
                self.stop_event.clear()
        except KeyboardInterrupt:
            pass

        self.log(f"Selesai. Total data terkirim: {self.data_count}")


def load_devices() -> list:
    try:
        with open(DEVICES_FILE, "r") as f:
            return json.load(f)
    except FileNotFoundError:
        print(f"ERROR: File {DEVICES_FILE} tidak ditemukan!")
        print(f"Buat file {DEVICES_FILE} dengan format:")
        print(json.dumps([{
            "device_id": "DEV-01",
            "api_key": "sats_xxxxxxxx",
            "profile": "normal",
            "interval": 2
        }], indent=2))
        sys.exit(1)
    except json.JSONDecodeError as e:
        print(f"ERROR: Format JSON salah di {DEVICES_FILE}: {e}")
        sys.exit(1)


def select_kategori_usia(devices: list) -> list:
    """Interaktif: pilih kategori usia untuk setiap device"""
    print("=" * 60)
    print("  PILIH KATEGORI USIA PASIEN")
    print("=" * 60)
    print()
    print("  Pilihan kategori usia:")
    for i, k in enumerate(KATEGORI_USIA, 1):
        print(f"    {i}. {k}")
    print()

    for dev in devices:
        default = dev.get("kategori_usia", "Dewasa")
        default_idx = KATEGORI_USIA.index(default) + 1 if default in KATEGORI_USIA else 3

        while True:
            choice = input(f"  {dev['device_id']} (default: {default}) [{default_idx}]: ").strip()
            if choice == "":
                dev["kategori_usia"] = default
                break
            elif choice.isdigit() and 1 <= int(choice) <= len(KATEGORI_USIA):
                dev["kategori_usia"] = KATEGORI_USIA[int(choice) - 1]
                break
            else:
                print(f"    Pilih 1-{len(KATEGORI_USIA)} atau tekan Enter untuk default.")

    print()
    print("  Konfigurasi:")
    for dev in devices:
        print(f"    {dev['device_id']}: {dev.get('kategori_usia', 'Dewasa')}")
    print()
    return devices


def main():
    devices = load_devices()

    if not devices:
        print("Tidak ada device di devices.json")
        sys.exit(1)

    print("=" * 60)
    print("  SIMULASI PERANGKAT SATS (MULTI-DEVICE)")
    print(f"  Server: {BASE_URL}")
    print(f"  Jumlah device: {len(devices)}")
    print("=" * 60)
    print()

    # Pilih kategori usia secara interaktif
    devices = select_kategori_usia(devices)

    threads = []
    simulators = []

    for dev in devices:
        sim = SATSSimulator(
            device_id=dev["device_id"],
            api_key=dev["api_key"],
            profile=dev.get("profile", "normal"),
            interval=dev.get("interval", DEFAULT_INTERVAL),
            kategori_usia=dev.get("kategori_usia", "Dewasa"),
        )
        simulators.append(sim)
        t = threading.Thread(target=sim.run, daemon=True)
        threads.append(t)

    # Jalankan semua thread
    for t in threads:
        t.start()
        time.sleep(0.5)  # Jeda antar start agar log tidak tumpang tindih

    print()
    print("-" * 60)
    print("  Semua device berjalan. Tekan Ctrl+C untuk berhenti.")
    print("-" * 60)

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        print()
        print("=" * 60)
        print("  Menghentikan semua simulasi...")
        print("=" * 60)

        for sim in simulators:
            sim.stop_event.set()

        for t in threads:
            t.join(timeout=3)

        print("  Semulasi selesai.")


if __name__ == "__main__":
    main()
