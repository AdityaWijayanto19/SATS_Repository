@extends('layouts.app')

@section('content')
<main class="h-screen flex-1 overflow-y-auto p-6 bg-[rgb(230,238,236)]">

    <h1 class="text-2xl font-medium text-[rgb(0,62,48)] mb-5">Dashboard</h1>

    {{-- Stats --}}
    <div class="grid grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl p-4" style="border: 0.5px solid rgba(0,83,63,0.12);">
            <p class="text-[11px] uppercase tracking-wide text-[#6b8a82] mb-1.5">Total Alat Terdaftar</p>
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">5</p>
        </div>
        <div class="bg-white rounded-xl p-4" style="border: 0.5px solid rgba(0,83,63,0.12);">
            <p class="text-[11px] uppercase tracking-wide text-[#6b8a82] mb-1.5 flex items-center gap-1.5">
                Alat Aktif
                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium" style="background:#d1f5e8; color:#0a6b47;">Online</span>
            </p>
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">3</p>
        </div>
        <div class="bg-white rounded-xl p-4" style="border: 0.5px solid rgba(0,83,63,0.12);">
            <p class="text-[11px] uppercase tracking-wide text-[#6b8a82] mb-1.5 flex items-center gap-1.5">
                Alat Non-Aktif
                <span class="text-[10px] px-1.5 py-0.5 rounded font-medium" style="background:#fde8e8; color:#a32d2d;">Offline</span>
            </p>
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">3</p>
        </div>
        <div class="bg-white rounded-xl p-4" style="border: 0.5px solid rgba(0,83,63,0.12);">
            <p class="text-[11px] uppercase tracking-wide text-[#6b8a82] mb-1.5">Total Pengguna</p>
            <p class="text-3xl font-medium text-[rgb(0,62,48)]">5</p>
        </div>
    </div>

    {{-- Bottom Grid --}}
    <div class="grid grid-cols-2 gap-3.5">

        {{-- Alat Kritis --}}
        <div class="bg-white rounded-xl overflow-hidden" style="border: 0.5px solid rgba(0,83,63,0.12);">
            <div class="px-4 py-3.5" style="border-bottom: 0.5px solid #e8f0ee;">
                <p class="text-sm font-medium text-[rgb(0,62,48)]">Alat Kritis (Perhatian Teknis Segera)</p>
            </div>
            <table class="w-full">
                <thead>
                    <tr style="background: rgb(0,83,63);">
                        <th class="text-left px-4 py-2 text-[11px] font-medium tracking-wide" style="color:rgba(255,255,255,0.85);">ID</th>
                        <th class="text-left px-4 py-2 text-[11px] font-medium tracking-wide" style="color:rgba(255,255,255,0.85);">Status</th>
                        <th class="text-left px-4 py-2 text-[11px] font-medium tracking-wide" style="color:rgba(255,255,255,0.85);">Masalah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 0.5px solid #f0f5f3;">
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">DEV-005</td>
                        <td class="px-4 py-2.5 text-xs">
                            <span class="flex items-center gap-1.5 text-[#1d9e75]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1d9e75] inline-block"></span>Online
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">Sering Terputus</td>
                    </tr>
                    <tr style="border-bottom: 0.5px solid #f0f5f3;">
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">DEV-006</td>
                        <td class="px-4 py-2.5 text-xs">
                            <span class="flex items-center gap-1.5 text-[#1d9e75]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1d9e75] inline-block"></span>Online
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">Tidak Stabil</td>
                    </tr>
                    <tr style="border-bottom: 0.5px solid #f0f5f3;">
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">DEV-007</td>
                        <td class="px-4 py-2.5 text-xs">
                            <span class="flex items-center gap-1.5 text-[#1d9e75]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#1d9e75] inline-block"></span>Online
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">Sering Terputus</td>
                    </tr>
                    <tr style="border-bottom: 0.5px solid #f0f5f3;">
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">DEV-008</td>
                        <td class="px-4 py-2.5 text-xs">
                            <span class="flex items-center gap-1.5 text-[#e24b4a]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#e24b4a] inline-block"></span>Offline
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">Sering Terputus</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">DEV-009</td>
                        <td class="px-4 py-2.5 text-xs">
                            <span class="flex items-center gap-1.5 text-[#e24b4a]">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#e24b4a] inline-block"></span>Offline
                            </span>
                        </td>
                        <td class="px-4 py-2.5 text-xs text-[#3d5c54]">Gangguan Jaringan</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Log Aktivitas --}}
        <div class="bg-white rounded-xl overflow-hidden" style="border: 0.5px solid rgba(0,83,63,0.12);">
            <div class="px-4 py-3.5" style="border-bottom: 0.5px solid #e8f0ee;">
                <p class="text-sm font-medium text-[rgb(0,62,48)]">Daftar Log Aktivitas Terbaru</p>
            </div>
            <div>
                <div class="flex items-start gap-2.5 px-4 py-2.5" style="border-bottom: 0.5px solid #f0f5f3;">
                    <span class="w-2 h-2 rounded-full mt-1 flex-shrink-0 bg-[#1d9e75]"></span>
                    <span class="text-[11px] text-[#8aab9f] w-8 flex-shrink-0">10.30</span>
                    <span class="text-xs text-[#3d5c54]">Admin Ave menambahkan alat DEV-010</span>
                </div>
                <div class="flex items-start gap-2.5 px-4 py-2.5" style="border-bottom: 0.5px solid #f0f5f3;">
                    <span class="w-2 h-2 rounded-full mt-1 flex-shrink-0 bg-[#1d9e75]"></span>
                    <span class="text-[11px] text-[#8aab9f] w-8 flex-shrink-0">10.50</span>
                    <span class="text-xs text-[#3d5c54]">Perawat Budi Login</span>
                </div>
                <div class="flex items-start gap-2.5 px-4 py-2.5" style="border-bottom: 0.5px solid #f0f5f3;">
                    <span class="w-2 h-2 rounded-full mt-1 flex-shrink-0 bg-[#e24b4a]"></span>
                    <span class="text-[11px] text-[#8aab9f] w-8 flex-shrink-0">10.59</span>
                    <span class="text-xs text-[#3d5c54]">Alat DEV-014 Terputus</span>
                </div>
                <div class="flex items-start gap-2.5 px-4 py-2.5">
                    <span class="w-2 h-2 rounded-full mt-1 flex-shrink-0 bg-[#1d9e75]"></span>
                    <span class="text-[11px] text-[#8aab9f] w-8 flex-shrink-0">11.18</span>
                    <span class="text-xs text-[#3d5c54]">Dr. Andi mengubah detail DEV-013</span>
                </div>
            </div>
        </div>

    </div>

</main>
@endsection