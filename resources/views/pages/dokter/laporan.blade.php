@extends('layouts.app')
@section('title', 'SATS Monitoring - Laporan')

@section('content')
<main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)] min-h-screen">

    <h1 class="text-3xl font-bold text-[rgb(0,62,48)] mb-6">Laporan</h1>

    <div class="flex gap-6 items-start">

        <!-- Konten Laporan (Kiri) -->
        <div class="flex-1 space-y-4">

            <!-- Identitas Pasien -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                <h2 class="text-base font-semibold text-[rgb(0,62,48)] text-center mb-4">
                    Laporan Medis Pasien: {{ $pasien?->no_rekam_medis ?? '24E56' }} – {{ $pasien?->nama_lengkap ?? 'Budi Santoso' }}
                </h2>
                <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm text-gray-700">
                    <div>
                        <p><span class="font-semibold">Nama Lengkap</span> : {{ $pasien?->nama_lengkap ?? 'Budi Santoso' }}</p>
                        <p><span class="font-semibold">NIK</span> : {{ $pasien?->nik ?? '33161224234777' }}</p>
                        <p><span class="font-semibold">Usia</span> : {{ $pasien?->usia ?? '59' }} tahun</p>
                        <p><span class="font-semibold">Jenis Kelamin</span> : {{ $pasien?->jenis_kelamin == 'L' ? 'Laki-laki' : ($pasien?->jenis_kelamin == 'P' ? 'Perempuan' : 'Laki-laki') }}</p>
                    </div>
                    <div>
                        <p><span class="font-semibold">Penyakit/Alergi</span> : {{ $pasien?->penyakit_alergi ?? 'Serangan Jantung' }}</p>
                        <p class="mt-1"><span class="font-semibold">Catatan Tambahan</span> : {{ $pasien?->catatan_tambahan ?? 'Harus dipantau setiap menitnya' }}</p>
                    </div>
                </div>
            </div>

            <!-- 
                Banner Prediksi ML
                TODO: Isi $prediksi dari controller via endpoint GET /api/device/{device_id}/prediction
                Struktur object: risk_level ('normal'|'warning'|'critical'), risk_percent, timeframe_minutes, message
            -->
            @php
                $riskLevel  = $prediksi->risk_level        ?? 'warning';
                $riskPercent = $prediksi->risk_percent      ?? 20;
                $riskMenit  = $prediksi->timeframe_minutes  ?? 15;
                $riskPesan  = $prediksi->message            ?? 'Kondisi pasien berpotensi memburuk ' . $riskPercent . '% dalam ' . $riskMenit . ' menit ke depan berdasarkan tren Heart Rate dan SpO2.';

                [$riskDot, $riskBadgeCls, $riskBadgeLabel, $riskBannerBg] = match($riskLevel) {
                    'critical' => ['bg-red-500',    'bg-red-100 text-red-700',       'Kritis',    'bg-red-50 border-red-200'],
                    'normal'   => ['bg-green-500',  'bg-green-100 text-green-700',   'Normal',    'bg-green-50 border-green-200'],
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

            <!-- Grafik Tekanan Darah + Nilai Vital + Klasifikasi ML -->
            <div class="grid grid-cols-5 gap-4">

                <!-- Grafik Tekanan Darah -->
                <div class="col-span-3 bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                    <p class="text-xs font-semibold text-gray-600 text-center mb-2">Tekanan Darah (Sistolik &amp; Diastolik)</p>
                    <div class="w-full" style="height: 180px;">
                        <canvas id="chartTekananDarah"></canvas>
                    </div>
                    <div class="flex justify-center gap-4 mt-2 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <span class="inline-block w-4 h-0.5 bg-red-500"></span> Sistolik
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block w-4 h-0.5 bg-blue-500"></span> Diastolik
                        </span>
                    </div>
                </div>

                <!-- Nilai Vital + Klasifikasi ML -->
                <div class="col-span-2 flex flex-col gap-4">

                    <!-- Nilai Vital -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <p class="text-sm font-bold text-[rgb(0,62,48)] mb-3">Nilai Vital</p>
                        <div class="flex justify-around items-center">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 mb-1">Detak Jantung</p>
                                <p class="text-4xl font-black text-gray-800 leading-none">{{ $vitalTerbaru?->detak_jantung ?? '72' }}</p>
                                <p class="text-xs text-gray-400 mt-1">bpm</p>
                            </div>
                            <div class="w-px h-12 bg-gray-200"></div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500 mb-1">SPO2</p>
                                <p class="text-4xl font-black text-gray-800 leading-none">
                                    {{ $vitalTerbaru?->spo2 ?? '98' }}<span class="text-2xl">%</span>
                                </p>
                                <p class="text-xs text-gray-400 mt-1">saturasi</p>
                            </div>
                        </div>
                    </div>

                    <!-- Klasifikasi ML -->
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <p class="text-sm font-bold text-[rgb(0,62,48)] mb-2">Hasil Klasifikasi ML</p>
                        @php
                            $status = $vitalTerbaru?->klasifikasi ?? 'Normal';
                            $statusColor = match($status) {
                                'Warning'  => 'bg-yellow-100 text-yellow-700 border-yellow-300',
                                'Critical' => 'bg-red-100 text-red-700 border-red-300',
                                default    => 'bg-green-100 text-green-700 border-green-300',
                            };
                        @endphp
                        <div class="flex justify-center mb-2">
                            <span class="px-5 py-1 rounded-full border text-sm font-semibold {{ $statusColor }}">
                                {{ $status }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 text-center">
                            <span class="font-semibold">Klasifikasi Otomatis :</span><br>
                            {{ $vitalTerbaru?->keterangan_klasifikasi ?? 'Kondisi Pasien Stabil' }}
                        </p>
                        <div class="flex justify-center gap-3 mt-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block"></span> Normal
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 inline-block"></span> Warning
                            </span>
                            <span class="flex items-center gap-1">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Critical
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Tabel Riwayat Kondisi Pasien -->
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="bg-[rgba(0,62,48,0.06)] px-4 py-3 border-b border-gray-200">
                    <p class="text-sm font-semibold text-[rgb(0,62,48)] text-center">Riwayat Kondisi Pasien</p>
                </div>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-2 px-4 text-left font-semibold text-gray-600 w-32">Waktu</th>
                            <th class="py-2 px-4 text-left font-semibold text-gray-600">Riwayat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat ?? [] as $item)
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-2 px-4 text-gray-500 font-mono text-xs">{{ $item->waktu }}</td>
                                <td class="py-2 px-4 text-gray-700">{{ $item->keterangan }}</td>
                            </tr>
                        @empty
                            @foreach([
                                ['10.38.59', 'Kondisi Pasien Warning'],
                                ['10.40.59', 'Detak jantung meningkat'],
                                ['10.42.59', 'Tabung Oksigen Dipasang'],
                                ['10.44.59', 'Tekanan Darah Menurun'],
                            ] as $row)
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                <td class="py-2 px-4 text-gray-500 font-mono text-xs">{{ $row[0] }}</td>
                                <td class="py-2 px-4 text-gray-700">{{ $row[1] }}</td>
                            </tr>
                            @endforeach
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Filter & Unduh -->
        <div class="w-52 flex-shrink-0 space-y-3 sticky top-6">
            <p class="text-sm font-semibold text-[rgb(0,62,48)]">Rentang Tanggal</p>
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-3 space-y-2">
                <div class="flex items-center gap-1.5 text-xs text-gray-600">
                    <svg class="w-4 h-4 text-[rgb(0,62,48)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>
                        {{ isset($dari)   ? \Carbon\Carbon::parse($dari)->format('d/m/Y')   : '12/03/2026' }}
                        –
                        {{ isset($sampai) ? \Carbon\Carbon::parse($sampai)->format('d/m/Y') : '16/03/2026' }}
                    </span>
                </div>
                <input type="date" id="inputDari"
                    value="{{ $dari ?? '2026-03-12' }}"
                    class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-gray-600 focus:outline-none focus:ring-1 focus:ring-[rgb(0,62,48)]">
                <input type="date" id="inputSampai"
                    value="{{ $sampai ?? '2026-03-16' }}"
                    class="w-full text-xs border border-gray-200 rounded px-2 py-1 text-gray-600 focus:outline-none focus:ring-1 focus:ring-[rgb(0,62,48)]">
                <button onclick="filterRentang()"
                    class="w-full py-1.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-xs rounded transition">
                    Terapkan
                </button>
            </div>

            <!-- Tombol Unduh PDF -->
            <a href="{{ route('laporan.pdf', [
                    'pasien_id' => $pasien?->id ?? 1,
                    'dari'      => $dari    ?? '2026-03-12',
                    'sampai'    => $sampai  ?? '2026-03-16',
                ]) }}"
               target="_blank"
               class="flex items-center justify-center gap-2 w-full py-2.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-sm font-semibold rounded-lg shadow transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh PDF
            </a>
        </div>

    </div>
</main>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const labels    = {!! json_encode($labelGrafik   ?? ['PM001','PM002','PM003','PM004','PM005','PM006','PM007','PM008','PM009','PM010','PM011','PM012','PM013','PM014','PM015']) !!};
    const sistolik  = {!! json_encode($dataSistolik  ?? [130,125,135,128,140,132,138,126,134,129,137,131,136,124,133]) !!};
    const diastolik = {!! json_encode($dataDiastolik ?? [82,78,85,80,88,83,86,79,84,81,87,82,85,78,83]) !!};

    const ctx = document.getElementById('chartTekananDarah').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Sistolik',
                    data: sistolik,
                    borderColor: 'rgb(220,38,38)',
                    backgroundColor: 'rgba(220,38,38,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    tension: 0.4,
                },
                {
                    label: 'Diastolik',
                    data: diastolik,
                    borderColor: 'rgb(59,130,246)',
                    backgroundColor: 'rgba(59,130,246,0.05)',
                    borderWidth: 1.5,
                    pointRadius: 2,
                    tension: 0.4,
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
                    ticks: { font: { size: 9 } },
                    title: { display: true, text: 'Tekanan (mmHg)', font: { size: 9 } }
                }
            }
        }
    });

    function filterRentang() {
        const dari   = document.getElementById('inputDari').value;
        const sampai = document.getElementById('inputSampai').value;
        if (!dari || !sampai) return alert('Pilih tanggal mulai dan selesai.');
        const url = new URL(window.location.href);
        url.searchParams.set('dari', dari);
        url.searchParams.set('sampai', sampai);
        window.location.href = url.toString();
    }
</script>
@endsection