@extends('layouts.app')
@section('title', 'SATS Monitoring - Inbox')

@section('content')
<div class="min-h-full p-8" style="background: rgba(230,238,236,0.5);" x-data="inboxPage()" x-init="init()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Inbox</h1>
            <p class="text-sm text-gray-500 mt-1">Pesan dan laporan dari pengguna</p>
        </div>
        <span x-show="unreadCount > 0" x-transition
            class="flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium bg-red-50 text-red-700">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <span x-text="unreadCount + ' pesan baru'"></span>
        </span>
    </div>

    {{-- Notifikasi Realtime --}}
    <div x-show="newReportNotif" x-transition
        class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
            <span class="text-sm font-medium text-green-700" x-text="'Pesan baru dari ' + (newReportData?.full_name || '') + '! Kategori: ' + (newReportData?.category_label || '')"></span>
        </div>
        <button @click="newReportNotif = false" class="text-green-500 hover:text-green-700 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Filter & Search --}}
    <div class="bg-white rounded-xl border border-gray-200 mb-4">
        <form method="GET" action="{{ route('superadmin.inbox') }}" class="px-6 py-4 flex flex-wrap items-end gap-4">
            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama atau email..."
                    class="w-full h-9 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
            </div>

            {{-- Filter Kategori --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Kategori</label>
                <select name="category" class="h-9 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] bg-white">
                    <option value="">Semua</option>
                    <option value="kendala_perangkat" {{ ($filters['category'] ?? '') === 'kendala_perangkat' ? 'selected' : '' }}>Kendala Perangkat</option>
                    <option value="kendala_aplikasi" {{ ($filters['category'] ?? '') === 'kendala_aplikasi' ? 'selected' : '' }}>Kendala Aplikasi</option>
                    <option value="request_akun" {{ ($filters['category'] ?? '') === 'request_akun' ? 'selected' : '' }}>Request Akun</option>
                    <option value="lainnya" {{ ($filters['category'] ?? '') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>

            {{-- Filter Urgensi --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Urgensi</label>
                <select name="urgency" class="h-9 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] bg-white">
                    <option value="">Semua</option>
                    <option value="rendah" {{ ($filters['urgency'] ?? '') === 'rendah' ? 'selected' : '' }}>Rendah</option>
                    <option value="sedang" {{ ($filters['urgency'] ?? '') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="darurat" {{ ($filters['urgency'] ?? '') === 'darurat' ? 'selected' : '' }}>Darurat</option>
                </select>
            </div>

            {{-- Filter Status --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="h-9 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] bg-white">
                    <option value="">Semua</option>
                    <option value="baru" {{ ($filters['status'] ?? '') === 'baru' ? 'selected' : '' }}>Baru</option>
                    <option value="diproses" {{ ($filters['status'] ?? '') === 'diproses' ? 'selected' : '' }}>Dalam Proses</option>
                    <option value="selesai" {{ ($filters['status'] ?? '') === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>

            {{-- Tombol Filter --}}
            <button type="submit" class="h-9 px-4 rounded-lg text-sm font-medium text-white cursor-pointer transition-all duration-150 hover:opacity-90" style="background: rgb(0,83,63);">
                Filter
            </button>

            {{-- Reset --}}
            @if(!empty($filters['search']) || !empty($filters['category']) || !empty($filters['urgency']) || !empty($filters['status']))
                <a href="{{ route('superadmin.inbox') }}" class="h-9 px-4 rounded-lg text-sm font-medium text-gray-600 border border-gray-200 hover:bg-gray-50 flex items-center cursor-pointer transition">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Tabel Inbox --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Daftar Pesan</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pesan dan laporan dari pengguna sistem</p>
            </div>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Total: {{ $reports->total() }} pesan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 font-semibold text-gray-600">Waktu</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Nama</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Kategori</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Urgensi</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-3 font-semibold text-gray-600 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="inbox-table-body" class="divide-y divide-gray-100">
                    @forelse($reports as $report)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-gray-500 text-xs whitespace-nowrap">
                                {{ $report->created_at->format('d M Y') }}<br>
                                <span class="text-gray-400">{{ $report->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="font-medium text-gray-700">{{ $report->full_name }}</div>
                                <div class="text-xs text-gray-400">{{ $report->email }}</div>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $categoryColors = [
                                        'kendala_perangkat' => 'text-blue-700 bg-blue-50',
                                        'kendala_aplikasi' => 'text-purple-700 bg-purple-50',
                                        'request_akun' => 'text-emerald-700 bg-emerald-50',
                                        'lainnya' => 'text-gray-700 bg-gray-100',
                                    ];
                                @endphp
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $categoryColors[$report->category] ?? 'text-gray-700 bg-gray-100' }}">
                                    {{ $report->category_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $urgencyColors = [
                                        'rendah' => 'text-gray-600 bg-gray-100',
                                        'sedang' => 'text-amber-700 bg-amber-50',
                                        'darurat' => 'text-red-700 bg-red-50',
                                    ];
                                @endphp
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ $urgencyColors[$report->urgency] ?? 'text-gray-700 bg-gray-100' }}">
                                    {{ $report->urgency_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @php
                                    $statusColors = [
                                        'baru' => 'text-blue-700 bg-blue-50',
                                        'diproses' => 'text-amber-700 bg-amber-50',
                                        'selesai' => 'text-emerald-700 bg-emerald-50',
                                    ];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full {{ $statusColors[$report->status] ?? 'text-gray-700 bg-gray-100' }}">
                                    @if($report->status === 'baru')
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                                    @endif
                                    {{ $report->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="showDetail({{ $report->id }})"
                                        class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">
                                        Detail
                                    </button>
                                    <button @click="showDeleteConfirm({{ $report->id }})"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-400">Belum ada pesan masuk</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($reports->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $reports->links() }}
            </div>
        @endif
    </div>

    {{-- ============================================ --}}
    {{-- MODAL: Detail Report                        --}}
    {{-- ============================================ --}}
    <div x-show="showDetailModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" @click="showDetailModal = false"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Detail Pesan</h3>
                    <p class="text-xs text-gray-500 mt-0.5" x-text="'ID: #' + detail.id"></p>
                </div>
                <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 hover:cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-4 space-y-4">
                {{-- Info Pengirim --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-400">Nama</p>
                        <p class="text-sm font-medium text-gray-700" x-text="detail.full_name"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Email</p>
                        <p class="text-sm text-gray-700" x-text="detail.email"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">No. HP</p>
                        <p class="text-sm text-gray-700" x-text="detail.phone || '-'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Waktu</p>
                        <p class="text-sm text-gray-700" x-text="detail.created_at"></p>
                    </div>
                </div>

                {{-- Badge --}}
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="getCategoryClass(detail.category)" x-text="detail.category_label"></span>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="getUrgencyClass(detail.urgency)" x-text="detail.urgency_label"></span>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full" :class="getStatusClass(detail.status)" x-text="detail.status_label"></span>
                </div>

                {{-- Conditional Info --}}
                <template x-if="detail.category === 'kendala_perangkat' && detail.device_id">
                    <div>
                        <p class="text-xs text-gray-400">ID Perangkat</p>
                        <p class="text-sm font-mono text-gray-700" x-text="detail.device_id"></p>
                    </div>
                </template>

                <template x-if="detail.category === 'request_akun'">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-400">Role Diminta</p>
                            <p class="text-sm font-medium text-gray-700" x-text="detail.role_requested === 'nakes' ? 'Nakes (Perawat)' : 'Dokter'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Instansi</p>
                            <p class="text-sm text-gray-700" x-text="detail.institution || '-'"></p>
                        </div>
                    </div>
                </template>

                {{-- Detail --}}
                <div>
                    <p class="text-xs text-gray-400 mb-1">Detail Kendala</p>
                    <div class="p-3 bg-gray-50 rounded-lg text-sm text-gray-700 whitespace-pre-wrap" x-text="detail.detail"></div>
                </div>

                {{-- Attachment --}}
                <template x-if="detail.attachment_url">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Bukti Lampiran</p>
                        <a :href="detail.attachment_url" target="_blank" class="block">
                            <img :src="detail.attachment_url" class="w-full max-h-48 object-contain rounded-lg border border-gray-200" />
                        </a>
                    </div>
                </template>

                {{-- Update Status --}}
                <div class="border-t border-gray-100 pt-4">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Ubah Status</label>
                    <div class="flex gap-2">
                        <button @click="updateStatus('baru')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors cursor-pointer"
                            :class="detail.status === 'baru' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500 hover:bg-blue-50'">
                            Baru
                        </button>
                        <button @click="updateStatus('diproses')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors cursor-pointer"
                            :class="detail.status === 'diproses' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500 hover:bg-amber-50'">
                            Dalam Proses
                        </button>
                        <button @click="updateStatus('selesai')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors cursor-pointer"
                            :class="detail.status === 'selesai' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:bg-emerald-50'">
                            Selesai
                        </button>
                    </div>
                </div>

                {{-- Catatan Admin --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Catatan Admin</label>
                    <textarea x-model="adminNotes" rows="2" placeholder="Catatan internal (tidak terlihat user)..."
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition resize-none"></textarea>
                    <button @click="saveNotes()" class="mt-2 px-3 py-1.5 rounded-lg text-xs font-medium text-white cursor-pointer transition-all hover:opacity-90" style="background: rgb(0,83,63);">
                        Simpan Catatan
                    </button>
                </div>

                {{-- Shortcut Buat Akun (untuk request_akun) --}}
                <template x-if="detail.category === 'request_akun'">
                    <div class="border-t border-gray-100 pt-4">
                        <a :href="'{{ url('/superadmin/manajemen-user') }}?prefill_nama=' + encodeURIComponent(detail.full_name) + '&prefill_email=' + encodeURIComponent(detail.email) + '&prefill_role=' + (detail.role_requested || '')"
                            class="flex items-center justify-center gap-2 w-full h-10 bg-[#00a884] hover:bg-[#008f70] text-white rounded-lg text-sm font-medium cursor-pointer transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            <span>Buat Akun untuk <span x-text="detail.full_name"></span></span>
                        </a>
                    </div>
                </template>
            </div>
        </div>
    </div>

    {{-- ============================================ --}}
    {{-- MODAL: Konfirmasi Hapus                     --}}
    {{-- ============================================ --}}
    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/40" @click="showDeleteModal = false"></div>

        {{-- Modal Content --}}
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="px-6 py-5 text-center">
                {{-- Icon --}}
                <div class="mx-auto w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Hapus Pesan?</h3>
                <p class="text-sm text-gray-500 mb-6">Pesan ini akan dihapus secara permanen dan tidak dapat dikembalikan.</p>

                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 h-10 border border-gray-200 rounded-lg text-sm text-gray-600 font-medium hover:bg-gray-50 cursor-pointer transition">
                        Batal
                    </button>
                    <button @click="confirmDelete()" class="flex-1 h-10 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium cursor-pointer transition">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Badge color maps (dipakai untuk render row baru via JS)
    const CATEGORY_COLORS = {
        'kendala_perangkat': 'text-blue-700 bg-blue-50',
        'kendala_aplikasi': 'text-purple-700 bg-purple-50',
        'request_akun': 'text-emerald-700 bg-emerald-50',
        'lainnya': 'text-gray-700 bg-gray-100',
    };
    const URGENCY_COLORS = {
        'rendah': 'text-gray-600 bg-gray-100',
        'sedang': 'text-amber-700 bg-amber-50',
        'darurat': 'text-red-700 bg-red-50',
    };
    const STATUS_COLORS = {
        'baru': 'text-blue-700 bg-blue-50',
        'diproses': 'text-amber-700 bg-amber-50',
        'selesai': 'text-emerald-700 bg-emerald-50',
    };

    function inboxPage() {
        return {
            showDetailModal: false,
            showDeleteModal: false,
            deleteTargetId: null,
            detail: {},
            adminNotes: '',
            unreadCount: {{ $unreadCount }},
            newReportNotif: false,
            newReportData: null,

            init() {
                window._inboxRef = this;

                // Guard: cegah subscribe ganda (Alpine double-init issue)
                if (window._inboxEchoBound) return;
                window._inboxEchoBound = true;

                this.subscribeToInbox();
            },

            // ==========================================
            // WebSocket Listener
            // ==========================================
            subscribeToInbox() {
                if (!window.Echo) return;

                window.Echo.private('superadmin.dashboard')
                    .listen('.support.report.created', (e) => {
                        const ref = window._inboxRef;
                        if (!ref) return;

                        // Update badge counter
                        ref.unreadCount++;

                        // Tampilkan notifikasi banner
                        ref.newReportData = e;
                        ref.newReportNotif = true;

                        // Sembunyikan notifikasi setelah 8 detik
                        setTimeout(() => { ref.newReportNotif = false; }, 8000);

                        // Insert row baru di top tabel
                        ref.insertNewRow(e);
                    });
            },

            // ==========================================
            // Insert Row Baru ke Tabel
            // ==========================================
            insertNewRow(e) {
                const tbody = document.getElementById('inbox-table-body');
                if (!tbody) return;

                // Hapus "Belum ada pesan masuk" jika ada
                const emptyRow = tbody.querySelector('td[colspan]');
                if (emptyRow) emptyRow.parentElement.remove();

                const catClass = CATEGORY_COLORS[e.category] || 'text-gray-700 bg-gray-100';
                const urgClass = URGENCY_COLORS[e.urgency] || 'text-gray-700 bg-gray-100';
                const statClass = STATUS_COLORS[e.status] || 'text-gray-700 bg-gray-100';

                const row = document.createElement('tr');
                row.className = 'hover:bg-gray-50 transition-colors bg-green-50/50';
                row.innerHTML = `
                    <td class="px-6 py-3 text-gray-500 text-xs whitespace-nowrap">
                        ${e.created_at}<br>
                        <span class="text-gray-400">${e.created_at_time}</span>
                    </td>
                    <td class="px-6 py-3">
                        <div class="font-medium text-gray-700">${this.escapeHtml(e.full_name)}</div>
                        <div class="text-xs text-gray-400">${this.escapeHtml(e.email)}</div>
                    </td>
                    <td class="px-6 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full ${catClass}">${this.escapeHtml(e.category_label)}</span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="text-xs font-medium px-2.5 py-1 rounded-full ${urgClass}">${this.escapeHtml(e.urgency_label)}</span>
                    </td>
                    <td class="px-6 py-3">
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full ${statClass}">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></span>
                            ${this.escapeHtml(e.status_label)}
                        </span>
                    </td>
                    <td class="px-6 py-3">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="window._inboxRef && window._inboxRef.showDetail(${e.id})"
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">
                                Detail
                            </button>
                            <button onclick="window._inboxRef && window._inboxRef.showDeleteConfirm(${e.id})"
                                class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">
                                Hapus
                            </button>
                        </div>
                    </td>
                `;

                // Insert di paling atas
                tbody.insertBefore(row, tbody.firstChild);

                // Hilangkan highlight setelah 3 detik
                setTimeout(() => {
                    row.classList.remove('bg-green-50/50');
                }, 3000);
            },

            // ==========================================
            // Helper: Escape HTML (prevent XSS)
            // ==========================================
            escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            },

            // ==========================================
            // Detail Report (AJAX)
            // ==========================================
            async showDetail(reportId) {
                try {
                    const response = await fetch(`/superadmin/inbox/${reportId}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.detail = data.data;
                        this.adminNotes = data.data.admin_notes || '';
                        this.showDetailModal = true;
                    }
                } catch (error) {
                    console.error('Error fetching detail:', error);
                }
            },

            // ==========================================
            // Update Status
            // ==========================================
            async updateStatus(status) {
                try {
                    const response = await fetch(`/superadmin/inbox/${this.detail.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ status }),
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.detail.status = status;
                        if (status === 'diproses' || status === 'selesai') {
                            this.unreadCount = Math.max(0, this.unreadCount - 1);
                        }
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error updating status:', error);
                }
            },

            // ==========================================
            // Simpan Catatan Admin
            // ==========================================
            async saveNotes() {
                try {
                    const response = await fetch(`/superadmin/inbox/${this.detail.id}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ admin_notes: this.adminNotes }),
                    });
                    const data = await response.json();
                    if (data.success) {
                        alert('Catatan berhasil disimpan.');
                    }
                } catch (error) {
                    console.error('Error saving notes:', error);
                }
            },

            // ==========================================
            // Hapus Report (Custom Modal)
            // ==========================================
            showDeleteConfirm(reportId) {
                this.deleteTargetId = reportId;
                this.showDeleteModal = true;
            },

            async confirmDelete() {
                if (!this.deleteTargetId) return;

                try {
                    const response = await fetch(`/superadmin/inbox/${this.deleteTargetId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                    });
                    const data = await response.json();
                    if (data.success) {
                        this.showDeleteModal = false;
                        this.deleteTargetId = null;
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error deleting report:', error);
                }
            },

            // ==========================================
            // Badge Color Helpers
            // ==========================================
            getCategoryClass(category) {
                return CATEGORY_COLORS[category] || 'text-gray-700 bg-gray-100';
            },

            getUrgencyClass(urgency) {
                return URGENCY_COLORS[urgency] || 'text-gray-700 bg-gray-100';
            },

            getStatusClass(status) {
                return STATUS_COLORS[status] || 'text-gray-700 bg-gray-100';
            },
        };
    }
</script>
@endpush
@endsection
