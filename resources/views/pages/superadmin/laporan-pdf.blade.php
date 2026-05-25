<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Superadmin – SATS</title>
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

        /* Section Divider */
        .section-divider {
            background: rgba(0,62,48,0.08);
            padding: 8px 14px;
            font-size: 12px;
            font-weight: bold;
            color: rgb(0,62,48);
            border: 1px solid rgba(0,62,48,0.2);
            border-radius: 6px;
            margin: 16px 0 12px;
        }

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

        /* Stats */
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
        .stat-green  .stat-value { color: #059669; }
        .stat-blue   .stat-value { color: #2563eb; }
        .stat-red    .stat-value { color: #dc2626; }
        .stat-purple .stat-value { color: #7c3aed; }
        .stat-amber  .stat-value { color: #d97706; }

        /* Chart */
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
        .tabel-data { width: 100%; border-collapse: collapse; font-size: 9.5px; }
        .tabel-data th {
            background: rgba(0,62,48,0.07);
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            color: #444;
            border-bottom: 1px solid #e5e7eb;
        }
        .tabel-data td {
            padding: 5px 8px;
            border-bottom: 1px solid #f3f4f6;
            color: #333;
        }
        .tabel-data tr:last-child td { border-bottom: none; }
        .tabel-data .text-center { text-align: center; }
        .tabel-data .font-mono { font-family: monospace; color: #666; }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-blue    { background: #dbeafe; color: #1d4ed8; }
        .badge-red     { background: #fee2e2; color: #b91c1c; }
        .badge-emerald { background: #d1fae5; color: #047857; }
        .badge-violet  { background: #ede9fe; color: #6d28d9; }
        .badge-amber   { background: #fef3c7; color: #b45309; }
        .badge-indigo  { background: #e0e7ff; color: #4338ca; }
        .badge-green   { background: #dcfce7; color: #166534; }
        .badge-teal    { background: #ccfbf1; color: #0f766e; }
        .badge-gray    { background: #f3f4f6; color: #4b5563; }

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
            <div class="doc-title">
                @if($exportType === 'operasional') Laporan Operasional
                @elseif($exportType === 'audit') Laporan Audit Keamanan
                @else Laporan Operasional &amp; Audit Keamanan
                @endif
            </div>
            <div>Rentang: {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}</div>
            <div>Dicetak: {{ now()->setTimezone('Asia/Jakarta')->format('d/m/Y H:i') }} &nbsp;|&nbsp; {{ auth()->user()->name ?? 'Super Admin' }}</div>
        </div>
    </div>

    {{-- ======================== SECTION OPERASIONAL ======================== --}}
    @if(in_array($exportType, ['operasional', 'both']) && $operasionalData)
    <div class="section-divider">Laporan Operasional</div>

    <div class="filter-info">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}
    </div>

    {{-- Stat Cards --}}
    <div class="stats-row">
        <div class="stat-box stat-green">
            <div class="stat-value">{{ $operasionalData['totalSesi'] }}</div>
            <div class="stat-label">Total Sesi Monitoring</div>
        </div>
        <div class="stat-box stat-blue">
            <div class="stat-value">{{ $operasionalData['totalPasien'] }}</div>
            <div class="stat-label">Total Pasien</div>
        </div>
        <div class="stat-box stat-emerald">
            <div class="stat-value">{{ $operasionalData['deviceAktif'] }}</div>
            <div class="stat-label">Device Aktif</div>
        </div>
        <div class="stat-box stat-red">
            <div class="stat-value">{{ $operasionalData['dataGagal'] }}</div>
            <div class="stat-label">Data Gagal Diproses</div>
        </div>
    </div>

    {{-- Chart Tren --}}
    <div class="chart-container">
        <div class="chart-title">Tren Sesi Monitoring per Hari</div>
        @if($grafikBase64)
            <img class="chart-img" src="data:image/png;base64,{{ $grafikBase64 }}" alt="Chart Tren">
        @else
            <div class="chart-empty">Grafik tidak tersedia</div>
        @endif
    </div>

    {{-- Device Utilization --}}
    <div class="section-divider" style="font-size: 10px; margin-top: 10px;">Utilisasi Device</div>
    <table class="tabel-data" style="border: 1px solid #d1d5db; border-top: none;">
        <thead>
            <tr>
                <th>Device</th>
                <th class="text-center" style="width:100px">Jumlah Sesi</th>
                <th class="text-center" style="width:120px">Durasi Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @forelse($operasionalData['deviceUtilization'] as $du)
            <tr>
                <td class="font-mono">{{ $du->device_id }}</td>
                <td class="text-center">{{ $du->jumlah_sesi }}</td>
                <td class="text-center">{{ round($du->durasi_avg ?? 0) }} menit</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center" style="padding: 16px; color: #999;">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Distribusi Kondisi --}}
    @php
        $dist = $operasionalData['distribusiKondisi'];
        $totalKondisi = ($dist->get('normal') ?? 0) + ($dist->get('warning') ?? 0) + ($dist->get('critical') ?? 0);
    @endphp
    @if($totalKondisi > 0)
    <div class="section-divider" style="font-size: 10px; margin-top: 10px;">Distribusi Kondisi Sensor</div>
    <div class="stats-row">
        <div class="stat-box stat-emerald">
            <div class="stat-value">{{ $dist->get('normal', 0) }}</div>
            <div class="stat-label">Normal ({{ $totalKondisi > 0 ? round($dist->get('normal', 0) / $totalKondisi * 100, 1) : 0 }}%)</div>
        </div>
        <div class="stat-box stat-amber">
            <div class="stat-value">{{ $dist->get('warning', 0) }}</div>
            <div class="stat-label">Warning ({{ $totalKondisi > 0 ? round($dist->get('warning', 0) / $totalKondisi * 100, 1) : 0 }}%)</div>
        </div>
        <div class="stat-box stat-red">
            <div class="stat-value">{{ $dist->get('critical', 0) }}</div>
            <div class="stat-label">Critical ({{ $totalKondisi > 0 ? round($dist->get('critical', 0) / $totalKondisi * 100, 1) : 0 }}%)</div>
        </div>
    </div>
    @endif
    @endif

    {{-- ======================== SECTION AUDIT ======================== --}}
    @if(in_array($exportType, ['audit', 'both']) && $auditData)
    <div class="section-divider">Laporan Audit Keamanan</div>

    <div class="filter-info">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}
    </div>

    {{-- Stat Cards --}}
    <div class="stats-row">
        <div class="stat-box stat-green">
            <div class="stat-value">{{ $auditData['totalAktivitas'] }}</div>
            <div class="stat-label">Total Aktivitas</div>
        </div>
        <div class="stat-box stat-blue">
            <div class="stat-value">{{ $auditData['loginHariIni'] }}</div>
            <div class="stat-label">Login Hari Ini</div>
        </div>
        <div class="stat-box stat-purple">
            <div class="stat-value">{{ $auditData['perubahanUser'] }}</div>
            <div class="stat-label">Perubahan User</div>
        </div>
        <div class="stat-box stat-amber">
            <div class="stat-value">{{ $auditData['perubahanDevice'] }}</div>
            <div class="stat-label">Perubahan Device</div>
        </div>
    </div>

    {{-- Log Aktivitas --}}
    <div class="section-divider" style="font-size: 10px; margin-top: 10px;">Log Aktivitas ({{ is_countable($auditData['logs']) ? count($auditData['logs']) : 0 }} entri teratas)</div>
    <table class="tabel-data" style="border: 1px solid #d1d5db; border-top: none;">
        <thead>
            <tr>
                <th style="width:100px">Waktu</th>
                <th style="width:120px">Tipe</th>
                <th>Pesan</th>
                <th style="width:90px">User</th>
                <th style="width:70px">Role</th>
                <th style="width:90px">Device</th>
            </tr>
        </thead>
        <tbody>
            @php
                $iconBadgeMap = [
                    'blue' => 'badge-blue',
                    'red' => 'badge-red',
                    'emerald' => 'badge-emerald',
                    'violet' => 'badge-violet',
                    'amber' => 'badge-amber',
                    'indigo' => 'badge-indigo',
                    'green' => 'badge-green',
                    'teal' => 'badge-teal',
                    'gray' => 'badge-gray',
                ];
            @endphp
            @forelse($auditData['logs'] as $log)
            <tr>
                <td class="font-mono">{{ $log->created_at?->setTimezone('Asia/Jakarta')->format('d/m H:i:s') }}</td>
                <td>
                    <span class="badge {{ $iconBadgeMap[$log->icon] ?? 'badge-gray' }}">{{ $log->type }}</span>
                </td>
                <td>{{ Str::limit($log->message, 80) }}</td>
                <td>{{ $log->user_name ?? '-' }}</td>
                <td class="text-center">{{ $log->user_role ?? '-' }}</td>
                <td class="font-mono">{{ $log->device_id ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #999;">Tidak ada log aktivitas pada rentang tanggal ini.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- Footer -->
    <div class="footer">
        <span>SATS – Smart Ambulance Telemedicine System</span>
        <span>Laporan dicetak otomatis oleh sistem</span>
        <span>Halaman 1</span>
    </div>

</body>
</html>
