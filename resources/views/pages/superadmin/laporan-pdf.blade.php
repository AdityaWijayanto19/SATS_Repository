<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Sensor – SATS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1a1a1a;
            background: #fff;
            padding: 24px 28px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid rgb(0,62,48);
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .header-brand { display: flex; align-items: center; gap: 10px; }
        .header-brand img { width: 40px; height: 40px; object-fit: contain; }
        .header-brand .brand-text .name    { font-size: 13px; font-weight: bold; color: rgb(0,62,48); }
        .header-brand .brand-text .tagline { font-size: 8px; color: #555; margin-top: 1px; }
        .header-right { text-align: right; font-size: 9px; color: #555; }
        .header-right .doc-title { font-size: 14px; font-weight: bold; color: rgb(0,62,48); }

        /* Filter Info */
        .filter-info {
            background: rgba(0,62,48,0.05);
            border: 1px solid rgba(0,62,48,0.15);
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .filter-info strong { color: rgb(0,62,48); }

        /* Grafik */
        .chart-container {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 14px;
            text-align: center;
        }
        .chart-title { font-size: 10px; font-weight: bold; color: #555; margin-bottom: 6px; }
        .chart-img { width: 100%; height: 180px; object-fit: contain; }
        .chart-empty {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
            border-radius: 4px;
            color: #aaa;
            font-size: 10px;
            font-style: italic;
        }

        /* Tabel */
        .tabel-sensor { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        .tabel-sensor th {
            background: rgba(0,62,48,0.07);
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            color: #444;
            border-bottom: 1px solid #e5e7eb;
        }
        .tabel-sensor td {
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            color: #333;
        }
        .tabel-sensor tr:last-child td { border-bottom: none; }
        .tabel-sensor .text-center { text-align: center; }
        .tabel-sensor .font-mono { font-family: monospace; color: #666; }

        /* Badge Klasifikasi */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge.normal   { background: #dcfce7; color: #15803d; }
        .badge.warning  { background: #fef9c3; color: #a16207; }
        .badge.critical { background: #fee2e2; color: #b91c1c; }

        /* Nilai highlight */
        .val-red { color: #dc2626; font-weight: bold; }

        /* Section Header */
        .section-header {
            background: rgba(0,62,48,0.06);
            padding: 6px 12px;
            font-size: 11px;
            font-weight: bold;
            color: rgb(0,62,48);
            text-align: center;
            border: 1px solid #d1d5db;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }

        /* Footer */
        .footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 8px;
            color: #888;
        }

        /* Summary Stats */
        .stats-row {
            display: flex;
            gap: 12px;
            margin-bottom: 14px;
        }
        .stat-box {
            flex: 1;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
        }
        .stat-value { font-size: 22px; font-weight: 900; line-height: 1; }
        .stat-label { font-size: 9px; color: #666; margin-top: 3px; }
        .stat-blue   .stat-value { color: #2563eb; }
        .stat-pink   .stat-value { color: #ec4899; }
        .stat-amber  .stat-value { color: #d97706; }
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
            <div class="doc-title">Laporan Data Sensor</div>
            <div>Rentang: {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}</div>
            <div>Dicetak: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; {{ auth()->user()->name ?? 'Super Admin' }}</div>
        </div>
    </div>

    <!-- Filter Info -->
    <div class="filter-info">
        <strong>Filter:</strong>
        Rentang Tanggal {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}
        @if($ambulans) &nbsp;&middot;&nbsp; Ambulans: <strong>{{ $ambulans }}</strong> @endif
        &nbsp;&middot;&nbsp; Total Data: <strong>{{ $dataSensor->count() }} entri</strong>
    </div>

    <!-- Summary Stats -->
    <div class="stats-row">
        <div class="stat-box stat-blue">
            <div class="stat-value">{{ $dataSensor->count() }}</div>
            <div class="stat-label">Total Data Sensor</div>
        </div>
        <div class="stat-box stat-pink">
            <div class="stat-value">{{ $dataSensor->where('klasifikasi', '!=', 'Normal')->count() }}</div>
            <div class="stat-label">Data Non-Normal</div>
        </div>
        <div class="stat-box stat-amber">
            <div class="stat-value">{{ $dataSensor->where('klasifikasi', 'Critical')->count() }}</div>
            <div class="stat-label">Data Kritis</div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="chart-container">
        <div class="chart-title">Grafik Vital Sign (Heart Rate &amp; SpO2)</div>
        @if(isset($grafikBase64))
            <img class="chart-img" src="data:image/png;base64,{{ $grafikBase64 }}" alt="Grafik Vital Sign">
        @else
            <div class="chart-empty">Grafik tidak tersedia</div>
        @endif
    </div>

    <!-- Tabel Data Sensor -->
    <div class="section-header">Data Sensor Perangkat</div>
    <table class="tabel-sensor" style="border: 1px solid #d1d5db; border-top: none;">
        <thead>
            <tr>
                <th style="width:70px">Waktu</th>
                <th>Perangkat</th>
                <th>Ambulans</th>
                <th class="text-center" style="width:80px">Heart Rate</th>
                <th class="text-center" style="width:60px">SpO2</th>
                <th class="text-center" style="width:60px">Suhu</th>
                <th class="text-center" style="width:80px">Klasifikasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataSensor as $row)
            <tr>
                <td class="font-mono">{{ $row->waktu }}</td>
                <td>{{ $row->device }}</td>
                <td>{{ $row->ambulans }}</td>
                <td class="text-center {{ $row->heart_rate > 100 ? 'val-red' : '' }}">{{ $row->heart_rate }} bpm</td>
                <td class="text-center {{ $row->spo2 < 95 ? 'val-red' : '' }}">{{ $row->spo2 }}%</td>
                <td class="text-center {{ $row->temperature > 37.5 ? 'val-red' : '' }}">{{ $row->temperature }}&deg;C</td>
                <td class="text-center">
                    <span class="badge {{ strtolower($row->klasifikasi) }}">{{ $row->klasifikasi }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center" style="padding: 20px; color: #999;">Tidak ada data sensor pada rentang tanggal ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <span>SATS – Smart Ambulance Telemedicine System</span>
        <span>Laporan dicetak otomatis oleh sistem</span>
        <span>Halaman 1</span>
    </div>

</body>
</html>
