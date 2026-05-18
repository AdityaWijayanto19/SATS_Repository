@extends('layouts.app')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]" x-data="dashboard()" x-init="init()">

        {{-- Header: Selalu Muncul --}}
        <div class="flex items-start justify-between mb-5">
            <div>
                <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring</h1>
                <p class="text-sm text-gray-400 mt-1"
                    x-text="apiKey ? 'Monitoring Real-time ID: ' + (selectedDeviceId ?? '...') : 'Silakan verifikasi API Key untuk memulai'">
                </p>
            </div>

            {{-- Tombol Logout Key: Hanya muncul jika sudah terhubung --}}
            <template x-if="apiKey">
                <button @click="logout()"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition shadow-sm">
                    Logout Key
                </button>
            </template>
        </div>

        {{-- 1. Tampilan Verifikasi (Placeholder Content) --}}
        {{-- Muncul JIKA apiKey Belum Ada --}}
        <div x-show="!apiKey" x-transition:enter="transition ease-out duration-300"
            class="flex flex-col items-center justify-center min-h-[60vh] py-12">

            <div class="bg-white rounded-3xl p-10 w-full max-w-lg shadow-sm border border-[rgba(0,83,63,0.1)] text-center">
                <div class="w-20 h-20 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-[rgb(0,62,48)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-[rgb(0,62,48)] mb-2">Verifikasi Akses</h2>
                <p class="text-sm text-gray-500 mb-8 px-4">Masukkan API Key monitoring dari Superadmin untuk mengakses data
                    sensor real-time dan instruksi dokter.</p>

                <form @submit.prevent="validateApiKey()" class="space-y-4 text-left">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">API Key Monitoring</label>
                        <input type="password" x-model="apiKeyInput" placeholder="sats_xxxxxxxxxxxxx"
                            class="w-full px-5 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] transition-all"
                            required>
                    </div>

                    <div x-show="apiKeyError" x-cloak
                        class="text-xs text-red-500 bg-red-50 p-3 rounded-xl border border-red-100" x-text="apiKeyError">
                    </div>

                    <button type="submit" :disabled="apiKeyLoading"
                        class="w-full bg-[rgb(0,62,48)] text-white py-3.5 rounded-2xl font-bold hover:opacity-90 transition disabled:opacity-50 shadow-lg shadow-emerald-900/20">
                        <span x-show="!apiKeyLoading">Hubungkan Monitoring</span>
                        <span x-show="apiKeyLoading" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Memverifikasi...
                        </span>
                    </button>
                </form>
            </div>
        </div>

        {{-- 2. Konten Utama (Monitoring & Chat) --}}
        {{-- Muncul JIKA apiKey SUDAH Ada --}}
        <div x-show="apiKey" x-cloak x-transition:enter="transition ease-out duration-500">

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
                    <p class="text-2xl font-medium" :class="getKondisiClass(latest?.status)"
                        x-text="statusLabel(latest?.status)"></p>
                    <p class="text-[10px] text-[rgba(0,62,48,0.5)] mt-1">Update: <span
                            x-text="formatTime(latest?.created_at)"></span></p>
                </div>
            </div>

            {{-- Prediksi ML --}}
            <div
                class="flex items-center gap-4 bg-white border border-[rgba(0,62,48,0.1)] rounded-xl px-5 py-3.5 mb-4 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-orange-400 animate-pulse"></span>
                <div class="flex-1">
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Analisis Prediksi ML
                    </p>
                    <p class="text-sm font-medium text-[rgb(0,62,48)]"
                        x-text="latest?.prediction ?? 'Menunggu data sensor...'"></p>
                </div>
            </div>

            {{-- Grafik Sensor (3 Kolom) --}}
            <div class="grid grid-cols-3 gap-3 mb-6">
                @foreach ([['id' => 'hrChart', 'label' => 'Heart Rate', 'unit' => 'bpm'], ['id' => 'spo2Chart', 'label' => 'SpO2', 'unit' => '%'], ['id' => 'tempChart', 'label' => 'Temperature', 'unit' => '°C']] as $chart)
                    <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)] shadow-sm">
                        <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.05)]">
                            <p class="text-xs font-bold text-[rgb(0,62,48)] uppercase">{{ $chart['label'] }}</p>
                        </div>
                        <div class="p-4 relative" style="height:180px;">
                            <canvas id="{{ $chart['id'] }}"></canvas>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Instruksi & Chat --}}
            <div
                class="bg-white rounded-2xl border border-[rgba(0,83,63,0.1)] shadow-sm overflow-hidden flex flex-col h-[80vh]">
                <div class="px-6 py-4 border-b border-[rgba(0,83,63,0.08)] bg-white flex items-center justify-between">
                    <h2 class="text-sm font-bold text-[rgb(0,62,48)] uppercase tracking-tight">Instruksi & Laporan Medis
                    </h2>
                </div>

                <div x-ref="chatBox" class="flex-1 overflow-y-auto p-6 flex flex-col gap-4 bg-gray-50/30">
                    <template x-for="item in sortedInstruksi" :key="item.id">
                        <div class="flex flex-col gap-2">
                            <div class="flex justify-end" x-show="item.laporan_nakes && item.laporan_nakes !== '-'">
                                <div
                                    class="max-w-[80%] bg-[rgb(0,83,63)] text-white p-3 rounded-2xl rounded-tr-none text-sm shadow-md">
                                    <p x-text="item.laporan_nakes"></p>
                                    <span class="text-[9px] opacity-60 block mt-1 text-right" x-text="item.waktu"></span>
                                </div>
                            </div>
                            <div class="flex justify-start items-end gap-2" x-show="item.instruksi_dokter">
                                <div
                                    class="w-7 h-7 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[9px] font-bold">
                                    DR</div>
                                <div
                                    class="max-w-[80%] bg-white border border-gray-200 p-3 rounded-2xl rounded-bl-none shadow-sm">
                                    <p class="text-[10px] font-bold text-emerald-800 mb-1"
                                        x-text="item.user_name || 'DOKTER SATS'"></p>
                                    <p class="text-sm text-gray-800" x-text="item.instruksi_dokter"></p>
                                    <div class="mt-3 pt-2 border-t border-dashed border-gray-100">
                                        <template x-if="!item.is_completed">
                                            <div class="flex flex-wrap gap-2">
                                                <template x-for="opsi in ['Sudah dilakukan', 'Alat tidak ada', 'Gagal']">
                                                    <button @click="kirimRespon(item, opsi)"
                                                        class="text-[9px] px-3 py-1 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-800 font-bold hover:bg-emerald-600 hover:text-white transition-all">
                                                        <span x-text="opsi"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="item.is_completed">
                                            <div
                                                class="flex items-center gap-1 text-emerald-700 text-[10px] font-bold bg-emerald-50 p-1.5 rounded-lg border border-emerald-100">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z">
                                                    </path>
                                                </svg>
                                                <span x-text="'DIKONFIRMASI: ' + item.respon_nakes"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="px-5 py-3.5 border-t border-[rgba(0,83,63,0.08)] bg-gray-50/50">
                    <div class="flex gap-3">
                        <textarea x-model="laporanBaru" placeholder="Ctrl + Enter untuk kirim" rows="1"
                            class="flex-1 px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 outline-none resize-none"
                            @keydown.ctrl.enter="kirimLaporan()"></textarea>
                        <button @click="kirimLaporan()" :disabled="!laporanBaru.trim() || isSending"
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
            function dashboard() {
                return {
                    apiKey: localStorage.getItem('monitoringApiKey') || null,
                    apiKeyInput: '',
                    apiKeyError: '',
                    apiKeyLoading: false,
                    selectedDeviceId: localStorage.getItem('selectedMonitoringDevice') || null,
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
                    instruksi: [],
                    laporanBaru: '',
                    isSending: false,
                    notificationSound: new Audio('/assets/sounds/notification.mp3'),

                    buildHeaders() {
                        return {
                            'X-API-Key': this.apiKey,
                            'Accept': 'application/json'
                        };
                    },
                    formatTime(ds) {
                        if (!ds) return '';
                        return new Date(ds).toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    },

                    async init() {
                        if (this.apiKey) {
                            const success = await this.fetchAndSetDevice();
                            if (success && this.selectedDeviceId) {
                                await this.loadAllData();
                            }
                        }
                    },

                    async validateApiKey() {
                        this.apiKeyError = '';
                        this.apiKeyLoading = true;
                        try {
                            const res = await fetch('/api/device', {
                                headers: {
                                    'X-API-Key': this.apiKeyInput.trim()
                                }
                            });
                            const json = await res.json();
                            if (json.success && json.data.length > 0) {
                                this.apiKey = this.apiKeyInput.trim();
                                localStorage.setItem('monitoringApiKey', this.apiKey);
                                this.selectedDeviceId = json.data[0].device_id;
                                localStorage.setItem('selectedMonitoringDevice', this.selectedDeviceId);
                                await this.loadAllData();
                            } else {
                                this.apiKeyError = (json.data && json.data.length === 0) ?
                                    'Key tidak terhubung ke perangkat.' : 'API Key tidak valid.';
                            }
                        } catch (e) {
                            this.apiKeyError = 'Gagal terhubung ke server.';
                        } finally {
                            this.apiKeyLoading = false;
                        }
                    },

                    async fetchAndSetDevice() {
                        try {
                            const res = await fetch('/api/device', {
                                headers: this.buildHeaders()
                            });
                            const json = await res.json();
                            if (res.status === 401) {
                                this.logout();
                                return false;
                            }
                            if (json.success && json.data.length > 0) {
                                this.selectedDeviceId = json.data[0].device_id;
                                return true;
                            }
                        } catch (e) {
                            console.error(e);
                        }
                        return false;
                    },

                    async loadAllData() {
                        this.resetCharts();
                        try {
                            const [resHist, resLat, resChat] = await Promise.all([
                                fetch(`/api/device/${this.selectedDeviceId}/sensor-data/history?minutes=1440`, {
                                    headers: this.buildHeaders()
                                }),
                                fetch(`/api/device/${this.selectedDeviceId}/sensor-data/latest`, {
                                    headers: this.buildHeaders()
                                }),
                                fetch(`/api/instruction?device_id=${this.selectedDeviceId}`)
                            ]);
                            const jHist = await resHist.json();
                            const jLat = await resLat.json();
                            const jChat = await resChat.json();

                            if (jLat.success) this.latest = jLat.data;
                            if (jHist.success && jHist.data.labels) {
                                this.chartData.labels = jHist.data.labels.map(l => this.formatTime(l));
                                this.chartData.heartRate = jHist.data.heart_rate;
                                this.chartData.spo2 = jHist.data.spo2;
                                this.chartData.temperature = jHist.data.temperature;
                            }
                            if (jChat.success) this.instruksi = jChat.data;

                        } catch (e) {
                            console.error('Load Error:', e);
                        }

                        this.$nextTick(() => {
                            this.initCharts();
                            this.setupWebSocket();
                            this.scrollToBottom();
                        });
                    },

                    initCharts() {
                        this.createChart('hrChart', 'Heart Rate', '#dc2626', [...this.chartData.heartRate]);
                        this.createChart('spo2Chart', 'SpO2', '#2563eb', [...this.chartData.spo2]);
                        this.createChart('tempChart', 'Temp', '#ea580c', [...this.chartData.temperature]);
                    },

                    createChart(canvasId, label, color, data) {
                        const ctx = document.getElementById(canvasId)?.getContext('2d');
                        if (!ctx) return;
                        this.charts[canvasId] = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: [...this.chartData.labels],
                                datasets: [{
                                    label,
                                    data,
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
                                        beginAtZero: false
                                    },
                                    x: {
                                        ticks: {
                                            maxTicksLimit: 6
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

                    get sortedInstruksi() {
                        return [...this.instruksi].sort((a, b) => (a.id || 0) - (b.id || 0));
                    },

                    async kirimLaporan() {
                        if (!this.laporanBaru.trim() || this.isSending) return;
                        this.isSending = true;
                        try {
                            const res = await fetch(`/api/instruction/report`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    device_id: this.selectedDeviceId,
                                    laporan_nakes: this.laporanBaru,
                                    waktu: new Date().toLocaleTimeString('id-ID', {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    })
                                })
                            });
                            const json = await res.json();
                            if (json.success) {
                                this.instruksi.push(json.data);
                                this.laporanBaru = '';
                                this.scrollToBottom();
                            }
                        } catch (e) {
                            console.error(e);
                        } finally {
                            this.isSending = false;
                        }
                    },

                    async kirimRespon(item, opsi) {
                        try {
                            const res = await fetch(`/api/instruction/${item.id}/complete`, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    respon_nakes: opsi
                                })
                            });
                            const json = await res.json();
                            if (json.success) {
                                item.is_completed = true;
                                item.respon_nakes = opsi;
                            }
                        } catch (e) {
                            console.error(e);
                        }
                    },

                    setupWebSocket() {
                        if (!window.Echo || !this.selectedDeviceId) return;
                        window.Echo.leaveAllChannels();
                        window.Echo.private(`device.${this.selectedDeviceId}`)
                            .listen('.sensor.received', (e) => {
                                this.onSensorReceived(e.sensor);
                            })
                            .listen('.instruction.created', (e) => {
                                const exists = this.instruksi.some(i => i.id === e.instruction.id);
                                if (!exists) {
                                    this.instruksi.push(e.instruction);
                                    this.notificationSound.play().catch(() => {});
                                    this.scrollToBottom();
                                }
                            });
                    },

                    onSensorReceived(sensor) {
                        this.latest = {
                            ...sensor,
                            created_at: sensor.timestamp
                        };
                        const time = this.formatTime(sensor.timestamp);
                        this.chartData.labels.push(time);
                        this.chartData.heartRate.push(sensor.heart_rate);
                        this.chartData.spo2.push(sensor.spo2);
                        this.chartData.temperature.push(sensor.temperature);
                        if (this.chartData.labels.length > this.maxDataPoints) {
                            this.chartData.labels.shift();
                            this.chartData.heartRate.shift();
                            this.chartData.spo2.shift();
                            this.chartData.temperature.shift();
                        }
                        Object.keys(this.charts).forEach(key => {
                            if (this.charts[key]) {
                                this.charts[key].data.labels = [...this.chartData.labels];
                                const dataRef = key === 'hrChart' ? 'heartRate' : (key === 'spo2Chart' ? 'spo2' :
                                    'temperature');
                                this.charts[key].data.datasets[0].data = [...this.chartData[dataRef]];
                                this.charts[key].update('none');
                            }
                        });
                        if (sensor.status === 'warning' || sensor.status === 'critical') {
                            this.notificationSound.play().catch(() => {});
                        }
                    },

                    scrollToBottom() {
                        this.$nextTick(() => {
                            if (this.$refs.chatBox) this.$refs.chatBox.scrollTop = this.$refs.chatBox.scrollHeight;
                        });
                    },
                    resetCharts() {
                        Object.values(this.charts).forEach(c => c?.destroy());
                        this.charts = {
                            hrChart: null,
                            spo2Chart: null,
                            tempChart: null
                        };
                    },
                    logout() {
                        localStorage.removeItem('monitoringApiKey');
                        localStorage.removeItem('selectedMonitoringDevice');
                        window.location.reload();
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
                    getKondisiClass(s) {
                        if (s === 'normal') return 'text-[rgb(0,62,48)]';
                        if (s === 'warning') return 'text-orange-500';
                        return s === 'critical' ? 'text-red-500' : 'text-gray-400';
                    },
                    statusLabel(s) {
                        if (!s) return '—';
                        return s.charAt(0).toUpperCase() + s.slice(1);
                    }
                };
            }
        </script>
    @endpush
@endsection
