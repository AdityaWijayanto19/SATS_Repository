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

                    {{-- WiFi Name --}}
                    <div class="mb-5">
                        <label for="wifi_name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama WiFi</label>
                        <input type="text" id="wifi_name" name="wifi_name" value="{{ old('wifi_name') }}"
                            placeholder="Nama jaringan WiFi"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                            required>
                        @error('wifi_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- WiFi Password --}}
                    <div class="mb-5">
                        <label for="wifi_password" class="block text-sm font-medium text-gray-700 mb-1.5">Password WiFi</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" id="wifi_password" name="wifi_password" value="{{ old('wifi_password') }}"
                                placeholder="Password WiFi"
                                class="w-full px-3.5 py-2.5 pr-10 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                                required>
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                <svg x-show="!showPassword" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showPassword" class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('wifi_password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

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
                    showPassword: false,
                    loading: false,
                }
            }
        </script>
    @endpush
@endsection
