"""
SATS - Simulasi Perangkat IoT dengan GUI

GUI untuk mengaktifkan/menonaktifkan perangkat IoT dan mengirim data sensor.
Menggantikan tombol toggle di dashboard nakes.

Usage:
    python device_gui.py
"""

import json
import os
import random
import secrets
import threading
import time
import tkinter as tk
from tkinter import ttk, scrolledtext
from datetime import datetime

import requests

# Konfigurasi
from config import (
    BASE_URL, DEVICES_FILE, SENSOR_CONFIG, THRESHOLDS, PROFILES,
    NORMAL_RANGES, WARNING_THRESHOLDS, CRITICAL_THRESHOLDS,
)


class DeviceGUI:
    def __init__(self, root):
        self.root = root
        self.root.title("SATS - Simulasi Perangkat IoT")
        self.root.geometry("520x650")
        self.root.resizable(False, False)
        self.root.configure(bg="#f5f5f5")

        # State
        self.devices = []
        self.current_device = None
        self.is_online = False
        self.is_sending = False
        self.stop_event = threading.Event()
        self.sensor_thread = None
        self.data_count = 0

        self.load_devices()
        self.build_ui()

    def load_devices(self):
        try:
            with open(DEVICES_FILE, "r") as f:
                self.devices = json.load(f)
        except FileNotFoundError:
            self.devices = []
        except json.JSONDecodeError:
            self.devices = []

    def build_ui(self):
        # Style
        style = ttk.Style()
        style.theme_use("clam")

        # Main frame
        main_frame = tk.Frame(self.root, bg="#f5f5f5", padx=20, pady=15)
        main_frame.pack(fill="both", expand=True)

        # Title
        title_frame = tk.Frame(main_frame, bg="#f5f5f5")
        title_frame.pack(fill="x", pady=(0, 15))
        tk.Label(title_frame, text="SATS", font=("Segoe UI", 20, "bold"),
                 fg="#003e30", bg="#f5f5f5").pack(side="left")
        tk.Label(title_frame, text="Simulasi Perangkat IoT", font=("Segoe UI", 11),
                 fg="#666", bg="#f5f5f5").pack(side="left", padx=(8, 0))

        # Separator
        ttk.Separator(main_frame, orient="horizontal").pack(fill="x", pady=(0, 15))

        # Device selection frame
        device_frame = tk.LabelFrame(main_frame, text=" Perangkat ", font=("Segoe UI", 10, "bold"),
                                     bg="#f5f5f5", fg="#333", padx=10, pady=10)
        device_frame.pack(fill="x", pady=(0, 10))

        # Device dropdown
        row1 = tk.Frame(device_frame, bg="#f5f5f5")
        row1.pack(fill="x", pady=3)
        tk.Label(row1, text="Device ID", width=10, anchor="w", font=("Segoe UI", 9),
                 bg="#f5f5f5", fg="#555").pack(side="left")
        self.device_var = tk.StringVar()
        device_ids = [d["device_id"] for d in self.devices] if self.devices else ["(tidak ada device)"]
        self.device_combo = ttk.Combobox(row1, textvariable=self.device_var, values=device_ids,
                                          state="readonly", width=30, font=("Segoe UI", 9))
        self.device_combo.pack(side="left", padx=(5, 0))
        self.device_combo.bind("<<ComboboxSelected>>", self.on_device_change)

        # API Key display
        row2 = tk.Frame(device_frame, bg="#f5f5f5")
        row2.pack(fill="x", pady=3)
        tk.Label(row2, text="API Key", width=10, anchor="w", font=("Segoe UI", 9),
                 bg="#f5f5f5", fg="#555").pack(side="left")
        self.api_key_var = tk.StringVar()
        self.api_key_entry = tk.Entry(row2, textvariable=self.api_key_var, width=33,
                                       font=("Consolas", 9), state="readonly", readonlybackground="#fff")
        self.api_key_entry.pack(side="left", padx=(5, 0))

        # Profile display
        row3 = tk.Frame(device_frame, bg="#f5f5f5")
        row3.pack(fill="x", pady=3)
        tk.Label(row3, text="Profile", width=10, anchor="w", font=("Segoe UI", 9),
                 bg="#f5f5f5", fg="#555").pack(side="left")
        self.profile_var = tk.StringVar(value="-")
        tk.Label(row3, textvariable=self.profile_var, font=("Segoe UI", 9, "bold"),
                 bg="#f5f5f5", fg="#333").pack(side="left", padx=(5, 0))

        # Kategori Usia dropdown
        row4 = tk.Frame(device_frame, bg="#f5f5f5")
        row4.pack(fill="x", pady=3)
        tk.Label(row4, text="Kategori Usia", width=10, anchor="w", font=("Segoe UI", 9),
                 bg="#f5f5f5", fg="#555").pack(side="left")
        self.kategori_usia_var = tk.StringVar(value="Dewasa")
        self.kategori_usia_combo = ttk.Combobox(row4, textvariable=self.kategori_usia_var,
                                                  values=["Balita", "Anak-anak", "Dewasa", "Lansia"],
                                                  state="readonly", width=15, font=("Segoe UI", 9))
        self.kategori_usia_combo.pack(side="left", padx=(5, 0))
        self.kategori_usia_combo.bind("<<ComboboxSelected>>", self.on_kategori_usia_change)

        # Status frame
        status_frame = tk.LabelFrame(main_frame, text=" Status ", font=("Segoe UI", 10, "bold"),
                                     bg="#f5f5f5", fg="#333", padx=10, pady=10)
        status_frame.pack(fill="x", pady=(0, 10))

        # Status indicator
        self.status_canvas = tk.Canvas(status_frame, width=20, height=20, bg="#f5f5f5",
                                        highlightthickness=0)
        self.status_canvas.pack(side="left")
        self.status_dot = self.status_canvas.create_oval(3, 3, 17, 17, fill="#ccc", outline="#ccc")

        self.status_label = tk.Label(status_frame, text="OFFLINE", font=("Segoe UI", 14, "bold"),
                                      fg="#999", bg="#f5f5f5")
        self.status_label.pack(side="left", padx=(8, 0))

        self.data_count_label = tk.Label(status_frame, text="Data: 0", font=("Segoe UI", 9),
                                          fg="#888", bg="#f5f5f5")
        self.data_count_label.pack(side="right")

        # Buttons frame
        btn_frame = tk.Frame(main_frame, bg="#f5f5f5")
        btn_frame.pack(fill="x", pady=(0, 10))

        self.on_btn = tk.Button(btn_frame, text="▶  NYALAKAN", font=("Segoe UI", 11, "bold"),
                                 bg="#22c55e", fg="white", relief="flat", cursor="hand2",
                                 activebackground="#16a34a", activeforeground="white",
                                 width=16, height=2, command=self.turn_on)
        self.on_btn.pack(side="left", expand=True, fill="x", padx=(0, 5))

        self.off_btn = tk.Button(btn_frame, text="■  MATIKAN", font=("Segoe UI", 11, "bold"),
                                  bg="#ef4444", fg="white", relief="flat", cursor="hand2",
                                  activebackground="#dc2626", activeforeground="white",
                                  width=16, height=2, state="disabled", command=self.turn_off)
        self.off_btn.pack(side="left", expand=True, fill="x", padx=(5, 0))

        # Log frame
        log_frame = tk.LabelFrame(main_frame, text=" Log Aktivitas ", font=("Segoe UI", 10, "bold"),
                                   bg="#f5f5f5", fg="#333", padx=10, pady=5)
        log_frame.pack(fill="both", expand=True)

        self.log_area = scrolledtext.ScrolledText(log_frame, height=12, font=("Consolas", 9),
                                                   state="disabled", bg="#1e1e1e", fg="#d4d4d4",
                                                   insertbackground="white", relief="flat",
                                                   padx=8, pady=5)
        self.log_area.pack(fill="both", expand=True)

        # Configure log tags
        self.log_area.tag_configure("INFO", foreground="#d4d4d4")
        self.log_area.tag_configure("SUCCESS", foreground="#4ade80")
        self.log_area.tag_configure("WARN", foreground="#fbbf24")
        self.log_area.tag_configure("ERROR", foreground="#f87171")
        self.log_area.tag_configure("DATA", foreground="#60a5fa")
        self.log_area.tag_configure("TIME", foreground="#888")

        # Select first device if available
        if self.devices:
            self.device_combo.current(0)
            self.on_device_change(None)

    def log(self, message, level="INFO"):
        timestamp = datetime.now().strftime("%H:%M:%S")

        def _write():
            self.log_area.configure(state="normal")
            self.log_area.insert("end", f"[{timestamp}] ", "TIME")
            self.log_area.insert("end", f"{message}\n", level)
            self.log_area.see("end")
            self.log_area.configure(state="disabled")

        self.root.after(0, _write)

    def on_device_change(self, event):
        idx = self.device_combo.current()
        if idx < 0 or idx >= len(self.devices):
            return

        self.current_device = self.devices[idx]
        self.api_key_var.set(self.current_device.get("api_key", ""))
        profile = self.current_device.get("profile", "normal")
        self.profile_var.set(profile.upper())

        kategori_usia = self.current_device.get("kategori_usia", "Dewasa")
        self.kategori_usia_var.set(kategori_usia)

        self.log(f"Device dipilih: {self.current_device['device_id']} (profile: {profile}, usia: {kategori_usia})")

    def on_kategori_usia_change(self, event):
        kategori_usia = self.kategori_usia_var.get()
        self.log(f"Kategori usia diubah: {kategori_usia}")

    def update_status(self, online):
        self.is_online = online

        def _update():
            if online:
                self.status_canvas.itemconfig(self.status_dot, fill="#22c55e", outline="#22c55e")
                self.status_label.config(text="ONLINE", fg="#22c55e")
                self.on_btn.config(state="disabled")
                self.off_btn.config(state="normal")
                # Lock kategori usia saat perangkat menyala
                self.kategori_usia_combo.config(state="disabled")
            else:
                self.status_canvas.itemconfig(self.status_dot, fill="#ccc", outline="#ccc")
                self.status_label.config(text="OFFLINE", fg="#999")
                self.on_btn.config(state="normal")
                self.off_btn.config(state="disabled")
                # Unlock kategori usia saat perangkat mati
                self.kategori_usia_combo.config(state="readonly")

        self.root.after(0, _update)

    def update_data_count(self, count):
        self.data_count = count
        self.root.after(0, lambda: self.data_count_label.config(text=f"Data: {count}"))

    def turn_on(self):
        if not self.current_device:
            self.log("Pilih device terlebih dahulu!", "WARN")
            return

        kategori_usia = self.kategori_usia_var.get()
        self.log(f"Mengaktifkan perangkat {self.current_device['device_id']} (usia: {kategori_usia})...")

        def _do():
            try:
                url = f"{BASE_URL}/device/{self.current_device['device_id']}/status"
                headers = {
                    "X-API-Key": self.current_device["api_key"],
                    "Content-Type": "application/json",
                }
                resp = requests.patch(url, headers=headers, json={"status": "online"}, timeout=10)

                if resp.status_code == 200:
                    self.log("Perangkat: ONLINE", "SUCCESS")
                    self.update_status(True)
                    self.data_count = 0
                    self.update_data_count(0)

                    # Start sending sensor data
                    self.stop_event.clear()
                    self.is_sending = True
                    self.sensor_thread = threading.Thread(target=self.send_sensor_loop, daemon=True)
                    self.sensor_thread.start()
                else:
                    self.log(f"Gagal mengaktifkan: {resp.status_code} - {resp.text}", "ERROR")
            except requests.ConnectionError:
                self.log("Tidak dapat terhubung ke server. Pastikan Laravel berjalan.", "ERROR")
            except Exception as e:
                self.log(f"Error: {e}", "ERROR")

        threading.Thread(target=_do, daemon=True).start()

    def turn_off(self):
        if not self.current_device:
            return

        self.log(f"Menonaktifkan perangkat {self.current_device['device_id']}...")
        self.stop_event.set()
        self.is_sending = False

        def _do():
            try:
                url = f"{BASE_URL}/device/{self.current_device['device_id']}/status"
                headers = {
                    "X-API-Key": self.current_device["api_key"],
                    "Content-Type": "application/json",
                }
                resp = requests.patch(url, headers=headers, json={"status": "offline"}, timeout=10)

                if resp.status_code == 200:
                    self.log("Perangkat: OFFLINE", "WARN")
                    self.update_status(False)
                else:
                    self.log(f"Gagal menonaktifkan: {resp.status_code} - {resp.text}", "ERROR")
            except requests.ConnectionError:
                self.log("Tidak dapat terhubung ke server.", "ERROR")
                self.update_status(False)
            except Exception as e:
                self.log(f"Error: {e}", "ERROR")
                self.update_status(False)

        threading.Thread(target=_do, daemon=True).start()

    def classify_status(self, heart_rate, temperature, spo2):
        """Klasifikasi kondisi pasien berdasarkan rule-based threshold per kategori usia."""
        usia = self.kategori_usia_var.get()
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

        return "normal"

    def generate_sensor_data(self):
        profile = self.current_device.get("profile", "normal")
        prob_normal, prob_warning, prob_critical = PROFILES.get(profile, PROFILES["normal"])
        cfg = SENSOR_CONFIG

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
            temperature = round(random.uniform(THRESHOLDS["temperature"]["warning_high"],
                                                THRESHOLDS["temperature"]["critical_high"]), 1)
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
        kategori_usia = self.kategori_usia_var.get()

        return {
            "heart_rate": heart_rate,
            "temperature": temperature,
            "spo2": spo2,
            "status": status,
            "kategori_usia": kategori_usia,
        }

    def send_sensor_loop(self):
        interval = self.current_device.get("interval", 2)
        self.log(f"Mengirim data setiap {interval} detik...")

        while not self.stop_event.is_set():
            data = self.generate_sensor_data()
            self.data_count += 1

            try:
                url = f"{BASE_URL}/device/{self.current_device['device_id']}/sensor-data"
                headers = {
                    "X-API-Key": self.current_device["api_key"],
                    "Content-Type": "application/json",
                    "Idempotency-Key": secrets.token_hex(16),
                }
                resp = requests.post(url, headers=headers, json=data, timeout=10)

                if resp.status_code in (200, 201, 202):
                    self.log(
                        f"[#{self.data_count}] HR={data['heart_rate']}bpm | "
                        f"SpO2={data['spo2']}% | Temp={data['temperature']}C | "
                        f"{data['status'].upper()} | Usia={data['kategori_usia']}",
                        "DATA"
                    )
                    self.update_data_count(self.data_count)
                elif resp.status_code == 401:
                    self.log("API key tidak valid!", "ERROR")
                    self.stop_event.set()
                    self.root.after(0, lambda: self.update_status(False))
                    return
                else:
                    self.log(f"Gagal kirim: {resp.status_code}", "ERROR")
            except requests.ConnectionError:
                self.log("Koneksi terputus.", "ERROR")
            except Exception as e:
                self.log(f"Error: {e}", "ERROR")

            # Wait with interruptible sleep
            self.stop_event.wait(interval)

        self.log("Berhenti mengirim data.")


def main():
    root = tk.Tk()
    app = DeviceGUI(root)

    def on_closing():
        app.stop_event.set()
        root.destroy()

    root.protocol("WM_DELETE_WINDOW", on_closing)
    root.mainloop()


if __name__ == "__main__":
    main()
