<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekam Medis - {{ $session->medical_record_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.5; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid rgb(0,62,48); padding-bottom: 12px; margin-bottom: 16px; }
        .header-left { display: flex; align-items: center; gap: 10px; }
        .header-left img { width: 40px; height: 40px; }
        .header-left .brand { font-size: 16px; font-weight: bold; color: rgb(0,62,48); }
        .header-left .sub { font-size: 9px; color: #666; }
        .header-right { text-align: right; font-size: 9px; }
        .header-right .title { font-size: 14px; font-weight: bold; color: rgb(0,62,48); margin-bottom: 4px; }
        .section-card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 12px; margin-bottom: 12px; }
        .section-title { font-size: 11px; font-weight: bold; color: rgb(0,62,48); margin-bottom: 8px; border-bottom: 1px solid #eee; padding-bottom: 4px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6px; }
        .info-item label { font-size: 9px; color: #888; display: block; }
        .info-item p { font-size: 10px; font-weight: 600; }
        .stats-row { display: flex; gap: 12px; margin-bottom: 12px; }
        .stat-box { flex: 1; border: 1px solid #e0e0e0; border-radius: 6px; padding: 10px; text-align: center; }
        .stat-box .label { font-size: 9px; color: #888; }
        .stat-box .value { font-size: 18px; font-weight: bold; }
        .stat-box .unit { font-size: 8px; color: #888; }
        .stat-box .range { font-size: 8px; color: #aaa; margin-top: 2px; }
        .stat-box.hr { border-color: rgb(220,38,38); }
        .stat-box.hr .value { color: rgb(220,38,38); }
        .stat-box.spo2 { border-color: rgb(59,130,246); }
        .stat-box.spo2 .value { color: rgb(59,130,246); }
        .stat-box.temp { border-color: rgb(234,179,8); }
        .stat-box.temp .value { color: rgb(234,179,8); }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: rgba(0,62,48,0.08); padding: 6px 8px; text-align: left; font-weight: 600; color: rgb(0,62,48); border-bottom: 1px solid #ddd; }
        td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; }
        .badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 8px; font-weight: 600; }
        .badge.normal { background: #dcfce7; color: #166534; }
        .badge.warning { background: #fef9c3; color: #854d0e; }
        .badge.critical { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 20px; border-top: 1px solid #ddd; padding-top: 8px; display: flex; justify-content: space-between; font-size: 8px; color: #aaa; }
        .chart-img { width: 100%; max-height: 180px; object-fit: contain; margin: 8px 0; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div>
                <div class="brand">SATS</div>
                <div class="sub">Smart Ambulance Telemedicine System</div>
            </div>
        </div>
        <div class="header-right">
            <div class="title">Rekam Medis</div>
            <div>No. RM: {{ $session->medical_record_number }}</div>
            <div>{{ $session->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }} — {{ $session->ended_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</div>
            <div>Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} oleh {{ auth()->user()->name }}</div>
        </div>
    </div>

    <!-- Info Nakes & Dokter -->
    <div class="section-card">
        <div class="section-title">Informasi Tim Medis</div>
        <div class="info-grid">
            <div class="info-item">
                <label>Nakes yang Menangani</label>
                <p>{{ $session->creator?->name ?? '-' }}</p>
            </div>
            <div class="info-item">
                <label>Dokter yang Bertugas</label>
                <p>{{ $session->dokter?->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Identitas Pasien -->
    @if($session->patient)
    <div class="section-card">
        <div class="section-title">Identitas Pasien</div>
        <div class="info-grid">
            <div class="info-item">
                <label>Nama</label>
                <p>{{ $session->patient->nama }}</p>
            </div>
            <div class="info-item">
                <label>NIK</label>
                <p>{{ $session->patient->nik ?? '-' }}</p>
            </div>
            <div class="info-item">
                <label>Umur</label>
                <p>{{ $session->patient->umur ?? '-' }} tahun</p>
            </div>
            <div class="info-item">
                <label>Jenis Kelamin</label>
                <p>{{ $session->patient->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
            </div>
            <div class="info-item">
                <label>Penyakit/Alergi</label>
                <p>{{ $session->patient->penyakit_alergi ?? '-' }}</p>
            </div>
            <div class="info-item">
                <label>Catatan</label>
                <p>{{ $session->patient->catatan_tambahan ?? '-' }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Statistik -->
    <div class="stats-row">
        <div class="stat-box hr">
            <div class="label">Heart Rate</div>
            <div class="value">{{ $stats['avg_heart_rate'] ?? '-' }}</div>
            <div class="unit">bpm (rata-rata)</div>
            <div class="range">{{ $stats['min_heart_rate'] ?? '-' }} - {{ $stats['max_heart_rate'] ?? '-' }}</div>
        </div>
        <div class="stat-box spo2">
            <div class="label">SpO2</div>
            <div class="value">{{ $stats['avg_spo2'] ?? '-' }}</div>
            <div class="unit">% (rata-rata)</div>
            <div class="range">{{ $stats['min_spo2'] ?? '-' }} - {{ $stats['max_spo2'] ?? '-' }}</div>
        </div>
        <div class="stat-box temp">
            <div class="label">Suhu</div>
            <div class="value">{{ $stats['avg_temperature'] ?? '-' }}</div>
            <div class="unit">°C (rata-rata)</div>
            <div class="range">{{ $stats['min_temperature'] ?? '-' }} - {{ $stats['max_temperature'] ?? '-' }}</div>
        </div>
    </div>

    <!-- Grafik -->
    @if($grafikBase64)
    <div class="section-card">
        <div class="section-title">Grafik Vital Signs</div>
        <img src="data:image/png;base64,{{ $grafikBase64 }}" class="chart-img" alt="Chart">
    </div>
    @endif

    <!-- Tabel Sensor -->
    <div class="section-card">
        <div class="section-title">Riwayat Sensor ({{ $stats['total_readings'] ?? 0 }} data)</div>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th style="text-align:center">Heart Rate</th>
                    <th style="text-align:center">SpO2</th>
                    <th style="text-align:center">Suhu</th>
                    <th style="text-align:center">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($session->sensorReadings->take(100) as $reading)
                <tr>
                    <td>{{ $reading->recorded_at?->setTimezone('Asia/Jakarta')->format('H:i:s') }}</td>
                    <td style="text-align:center">{{ $reading->heart_rate }}</td>
                    <td style="text-align:center">{{ $reading->spo2 }}%</td>
                    <td style="text-align:center">{{ $reading->temperature }}°C</td>
                    <td style="text-align:center">
                        @if($reading->status === 'critical')
                            <span class="badge critical">Kritis</span>
                        @elseif($reading->status === 'warning')
                            <span class="badge warning">Peringatan</span>
                        @else
                            <span class="badge normal">Normal</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <span>SATS — Smart Ambulance Telemedicine System</span>
        <span>Rekam Medis dicetak otomatis oleh sistem</span>
        <span>Halaman 1</span>
    </div>
</body>
</html>
