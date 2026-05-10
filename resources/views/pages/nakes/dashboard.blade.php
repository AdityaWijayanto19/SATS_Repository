@extends('layouts.app')

@section('content')

<main
    class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]"
    x-data="dashboard()"
    x-init="init()"
>

    {{-- Header --}}
    <div class="flex items-start justify-between mb-5">
        <div>
            <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring</h1>
            <p class="text-sm text-gray-400 mt-1" x-text="selectedDeviceId ?? 'Belum ada perangkat'"></p>
        </div>

        {{-- Dropdown Perangkat --}}
        <div class="relative">
            <button
                @click="dropdownOpen = !dropdownOpen"
                class="cursor-pointer px-4 py-2 bg-[rgb(0,62,48)] text-white rounded-lg flex items-center gap-2 text-sm hover:opacity-90 transition"
            >
                Pilih Perangkat
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                x-show="dropdownOpen"
                x-transition
                @click.outside="dropdownOpen = false"
                class="absolute right-0 mt-2 w-52 bg-white border border-[rgba(0,83,63,0.15)] rounded-xl shadow-sm z-50 overflow-hidden"
            >
                <template x-for="device in allDevices" :key="device.device_id">
                    <div
                        @click="selectDevice(device.device_id); dropdownOpen = false"
                        class="px-4 py-2.5 text-sm hover:bg-[rgba(0,83,63,0.06)] cursor-pointer"
                        :class="device.device_id === selectedDeviceId ? 'text-[rgb(0,62,48)] font-semibold' : 'text-[rgb(0,62,48)]'"
                        x-text="device.device_id"
                    ></div>
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
            <p class="text-[10px] mt-1"
                :class="getStatusClass(latest?.heart_rate, 'hr')"
                x-text="getStatusText(latest?.heart_rate, 'hr')"
            ></p>
        </div>

        {{-- SpO2 --}}
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <p class="text-xs font-medium text-blue-400 mb-2">SpO2</p>
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">
                <span x-text="latest?.spo2 ?? '—'"></span>
                <span class="text-sm font-normal text-blue-400">%</span>
            </p>
            <p class="text-[10px] mt-1"
                :class="getStatusClass(latest?.spo2, 'spo2')"
                x-text="getStatusText(latest?.spo2, 'spo2')"
            ></p>
        </div>

        {{-- Temperature --}}
        <div class="bg-orange-50 rounded-xl p-4 border border-orange-200">
            <p class="text-xs font-medium text-orange-400 mb-2">Temperature</p>
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">
                <span x-text="latest?.temperature ?? '—'"></span>
                <span class="text-sm font-normal text-orange-400">°C</span>
            </p>
            <p class="text-[10px] mt-1"
                :class="getStatusClass(latest?.temperature, 'temp')"
                x-text="getStatusText(latest?.temperature, 'temp')"
            ></p>
        </div>

        {{-- Kondisi Pasien --}}
        <div class="bg-[rgba(0,83,63,0.05)] rounded-xl p-4 border border-[rgba(0,83,63,0.2)]">
            <p class="text-xs font-medium text-[rgb(0,62,48)] mb-2">Kondisi Pasien</p>
            <p class="text-2xl font-medium"
                :class="{
                    'text-[rgb(0,62,48)]': latest?.status === 'normal',
                    'text-orange-500':     latest?.status === 'warning',
                    'text-red-500':        latest?.status === 'critical'
                }"
                x-text="latest?.status ? (latest.status.charAt(0).toUpperCase() + latest.status.slice(1)) : '—'"
            ></p>
            <p class="text-[10px] text-[rgba(0,62,48,0.5)] mt-1">
                Pembaruan: <span x-text="latest?.created_at ?? '—'"></span>
            </p>
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

        <span
            class="text-[10px] font-medium px-2.5 py-1 rounded flex-shrink-0"
            :class="{
                'bg-green-100 text-green-700': latest?.status === 'normal',
                'bg-orange-100 text-orange-700': latest?.status === 'warning',
                'bg-red-100 text-red-700': latest?.status === 'critical'
            }"
            x-text="statusLabel(latest?.status)"
        ></span>
    </div>

    {{-- Grafik Sensor --}}
    <div class="grid grid-cols-3 gap-3">

        @foreach ([
            ['id' => 'hrChart',   'label' => 'Heart Rate',   'unit' => 'bpm'],
            ['id' => 'spo2Chart', 'label' => 'SpO2',         'unit' => '%'],
            ['id' => 'tempChart', 'label' => 'Temperature',  'unit' => '°C'],
        ] as $chart)
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

    {{-- Instruksi Dokter --}}
    <div class="mt-4" x-data="komentarNakes()">
        <div class="bg-white rounded-xl border border-[rgba(0,83,63,0.1)] overflow-hidden">

            {{-- Header --}}
            <div class="px-5 py-3.5 border-b border-[rgba(0,83,63,0.08)] flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-[rgb(0,62,48)]">Instruksi dari Dokter</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Respon instruksi dan checklist yang sudah dilakukan</p>
                </div>
                <span class="text-[10px] font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full"
                    x-text="activeCount + ' instruksi aktif'"
                ></span>
            </div>

            {{-- List --}}
            <div>
                <template x-if="activeCount === 0">
                    <p class="px-5 py-4 text-sm text-gray-400 text-center">Tidak ada instruksi dari dokter.</p>
                </template>

                <template x-for="item in komentar" :key="item.id">
                    <div x-show="!item.checked" class="px-5 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-3">

                            {{-- Checkbox --}}
                            <label class="mt-1 flex-shrink-0 cursor-pointer">
                                <input
                                    type="checkbox"
                                    x-model="item.checked"
                                    @change="onChecked(item)"
                                    class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                >
                            </label>

                            {{-- Avatar Dokter --}}
                            <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">D</div>

                            {{-- Konten --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-gray-700" x-text="item.user_name"></span>
                                    <span class="text-[10px] text-gray-400" x-text="item.waktu"></span>
                                </div>

                                <p class="text-sm text-gray-600 mb-2" x-text="item.teks"></p>

                                {{-- Form Respon --}}
                                <template x-if="!item.respon">
                                    <div class="flex items-center gap-2">
                                        <select
                                            x-model="item.selectedRespon"
                                            class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                                        >
                                            <option value="" disabled selected>Pilih respon...</option>
                                            <option>Sudah dilakukan</option>
                                            <option>Tidak bisa dilakukan</option>
                                            <option>Tidak memungkinkan</option>
                                            <option>Sedang diproses</option>
                                            <option>Butuh konfirmasi ulang</option>
                                        </select>
                                        <button
                                            @click="kirimRespon(item)"
                                            :disabled="!item.selectedRespon"
                                            class="text-xs font-medium text-white px-3 py-1.5 rounded-lg cursor-pointer transition-all hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed bg-[rgb(0,83,63)]"
                                        >Kirim</button>
                                    </div>
                                </template>

                                {{-- Respon Terkirim --}}
                                <template x-if="item.respon">
                                    <div class="mt-1.5 pl-3 border-l-2 border-emerald-300 bg-emerald-50 rounded-r-lg py-1.5 px-3">
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <span class="text-[10px] font-semibold text-emerald-700">Respon Anda</span>
                                            <span class="text-[10px] text-emerald-500" x-text="item.responWaktu"></span>
                                        </div>
                                        <p class="text-xs text-emerald-800" x-text="item.respon"></p>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>
                </template>
            </div>

        </div>
    </div>

</main>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
/* 
   Konstanta & Helpers
*/

const THRESHOLDS = {
    hr:   { normal: [60, 100],  warning: [50, 120] },
    spo2: { normal: [95, 100],  warning: [90, 94]  },
    temp: { normal: [36.0, 37.5], warning: [37.6, 38.5] },
};

function getVitalStatus(value, type) {
    if (value == null) return 'unknown';
    const t = THRESHOLDS[type];
    if (value >= t.normal[0] && value <= t.normal[1]) return 'normal';
    if (type === 'hr'   && value >= t.warning[0] && value <= t.warning[1]) return 'warning';
    if (type === 'spo2' && value >= t.warning[0] && value <= t.warning[1]) return 'warning';
    if (type === 'temp' && value >= t.normal[1]  && value <= t.warning[1]) return 'warning';
    return 'critical';
}

const STATUS_CLASS = {
    normal:   { hr: 'text-red-400',    spo2: 'text-blue-400',  temp: 'text-orange-400' },
    warning:  { hr: 'text-amber-500',  spo2: 'text-amber-500', temp: 'text-amber-500'  },
    critical: { hr: 'text-red-600',    spo2: 'text-red-600',   temp: 'text-red-600'    },
    unknown:  { hr: 'text-gray-400',   spo2: 'text-gray-400',  temp: 'text-gray-400'   },
};

const STATUS_TEXT = { normal: 'Normal', warning: 'Warning', critical: 'Critical', unknown: 'Tidak ada data' };
const STATUS_LABEL = { normal: 'Aman', warning: 'Perhatian', critical: 'Kritis' };

/*
   Chart Helpers
*/

function makeGradient(ctx, hex) {
    const g = ctx.createLinearGradient(0, 0, 0, 160);
    g.addColorStop(0, hex + '35');
    g.addColorStop(1, hex + '00');
    return g;
}

function buildChartConfig(ctx, color, yMin, yMax) {
    return {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                data: [],
                borderColor: color,
                backgroundColor: makeGradient(ctx, color),
                borderWidth: 1.5,
                fill: true,
                tension: 0.4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: 'index', intersect: false },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: '#8aab9f' },
                },
                y: {
                    min: yMin,
                    max: yMax,
                    grid: { color: 'rgba(0,83,63,0.07)', lineWidth: 0.5 },
                    ticks: { font: { size: 10 }, color: '#8aab9f' },
                },
            },
            elements: { point: { radius: 2.5, hoverRadius: 5 } },
        },
    };
}

/*
   Alpine -> dashboard()
*/

const initialDevices = @json($devices);

// Disimpan di luar Alpine agar tidak dibungkus Proxy
const _chartInstances = {};

let _selectedDeviceId = initialDevices[0]?.device_id ?? null;

function dashboard() {
    return {
        allDevices:       initialDevices,
        selectedDeviceId: _selectedDeviceId,
        latest:           initialDevices[0]?.latest    ?? null,
        dropdownOpen:     false,
        _pollTimer:       null,
        _devicePollTimer: null,
        _lastChartLabels: null,

        init() {
            setTimeout(async () => {
                this._initCharts();
                if (this.selectedDeviceId) await this._fetchChartData();
                this._pollTimer = setInterval(() => {
                    this._pollLatest();
                    this._fetchChartData();
                }, 5_000);
                this._devicePollTimer = setInterval(() => this._pollDevices(), 10_000);
            }, 150);
        },

        selectDevice(deviceId) {
            _selectedDeviceId     = deviceId;
            this.selectedDeviceId = deviceId;
            this._lastChartLabels = null;
            this.latest = this.allDevices.find(d => d.device_id === deviceId)?.latest ?? null;
            this._fetchChartData();
        },

        statusLabel(status) {
            return STATUS_LABEL[status] ?? '—';
        },

        getStatusClass(value, type) {
            return STATUS_CLASS[getVitalStatus(value, type)][type];
        },

        getStatusText(value, type) {
            return STATUS_TEXT[getVitalStatus(value, type)];
        },

        _initCharts() {
            // Destroy dulu kalau ada instance lama
            Object.values(_chartInstances).forEach(c => c.destroy());

            const specs = [
                { key: 'hr',   id: 'hrChart',   color: '#ef4444', yMin: 40, yMax: 160 },
                { key: 'spo2', id: 'spo2Chart', color: '#3b82f6', yMin: 80, yMax: 100 },
                { key: 'temp', id: 'tempChart', color: '#f97316', yMin: 35, yMax: 42  },
            ];

            specs.forEach(({ key, id, color, yMin, yMax }) => {
                const ctx = document.getElementById(id).getContext('2d');
                _chartInstances[key] = new Chart(ctx, buildChartConfig(ctx, color, yMin, yMax));
            });
        },

        _updateCharts(data) {
            const mapping = { hr: 'heart_rate', spo2: 'spo2', temp: 'temperature' };
            const newLabels = JSON.stringify(data.labels);
            if (newLabels === this._lastChartLabels) return;
            this._lastChartLabels = newLabels;
            Object.entries(mapping).forEach(([key, field]) => {
                const chart = _chartInstances[key];
                if (!chart) return;
                chart.data.labels           = data.labels;
                chart.data.datasets[0].data = data[field];
                chart.update('none');
            });
        },

        async _fetchChartData() {
            if (!this.selectedDeviceId) return;
            try {
                const res  = await fetch(`/api/device/${this.selectedDeviceId}/sensor-data/history?minutes=10`);
                const json = await res.json();
                if (json.success && json.data) this._updateCharts(json.data);
            } catch { /* silent */ }
        },

        async _pollLatest() {
            if (!this.selectedDeviceId) return;
            try {
                const res  = await fetch(`/api/device/${this.selectedDeviceId}/sensor-data/latest`, {
                    headers: { Accept: 'application/json' },
                });
                const json = await res.json();
                if (!json.success || !json.data) return;

                const d = json.data;
                this.latest = {
                    heart_rate:  d.heart_rate,
                    spo2:        d.spo2,
                    temperature: d.temperature,
                    status:      d.status,
                    prediction:  d.prediction ?? null,
                    created_at:  new Date(d.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
                };

                const device = this.allDevices.find(d => d.device_id === this.selectedDeviceId);
                if (device) device.latest = this.latest;
            } catch { /* silent */ }
        },

        async _pollDevices() {
            try {
                const res  = await fetch('/api/devices', { headers: { Accept: 'application/json' } });
                const json = await res.json();
                if (!json.success) return;
                const newDevices = json.data;
                const oldIds = this.allDevices.map(d => d.device_id);
                const newIds = newDevices.map(d => d.device_id);
                if (JSON.stringify(oldIds) === JSON.stringify(newIds)) return;
                this.allDevices = newDevices;
                if (!newIds.includes(this.selectedDeviceId)) {
                    this.selectDevice(newIds[0] ?? null);
                }
            } catch { /* silent */ }
        },
    };
}

/*
   Alpine -> komentarNakes()
*/

function komentarNakes() {
    return {
        komentar: [],
        _pollTimer: null,
        _checkedIds: new Set(),

        async init() {
            await this._fetch();
            this._pollTimer = setInterval(() => this._fetch(), 5000);
        },

        get activeCount() {
            return this.komentar.filter(k => !k.checked).length;
        },

        async _fetch() {
            const did = _selectedDeviceId;
            if (!did) return;
            try {
                const res = await fetch(`/api/comments?device_id=${did}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const json = await res.json();
                if (json.success) {
                    const currentIds = new Set(json.data.map(c => c.id));
                    this._checkedIds.forEach(id => { if (!currentIds.has(id)) this._checkedIds.delete(id); });
                    this.komentar = json.data.map(c => ({
                        ...c,
                        selectedRespon: '',
                        checked: this._checkedIds.has(c.id),
                    }));
                }
            } catch { /* silent */ }
        },

        async kirimRespon(item) {
            if (!item.selectedRespon) return;
            try {
                const res = await fetch(`/api/comments/${item.id}/respond`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ respon: item.selectedRespon }),
                });
                const json = await res.json();
                if (json.success) {
                    item.respon = json.data.respon;
                    item.responWaktu = json.data.responWaktu;
                }
            } catch { /* silent */ }
        },

        onChecked(item) {
            if (item.checked) {
                this._checkedIds.add(item.id);
            } else {
                this._checkedIds.delete(item.id);
            }
        },
    };
}
</script>
@endpush

@endsection