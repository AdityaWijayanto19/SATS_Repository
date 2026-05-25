<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Medis – {{ $session->medical_record_number ?? 'Unknown' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1a1a1a;
            background: #fff;
            padding: 28px 32px;
        }

        /* Header */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid rgb(0,62,48);
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header-brand { display: table-cell; vertical-align: middle; }
        .header-brand img { display: inline-block; vertical-align: middle; margin-right: 10px; width: 44px; height: 44px; }
        .header-brand .brand-text .name    { font-size: 14px; font-weight: bold; color: rgb(0,62,48); }
        .header-brand .brand-text .tagline { font-size: 9px; color: #555; margin-top: 1px; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; font-size: 10px; color: #555; }
        .header-right .doc-title { font-size: 15px; font-weight: bold; color: rgb(0,62,48); }

        /* Section Card */
        .section-card {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            margin-top: 12px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .section-card-header {
            background: rgba(0,62,48,0.07);
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            color: rgb(0,62,48);
            text-align: center;
        }
        .section-card-body { padding: 10px 12px; }

        /* Identitas Pasien */
        .identitas-grid { display: table; width: 100%; }
        .identitas-col { display: table-cell; width: 50%; vertical-align: top; padding-right: 12px; }
        .identitas-col:last-child { padding-right: 0; padding-left: 12px; }
        .identitas-row { margin-bottom: 3px; font-size: 10.5px; }
        .identitas-row .label { font-weight: bold; color: #333; }

        /* Banner Prediksi ML */
        .ml-banner {
            display: table;
            width: 100%;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 12px;
            border: 1px solid;
        }
        .ml-banner.warning  { background: #fffbeb; border-color: #fcd34d; }
        .ml-banner.critical { background: #fff1f1; border-color: #fca5a5; }
        .ml-banner.normal   { background: #f0fdf4; border-color: #86efac; }
        .ml-banner-dot { display: table-cell; width: 8px; vertical-align: top; padding-top: 3px; }
        .ml-banner-dot-inner { width: 8px; height: 8px; border-radius: 50%; }
        .ml-banner-dot-inner.warning  { background: #f59e0b; }
        .ml-banner-dot-inner.critical { background: #ef4444; }
        .ml-banner-dot-inner.normal   { background: #22c55e; }
        .ml-banner-content { display: table-cell; vertical-align: top; padding: 0 10px; }
        .ml-banner-label { font-size: 8.5px; font-weight: bold; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .ml-banner-text  { font-size: 10.5px; color: rgb(0,62,48); font-weight: 500; }
        .ml-banner-badge-cell { display: table-cell; vertical-align: middle; text-align: right; }
        .ml-banner-badge { font-size: 9px; font-weight: bold; padding: 2px 10px; border-radius: 999px; }
        .ml-banner-badge.warning  { background: #fef3c7; color: #92400e; }
        .ml-banner-badge.critical { background: #fee2e2; color: #b91c1c; }
        .ml-banner-badge.normal   { background: #dcfce7; color: #15803d; }

        /* Dua Kolom: grafik + vital/stats */
        .row-cols { display: table; width: 100%; border-spacing: 12px; margin: -12px -12px 0; }
        .col-chart { display: table-cell; width: 60%; vertical-align: top; }
        .col-side  { display: table-cell; width: 40%; vertical-align: top; }

        /* Nilai Vital */
        .vital-box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
        }
        .vital-title { font-size: 11px; font-weight: bold; color: rgb(0,62,48); margin-bottom: 8px; }
        .vital-values { width: 100%; }
        .vital-item { display: inline-block; width: 31%; text-align: center; vertical-align: middle; }
        .vital-item .label { font-size: 9px; color: #666; margin-bottom: 2px; }
        .vital-item .value { font-size: 26px; font-weight: 900; color: #111; line-height: 1; }
        .vital-item .unit  { font-size: 9px; color: #888; margin-top: 2px; }
        .vital-divider { display: inline-block; width: 1px; height: 40px; background: #e5e7eb; vertical-align: middle; }

        /* Statistik */
        .stats-box { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; margin-top: 10px; }
        .stats-title { font-size: 11px; font-weight: bold; color: rgb(0,62,48); margin-bottom: 6px; }
        .stats-row { font-size: 10px; color: #555; margin-bottom: 3px; }
        .stats-row .val { font-weight: 600; color: #111; }
        .stats-divider { border-top: 1px solid #e5e7eb; margin: 4px 0; }

        /* Grafik */
        .chart-placeholder {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            height: 200px;
        }
        .chart-title { font-size: 10px; font-weight: bold; color: #555; text-align: center; margin-bottom: 6px; }
        .chart-img   { width: 100%; max-height: 170px; }
        .chart-empty {
            height: 80px;
            line-height: 80px;
            text-align: center;
            background: #f9fafb;
            border-radius: 4px;
            color: #aaa;
            font-size: 10px;
            font-style: italic;
        }

        /* Tabel Riwayat */
        .tabel-riwayat { width: 100%; border-collapse: collapse; font-size: 10.5px; }
        .tabel-riwayat th {
            background: rgba(0,62,48,0.06);
            padding: 6px 10px;
            text-align: left;
            font-weight: bold;
            color: #444;
            border-bottom: 1px solid #e5e7eb;
        }
        .tabel-riwayat td {
            padding: 5px 10px;
            border-bottom: 1px solid #f3f4f6;
            color: #333;
        }
        .tabel-riwayat tr:last-child td { border-bottom: none; }
        .waktu-col { width: 90px; font-family: monospace; color: #666; }
        .num-col { text-align: center; width: 80px; }
        .status-badge {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 600;
        }
        .status-badge.normal { background: #dcfce7; color: #15803d; }
        .status-badge.warning { background: #fef9c3; color: #a16207; }
        .status-badge.critical { background: #fee2e2; color: #b91c1c; }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            display: table;
            width: 100%;
            font-size: 9px;
            color: #888;
        }
        .footer span { display: table-cell; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-brand">
            <img src="{{ public_path('assets/logo.png') }}" alt="SATS Logo">
            <div class="brand-text">
                <div class="name">SATS</div>
                <div class="tagline">Smart Ambulance Telemedicine System</div>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">Laporan Medis Pasien</div>
            <div>No. RM: {{ $session->medical_record_number ?? '-' }}</div>
            <div>Sesi: {{ $session->started_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? '-' }}
                – {{ $session->ended_at?->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') ?? 'Sedang Berlangsung' }}</div>
            <div>Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} &nbsp;|&nbsp; {{ auth()->user()->name ?? '-' }}</div>
        </div>
    </div>

    <!-- Identitas Pasien -->
    <div class="section-card">
        <div class="section-card-header">
            Laporan Medis Pasien: {{ $session->medical_record_number ?? '-' }}
            @if($patient) — {{ $patient->nama }} @endif
        </div>
        <div class="section-card-body">
            @if($patient)
                <div class="identitas-grid">
                    <div class="identitas-col">
                        <div class="identitas-row"><span class="label">Nama Lengkap</span> : {{ $patient->nama }}</div>
                        <div class="identitas-row"><span class="label">NIK</span> : {{ $patient->nik ?? '-' }}</div>
                        <div class="identitas-row"><span class="label">Umur</span> : {{ $patient->umur ?? '-' }} tahun</div>
                        <div class="identitas-row"><span class="label">Jenis Kelamin</span> : {{ $patient->jenis_kelamin == 'L' ? 'Laki-laki' : ($patient->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</div>
                    </div>
                    <div class="identitas-col">
                        <div class="identitas-row"><span class="label">Penyakit/Alergi</span> : {{ $patient->penyakit_alergi ?? '-' }}</div>
                        <div class="identitas-row" style="margin-top:4px"><span class="label">Catatan Tambahan</span> : {{ $patient->catatan_tambahan ?? '-' }}</div>
                    </div>
                </div>
            @else
                <p style="text-align:center; color:#888; font-style:italic; font-size:10px;">Data pasien belum diinput.</p>
            @endif
        </div>
    </div>

    <!-- Banner Prediksi ML -->
    @php
        $riskLevel   = $prediksi->risk_level ?? 'warning';
        $riskPercent = $prediksi->risk_percent ?? 20;
        $riskMenit   = $prediksi->timeframe_minutes ?? 15;
        $riskPesan   = $prediksi->message ?? 'Kondisi pasien berpotensi memburuk ' . $riskPercent . '% dalam ' . $riskMenit . ' menit ke depan.';
        $riskBadgeLabel = match($riskLevel) {
            'critical' => 'Kritis',
            'normal'   => 'Normal',
            default    => 'Perhatian',
        };
    @endphp
    <div class="ml-banner {{ $riskLevel }}">
        <div class="ml-banner-dot"><div class="ml-banner-dot-inner {{ $riskLevel }}"></div></div>
        <div class="ml-banner-content">
            <div class="ml-banner-label">Prediksi ML</div>
            <div class="ml-banner-text">{{ $riskPesan }}</div>
        </div>
        <div class="ml-banner-badge-cell"><span class="ml-banner-badge {{ $riskLevel }}">{{ $riskBadgeLabel }}</span></div>
    </div>

    <!-- Grafik & Nilai Vital -->
    <div class="row-cols">
        <div class="col-chart">
            <div class="chart-placeholder">
                <div class="chart-title">Grafik Vital Signs</div>
                @if(isset($grafikBase64))
                    <img class="chart-img" src="data:image/png;base64,{{ $grafikBase64 }}" alt="Grafik Vital Signs">
                @else
                    <div class="chart-empty">Grafik tidak tersedia</div>
                @endif
            </div>
        </div>

        <div class="col-side">

            <!-- Nilai Vital Terbaru -->
            <div class="vital-box">
                <div class="vital-title">Nilai Vital Terbaru</div>
                @if($latestReading)
                    <div class="vital-values">
                        <div class="vital-item">
                            <div class="label">Heart Rate</div>
                            <div class="value">{{ $latestReading->heart_rate ?? '-' }}</div>
                            <div class="unit">bpm</div>
                        </div>
                        <div class="vital-divider"></div>
                        <div class="vital-item">
                            <div class="label">SpO2</div>
                            <div class="value">{{ $latestReading->spo2 ?? '-' }}<span style="font-size:18px">%</span></div>
                            <div class="unit">saturasi</div>
                        </div>
                        <div class="vital-divider"></div>
                        <div class="vital-item">
                            <div class="label">Suhu</div>
                            <div class="value">{{ $latestReading->temperature ?? '-' }}<span style="font-size:18px">°</span></div>
                            <div class="unit">celsius</div>
                        </div>
                    </div>
                @else
                    <p style="text-align:center; color:#888; font-style:italic; font-size:10px;">Belum ada data</p>
                @endif
            </div>

            <!-- Statistik Sesi -->
            @if($stats)
                <div class="stats-box">
                    <div class="stats-title">Statistik Sesi</div>
                    <div class="stats-row">Total Pembacaan : <span class="val">{{ $stats['total_readings'] }}</span></div>
                    <div class="stats-row">Rata-rata HR : <span class="val">{{ $stats['avg_heart_rate'] }} bpm</span></div>
                    <div class="stats-row">Rata-rata SpO2 : <span class="val">{{ $stats['avg_spo2'] }}%</span></div>
                    <div class="stats-row">Rata-rata Suhu : <span class="val">{{ $stats['avg_temperature'] }}°C</span></div>
                    <div class="stats-divider"></div>
                    <div class="stats-row">Min–Max HR : <span class="val">{{ $stats['min_heart_rate'] }}–{{ $stats['max_heart_rate'] }} bpm</span></div>
                    <div class="stats-row">Min–Max SpO2 : <span class="val">{{ $stats['min_spo2'] }}–{{ $stats['max_spo2'] }}%</span></div>
                    <div class="stats-row">Min–Max Suhu : <span class="val">{{ $stats['min_temperature'] }}–{{ $stats['max_temperature'] }}°C</span></div>
                    <div class="stats-divider"></div>
                    <div class="stats-row">Normal : <span class="val">{{ $stats['normal_count'] }}</span></div>
                    <div class="stats-row">Warning : <span class="val">{{ $stats['warning_count'] }}</span></div>
                    <div class="stats-row">Kritis : <span class="val">{{ $stats['critical_count'] }}</span></div>
                </div>
            @endif

        </div>
    </div>

    <!-- Tabel Riwayat Sensor Readings -->
    <div class="section-card">
        <div class="section-card-header">Riwayat Pembacaan Sensor</div>
        <div class="section-card-body" style="padding:0">
            <table class="tabel-riwayat">
                <thead>
                    <tr>
                        <th class="waktu-col">Waktu</th>
                        <th class="num-col">Heart Rate</th>
                        <th class="num-col">SpO2</th>
                        <th class="num-col">Suhu</th>
                        <th class="num-col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($session->sensorReadings ?? [] as $reading)
                        <tr>
                            <td class="waktu-col">{{ $reading->recorded_at?->setTimezone('Asia/Jakarta')->format('H:i:s') }}</td>
                            <td class="num-col">{{ $reading->heart_rate }} bpm</td>
                            <td class="num-col">{{ $reading->spo2 }}%</td>
                            <td class="num-col">{{ $reading->temperature }}°C</td>
                            <td class="num-col">
                                <span class="status-badge {{ $reading->status }}">{{ $reading->status_badge }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; color:#888; font-style:italic; padding:20px;">
                                Belum ada data pembacaan sensor.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <span>SATS – Smart Ambulance Telemedicine System</span>
        <span>Laporan dicetak otomatis oleh sistem</span>
        <span>Halaman 1</span>
    </div>

</body>
</html>
