@extends('layouts.app')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]" x-data="dashboard()" x-init="init()">

        {{-- Header & Dropdown --}}
        <div class="flex items-start justify-between mb-5">
            <div>
                <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring Dokter</h1>
                <p class="text-sm text-gray-400 mt-1" x-text="selectedDeviceId ? 'Monitoring: ' + selectedDeviceId : 'Pilih perangkat untuk memulai'"></p>
            </div>

            <div class="relative">
                <button @click="dropdownOpen = !dropdownOpen"
                    class="cursor-pointer px-4 py-2 bg-[rgb(0,62,48)] text-white rounded-lg flex items-center gap-2 text-sm hover:opacity-90 transition">
                    <span x-text="selectedDeviceId || 'Pilih Perangkat'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="dropdownOpen" @click.outside="dropdownOpen = false" x-transition
                    class="absolute right-0 mt-2 w-52 bg-white border border-[rgba(0,83,63,0.15)] rounded-xl shadow-sm z-50 overflow-hidden">
                    <template x-for="device in allDevices" :key="device.device_id">
                        <div @click="selectDevice(device.device_id); dropdownOpen = false"
                            class="px-4 py-2.5 text-sm hover:bg-[rgba(0,83,63,0.06)] cursor-pointer"
                            :class="device.device_id === selectedDeviceId ? 'text-[rgb(0,62,48)] font-bold bg-green-50' : 'text-[rgb(0,62,48)]'"
                            x-text="device.device_id"></div>
                    </template>
                </div>
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
                <p class="text-[10px] mt-1" :class="getStatusClass(latest?.heart_rate, 'hr')" x-text="getStatusText(latest?.heart_rate, 'hr')"></p>
            </div>

            {{-- SpO2 --}}
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                <p class="text-xs font-medium text-blue-400 mb-2">SpO2</p>
                <p class="text-3xl font-medium text-[rgb(0,62,48)]">
                    <span x-text="latest?.spo2 ?? '—'"></span>
                    <span class="text-sm font-normal text-blue-400">%</span>
                </p>
                <p class="text-[10px] mt-1" :class="getStatusClass(latest?.spo2, 'spo2')" x-text="getStatusText(latest?.spo2, 'spo2')"></p>
            </div>

            {{-- Temperature --}}
            <div class="bg-orange-50 rounded-xl p-4 border border-orange-200">
                <p class="text-xs font-medium text-orange-400 mb-2">Temperature</p>
                <p class="text-3xl font-medium text-[rgb(0,62,48)]">
                    <span x-text="latest?.temperature ?? '—'"></span>
                    <span class="text-sm font-normal text-orange-400">°C</span>
                </p>
                <p class="text-[10px] mt-1" :class="getStatusClass(latest?.temperature, 'temp')" x-text="getStatusText(latest?.temperature, 'temp')"></p>
            </div>

            {{-- Kondisi Pasien --}}
            <div class="bg-[rgba(0,83,63,0.05)] rounded-xl p-4 border border-[rgba(0,83,63,0.2)]">
                <p class="text-xs font-medium text-[rgb(0,62,48)] mb-2">Kondisi Pasien</p>
                <p class="text-2xl font-medium" :class="{'text-[rgb(0,62,48)]': latest?.status === 'normal', 'text-orange-500': latest?.status === 'warning', 'text-red-500': latest?.status === 'critical'}"
                    x-text="latest?.status ? (latest.status.charAt(0).toUpperCase() + latest.status.slice(1)) : '—'"></p>
                <p class="text-[10px] text-[rgba(0,62,48,0.5)] mt-1">Update: <span x-text="formatTime(latest?.created_at) || '—'"></span></p>
            </div>
        </div>

        {{-- Prediksi ML --}}
        <div class="flex items-center gap-4 bg-[rgba(0,62,48,0.05)] border border-[rgba(0,62,48,0.18)] rounded-xl px-5 py-3.5 mb-4">
            <span class="w-2 h-2 rounded-full bg-orange-400 flex-shrink-0"></span>
            <div class="flex-1">
                <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Prediksi ML</p>
                <p class="text-sm font-medium text-[rgb(0,62,48)]">
                    <span x-show="latest?.prediction" x-text="latest?.prediction"></span>
                    <span x-show="!latest?.prediction">Data prediksi belum tersedia.</span>
                </p>
            </div>
            <span class="text-[10px] font-medium px-2.5 py-1 rounded flex-shrink-0"
                :class="{'bg-green-100 text-green-700': latest?.status === 'normal', 'bg-orange-100 text-orange-700': latest?.status === 'warning', 'bg-red-100 text-red-700': latest?.status === 'critical'}"
                x-text="latest?.status === 'normal' ? 'Aman' : (latest?.status === 'warning' ? 'Perhatian' : (latest?.status === 'critical' ? 'Kritis' : '—'))">
            </span>
        </div>

        {{-- Grafik --}}
        <div class="grid grid-cols-3 gap-3">
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
    </main>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        function dashboard() {
            return {
                allDevices: [],
                selectedDeviceId: localStorage.getItem('selectedMonitoringDeviceDoc') || null,
                dropdownOpen: false,
                latest: null,
                chartData: { heartRate: [], spo2: [], temperature: [], labels: [] },
                charts: { hrChart: null, spo2Chart: null, tempChart: null },
                maxDataPoints: 30,
                notificationSound: new Audio('/assets/sounds/notification.mp3'),

                async init() {
                    await this.loadDevices();
                    if (this.selectedDeviceId) {
                        await this.loadHistory();
                        this.$nextTick(() => {
                            this.initCharts();
                            this.setupWebSocket();
                        });
                    }
                },

                formatTime(dateString) {
                    if (!dateString) return '';
                    let date = new Date(dateString);
                    if (isNaN(date.getTime())) date = new Date(dateString.replace(" ", "T"));
                    if (isNaN(date.getTime())) return dateString.match(/\d{2}:\d{2}/)?.[0] || dateString;
                    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                },

                async selectDevice(deviceId) {
                    this.selectedDeviceId = deviceId;
                    localStorage.setItem('selectedMonitoringDeviceDoc', deviceId);
                    this.resetCharts();
                    await this.loadHistory();
                    this.$nextTick(() => {
                        this.initCharts();
                        this.setupWebSocket();
                    });
                },

                async loadDevices() {
                    try {
                        const res = await fetch('/api/device?limit=100');
                        const json = await res.json();
                        if (json.success) {
                            this.allDevices = json.data;
                            if (this.allDevices.length > 0 && !this.selectedDeviceId) {
                                this.selectedDeviceId = this.allDevices[0].device_id;
                            }
                        }
                    } catch (e) { console.error('Error load devices:', e); }
                },

                async loadHistory() {
                    if (!this.selectedDeviceId) return;
                    try {
                        const [resHistory, resLatest] = await Promise.all([
                            fetch(`/api/device/${this.selectedDeviceId}/sensor-data/history?minutes=1440`),
                            fetch(`/api/device/${this.selectedDeviceId}/sensor-data/latest`)
                        ]);
                        const jsonHistory = await resHistory.json();
                        const jsonLatest = await resLatest.json();

                        if (jsonHistory.success && jsonHistory.data) {
                            const d = jsonHistory.data;
                            // Jika API mengembalikan objek berisi array (seperti case sebelumnya)
                            if (d.labels) {
                                this.chartData.labels = d.labels.map(l => this.formatTime(l));
                                this.chartData.heartRate = d.heart_rate.map(v => parseFloat(v));
                                this.chartData.spo2 = d.spo2.map(v => parseFloat(v));
                                this.chartData.temperature = d.temperature.map(v => parseFloat(v));
                            }
                        }
                        if (jsonLatest.success) this.latest = jsonLatest.data;
                    } catch (e) { console.error('Error load history:', e); }
                },

                initCharts() {
                    this.resetCharts();
                    this.createChart('hrChart', 'Heart Rate', '#dc2626', [...this.chartData.heartRate]);
                    this.createChart('spo2Chart', 'SpO2', '#2563eb', [...this.chartData.spo2]);
                    this.createChart('tempChart', 'Temp', '#ea580c', [...this.chartData.temperature]);
                },

                createChart(canvasId, label, color, data) {
                    const ctx = document.getElementById(canvasId);
                    if (!ctx) return;
                    this.charts[canvasId] = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [...this.chartData.labels],
                            datasets: [{
                                label: label,
                                data: data,
                                borderColor: color,
                                backgroundColor: color + '15',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: false,
                            scales: {
                                y: {
                                    beginAtZero: false,
                                    suggestedMin: label.includes('SpO2') ? 80 : (label.includes('Temp') ? 34 : 40),
                                    suggestedMax: label.includes('SpO2') ? 100 : (label.includes('Temp') ? 40 : 120)
                                },
                                x: { ticks: { maxTicksLimit: 6 } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });
                },

                setupWebSocket() {
                    if (!window.Echo || !this.selectedDeviceId) return;
                    window.Echo.leaveAllChannels();
                    window.Echo.private(`device.${this.selectedDeviceId}`).listen('.sensor.received', (e) => {
                        this.onSensorDataReceived(e.sensor);
                    });
                },

                onSensorDataReceived(sensor) {
                    this.latest = { ...sensor, created_at: sensor.timestamp };
                    const time = this.formatTime(sensor.timestamp);

                    this.chartData.labels.push(time);
                    this.chartData.heartRate.push(parseFloat(sensor.heart_rate));
                    this.chartData.spo2.push(parseFloat(sensor.spo2));
                    this.chartData.temperature.push(parseFloat(sensor.temperature));

                    if (this.chartData.labels.length > this.maxDataPoints) {
                        this.chartData.labels.shift();
                        this.chartData.heartRate.shift();
                        this.chartData.spo2.shift();
                        this.chartData.temperature.shift();
                    }
                    this.updateCharts();
                },

                updateCharts() {
                    Object.keys(this.charts).forEach(key => {
                        if (this.charts[key]) {
                            this.charts[key].data.labels = [...this.chartData.labels];
                            const idx = key === 'hrChart' ? 'heartRate' : (key === 'spo2Chart' ? 'spo2' : 'temperature');
                            this.charts[key].data.datasets[0].data = [...this.chartData[idx]];
                            this.charts[key].update('none');
                        }
                    });
                },

                resetCharts() {
                    Object.keys(this.charts).forEach(key => {
                        if (this.charts[key]) { this.charts[key].destroy(); this.charts[key] = null; }
                    });
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
                }
            };
        }
    </script>
    @endpush
@endsection
