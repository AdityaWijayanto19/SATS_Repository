<!-- Banner Prediksi ML -->
@php
    $riskLevel  = 'warning';
    $riskPercent = 20;
    $riskMenit  = 15;
    $riskPesan  = 'Kondisi pasien berpotensi memburuk ' . $riskPercent . '% dalam ' . $riskMenit . ' menit ke depan.';
    [$riskDot, $riskBadgeCls, $riskBadgeLabel, $riskBannerBg] = match($riskLevel) {
        'critical' => ['bg-red-500', 'bg-red-100 text-red-700', 'Kritis', 'bg-red-50 border-red-200'],
        'normal'   => ['bg-green-500', 'bg-green-100 text-green-700', 'Normal', 'bg-green-50 border-green-200'],
        default    => ['bg-orange-400', 'bg-orange-100 text-orange-700', 'Perhatian', 'bg-[rgba(0,62,48,0.05)] border-[rgba(0,62,48,0.18)]'],
    };
@endphp
<div class="flex items-center gap-4 mt-4 {{ $riskBannerBg }} border rounded-xl px-5 py-3.5">
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
