@extends('layouts.app')
@section('title', 'SATS Monitoring - Laporan')

@section('content')
<main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)] min-h-screen">

    <h1 class="text-3xl font-bold text-[rgb(0,62,48)] mb-6">Laporan</h1>

    <div class="flex gap-6 items-start">

        <!-- Konten Laporan (Kiri) -->
        <div class="flex-1 space-y-4">

            <!-- Filter: Device + Session -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <form method="GET" action="{{ route('dokter.laporan') }}" class="flex flex-wrap gap-3 items-end">
                    <!-- Device ID (for dokter who can monitor multiple devices) -->
                    @if($monitoredDevices->count() > 0)
                        <div class="flex-1 min-w-[180px]">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Perangkat</label>
                            <select name="device_id" id="deviceFilter" onchange="this.form.submit()"
                                class="w-full text-sm border border-gray-200 rounded px-3 py-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-[rgb(0,62,48)]">
                                @foreach($monitoredDevices as $device)
                                    <option value="{{ $device->device_id }}" {{ ($deviceId ?? '') == $device->device_id ? 'selected' : '' }}>
                                        {{ $device->device_id }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="flex-1 min-w-[180px]">
                            <span class="text-sm text-gray-500">Belum memantau perangkat</span>
                        </div>
                    @endif

                    <!-- Session -->
                    <div class="flex-1 min-w-[220px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Sesi Monitoring</label>
                        <select name="session_id" id="sessionFilter" onchange="this.form.submit()"
                            class="w-full text-sm border border-gray-200 rounded px-3 py-2 text-gray-700 focus:outline-none focus:ring-1 focus:ring-[rgb(0,62,48)]">
                            <option value="">-- Pilih Sesi --</option>
                            @foreach($sessions ?? [] as $s)
                                <option value="{{ $s->id }}" {{ ($sessionId ?? '') == $s->id ? 'selected' : '' }}>
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
                            <input type="checkbox" name="vital_signs[]" value="heart_rate"
                                {{ in_array('heart_rate', $vitalSigns ?? ['heart_rate']) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-[rgb(0,62,48)] focus:ring-[rgb(0,62,48)]">
                            Heart Rate
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" name="vital_signs[]" value="spo2"
                                {{ in_array('spo2', $vitalSigns ?? ['spo2']) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-[rgb(0,62,48)] focus:ring-[rgb(0,62,48)]">
                            SpO2
                        </label>
                        <label class="flex items-center gap-1.5 text-xs text-gray-600">
                            <input type="checkbox" name="vital_signs[]" value="temperature"
                                {{ in_array('temperature', $vitalSigns ?? ['temperature']) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-[rgb(0,62,48)] focus:ring-[rgb(0,62,48)]">
                            Suhu
                        </label>
                        <button type="submit"
                            class="px-3 py-1.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-xs rounded transition">
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>

            @if($session)
                <!-- Identitas Pasien -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h2 class="text-base font-semibold text-[rgb(0,62,48)] text-center mb-4">
                        Laporan Medis Pasien: {{ $session->medical_record_number }}
                        @if($patient) — {{ $patient->nama }} @endif
                    </h2>
                    @if($patient)
                        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm text-gray-700">
                            <div>
                                <p><span class="font-semibold">Nama Lengkap</span> : {{ $patient->nama }}</p>
                                <p><span class="font-semibold">NIK</span> : {{ $patient->nik ?? '-' }}</p>
                                <p><span class="font-semibold">Umur</span> : {{ $patient->umur ?? '-' }} tahun</p>
                                <p><span class="font-semibold">Jenis Kelamin</span> : {{ $patient->jenis_kelamin == 'L' ? 'Laki-laki' : ($patient->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</p>
                            </div>
                            <div>
                                <p><span class="font-semibold">Penyakit/Alergi</span> : {{ $patient->penyakit_alergi ?? '-' }}</p>
                                <p class="mt-1"><span class="font-semibold">Catatan Tambahan</span> : {{ $patient->catatan_tambahan ?? '-' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center italic">Data pasien belum diinput oleh nakes.</p>
                    @endif
                </div>

                <!-- Banner Prediksi ML -->
                @php
                    $riskLevel  = $prediksi->risk_level ?? 'warning';
                    $riskPercent = $prediksi->risk_percent ?? 20;
                    $riskMenit  = $prediksi->timeframe_minutes ?? 15;
                    $riskPesan  = $prediksi->message ?? 'Kondisi pasien berpotensi memburuk ' . $riskPercent . '% dalam ' . $riskMenit . ' menit ke depan.';

                    [$riskDot, $riskBadgeCls, $riskBadgeLabel, $riskBannerBg] = match($riskLevel) {
                        'critical' => ['bg-red-500', 'bg-red-100 text-red-700', 'Kritis', 'bg-red-50 border-red-200'],
                        'normal'   => ['bg-green-500', 'bg-green-100 text-green-700', 'Normal', 'bg-green-50 border-green-200'],
                        default    => ['bg-orange-400', 'bg-orange-100 text-orange-700', 'Perhatian', 'bg-[rgba(0,62,48,0.05)] border-[rgba(0,62,48,0.18)]'],
                    };
                @endphp
                <div class="flex items-center gap-4 {{ $riskBannerBg }} border rounded-xl px-5 py-3.5">
                    <span class="w-2 h-2 rounded-full {{ $riskDot }} flex-shrink-0"></span>
                    <div class="flex-1">
                        <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Prediksi ML</p>
                        <p class="text-sm font-medium text-[rgb(0,62,48)]">{{ $riskPesan }}</p>
                    </div>
                    <span class="text-[10px] font-medium px-2.5 py-1 rounded {{ $riskBadgeCls }} flex-shrink-0">
                        {{ $riskBadgeLabel }}
                    </span>
                </div>

                <!-- Grafik Vital Signs + Nilai Vital -->
                <div class="grid grid-cols-5 gap-4">

                    <!-- Grafik Vital Signs -->
                    <div class="col-span-3 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <p class="text-xs font-semibold text-gray-600 text-center mb-2">Grafik Vital Signs</p>
                        <div class="w-full" style="height: 200px;">
                            <canvas id="chartVitalSigns"></canvas>
                        </div>
                        <div class="flex justify-center gap-4 mt-2 text-xs text-gray-500">
                            @if(in_array('heart_rate', $vitalSigns ?? []))
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-4 h-0.5 bg-red-500"></span> Heart Rate (bpm)
                                </span>
                            @endif
                            @if(in_array('spo2', $vitalSigns ?? []))
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-4 h-0.5 bg-blue-500"></span> SpO2 (%)
                                </span>
                            @endif
                            @if(in_array('temperature', $vitalSigns ?? []))
                                <span class="flex items-center gap-1">
                                    <span class="inline-block w-4 h-0.5 bg-yellow-500"></span> Suhu (°C)
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Nilai Vital + Statistik -->
                    <div class="col-span-2 flex flex-col gap-4">

                        <!-- Nilai Vital Terbaru -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                            <p class="text-sm font-bold text-[rgb(0,62,48)] mb-3">Nilai Vital Terbaru</p>
                            @if($latestReading)
                                <div class="flex justify-around items-center">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 mb-1">Detak Jantung</p>
                                        <p class="text-3xl font-black text-gray-800 leading-none">{{ $latestReading->heart_rate ?? '-' }}</p>
                                        <p class="text-xs text-gray-400 mt-1">bpm</p>
                                    </div>
                                    <div class="w-px h-12 bg-gray-200"></div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 mb-1">SpO2</p>
                                        <p class="text-3xl font-black text-gray-800 leading-none">
                                            {{ $latestReading->spo2 ?? '-' }}<span class="text-xl">%</span>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">saturasi</p>
                                    </div>
                                    <div class="w-px h-12 bg-gray-200"></div>
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500 mb-1">Suhu</p>
                                        <p class="text-3xl font-black text-gray-800 leading-none">
                                            {{ $latestReading->temperature ?? '-' }}<span class="text-xl">°</span>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-1">celsius</p>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 text-center italic">Belum ada data</p>
                            @endif
                        </div>

                        <!-- Statistik Sesi -->
                        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                            <p class="text-sm font-bold text-[rgb(0,62,48)] mb-2">Statistik Sesi</p>
                            @if($stats)
                                <div class="space-y-1.5 text-xs text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Total Pembacaan</span>
                                        <span class="font-semibold">{{ $stats['total_readings'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Rata-rata HR</span>
                                        <span class="font-semibold">{{ $stats['avg_heart_rate'] }} bpm</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Rata-rata SpO2</span>
                                        <span class="font-semibold">{{ $stats['avg_spo2'] }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Rata-rata Suhu</span>
                                        <span class="font-semibold">{{ $stats['avg_temperature'] }}°C</span>
                                    </div>
                                    <div class="flex justify-between pt-1 border-t border-gray-100">
                                        <span class="flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Normal
                                        </span>
                                        <span class="font-semibold">{{ $stats['normal_count'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-yellow-400"></span> Warning
                                        </span>
                                        <span class="font-semibold">{{ $stats['warning_count'] }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="flex items-center gap-1">
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span> Kritis
                                        </span>
                                        <span class="font-semibold">{{ $stats['critical_count'] }}</span>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-gray-500 text-center italic">Belum ada data</p>
                            @endif
                        </div>

                    </div>
                </div>

                <!-- Tabel Riwayat Sensor Readings -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="bg-[rgba(0,62,48,0.06)] px-4 py-3 border-b border-gray-200">
                        <p class="text-sm font-semibold text-[rgb(0,62,48)] text-center">Riwayat Pembacaan Sensor</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100">
                                    <th class="py-2 px-4 text-left font-semibold text-gray-600">Waktu</th>
                                    <th class="py-2 px-4 text-center font-semibold text-gray-600">Heart Rate</th>
                                    <th class="py-2 px-4 text-center font-semibold text-gray-600">SpO2</th>
                                    <th class="py-2 px-4 text-center font-semibold text-gray-600">Suhu</th>
                                    <th class="py-2 px-4 text-center font-semibold text-gray-600">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($session->sensorReadings ?? [] as $reading)
                                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                        <td class="py-2 px-4 text-gray-500 font-mono text-xs">
                                            {{ $reading->recorded_at?->setTimezone('Asia/Jakarta')->format('H:i:s') }}
                                        </td>
                                        <td class="py-2 px-4 text-center text-gray-700">{{ $reading->heart_rate }} bpm</td>
                                        <td class="py-2 px-4 text-center text-gray-700">{{ $reading->spo2 }}%</td>
                                        <td class="py-2 px-4 text-center text-gray-700">{{ $reading->temperature }}°C</td>
                                        <td class="py-2 px-4 text-center">
                                            @php
                                                $statusColor = match($reading->status) {
                                                    'critical' => 'bg-red-100 text-red-700',
                                                    'warning' => 'bg-yellow-100 text-yellow-700',
                                                    default => 'bg-green-100 text-green-700',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                                {{ $reading->status_badge }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-gray-500 italic">
                                            Belum ada data pembacaan sensor untuk sesi ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            @else
                <!-- No session selected -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-gray-500 text-sm">Pilih perangkat dan sesi monitoring untuk menampilkan laporan.</p>
                </div>
            @endif

        </div>

        <!-- Sidebar: Info & Unduh -->
        <div class="w-52 flex-shrink-0 space-y-3 sticky top-6">
            @if($session)
                <!-- Info Sesi -->
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-3 space-y-2">
                    <p class="text-xs font-semibold text-[rgb(0,62,48)]">Info Sesi</p>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p><span class="font-medium">No. RM:</span> {{ $session->medical_record_number }}</p>
                        <p><span class="font-medium">Mulai:</span> {{ $session->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</p>
                        <p><span class="font-medium">Selesai:</span> {{ $session->ended_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }}</p>
                        <p><span class="font-medium">Data:</span> {{ $session->total_readings }} pembacaan</p>
                    </div>
                </div>

                <!-- Tombol Unduh PDF -->
                <a href="{{ route('dokter.laporan.pdf', [
                        'session_id' => $session->id,
                        'vital_signs' => $vitalSigns ?? ['heart_rate', 'spo2', 'temperature'],
                    ]) }}"
                   target="_blank"
                   class="flex items-center justify-center gap-2 w-full py-2.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-sm font-semibold rounded-lg shadow transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Unduh PDF
                </a>
            @else
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-3">
                    <p class="text-xs text-gray-500 text-center italic">Pilih sesi untuk mengunduh laporan.</p>
                </div>
            @endif
        </div>

    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
@if($session && $chartData)
<script>
    const labels = {!! json_encode($chartData['labels'] ?? []) !!};
    const datasets = [];

    @if(in_array('heart_rate', $vitalSigns ?? []))
        datasets.push({
            label: 'Heart Rate (bpm)',
            data: {!! json_encode($chartData['datasets']['heart_rate'] ?? []) !!},
            borderColor: 'rgb(220,38,38)',
            backgroundColor: 'rgba(220,38,38,0.05)',
            borderWidth: 1.5,
            pointRadius: 2,
            tension: 0.4,
            yAxisID: 'y',
        });
    @endif

    @if(in_array('spo2', $vitalSigns ?? []))
        datasets.push({
            label: 'SpO2 (%)',
            data: {!! json_encode($chartData['datasets']['spo2'] ?? []) !!},
            borderColor: 'rgb(59,130,246)',
            backgroundColor: 'rgba(59,130,246,0.05)',
            borderWidth: 1.5,
            pointRadius: 2,
            tension: 0.4,
            yAxisID: 'y1',
        });
    @endif

    @if(in_array('temperature', $vitalSigns ?? []))
        datasets.push({
            label: 'Suhu (°C)',
            data: {!! json_encode($chartData['datasets']['temperature'] ?? []) !!},
            borderColor: 'rgb(234,179,8)',
            backgroundColor: 'rgba(234,179,8,0.05)',
            borderWidth: 1.5,
            pointRadius: 2,
            tension: 0.4,
            yAxisID: 'y2',
        });
    @endif

    const ctx = document.getElementById('chartVitalSigns').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: { labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { font: { size: 8 }, maxRotation: 90, maxTicksLimit: 20 } },
                y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'HR (bpm)', font: { size: 9 } },
                    ticks: { font: { size: 9 } },
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    min: 80,
                    max: 100,
                    title: { display: true, text: 'SpO2 (%)', font: { size: 9 } },
                    ticks: { font: { size: 9 } },
                    grid: { drawOnChartArea: false },
                },
                y2: {
                    type: 'linear',
                    position: 'right',
                    title: { display: true, text: 'Suhu (°C)', font: { size: 9 } },
                    ticks: { font: { size: 9 } },
                    grid: { drawOnChartArea: false },
                },
            }
        }
    });
</script>
@endif
@endsection
