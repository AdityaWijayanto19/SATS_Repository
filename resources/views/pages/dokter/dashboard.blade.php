@extends('layouts.app')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]" x-data="dashboard()" x-init="init()">

        {{-- Judul dan Dropdown Btn --}}
        <div class="flex items-start justify-between mb-5">
            <div>
                <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring</h1>
                <p class="text-sm text-gray-400 mt-1" x-text="selectedDeviceId ?? 'Belum memilih perangkat'"></p>
            </div>

            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen"
                    class="cursor-pointer px-4 py-2 bg-[rgb(0,62,48)] text-white rounded-lg flex items-center gap-2 text-sm hover:opacity-90 transition">
                    Pilih Perangkat
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="dropdownOpen" @click.outside="dropdownOpen = false" x-transition
                    class="absolute right-0 mt-2 w-52 bg-white border border-[rgba(0,83,63,0.15)] rounded-xl shadow-sm z-50 overflow-hidden">
                    <template x-for="device in allDevices" :key="device.device_id">
                        <div @click="selectDevice(device.device_id); dropdownOpen = false"
                            class="px-4 py-2.5 text-sm hover:bg-[rgba(0,83,63,0.06)] cursor-pointer text-[rgb(0,62,48)]"
                            x-text="device.device_id"></div>
                    </template>
                    <div x-show="allDevices.length === 0" class="px-4 py-3 text-sm text-gray-400 text-center">
                        Tidak ada perangkat online
                    </div>
                </div>
            </div>
        </div>

        {{-- Peringatan: Belum Pilih Perangkat --}}
        <div x-show="!selectedDeviceId" x-transition
            class="flex flex-col items-center justify-center py-20">
            <div class="w-20 h-20 rounded-full bg-amber-50 flex items-center justify-center mb-5">
                <svg class="w-10 h-10 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-700 mb-2">Belum Ada Perangkat Dipilih</h2>
            <p class="text-sm text-gray-400 text-center max-w-md">
                Pilih perangkat yang sedang online dari dropdown di atas untuk memulai monitoring vital sign pasien.
            </p>
        </div>

        {{-- Stat Card (4 Kolom) --}}
        <div x-show="selectedDeviceId" x-transition class="grid grid-cols-4 gap-3 mb-4">

            {{-- Heart Rate --}}
            <div class="bg-red-50 rounded-xl p-4 border border-red-200">
                <p class="text-xs font-medium text-red-400 mb-2">Heart Rate</p>
                <p class="text-3xl font-medium text-[rgb(0,62,48)]">
                    <span x-text="latest?.heart_rate ?? '—'"></span>
                    <span class="text-sm font-normal text-red-400">bpm</span>
                </p>
                <p class="text-[10px] mt-1" :class="getStatusClass(latest?.heart_rate, 'hr')"
                    x-text="getStatusText(latest?.heart_rate, 'hr')"></p>
            </div>

            {{-- SpO2 --}}
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <p class="text-xs font-medium text-blue-400 mb-2">SpO2</p>
                <p class="text-3xl font-medium text-[rgb(0,62,48)]">
                    <span x-text="latest?.spo2 ?? '—'"></span>
                    <span class="text-sm font-normal text-blue-400">%</span>
                </p>
                <p class="text-[10px] mt-1" :class="getStatusClass(latest?.spo2, 'spo2')"
                    x-text="getStatusText(latest?.spo2, 'spo2')"></p>
            </div>

            {{-- Temperature --}}
            <div class="bg-orange-50 rounded-xl p-4 border border-orange-200">
                <p class="text-xs font-medium text-orange-400 mb-2">Temperature</p>
                <p class="text-3xl font-medium text-[rgb(0,62,48)]">
                    <span x-text="latest?.temperature ?? '—'"></span>
                    <span class="text-sm font-normal text-orange-400">°C</span>
                </p>
                <p class="text-[10px] mt-1" :class="getStatusClass(latest?.temperature, 'temp')"
                    x-text="getStatusText(latest?.temperature, 'temp')"></p>
            </div>

            {{-- Kondisi Pasien --}}
            <div class="bg-[rgba(0,83,63,0.05)] rounded-xl p-4 border border-[rgba(0,83,63,0.2)]">
                <p class="text-xs font-medium text-[rgb(0,62,48)] mb-2">Kondisi Pasien</p>
                <p class="text-2xl font-medium"
                    :class="{
                        'text-[rgb(0,62,48)]': latest?.status === 'normal',
                        'text-orange-500': latest?.status === 'warning',
                        'text-red-500': latest?.status === 'critical'
                    }"
                    x-text="latest?.status ? latest.status.charAt(0).toUpperCase() + latest.status.slice(1) : '—'"></p>
                <p class="text-[10px] text-[rgba(0,62,48,0.5)] mt-1">
                    Pembaruan: <span x-text="latest?.created_at ?? '—'"></span>
                </p>
            </div>
        </div>

        {{-- Prediksi Machine Learning --}}
        <div x-show="selectedDeviceId" x-transition
            class="flex items-center gap-4 bg-[rgba(0,62,48,0.05)] border border-[rgba(0,62,48,0.18)] rounded-xl px-5 py-3.5 mb-4">
            <span class="w-2 h-2 rounded-full flex-shrink-0"
                :class="{
                    'bg-green-400': latest?.ml_condition === 'NORMAL',
                    'bg-orange-400': latest?.ml_condition === 'WARNING',
                    'bg-red-400': latest?.ml_condition === 'CRITICAL',
                    'bg-gray-300': !latest?.ml_condition
                }"></span>
            <div class="flex-1">
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Prediksi ML</p>
                <p class="text-sm font-medium text-[rgb(0,62,48)]">
                    <span x-show="latest?.ml_prediction" x-text="latest?.ml_prediction"></span>
                    <span x-show="!latest?.ml_prediction">Data prediksi belum tersedia.</span>
                </p>
            </div>
            <span x-show="latest?.ml_condition" class="text-[10px] font-medium px-2.5 py-1 rounded flex-shrink-0"
                :class="{
                    'bg-green-100 text-green-700': latest?.ml_condition === 'NORMAL',
                    'bg-orange-100 text-orange-700': latest?.ml_condition === 'WARNING',
                    'bg-red-100 text-red-700': latest?.ml_condition === 'CRITICAL'
                }"
                x-text="latest?.ml_risk_level ?? latest?.ml_condition">
            </span>
        </div>

        {{-- Grafik Sensor (3 Kolom) --}}
        <div x-show="selectedDeviceId" x-transition class="grid grid-cols-3 gap-3">
            <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                    <p class="text-sm font-medium text-[rgb(0,62,48)]">Heart Rate</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">bpm — 10 menit terakhir</p>
                </div>
                <div class="p-4 relative" style="height: 200px;">
                    <canvas id="hrChart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                    <p class="text-sm font-medium text-[rgb(0,62,48)]">SpO2</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">% — 10 menit terakhir</p>
                </div>
                <div class="p-4 relative" style="height: 200px;">
                    <canvas id="spo2Chart"></canvas>
                </div>
            </div>

            <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                    <p class="text-sm font-medium text-[rgb(0,62,48)]">Temperature</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">°C — 10 menit terakhir</p>
                </div>
                <div class="p-4 relative" style="height: 200px;">
                    <canvas id="tempChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Instruksi Dokter --}}
        <div x-show="selectedDeviceId" x-transition class="mt-4 mx-auto" x-data="instruksiDokter()" x-init="init()">
            <div
                class="bg-white rounded-2xl border border-[rgba(0,83,63,0.1)] shadow-sm overflow-hidden flex flex-col h-[80vh]">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-[rgba(0,83,63,0.08)] bg-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[rgb(0,62,48)]">Monitoring Ambulans: <span
                                x-text="deviceId"></span>
                        </h2>
                        <p class="text-[11px] text-gray-400">Pantau laporan nakes dan berikan instruksi medis</p>
                    </div>
                </div>

                {{-- Chat Area dengan x-ref untuk Scroll --}}
                <div x-ref="chatBox" class="flex-1 overflow-y-auto p-6 flex flex-col gap-6 bg-gray-50/30 scroll-smooth">
                    <template x-if="instruksi.length === 0">
                        <div class="flex flex-col items-center justify-center h-full opacity-40">
                            <p class="text-sm italic">Belum ada aktivitas laporan.</p>
                        </div>
                    </template>

                    <template x-for="item in sortedInstruksi" :key="item.id">
                        <div class="flex flex-col gap-3">

                            {{-- 1. Laporan Nakes (Sisi Kiri) --}}
                            <div class="flex justify-start items-end gap-2"
                                x-show="item.laporan_nakes && item.laporan_nakes !== '-'">
                                {{-- Foto Profil Inisial --}}
                                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 mb-1"
                                    x-text="getInitials(item.nakes_name || 'Nakes')">
                                </div>

                                <div
                                    class="max-w-[75%] bg-white border border-gray-200 p-3 rounded-2xl rounded-bl-none shadow-sm relative">
                                    <div class="flex flex-col">
                                        {{-- Nama Nakes --}}
                                        <span class="text-[10px] font-bold text-emerald-700 uppercase mb-1"
                                            x-text="item.nakes_name || 'NAKES'"></span>

                                        <p class="text-sm text-gray-700 leading-relaxed font-normal mb-4"
                                            x-text="item.laporan_nakes"></p>

                                        {{-- Waktu Pojok Bawah --}}
                                        <span class="absolute bottom-1 right-3 text-[9px] text-gray-400"
                                            x-text="item.waktu"></span>
                                    </div>
                                </div>
                            </div>

                            <template x-if="item.instruksi_dokter">
                                <div class="flex justify-end">
                                    <div :class="item.is_completed ? 'bg-green-50 border-green-100 opacity-90' :
                                        'bg-[rgb(0,83,63)] text-white'"
                                        class="max-w-[80%] p-4 rounded-2xl rounded-tr-none shadow-md transition-all border relative">

                                        <p class="text-sm leading-relaxed mb-2"
                                            :class="item.is_completed ? 'text-green-900' : 'text-white'"
                                            x-text="item.instruksi_dokter"></p>

                                        <span class="absolute bottom-1 right-3 text-[9px] opacity-70"
                                            :class="item.is_completed ? 'text-emerald-600' : 'text-white'"
                                            x-text="item.waktu"></span>

                                        {{-- Slot Respon dari Nakes --}}
                                        <template x-if="item.is_completed">
                                            <div class="mt-3 pt-2 border-t border-green-200/50 flex flex-col gap-1">
                                                <div class="flex items-center gap-2">
                                                    <p class="text-[9px] font-bold text-green-800/60 uppercase">Respon
                                                        Balik
                                                        Nakes:</p>
                                                    <template x-if="item.is_completed">
                                                        <span
                                                            class="text-[9px] bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold">SELESAI</span>
                                                    </template>
                                                </div>

                                                <div
                                                    class="flex items-center gap-2 text-green-900 font-bold text-xs bg-white/50 p-2 rounded-lg">
                                                    <span x-text="item.respon_nakes"></span>
                                                    <span class="text-[9px] font-normal opacity-60"
                                                        x-text="'• ' + item.completed_at"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Input Bar --}}
                <div class="px-5 py-3.5 border-t border-[rgba(0,83,63,0.08)] bg-gray-50/50">
                    <div class="flex gap-3">
                        <textarea x-model="teksBaru" rows="1" @input="autoResize($el)" placeholder="Ctrl + Enter untuk kirim"
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                            @keydown.ctrl.enter="kirimInstruksi()"></textarea>
                        <button @click="kirimInstruksi()" :disabled="!teksBaru.trim() || isSending"
                            class="self-end p-2.5 bg-[rgb(0,83,63)] text-white rounded-xl hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                            <template x-if="!isSending">
                                <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                </svg>
                            </template>
                            <template x-if="isSending">
                                <span
                                    class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                            </template>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

        <script>
            // Global state untuk share data antar Alpine components
            let globalSelectedDeviceId = null;

            // Chart instances
            let hrChart, spo2Chart, tempChart;

            function initCharts() {
                const chartOpts = {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 300 },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0 } },
                        y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { font: { size: 10 } } }
                    },
                    elements: { point: { radius: 2, hoverRadius: 4 }, line: { tension: 0.3, borderWidth: 2 } }
                };

                hrChart = new Chart(document.getElementById('hrChart'), {
                    type: 'line',
                    data: { labels: [], datasets: [{ data: [], borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true }] },
                    options: { ...chartOpts, scales: { ...chartOpts.scales, y: { ...chartOpts.scales.y, min: 40, max: 160 } } }
                });
                spo2Chart = new Chart(document.getElementById('spo2Chart'), {
                    type: 'line',
                    data: { labels: [], datasets: [{ data: [], borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true }] },
                    options: { ...chartOpts, scales: { ...chartOpts.scales, y: { ...chartOpts.scales.y, min: 85, max: 100 } } }
                });
                tempChart = new Chart(document.getElementById('tempChart'), {
                    type: 'line',
                    data: { labels: [], datasets: [{ data: [], borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.1)', fill: true }] },
                    options: { ...chartOpts, scales: { ...chartOpts.scales, y: { ...chartOpts.scales.y, min: 35, max: 40 } } }
                });
            }

            function updateCharts(history) {
                if (!history) {
                    [hrChart, spo2Chart, tempChart].forEach(c => {
                        if (c) { c.data.labels = []; c.data.datasets[0].data = []; c.update('none'); }
                    });
                    return;
                }
                hrChart.data.labels = history.labels; hrChart.data.datasets[0].data = history.heart_rate; hrChart.update('none');
                spo2Chart.data.labels = history.labels; spo2Chart.data.datasets[0].data = history.spo2; spo2Chart.update('none');
                tempChart.data.labels = history.labels; tempChart.data.datasets[0].data = history.temperature; tempChart.update('none');
            }

            function dashboard() {
                return {
                    allDevices: [],
                    selectedDeviceId: null,
                    dropdownOpen: false,
                    latest: null,

                    async init() {
                        initCharts();
                        await this.fetchDevices();
                        this.setupRealtime();
                    },

                    async fetchDevices() {
                        try {
                            const res = await fetch('/api/devices');
                            const json = await res.json();
                            if (json.success) {
                                this.allDevices = json.data.filter(d => d.status === 'online');

                                if (this.selectedDeviceId) {
                                    const selected = this.allDevices.find(d => d.device_id === this.selectedDeviceId);
                                    if (selected) {
                                        this.latest = selected.latest;
                                        updateCharts(selected.history);
                                    } else {
                                        this.selectedDeviceId = null;
                                        globalSelectedDeviceId = null;
                                        this.latest = null;
                                        updateCharts(null);
                                    }
                                }

                                if (!this.selectedDeviceId && this.allDevices.length > 0) {
                                    this.selectDevice(this.allDevices[0].device_id);
                                }
                            }
                        } catch (e) {
                            console.error('Error fetching devices:', e);
                        }
                    },

                    setupRealtime() {
                        if (!window.Echo) return;
                        // Subscribe ke semua device untuk status changes
                        this.subscribeAllDevices();
                    },

                    subscribeAllDevices() {
                        const allDeviceIds = this.allDevices.map(d => d.device_id);
                        if (this.selectedDeviceId && !allDeviceIds.includes(this.selectedDeviceId)) {
                            allDeviceIds.push(this.selectedDeviceId);
                        }

                        allDeviceIds.forEach(deviceId => {
                            window.Echo.private(`device.${deviceId}`)
                                .listen('.device.status.changed', async (e) => {
                                    if (e.status === 'offline') {
                                        this.allDevices = this.allDevices.filter(d => d.device_id !== e.device_id);
                                        if (this.selectedDeviceId === e.device_id) {
                                            this.selectedDeviceId = null;
                                            globalSelectedDeviceId = null;
                                            this.latest = null;
                                            updateCharts(null);
                                        }
                                    } else {
                                        // Device online — refresh list + re-subscribe
                                        await this.fetchDevices();
                                        this.subscribeAllDevices();
                                    }
                                })
                                .listen('.sensor.data.received', (e) => {
                                    if (this.selectedDeviceId === e.device_id) {
                                        this.latest = e.latest;
                                        updateCharts(e.history);
                                    }
                                });
                        });
                    },

                    selectDevice(deviceId) {
                        this.selectedDeviceId = deviceId;
                        globalSelectedDeviceId = deviceId;
                        const selected = this.allDevices.find(d => d.device_id === deviceId);
                        if (selected) {
                            this.latest = selected.latest;
                            updateCharts(selected.history);
                        }
                        window.dispatchEvent(new CustomEvent('deviceSelected', { detail: { deviceId } }));
                    },

                    destroy() {}
                };
            }

             function instruksiDokter() {
            return {
                instruksi: [],
                teksBaru: '',
                isSending: false,
                deviceId: null,
                notificationSound: new Audio('/assets/sounds/notification.mp3'),

                async init() {
                    window.addEventListener('deviceSelected', async (e) => {
                        const deviceId = e.detail.deviceId;
                        this.deviceId = deviceId;
                        console.log('instruksiDokter: Fetch instruksi for device:', deviceId);
                        await this.fetchInstructions(deviceId);
                        this.setupReverb();
                        setTimeout(() => this.scrollToBottom(), 300);
                    });
                },

                // Ambil Inisial Nama (Contoh: Budi Santoso -> BS)
                getInitials(name) {
                    if (!name) return 'NK';
                    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                },

                // Fungsi Auto Scroll ke paling bawah
                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.chatBox;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                autoResize(el) {
                    el.style.height = 'auto';
                    el.style.height = el.scrollHeight + 'px';
                },

                get sortedInstruksi() {
                    return [...this.instruksi].sort((a, b) => (a.id || 0) - (b.id || 0));
                },

                setupReverb() {
                    if (!window.Echo) return;

                    window.Echo.private(`device.${this.deviceId}`)
                        .listen('.instruction.report.submitted', (e) => {
                            const exists = this.instruksi.some(i => i.id === e.instruction.id);
                            if (!exists) {
                                this.instruksi.push(e.instruction);
                                this.scrollToBottom();
                                this.notificationSound.play().catch(() => {});
                            }
                        })

                        .listen('.instruction.updated', (e) => {
                            const item = this.instruksi.find(i => i.id === e.instruction.id);
                            if (item) {
                                item.is_completed = e.instruction.is_completed;
                                item.respon_nakes = e.instruction.respon_nakes;
                                item.completed_at = e.instruction.completed_at;
                                this.notificationSound.play().catch(() => {});
                                this.scrollToBottom();
                            }
                        });
                },

                async fetchInstructions(deviceId) {
                    if (!deviceId) return;
                    try {
                        const res = await fetch(`/api/instruction?device_id=${deviceId}`);
                        const json = await res.json();
                        console.log(`[fetchInstructions] device=${deviceId}:`, json);
                        if (json.success) {
                            this.instruksi = json.data;
                        }
                    } catch (e) {
                        console.error('fetchInstructions error:', e);
                    }
                },

                async kirimInstruksi() {
                    if (!this.teksBaru.trim() || this.isSending) return;

                    // Ambil waktu real-time saat ini
                    this.isSending = true;
                    const sekarang = new Date().toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const pesanBaru = {
                        device_id: this.deviceId,
                        instruksi_dokter: this.teksBaru,
                        waktu: sekarang // Menggunakan waktu client agar instan
                    };

                    try {
                        const res = await fetch(`/api/instruction`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(pesanBaru)
                        });

                        if (res.ok) {
                            const json = await res.json();
                            this.teksBaru = '';
                            this.fetchInstructions(this.deviceId);
                            // Tambahkan ke array lokal agar langsung muncul (Optimistic Update)
                            this.instruksi.push(json.data);
                            this.scrollToBottom(); // Scroll otomatis setelah kirim
                        }
                        this.teksBaru = '';
                        this.$nextTick(() => {
                            const el = document.querySelector('textarea'); // Pastikan targetnya pas
                            if (el) el.style.height = 'auto';
                        });
                    } catch (e) {
                        console.error('Kirim Instruksi Error:', e);
                    } finally {
                        this.isSending = false;
                    }
                }
            }
        }
        </script>
    @endpush


@endsection
