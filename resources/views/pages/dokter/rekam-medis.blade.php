@extends('layouts.app')
@section('title', 'SATS - Rekam Medis')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)] min-h-screen">
        <h1 class="text-3xl font-bold text-[rgb(0,62,48)] mb-6">Rekam Medis</h1>

        @if($rekamMedis->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-1">Belum ada rekam medis</h3>
                <p class="text-sm text-gray-500">Rekam medis akan muncul setelah sesi monitoring selesai dan data pasien diinput.</p>
            </div>
        @else
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-[rgba(0,62,48,0.05)] border-b border-gray-200">
                                <th class="text-left px-4 py-3 font-semibold text-[rgb(0,62,48)]">No. RM</th>
                                <th class="text-left px-4 py-3 font-semibold text-[rgb(0,62,48)]">Nama Pasien</th>
                                <th class="text-left px-4 py-3 font-semibold text-[rgb(0,62,48)]">Nakes</th>
                                <th class="text-left px-4 py-3 font-semibold text-[rgb(0,62,48)]">Perangkat</th>
                                <th class="text-left px-4 py-3 font-semibold text-[rgb(0,62,48)]">Tgl Selesai</th>
                                <th class="text-center px-4 py-3 font-semibold text-[rgb(0,62,48)]">Avg HR</th>
                                <th class="text-center px-4 py-3 font-semibold text-[rgb(0,62,48)]">Avg SpO2</th>
                                <th class="text-center px-4 py-3 font-semibold text-[rgb(0,62,48)]">Avg Suhu</th>
                                <th class="text-center px-4 py-3 font-semibold text-[rgb(0,62,48)]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($rekamMedis as $item)
                                @php
                                    $s = $item['session'];
                                    $st = $item['stats'];
                                @endphp
                                <tr class="hover:bg-[rgba(0,62,48,0.02)] transition">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $s->medical_record_number }}</td>
                                    <td class="px-4 py-3">{{ $s->patient?->nama ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $s->creator?->formatted_name ?? '-' }}</td>
                                    <td class="px-4 py-3">{{ $s->device_id }}</td>
                                    <td class="px-4 py-3">{{ $s->ended_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ ($st['avg_heart_rate'] ?? 0) > 100 || ($st['avg_heart_rate'] ?? 0) < 60 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                            {{ $st['avg_heart_rate'] ?? '-' }} bpm
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ ($st['avg_spo2'] ?? 100) < 95 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                            {{ $st['avg_spo2'] ?? '-' }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="{{ ($st['avg_temperature'] ?? 37) > 37.5 || ($st['avg_temperature'] ?? 37) < 36 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                            {{ $st['avg_temperature'] ?? '-' }}°C
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('dokter.rekam-medis.show', $s->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-xs font-medium rounded-md transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-3 text-xs text-gray-500">Menampilkan {{ $rekamMedis->count() }} rekam medis</p>
        @endif
    </main>
@endsection
