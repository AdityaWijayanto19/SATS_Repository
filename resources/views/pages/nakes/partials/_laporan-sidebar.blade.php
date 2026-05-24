<!-- Info Sesi -->
<div class="bg-white rounded-lg border border-gray-200 shadow-sm p-3 space-y-2">
    <p class="text-xs font-semibold text-[rgb(0,62,48)]">Info Sesi</p>
    <div class="text-xs text-gray-600 space-y-1">
        <p><span class="font-medium">No. RM:</span> {{ $session->medical_record_number }}</p>
        <p><span class="font-medium">Mulai:</span> {{ $session->started_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') }}</p>
        <p><span class="font-medium">Selesai:</span> {{ $session->ended_at?->setTimezone('Asia/Jakarta')->format('d M Y, H:i') ?? '-' }}</p>
        <p><span class="font-medium">Data:</span> {{ $session->total_readings }} pembacaan</p>
    </div>
</div>

<!-- Tombol Unduh PDF -->
<a href="{{ route('laporan.pdf', [
        'session_id' => $session->id,
        'vital_signs' => $vitalSigns ?? ['heart_rate', 'spo2', 'temperature'],
    ]) }}"
   target="_blank"
   class="flex items-center justify-center gap-2 w-full py-2.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-sm font-semibold rounded-lg shadow transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
    </svg>
    Unduh PDF
</a>
