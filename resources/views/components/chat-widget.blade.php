{{--
    Floating Chat Widget
    - Minimized: green rounded button bottom-right
    - Expanded: chat panel with messages + input
    - Both nakes and dokter can send messages
--}}
@php
    $user = auth()->user();
    $role = $user->role;
    $currentUserPhoto = $user->photo ? asset($user->photo) : null;
@endphp

<div x-data="chatWidget()" x-init="init()" class="fixed bottom-6 right-6 z-[9999]" style="font-family: inherit;">

    {{-- ============================================ --}}
    {{-- FLOATING BUTTON (Minimized State) --}}
    {{-- ============================================ --}}
    <button x-show="!isOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
        @click="toggle()"
        class="w-14 h-14 bg-[rgb(0,83,63)] hover:bg-[rgb(0,100,80)] text-white rounded-2xl shadow-lg hover:shadow-xl flex items-center justify-center transition-all duration-200 cursor-pointer group">
        <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
        </svg>
        <span x-show="hasNewMessage"
            class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
    </button>

    {{-- ============================================ --}}
    {{-- EXPANDED PANEL --}}
    {{-- ============================================ --}}
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        class="w-[380px] h-[560px] bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden flex flex-col mb-3">

        {{-- ======== HEADER ======== --}}
        <div class="bg-[rgb(0,83,63)] px-5 py-4 flex-shrink-0">
            <div class="flex items-center gap-3">
                <img src="/assets/logo.png" alt="SATS" class="w-9 h-9 rounded-lg object-contain bg-white/10 p-0.5">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h3 class="text-white font-bold text-sm">SATS</h3>
                        <span class="flex items-center gap-1 text-[10px] text-emerald-200">
                            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                            Online
                        </span>
                    </div>
                    <p class="text-emerald-200/80 text-[10px] mt-0.5" x-text="greetingText"></p>
                </div>
            </div>
        </div>

        {{-- ======== CHAT AREA ======== --}}
        <div class="flex-1 overflow-hidden flex flex-col">

            {{-- Messages --}}
            <div x-ref="chatBox" class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 bg-gray-50/30">

                {{-- Empty state --}}
                <template x-if="instruksi.length === 0">
                    <div class="flex flex-col items-center justify-center h-full opacity-40">
                        <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
                        </svg>
                        <p class="text-xs italic text-gray-400">Belum ada percakapan.</p>
                        <p x-show="!deviceId" class="text-[10px] text-amber-500 mt-1">Pilih perangkat terlebih dahulu</p>
                    </div>
                </template>

                {{-- Messages list --}}
                <template x-for="item in sortedInstruksi" :key="item.id">
                    <div class="flex flex-col gap-2">

                        {{-- Laporan/pesan dari nakes --}}
                        <div x-show="item.laporan_nakes && item.laporan_nakes !== '-'"
                            :class="role === 'nakes' ? 'flex justify-end' : 'flex justify-start items-end gap-2'">

                            <div x-show="role === 'dokter'" class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                <template x-if="item.nakes_photo">
                                    <img :src="'/' + item.nakes_photo" alt="Nakes" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.nakes_photo">
                                    <span class="text-[9px] font-bold text-blue-700">NK</span>
                                </template>
                            </div>

                            <div :class="role === 'nakes'
                                ? 'max-w-[80%] bg-[rgb(0,83,63)] text-white p-3 rounded-2xl rounded-tr-none text-sm shadow-md'
                                : 'max-w-[80%] bg-white border border-gray-200 p-3 rounded-2xl rounded-bl-none shadow-sm'">
                                <p x-show="role === 'dokter'" class="text-[10px] font-bold text-blue-800 mb-1" x-text="item.nakes_name || 'NAKES'"></p>
                                <p x-text="item.laporan_nakes" class="text-[13px]" :class="role === 'dokter' ? 'text-gray-800' : ''"></p>
                                <span class="text-[9px] opacity-60 block mt-1 text-right" :class="role === 'dokter' ? 'text-gray-400' : ''" x-text="item.waktu"></span>
                            </div>
                        </div>

                        {{-- Instruksi dokter (always in DOM, hidden if no instruksi_dokter) --}}
                        <div x-show="item.instruksi_dokter"
                            :class="role === 'nakes' ? 'flex justify-start items-end gap-2' : 'flex justify-end'">

                            <div x-show="role === 'nakes'" class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                <template x-if="item.user_photo">
                                    <img :src="'/' + item.user_photo" alt="Dokter" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!item.user_photo">
                                    <span class="text-[9px] font-bold text-emerald-700">DR</span>
                                </template>
                            </div>

                            <div x-show="item.instruksi_dokter"
                                :class="role === 'nakes'
                                    ? 'max-w-[80%] bg-white border border-gray-200 p-3 rounded-2xl rounded-bl-none shadow-sm'
                                    : (item.is_completed ? 'max-w-[80%] bg-green-50 border-green-100 p-3 rounded-2xl rounded-tr-none shadow-md opacity-90' : 'max-w-[80%] bg-[rgb(0,83,63)] text-white p-3 rounded-2xl rounded-tr-none shadow-md')">

                                <p x-show="role === 'nakes'" class="text-[10px] font-bold text-emerald-800 mb-1" x-text="item.user_name || 'DOKTER SATS'"></p>

                                <p class="text-[13px] leading-relaxed mb-2"
                                    :class="role === 'nakes' ? 'text-gray-800' : (item.is_completed ? 'text-green-900' : 'text-white')"
                                    x-text="item.instruksi_dokter"></p>

                                <span class="text-[9px] opacity-60 block text-right"
                                    :class="role === 'nakes' ? 'text-gray-400' : (item.is_completed ? 'text-emerald-600' : 'text-white')"
                                    x-text="item.waktu"></span>

                                {{-- Quick response buttons (nakes only, incomplete) --}}
                                <div x-show="role === 'nakes' && !item.is_completed" class="mt-2.5 pt-2 border-t border-dashed border-gray-100">
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="opsi in quickReplies">
                                            <button @click="kirimRespon(item, opsi)"
                                                class="text-[9px] px-2.5 py-1 rounded-full border border-emerald-100 bg-emerald-50 text-emerald-800 font-bold hover:bg-emerald-600 hover:text-white transition-all cursor-pointer">
                                                <span x-text="opsi"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Completed badge (nakes view) --}}
                                <div x-show="role === 'nakes' && item.is_completed" class="mt-2.5 pt-2 border-t border-dashed border-gray-100">
                                    <div class="flex items-center gap-1 text-emerald-700 text-[9px] font-bold bg-emerald-50 p-1.5 rounded-lg border border-emerald-100">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                                        </svg>
                                        <span x-text="'DIKONFIRMASI: ' + item.respon_nakes"></span>
                                    </div>
                                </div>

                                {{-- Completed badge (dokter view) --}}
                                <div x-show="role === 'dokter' && item.is_completed" class="mt-2 pt-2 border-t border-green-200/50 flex flex-col gap-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-[9px] font-bold text-green-800/60 uppercase">Respon Nakes:</p>
                                        <span class="text-[9px] bg-emerald-500 text-white px-2 py-0.5 rounded-full font-bold">SELESAI</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-green-900 font-bold text-xs bg-white/50 p-2 rounded-lg">
                                        <span x-text="item.respon_nakes"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            {{-- ======== INPUT AREA ======== --}}
            <div class="px-4 py-3 border-t border-gray-100 bg-white flex-shrink-0">
                <div class="flex gap-2">
                    <textarea x-model="teksBaru" rows="1"
                        :placeholder="role === 'nakes' ? 'Ketik pesan atau laporan...' : 'Ketik instruksi medis...'"
                        class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 outline-none resize-none"
                        @keydown.enter.prevent="kirimPesan()"></textarea>
                    <button @click="kirimPesan()" :disabled="!teksBaru.trim() || isSending || !deviceId"
                        class="self-end p-2 bg-[rgb(0,83,63)] text-white rounded-xl hover:opacity-90 disabled:opacity-40 disabled:cursor-not-allowed transition-all cursor-pointer">
                        <template x-if="!isSending">
                            <svg class="w-4 h-4 rotate-90" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                            </svg>
                        </template>
                        <template x-if="isSending">
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                        </template>
                    </button>
                </div>
            </div>
        </div>

        {{-- ======== FOOTER ======== --}}
        <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex-shrink-0">
            <p class="text-[9px] text-gray-400 text-center tracking-wide">Smart Ambulance Telemedicine System</p>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- CLOSE BUTTON (when panel is open) --}}
    {{-- ============================================ --}}
    <button x-show="isOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
        @click="toggle()"
        class="w-14 h-14 bg-[rgb(0,83,63)] hover:bg-red-600 text-white rounded-2xl shadow-lg hover:shadow-xl flex items-center justify-center transition-all duration-200 cursor-pointer">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

@push('scripts')
<script>
    function chatWidget() {
        return {
            isOpen: false,
            role: '{{ $role }}',
            currentUserPhoto: @json($currentUserPhoto),
            instruksi: [],
            teksBaru: '',
            isSending: false,
            deviceId: null,
            _prevDeviceId: null,
            hasNewMessage: false,
            notificationSound: new Audio('/assets/sounds/notification.mp3'),

            quickReplies: [
                'Sudah dilakukan',
                'Dalam proses',
                'Alat tidak tersedia',
                'Obat sudah diberikan',
                'Pasien stabil',
                'Pasien kritis',
                'Butuh bantuan',
                'Gagal',
                'Monitoring lanjutan',
            ],

            get greetingText() {
                if (this.role === 'nakes') {
                    return 'Butuh instruksi dari dokter? Mulai percakapan di sini.';
                }
                return 'Kirim instruksi medis ke nakes melalui percakapan ini.';
            },

            get sortedInstruksi() {
                return [...this.instruksi].sort((a, b) => (a.id || 0) - (b.id || 0));
            },

            toggle() {
                this.isOpen = !this.isOpen;
                if (this.isOpen) {
                    this.hasNewMessage = false;
                    this.$nextTick(() => this.scrollToBottom());
                }
            },

            async init() {
                // Listen for device changes
                window.addEventListener('deviceSelected', async (e) => {
                    const deviceId = e.detail.deviceId;
                    this._connectDevice(deviceId);
                });

                // Also check if device was already selected before this component initialized
                // (dashboard init() runs first and dispatches deviceSelected before listener is ready)
                this.$nextTick(async () => {
                    if (globalSelectedDeviceId && !this.deviceId) {
                        await this._connectDevice(globalSelectedDeviceId);
                    }
                });
            },

            async _connectDevice(deviceId) {
                if (!deviceId) return;
                if (this._prevDeviceId && window.Echo && this._prevDeviceId !== deviceId) {
                    window.Echo.leave(`device.${this._prevDeviceId}`);
                }
                this._prevDeviceId = deviceId;
                this.deviceId = deviceId;
                await this.fetchInstructions(deviceId);
                this.setupReverb(deviceId);
                this.$nextTick(() => this.scrollToBottom());
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    if (this.$refs.chatBox) {
                        this.$refs.chatBox.scrollTop = this.$refs.chatBox.scrollHeight;
                    }
                });
            },

            async fetchInstructions(deviceId) {
                if (!deviceId) return;
                try {
                    const res = await fetch(`/api/instruction?device_id=${deviceId}`);
                    const json = await res.json();
                    if (json.success) this.instruksi = json.data;
                } catch (e) {
                    console.error('fetchInstructions error:', e);
                }
            },

            setupReverb(deviceId) {
                if (!window.Echo) return;
                window.Echo.private(`device.${deviceId}`)
                    .listen('.instruction.created', (e) => {
                        if (!this.instruksi.some(i => i.id === e.instruction.id)) {
                            this.instruksi = [...this.instruksi, e.instruction];
                            this.playNotification();
                        }
                    })
                    .listen('.instruction.report.submitted', (e) => {
                        if (!this.instruksi.some(i => i.id === e.instruction.id)) {
                            this.instruksi = [...this.instruksi, e.instruction];
                            this.playNotification();
                        }
                    })
                    .listen('.instruction.updated', (e) => {
                        this.instruksi = this.instruksi.map(i =>
                            i.id === e.instruction.id
                                ? { ...i, is_completed: e.instruction.is_completed, respon_nakes: e.instruction.respon_nakes, completed_at: e.instruction.completed_at }
                                : i
                        );
                        this.playNotification();
                    });
            },

            playNotification() {
                if (!this.isOpen) {
                    this.hasNewMessage = true;
                }
                this.notificationSound.play().catch(() => {});
                this.scrollToBottom();
            },

            // Unified send: route based on role
            async kirimPesan() {
                if (!this.teksBaru.trim() || this.isSending || !this.deviceId) return;
                if (this.role === 'dokter') {
                    await this.kirimInstruksi();
                } else {
                    await this.kirimLaporan();
                }
            },

            async kirimInstruksi() {
                this.isSending = true;
                const sekarang = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                try {
                    const res = await fetch('/api/instruction', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            device_id: this.deviceId,
                            instruksi_dokter: this.teksBaru,
                            waktu: sekarang
                        })
                    });
                    if (res.ok) {
                        this.teksBaru = '';
                        // Jangan push di sini — Reverb broadcast sudah menambahkan pesan
                        this.scrollToBottom();
                    }
                } catch (e) {
                    console.error('Kirim Instruksi Error:', e);
                } finally {
                    this.isSending = false;
                }
            },

            async kirimLaporan() {
                this.isSending = true;
                const sekarang = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                try {
                    const res = await fetch('/api/instruction/report', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            device_id: this.deviceId,
                            laporan_nakes: this.teksBaru,
                            waktu: sekarang
                        })
                    });
                    const json = await res.json();
                    if (json.success) {
                        this.teksBaru = '';
                        // Jangan push di sini — Reverb broadcast sudah menambahkan pesan
                        this.scrollToBottom();
                    }
                } catch (e) {
                    console.error('Kirim Laporan Error:', e);
                } finally {
                    this.isSending = false;
                }
            },

            async kirimRespon(item, opsi) {
                try {
                    const res = await fetch(`/api/instruction/${item.id}/complete`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ respon_nakes: opsi })
                    });
                    const json = await res.json();
                    if (json.success) {
                        this.instruksi = this.instruksi.map(i =>
                            i.id === item.id ? { ...i, is_completed: true, respon_nakes: opsi } : i
                        );
                    }
                } catch (e) {
                    console.error(e);
                }
            }
        };
    }
</script>
@endpush
