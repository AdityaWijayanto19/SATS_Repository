@extends('layouts.app')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]">
        <div class="max-w-4xl mx-auto" x-data="instruksiNakes()" x-init="init()">
            <div
                class="bg-white rounded-2xl border border-[rgba(0,83,63,0.1)] shadow-sm overflow-hidden flex flex-col h-[80vh]">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-[rgba(0,83,63,0.08)] bg-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[rgb(0,62,48)]">Monitoring Ambulans: <span x-text="deviceId"></span></h2>
                        <p class="text-[11px] text-gray-400">Lapor kejadian dan konfirmasi instruksi dokter</p>
                    </div>
                </div>

                {{-- Chat Area dengan x-ref untuk Scroll --}}
                <div x-ref="chatBox" class="flex-1 overflow-y-auto p-6 flex flex-col gap-6 bg-gray-50/30 scroll-smooth">
                    <template x-if="instruksi.length === 0">
                        <div class="flex flex-col items-center justify-center h-full opacity-40">
                            <p class="text-sm italic">Belum ada aktivitas instruksi.</p>
                        </div>
                    </template>

                    <template x-for="item in sortedInstruksi" :key="item.id">
                        <div class="flex flex-col gap-3">

                            {{-- 1. Laporan Nakes / Anda Sendiri (Sisi Kanan) --}}
                            <div class="flex justify-end items-end gap-2"
                                x-show="item.laporan_nakes && item.laporan_nakes !== '-'">
                                <div
                                    class="max-w-[80%] bg-[rgb(0,83,63)] text-white p-4 rounded-2xl transition-all rounded-tr-none shadow-md relative">

                                        <p class="text-sm leading-relaxed mb-2" x-text="item.laporan_nakes"></p>

                                        {{-- Waktu Pojok Bawah --}}
                                        <span class="absolute bottom-1 right-3 text-[9px] opacity-60"
                                            x-text="item.waktu"></span>

                                </div>
                            </div>

                            {{-- 2. Instruksi Dokter (Sisi Kiri) --}}
                            <template x-if="item.instruksi_dokter">
                                <div class="flex justify-start items-end gap-2">
                                    {{-- Avatar Dokter --}}
                                    <div
                                        class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 mb-1">
                                        DR
                                    </div>

                                    <div :class="item.is_completed ? 'bg-white opacity-90' : 'bg-white border-l-4 border-green-500'"
                                        class="max-w-[80%] p-4 rounded-2xl rounded-tl-none shadow-sm border border-gray-200 relative transition-all">

                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-[10px] font-bold text-green-900 uppercase tracking-tighter"
                                                x-text="item.user_name || 'DOKTER SATS'"></span>
                                        </div>

                                        <p class="text-sm text-gray-800 font-normal mb-2" x-text="item.instruksi_dokter">
                                        </p>

                                        {{-- Waktu Pojok Bawah --}}
                                        <span class="absolute bottom-1 right-3 text-[9px] text-gray-400"
                                            x-text="item.waktu || ''"></span>

                                        {{-- Area Respon (Tombol Tindakan) --}}
                                        <div class="mt-1 pt-3 border-t border-dashed border-gray-100">
                                            <template x-if="!item.is_completed">
                                                <div class="flex flex-col gap-2">
                                                    <p class="text-[9px] font-bold text-gray-400 uppercase">Respon Anda:</p>
                                                    <div class="flex flex-wrap gap-2">
                                                        <template
                                                            x-for="opsi in ['Sudah dilakukan', 'Alat tidak ada', 'Gagal']">
                                                            <button @click="item.selectedRespon = opsi; kirimRespon(item)"
                                                                class="text-[10px] px-3 py-1.5 rounded-full border border-green-100 bg-green-50 text-green-900 font-bold hover:bg-green-600 hover:text-white transition-all">
                                                                <span x-text="opsi"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Jika Sudah Selesai --}}
                                            <template x-if="item.is_completed">
                                                <div
                                                    class="flex items-center gap-2 text-green-900 bg-emerald-50 p-2 rounded-lg border border-emerald-100">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path
                                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z">
                                                        </path>
                                                    </svg>
                                                    <span class="text-[10px] font-bold tracking-wide"
                                                        x-text="'DIKONFIRMASI: ' + item.respon_nakes"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </template>
                </div>

                {{-- Input Bar (Bottom) --}}
                <div class="px-5 py-3.5 border-t border-[rgba(0,83,63,0.08)] bg-gray-50/50">
                    <div class="flex gap-3">
                        <textarea x-model="laporanBaru" rows="1" @input="autoResize($el)"
                            placeholder="Lapor kejadian kritis (misal: Pasien Kejang-kejang)..."
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                            @keydown.ctrl.enter="kirimLaporan()"></textarea>
                        <button @click="kirimLaporan()" :disabled="!laporanBaru.trim() || isSending"
                            class="self-end p-2.5 bg-[rgb(0,83,63)] text-white rounded-xl hover:opacity-90 disabled:opacity-40 transition-all shadow-md">
                            <template x-if="!isSending">
                                <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                </svg>
                            </template>
                            <template x-if="isSending">
                                <span
                                    class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                            </template>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        function instruksiNakes() {
            return {
                instruksi: [],
                laporanBaru: '',
                isSending: false,
                deviceId: 'DEVICE_01',
                notificationSound: new Audio('/assets/sounds/notification.mp3'),

                scrollToBottom() {
                    this.$nextTick(() => {
                        const container = this.$refs.chatBox;
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                },

                autoResize(el) {
                    el.style.height = 'auto';
                    el.style.height = el.scrollHeight + 'px';
                },

                get sortedInstruksi() {
                    return [...this.instruksi].sort((a, b) => (a.id || 0) - (b.id || 0));
                },

                async init() {
                    // Ambil device pertama dari API
                    await this.fetchDeviceId();
                    await this.getHistory();
                    this.setupReverb();
                    // Polling setiap 5 detik (fallback jika Reverb tidak jalan)
                    setInterval(() => this.getHistory(), 5000);
                    setTimeout(() => this.scrollToBottom(), 500);
                },

                async fetchDeviceId() {
                    try {
                        const res = await fetch('/api/devices');
                        const json = await res.json();
                        if (json.success && json.data.length > 0) {
                            this.deviceId = json.data[0].device_id;
                        }
                    } catch (e) {
                        console.error('Fetch device error:', e);
                    }
                },

                setupReverb() {
                    if (!window.Echo) return;

                    window.Echo.private(`device.${this.deviceId}`)
                        .listen('.instruction.created', (e) => {
                            const exists = this.instruksi.some(i => i.id === e.instruction.id);
                            if (!exists) {
                                this.instruksi.push({
                                    ...e.instruction,
                                    selectedRespon: ''
                                });
                                this.notificationSound.play().catch(() => {});
                                this.scrollToBottom();
                            }
                        });
                },

                async getHistory() {
                    try {
                        const res = await fetch(`/api/instruction?device_id=${this.deviceId}`);
                        const json = await res.json();
                        if (json.success) {
                            const prevCount = this.instruksi.length;
                            // Preserve selectedRespon state saat polling
                            this.instruksi = json.data.map(item => {
                                const existing = this.instruksi.find(i => i.id === item.id);
                                return {
                                    ...item,
                                    selectedRespon: existing ? existing.selectedRespon : ''
                                };
                            });
                            // Scroll ke bawah hanya jika ada data baru
                            if (json.data.length > prevCount) {
                                this.scrollToBottom();
                            }
                        }
                    } catch (e) {
                        console.error('History Error:', e);
                    }
                },

                async kirimLaporan() {
                    if (!this.laporanBaru.trim() || this.isSending) return;

                    // Waktu real-time untuk local update
                    this.isSending = true;
                    const sekarang = new Date().toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    try {
                        const res = await fetch(`/api/instruction/report`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                device_id: this.deviceId,
                                laporan_nakes: this.laporanBaru,
                                waktu: sekarang
                            })
                        });

                        const json = await res.json();
                        if (json.success) {
                            this.laporanBaru = '';
                            this.instruksi.push(json.data);
                            this.scrollToBottom();

                            // Reset textarea height
                            const ta = document.querySelector('textarea');
                            if (ta) ta.style.height = 'auto';
                        }

                        this.laporanBaru = '';
                        this.$nextTick(() => {
                            const el = document.querySelector('textarea'); // Pastikan targetnya pas
                            if (el) el.style.height = 'auto';
                        });
                    } catch (e) {
                        console.error('Laporan Error:', e);
                    } finally {
                        this.isSending = false;
                    }
                },

                async kirimRespon(item) {
                    if (!item.selectedRespon) return;

                    try {
                        const res = await fetch(`/api/instruction/${item.id}/complete`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                respon_nakes: item.selectedRespon
                            })
                        });

                        const json = await res.json();
                        if (json.success) {
                            item.is_completed = true;
                            item.respon_nakes = item.selectedRespon;
                            this.instruksi = [...this.instruksi];
                            this.scrollToBottom();
                        }
                    } catch (e) {
                        console.error('Respon Error:', e);
                    } finally {
                        this.isSending = false;
                    }
                }
            }
        }
    </script>
@endpush
