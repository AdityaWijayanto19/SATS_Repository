@extends('layouts.app')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]" x-data="dashboard()" x-init="init()">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-5">
            <div>
                <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring</h1>
                <div class="flex items-center gap-2 mt-1">
                    <p class="text-sm text-gray-400" x-text="selectedDeviceId ?? 'Belum ada perangkat'"></p>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                        :class="deviceOnline ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                        x-text="deviceOnline ? 'Online' : 'Offline'"></span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                {{-- Toggle Perangkat --}}
                <button @click="toggleDevice()"
                    class="cursor-pointer px-4 py-2 rounded-lg flex items-center gap-2 text-sm font-medium transition"
                    :class="deviceOnline
                        ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100'
                        : 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-100'">
                    <span x-text="deviceOnline ? 'Matikan Perangkat' : 'Aktifkan Perangkat'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.636 5.636a9 9 0 1012.728 0M12 3v6" />
                    </svg>
                </button>

                {{-- Ganti Perangkat --}}
                <form method="POST" action="{{ route('nakes.device-config.reset') }}"
                    onsubmit="return confirm('Ganti perangkat? Anda harus mengonfigurasi perangkat baru.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="cursor-pointer px-4 py-2 bg-[rgb(0,62,48)] text-white rounded-lg flex items-center gap-2 text-sm hover:opacity-90 transition">
                        Ganti Perangkat
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-4 gap-3 mb-4">

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
                    x-text="latest?.status ? (latest.status.charAt(0).toUpperCase() + latest.status.slice(1)) : '—'"></p>
                <p class="text-[10px] text-[rgba(0,62,48,0.5)] mt-1">
                    Pembaruan: <span x-text="latest?.created_at ?? '—'"></span>
                </p>
            </div>

        </div>

        {{-- Prediksi ML --}}
        <div x-show="latest"
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
                x-text="latest?.ml_risk_level ?? latest?.ml_condition"></span>
        </div>

        {{-- Grafik Sensor --}}
        <div class="grid grid-cols-3 gap-3">

            @foreach ([['id' => 'hrChart', 'label' => 'Heart Rate', 'unit' => 'bpm'], ['id' => 'spo2Chart', 'label' => 'SpO2', 'unit' => '%'], ['id' => 'tempChart', 'label' => 'Temperature', 'unit' => '°C']] as $chart)
                <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                    <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                        <p class="text-sm font-medium text-[rgb(0,62,48)]">{{ $chart['label'] }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $chart['unit'] }} — 10 menit terakhir</p>
                    </div>
                    {{-- position:relative wajib agar Chart.js bisa hitung tinggi --}}
                    <div class="p-4 relative" style="height:200px;">
                        <canvas id="{{ $chart['id'] }}"></canvas>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- Instruksi dari Dokter --}}
        <div class="mt-4 mx-auto" x-data="instruksiNakes()" x-init="init()">
            <div
                class="bg-white rounded-2xl border border-[rgba(0,83,63,0.1)] shadow-sm overflow-hidden flex flex-col h-[80vh]">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-[rgba(0,83,63,0.08)] bg-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[rgb(0,62,48)]">Monitoring Ambulans: <span
                                x-text="deviceId"></span></h2>
                        <p class="text-[11px] text-gray-400">Lapor kejadian dan konfirmasi instruksi dokter</p>
                    </div>
                </div>

                {{-- Chat Area dengan x-ref untuk Scroll --}}
                <div x-ref="chatBox" class="flex-1 overflow-y-auto p-6 flex flex-col gap-6 bg-gray-50/30 scroll-smooth">
                    <template x-if="instruksi.length === 0">
                        <div class="flex flex-col items-center justify-center h-full opacity-40">
                            <p class="text-sm italic">Belum ada aktivitas instruksi.</p>
                        </div>
                    </template>

                    <template x-for="item in sortedInstruksi" :key="item.id">
                        <div class="flex flex-col gap-3">

                            {{-- 1. Laporan Nakes / Anda Sendiri (Sisi Kanan) --}}
                            <div class="flex justify-end items-end gap-2"
                                x-show="item.laporan_nakes && item.laporan_nakes !== '-'">
                                <div
                                    class="max-w-[80%] bg-[rgb(0,83,63)] text-white p-4 rounded-2xl transition-all rounded-tr-none shadow-md relative">

                                    <p class="text-sm leading-relaxed mb-2" x-text="item.laporan_nakes"></p>

                                    {{-- Waktu Pojok Bawah --}}
                                    <span class="absolute bottom-1 right-3 text-[9px] opacity-60"
                                        x-text="item.waktu"></span>

                                </div>
                            </div>

                            {{-- 2. Instruksi Dokter (Sisi Kiri) --}}
                            <template x-if="item.instruksi_dokter">
                                <div class="flex justify-start items-end gap-2">
                                    {{-- Avatar Dokter --}}
                                    <div
                                        class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 mb-1">
                                        DR
                                    </div>

                                    <div :class="item.is_completed ? 'bg-white opacity-90' : 'bg-white border-l-4 border-green-500'"
                                        class="max-w-[75%] bg-white border border-gray-200 p-3 rounded-2xl rounded-bl-none shadow-sm relative transition-all">

                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-[10px] font-bold text-green-900 uppercase tracking-tighter"
                                                x-text="item.user_name || 'DOKTER SATS'"></span>
                                        </div>

                                        <p class="text-sm text-gray-800 font-normal mb-2" x-text="item.instruksi_dokter">
                                        </p>

                                        {{-- Waktu Pojok Bawah --}}
                                        <span class="absolute bottom-1 right-3 text-[9px] text-gray-400"
                                            x-text="item.waktu || ''"></span>

                                        {{-- Area Respon (Tombol Tindakan) --}}
                                        <div class="mt-1 pt-3 border-t border-dashed border-gray-100">
                                            <template x-if="!item.is_completed">
                                                <div class="flex flex-col gap-2">
                                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Respon Anda:
                                                    </p>
                                                    <div class="flex flex-wrap gap-2">
                                                        <template
                                                            x-for="opsi in ['Sudah dilakukan', 'Alat tidak ada', 'Gagal']">
                                                            <button @click="item.selectedRespon = opsi; kirimRespon(item)"
                                                                class="text-[10px] px-3 py-1.5 rounded-full border border-green-100 bg-green-50 text-green-900 font-bold hover:bg-green-600 hover:text-white transition-all">
                                                                <span x-text="opsi"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Jika Sudah Selesai --}}
                                            <template x-if="item.is_completed">
                                                <div
                                                    class="flex items-center gap-2 text-green-900 mb-2 bg-emerald-50 p-2 rounded-lg border border-emerald-100">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z">
                                                        </path>
                                                    </svg>
                                                    <span class="text-[10px] font-bold tracking-wide"
                                                        x-text="'DIKONFIRMASI: ' + item.respon_nakes"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>

                {{-- Input Bar (Bottom) --}}
                <div class="px-5 py-3.5 border-t border-[rgba(0,83,63,0.08)] bg-gray-50/50">
                    <div class="flex gap-3">
                        <textarea x-model="laporanBaru" rows="1" @input="autoResize($el)"
                            placeholder="Ctrl + Enter untuk kirim"
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                            @keydown.ctrl.enter="kirimLaporan()"></textarea>
                        <button @click="kirimLaporan()" :disabled="!laporanBaru.trim() || isSending"
                            class="self-end p-2.5 bg-[rgb(0,83,63)] text-white rounded-xl hover:opacity-90 disabled:opacity-40 transition-all shadow-md">
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
                    selectedDeviceId: null,
                    latest: null,
                    deviceOnline: false,

                    async init() {
                        initCharts();
                        try {
                            const res = await fetch('/api/devices');
                            const json = await res.json();
                            if (json.success && json.data.length > 0) {
                                const device = json.data[0];
                                this.selectedDeviceId = device.device_id;
                                globalSelectedDeviceId = this.selectedDeviceId;
                                this.latest = device.latest;
                                this.deviceOnline = device.status === 'online';
                                updateCharts(device.history);
                                window.dispatchEvent(new CustomEvent('deviceSelected', {
                                    detail: { deviceId: this.selectedDeviceId }
                                }));
                                this.setupRealtime();
                            }
                        } catch (e) {
                            console.error('Error fetching devices:', e);
                        }
                    },

                    setupRealtime() {
                        if (!window.Echo || !this.selectedDeviceId) return;
                        window.Echo.private(`device.${this.selectedDeviceId}`)
                            .listen('.device.status.changed', (e) => {
                                this.deviceOnline = e.status === 'online';
                            })
                            .listen('.sensor.data.received', (e) => {
                                this.latest = e.latest;
                                updateCharts(e.history);
                            });
                    },

                    async toggleDevice() {
                        const newStatus = this.deviceOnline ? 'offline' : 'online';
                        this.deviceOnline = newStatus === 'online';
                        try {
                            const res = await fetch('/nakes/device-status', {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({ status: newStatus }),
                            });
                            const json = await res.json();
                            if (!json.success) {
                                this.deviceOnline = newStatus !== 'online';
                            }
                        } catch (e) {
                            this.deviceOnline = newStatus !== 'online';
                            console.error('Error toggling device:', e);
                        }
                    },

                    destroy() {
                        if (this.selectedDeviceId && window.Echo) {
                            window.Echo.leave(`device.${this.selectedDeviceId}`);
                        }
                    }
                };
            }

            function instruksiNakes() {
                return {
                    instruksi: [],
                    laporanBaru: '',
                    isSending: false,
                    deviceId: null,
                    notificationSound: new Audio('/assets/sounds/notification.mp3'),

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

                    async init() {
                        // Listen ketika device dipilih dari dashboard
                        window.addEventListener('deviceSelected', async (e) => {
                            const deviceId = e.detail.deviceId;
                            this.deviceId = deviceId;
                            console.log('instruksiNakes: Device changed to:', deviceId);
                            await this.getHistory();
                            this.setupReverb();
                            setTimeout(() => this.scrollToBottom(), 300);
                        });
                    },

                    setupReverb() {
                        if (!window.Echo) return;
                        if (!this.deviceId) {
                            console.warn('instruksiNakes: deviceId not set yet');
                            return;
                        }

                        console.log('instruksiNakes: Subscribe ke channel device.' + this.deviceId);

                        window.Echo.private(`device.${this.deviceId}`)
                            .listen('.instruction.created', (e) => {
                                const exists = this.instruksi.some(i => i.id === e.instruction.id);
                                if (!exists) {
                                    this.instruksi.push({
                                        ...e.instruction,
                                        selectedRespon: ''
                                    });
                                    this.notificationSound.play().catch(() => {});
                                    this.scrollToBottom();
                                }
                            });
                    },

                    async getHistory() {
                        if (!this.deviceId) {
                            console.warn('instruksiNakes: deviceId not set yet');
                            return;
                        }

                        try {
                            const res = await fetch(`/api/instruction?device_id=${this.deviceId}`);
                            const json = await res.json();
                            console.log(`[getHistory] device=${this.deviceId}:`, json);
                            if (json.success) {
                                this.instruksi = json.data.map(item => ({
                                    ...item,
                                    selectedRespon: ''
                                }));
                            }
                        } catch (e) {
                            console.error('History Error:', e);
                        }
                    },

                    async kirimLaporan() {
                        if (!this.laporanBaru.trim() || this.isSending || !this.deviceId) return;

                        // Waktu real-time untuk local update
                        this.isSending = true;
                        const sekarang = new Date().toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });

                        try {
                            const res = await fetch(`/api/instruction/report`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    device_id: this.deviceId,
                                    laporan_nakes: this.laporanBaru,
                                    waktu: sekarang
                                })
                            });

                            const json = await res.json();
                            if (json.success) {
                                this.laporanBaru = '';
                                this.instruksi.push(json.data);
                                this.scrollToBottom();

                                // Reset textarea height
                                const ta = document.querySelector('textarea');
                                if (ta) ta.style.height = 'auto';
                            }

                            this.laporanBaru = '';
                            this.$nextTick(() => {
                                const el = document.querySelector('textarea');
                                if (el) el.style.height = 'auto';
                            });
                        } catch (e) {
                            console.error('Laporan Error:', e);
                        } finally {
                            this.isSending = false;
                        }
                    },

                    async kirimRespon(item) {
                        if (!item.selectedRespon || !this.deviceId) return;

                        try {
                            const res = await fetch(`/api/instruction/${item.id}/complete`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    respon_nakes: item.selectedRespon
                                })
                            });

                            const json = await res.json();
                            if (json.success) {
                                item.is_completed = true;
                                item.respon_nakes = item.selectedRespon;
                                this.instruksi = [...this.instruksi];
                                this.scrollToBottom();
                            }
                        } catch (e) {
                            console.error('Respon Error:', e);
                        } finally {
                            this.isSending = false;
                        }
                    }
                }
            }
        </script>
    @endpush
@endsection
