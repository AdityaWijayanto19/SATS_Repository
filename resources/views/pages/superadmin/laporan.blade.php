@extends('layouts.app')

@section('content')
<main class="flex-1 overflow-y-auto p-8 bg-[rgba(230,238,236,0.5)] min-h-screen">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Laporan</h1>
            <p class="text-sm text-gray-500 mt-1">Laporan data sensor seluruh perangkat SATS</p>
        </div>
    </div>

    {{-- Filter Pencarian --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <div class="flex items-end gap-4 flex-wrap">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rentang Tanggal</label>
                <div class="flex items-center gap-2">
                    <input type="date" id="inputDari" value="{{ $dari }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                    <span class="text-gray-400 text-sm">s/d</span>
                    <input type="date" id="inputSampai" value="{{ $sampai }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                </div>
            </div>
            <div class="w-56">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Ambulans</label>
                <select id="inputAmbulans"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                    <option value="">Semua Ambulans</option>
                    @foreach($daftarAmbulans as $a)
                        <option value="{{ $a }}" {{ $ambulans === $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <button onclick="filterData()"
                class="px-5 py-2 text-sm font-medium text-white rounded-lg cursor-pointer transition-all hover:opacity-90"
                style="background: rgb(0,83,63);">
                Terapkan
            </button>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-3 gap-5 mb-6">
        <div class="rounded-xl p-5 border" style="background: rgba(59,130,246,0.06); border-color: rgba(59,130,246,0.2);">
            <p class="text-3xl font-bold text-blue-600">{{ $totalPenggunaanAlat }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Penggunaan Alat</p>
        </div>
        <div class="rounded-xl p-5 border" style="background: rgba(236,72,153,0.06); border-color: rgba(236,72,153,0.2);">
            <p class="text-3xl font-bold text-pink-500">{{ $totalAktivitasUser }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Aktivitas User</p>
        </div>
        <div class="rounded-xl p-5 border" style="background: rgba(245,158,11,0.06); border-color: rgba(245,158,11,0.2);">
            <p class="text-3xl font-bold text-amber-500">{{ $totalLaporanTersimpan }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Laporan Tersimpan</p>
        </div>
    </div>

    {{-- Grafik Vital Sign --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <h2 class="text-base font-semibold text-gray-700 mb-3">Grafik Vital Sign</h2>
        <div style="height: 220px;">
            <canvas id="chartVitalSign"></canvas>
        </div>
        <div class="flex justify-center gap-5 mt-2 text-xs text-gray-500">
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-4 h-0.5 bg-red-500"></span> Heart Rate (bpm)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-4 h-0.5 bg-blue-500"></span> SpO2 (%)
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block w-4 h-0.5 bg-amber-500"></span> Suhu (&deg;C)
            </span>
        </div>
    </div>

    {{-- Tabel Data Sensor --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Data Sensor Perangkat</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}
                    {{ $ambulans ? ' &middot; ' . $ambulans : '' }}
                </p>
            </div>
            {{-- Tombol Unduh PDF --}}
            <a href="{{ route('superadmin.laporan.pdf', ['dari' => $dari, 'sampai' => $sampai, 'ambulans' => $ambulans]) }}"
               target="_blank"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white cursor-pointer transition-all hover:opacity-90 shadow"
               style="background: rgb(0,83,63);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh Laporan PDF
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-5 py-3 font-semibold text-gray-600">Waktu</th>
                        <th class="px-5 py-3 font-semibold text-gray-600">Perangkat</th>
                        <th class="px-5 py-3 font-semibold text-gray-600">Ambulans</th>
                        <th class="px-5 py-3 font-semibold text-gray-600 text-center">Heart Rate</th>
                        <th class="px-5 py-3 font-semibold text-gray-600 text-center">SpO2</th>
                        <th class="px-5 py-3 font-semibold text-gray-600 text-center">Suhu</th>
                        <th class="px-5 py-3 font-semibold text-gray-600 text-center">Klasifikasi</th>
                    </tr>
                </thead>
                {{-- TODO: Ganti dengan data real dari backend --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse($dataSensor as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-2.5 font-mono text-xs text-gray-600">{{ $row->waktu }}</td>
                        <td class="px-5 py-2.5 text-gray-700">{{ $row->device }}</td>
                        <td class="px-5 py-2.5 text-gray-500">{{ $row->ambulans }}</td>
                        <td class="px-5 py-2.5 text-center font-medium {{ $row->heart_rate > 100 ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $row->heart_rate }} bpm
                        </td>
                        <td class="px-5 py-2.5 text-center font-medium {{ $row->spo2 < 95 ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $row->spo2 }}%
                        </td>
                        <td class="px-5 py-2.5 text-center font-medium {{ $row->temperature > 37.5 ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $row->temperature }}&deg;C
                        </td>
                        <td class="px-5 py-2.5 text-center">
                            <span class="text-xs font-medium px-2.5 py-1 rounded-full
                                @if($row->klasifikasi === 'Normal') text-emerald-700 bg-emerald-50
                                @elseif($row->klasifikasi === 'Warning') text-amber-700 bg-amber-50
                                @else text-red-700 bg-red-50 @endif">
                                {{ $row->klasifikasi }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-gray-400">Tidak ada data sensor pada rentang tanggal ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Data untuk chart
    const labels = {!! json_encode($dataSensor->pluck('waktu')->toArray()) !!};
    const heartRates = {!! json_encode($dataSensor->pluck('heart_rate')->toArray()) !!};
    const spo2Values = {!! json_encode($dataSensor->pluck('spo2')->toArray()) !!};
    const temperatures = {!! json_encode($dataSensor->pluck('temperature')->toArray()) !!};

    const ctx = document.getElementById('chartVitalSign').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Heart Rate (bpm)',
                    data: heartRates,
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 3,
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'SpO2 (%)',
                    data: spo2Values,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 3,
                    tension: 0.4,
                    yAxisID: 'y1',
                },
                {
                    label: 'Suhu (°C)',
                    data: temperatures,
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245,158,11,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 3,
                    tension: 0.4,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { font: { size: 8 }, maxRotation: 90 } },
                y: {
                    type: 'linear',
                    position: 'left',
                    title: { display: true, text: 'Heart Rate (bpm)', font: { size: 9 } },
                    ticks: { font: { size: 9 } }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    min: 80,
                    max: 100,
                    title: { display: true, text: 'SpO2 (%)', font: { size: 9 } },
                    ticks: { font: { size: 9 } },
                    grid: { drawOnChartArea: false }
                },
                y2: {
                    type: 'linear',
                    position: 'right',
                    min: 35,
                    max: 40,
                    title: { display: true, text: 'Suhu (°C)', font: { size: 9 } },
                    ticks: { font: { size: 9 } },
                    grid: { drawOnChartArea: false },
                    offset: true
                }
            }
        }
    });

    function filterData() {
        const dari = document.getElementById('inputDari').value;
        const sampai = document.getElementById('inputSampai').value;
        const ambulans = document.getElementById('inputAmbulans').value;
        if (!dari || !sampai) return alert('Pilih tanggal mulai dan selesai.');
        const url = new URL(window.location.href);
        url.searchParams.set('dari', dari);
        url.searchParams.set('sampai', sampai);
        if (ambulans) {
            url.searchParams.set('ambulans', ambulans);
        } else {
            url.searchParams.delete('ambulans');
        }
        window.location.href = url.toString();
    }
</script>
@endsection
