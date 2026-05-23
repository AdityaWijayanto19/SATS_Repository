@extends('layouts.app')
@section('title', 'SATS Monitoring - Dashboard')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]" x-data="dashboard()" x-init="init()">

        {{-- Header: Selalu Muncul --}}
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

        {{-- Probabilitas Kondisi Pasien --}}
        <div x-show="latest?.ml_probabilities" x-transition
            class="grid grid-cols-3 gap-3 mb-4">

            {{-- Membaik --}}
            <div class="bg-green-50 rounded-xl p-4 border border-green-200 text-center">
                <p class="text-xs font-medium text-green-500 mb-1">Membaik</p>
                <p class="text-3xl font-bold text-green-600"
                    x-text="(latest?.ml_probabilities?.membaik ?? '—') + (latest?.ml_probabilities?.membaik != null ? '%' : '')"></p>
                <div class="mt-2 w-full bg-green-100 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500"
                        :style="'width:' + (latest?.ml_probabilities?.membaik ?? 0) + '%'"></div>
                </div>
            </div>

            {{-- Stabil --}}
            <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200 text-center">
                <p class="text-xs font-medium text-yellow-500 mb-1">Stabil</p>
                <p class="text-3xl font-bold text-yellow-600"
                    x-text="(latest?.ml_probabilities?.stabil ?? '—') + (latest?.ml_probabilities?.stabil != null ? '%' : '')"></p>
                <div class="mt-2 w-full bg-yellow-100 rounded-full h-1.5">
                    <div class="bg-yellow-500 h-1.5 rounded-full transition-all duration-500"
                        :style="'width:' + (latest?.ml_probabilities?.stabil ?? 0) + '%'"></div>
                </div>
            </div>

            {{-- Memburuk --}}
            <div class="bg-red-50 rounded-xl p-4 border border-red-200 text-center">
                <p class="text-xs font-medium text-red-400 mb-1">Memburuk</p>
                <p class="text-3xl font-bold text-red-500"
                    x-text="(latest?.ml_probabilities?.memburuk ?? '—') + (latest?.ml_probabilities?.memburuk != null ? '%' : '')"></p>
                <div class="mt-2 w-full bg-red-100 rounded-full h-1.5">
                    <div class="bg-red-500 h-1.5 rounded-full transition-all duration-500"
                        :style="'width:' + (latest?.ml_probabilities?.memburuk ?? 0) + '%'"></div>
                </div>
            </div>

        </div>

        {{-- Grafik Sensor --}}
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-medium text-[rgb(0,62,48)]">Grafik Vital Sign</p>
            <div class="flex bg-gray-100 rounded-lg p-0.5">
                <button @click="chartMode = 'separate'"
                    class="px-3 py-1 text-xs font-medium rounded-md transition cursor-pointer"
                    :class="chartMode === 'separate' ? 'bg-white text-[rgb(0,62,48)] shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                    Terpisah
                </button>
                <button @click="chartMode = 'combined'; $nextTick(() => initCombinedChart())"
                    class="px-3 py-1 text-xs font-medium rounded-md transition cursor-pointer"
                    :class="chartMode === 'combined' ? 'bg-white text-[rgb(0,62,48)] shadow-sm' : 'text-gray-500 hover:text-gray-700'">
                    Gabungan
                </button>
            </div>
        </div>

        {{-- Charts: Terpisah --}}
        <div x-show="chartMode === 'separate'" class="grid grid-cols-3 gap-3">
            @foreach ([['id' => 'hrChart', 'label' => 'Heart Rate', 'unit' => 'bpm'], ['id' => 'spo2Chart', 'label' => 'SpO2', 'unit' => '%'], ['id' => 'tempChart', 'label' => 'Temperature', 'unit' => '°C']] as $chart)
                <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                    <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                        <p class="text-sm font-medium text-[rgb(0,62,48)]">{{ $chart['label'] }}</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">{{ $chart['unit'] }} — 10 menit terakhir</p>
                    </div>
                    <div class="p-4 relative" style="height:200px;">
                        <canvas id="{{ $chart['id'] }}"></canvas>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Chart: Gabungan --}}
        <div x-show="chartMode === 'combined'" x-cloak>
            <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                    <p class="text-sm font-medium text-[rgb(0,62,48)]">Heart Rate, SpO2 & Temperature</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">bpm / % / °C — 10 menit terakhir</p>
                </div>
                <div class="p-4 relative" style="height:260px;">
                    <canvas id="chartVitalSignCombined"></canvas>
                </div>
                <div class="flex justify-center gap-5 pb-3 text-xs text-gray-500">
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#ef4444"></span> Heart Rate</span>
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#3b82f6"></span> SpO2</span>
                    <span class="flex items-center gap-1"><span class="inline-block w-3 h-3 rounded-full" style="background:#f59e0b"></span> Temperature</span>
                </div>
            </div>
        </div>

    </main>

    @include('components.chat-widget')

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            // Global state untuk share data antar Alpine components
            let globalSelectedDeviceId = null;

            // Chart instances
            let hrChart, spo2Chart, tempChart, combinedChart;

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

            function initCombinedChart() {
                if (combinedChart) return;
                const canvas = document.getElementById('chartVitalSignCombined');
                if (!canvas) return;
                combinedChart = new Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: [],
                        datasets: [
                            {
                                label: 'Heart Rate (bpm)',
                                data: [],
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,0.05)',
                                borderWidth: 1.5,
                                pointRadius: 2,
                                tension: 0.4,
                                yAxisID: 'y',
                            },
                            {
                                label: 'SpO2 (%)',
                                data: [],
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.05)',
                                borderWidth: 1.5,
                                pointRadius: 2,
                                tension: 0.4,
                                yAxisID: 'y1',
                            },
                            {
                                label: 'Temperature (°C)',
                                data: [],
                                borderColor: '#f59e0b',
                                backgroundColor: 'rgba(245,158,11,0.05)',
                                borderWidth: 1.5,
                                pointRadius: 2,
                                tension: 0.4,
                                yAxisID: 'y2',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: { duration: 300 },
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0 } },
                            y: {
                                position: 'left',
                                min: 40, max: 160,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: { font: { size: 9 }, color: '#ef4444' },
                                title: { display: true, text: 'bpm', font: { size: 9 }, color: '#ef4444' }
                            },
                            y1: {
                                position: 'right',
                                min: 80, max: 100,
                                grid: { drawOnChartArea: false },
                                ticks: { font: { size: 9 }, color: '#3b82f6' },
                                title: { display: true, text: '%', font: { size: 9 }, color: '#3b82f6' }
                            },
                            y2: {
                                position: 'right',
                                min: 34, max: 41,
                                grid: { drawOnChartArea: false },
                                ticks: { font: { size: 9 }, color: '#f59e0b' },
                                title: { display: true, text: '°C', font: { size: 9 }, color: '#f59e0b' },
                                offset: true
                            }
                        },
                        elements: { point: { radius: 2, hoverRadius: 4 }, line: { tension: 0.3, borderWidth: 1.5 } }
                    }
                });
                // Load existing data if available
                if (hrChart?.data?.labels?.length > 0) {
                    combinedChart.data.labels = hrChart.data.labels;
                    combinedChart.data.datasets[0].data = hrChart.data.datasets[0].data;
                    combinedChart.data.datasets[1].data = spo2Chart.data.datasets[0].data;
                    combinedChart.data.datasets[2].data = tempChart.data.datasets[0].data;
                    combinedChart.update('none');
                }
            }

            function updateCharts(history) {
                if (!history) {
                    [hrChart, spo2Chart, tempChart, combinedChart].forEach(c => {
                        if (c) { c.data.labels = []; c.data.datasets.forEach(ds => ds.data = []); c.update('none'); }
                    });
                    return;
                }
                hrChart.data.labels = history.labels; hrChart.data.datasets[0].data = history.heart_rate; hrChart.update('none');
                spo2Chart.data.labels = history.labels; spo2Chart.data.datasets[0].data = history.spo2; spo2Chart.update('none');
                tempChart.data.labels = history.labels; tempChart.data.datasets[0].data = history.temperature; tempChart.update('none');
                if (combinedChart) {
                    combinedChart.data.labels = history.labels;
                    combinedChart.data.datasets[0].data = history.heart_rate;
                    combinedChart.data.datasets[1].data = history.spo2;
                    combinedChart.data.datasets[2].data = history.temperature;
                    combinedChart.update('none');
                }
            }

            function dashboard() {
                return {
                    selectedDeviceId: null,
                    latest: null,
                    deviceOnline: false,
                    chartMode: 'separate',

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

                    getStatusClass(v, t) {
                        if (!v) return 'text-gray-400';
                        if (t === 'hr') return (v < 60 || v > 100) ? 'text-red-500' : 'text-green-500';
                        if (t === 'spo2') return (v < 95) ? 'text-red-500' : 'text-green-500';
                        if (t === 'temp') return (v < 36 || v > 37.5) ? 'text-orange-500' : 'text-green-500';
                    },

                    getStatusText(v, t) {
                        if (!v) return '—';
                        if (t === 'hr') return (v < 60 || v > 100) ? 'Abnormal' : 'Normal';
                        if (t === 'spo2') return (v < 95) ? 'Rendah' : 'Normal';
                        if (t === 'temp') return (v < 36 || v > 37.5) ? 'Abnormal' : 'Normal';
                    },

                    destroy() {
                        if (this.selectedDeviceId && window.Echo) {
                            window.Echo.leave(`device.${this.selectedDeviceId}`);
                        }
                    }
                };
            }

        </script>
    @endpush
@endsection
