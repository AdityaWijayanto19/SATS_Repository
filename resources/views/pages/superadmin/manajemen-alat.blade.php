@extends('layouts.app')

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
            {{-- TODO: Ganti dengan data real dari backend --}}
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Total: 9 perangkat</span>
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
                {{-- TODO: Ganti dengan data real dari backend (loop dari database) --}}
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-001</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-1</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Online
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">Normal</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(0)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-002</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-2</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Online
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">Normal</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(1)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-003</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-3</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Online
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">Normal</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(2)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-004</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-4</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Online
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">Normal</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(3)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-005</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-5</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                Offline
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">Warning</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(4)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-006</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-6</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                Offline
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">Critical</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(5)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-007</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-7</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                Offline
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">Warning</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(6)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-008</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-8</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                Offline
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">Critical</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(7)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 font-mono text-gray-700">DEV-009</td>
                        <td class="px-6 py-3 text-gray-600">SATS Wearable-9</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                Offline
                            </span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">Warning</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button @click="showDetail(8)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                                <button class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Hapus</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ==================== MODAL DETAIL PERANGKAT ==================== --}}
    <div x-show="showDetailModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/40" @click="showDetailModal = false"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Detail Perangkat</h3>
                <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            {{-- TODO: Ganti dengan data real dari backend --}}
            <div class="px-6 py-5 space-y-4" x-show="selectedDevice">
                {{-- Info Utama --}}
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0"
                        :class="selectedDevice?.status === 'Online' ? 'bg-emerald-50' : 'bg-pink-50'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7"
                            :class="selectedDevice?.status === 'Online' ? 'text-emerald-600' : 'text-pink-500'"
                            viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 2h-6c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm-1 9h-4v-7h4v7z"/>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800" x-text="selectedDevice?.nama"></h4>
                        <p class="text-sm font-mono text-gray-500" x-text="selectedDevice?.id"></p>
                    </div>
                </div>

                {{-- Grid Info --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Status</p>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2 py-0.5 rounded-full"
                            :class="selectedDevice?.status === 'Online' ? 'text-emerald-700 bg-emerald-100' : 'text-pink-700 bg-pink-100'">
                            <span class="w-1.5 h-1.5 rounded-full"
                                :class="selectedDevice?.status === 'Online' ? 'bg-emerald-500 animate-pulse' : 'bg-pink-500'"></span>
                            <span x-text="selectedDevice?.status"></span>
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Urgensi Terakhir</p>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                            :class="{
                                'text-emerald-700 bg-emerald-100': selectedDevice?.urgensi === 'Normal',
                                'text-amber-700 bg-amber-100': selectedDevice?.urgensi === 'Warning',
                                'text-red-700 bg-red-100': selectedDevice?.urgensi === 'Critical'
                            }"
                            x-text="selectedDevice?.urgensi"></span>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Terdaftar Sejak</p>
                        <p class="text-sm font-medium text-gray-700" x-text="selectedDevice?.terdaftar"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Terakhir Aktif</p>
                        <p class="text-sm font-medium text-gray-700" x-text="selectedDevice?.terakhirAktif"></p>
                    </div>
                </div>

                {{-- Lokasi --}}
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs text-gray-400 mb-1">Lokasi / Keterangan</p>
                    <p class="text-sm text-gray-700" x-text="selectedDevice?.keterangan"></p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button @click="showDetailModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL TAMBAH ALAT ==================== --}}
    <div x-show="showTambahModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">

        {{-- Overlay --}}
        <div class="absolute inset-0 bg-black/40" @click="showTambahModal = false"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            {{-- Modal Header --}}
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Daftar Perangkat Baru</h3>
                <button @click="showTambahModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            {{-- TODO: Tambahkan action form ke backend (POST route) --}}
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

                {{-- Modal Footer --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showTambahModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-all cursor-pointer hover:opacity-90"
                        style="background: rgb(0,83,63);">
                        Daftarkan
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

@push('scripts')
<script>
    // TODO: Ganti data dummy dengan fetch dari backend
    const devices = [
        { id: 'DEV-001', nama: 'SATS Wearable-1', status: 'Online', urgensi: 'Normal', terdaftar: '01 Jan 2026', terakhirAktif: '09 Mei 2026, 08:30', keterangan: 'Ambulans Unit A - RSUD Kota' },
        { id: 'DEV-002', nama: 'SATS Wearable-2', status: 'Online', urgensi: 'Normal', terdaftar: '01 Jan 2026', terakhirAktif: '09 Mei 2026, 08:25', keterangan: 'Ambulans Unit B - RSUD Kota' },
        { id: 'DEV-003', nama: 'SATS Wearable-3', status: 'Online', urgensi: 'Normal', terdaftar: '15 Jan 2026', terakhirAktif: '09 Mei 2026, 08:20', keterangan: 'Ambulans Unit C - Klinik Pusat' },
        { id: 'DEV-004', nama: 'SATS Wearable-4', status: 'Online', urgensi: 'Normal', terdaftar: '15 Jan 2026', terakhirAktif: '09 Mei 2026, 08:15', keterangan: 'Ambulans Unit D - RS Swasta' },
        { id: 'DEV-005', nama: 'SATS Wearable-5', status: 'Offline', urgensi: 'Warning', terdaftar: '01 Feb 2026', terakhirAktif: '08 Mei 2026, 22:10', keterangan: 'Ambulans Unit E - Dalam Perbaikan' },
        { id: 'DEV-006', nama: 'SATS Wearable-6', status: 'Offline', urgensi: 'Critical', terdaftar: '01 Feb 2026', terakhirAktif: '07 Mei 2026, 15:00', keterangan: 'Ambulans Unit F - Gangguan Jaringan' },
        { id: 'DEV-007', nama: 'SATS Wearable-7', status: 'Offline', urgensi: 'Warning', terdaftar: '15 Feb 2026', terakhirAktif: '08 Mei 2026, 20:30', keterangan: 'Ambulans Unit G - Sering Terputus' },
        { id: 'DEV-008', nama: 'SATS Wearable-8', status: 'Offline', urgensi: 'Critical', terdaftar: '01 Mar 2026', terakhirAktif: '07 Mei 2026, 10:45', keterangan: 'Ambulans Unit H - Kerusakan Hardware' },
        { id: 'DEV-009', nama: 'SATS Wearable-9', status: 'Offline', urgensi: 'Warning', terdaftar: '01 Mar 2026', terakhirAktif: '08 Mei 2026, 21:00', keterangan: 'Ambulans Unit I - Dalam Perbaikan' },
    ];

    function manajemenAlat() {
        return {
            showTambahModal: false,
            showDetailModal: false,
            selectedDevice: null,
            form: {
                id: '',
                nama: ''
            },
            showDetail(index) {
                this.selectedDevice = devices[index];
                this.showDetailModal = true;
            },
            tambahAlat() {
                // TODO: Kirim data ke backend (POST /superadmin/manajemen-alat)
                console.log('Tambah perangkat:', this.form);
                this.showTambahModal = false;
                this.form = { id: '', nama: '' };
                // TODO: Refresh tabel setelah berhasil tambah
            }
        }
    }
</script>
@endpush
@endsection
