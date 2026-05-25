@extends('layouts.app')
@section('title', 'SATS Monitoring - Laporan')

@section('content')

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
window.__laporanInit = {
    sessionId: {{ $sessionId ?? 'null' }},
    deviceId: '{{ $deviceId ?? '' }}',
    chartData: @json($chartData),
    vitalSigns: @json($vitalSigns ?? ['heart_rate', 'spo2', 'temperature'])
};

function laporanPage() {
    var initial = window.__laporanInit;
    return {
        showPatientModal: false,
        selectedSessionId: initial.sessionId ? String(initial.sessionId) : '',
        selectedSessionLabel: '',
        deviceId: initial.deviceId,
        vitalSigns: initial.vitalSigns || ['heart_rate', 'spo2', 'temperature'],
        loading: false,
        chartInstance: null,
        chartData: initial.chartData,

        init() {
            // Register global function for partial buttons
            window.openPatientModal = () => { this.showPatientModal = true; };

            // Set session label from selected option
            this.$nextTick(() => {
                const select = this.$el.querySelector('select[x-model="selectedSessionId"]');
                if (select && this.selectedSessionId) {
                    const opt = select.options[select.selectedIndex];
                    this.selectedSessionLabel = opt ? opt.text.trim() : '';
                }
                // Init chart if data exists
                if (this.chartData) {
                    this.initChart(this.chartData);
                }
            });
        },

        async loadSession() {
            if (!this.selectedSessionId) return;

            this.loading = true;
            const vs = this.vitalSigns.map(v => 'vital_signs[]=' + encodeURIComponent(v)).join('&');
            const url = `/nakes/laporan/session-data?session_id=${this.selectedSessionId}&${vs}`;

            try {
                const resp = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!resp.ok) throw new Error('Gagal memuat data sesi');
                const data = await resp.json();

                // Update patient section
                this.$refs.patientSection.innerHTML = data.patientHtml;

                // Update content section
                this.$refs.contentSection.innerHTML = data.contentHtml;

                // Update sidebar
                this.$refs.sidebarSection.innerHTML = data.sidebarHtml;

                // Update session label
                const select = this.$el.querySelector('select[x-model="selectedSessionId"]');
                if (select) {
                    const opt = select.options[select.selectedIndex];
                    this.selectedSessionLabel = opt ? opt.text.trim() : '';
                }

                // Re-init chart
                this.chartData = data.chartData;
                this.$nextTick(() => {
                    if (data.chartData) {
                        this.initChart(data.chartData);
                    }
                });

            } catch (err) {
                console.error(err);
                alert('Gagal memuat data sesi. Silakan coba lagi.');
            } finally {
                this.loading = false;
            }
        },

        initChart(chartData) {
            if (!chartData || !chartData.labels) return;
            const canvas = document.getElementById('chartVitalSigns');
            if (!canvas) return;

            if (this.chartInstance) {
                this.chartInstance.destroy();
                this.chartInstance = null;
            }

            const datasets = [];
            const vs = this.vitalSigns;

            if (vs.includes('heart_rate') && chartData.datasets.heart_rate) {
                datasets.push({
                    label: 'Heart Rate (bpm)',
                    data: chartData.datasets.heart_rate,
                    borderColor: 'rgb(220,38,38)',
                    backgroundColor: 'rgba(220,38,38,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    tension: 0.4,
                    yAxisID: 'y',
                });
            }
            if (vs.includes('spo2') && chartData.datasets.spo2) {
                datasets.push({
                    label: 'SpO2 (%)',
                    data: chartData.datasets.spo2,
                    borderColor: 'rgb(59,130,246)',
                    backgroundColor: 'rgba(59,130,246,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    tension: 0.4,
                    yAxisID: 'y1',
                });
            }
            if (vs.includes('temperature') && chartData.datasets.temperature) {
                datasets.push({
                    label: 'Suhu (°C)',
                    data: chartData.datasets.temperature,
                    borderColor: 'rgb(234,179,8)',
                    backgroundColor: 'rgba(234,179,8,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    tension: 0.4,
                    yAxisID: 'y2',
                });
            }

            if (datasets.length === 0) return;

            const ctx = canvas.getContext('2d');
            this.chartInstance = new Chart(ctx, {
                type: 'line',
                data: { labels: chartData.labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { font: { size: 8 }, maxRotation: 90, maxTicksLimit: 20 } },
                        y: {
                            type: 'linear', position: 'left',
                            title: { display: true, text: 'HR (bpm)', font: { size: 9 } },
                            ticks: { font: { size: 9 } },
                        },
                        y1: {
                            type: 'linear', position: 'right', min: 80, max: 100,
                            title: { display: true, text: 'SpO2 (%)', font: { size: 9 } },
                            ticks: { font: { size: 9 } },
                            grid: { drawOnChartArea: false },
                        },
                        y2: {
                            type: 'linear', position: 'right',
                            title: { display: true, text: 'Suhu (°C)', font: { size: 9 } },
                            ticks: { font: { size: 9 } },
                            grid: { drawOnChartArea: false },
                        },
                    }
                }
            });
        }
    };
}
</script>

<main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)] min-h-screen"
    x-data="laporanPage()">

    <h1 class="text-3xl font-bold text-[rgb(0,62,48)] mb-6">Laporan</h1>

    <div class="flex gap-6 items-start">

        <!-- Konten Laporan (Kiri) -->
        <div class="flex-1 space-y-4">

            <!-- Filter: Session + Vital Signs -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-[rgb(0,62,48)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                    </svg>
                    <span class="text-sm font-medium text-[rgb(0,62,48)]">Perangkat: {{ $deviceId ?? '-' }}</span>
                </div>
                <div class="flex flex-wrap gap-3 items-end">
                    <!-- Session -->
                    <div class="flex-1 min-w-[250px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sesi Monitoring</label>
                        <select x-model="selectedSessionId" @change="loadSession()"
                            class="w-full text-sm border border-gray-200 rounded px-3 py-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-[rgb(0,62,48)]">
                            <option value="">-- Pilih Sesi --</option>
                            @foreach($sessions ?? [] as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->medical_record_number }}
                                    @if($s->patient) — {{ $s->patient->nama }} @endif
                                    ({{ $s->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Vital Signs Checkboxes -->
                    <div class="flex gap-3 items-center">
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" value="heart_rate" x-model="vitalSigns"
                                class="rounded border-gray-300 text-[rgb(0,62,48)] focus:ring-[rgb(0,62,48)]">
                            Heart Rate
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" value="spo2" x-model="vitalSigns"
                                class="rounded border-gray-300 text-[rgb(0,62,48)] focus:ring-[rgb(0,62,48)]">
                            SpO2
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" value="temperature" x-model="vitalSigns"
                                class="rounded border-gray-300 text-[rgb(0,62,48)] focus:ring-[rgb(0,62,48)]">
                            Suhu
                        </label>
                        <button @click="loadSession()" :disabled="!selectedSessionId || loading"
                            class="px-3 py-1.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] disabled:opacity-50 text-white text-xs rounded transition">
                            Tampilkan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading indicator -->
            <div x-show="loading" class="flex items-center justify-center py-8">
                <svg class="animate-spin h-6 w-6 text-[rgb(0,62,48)]" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="ml-2 text-sm text-gray-500">Memuat data sesi...</span>
            </div>

            <!-- Dynamic Content Area -->
            <div x-show="!loading">
                <!-- Identitas Pasien -->
                <div id="laporan-patient" x-ref="patientSection">
                    @if($session)
                        @include('pages.nakes.partials._laporan-patient')
                    @endif
                </div>

                <!-- Main content: chart, vitals, stats, readings -->
                <div id="laporan-content" x-ref="contentSection" class="space-y-4">
                    @if($session)
                        @include('pages.nakes.partials._laporan-content')
                    @else
                        <!-- No session selected -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">Pilih sesi monitoring untuk menampilkan laporan.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <!-- Sidebar: Info & Unduh -->
        <div class="w-52 flex-shrink-0 space-y-3 sticky top-6" id="laporan-sidebar" x-ref="sidebarSection">
            @if($session)
                @include('pages.nakes.partials._laporan-sidebar')
            @else
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-3">
                    <p class="text-xs text-gray-500 text-center italic">Pilih sesi untuk mengunduh laporan.</p>
                </div>
            @endif
        </div>

    </div>

    <!-- Modal Input Data Pasien -->
    <div x-show="showPatientModal" x-transition
        class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-white/70 backdrop-blur-sm" @click="showPatientModal = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-100 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                <h3 class="text-lg font-semibold text-[rgb(0,62,48)]">Input Data Pasien</h3>
                <button @click="showPatientModal = false"
                    class="p-1 hover:bg-gray-100 rounded-full transition cursor-pointer">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('input-data-pasien.store') }}" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="device_id" :value="deviceId" />
                <input type="hidden" name="session_id" :value="selectedSessionId" />
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                    <span class="font-medium">Sesi:</span> <span x-text="selectedSessionLabel"></span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="modal_nama" class="block text-sm font-medium text-gray-700 mb-1">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="modal_nama" name="nama" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent">
                    </div>
                    <div>
                        <label for="modal_nik" class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                        <input type="text" id="modal_nik" name="nik" maxlength="16"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="modal_tanggal_lahir" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir</label>
                        <input type="date" id="modal_tanggal_lahir" name="tanggal_lahir"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent">
                    </div>
                    <div>
                        <label for="modal_umur" class="block text-sm font-medium text-gray-700 mb-1">
                            Umur <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="modal_umur" name="umur" required min="0" max="150"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent">
                    </div>
                    <div>
                        <label for="modal_jenis_kelamin" class="block text-sm font-medium text-gray-700 mb-1">
                            Jenis Kelamin <span class="text-red-500">*</span>
                        </label>
                        <select id="modal_jenis_kelamin" name="jenis_kelamin" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent">
                            <option value="" disabled selected>Pilih</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="modal_penyakit_alergi" class="block text-sm font-medium text-gray-700 mb-1">Penyakit/Alergi</label>
                    <input type="text" id="modal_penyakit_alergi" name="penyakit_alergi"
                        placeholder="Contoh: Diabetes, Alergi penisilin"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent">
                </div>
                <div>
                    <label for="modal_catatan_tambahan" class="block text-sm font-medium text-gray-700 mb-1">Catatan Tambahan</label>
                    <input type="text" id="modal_catatan_tambahan" name="catatan_tambahan"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showPatientModal = false"
                        class="px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-sm font-medium rounded-lg transition cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</main>

@endsection
