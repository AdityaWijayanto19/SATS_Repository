@extends('layouts.app')
@section('title', 'SATS - Manajemen Rekam Medis')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)] min-h-screen"
          x-data="{ showDeleteModal: false, deleteTarget: null, deleteUrl: '' }">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-[rgb(0,62,48)]">Manajemen Rekam Medis</h1>
                <p class="text-sm text-gray-500 mt-1">Kelola semua rekam medis pasien</p>
            </div>
        </div>

        {{-- Search & Filter --}}
        <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-4">
            <div class="grid grid-cols-4 gap-3">
                {{-- Search --}}
                <div class="col-span-1">
                    <label class="text-xs font-medium text-gray-500 mb-1 block">Cari</label>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Nama pasien / No. RM..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent outline-none">
                </div>

                {{-- Filter Dokter --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 mb-1 block">Dokter</label>
                    <select name="dokter_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent outline-none">
                        <option value="">Semua Dokter</option>
                        @foreach($dokters as $d)
                            <option value="{{ $d->id }}" {{ $filterDokter == $d->id ? 'selected' : '' }}>{{ $d->formatted_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Nakes --}}
                <div>
                    <label class="text-xs font-medium text-gray-500 mb-1 block">Nakes</label>
                    <select name="nakes_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent outline-none">
                        <option value="">Semua Nakes</option>
                        @foreach($nakesList as $n)
                            <option value="{{ $n->id }}" {{ $filterNakes == $n->id ? 'selected' : '' }}>{{ $n->formatted_name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-[rgb(0,62,48)] text-white text-sm font-medium rounded-lg hover:bg-[rgb(0,80,60)] transition cursor-pointer">
                        Filter
                    </button>
                    <a href="{{ route('superadmin.rekam-medis') }}"
                        class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition">
                        Reset
                    </a>
                </div>
            </div>
        </form>

        {{-- Table --}}
        @if($rekamMedis->isEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-1">Tidak ada rekam medis</h3>
                <p class="text-sm text-gray-500">
                    @if($search || $filterDokter || $filterNakes)
                        Tidak ditemukan rekam medis yang sesuai dengan filter.
                    @else
                        Rekam medis akan muncul setelah sesi monitoring selesai.
                    @endif
                </p>
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
                                <th class="text-left px-4 py-3 font-semibold text-[rgb(0,62,48)]">Dokter</th>
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
                                    <td class="px-4 py-3">{{ $s->dokter?->formatted_name ?? '-' }}</td>
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
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="{{ route('superadmin.rekam-medis.show', $s->id) }}"
                                               class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-xs font-medium rounded-md transition">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </a>
                                            <button @click="showDeleteModal = true; deleteTarget = '{{ $s->medical_record_number }}'; deleteUrl = '{{ route('superadmin.rekam-medis.destroy', $s->id) }}'"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-100 hover:bg-red-200 text-red-700 text-xs font-medium rounded-md transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Menampilkan {{ $rekamMedis->firstItem() }}-{{ $rekamMedis->lastItem() }} dari {{ $rekamMedis->total() }} rekam medis
                    </p>
                    <div class="flex gap-1">
                        {{ $rekamMedis->links() }}
                    </div>
                </div>
            </div>
        @endif

        {{-- Delete Confirmation Modal --}}
        <div x-show="showDeleteModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="showDeleteModal = false">
            <div class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md mx-4 border border-gray-200" x-transition>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Hapus Rekam Medis</h3>
                        <p class="text-sm text-gray-500">Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-6">
                    Yakin ingin menghapus rekam medis <strong x-text="deleteTarget"></strong>? Semua data sensor readings terkait juga akan dihapus.
                </p>
                <div class="flex justify-end gap-3 mt-2">
                    <button @click="showDeleteModal = false"
                        class="px-5 py-2.5 text-sm font-medium rounded-lg cursor-pointer"
                        style="background: #f3f4f6; color: #374151; border: 1px solid #d1d5db;">
                        Batal
                    </button>
                    <button @click="
                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                        }).then(r => r.json()).then(d => {
                            if (d.success) { window.location.reload(); }
                            else { alert(d.message || 'Gagal menghapus'); }
                        }).catch(() => alert('Gagal menghapus'))
                    "
                        class="px-5 py-2.5 text-sm font-medium rounded-lg cursor-pointer"
                        style="background: #dc2626; color: #ffffff;">
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </main>
@endsection
