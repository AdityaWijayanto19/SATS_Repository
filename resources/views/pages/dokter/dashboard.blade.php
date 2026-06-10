@extends('layouts.app')
@section('title', 'SATS Monitoring - Dashboard')

@section('content')
    <main class="flex-1 h-143 overflow-y-auto p-6 bg-gray-100" x-data="dashboard()" x-init="init()">

        {{-- ============================================================ --}}
        {{-- VIEW 1: CARD VIEW — Pilih Perangkat                        --}}
        {{-- ============================================================ --}}
        <div x-show="viewMode === 'cards'" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">

            <div class="mb-6">
                <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring</h1>
                <p class="text-sm text-gray-400 mt-1">Pilih perangkat yang aktif untuk memulai monitoring vital sign pasien.</p>
            </div>

            {{-- Device Cards Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

                <template x-for="device in allDevices" :key="device.device_id">
                    <div class="rounded-xl border-2 transition-all duration-200 overflow-hidden"
                        :class="device.status === 'online'
                            ? 'border-green-300 bg-green-50 hover:shadow-lg hover:border-green-400 cursor-pointer'
                            : 'border-gray-200 bg-gray-50 opacity-60 cursor-not-allowed'">

                        {{-- Card Header --}}
                        <div class="px-4 py-3 flex items-center justify-between"
                            :class="device.status === 'online' ? 'bg-green-100' : 'bg-gray-100'">
                            <div class="flex items-center gap-2">
                                <span class="relative flex h-3 w-3">
                                    <span x-show="device.status === 'online'"
                                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-3 w-3"
                                        :class="device.status === 'online' ? 'bg-green-500' : 'bg-gray-400'"></span>
                                </span>
                                <span class="text-sm font-semibold"
                                    :class="device.status === 'online' ? 'text-green-800' : 'text-gray-500'"
                                    x-text="device.device_id"></span>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                :class="device.status === 'online' ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-500'"
                                x-text="device.status === 'online' ? 'Online' : 'Offline'"></span>
                        </div>

                        {{-- Card Body --}}
                        <div class="px-4 py-3 space-y-2">
                            {{-- Nakes --}}
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-xs text-gray-500"
                                    x-text="device.status === 'online' ? (device.latest?.kategori_usia ?? '—') : 'Tidak aktif'"></span>
                            </div>

                            {{-- Vital Signs (hanya untuk online) --}}
                            <div x-show="device.status === 'online' && device.latest" class="grid grid-cols-3 gap-2 pt-1">
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400">HR</p>
                                    <p class="text-sm font-semibold text-red-600" x-text="(device.latest?.heart_rate ?? '—') + ''"></p>
                                    <p class="text-[9px] text-gray-400">bpm</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400">SpO2</p>
                                    <p class="text-sm font-semibold text-blue-600" x-text="(device.latest?.spo2 ?? '—') + ''"></p>
                                    <p class="text-[9px] text-gray-400">%</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-[10px] text-gray-400">Temp</p>
                                    <p class="text-sm font-semibold text-orange-600" x-text="(device.latest?.temperature ?? '—') + ''"></p>
                                    <p class="text-[9px] text-gray-400">°C</p>
                                </div>
                            </div>

                            {{-- Status Kondisi --}}
                            <div x-show="device.status === 'online'" class="flex items-center gap-1.5 pt-1">
                                <span class="w-2 h-2 rounded-full"
                                    :class="{
                                        'bg-green-500': device.latest?.status === 'normal',
                                        'bg-orange-500': device.latest?.status === 'warning',
                                        'bg-red-500': device.latest?.status === 'critical',
                                        'bg-gray-300': !device.latest?.status
                                    }"></span>
                                <span class="text-xs font-medium"
                                    :class="{
                                        'text-green-700': device.latest?.status === 'normal',
                                        'text-orange-700': device.latest?.status === 'warning',
                                        'text-red-700': device.latest?.status === 'critical',
                                        'text-gray-400': !device.latest?.status
                                    }"
                                    x-text="device.latest?.status ? (device.latest.status.charAt(0).toUpperCase() + device.latest.status.slice(1)) : 'Menunggu data...'"></span>
                            </div>
                        </div>

                        {{-- Card Footer — Tombol Pilih --}}
                        <div class="px-4 py-3 border-t"
                            :class="device.status === 'online' ? 'border-green-200' : 'border-gray-200'">
                            <button x-show="device.status === 'online'"
                                @click="enterMonitoring(device.device_id)"
                                class="w-full cursor-pointer px-4 py-2 bg-[rgb(0,62,48)] text-white rounded-lg text-xs font-medium hover:opacity-90 transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                                </svg>
                                Monitor Perangkat
                            </button>
                            <p x-show="device.status !== 'online'" class="text-center text-xs text-gray-400 py-2">
                                Perangkat tidak aktif
                            </p>
                        </div>
                    </div>
                </template>

                {{-- Empty State --}}
                <div x-show="allDevices.length === 0"
                    class="col-span-full flex flex-col items-center justify-center py-20 text-gray-400">
                    <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm font-medium">Belum ada perangkat terdaftar</p>
                    <p class="text-xs mt-1">Hubungi superadmin untuk mendaftarkan perangkat.</p>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- VIEW 2: MONITORING VIEW — Dashboard Perangkat              --}}
        {{-- ============================================================ --}}
        <div x-show="viewMode === 'monitoring'" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">

            {{-- Header: Tombol Kembali + Info Perangkat --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <button @click="backToCards()"
                        class="cursor-pointer p-2 rounded-lg bg-white border border-gray-200 hover:bg-gray-50 transition">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <p class="text-sm text-gray-400" x-text="selectedDeviceId"></p>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                :class="deviceOnline ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                                x-text="deviceOnline ? 'Online' : 'Offline'"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <div class="px-4 py-2 rounded-lg flex items-center gap-2 text-sm font-medium border"
                        :class="deviceOnline
                            ? 'bg-green-50 text-green-700 border-green-200'
                            : 'bg-gray-50 text-gray-500 border-gray-200'">
                        <span class="relative flex h-2.5 w-2.5">
                            <span x-show="deviceOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="deviceOnline ? 'bg-green-500' : 'bg-gray-400'"></span>
                        </span>
                        <span x-text="deviceOnline ? 'Perangkat Aktif' : 'Perangkat Tidak Aktif'"></span>
                    </div>
                </div>
            </div>

            {{-- Peringatan: Perangkat Offline --}}
            <div x-show="!deviceOnline" x-transition
                class="flex flex-col items-center justify-center min-h-[60vh]">
                <div class="w-20 h-20 rounded-full bg-gray-50 flex items-center justify-center mb-5">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3"/>
                    </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Perangkat Offline</h2>
                <p class="text-sm text-gray-400 text-center max-w-md">
                    Perangkat <span class="font-medium text-gray-600" x-text="selectedDeviceId"></span> sedang tidak aktif.
                    Monitoring akan muncul saat perangkat dinyalakan.
                </p>
                <button @click="backToCards()"
                    class="mt-4 cursor-pointer px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                    ← Kembali ke Daftar Perangkat
                </button>
            </div>

            {{-- Stat Card (4 Kolom) --}}
            <div x-show="deviceOnline" x-transition class="grid grid-cols-4 gap-3 mb-4">

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

                {{-- Kondisi Pasien (dari perangkat, rule-based) --}}
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
                        Update: <span x-text="formatTime(latest?.created_at) || '—'"></span>
                    </p>
                </div>
            </div>

            {{-- Kategori Usia --}}
            <div x-show="deviceOnline" x-transition class="mb-4">
                <div class="bg-purple-50 rounded-xl p-4 border border-purple-200 inline-flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-purple-400">Kategori Usia Pasien</p>
                        <p class="text-lg font-semibold text-purple-700" x-text="latest?.kategori_usia ?? '—'"></p>
                    </div>
                    <span class="text-[10px] text-purple-400 ml-2">Data dari perangkat</span>
                </div>
            </div>

            {{-- Prediksi Machine Learning --}}
            <p x-show="deviceOnline" class="text-[10px] text-gray-400 mb-1">Prediksi di update setiap 5 data terkirim</p>
            <div x-show="deviceOnline && ml" x-transition
                class="flex items-center gap-4 bg-[rgba(0,62,48,0.05)] border border-[rgba(0,62,48,0.18)] rounded-xl px-5 py-3.5 mb-4">
                <span class="w-2 h-2 rounded-full flex-shrink-0"
                    :class="{
                        'bg-green-400': ml?.condition === 'NORMAL',
                        'bg-orange-400': ml?.condition === 'WARNING',
                        'bg-red-400': ml?.condition === 'CRITICAL',
                        'bg-gray-300': !ml?.condition
                    }"></span>
                <div class="flex-1">
                    <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Prediksi ML</p>
                    <p class="text-sm font-medium text-[rgb(0,62,48)]">
                        <span x-show="ml?.prediction" x-text="ml?.prediction"></span>
                        <span x-show="!ml?.prediction">Data prediksi belum tersedia.</span>
                    </p>
                </div>
                <span x-show="ml?.condition" class="text-[10px] font-medium px-2.5 py-1 rounded flex-shrink-0"
                    :class="{
                        'bg-green-100 text-green-700': ml?.condition === 'NORMAL',
                        'bg-orange-100 text-orange-700': ml?.condition === 'WARNING',
                        'bg-red-100 text-red-700': ml?.condition === 'CRITICAL'
                    }"
                    x-text="ml?.risk_level ?? ml?.condition">
                </span>
            </div>

            {{-- Probabilitas Kondisi Pasien --}}
            <div x-show="deviceOnline && ml?.probabilities" x-transition
                class="grid grid-cols-3 gap-3 mb-4">

                {{-- Membaik --}}
                <div class="bg-green-50 rounded-xl p-4 border border-green-200 text-center">
                    <p class="text-xs font-medium text-green-500 mb-1">Membaik</p>
                    <p class="text-3xl font-bold text-green-600"
                        x-text="(ml?.probabilities?.membaik ?? '—') + (ml?.probabilities?.membaik != null ? '%' : '')"></p>
                    <div class="mt-2 w-full bg-green-100 rounded-full h-1.5">
                        <div class="bg-green-500 h-1.5 rounded-full transition-all duration-500"
                            :style="'width:' + (ml?.probabilities?.membaik ?? 0) + '%'"></div>
                    </div>
                </div>

                {{-- Stabil --}}
                <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-200 text-center">
                    <p class="text-xs font-medium text-yellow-500 mb-1">Stabil</p>
                    <p class="text-3xl font-bold text-yellow-600"
                        x-text="(ml?.probabilities?.stabil ?? '—') + (ml?.probabilities?.stabil != null ? '%' : '')"></p>
                    <div class="mt-2 w-full bg-yellow-100 rounded-full h-1.5">
                        <div class="bg-yellow-500 h-1.5 rounded-full transition-all duration-500"
                            :style="'width:' + (ml?.probabilities?.stabil ?? 0) + '%'"></div>
                    </div>
                </div>

                {{-- Memburuk --}}
                <div class="bg-red-50 rounded-xl p-4 border border-red-200 text-center">
                    <p class="text-xs font-medium text-red-400 mb-1">Memburuk</p>
                    <p class="text-3xl font-bold text-red-500"
                        x-text="(ml?.probabilities?.memburuk ?? '—') + (ml?.probabilities?.memburuk != null ? '%' : '')"></p>
                    <div class="mt-2 w-full bg-red-100 rounded-full h-1.5">
                        <div class="bg-red-500 h-1.5 rounded-full transition-all duration-500"
                            :style="'width:' + (ml?.probabilities?.memburuk ?? 0) + '%'"></div>
                    </div>
                </div>
            </div>

            {{-- Grafik Sensor --}}
            <div x-show="deviceOnline" x-transition>
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
                    <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                        <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                            <p class="text-sm font-medium text-[rgb(0,62,48)]">Heart Rate</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">bpm — 10 menit terakhir</p>
                        </div>
                        <div class="p-4 relative" style="height:200px;">
                            <canvas id="hrChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                        <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                            <p class="text-sm font-medium text-[rgb(0,62,48)]">SpO2</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">% — 10 menit terakhir</p>
                        </div>
                        <div class="p-4 relative" style="height:200px;">
                            <canvas id="spo2Chart"></canvas>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
                        <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                            <p class="text-sm font-medium text-[rgb(0,62,48)]">Temperature</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">°C — 10 menit terakhir</p>
                        </div>
                        <div class="p-4 relative" style="height:200px;">
                            <canvas id="tempChart"></canvas>
                        </div>
                    </div>
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
                [hrChart, spo2Chart, tempChart].forEach(c => { if (c) c.destroy(); });
                hrChart = spo2Chart = tempChart = null;

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
                    viewMode: 'cards', // 'cards' | 'monitoring'
                    allDevices: [],
                    allDeviceIds: [],
                    subscribedIds: new Set(),
                    selectedDeviceId: null,
                    latest: null,
                    ml: null,
                    deviceOnline: false,
                    chartMode: 'separate',

                    async init() {
                        await this.fetchDevices();
                        this.setupRealtime();
                    },

                    formatTime(dateString) {
                        if (!dateString) return '';
                        let date = new Date(dateString);
                        if (isNaN(date.getTime())) date = new Date(dateString.replace(" ", "T"));
                        if (isNaN(date.getTime())) return dateString.match(/\d{2}:\d{2}/)?.[0] || dateString;
                        return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    },

                    async fetchDevices() {
                        try {
                            const res = await fetch('/api/devices');
                            const json = await res.json();
                            if (json.success) {
                                this.allDevices = json.data;
                                this.allDeviceIds = json.data.map(d => d.device_id);

                                // Jika sedang monitoring, update data device yang dipilih
                                if (this.viewMode === 'monitoring' && this.selectedDeviceId) {
                                    const selected = this.allDevices.find(d => d.device_id === this.selectedDeviceId);
                                    if (selected) {
                                        this.deviceOnline = selected.status === 'online';
                                        if (selected.status === 'offline') {
                                            this.latest = null;
                                            this.ml = null;
                                            updateCharts(null);
                                        }
                                    }
                                }
                            }
                        } catch (e) {
                            console.error('Error fetching devices:', e);
                        }
                    },

                    setupRealtime() {
                        if (!window.Echo) return;
                        this.subscribeAllDevices();
                    },

                    subscribeAllDevices() {
                        const ids = [...this.allDeviceIds];
                        if (this.selectedDeviceId && !ids.includes(this.selectedDeviceId)) {
                            ids.push(this.selectedDeviceId);
                        }

                        ids.forEach(deviceId => {
                            if (this.subscribedIds.has(deviceId)) return;
                            this.subscribedIds.add(deviceId);

                            window.Echo.private(`device.${deviceId}`)
                                .listen('.device.status.changed', async (e) => {
                                    // Update status di card view
                                    const dev = this.allDevices.find(d => d.device_id === e.device_id);
                                    if (dev) dev.status = e.status;

                                    // Jika device yang sedang dimonitor berubah status
                                    if (this.selectedDeviceId === e.device_id && this.viewMode === 'monitoring') {
                                        this.deviceOnline = e.status === 'online';
                                        if (e.status === 'offline') {
                                            this.latest = null;
                                            this.ml = null;
                                            updateCharts(null);
                                        } else {
                                            // Device online lagi, fetch data
                                            await this.fetchDevices();
                                            const selected = this.allDevices.find(d => d.device_id === this.selectedDeviceId);
                                            if (selected) {
                                                this.latest = selected.latest;
                                                this.ml = selected.ml ?? null;
                                                updateCharts(selected.history);
                                            }
                                        }
                                    }
                                })
                                .listen('.sensor.data.received', (e) => {
                                    if (this.selectedDeviceId === e.device_id && this.viewMode === 'monitoring') {
                                        // Simpan kategori_usia sebelum update (tidak berubah selama sesi)
                                        const lockedKategoriUsia = this.latest?.kategori_usia;
                                        this.latest = e.latest;
                                        // Kunci kategori_usia — tidak di-update oleh data sensor baru
                                        if (lockedKategoriUsia && this.latest) {
                                            this.latest.kategori_usia = lockedKategoriUsia;
                                        }
                                        if (e.latest?.ml_prediction) {
                                            this.ml = {
                                                prediction: e.latest.ml_prediction,
                                                condition: e.latest.ml_condition,
                                                risk_level: e.latest.ml_risk_level,
                                                probabilities: e.latest.ml_probabilities,
                                                predicted_at: e.latest.ml_predicted_at,
                                            };
                                        }
                                        updateCharts(e.history);
                                    }
                                });
                        });
                    },

                    enterMonitoring(deviceId) {
                        this.selectedDeviceId = deviceId;
                        globalSelectedDeviceId = deviceId;
                        const selected = this.allDevices.find(d => d.device_id === deviceId);
                        if (selected) {
                            this.latest = selected.latest;
                            this.ml = selected.ml ?? null;
                            this.deviceOnline = selected.status === 'online';
                        }

                        this.viewMode = 'monitoring';
                        this.chartMode = 'separate';

                        // Init charts setelah view benar-benar tampil
                        setTimeout(() => {
                            initCharts();
                            if (selected?.history) updateCharts(selected.history);
                        }, 100);

                        window.dispatchEvent(new CustomEvent('deviceSelected', { detail: { deviceId } }));

                        // Register ke backend
                        fetch('/dokter/select-device', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({ device_id: deviceId }),
                        }).catch(err => console.error('Error registering device monitoring:', err));
                    },

                    backToCards() {
                        // Cleanup
                        if (this.selectedDeviceId && window.Echo) {
                            // Tetap subscribe, tapi tidak proses sensor data di card view
                        }

                        this.viewMode = 'cards';
                        this.selectedDeviceId = null;
                        this.latest = null;
                        this.ml = null;
                        this.deviceOnline = false;
                        globalSelectedDeviceId = null;

                        // Refresh data card
                        this.fetchDevices();
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

                    destroy() {}
                };
            }

        </script>
    @endpush

@endsection
