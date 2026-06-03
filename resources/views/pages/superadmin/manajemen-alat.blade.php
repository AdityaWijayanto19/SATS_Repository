@extends('layouts.app')
@section('title', 'SATS Monitoring - Manajemen Alat')

@section('content')
<div class="min-h-full p-8" style="background: rgba(230,238,236,0.5);" x-data="manajemenAlat()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Manajemen Alat</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola perangkat SATS yang terdaftar</p>
        </div>
        <button @click="showTambahModal = true"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium text-white cursor-pointer transition-all duration-150 hover:opacity-90"
            style="background: rgb(0,83,63);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Daftar Alat
        </button>
    </div>

    {{-- Tabel Inventaris --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Inventaris Perangkat</h2>
                <p class="text-xs text-gray-400 mt-0.5">Daftar seluruh perangkat SATS yang terdaftar</p>
            </div>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Total: <span x-text="devices.length"></span> perangkat</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 font-semibold text-gray-600">ID Perangkat</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Nama Perangkat</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Urgensi Terakhir</th>
                        <th class="px-6 py-3 font-semibold text-gray-600 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(device, index) in devices" :key="device.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-gray-700" x-text="device.id"></td>
                            <td class="px-6 py-3 text-gray-600" x-text="device.nama"></td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                                    :class="device.status === 'online' ? 'text-emerald-700 bg-emerald-50' : 'text-pink-700 bg-pink-50'">
                                    <span class="w-1.5 h-1.5 rounded-full"
                                        :class="device.status === 'online' ? 'bg-emerald-500 animate-pulse' : 'bg-pink-500'"></span>
                                    <span x-text="device.status === 'online' ? 'Online' : 'Offline'"></span>
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full"
                                    :class="{
                                        'text-emerald-700 bg-emerald-50': device.urgensi === 'normal',
                                        'text-amber-700 bg-amber-50': device.urgensi === 'warning',
                                        'text-red-700 bg-red-50': device.urgensi === 'critical'
                                    }"
                                    x-text="device.urgensi ? device.urgensi.charAt(0).toUpperCase() + device.urgensi.slice(1) : 'Normal'"></span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="showDetail(index)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                    <button @click="showDeleteConfirm(device.id)" class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="devices.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada perangkat terdaftar</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Detail Perangkat --}}
    <div x-show="showDetailModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">

        <div class="absolute inset-0 bg-black/40" @click="showDetailModal = false"></div>

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Detail Perangkat</h3>
                <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4" x-show="selectedDevice">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0"
                        :class="selectedDevice?.status === 'online' ? 'bg-emerald-50' : 'bg-pink-50'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7"
                            :class="selectedDevice?.status === 'online' ? 'text-emerald-600' : 'text-pink-500'"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 2h-6c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm-1 9h-4v-7h4v7z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800" x-text="selectedDevice?.nama"></h4>
                        <p class="text-sm font-mono text-gray-500" x-text="selectedDevice?.id"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Status</p>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full"
                            :class="selectedDevice?.status === 'online' ? 'text-emerald-700 bg-emerald-100' : 'text-pink-700 bg-pink-100'">
                            <span class="w-1.5 h-1.5 rounded-full"
                                :class="selectedDevice?.status === 'online' ? 'bg-emerald-500 animate-pulse' : 'bg-pink-500'"></span>
                            <span x-text="selectedDevice?.status === 'online' ? 'Online' : 'Offline'"></span>
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Urgensi Terakhir</p>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                            :class="{
                                'text-emerald-700 bg-emerald-100': selectedDevice?.urgensi === 'normal',
                                'text-amber-700 bg-amber-100': selectedDevice?.urgensi === 'warning',
                                'text-red-700 bg-red-100': selectedDevice?.urgensi === 'critical'
                            }"
                            x-text="selectedDevice?.urgensi ? selectedDevice.urgensi.charAt(0).toUpperCase() + selectedDevice.urgensi.slice(1) : 'Normal'"></span>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Terdaftar Sejak</p>
                        <p class="text-sm font-medium text-gray-700" x-text="selectedDevice?.terdaftar ?? '-'"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Terakhir Aktif</p>
                        <p class="text-sm font-medium text-gray-700" x-text="selectedDevice?.terakhirAktif ?? '-'"></p>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs text-gray-400 mb-1">Status Monitoring</p>
                    <p class="text-sm text-gray-700" x-text="selectedDevice?.keterangan ?? '-'"></p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button @click="showDetailModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Tambah Alat --}}
    <div x-show="showTambahModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">

        <div class="absolute inset-0 bg-black/40" @click="showTambahModal = false"></div>

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Daftar Perangkat Baru</h3>
                <button @click="showTambahModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="tambahAlat()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">ID Perangkat</label>
                    <input type="text" x-model="form.id" placeholder="Contoh: DEV-010"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Perangkat</label>
                    <input type="text" x-model="form.nama" placeholder="Contoh: SATS Wearable-10"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showTambahModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="loading"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-all cursor-pointer hover:opacity-90 disabled:opacity-50"
                        style="background: rgb(0,83,63);">
                        <span x-show="!loading">Daftarkan</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal API Key --}}
    <div x-show="showApiKeyModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">

        <div class="absolute inset-0 bg-black/40" @click="closeApiKeyModal()"></div>

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Perangkat Berhasil Didaftarkan</h3>
                <button @click="closeApiKeyModal()" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4">
                    <p class="text-sm text-emerald-800 font-medium mb-2">Simpan API Key ini! Hanya ditampilkan sekali.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Device ID</label>
                    <div class="flex items-center gap-2">
                        <input type="text" :value="newDevice.device_id" readonly
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm bg-gray-50 font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">API Key</label>
                    <div class="flex items-center gap-2">
                        <input type="text" x-ref="apiKeyInput" :value="newDevice.api_key" readonly
                            class="flex-1 px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm bg-gray-50 font-mono">
                        <button @click="copyApiKey()"
                            class="px-3 py-2.5 text-sm font-medium text-white rounded-lg transition-all cursor-pointer hover:opacity-90"
                            style="background: rgb(0,83,63);">
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-5">
                <button @click="closeApiKeyModal()"
                    class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-all cursor-pointer hover:opacity-90"
                    style="background: rgb(0,83,63);">
                    Selesai
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-black/40" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Hapus Perangkat?</h3>
                <p class="text-sm text-gray-500 mb-1">Perangkat <span class="font-mono font-medium text-gray-700" x-text="deleteTargetId"></span></p>
                <p class="text-sm text-gray-500 mb-6">Semua data terkait akan dihapus secara permanen.</p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 h-10 border border-gray-200 rounded-lg text-sm text-gray-600 font-medium hover:bg-gray-50 cursor-pointer transition">Batal</button>
                    <button @click="confirmDelete()" class="flex-1 h-10 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium cursor-pointer transition">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    const initialDevices = @json($devices);

    function manajemenAlat() {
        return {
            devices: initialDevices,
            showTambahModal: false,
            showDetailModal: false,
            showApiKeyModal: false,
            showDeleteModal: false,
            deleteTargetId: null,
            selectedDevice: null,
            loading: false,
            copied: false,
            form: { id: '', nama: '' },
            newDevice: { device_id: '', api_key: '' },
            pollingInterval: null,

            init() {
                this.startPolling();
            },

            startPolling() {
                this.pollingInterval = setInterval(async () => {
                    try {
                        const res = await fetch('/api/devices');
                        const json = await res.json();
                        if (json.success && json.data) {
                            json.data.forEach(apiDevice => {
                                const existing = this.devices.find(d => d.id === apiDevice.device_id);
                                if (existing) {
                                    existing.status = apiDevice.status;
                                    existing.urgensi = apiDevice.latest?.status || 'normal';
                                    existing.terakhirAktif = apiDevice.latest?.created_at || existing.terakhirAktif;
                                    existing.keterangan = apiDevice.status === 'online' ? 'Aktif monitoring' : 'Tidak aktif';
                                }
                            });
                        }
                    } catch (e) {
                        // silent
                    }
                }, 3000);
            },

            destroy() {
                if (this.pollingInterval) clearInterval(this.pollingInterval);
            },

            showDetail(index) {
                this.selectedDevice = this.devices[index];
                this.showDetailModal = true;
            },

            async tambahAlat() {
                this.loading = true;
                try {
                    const response = await fetch('{{ route("superadmin.manajemen-alat.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            device_id: this.form.id,
                            nama: this.form.nama,
                        }),
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.newDevice = {
                            device_id: result.data.device_id,
                            api_key: result.data.api_key,
                        };
                        this.showTambahModal = false;
                        this.showApiKeyModal = true;
                        this.form = { id: '', nama: '' };
                        // Refresh device list
                        this.devices.push({
                            id: result.data.device_id,
                            nama: result.data.nama,
                            status: 'offline',
                            urgensi: 'normal',
                            terdaftar: new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }),
                            terakhirAktif: '-',
                            keterangan: 'Tidak aktif',
                        });
                    } else {
                        alert(result.message || 'Gagal mendaftarkan perangkat');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },

            showDeleteConfirm(deviceId) {
                this.deleteTargetId = deviceId;
                this.showDeleteModal = true;
            },

            async confirmDelete() {
                if (!this.deleteTargetId) return;

                try {
                    const response = await fetch(`/superadmin/manajemen-alat/${this.deleteTargetId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.devices = this.devices.filter(d => d.id !== this.deleteTargetId);
                        this.showDeleteModal = false;
                        this.deleteTargetId = null;
                    } else {
                        alert(result.message || 'Gagal menghapus perangkat');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            },

            copyApiKey() {
                navigator.clipboard.writeText(this.newDevice.api_key).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                });
            },

            closeApiKeyModal() {
                this.showApiKeyModal = false;
                this.newDevice = { device_id: '', api_key: '' };
                this.copied = false;
            },
        }
    }
</script>
@endpush
@endsection
