@extends('layouts.app')
@section('title', 'SATS - Detail Rekam Medis')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)] min-h-screen"
          x-data="{ showDeleteModal: false }">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('superadmin.rekam-medis') }}" class="text-[rgb(0,62,48)] hover:text-[rgb(0,80,60)] transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-[rgb(0,62,48)]">Detail Rekam Medis</h1>
            </div>
            <button @click="showDeleteModal = true"
                class="px-4 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-lg transition cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
            </button>
        </div>

        <div class="flex gap-6">
            <!-- Konten Utama -->
            <div class="flex-1 space-y-6">

                <!-- Info Session -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-[rgb(0,62,48)] mb-4">Informasi Sesi Monitoring</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">No. Rekam Medis</p>
                            <p class="font-mono font-semibold">{{ $session->medical_record_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Perangkat</p>
                            <p class="font-semibold">{{ $session->device_id }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Nakes yang Menangani</p>
                            <p class="font-semibold">{{ $session->creator?->formatted_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Dokter yang Bertugas</p>
                            <p class="font-semibold">{{ $session->dokter?->formatted_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Waktu Mulai</p>
                            <p class="font-semibold">{{ $session->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Waktu Selesai</p>
                            <p class="font-semibold">{{ $session->ended_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Total Readings</p>
                            <p class="font-semibold">{{ $stats['total_readings'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Identitas Pasien -->
                @if($session->patient)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-[rgb(0,62,48)] mb-4">Identitas Pasien</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Nama</p>
                            <p class="font-semibold">{{ $session->patient->nama }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">NIK</p>
                            <p class="font-semibold">{{ $session->patient->nik ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Umur</p>
                            <p class="font-semibold">{{ $session->patient->umur ?? '-' }} tahun</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Jenis Kelamin</p>
                            <p class="font-semibold">{{ $session->patient->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Penyakit/Alergi</p>
                            <p class="font-semibold">{{ $session->patient->penyakit_alergi ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Catatan</p>
                            <p class="font-semibold">{{ $session->patient->catatan_tambahan ?? '-' }}</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Statistik Vital Signs -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-[rgb(0,62,48)] mb-4">Statistik Vital Signs</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div class="bg-red-50 rounded-lg p-4 text-center">
                            <p class="text-xs text-red-600 font-medium mb-1">Heart Rate</p>
                            <p class="text-2xl font-bold text-red-700">{{ $stats['avg_heart_rate'] ?? '-' }}</p>
                            <p class="text-xs text-red-500">bpm (rata-rata)</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['min_heart_rate'] ?? '-' }} - {{ $stats['max_heart_rate'] ?? '-' }} bpm</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 text-center">
                            <p class="text-xs text-blue-600 font-medium mb-1">SpO2</p>
                            <p class="text-2xl font-bold text-blue-700">{{ $stats['avg_spo2'] ?? '-' }}</p>
                            <p class="text-xs text-blue-500">% (rata-rata)</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['min_spo2'] ?? '-' }} - {{ $stats['max_spo2'] ?? '-' }}%</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 text-center">
                            <p class="text-xs text-yellow-600 font-medium mb-1">Suhu</p>
                            <p class="text-2xl font-bold text-yellow-700">{{ $stats['avg_temperature'] ?? '-' }}</p>
                            <p class="text-xs text-yellow-500">°C (rata-rata)</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $stats['min_temperature'] ?? '-' }} - {{ $stats['max_temperature'] ?? '-' }}°C</p>
                        </div>
                    </div>
                </div>

                <!-- Chart Vital Signs -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-[rgb(0,62,48)] mb-4">Grafik Vital Signs</h2>
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($vitalSigns as $sign)
                            @php
                                $titles = ['heart_rate' => 'Heart Rate (bpm)', 'spo2' => 'SpO2 (%)', 'temperature' => 'Suhu (°C)'];
                                $colors = ['heart_rate' => 'rgb(220,38,38)', 'spo2' => 'rgb(59,130,246)', 'temperature' => 'rgb(234,179,8)'];
                            @endphp
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 mb-2">{{ $titles[$sign] }}</h3>
                                <canvas id="chart-{{ $sign }}" height="150"></canvas>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Tabel Sensor Readings -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-[rgb(0,62,48)]">Riwayat Sensor</h2>
                    </div>
                    <div class="overflow-x-auto max-h-96 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 bg-white">
                                <tr class="bg-[rgba(0,62,48,0.05)] border-b border-gray-200">
                                    <th class="text-left px-4 py-2 font-semibold text-[rgb(0,62,48)]">Waktu</th>
                                    <th class="text-center px-4 py-2 font-semibold text-[rgb(0,62,48)]">Heart Rate</th>
                                    <th class="text-center px-4 py-2 font-semibold text-[rgb(0,62,48)]">SpO2</th>
                                    <th class="text-center px-4 py-2 font-semibold text-[rgb(0,62,48)]">Suhu</th>
                                    <th class="text-center px-4 py-2 font-semibold text-[rgb(0,62,48)]">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($session->sensorReadings->take(50) as $reading)
                                    <tr class="hover:bg-[rgba(0,62,48,0.02)]">
                                        <td class="px-4 py-2 text-xs">{{ $reading->recorded_at?->setTimezone('Asia/Jakarta')->format('H:i:s') }}</td>
                                        <td class="px-4 py-2 text-center">{{ $reading->heart_rate }}</td>
                                        <td class="px-4 py-2 text-center">{{ $reading->spo2 }}%</td>
                                        <td class="px-4 py-2 text-center">{{ $reading->temperature }}°C</td>
                                        <td class="px-4 py-2 text-center">
                                            @if($reading->status === 'critical')
                                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded-full font-medium">Kritis</span>
                                            @elseif($reading->status === 'warning')
                                                <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded-full font-medium">Peringatan</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full font-medium">Normal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($session->sensorReadings->count() > 50)
                        <div class="px-4 py-2 bg-gray-50 text-xs text-gray-500 text-center">
                            Menampilkan 50 dari {{ $session->sensorReadings->count() }} data. Unduh PDF untuk data lengkap.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar Kanan -->
            <div class="w-56 flex-shrink-0" x-data="{
                dari: '{{ $session->started_at?->setTimezone('Asia/Jakarta')->format('H:i') }}',
                sampai: '{{ $session->ended_at?->setTimezone('Asia/Jakarta')->format('H:i') ?? now()->setTimezone('Asia/Jakarta')->format('H:i') }}',
                count: {{ $stats['total_readings'] ?? 0 }},
                loading: false,
            }">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 sticky top-6 space-y-3">
                    <h3 class="text-sm font-semibold text-[rgb(0,62,48)]">Unduh PDF</h3>

                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Dari Jam</label>
                        <input type="time" x-model="dari"
                            class="w-full px-2.5 py-1.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Sampai Jam</label>
                        <input type="time" x-model="sampai"
                            class="w-full px-2.5 py-1.5 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                    </div>

                    <a :href="'{{ route('superadmin.rekam-medis.pdf', $session->id) }}' + '?dari=' + dari + '&sampai=' + sampai"
                       class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-sm font-medium rounded-md transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Unduh PDF
                    </a>

                    <div class="border-t border-gray-100 pt-3 text-xs text-gray-500 space-y-1">
                        <p><span class="font-medium text-gray-700">RM:</span> {{ $session->medical_record_number }}</p>
                        <p><span class="font-medium text-gray-700">Data:</span> <span x-text="count + ' readings'"></span></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Delete Confirmation Modal --}}
        <div x-show="showDeleteModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="showDeleteModal = false">
            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200" x-transition>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Hapus Rekam Medis</h3>
                        <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-6">
                    Yakin ingin menghapus rekam medis <strong>{{ $session->medical_record_number }}</strong>? Semua data sensor readings terkait juga akan dihapus.
                </p>
                <div class="flex justify-end gap-3 mt-2">
                    <button @click="showDeleteModal = false"
                        class="px-5 py-2.5 text-sm font-medium rounded-lg cursor-pointer"
                        style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db;">
                        Batal
                    </button>
                    <button @click="
                        fetch('{{ route('superadmin.rekam-medis.destroy', $session->id) }}', {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                        }).then(r => r.json()).then(d => {
                            if (d.success) { window.location.href = '{{ route('superadmin.rekam-medis') }}'; }
                            else { alert(d.message || 'Gagal menghapus'); }
                        }).catch(() => alert('Gagal menghapus'))
                    "
                        class="px-5 py-2.5 text-sm font-medium rounded-lg cursor-pointer"
                        style="background: #dc2626; color: #ffffff;">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const chartData = @json($chartData);
            const vitalSigns = @json($vitalSigns);
            const colors = {
                heart_rate: 'rgb(220,38,38)',
                spo2: 'rgb(59,130,246)',
                temperature: 'rgb(234,179,8)',
            };

            vitalSigns.forEach(sign => {
                const ctx = document.getElementById('chart-' + sign);
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: sign,
                            data: chartData.datasets[sign],
                            borderColor: colors[sign],
                            borderWidth: 2,
                            pointRadius: 1,
                            tension: 0.4,
                            fill: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { display: true, ticks: { maxTicksLimit: 8, font: { size: 10 } } },
                            y: { display: true, ticks: { font: { size: 10 } } }
                        }
                    }
                });
            });
        });
    </script>
@endsection
