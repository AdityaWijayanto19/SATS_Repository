@extends('layouts.app')
@section('title', 'SATS Monitoring - Setup Device')

@section('content')
    <main class="flex-1 overflow-y-auto p-6 bg-[rgba(230,238,236,0.5)] flex items-center justify-center" x-data="setupDevice()">

        <div class="w-full max-w-lg">

            {{-- Card --}}
            <div class="bg-white rounded-2xl border border-[rgba(0,83,63,0.1)] shadow-sm p-8">

                {{-- Header --}}
                <div class="flex flex-col items-center mb-8">
                    <div class="w-16 h-16 rounded-2xl bg-[rgba(0,83,63,0.08)] flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-[rgb(0,62,48)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.288 15.038a5.25 5.25 0 017.424 0M5.106 11.856c3.807-3.808 9.98-3.808 13.788 0M1.924 8.674c5.565-5.565 14.587-5.565 20.152 0M12.53 18.22l-.53.53-.53-.53a.75.75 0 011.06 0z" />
                        </svg>
                    </div>
                    <h1 class="text-xl font-medium text-[rgb(0,62,48)]">Konfigurasi Perangkat</h1>
                    <p class="text-sm text-gray-400 mt-1 text-center">Hubungkan perangkat SATS Anda untuk memulai monitoring</p>
                </div>

                {{-- Form --}}
                <form method="POST" action="{{ route('nakes.device-config.store') }}">
                    @csrf

                    {{-- API Key --}}
                    <div class="mb-5">
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-1.5">API Key Perangkat</label>
                        <input type="text" id="api_key" name="api_key" value="{{ old('api_key') }}"
                            placeholder="Masukkan API Key perangkat Anda"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all font-mono"
                            required>
                        @error('api_key')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Info Box --}}
                    <div class="flex items-start gap-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 mb-6">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-xs text-blue-700 leading-relaxed">API Key diberikan oleh admin saat pendaftaran perangkat. Hubungi admin jika Anda belum memiliki API Key.</p>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" :disabled="loading"
                        class="w-full py-2.5 bg-[rgb(0,62,48)] text-white rounded-lg text-sm font-medium hover:opacity-90 disabled:opacity-50 transition-all flex items-center justify-center gap-2">
                        <template x-if="!loading">
                            <span>Konfigurasi Perangkat</span>
                        </template>
                        <template x-if="loading">
                            <span class="flex items-center gap-2">
                                <span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                                Menyimpan...
                            </span>
                        </template>
                    </button>
                </form>

            </div>

        </div>

    </main>

    @push('scripts')
        <script>
            function setupDevice() {
                return {
                    loading: false,
                }
            }
        </script>
    @endpush
@endsection
