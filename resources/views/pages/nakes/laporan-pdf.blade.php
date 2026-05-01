<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Medis – {{ $pasien?->nama_lengkap ?? 'Pasien' }}</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid rgb(0,62,48);
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .header-brand { display: flex; align-items: center; gap: 10px; }
        .header-brand img { width: 44px; height: 44px; object-fit: contain; }
        .header-brand .brand-text .name    { font-size: 14px; font-weight: bold; color: rgb(0,62,48); }
        .header-brand .brand-text .tagline { font-size: 9px; color: #555; margin-top: 1px; }
        .header-right { text-align: right; font-size: 10px; color: #555; }
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
        .identitas-grid { display: flex; gap: 24px; }
        .identitas-col { flex: 1; }
        .identitas-row { margin-bottom: 3px; font-size: 10.5px; }
        .identitas-row .label { font-weight: bold; color: #333; }

        /* Banner Prediksi ML */
        .ml-banner {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 12px;
            border: 1px solid;
        }
        .ml-banner.warning  { background: #fffbeb; border-color: #fcd34d; }
        .ml-banner.critical { background: #fff1f1; border-color: #fca5a5; }
        .ml-banner.normal   { background: #f0fdf4; border-color: #86efac; }
        .ml-banner-dot { width: 8px; height: 8px; border-radius: 50%; margin-top: 3px; flex-shrink: 0; }
        .ml-banner-dot.warning  { background: #f59e0b; }
        .ml-banner-dot.critical { background: #ef4444; }
        .ml-banner-dot.normal   { background: #22c55e; }
        .ml-banner-content { flex: 1; }
        .ml-banner-label { font-size: 8.5px; font-weight: bold; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .ml-banner-text  { font-size: 10.5px; color: rgb(0,62,48); font-weight: 500; }
        .ml-banner-badge { font-size: 9px; font-weight: bold; padding: 2px 10px; border-radius: 999px; flex-shrink: 0; }
        .ml-banner-badge.warning  { background: #fef3c7; color: #92400e; }
        .ml-banner-badge.critical { background: #fee2e2; color: #b91c1c; }
        .ml-banner-badge.normal   { background: #dcfce7; color: #15803d; }

        /* Dua Kolom: grafik + vital/ML */
        .row-cols { display: flex; gap: 12px; margin-bottom: 12px; }
        .col-chart { flex: 3; }
        .col-side  { flex: 2; display: flex; flex-direction: column; gap: 10px; }

        /* Nilai Vital */
        .vital-box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
        }
        .vital-title { font-size: 11px; font-weight: bold; color: rgb(0,62,48); margin-bottom: 8px; }
        .vital-values { display: flex; justify-content: space-around; align-items: center; }
        .vital-item .label { font-size: 9px; color: #666; margin-bottom: 2px; }
        .vital-item .value { font-size: 26px; font-weight: 900; color: #111; line-height: 1; }
        .vital-item .unit  { font-size: 9px; color: #888; margin-top: 2px; }
        .vital-divider { width: 1px; height: 36px; background: #e5e7eb; }

        /* Klasifikasi ML */
        .ml-box { border: 1px solid #d1d5db; border-radius: 8px; padding: 10px 12px; }
        .ml-title { font-size: 11px; font-weight: bold; color: rgb(0,62,48); margin-bottom: 6px; }
        .ml-badge {
            display: inline-block;
            padding: 3px 16px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: bold;
            margin: 0 auto 6px;
        }
        .ml-badge.normal   { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .ml-badge.warning  { background: #fef9c3; color: #a16207; border: 1px solid #fde047; }
        .ml-badge.critical { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .ml-keterangan { font-size: 9.5px; color: #444; text-align: center; margin-bottom: 6px; }
        .ml-legend { display: flex; justify-content: center; gap: 12px; font-size: 9px; color: #666; }
        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 3px; }
        .dot.green  { background: #22c55e; }
        .dot.yellow { background: #facc15; }
        .dot.red    { background: #ef4444; }

        /* Grafik */
        .chart-placeholder {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            height: 180px;
        }
        .chart-title { font-size: 10px; font-weight: bold; color: #555; text-align: center; margin-bottom: 6px; }
        .chart-img   { width: 100%; height: 150px; object-fit: contain; }
        .chart-empty {
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
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

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #888;
        }
    </style>
</head>
<body>

    <!-- Header dengan Logo dari folder public -->
    <div class="header">
        <div class="header-brand">
            {{--
                Taruh file logo kamu di public/images/logo.png (atau sesuaikan ekstensinya).
                DomPDF butuh path absolut, bukan URL relatif.
            --}}
            <img src="{{ public_path('assets/logo.png') }}" alt="SATS Logo">
            <div class="brand-text">
                <div class="name">SATS</div>
                <div class="tagline">Smart Ambulance Telemedicine System</div>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-title">Laporan Medis Pasien</div>
            <div>Rentang: {{ \Carbon\Carbon::parse($dari)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($sampai)->format('d/m/Y') }}</div>
            <div>Dicetak: {{ now()->format('d/m/Y H:i') }} &nbsp;|&nbsp; Dr. {{ auth()->user()->name ?? 'Andi' }}</div>
        </div>
    </div>

    <!-- Identitas Pasien -->
    <div class="section-card">
        <div class="section-card-header">
            Laporan Medis Pasien: {{ $pasien?->no_rekam_medis ?? '24E56' }} – {{ $pasien?->nama_lengkap ?? 'Budi Santoso' }}
        </div>
        <div class="section-card-body">
            <div class="identitas-grid">
                <div class="identitas-col">
                    <div class="identitas-row"><span class="label">Nama Lengkap</span> : {{ $pasien?->nama_lengkap ?? 'Budi Santoso' }}</div>
                    <div class="identitas-row"><span class="label">NIK</span> : {{ $pasien?->nik ?? '33161224234777' }}</div>
                    <div class="identitas-row"><span class="label">Usia</span> : {{ $pasien?->usia ?? '59' }} tahun</div>
                    <div class="identitas-row"><span class="label">Jenis Kelamin</span> : {{ $pasien?->jenis_kelamin == 'L' ? 'Laki-laki' : ($pasien?->jenis_kelamin == 'P' ? 'Perempuan' : 'Laki-laki') }}</div>
                </div>
                <div class="identitas-col">
                    <div class="identitas-row"><span class="label">Penyakit/Alergi</span> : {{ $pasien?->penyakit_alergi ?? 'Serangan Jantung' }}</div>
                    <div class="identitas-row" style="margin-top:4px"><span class="label">Catatan Tambahan</span> : {{ $pasien?->catatan_tambahan ?? 'Harus dipantau setiap menitnya' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Banner Prediksi ML -->
    @php
        $riskLevel   = $prediksi->risk_level        ?? 'warning';
        $riskPercent = $prediksi->risk_percent       ?? 20;
        $riskMenit   = $prediksi->timeframe_minutes  ?? 15;
        $riskPesan   = $prediksi->message            ?? 'Kondisi pasien berpotensi memburuk ' . $riskPercent . '% dalam ' . $riskMenit . ' menit ke depan berdasarkan tren Heart Rate dan SpO2.';
        $riskBadgeLabel = match($riskLevel) {
            'critical' => 'Kritis',
            'normal'   => 'Normal',
            default    => 'Perhatian',
        };
    @endphp
    <div class="ml-banner {{ $riskLevel }}">
        <div class="ml-banner-dot {{ $riskLevel }}"></div>
        <div class="ml-banner-content">
            <div class="ml-banner-label">Prediksi ML</div>
            <div class="ml-banner-text">{{ $riskPesan }}</div>
        </div>
        <span class="ml-banner-badge {{ $riskLevel }}">{{ $riskBadgeLabel }}</span>
    </div>

    <!-- Grafik & Nilai Vital -->
    <div class="row-cols">
        <div class="col-chart">
            <div class="chart-placeholder">
                <div class="chart-title">Tekanan Darah (Sistolik &amp; Diastolik)</div>
                @if(isset($grafikBase64))
                    <img class="chart-img" src="data:image/png;base64,{{ $grafikBase64 }}" alt="Grafik Tekanan Darah">
                @else
                    <div class="chart-empty">Grafik tidak tersedia</div>
                @endif
            </div>
        </div>

        <div class="col-side">

            <!-- Nilai Vital -->
            <div class="vital-box">
                <div class="vital-title">Nilai Vital</div>
                <div class="vital-values">
                    <div class="vital-item">
                        <div class="label">Detak Jantung</div>
                        <div class="value">{{ $vitalTerbaru?->detak_jantung ?? '72' }}</div>
                        <div class="unit">bpm</div>
                    </div>
                    <div class="vital-divider"></div>
                    <div class="vital-item">
                        <div class="label">SPO2</div>
                        <div class="value">{{ $vitalTerbaru?->spo2 ?? '98' }}<span style="font-size:18px">%</span></div>
                        <div class="unit">saturasi</div>
                    </div>
                </div>
            </div>

            <!-- Klasifikasi ML -->
            @php
                $status = $vitalTerbaru?->klasifikasi ?? 'Normal';
                $badgeClass = match($status) {
                    'Warning'  => 'warning',
                    'Critical' => 'critical',
                    default    => 'normal',
                };
            @endphp
            <div class="ml-box">
                <div class="ml-title">Hasil Klasifikasi ML</div>
                <div style="text-align:center">
                    <span class="ml-badge {{ $badgeClass }}">{{ $status }}</span>
                </div>
                <div class="ml-keterangan">
                    <strong>Klasifikasi Otomatis :</strong><br>
                    {{ $vitalTerbaru?->keterangan_klasifikasi ?? 'Kondisi Pasien Stabil' }}
                </div>
                <div class="ml-legend">
                    <span><span class="dot green"></span>Normal</span>
                    <span><span class="dot yellow"></span>Warning</span>
                    <span><span class="dot red"></span>Critical</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Tabel Riwayat Kondisi -->
    <div class="section-card" style="margin-top:80px">
        <div class="section-card-header">Riwayat Kondisi Pasien</div>
        <div class="section-card-body" style="padding:0">
            <table class="tabel-riwayat">
                <thead>
                    <tr>
                        <th class="waktu-col">Waktu</th>
                        <th>Riwayat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayat ?? [] as $item)
                        <tr>
                            <td class="waktu-col">{{ $item->waktu }}</td>
                            <td>{{ $item->keterangan }}</td>
                        </tr>
                    @empty
                        @foreach([
                            ['10.38.59', 'Kondisi Pasien Warning'],
                            ['10.40.59', 'Detak jantung meningkat'],
                            ['10.42.59', 'Tabung Oksigen Dipasang'],
                            ['10.44.59', 'Tekanan Darah Menurun'],
                        ] as $row)
                        <tr>
                            <td class="waktu-col">{{ $row[0] }}</td>
                            <td>{{ $row[1] }}</td>
                        </tr>
                        @endforeach
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