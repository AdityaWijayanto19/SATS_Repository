@extends('layouts.app')

@section('content')
    <main class="h-screen flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)]">
        <!-- Judul -->
        <h1 class="text-3xl font-bold text-[rgb(0,62,48)] mb-6">Input Data Pasien</h1>

        <!-- Card Form -->
        <div class="bg-white rounded-xl shadow-sm border border-[rgba(0,62,48,0.1)] p-8 max-w-5xl">

            @if(session('success'))
                <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-800 rounded-lg text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" class="space-y-5">
                @csrf

                <!-- No. Rekam Medis -->
                <div>
                    <label for="no_rekam_medis" class="block text-sm font-medium text-[rgb(0,62,48)] mb-1">
                        No. Rekam Medis
                    </label>
                    <input
                        type="text"
                        id="no_rekam_medis"
                        name="no_rekam_medis"
                        value="{{ old('no_rekam_medis') }}"
                        placeholder="Masukkan nomor rekam medis"
                        class="w-full px-3 py-2 bg-[rgba(0,100,70,0.07)] border border-[rgba(0,62,48,0.2)] rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent transition"
                    />
                    @error('no_rekam_medis')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nama Lengkap & NIK -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="nama_lengkap" class="block text-sm font-medium text-[rgb(0,62,48)] mb-1">
                            Nama Lengkap Pasien
                        </label>
                        <input
                            type="text"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            value="{{ old('nama_lengkap') }}"
                            placeholder="Masukkan nama lengkap"
                            class="w-full px-3 py-2 bg-[rgba(0,100,70,0.07)] border border-[rgba(0,62,48,0.2)] rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent transition"
                        />
                        @error('nama_lengkap')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nik" class="block text-sm font-medium text-[rgb(0,62,48)] mb-1">
                            NIK Pasien
                        </label>
                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            value="{{ old('nik') }}"
                            placeholder="16 digit NIK"
                            maxlength="16"
                            class="w-full px-3 py-2 bg-[rgba(0,100,70,0.07)] border border-[rgba(0,62,48,0.2)] rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent transition"
                        />
                        @error('nik')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tanggal Lahir & Jenis Kelamin -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="tanggal_lahir" class="block text-sm font-medium text-[rgb(0,62,48)] mb-1">
                            Tanggal Lahir
                        </label>
                        <input
                            type="date"
                            id="tanggal_lahir"
                            name="tanggal_lahir"
                            value="{{ old('tanggal_lahir') }}"
                            class="w-full px-3 py-2 bg-[rgba(0,100,70,0.07)] border border-[rgba(0,62,48,0.2)] rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent transition"
                        />
                        @error('tanggal_lahir')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="jenis_kelamin" class="block text-sm font-medium text-[rgb(0,62,48)] mb-1">
                            Jenis Kelamin
                        </label>
                        <select
                            id="jenis_kelamin"
                            name="jenis_kelamin"
                            class="w-full px-3 py-2 bg-[rgba(0,100,70,0.07)] border border-[rgba(0,62,48,0.2)] rounded-md text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent transition appearance-none cursor-pointer"
                        >
                            <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih jenis kelamin</option>
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jenis_kelamin')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Penyakit / Alergi -->
                <div>
                    <label for="penyakit_alergi" class="block text-sm font-medium text-[rgb(0,62,48)] mb-1">
                        Penyakit/Alergi
                    </label>
                    <input
                        type="text"
                        id="penyakit_alergi"
                        name="penyakit_alergi"
                        value="{{ old('penyakit_alergi') }}"
                        placeholder="Contoh: Diabetes, Alergi penisilin"
                        class="w-full px-3 py-2 bg-[rgba(0,100,70,0.07)] border border-[rgba(0,62,48,0.2)] rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent transition"
                    />
                    @error('penyakit_alergi')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catatan Tambahan -->
                <div>
                    <label for="catatan_tambahan" class="block text-sm font-medium text-[rgb(0,62,48)] mb-1">
                        Catatan Tambahan
                    </label>
                    <input
                        type="text"
                        id="catatan_tambahan"
                        name="catatan_tambahan"
                        value="{{ old('catatan_tambahan') }}"
                        placeholder="Catatan lain yang perlu diketahui"
                        class="w-full px-3 py-2 bg-[rgba(0,100,70,0.07)] border border-[rgba(0,62,48,0.2)] rounded-md text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[rgb(0,62,48)] focus:border-transparent transition"
                    />
                    @error('catatan_tambahan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tombol Submit -->
                <div class="flex justify-center pt-2">
                    <button
                        type="submit"
                        class="px-10 py-2.5 bg-[rgb(0,62,48)] hover:bg-[rgb(0,80,60)] text-white text-sm font-medium rounded-md transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[rgb(0,62,48)]"
                    >
                        Simpan Data Pasien
                    </button>
                </div>
            </form>
        </div>
    </main>
@endsection