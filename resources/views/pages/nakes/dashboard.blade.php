@extends('layouts.app')

@section('content')

{{--
    Dashboard Monitoring Pasien oleh Nakes/Dokter

    Halaman ini menampilkan:
    - 4 stat card (Heart Rate, SpO2, Temperature, Kondisi Pasien)
    - Banner prediksi Machine Learning
    - 3 grafik sensor real-time (Chart.js)

    Integrasi Backend bisa dilakukan seperti contoh dibawah:
    - Data sensor → endpoint: GET /api/device/{device_id}/latest
    - Data historis grafik → endpoint: GET /api/device/{device_id}/history?minutes=10
    - Prediksi ML → endpoint: GET /api/device/{device_id}/prediction
    - Gunakan Laravel Echo + Pusher / WebSocket untuk update real-time

    Integrasi IoT:
    - Device mengirim data via MQTT atau HTTP POST ke /api/ingest
    - Format payload JSON: { device_id, heart_rate, spo2, temperature, timestamp }
--}}

<main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]">

    {{--
        Judul dan Dropdown Btn
        
        TODO: Update array $devices dengan data dari Database, misal: 
            $devices = Device::where('user_id', auth()->id())->get();
    --}}
    <div x-data="deviceDropdown()" class="flex items-start justify-between mb-5">
        <div>
            <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Dashboard Monitoring</h1>
            <p class="text-sm text-gray-400 mt-1" x-text="selectedDevice"></p>
        </div>

        <div class="relative">
            <button @click="toggle()" class="cursor-pointer px-4 py-2 bg-[rgb(0,62,48)] text-white rounded-lg flex items-center gap-2 text-sm hover:opacity-90 transition">
                Pilih Perangkat
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div
                x-show="open"
                @click.outside="close()"
                x-transition
                class="absolute right-0 mt-2 w-52 bg-white border border-[rgba(0,83,63,0.15)] rounded-xl shadow-sm z-50 overflow-hidden"
            >
                {{--
                    TODO: Ganti `devices` di Alpine dengan data dinamis dari controller.
                    Contoh: pass via Blade ke JS dengan @json($devices->pluck('name'))
                --}}
                <template x-for="device in devices" :key="device">
                    <div
                        @click="selectDevice(device)"
                        class="px-4 py-2.5 text-sm hover:bg-[rgba(0,83,63,0.06)] cursor-pointer text-[rgb(0,62,48)]"
                        x-text="device"
                    ></div>
                </template>
            </div>
        </div>
    </div>

    {{--
        Stat Card (4 Kolom)
        TODO: Ganti nilai dummy dengan data dari controller, misal:
               $latest = DeviceReading::where('device_id', $deviceId)
                           ->latest()->first();
        Kemudian pass ke view: compact('latest')
        --}}
    <div class="grid grid-cols-4 gap-3 mb-4">

        {{-- Heart Rate --}}
        <div class="bg-red-50 rounded-xl p-4 border border-red-200">
            <p class="text-xs font-medium text-red-400 mb-2">Heart Rate</p>
            {{-- TODO: {{ $latest->heart_rate ?? '—' }} --}}
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">82 <span class="text-sm font-normal text-red-400">bpm</span></p>
            <p class="text-[10px] text-red-400 mt-1">
                {{-- TODO: Kondisi berdasarkan threshold, e.g. 60-100 bpm = Normal --}}
                Normal
            </p>
        </div>

        {{-- SpO2 --}}
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
            <p class="text-xs font-medium text-blue-400 mb-2">SpO2</p>
            {{-- TODO: {{ $latest->spo2 ?? '—' }} --}}
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">97 <span class="text-sm font-normal text-blue-400">%</span></p>
            <p class="text-[10px] text-blue-400 mt-1">
                {{-- TODO: Kondisi: ≥95% = Normal, 90-94% = Warning, < 90% = Critical --}}
                Normal
            </p>
        </div>

        {{-- Temperature --}}
        <div class="bg-orange-50 rounded-xl p-4 border border-orange-200">
            <p class="text-xs font-medium text-orange-400 mb-2">Temperature</p>
            {{-- TODO: {{ $latest->temperature ?? '—' }} --}}
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">36.7 <span class="text-sm font-normal text-orange-400">°C</span></p>
            <p class="text-[10px] text-orange-400 mt-1">
                {{-- TODO: Kondisi: 36.1–37.2°C = Normal, >37.5°C = Demam --}}
                Normal
            </p>
        </div>

        {{-- Kondisi Pasien --}}
        <div class="bg-[rgba(0,83,63,0.05)] rounded-xl p-4 border border-[rgba(0,83,63,0.2)]">
            <p class="text-xs font-medium text-[rgb(0,62,48)] mb-2">Kondisi Pasien</p>
            {{--
                TODO: Hitung kondisi di backend berdasarkan semua sensor.
                Nilai: 'Normal' | 'Warning' | 'Critical'
                Warna class bisa dikondisikan:
                    Normal   → text-[rgb(0,62,48)]
                    Warning → text-orange-500
                    Critical    → text-red-500
            --}}
            <p class="text-2xl font-medium text-[rgb(0,62,48)]">Normal</p>
            <p class="text-[10px] text-[rgba(0,62,48,0.5)] mt-1">
                Pembaruan: {{ now()->format('H:i') }}
                {{-- TODO: Ganti dengan timestamp dari $latest->created_at->format('H:i') --}}
            </p>
        </div>

    </div>

    {{--
        Prediksi Machine Learning
         
         TODO: Endpoint ML → GET /api/device/{device_id}/prediction
         Response JSON:
         {
           "risk_level": "warning",       // normal | warning | critical
           "risk_percent": 20,
           "timeframe_minutes": 15,
           "message": "Kondisi pasien berpotensi memburuk 20% dalam 15 menit ke depan."
         }

         Gunakan Alpine.js atau Livewire untuk fetch dan render dinamis.
         Warna badge menyesuaikan risk_level:
           normal   → bg-green-50  text-green-700
           warning  → bg-orange-50 text-orange-700
           critical → bg-red-50    text-red-700
    --}}
    <div class="flex items-center gap-4 bg-[rgba(0,62,48,0.05)] border border-[rgba(0,62,48,0.18)] rounded-xl px-5 py-3.5 mb-4">
        <span class="w-2 h-2 rounded-full bg-orange-400 flex-shrink-0"></span>
        <div class="flex-1">
            <p class="text-[10px] font-medium text-gray-400 uppercase tracking-wider mb-0.5">Prediksi ML</p>
            {{-- TODO: {{ $prediction->message ?? 'Data prediksi belum tersedia.' }} --}}
            <p class="text-sm font-medium text-[rgb(0,62,48)]">
                Kondisi pasien berpotensi memburuk 20% dalam 15 menit ke depan berdasarkan tren Heart Rate dan SpO2.
            </p>
        </div>
        {{-- TODO: Badge menyesuaikan risk_level --}}
        <span class="text-[10px] font-medium px-2.5 py-1 rounded bg-orange-100 text-orange-700 flex-shrink-0">
            Perhatian
        </span>
    </div>

    {{--
        Grafik Sensor (3 Kolom)
        
        Data grafik diambil via AJAX dari endpoint:
        GET /api/device/{device_id}/history?minutes=10
        Response JSON:
        {
        "labels": ["10:09", "10:11", ...],
        "heart_rate": [78, 80, 83, ...],
        "spo2": [98, 97, 97, ...],
        "temperature": [36.5, 36.6, ...]
        }
    --}}
    <div class="grid grid-cols-3 gap-3">

        {{-- Heart Rate Chart --}}
        <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
            <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                <p class="text-sm font-medium text-[rgb(0,62,48)]">Heart Rate</p>
                <p class="text-[11px] text-gray-400 mt-0.5">bpm — 10 menit terakhir</p>
            </div>
            <div class="p-4">
                <canvas id="hrChart" height="150"></canvas>
            </div>
        </div>

        {{-- SpO2 Chart --}}
        <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
            <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                <p class="text-sm font-medium text-[rgb(0,62,48)]">SpO2</p>
                <p class="text-[11px] text-gray-400 mt-0.5">% — 10 menit terakhir</p>
            </div>
            <div class="p-4">
                <canvas id="spo2Chart" height="150"></canvas>
            </div>
        </div>

        {{-- Temperature Chart --}}
        <div class="bg-white rounded-xl overflow-hidden border border-[rgba(0,83,63,0.1)]">
            <div class="px-4 py-3 border-b border-[rgba(0,83,63,0.08)]">
                <p class="text-sm font-medium text-[rgb(0,62,48)]">Temperature</p>
                <p class="text-[11px] text-gray-400 mt-0.5">°C — 10 menit terakhir</p>
            </div>
            <div class="p-4">
                <canvas id="tempChart" height="150"></canvas>
            </div>
        </div>

    </div>

    {{-- ==================== KOMENTAR DOKTER (Nakes View) ==================== --}}
    {{-- Container untuk nakes melihat dan merespon komentar dari dokter --}}
    <div class="mt-4" x-data="komentarNakes()">
        <div class="bg-white rounded-xl border border-[rgba(0,83,63,0.1)] overflow-hidden">
            <div class="px-5 py-3.5 border-b border-[rgba(0,83,63,0.08)] flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-[rgb(0,62,48)]">Instruksi dari Dokter</p>
                    <p class="text-[11px] text-gray-400 mt-0.5">Respon instruksi dan checklist yang sudah dilakukan</p>
                </div>
                <span class="text-[10px] font-medium text-blue-600 bg-blue-50 px-2.5 py-1 rounded-full"
                    x-text="komentar.filter(k => !k.checked).length + ' instruksi aktif'"></span>
            </div>

            {{-- Daftar komentar dari dokter --}}
            <div>
                <template x-if="komentar.filter(k => !k.checked).length === 0">
                    <p class="px-5 py-4 text-sm text-gray-400 text-center">Tidak ada instruksi dari dokter.</p>
                </template>
                <template x-for="(item, index) in komentar" :key="item.id">
                    <div x-show="!item.checked" class="px-5 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start gap-3">
                            {{-- Checklist --}}
                            <label class="flex items-center gap-2 cursor-pointer mt-1 flex-shrink-0">
                                <input type="checkbox" x-model="item.checked"
                                    @change="checklistKomentar(item)"
                                    class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            </label>

                            <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">D</div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-semibold text-gray-700">Dr. Andi</span>
                                    <span class="text-[10px] text-gray-400" x-text="item.waktu"></span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2" x-text="item.teks"></p>

                                {{-- Dropdown Respon --}}
                                <div class="flex items-center gap-2" x-show="!item.respon">
                                    <select x-model="item.selectedRespon"
                                        class="text-xs border border-gray-300 rounded-lg px-2.5 py-1.5 bg-white focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all">
                                        <option value="" disabled selected>Pilih respon...</option>
                                        <option value="Sudah dilakukan">Sudah dilakukan</option>
                                        <option value="Tidak bisa dilakukan">Tidak bisa dilakukan</option>
                                        <option value="Tidak memungkinkan">Tidak memungkinkan</option>
                                        <option value="Sedang diproses">Sedang diproses</option>
                                        <option value="Butuh konfirmasi ulang">Butuh konfirmasi ulang</option>
                                    </select>
                                    <button @click="kirimRespon(item)"
                                        :disabled="!item.selectedRespon"
                                        class="text-xs font-medium text-white px-3 py-1.5 rounded-lg cursor-pointer transition-all hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed"
                                        style="background: rgb(0,83,63);">
                                        Kirim
                                    </button>
                                </div>

                                {{-- Tampilkan respon jika sudah dikirim --}}
                                <template x-if="item.respon">
                                    <div class="mt-1.5 ml-0.5 pl-3 border-l-2 border-emerald-300 bg-emerald-50 rounded-r-lg py-1.5 px-3">
                                        <div class="flex items-center gap-1.5 mb-0.5">
                                            <span class="text-[10px] font-semibold text-emerald-700">Respon Anda</span>
                                            <span class="text-[10px] text-emerald-500" x-text="item.responWaktu"></span>
                                        </div>
                                        <p class="text-xs text-emerald-800" x-text="item.respon"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</main>

@push('scripts')

{{-- Chart.js — pastikan sudah ada di package.json atau CDN di layouts/app.blade.php --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    // Alpine.js — Device Dropdown
    function deviceDropdown() {
        return {
            devices: ['Sats Wearable-1', 'Sats Wearable-2', 'Sats Wearable-3'],
            selectedDevice: 'Sats Wearable-1',
            open: false,
            toggle() { this.open = !this.open },
            close()  { this.open = false },
            selectDevice(device) {
                this.selectedDevice = device;
                this.close();
                // TODO: Saat device diganti, panggil fetchChartData(deviceId)
                // agar grafik dan stat card ikut diperbarui
            }
        }
    }

    // KONFIGURASI CHART.JS
    // Pengaturan default yang dipakai di ketiga grafik
    const sharedOptions = (yMin, yMax) => ({
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                mode: 'index',
                intersect: false,
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 }, color: '#8aab9f' }
            },
            y: {
                min: yMin,
                max: yMax,
                grid: { color: 'rgba(0,83,63,0.07)', lineWidth: 0.5 },
                ticks: { font: { size: 10 }, color: '#8aab9f' }
            }
        },
        elements: {
            point: { radius: 2.5, hoverRadius: 5 }
        }
    });

    // Helper buat gradient area di bawah garis
    function makeGradient(ctx, hexColor) {
        const gradient = ctx.createLinearGradient(0, 0, 0, 160);
        gradient.addColorStop(0, hexColor + '35');
        gradient.addColorStop(1, hexColor + '00');
        return gradient;
    }

    //
    // Data Dummy - Ganti dengan fetch API saat integrasi
    //
    // Contoh fetch ke backend Laravel:
    //
    //   async function fetchChartData(deviceId) {
    //       const res = await fetch(`/api/device/${deviceId}/history?minutes=10`);
    //       const data = await res.json();
    //       // Perbarui chart.data.labels dan chart.data.datasets[0].data
    //       hrChart.data.labels = data.labels;
    //       hrChart.data.datasets[0].data = data.heart_rate;
    //       hrChart.update();
    //       // ... lakukan hal yang sama untuk spo2Chart dan tempChart
    //   }
    //
    //   // Panggil saat halaman load dan setiap 10 detik (polling)
    //   fetchChartData(selectedDeviceId);
    //   setInterval(() => fetchChartData(selectedDeviceId), 10000);
    //
    //   // Atau gunakan Laravel Echo + Pusher untuk real-time WebSocket:
    //   // Echo.channel(`device.${selectedDeviceId}`)
    //   //     .listen('SensorDataReceived', (e) => { updateCharts(e); });
    //
    const dummyLabels = ['10:09','10:11','10:13','10:15','10:17','10:19','10:21','10:23','10:25','10:27'];

    //
    // Grafik Heart Rate
    // Threshold normal: 60–100 bpm
    // Warna: merah (#ef4444)
    //
    const hrCtx = document.getElementById('hrChart').getContext('2d');
    const hrChart = new Chart(hrCtx, {
        type: 'line',
        data: {
            labels: dummyLabels,
            // TODO: Ganti dengan data.heart_rate dari API
            datasets: [{
                data: [78, 80, 83, 85, 82, 84, 87, 86, 83, 82],
                borderColor: '#ef4444',
                backgroundColor: makeGradient(hrCtx, '#ef4444'),
                borderWidth: 1.5,
                fill: true,
                tension: 0.4,
            }]
        },
        options: sharedOptions(60, 120)
    });

    //
    // GRAFIK SPO2
    // Threshold normal: ≥ 95%
    // Warna: biru (#3b82f6)
    //
    const spo2Ctx = document.getElementById('spo2Chart').getContext('2d');
    const spo2Chart = new Chart(spo2Ctx, {
        type: 'line',
        data: {
            labels: dummyLabels,
            // TODO: Ganti dengan data.spo2 dari API
            datasets: [{
                data: [98, 97, 97, 96, 97, 96, 95, 96, 97, 97],
                borderColor: '#3b82f6',
                backgroundColor: makeGradient(spo2Ctx, '#3b82f6'),
                borderWidth: 1.5,
                fill: true,
                tension: 0.4,
            }]
        },
        options: sharedOptions(90, 100)
    });

    //
    // GRAFIK TEMPERATURE
    // Threshold normal: 36.1–37.2°C
    // Warna: oranye (#f97316)
    //
    const tempCtx = document.getElementById('tempChart').getContext('2d');
    const tempChart = new Chart(tempCtx, {
        type: 'line',
        data: {
            labels: dummyLabels,
            // TODO: Ganti dengan data.temperature dari API
            datasets: [{
                data: [36.5, 36.6, 36.7, 36.8, 36.7, 36.9, 37.0, 36.9, 36.8, 36.7],
                borderColor: '#f97316',
                backgroundColor: makeGradient(tempCtx, '#f97316'),
                borderWidth: 1.5,
                fill: true,
                tension: 0.4,
            }]
        },
        options: sharedOptions(35.0, 38.5)
    });

    // ==================== KOMENTAR NAKES ====================
    let komentarIdCounter = 100;
    function komentarNakes() {
        return {
            // TODO: Ganti dengan data dari backend (fetch komentar dari dokter)
            komentar: [
                {
                    id: 1,
                    teks: 'Tolong pasang tabung oksigen, SpO2 pasien mulai turun.',
                    waktu: '10:15',
                    respon: null,
                    responWaktu: null,
                    selectedRespon: '',
                    checked: false
                },
                {
                    id: 2,
                    teks: 'Monitor heart rate secara intensif, ada peningkatan signifikan.',
                    waktu: '10:20',
                    respon: null,
                    responWaktu: null,
                    selectedRespon: '',
                    checked: false
                },
                {
                    id: 3,
                    teks: 'Siapkan infus saline 500ml, pasien dehidrasi.',
                    waktu: '10:30',
                    respon: null,
                    responWaktu: null,
                    selectedRespon: '',
                    checked: false
                }
            ],
            kirimRespon(item) {
                if (!item.selectedRespon) return;
                // TODO: POST ke backend
                item.respon = item.selectedRespon;
                item.responWaktu = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            },
            checklistKomentar(item) {
                // TODO: POST ke backend (update status checked)
                // Item akan otomatis tersembunyi karena x-show="!item.checked"
            }
        }
    }

</script>
@endpush

@endsection