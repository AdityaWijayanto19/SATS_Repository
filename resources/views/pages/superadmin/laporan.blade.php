@extends('layouts.app')
@section('title', 'SATS Monitoring - Laporan')

@section('content')
<main class="flex-1 overflow-y-auto p-8 bg-[rgba(230,238,236,0.5)] min-h-screen"
      x-data="{ tab: '{{ $tab }}' }">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Laporan</h1>
            <p class="text-sm text-gray-500 mt-1">Laporan operasional dan audit keamanan sistem SATS</p>
        </div>
    </div>

    {{-- Filter Tanggal (berlaku untuk kedua tab) --}}
    <form method="GET" action="{{ route('superadmin.laporan') }}" class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
        <div class="flex items-end gap-4 flex-wrap">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Rentang Tanggal</label>
                <div class="flex items-center gap-2">
                    <input type="date" name="dari" value="{{ $dari }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                    <span class="text-gray-400 text-sm">s/d</span>
                    <input type="date" name="sampai" value="{{ $sampai }}"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                </div>
            </div>

            <input type="hidden" name="tab" :value="tab">

            <button type="submit"
                class="px-5 py-2 text-sm font-medium text-white rounded-lg cursor-pointer transition-all hover:opacity-90"
                style="background: rgb(0,83,63);">
                Terapkan
            </button>
        </div>
    </form>

    {{-- Tab Switcher --}}
    <div class="flex gap-1 mb-6 bg-white rounded-lg border border-gray-200 p-1 w-fit">
        <button @click="tab = 'operasional'"
            :class="tab === 'operasional' ? 'bg-[rgb(0,62,48)] text-white' : 'text-gray-600 hover:bg-gray-100'"
            class="px-5 py-2 text-sm font-medium rounded-md transition-all cursor-pointer">
            Operasional
        </button>
        <button @click="tab = 'audit'"
            :class="tab === 'audit' ? 'bg-[rgb(0,62,48)] text-white' : 'text-gray-600 hover:bg-gray-100'"
            class="px-5 py-2 text-sm font-medium rounded-md transition-all cursor-pointer">
            Audit Keamanan
        </button>
    </div>

    {{-- ======================== TAB OPERASIONAL ======================== --}}
    <div x-show="tab === 'operasional'" x-cloak>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-4 gap-5 mb-6">
            <div class="rounded-xl p-5 border" style="background: rgba(0,62,48,0.05); border-color: rgba(0,62,48,0.15);">
                <p class="text-3xl font-bold" style="color: rgb(0,62,48);">{{ $operasionalData['totalSesi'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Sesi Monitoring</p>
            </div>
            <div class="rounded-xl p-5 border" style="background: rgba(59,130,246,0.06); border-color: rgba(59,130,246,0.2);">
                <p class="text-3xl font-bold text-blue-600">{{ $operasionalData['totalPasien'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Pasien</p>
            </div>
            <div class="rounded-xl p-5 border" style="background: rgba(16,185,129,0.06); border-color: rgba(16,185,129,0.2);">
                <p class="text-3xl font-bold text-emerald-600">{{ $operasionalData['deviceAktif'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Device Aktif</p>
            </div>
            <div class="rounded-xl p-5 border" style="background: rgba(239,68,68,0.06); border-color: rgba(239,68,68,0.2);">
                <p class="text-3xl font-bold text-red-600">{{ $operasionalData['dataGagal'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Data Gagal Diproses</p>
            </div>
        </div>

        {{-- Chart Tren --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
            <h2 class="text-base font-semibold text-gray-700 mb-3">Tren Sesi Monitoring per Hari</h2>
            <div style="height: 220px;">
                <canvas id="chartTren"></canvas>
            </div>
        </div>

        {{-- Grid: Device Utilization + Distribusi --}}
        <div class="grid grid-cols-2 gap-6 mb-6">
            {{-- Device Utilization --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-700">Utilisasi Device</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-left">
                                <th class="px-4 py-2.5 font-semibold text-gray-600">Device</th>
                                <th class="px-4 py-2.5 font-semibold text-gray-600 text-center">Jumlah Sesi</th>
                                <th class="px-4 py-2.5 font-semibold text-gray-600 text-center">Durasi Rata-rata</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($operasionalData['deviceUtilization'] as $du)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-2.5 font-mono text-xs">{{ $du->device_id }}</td>
                                <td class="px-4 py-2.5 text-center font-medium">{{ $du->jumlah_sesi }}</td>
                                <td class="px-4 py-2.5 text-center text-gray-600">{{ round($du->durasi_avg ?? 0) }} menit</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-400">Tidak ada data</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Distribusi Kondisi --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-700 mb-3">Distribusi Kondisi</h2>
                <div style="height: 220px;">
                    <canvas id="chartDistribusi"></canvas>
                </div>
                <div class="flex justify-center gap-5 mt-2 text-xs text-gray-500">
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded-full bg-emerald-500"></span> Normal
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded-full bg-amber-500"></span> Warning
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="inline-block w-3 h-3 rounded-full bg-red-500"></span> Critical
                    </span>
                </div>
            </div>
        </div>

        {{-- Download PDF --}}
        <div class="flex justify-end mb-4">
            <a href="{{ route('superadmin.laporan.pdf', ['dari' => $dari, 'sampai' => $sampai, 'device_id' => $deviceId, 'export_type' => 'operasional']) }}"
               target="_blank"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white cursor-pointer transition-all hover:opacity-90 shadow"
               style="background: rgb(0,83,63);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh PDF Operasional
            </a>
        </div>
    </div>

    {{-- ======================== TAB AUDIT KEAMANAN ======================== --}}
    <div x-show="tab === 'audit'" x-cloak>

        {{-- Filter Audit (kategori + device) --}}
        <form method="GET" action="{{ route('superadmin.laporan') }}" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <div class="flex items-end gap-4 flex-wrap">
                {{-- Kirim ulang date range --}}
                <input type="hidden" name="dari" value="{{ $dari }}">
                <input type="hidden" name="sampai" value="{{ $sampai }}">
                <input type="hidden" name="tab" value="audit">

                <div class="w-48">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Kategori Event</label>
                    <select name="kategori"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                        @foreach($kategoriList as $key => $label)
                            <option value="{{ $key }}" {{ $kategori === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-52">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Device</label>
                    <select name="device_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                        <option value="">Semua Device</option>
                        @foreach($devices as $d)
                            <option value="{{ $d->device_id }}" {{ $deviceId === $d->device_id ? 'selected' : '' }}>{{ $d->device_id }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white rounded-lg cursor-pointer transition-all hover:opacity-90"
                    style="background: rgb(0,83,63);">
                    Filter Audit
                </button>
            </div>
        </form>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-4 gap-5 mb-6">
            <div class="rounded-xl p-5 border" style="background: rgba(0,62,48,0.05); border-color: rgba(0,62,48,0.15);">
                <p class="text-3xl font-bold" style="color: rgb(0,62,48);">{{ $auditData['totalAktivitas'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Total Aktivitas</p>
            </div>
            <div class="rounded-xl p-5 border" style="background: rgba(59,130,246,0.06); border-color: rgba(59,130,246,0.2);">
                <p class="text-3xl font-bold text-blue-600">{{ $auditData['loginHariIni'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Login Hari Ini</p>
            </div>
            <div class="rounded-xl p-5 border" style="background: rgba(168,85,247,0.06); border-color: rgba(168,85,247,0.2);">
                <p class="text-3xl font-bold text-purple-600">{{ $auditData['perubahanUser'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Perubahan User</p>
            </div>
            <div class="rounded-xl p-5 border" style="background: rgba(234,179,8,0.06); border-color: rgba(234,179,8,0.2);">
                <p class="text-3xl font-bold text-yellow-600">{{ $auditData['perubahanDevice'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Perubahan Device</p>
            </div>
        </div>

        {{-- Tabel Log Aktivitas --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Log Aktivitas</h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} &ndash; {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}
                    @if($kategori) &middot; Kategori: <strong>{{ ucfirst($kategori) }}</strong> @endif
                    @if($deviceId) &middot; Device: <strong>{{ $deviceId }}</strong> @endif
                </p>
            </div>

            <div class="overflow-x-auto max-h-[500px] overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white z-10">
                        <tr class="bg-gray-50 text-left">
                            <th class="px-4 py-2.5 font-semibold text-gray-600">Waktu</th>
                            <th class="px-4 py-2.5 font-semibold text-gray-600">Tipe</th>
                            <th class="px-4 py-2.5 font-semibold text-gray-600">Pesan</th>
                            <th class="px-4 py-2.5 font-semibold text-gray-600">User</th>
                            <th class="px-4 py-2.5 font-semibold text-gray-600">Role</th>
                            <th class="px-4 py-2.5 font-semibold text-gray-600">Device</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($auditData['logs'] as $log)
                        @php
                            $colorMap = [
                                'blue' => 'bg-blue-100 text-blue-700',
                                'red' => 'bg-red-100 text-red-700',
                                'emerald' => 'bg-emerald-100 text-emerald-700',
                                'violet' => 'bg-violet-100 text-violet-700',
                                'amber' => 'bg-amber-100 text-amber-700',
                                'indigo' => 'bg-indigo-100 text-indigo-700',
                                'green' => 'bg-green-100 text-green-700',
                                'teal' => 'bg-teal-100 text-teal-700',
                                'gray' => 'bg-gray-100 text-gray-700',
                            ];
                            $badgeClass = $colorMap[$log->icon] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-600">
                                {{ $log->created_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $badgeClass }}">
                                    {{ $log->type }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-gray-700 max-w-xs truncate">{{ $log->message }}</td>
                            <td class="px-4 py-2.5 text-gray-700">{{ $log->user_name ?? '-' }}</td>
                            <td class="px-4 py-2.5">
                                @if($log->user_role)
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">{{ $log->user_role }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs text-gray-600">{{ $log->device_id ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-gray-400">Tidak ada log aktivitas pada filter ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($auditData['logs']->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $auditData['logs']->appends(['dari' => $dari, 'sampai' => $sampai, 'kategori' => $kategori, 'device_id' => $deviceId, 'tab' => 'audit'])->links() }}
            </div>
            @endif
        </div>

        {{-- Download PDF --}}
        <div class="flex justify-end mt-4 mb-4">
            <a href="{{ route('superadmin.laporan.pdf', ['dari' => $dari, 'sampai' => $sampai, 'device_id' => $deviceId, 'kategori' => $kategori, 'export_type' => 'audit']) }}"
               target="_blank"
               class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white cursor-pointer transition-all hover:opacity-90 shadow"
               style="background: rgb(0,83,63);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh PDF Audit
            </a>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tren chart
        const trenData = @json($operasionalData['trenPerHari']);
        if (trenData.length > 0) {
            const ctxTren = document.getElementById('chartTren').getContext('2d');
            new Chart(ctxTren, {
                type: 'line',
                data: {
                    labels: trenData.map(d => d.tanggal),
                    datasets: [{
                        label: 'Jumlah Sesi',
                        data: trenData.map(d => d.jumlah),
                        borderColor: 'rgb(0,62,48)',
                        backgroundColor: 'rgba(0,62,48,0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        tension: 0.3,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Jumlah Sesi' },
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });
        }

        // Distribusi kondisi pie chart
        const distribusi = @json($operasionalData['distribusiKondisi']);
        const normalCount = distribusi.normal || 0;
        const warningCount = distribusi.warning || 0;
        const criticalCount = distribusi.critical || 0;

        if (normalCount + warningCount + criticalCount > 0) {
            const ctxDist = document.getElementById('chartDistribusi').getContext('2d');
            new Chart(ctxDist, {
                type: 'doughnut',
                data: {
                    labels: ['Normal', 'Warning', 'Critical'],
                    datasets: [{
                        data: [normalCount, warningCount, criticalCount],
                        backgroundColor: [
                            'rgba(16,185,129,0.8)',
                            'rgba(245,158,11,0.8)',
                            'rgba(239,68,68,0.8)',
                        ],
                        borderWidth: 0,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                }
            });
        }
    });
</script>
@endsection
