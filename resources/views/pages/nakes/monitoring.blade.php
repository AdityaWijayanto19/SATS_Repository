@extends('layouts.app')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]" x-data="dashboard()" x-init="init()">

        {{-- Modal Input API Key --}}
        <template x-if="!apiKey">
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-xl">
                    <h2 class="text-2xl font-semibold text-[rgb(0,62,48)] mb-2">Input API Key</h2>
                    <p class="text-sm text-gray-500 mb-6">Masukkan API Key yang telah diberikan oleh
                        superadmin untuk memulai
                        monitoring</p>

                    <form @submit.prevent="validateApiKey()" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-[rgb(0,62,48)] mb-2">API Key</label>
                            <input type="password" x-model="apiKeyInput" placeholder="sats_xxxxxxxxxxxxx"
                                class="w-full px-4 py-2.5 border border-[rgba(0,83,63,0.2)] rounded-lg focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent"
                                required>
                        </div>

                        <div x-show="apiKeyError" class="text-sm text-red-500 bg-red-50 p-3 rounded-lg">
                            <span x-text="apiKeyError"></span>
                        </div>

                        <button type="submit" :disabled="apiKeyLoading"
                            class="w-full bg-[rgb(0,62,48)] text-white py-2.5 rounded-lg font-medium hover:opacity-90 transition disabled:opacity-50"
                            x-text="apiKeyLoading ? 'Verifying...' : 'Verify API Key'">
                        </button>
                    </form>
                </div>
            </div>
        </template>

        {{-- Dashboard Content (shown only after API Key validation) --}}
        <div x-show="apiKey" x-cloak>
            <div>
                {{-- Header --}}
                <div class="flex items-start justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring
                        </h1>
                        <p class="text-sm text-gray-400 mt-1" x-text="selectedDeviceId ?? 'Belum ada perangkat'"></p>
                    </div>

                    <div class="flex items-center gap-3">
                        {{-- Dropdown Perangkat --}}
                        <div class="relative">

                            <div x-show="dropdownOpen" x-transition @click.outside="dropdownOpen = false"
                                class="absolute right-0 mt-2 w-52 bg-white border border-[rgba(0,83,63,0.15)] rounded-xl shadow-sm z-50 overflow-hidden">
                                <template x-for="device in allDevices" :key="device.device_id">
                                    <div @click="selectDevice(device.device_id); dropdownOpen = false"
                                        class="px-4 py-2.5 text-sm hover:bg-[rgba(0,83,63,0.06)] cursor-pointer"
                                        :class="device.device_id === selectedDeviceId ? 'text-[rgb(0,62,48)] font-semibold' :
                                            'text-[rgb(0,62,48)]'"
                                        x-text="device.device_id"></div>
                                </template>
                            </div>
                        </div>

                        {{-- Logout Button --}}
                        <button @click="logout()"
                            class="cursor-pointer px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:opacity-90 transition">
                            Logout Key
                        </button>
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
                            x-text="latest?.status ? (latest.status.charAt(0).toUpperCase() + latest.status.slice(1)) : '—'">
                        </p>
                        <p class="text-[10px] text-[rgba(0,62,48,0.5)] mt-1">
                            Pembaruan: <span x-text="latest?.created_at ?? '—'"></span>
                        </p>
                    </div>

                </div>

                {{-- Prediksi ML --}}
                <div
                    class="flex items-center gap-4 bg-[rgba(0,62,48,0.05)] border border-[rgba(0,62,48,0.18)] rounded-xl px-5 py-3.5 mb-4">
                    <span class="w-2 h-2 rounded-full bg-orange-400 flex-shrink-0"></span>

                    <div class="flex-1">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Prediksi ML</p>
                        <p class="text-sm font-medium text-[rgb(0,62,48)]">
                            <span x-show="latest?.prediction" x-text="latest?.prediction"></span>
                            <span x-show="!latest?.prediction">Data prediksi belum
                                tersedia.</span>
                        </p>
                    </div>

                    <span class="text-[10px] font-medium px-2.5 py-1 rounded flex-shrink-0"
                        :class="{
                            'bg-green-100 text-green-700': latest?.status === 'normal',
                            'bg-orange-100 text-orange-700': latest?.status === 'warning',
                            'bg-red-100 text-red-700': latest?.status === 'critical'
                        }"
                        x-text="statusLabel(latest?.status)"></span>
                </div>

                {{-- Grafik Sensor --}}
                <div class="grid grid-cols-3 gap-3">

                    @foreach ([['id' => 'hrChart', 'label' => 'Heart Rate', 'unit' => 'bpm'], ['id' => 'spo2Chart', 'label' => 'SpO2', 'unit' => '%'], ['id' => 'tempChart', 'label' => 'Temperature', 'unit' => '°C']] as $chart)
                        <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                            <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                                <p class="text-sm font-medium text-[rgb(0,62,48)]">
                                    {{ $chart['label'] }}</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    {{ $chart['unit'] }} — 10 menit terakhir</p>
                            </div>
                            {{-- position:relative wajib agar Chart.js bisa hitung tinggi --}}
                            <div class="p-4 relative" style="height:200px;">
                                <canvas id="{{ $chart['id'] }}"></canvas>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>

    </main>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>


        <script>
            function dashboard() {
                return {
                    // API Key Management
                    apiKey: (localStorage.getItem('monitoringApiKey') || '').trim() || null,
                    apiKeyInput: '',
                    apiKeyError: '',
                    apiKeyLoading: false,

                    // Device & Monitoring
                    allDevices: [],
                    selectedDeviceId: localStorage.getItem('selectedMonitoringDevice') || null,
                    dropdownOpen: false,
                    latest: null,
                    chartData: {
                        heartRate: [],
                        spo2: [],
                        temperature: [],
                        labels: []
                    },
                    charts: {
                        hrChart: null,
                        spo2Chart: null,
                        tempChart: null
                    },
                    maxDataPoints: 30,
                    notificationSound: new Audio('/assets/sounds/notification.mp3'),

                    buildHeaders() {
                        return this.apiKey ? {
                            'X-API-Key': this.apiKey.trim(),
                            'Accept': 'application/json'
                        } : {};
                    },

                    // Helper untuk format jam agar grafik rapi
                    formatTime(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        return date.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    },

                    handleUnauthorized(message = 'API Key tidak valid atau sudah expired.') {
                        this.apiKey = null;
                        this.selectedDeviceId = null;
                        this.allDevices = [];
                        this.latest = null;
                        this.resetCharts();
                        localStorage.removeItem('monitoringApiKey');
                        localStorage.removeItem('selectedMonitoringDevice');
                        this.apiKeyError = message;
                    },

                    async init() {
                        if (this.apiKey) {
                            const ok = await this.loadDevices();
                            if (ok && this.selectedDeviceId) {
                                await this.loadHistory();
                                this.$nextTick(() => {
                                    this.initCharts();
                                    this.setupWebSocket();
                                });
                            }
                        }
                    },

                    async validateApiKey() {
                        this.apiKeyError = '';
                        this.apiKeyLoading = true;
                        const normalizedKey = (this.apiKeyInput || '').trim();

                        try {
                            const res = await fetch('/api/device', {
                                headers: {
                                    'X-API-Key': normalizedKey
                                }
                            });
                            const json = await res.json();

                            if (json.success) {
                                this.apiKey = normalizedKey;
                                localStorage.setItem('monitoringApiKey', this.apiKey);
                                this.apiKeyInput = '';
                                await this.init();
                            } else {
                                this.apiKeyError = json.message || 'API Key salah.';
                            }
                        } catch (e) {
                            this.apiKeyError = 'Gagal terhubung ke server.';
                        } finally {
                            this.apiKeyLoading = false;
                        }
                    },

                    async selectDevice(deviceId) {
                        this.selectedDeviceId = deviceId;
                        localStorage.setItem('selectedMonitoringDevice', deviceId);
                        this.resetCharts();
                        await this.loadHistory();
                        this.$nextTick(() => {
                            this.initCharts();
                            this.setupWebSocket();
                        });
                    },

                    async loadDevices() {
                        try {
                            const res = await fetch('/api/device?limit=100', {
                                headers: this.buildHeaders()
                            });
                            const json = await res.json();

                            if (res.status === 401) return this.handleUnauthorized();

                            if (json.success) {
                                this.allDevices = json.data;
                                if (this.allDevices.length > 0 && !this.selectedDeviceId) {
                                    this.selectedDeviceId = this.allDevices[0].device_id;
                                }
                                return true;
                            }
                        } catch (e) {
                            console.error(e);
                        }
                        return false;
                    },

                    async loadHistory() {
                        if (!this.selectedDeviceId) return;
                        try {
                            const headers = this.buildHeaders();
                            // Gunakan 1440 menit (24 jam) agar data lama kamu muncul di grafik
                            const [resHistory, resLatest] = await Promise.all([
                                fetch(`/api/device/${this.selectedDeviceId}/sensor-data/history?minutes=1440`, {
                                    headers
                                }),
                                fetch(`/api/device/${this.selectedDeviceId}/sensor-data/latest`, {
                                    headers
                                })
                            ]);

                            const jsonHistory = await resHistory.json();
                            const jsonLatest = await resLatest.json();

                            // 1. Set data Latest dulu
                            if (jsonLatest.success && jsonLatest.data) {
                                this.latest = jsonLatest.data;
                            }

                            // 2. Olah data History
                            if (jsonHistory.success && jsonHistory.data) {
                                const d = jsonHistory.data;

                                // Cek apakah history memang ada isinya
                                if (d.labels && d.labels.length > 0) {
                                    this.chartData.labels = d.labels.map(l => this.formatTime(l));
                                    this.chartData.heartRate = d.heart_rate;
                                    this.chartData.spo2 = d.spo2;
                                    this.chartData.temperature = d.temperature;
                                }
                                // FALLBACK: Jika history kosong tapi ada data latest, masukkan data latest ke grafik
                                else if (this.latest) {
                                    console.log("History kosong, menggunakan data latest sebagai titik awal");
                                    this.chartData.labels = [this.formatTime(this.latest.created_at)];
                                    this.chartData.heartRate = [this.latest.heart_rate];
                                    this.chartData.spo2 = [this.latest.spo2];
                                    this.chartData.temperature = [this.latest.temperature];
                                }
                            }

                            console.log("FINAL CHART DATA HR:", JSON.parse(JSON.stringify(this.chartData.heartRate)));

                            this.$nextTick(() => this.initCharts());

                        } catch (e) {
                            console.error('History Error:', e);
                        }
                    },

                    initCharts() {
                        this.resetCharts();
                        // Gunakan spread operator [...this.chartData.xxx] agar data benar-benar terputus dari proxy Alpine
                        this.createChart('hrChart', 'Heart Rate', '#dc2626', [...this.chartData.heartRate]);
                        this.createChart('spo2Chart', 'SpO2', '#2563eb', [...this.chartData.spo2]);
                        this.createChart('tempChart', 'Temp', '#ea580c', [...this.chartData.temperature]);
                    },

                    createChart(canvasId, label, color, data) {
                        const canvas = document.getElementById(canvasId);
                        if (!canvas) return;

                        const ctx = canvas.getContext('2d');

                        this.charts[canvasId] = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: [...this.chartData.labels],
                                datasets: [{
                                    label: label,
                                    data: [...data], // Gunakan spread operator agar data murni
                                    borderColor: color,
                                    backgroundColor: color + '15',
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 2,
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: false, // Matikan animasi agar update real-time lancar
                                scales: {
                                    y: {
                                        beginAtZero: false,
                                        // Memaksa skala grafik agar terlihat bagus jika data sedikit
                                        suggestedMin: label === 'SpO2' ? 85 : (label === 'Temp' ? 34 : 50),
                                        suggestedMax: label === 'SpO2' ? 100 : (label === 'Temp' ? 40 : 120),
                                        grid: {
                                            color: 'rgba(0,0,0,0.05)'
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxTicksLimit: 6,
                                            maxRotation: 0
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    },

                    setupWebSocket() {
                        if (!window.Echo || !this.selectedDeviceId) return;
                        window.Echo.leaveAllChannels();

                        window.Echo.private(`device.${this.selectedDeviceId}`)
                            .listen('.sensor.received', (e) => {
                                this.onSensorDataReceived(e.sensor);
                            });
                    },

                    onSensorDataReceived(sensor) {
                        this.latest = {
                            ...sensor,
                            created_at: sensor.timestamp
                        };

                        const time = this.formatTime(sensor.timestamp);

                        // Push data baru
                        this.chartData.labels.push(time);
                        this.chartData.heartRate.push(sensor.heart_rate);
                        this.chartData.spo2.push(sensor.spo2);
                        this.chartData.temperature.push(sensor.temperature);

                        // Shift jika melebihi batas
                        if (this.chartData.labels.length > this.maxDataPoints) {
                            this.chartData.labels.shift();
                            this.chartData.heartRate.shift();
                            this.chartData.spo2.shift();
                            this.chartData.temperature.shift();
                        }

                        this.updateCharts();

                        if (sensor.status === 'warning' || sensor.status === 'critical') {
                            this.notificationSound.play().catch(() => {});
                        }
                    },

                    updateCharts() {
                        Object.keys(this.charts).forEach(key => {
                            if (this.charts[key]) {
                                this.charts[key].data.labels = [...this.chartData.labels];
                                if (key === 'hrChart') this.charts[key].data.datasets[0].data = [...this.chartData
                                    .heartRate
                                ];
                                if (key === 'spo2Chart') this.charts[key].data.datasets[0].data = [...this.chartData
                                    .spo2
                                ];
                                if (key === 'tempChart') this.charts[key].data.datasets[0].data = [...this.chartData
                                    .temperature
                                ];
                                this.charts[key].update('none');
                            }
                        });
                    },

                    resetCharts() {
                        Object.keys(this.charts).forEach(key => {
                            if (this.charts[key]) {
                                this.charts[key].destroy();
                                this.charts[key] = null;
                            }
                        });
                    },

                    logout() {
                        if (confirm('Logout dan hapus API Key?')) {
                            this.handleUnauthorized('');
                            window.location.reload();
                        }
                    },

                    getStatusClass(value, type) {
                        if (!value) return 'text-gray-400';
                        if (type === 'hr') return (value < 60 || value > 100) ? 'text-red-500' : 'text-green-500';
                        if (type === 'spo2') return (value < 95) ? 'text-red-500' : 'text-green-500';
                        if (type === 'temp') return (value < 36 || value > 37.5) ? 'text-orange-500' : 'text-green-500';
                        return 'text-gray-400';
                    },

                    getStatusText(value, type) {
                        if (!value) return '—';
                        if (type === 'hr') return (value < 60 || value > 100) ? 'Abnormal' : 'Normal';
                        if (type === 'spo2') return (value < 95) ? 'Rendah' : 'Normal';
                        if (type === 'temp') return (value < 36 || value > 37.5) ? 'Abnormal' : 'Normal';
                        return '—';
                    },

                    statusLabel(status) {
                        if (!status) return '—';
                        return status.charAt(0).toUpperCase() + status.slice(1);
                    }
                };
            }
        </script>
    @endpush
@endsection
