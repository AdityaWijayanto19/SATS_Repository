@extends('layouts.app')

@section('content')
    <main class="relative w-auto h-full flex-1 overflow-hidden p-6 bg-black flex justify-center items-center"
        x-data="dashboard3D()" x-init="init()">
        <div id="canvas-3d" class="w-full h-full"></div>
    </main>

    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>

        <script>
            function dashboard3D() {
                return {
                    heartRate: 72,
                    spo2: 98,
                    temperature: 36.5,
                    dynamicCanvas: null,
                    ctx: null,
                    screenTexture: null,
                    tick: 0,

                    init() {
                        // 1. Siapkan Canvas Gaib untuk Tekstur Angka Medis
                        this.setupDynamicTexture();

                        // 2. Jalankan Three.js
                        this.initThree();

                        // 3. Loop Simulasi Angka Naik-Turun
                        setInterval(() => {
                            this.heartRate = Math.floor(Math.random() * (85 - 65 + 1)) + 65;
                            this.spo2 = Math.floor(Math.random() * (100 - 96 + 1)) + 96;
                            this.temperature = (Math.random() * (37.5 - 36.0) + 36.0).toFixed(1);
                        }, 1500);
                    },

                    setupDynamicTexture() {
                        // Membuat canvas 2D di memori (tidak tampil di HTML)
                        this.dynamicCanvas = document.createElement('canvas');
                        this.dynamicCanvas.width = 512; // Resolusi tekstur angka
                        this.dynamicCanvas.height = 512;
                        this.ctx = this.dynamicCanvas.getContext('2d');

                        // Ubah canvas menjadi tekstur yang dimengerti Three.js
                        this.screenTexture = new THREE.CanvasTexture(this.dynamicCanvas);
                        this.updateTexture();
                    },

                    getStatus() {
                        if (this.heartRate > 100 || this.heartRate < 50 || this.spo2 < 90 || this.temperature > 38.5 || this
                            .temperature < 35.0) {
                            return {
                                label: 'CRITICAL',
                                color: '#ef4444',
                                bg: 'rgba(239,68,68,0.15)'
                            };
                        }
                        if (this.heartRate > 90 || this.heartRate < 60 || this.spo2 < 95 || this.temperature > 37.5 || this
                            .temperature < 36.0) {
                            return {
                                label: 'WARNING',
                                color: '#f59e0b',
                                bg: 'rgba(245,158,11,0.15)'
                            };
                        }
                        return {
                            label: 'NORMAL',
                            color: '#22c55e',
                            bg: 'rgba(34,197,94,0.15)'
                        };
                    },

                    getPrediction() {
                        const status = this.getStatus();
                        const predictions = {
                            'NORMAL': [
                                'Kondisi stabil, vital sign dalam rentang aman',
                                'Tidak ada tanda abnormal terdeteksi',
                                'Pasien dalam kondisi baik, lanjutkan monitoring'
                            ],
                            'WARNING': [
                                'Tren penurunan SpO2 terdeteksi, waspada',
                                'HR cenderung meningkat, pantau ketat',
                                'Suhu berfluktuasi, kemungkinan infeksi'
                            ],
                            'CRITICAL': [
                                'Risiko hipoksia dalam 5 menit, siapkan oksigen',
                                'Detak jantung tidak stabil, siapkan defibrilator',
                                'Kondisi kritis, segera panggil tim medis'
                            ]
                        };
                        const list = predictions[status.label];
                        return list[Math.floor(this.tick / 200) % list.length];
                    },

                    updateTexture() {
                        if (!this.ctx) return;
                        const ctx = this.ctx;
                        const W = 512,
                            H = 512;
                        const t = this.tick;

                        // =============================================
                        // 1. BACKGROUND
                        // =============================================
                        ctx.fillStyle = '#08080a';
                        ctx.fillRect(0, 0, W, H);

                        // =============================================
                        // 2. GRID (kotak-kotak kecil samar)
                        // =============================================
                        ctx.strokeStyle = 'rgba(30, 41, 59, 0.5)';
                        ctx.lineWidth = 0.5;
                        const gridStep = 20;
                        for (let x = 0; x < W; x += gridStep) {
                            ctx.beginPath();
                            ctx.moveTo(x, 0);
                            ctx.lineTo(x, H);
                            ctx.stroke();
                        }
                        for (let y = 0; y < H; y += gridStep) {
                            ctx.beginPath();
                            ctx.moveTo(0, y);
                            ctx.lineTo(W, y);
                            ctx.stroke();
                        }

                        // =============================================
                        // 3. HEADER — Info bar atas
                        // =============================================
                        ctx.fillStyle = '#475569';
                        ctx.font = 'bold 13px monospace';
                        ctx.fillText('SATS MONITOR v1.0', 12, 22);

                        // Ambulance Mode indicator (kedap-kedip)
                        const blink = Math.floor(t / 30) % 2 === 0;
                        ctx.fillStyle = blink ? '#ef4444' : '#7f1d1d';
                        ctx.font = 'bold 11px monospace';
                        ctx.fillText('● AMBULANCE MODE', 180, 22);

                        // Jam digital
                        const now = new Date();
                        const timeStr = now.getHours().toString().padStart(2, '0') + ':' +
                            now.getMinutes().toString().padStart(2, '0') + ':' +
                            now.getSeconds().toString().padStart(2, '0');
                        ctx.fillStyle = '#94a3b8';
                        ctx.font = '12px monospace';
                        ctx.textAlign = 'right';
                        ctx.fillText(timeStr, W - 12, 22);
                        ctx.textAlign = 'left';

                        // Garis bawah header
                        ctx.strokeStyle = '#1e293b';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(10, 30);
                        ctx.lineTo(W - 10, 30);
                        ctx.stroke();

                        // =============================================
                        // AREA GRAFIK (Waveforms) — Terintegrasi dengan Data Real-time
                        // =============================================
                        const graphX = 14,
                            graphW = 248;
                        const ecgY = 42,
                            ecgH = 95;
                        const spo2Y = 150,
                            spo2H = 78;
                        const tempY = 240,
                            tempH = 65;

                        // --- 4. ECG WAVEFORM (Hijau Neon - Sinkron dengan HR) ---
                        ctx.fillStyle = '#10b981';
                        ctx.font = 'bold 13px monospace';
                        ctx.fillText('II', graphX, ecgY - 5);

                        const ecgMidY = ecgY + ecgH / 2;
                        ctx.strokeStyle = '#10b981';
                        ctx.lineWidth = 1.8;
                        ctx.shadowColor = '#10b981';
                        ctx.shadowBlur = 6;
                        ctx.beginPath();

                        // Penyelarasan Frekuensi Berdasarkan Heart Rate asli
                        // Semakin tinggi HR -> detak makin rapat, speed konstan meluncur rata (t * 0.1) agar tidak lompat
                        const hrFactor = this.heartRate / 70; // basis normal di 70 BPM
                        const ecgFrequency = 0.04 * hrFactor;

                        for (let px = 0; px < graphW; px++) {
                            // Menggunakan t * 0.1 konstan untuk scroll agar gelombang mengalir halus tanpa glitch loncat
                            const samplePoint = (px + t * 2.5) * ecgFrequency;
                            const modFactor = samplePoint % (Math.PI * 2);

                            let ecgVal = 0;
                            // Pola Gelombang Medis Asli (P-Q-R-S-T Complex) berdasarkan fase radian
                            if (modFactor < 0.3) {
                                // P Wave (Riak kecil awal)
                                ecgVal = Math.sin(modFactor * (Math.PI / 0.3)) * 0.15;
                            } else if (modFactor >= 0.5 && modFactor < 0.65) {
                                // Q Drop (Turun sedikit sebelum spike)
                                ecgVal = -Math.sin((modFactor - 0.5) * (Math.PI / 0.15)) * 0.18;
                            } else if (modFactor >= 0.65 && modFactor < 0.8) {
                                // R Peak (Spike tajam ke atas!)
                                ecgVal = Math.sin((modFactor - 0.65) * (Math.PI / 0.15)) * 1.0;
                            } else if (modFactor >= 0.8 && modFactor < 0.95) {
                                // S Drop (Hentakan tajam ke bawah dasar)
                                ecgVal = -Math.sin((modFactor - 0.8) * (Math.PI / 0.15)) * 0.35;
                            } else if (modFactor >= 1.2 && modFactor < 1.6) {
                                // T Wave (Gelombang pemulihan setelah detak)
                                ecgVal = Math.sin((modFactor - 1.2) * (Math.PI / 0.4)) * 0.25;
                            } else {
                                // Baseline datar (Isoelektris)
                                ecgVal = (Math.sin(samplePoint * 5) * 0.015);
                            }

                            const y = ecgMidY - ecgVal * (ecgH * 0.42);
                            if (px === 0) ctx.moveTo(graphX + px, y);
                            else ctx.lineTo(graphX + px, y);
                        }
                        ctx.stroke();
                        ctx.shadowBlur = 0;

                        // Garis bawah area ECG
                        ctx.strokeStyle = '#1e293b';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(10, ecgY + ecgH + 8);
                        ctx.lineTo(W - 10, ecgY + ecgH + 8);
                        ctx.stroke();

                        // --- 5. SpO2 WAVEFORM (Cyan - Sinkron dengan SpO2 & HR) ---
                        ctx.fillStyle = '#06b6d4';
                        ctx.font = 'bold 13px monospace';
                        ctx.fillText('SpO2', graphX, spo2Y - 5);

                        const spo2MidY = spo2Y + spo2H / 2;
                        ctx.strokeStyle = '#06b6d4';
                        ctx.lineWidth = 1.8;
                        ctx.shadowColor = '#06b6d4';
                        ctx.shadowBlur = 6;
                        ctx.beginPath();

                        // Amplitudo & Kerapatan menyesuaikan kondisi saturasi oksigen asli
                        const spo2Norm = Math.max(0, Math.min(1, (this.spo2 - 90) / 10)); // Skala 90% - 100%
                        const spo2Amp = 0.25 + (spo2Norm * 0.5); // Jika SpO2 drop, gelombang otomatis mengecil/dangkal
                        const spo2Freq = 0.035 * (this.heartRate /
                        70); // Ritme denyut nadi SpO2 harus searah dengan denyut jantung
                        const jitterFactor = (1 - spo2Norm) * 2.5; // Jika di bawah 95%, riak distorsi mulai muncul

                        for (let px = 0; px < graphW; px++) {
                            const angle = (px + t * 2.5) * spo2Freq;
                            // Plethysmogram wave form (Gelombang khas denyut nadi)
                            let baseWave = Math.sin(angle) * 0.6 + Math.sin(angle * 2 + 1.2) * 0.25;

                            // Suntik efek noise/distorsi acak jika data SpO2 memburuk (Warning/Critical)
                            if (jitterFactor > 0) {
                                baseWave += (Math.sin(px * 0.4 + t) * 0.05 * jitterFactor);
                            }

                            const y = spo2MidY - (baseWave * spo2Amp) * (spo2H * 0.42);
                            if (px === 0) ctx.moveTo(graphX + px, y);
                            else ctx.lineTo(graphX + px, y);
                        }
                        ctx.stroke();
                        ctx.shadowBlur = 0;

                        // Garis bawah area SpO2
                        ctx.strokeStyle = '#1e293b';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(10, spo2Y + spo2H + 8);
                        ctx.lineTo(W - 10, spo2Y + spo2H + 8);
                        ctx.stroke();

                        // --- 6. TEMP WAVEFORM (Kuning/Amber) ---
                        ctx.fillStyle = '#fbbf24';
                        ctx.font = 'bold 13px monospace';
                        ctx.fillText('TEMP', graphX, tempY - 5);

                        const tempMidY = tempY + tempH / 2;
                        ctx.strokeStyle = '#fbbf24';
                        ctx.lineWidth = 1.8;
                        ctx.shadowColor = '#fbbf24';
                        ctx.shadowBlur = 6;
                        ctx.beginPath();

                        for (let px = 0; px < graphW; px++) {
                            const s = (px + t * 0.8) * 0.015;
                            const val = Math.sin(s) * 0.40 +
                                Math.cos(s * 1.4 + 0.5) * 0.28 +
                                Math.sin(s * 2.6 + 1.8) * 0.15;
                            const y = tempMidY - val * (tempH * 0.42);
                            if (px === 0) ctx.moveTo(graphX + px, y);
                            else ctx.lineTo(graphX + px, y);
                        }
                        ctx.stroke();
                        ctx.shadowBlur = 0;

                        // Garis bawah area TEMP
                        ctx.strokeStyle = '#1e293b';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(10, tempY + tempH + 8);
                        ctx.lineTo(W - 10, tempY + tempH + 8);
                        ctx.stroke();

                        // =============================================
                        // 7. ANGKA VITAL (Sisi Kanan)
                        // =============================================
                        const dataX = 280;

                        ctx.strokeStyle = '#1e293b';
                        ctx.lineWidth = 1;
                        ctx.beginPath();
                        ctx.moveTo(dataX - 14, 40);
                        ctx.lineTo(dataX - 14, 310);
                        ctx.stroke();

                        // --- Heart Rate ---
                        ctx.fillStyle = '#475569';
                        ctx.font = 'bold 11px monospace';
                        ctx.fillText('HR (bpm)', dataX, 52);

                        const hrBlink = Math.floor(t / 8) % 2 === 0;
                        ctx.fillStyle = hrBlink ? '#10b981' : '#059669';
                        ctx.font = 'bold 42px monospace';
                        ctx.fillText(this.heartRate.toString(), dataX, 95);

                        const heartScale = 1 + Math.sin(t * 0.3) * 0.15;
                        ctx.fillStyle = '#10b981';
                        ctx.font = `bold ${Math.round(18 * heartScale)}px sans-serif`;
                        ctx.fillText('♥', dataX + 115, 52);

                        ctx.strokeStyle = '#1e293b';
                        ctx.beginPath();
                        ctx.moveTo(dataX, 108);
                        ctx.lineTo(W - 12, 108);
                        ctx.stroke();

                        // --- SpO2 ---
                        ctx.fillStyle = '#475569';
                        ctx.font = 'bold 11px monospace';
                        ctx.fillText('SpO2 (%)', dataX, 130);

                        ctx.fillStyle = '#06b6d4';
                        ctx.font = 'bold 40px monospace';
                        ctx.fillText(this.spo2.toString(), dataX, 170);

                        ctx.strokeStyle = '#1e293b';
                        ctx.beginPath();
                        ctx.moveTo(dataX, 182);
                        ctx.lineTo(W - 12, 182);
                        ctx.stroke();

                        // --- Temperature ---
                        ctx.fillStyle = '#475569';
                        ctx.font = 'bold 11px monospace';
                        ctx.fillText('TEMP (°C)', dataX, 204);

                        ctx.fillStyle = '#fbbf24';
                        ctx.font = 'bold 38px monospace';
                        ctx.fillText(this.temperature.toString(), dataX, 244);

                        // =============================================
                        // 8. STATUS PASIEN + PREDIKSI AI (Sinkronisasi Panel Kiri Bawah)
                        // =============================================
                        const status = this.getStatus();
                        const prediction = this.getPrediction();
                        const panelY = 345;
                        const panelH = 135;

                        ctx.fillStyle = 'rgba(15,23,42,0.9)';
                        ctx.fillRect(10, panelY, W - 20, panelH);
                        ctx.strokeStyle = '#1e293b';
                        ctx.strokeRect(10, panelY, W - 20, panelH);

                        const midX = 220;
                        ctx.beginPath();
                        ctx.moveTo(midX, panelY + 8);
                        ctx.lineTo(midX, panelY + panelH - 8);
                        ctx.stroke();

                        // --- KIRI: STATUS ---
                        ctx.fillStyle = '#475569';
                        ctx.font = 'bold 11px monospace';
                        ctx.fillText('PATIENT STATUS', 20, panelY + 20);

                        const statusBlink = status.label === 'CRITICAL' ? (Math.floor(t / 20) % 2 === 0) : true;
                        ctx.fillStyle = statusBlink ? status.bg : 'rgba(15,23,42,0.9)';
                        ctx.fillRect(20, panelY + 30, 90, 24);
                        ctx.strokeStyle = statusBlink ? status.color : '#1e293b';
                        ctx.strokeRect(20, panelY + 30, 90, 24);
                        ctx.fillStyle = statusBlink ? status.color : '#475569';
                        ctx.font = 'bold 13px monospace';
                        ctx.fillText(status.label, 30, panelY + 47);

                        const dotBlink = Math.floor(t / 15) % 2 === 0;
                        ctx.fillStyle = dotBlink ? status.color : 'transparent';
                        ctx.beginPath();
                        ctx.arc(120, panelY + 42, 4, 0, Math.PI * 2);
                        ctx.fill();

                        // DINAMIS: Menggunakan data asli, bukan hardcoded teks statis lagi
                        ctx.fillStyle = '#64748b';
                        ctx.font = '10px monospace';
                        ctx.fillText(`HR: ${this.heartRate} bpm   SpO2: ${this.spo2}%`, 20, panelY + 72);
                        ctx.fillText(`Temp: ${this.temperature}°C`, 20, panelY + 86);

                        const trendUp = this.heartRate > 78 || this.temperature > 37.0;
                        const trendDown = this.heartRate < 65 || this.spo2 < 97;
                        let trendSymbol = '— Stable';
                        let trendColor = '#64748b';
                        if (trendUp && !trendDown) {
                            trendSymbol = '↑ Rising';
                            trendColor = '#f59e0b';
                        } else if (trendDown && !trendUp) {
                            trendSymbol = '↓ Falling';
                            trendColor = '#ef4444';
                        }
                        ctx.fillStyle = trendColor;
                        ctx.font = 'bold 10px monospace';
                        ctx.fillText(`Trend: ${trendSymbol}`, 20, panelY + 104);

                        // --- KANAN: PREDIKSI AI ---
                        ctx.fillStyle = '#8b5cf6';
                        ctx.font = 'bold 11px monospace';
                        ctx.fillText('AI PREDICTION', midX + 12, panelY + 20);

                        const aiBlink = Math.floor(t / 25) % 2 === 0;
                        ctx.fillStyle = aiBlink ? '#8b5cf6' : '#6d28d9';
                        ctx.font = 'bold 11px monospace';
                        ctx.fillText('●', midX + 120, panelY + 20);

                        ctx.strokeStyle = '#8b5cf6';
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(midX + 12, panelY + 26);
                        ctx.lineTo(W - 20, panelY + 26);
                        ctx.stroke();

                        ctx.fillStyle = '#cbd5e1';
                        ctx.font = '11px monospace';
                        const maxLineW = W - midX - 35;
                        const words = prediction.split(' ');
                        let line = '';
                        let lineY = panelY + 44;
                        const lineH = 16;
                        for (let i = 0; i < words.length; i++) {
                            const testLine = line + words[i] + ' ';
                            if (ctx.measureText(testLine).width > maxLineW && line !== '') {
                                ctx.fillText(line.trim(), midX + 12, lineY);
                                line = words[i] + ' ';
                                lineY += lineH;
                            } else {
                                line = testLine;
                            }
                        }
                        ctx.fillText(line.trim(), midX + 12, lineY);

                        const confY = panelY + panelH - 22;
                        ctx.fillStyle = '#334155';
                        ctx.font = '9px monospace';
                        ctx.fillText('Confidence:', midX + 12, confY);
                        const confBarX = midX + 80;
                        const confBarW = W - confBarX - 20;
                        ctx.fillStyle = '#1e293b';
                        ctx.fillRect(confBarX, confY - 8, confBarW, 8);
                        const conf = status.label === 'NORMAL' ? 0.92 : status.label === 'WARNING' ? 0.78 : 0.85;
                        const confColor = conf > 0.85 ? '#22c55e' : conf > 0.7 ? '#f59e0b' : '#ef4444';
                        ctx.fillStyle = confColor;
                        ctx.fillRect(confBarX, confY - 8, confBarW * conf, 8);
                        ctx.fillStyle = '#94a3b8';
                        ctx.font = '9px monospace';
                        ctx.fillText(`${Math.round(conf * 100)}%`, confBarX + confBarW + 4, confY);

                        // =============================================
                        // 9. STATUS BAR (Bawah)
                        // =============================================
                        ctx.fillStyle = '#334155';
                        ctx.font = '10px monospace';
                        ctx.fillText('LEAD II   |   25mm/s   |   10mm/mV   |   FILTER: ON', 12, H - 10);

                        const connBlink = Math.floor(t / 45) % 2 === 0;
                        ctx.fillStyle = connBlink ? '#22c55e' : '#15803d';
                        ctx.font = '10px monospace';
                        ctx.textAlign = 'right';
                        ctx.fillText('● CONNECTED', W - 12, H - 10);
                        ctx.textAlign = 'left';

                        // Beritahu Three.js bahwa tekstur telah diupdate
                        this.screenTexture.needsUpdate = true;
                    },

                    initThree() {
                        const container = document.getElementById('canvas-3d');
                        if (!container) return;

                        const scene = new THREE.Scene();
                        const camera = new THREE.PerspectiveCamera(45, container.clientWidth / container.clientHeight, 0.1,
                            1000);
                        camera.position.set(0, 0, 3);

                        const renderer = new THREE.WebGLRenderer({
                            antialias: true,
                            alpha: true
                        });
                        renderer.setSize(container.clientWidth, container.clientHeight);
                        renderer.setPixelRatio(window.devicePixelRatio);
                        container.appendChild(renderer.domElement);

                        // Lighting
                        const ambientLight = new THREE.AmbientLight(0xffffff, 1.5);
                        scene.add(ambientLight);
                        const dirLight = new THREE.DirectionalLight(0xffffff, 2.0);
                        dirLight.position.set(5, 5, 5);
                        scene.add(dirLight);

                        const controls = new THREE.OrbitControls(camera, renderer.domElement);
                        controls.enableDamping = true;

                        // LOADER DENGAN TUNING LAYAR
                        const loader = new THREE.GLTFLoader();
                        loader.load('/assets/models/monitor-3d.glb', (gltf) => {
                            const model = gltf.scene;

                            // Auto Center
                            const box = new THREE.Box3().setFromObject(model);
                            const center = box.getCenter(new THREE.Vector3());
                            model.position.x += (model.position.x - center.x);
                            model.position.y += (model.position.y - center.y);
                            model.position.z += (model.position.z - center.z);

                            scene.add(model);

                            // ====================================================================
                            // --- SETTING FIX KOORDINAT LAYAR DAN INJEKSI TEKSTUR ANGKA MEDIS ---
                            // ====================================================================

                            // 1. Definisikan Geometri dengan ukuran yang sudah kamu tuning pas tadi
                            const screenGeometry = new THREE.PlaneGeometry(1.55, 1.20);

                            // 2. Pasang material asli dengan memetakan (map) ke canvas dynamicTexture kamu
                            const screenMaterial = new THREE.MeshStandardMaterial({
                                map: this.screenTexture,
                                side: THREE.DoubleSide,
                                emissive: new THREE.Color(0xffffff),
                                emissiveMap: this.screenTexture,
                                emissiveIntensity: 0.2
                            });

                            const screenMesh = new THREE.Mesh(screenGeometry, screenMaterial);

                            // 3. Kunci koordinat hasil tuning kamu yang sudah presisi
                            screenMesh.position.set(0, 0.05, 0.46);

                            // Masukkan plane ke dalam model monitor
                            model.add(screenMesh);


                            // ====================================================================
                            // --- PENAMBAL BOCOR TEKSTUR AI (DINDING HITAM BELAKANG LAYAR) -------
                            // ====================================================================

                            // Ukuran disengaja sedikit lebih lebar dari screenGeometry untuk blokade total
                            const patchGeometry = new THREE.PlaneGeometry(1.6, 1.25);

                            // Menggunakan warna hitam pekat doff matte biar menyatu dengan jeroan monitor
                            const patchMaterial = new THREE.MeshBasicMaterial({
                                color: 0x08080a,
                                side: THREE.DoubleSide
                            });

                            const patchMesh = new THREE.Mesh(patchGeometry, patchMaterial);

                            // Posisi Z dimundurkan sedikit (0.45) tepat di belakang screenMesh (0.46)
                            patchMesh.position.set(0, 0.05, 0.45);

                            // Masukkan tameng penutup ini ke dalam model monitor
                            model.add(patchMesh);


                            console.log("%c[SATS] Layar monitor medis berhasil di-render dengan tekstur dinamis!",
                                "color: #10b981; font-weight: bold;");

                        }, undefined, (error) => {
                            console.error("Gagal load model:", error);
                        });

                        // Animation Loop
                        const self = this;
                        const animate = () => {
                            requestAnimationFrame(animate);
                            self.tick++;
                            self.updateTexture();
                            controls.update();
                            renderer.render(scene, camera);
                        };
                        animate();

                        window.addEventListener('resize', () => {
                            camera.aspect = container.clientWidth / container.clientHeight;
                            camera.updateProjectionMatrix();
                            renderer.setSize(container.clientWidth, container.clientHeight);
                        });
                    }
                }
            }
        </script>
    @endpush
@endsection
