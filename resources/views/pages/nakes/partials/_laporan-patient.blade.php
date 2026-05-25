<!-- Identitas Pasien -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
    <h2 class="text-base font-semibold text-[rgb(0,62,48)] text-center mb-4">
        Laporan Medis Pasien: {{ $session->medical_record_number }}
        @if($patient) — {{ $patient->nama }} @endif
    </h2>
    @if($patient)
        <div class="grid grid-cols-2 gap-x-8 gap-y-1 text-sm text-gray-700">
            <div>
                <p><span class="font-semibold">Nama Lengkap</span> : {{ $patient->nama }}</p>
                <p><span class="font-semibold">NIK</span> : {{ $patient->nik ?? '-' }}</p>
                <p><span class="font-semibold">Umur</span> : {{ $patient->umur ?? '-' }} tahun</p>
                <p><span class="font-semibold">Jenis Kelamin</span> : {{ $patient->jenis_kelamin == 'L' ? 'Laki-laki' : ($patient->jenis_kelamin == 'P' ? 'Perempuan' : '-') }}</p>
            </div>
            <div>
                <p><span class="font-semibold">Penyakit/Alergi</span> : {{ $patient->penyakit_alergi ?? '-' }}</p>
                <p class="mt-1"><span class="font-semibold">Catatan Tambahan</span> : {{ $patient->catatan_tambahan ?? '-' }}</p>
            </div>
        </div>
    @else
        <div class="text-center">
            <p class="text-sm text-gray-500 italic mb-3">Data pasien belum diinput.</p>
            <button onclick="window.openPatientModal && window.openPatientModal()"
                class="px-4 py-2 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-sm font-medium rounded-lg transition cursor-pointer">
                Input Data Pasien
            </button>
        </div>
    @endif
</div>
