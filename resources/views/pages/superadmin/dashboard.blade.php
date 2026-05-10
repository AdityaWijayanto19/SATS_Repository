@extends('layouts.app')

@section('content')
<div class="min-h-full p-8" style="background: rgba(230,238,236,0.5);">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Dashboard Superadmin</h1>
        <p class="text-sm text-gray-500 mt-1">Monitoring sistem dan perangkat SATS</p>
    </div>

    {{-- ==================== STAT CARDS ==================== --}}
    <div class="grid grid-cols-4 gap-5 mb-6">

        {{-- Total Alat Terdaftar --}}
        <div class="rounded-xl p-5 border" style="background: rgba(0,83,63,0.05); border-color: rgba(0,83,63,0.15);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(0,83,63,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: rgb(0,62,48);" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M4 6h18V4H4c-1.1 0-2 .9-2 2v11H0v3h14v-3H4V6zm19 2h-6c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h6c.55 0 1-.45 1-1V9c0-.55-.45-1-1-1zm-1 9h-4v-7h4v7z"/>
                    </svg>
                </div>
            </div>
            {{-- TODO: Ganti dengan data real dari backend --}}
            <p class="text-2xl font-bold" style="color: rgb(0,62,48);">5</p>
            <p class="text-sm text-gray-500">Total Alat Terdaftar</p>
        </div>

        {{-- Alat Aktif (Online) --}}
        <div class="rounded-xl p-5 border" style="background: rgba(16,185,129,0.05); border-color: rgba(16,185,129,0.2);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Online
                </span>
            </div>
            {{-- TODO: Ganti dengan data real dari backend --}}
            <p class="text-2xl font-bold text-emerald-700">3</p>
            <p class="text-sm text-gray-500">Alat Aktif</p>
        </div>

        {{-- Alat Non-Aktif (Offline) --}}
        <div class="rounded-xl p-5 border" style="background: rgba(236,72,153,0.05); border-color: rgba(236,72,153,0.2);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(236,72,153,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-pink-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <span class="flex items-center gap-1.5 text-xs font-medium text-pink-600 bg-pink-50 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                    Offline
                </span>
            </div>
            {{-- TODO: Ganti dengan data real dari backend --}}
            <p class="text-2xl font-bold text-pink-600">3</p>
            <p class="text-sm text-gray-500">Alat Non-Aktif</p>
        </div>

        {{-- Total Pengguna --}}
        <div class="rounded-xl p-5 border" style="background: rgba(59,130,246,0.05); border-color: rgba(59,130,246,0.2);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(59,130,246,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                </div>
            </div>
            {{-- TODO: Ganti dengan data real dari backend --}}
            <p class="text-2xl font-bold text-blue-600">5</p>
            <p class="text-sm text-gray-500">Total Pengguna</p>
        </div>

    </div>

    {{-- ==================== KONTEN BAWAH: TABEL + LOG ==================== --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- Tabel Alat Kritis (2 kolom) --}}
        <div class="col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Alat Kritis</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Perangkat yang membutuhkan perhatian segera</p>
                </div>
                <span class="text-xs font-medium text-red-600 bg-red-50 px-3 py-1 rounded-full">Perlu Perhatian</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-6 py-3 font-semibold text-gray-600">ID Perangkat</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Nama Alat</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Masalah</th>
                        </tr>
                    </thead>
                    {{-- TODO: Ganti dengan data real dari backend (loop dari database) --}}
                    <tbody class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-gray-700">DEV-005</td>
                            <td class="px-6 py-3 text-gray-600">Monitor Vital Sign A</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Warning
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">Sering Terputus</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-gray-700">DEV-006</td>
                            <td class="px-6 py-3 text-gray-600">Monitor Vital Sign B</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Kritis
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">Gangguan Jaringan</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-gray-700">DEV-007</td>
                            <td class="px-6 py-3 text-gray-600">Sensor SpO2 C</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Warning
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">Sering Terputus</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-gray-700">DEV-008</td>
                            <td class="px-6 py-3 text-gray-600">Sensor Suhu D</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-700 bg-red-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                    Kritis
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">Gangguan Jaringan</td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-mono text-gray-700">DEV-009</td>
                            <td class="px-6 py-3 text-gray-600">Monitor Vital Sign E</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Warning
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-600">Sering Terputus</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Log Aktivitas Terbaru (1 kolom) --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Log Aktivitas</h2>
                <p class="text-xs text-gray-400 mt-0.5">Riwayat aktivitas sistem terbaru</p>
            </div>

            {{-- TODO: Ganti dengan data real dari backend (loop dari database/log) --}}
            <div class="px-6 py-4 space-y-4 max-h-[400px] overflow-y-auto">

                {{-- Activity Item --}}
                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></div>
                        <div class="w-0.5 h-full bg-gray-200 flex-1"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm text-gray-700">Admin menambahkan alat baru <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">DEV-010</span></p>
                        <p class="text-xs text-gray-400 mt-1">09 Mei 2026, 08:30</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
                        <div class="w-0.5 h-full bg-gray-200 flex-1"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm text-gray-700">User <span class="font-medium">Dr. Andi</span> berhasil login</p>
                        <p class="text-xs text-gray-400 mt-1">09 Mei 2026, 08:15</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></div>
                        <div class="w-0.5 h-full bg-gray-200 flex-1"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm text-gray-700">Alat <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">DEV-006</span> terputus dari jaringan</p>
                        <p class="text-xs text-gray-400 mt-1">09 Mei 2026, 07:50</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 mt-1.5 flex-shrink-0"></div>
                        <div class="w-0.5 h-full bg-gray-200 flex-1"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm text-gray-700">Admin memperbarui data alat <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">DEV-003</span></p>
                        <p class="text-xs text-gray-400 mt-1">09 Mei 2026, 07:30</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500 mt-1.5 flex-shrink-0"></div>
                        <div class="w-0.5 h-full bg-gray-200 flex-1"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm text-gray-700">Peringatan: <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">DEV-005</span> mengalami gangguan koneksi</p>
                        <p class="text-xs text-gray-400 mt-1">09 Mei 2026, 07:00</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-blue-500 mt-1.5 flex-shrink-0"></div>
                        <div class="w-0.5 h-full bg-gray-200 flex-1"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm text-gray-700">User <span class="font-medium">Suster Rina</span> berhasil login</p>
                        <p class="text-xs text-gray-400 mt-1">08 Mei 2026, 22:00</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></div>
                        <div class="w-0.5 h-full bg-gray-200 flex-1"></div>
                    </div>
                    <div class="pb-4">
                        <p class="text-sm text-gray-700">Alat <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded">DEV-009</span> terputus dari jaringan</p>
                        <p class="text-xs text-gray-400 mt-1">08 Mei 2026, 21:45</p>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection
