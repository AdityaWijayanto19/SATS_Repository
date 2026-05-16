@extends('layouts.app')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]">
        <div class="max-w-4xl mx-auto" x-data="instruksiDokter()" x-init="init()">
            <div
                class="bg-white rounded-2xl border border-[rgba(0,83,63,0.1)] shadow-sm overflow-hidden flex flex-col h-[80vh]">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-[rgba(0,83,63,0.08)] bg-white flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-[rgb(0,62,48)]">Monitoring Ambulans: <span x-text="deviceId"></span>
                        </h2>
                        <p class="text-[11px] text-gray-400">Pantau laporan nakes dan berikan instruksi medis</p>
                    </div>
                </div>

                {{-- Chat Area dengan x-ref untuk Scroll --}}
                <div x-ref="chatBox" class="flex-1 overflow-y-auto p-6 flex flex-col gap-6 bg-gray-50/30 scroll-smooth">
                    <template x-if="instruksi.length === 0">
                        <div class="flex flex-col items-center justify-center h-full opacity-40">
                            <p class="text-sm italic">Belum ada aktivitas laporan.</p>
                        </div>
                    </template>

                    <template x-for="item in sortedInstruksi" :key="item.id">
                        <div class="flex flex-col gap-3">

                            {{-- 1. Laporan Nakes (Sisi Kiri) --}}
                            <div class="flex justify-start items-end gap-2"
                                x-show="item.laporan_nakes && item.laporan_nakes !== '-'">
                                {{-- Foto Profil Inisial --}}
                                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0 mb-1"
                                    x-text="getInitials(item.nakes_name || 'Nakes')">
                                </div>

                                <div
                                    class="max-w-[75%] bg-white border border-gray-200 p-3 rounded-2xl rounded-bl-none shadow-sm relative">
                                    <div class="flex flex-col">
                                        {{-- Nama Nakes --}}
                                        <span class="text-[10px] font-bold text-emerald-700 uppercase mb-1"
                                            x-text="item.nakes_name || 'NAKES'"></span>

                                        <p class="text-sm text-gray-700 leading-relaxed font-normal mb-4"
                                            x-text="item.laporan_nakes"></p>

                                        {{-- Waktu Pojok Bawah --}}
                                        <span class="absolute bottom-1 right-3 text-[9px] text-gray-400"
                                            x-text="item.waktu"></span>
                                    </div>
                                </div>
                            </div>

                            <template x-if="item.instruksi_dokter">
                                <div class="flex justify-end">
                                    <div :class="item.is_completed ? 'bg-green-50 border-green-100 opacity-90' :
                                        'bg-[rgb(0,83,63)] text-white'"
                                        class="max-w-[80%] p-4 rounded-2xl rounded-tr-none shadow-md transition-all border relative">

                                        <p class="text-sm leading-relaxed mb-2"
                                            :class="item.is_completed ? 'text-green-900' : 'text-white'"
                                            x-text="item.instruksi_dokter"></p>

                                        <span class="absolute bottom-1 right-3 text-[9px] opacity-70"
                                            :class="item.is_completed ? 'text-emerald-600' : 'text-white'"
                                            x-text="item.waktu"></span>

                                        {{-- Slot Respon dari Nakes --}}
                                        <template x-if="item.is_completed">
                                            <div class="mt-3 pt-2 border-t border-green-200/50 flex flex-col gap-1">
                                                <div class="flex items-center gap-2">
                                                    <p class="text-[9px] font-bold text-green-800/60 uppercase">Respon Balik
                                                        Nakes:</p>
                                                    <template x-if="item.is_completed">
                                                        <span
                                                            class="text-[9px] bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold">SELESAI</span>
                                                    </template>
                                                </div>

                                                <div
                                                    class="flex items-center gap-2 text-green-900 font-bold text-xs bg-white/50 p-2 rounded-lg">
                                                    <span x-text="item.respon_nakes"></span>
                                                    <span class="text-[9px] font-normal opacity-60"
                                                        x-text="'• ' + item.completed_at"></span>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Input Bar --}}
                <div class="px-5 py-3.5 border-t border-[rgba(0,83,63,0.08)] bg-gray-50/50">
                    <div class="flex gap-3">
                        <textarea x-model="teksBaru" rows="1" @input="autoResize($el)" placeholder="Tulis instruksi medis..."
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                            @keydown.ctrl.enter="kirimInstruksi()"></textarea>
                        <button @click="kirimInstruksi()" :disabled="!teksBaru.trim() || isSending"
                            class="self-end p-2.5 bg-[rgb(0,83,63)] text-white rounded-xl hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
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
        function instruksiDokter() {
            return {
                instruksi: [],
                teksBaru: '',
                isSending: false,
                deviceId: 'DEVICE_01',
                notificationSound: new Audio('/assets/sounds/notification.mp3'),

                // Ambil Inisial Nama (Contoh: Budi Santoso -> BS)
                getInitials(name) {
                    if (!name) return 'NK';
                    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
                },

                // Fungsi Auto Scroll ke paling bawah
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
                    // Scroll ke bawah setelah data awal dimuat
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
                        .listen('.instruction.report.submitted', (e) => {
                            const exists = this.instruksi.some(i => i.id === e.instruction.id);
                            if (!exists) {
                                this.instruksi.push(e.instruction);
                                this.scrollToBottom();
                                this.notificationSound.play().catch(() => {});
                            }
                        })

                        .listen('.instruction.updated', (e) => {
                            const item = this.instruksi.find(i => i.id === e.instruction.id);
                            if (item) {
                                item.is_completed = e.instruction.is_completed;
                                item.respon_nakes = e.instruction.respon_nakes;
                                item.completed_at = e.instruction.completed_at;
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
                            this.instruksi = json.data;
                            // Scroll ke bawah hanya jika ada data baru
                            if (json.data.length > prevCount) {
                                this.scrollToBottom();
                            }
                        }
                    } catch (e) {
                        console.error('History Error:', e);
                    }
                },

                async kirimInstruksi() {
                    if (!this.teksBaru.trim() || this.isSending) return;

                    // Ambil waktu real-time saat ini
                    this.isSending = true;
                    const sekarang = new Date().toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });

                    const pesanBaru = {
                        device_id: this.deviceId,
                        instruksi_dokter: this.teksBaru,
                        waktu: sekarang // Menggunakan waktu client agar instan
                    };

                    try {
                        const res = await fetch(`/api/instruction`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(pesanBaru)
                        });

                        if (res.ok) {
                            const json = await res.json();
                            this.teksBaru = '';
                            // Tambahkan ke array lokal agar langsung muncul (Optimistic Update)
                            this.instruksi.push(json.data);
                            this.scrollToBottom(); // Scroll otomatis setelah kirim
                        }
                        this.teksBaru = '';
                        this.$nextTick(() => {
                            const el = document.querySelector('textarea'); // Pastikan targetnya pas
                            if (el) el.style.height = 'auto';
                        });
                    } catch (e) {
                        console.error('Kirim Instruksi Error:', e);
                    } finally {
                        this.isSending = false;
                    }
                }
            }
        }
    </script>
@endpush
