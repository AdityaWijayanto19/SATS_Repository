@extends('layouts.auth')

@section('content')
    <div class="flex items-center justify-center bg-white px-4">
        <div class="flex w-full mt-9 max-w-5xl min-h-[550px] rounded-2xl overflow-hidden shadow-2xl shadow-gray-200 border border-gray-100">

            {{-- Image Slider --}}
            <div class="relative flex-1 bg-[#00a884] overflow-hidden hidden md:block" id="slider">
                @php
                    $slides = [
                        ['image' => 'assets/ambulance_1.jpg', 'title' => 'Smart Ambulance Telemedicine System', 'sub' => 'Monitoring kondisi pasien secara real-time selama perjalanan ambulans.'],
                        ['image' => 'assets/ambulance_2.jpg', 'title' => 'Deteksi Kondisi Cepat', 'sub' => 'Klasifikasi otomatis dari normal, warning, hingga critical secara instan.'],
                        ['image' => 'assets/dokter.jpg', 'title' => 'Prediksi Berbasis AI', 'sub' => 'Memprediksi perubahan kondisi pasien sebelum terlambat.'],
                        ['image' => 'assets/vital_sign.jpg', 'title' => 'Terhubung ke Rumah Sakit', 'sub' => 'Data dikirim real-time untuk mendukung keputusan medis lebih cepat.']
                    ];
                @endphp

                @foreach($slides as $i => $slide)
                    <div class="slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 {{ $i === 0 ? 'opacity-100' : 'opacity-0' }}">
                        <img src="{{ asset($slide['image']) }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40"></div>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-12 z-10">
                            <h2 class="text-white text-3xl font-bold leading-tight">{{ $slide['title'] }}</h2>
                            <p class="text-white/90 text-lg mt-4 leading-relaxed">{{ $slide['sub'] }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="absolute bottom-10 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    @foreach($slides as $i => $slide)
                    <button onclick="goSlide({{ $i }})" class="dot h-1.5 rounded-full bg-white/40 transition-all {{ $i === 0 ? 'w-8 bg-white' : 'w-2' }}"></button>
                    @endforeach
                </div>
            </div>

            {{-- Form Forgot Password --}}
            <div class="w-full md:w-[450px] bg-white flex flex-col justify-center px-10 py-12 relative">
                
                <!-- Step Indicator -->
                <div class="mb-10">
                    <div class="flex items-center justify-center gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-[#00a884] text-white rounded-full flex items-center justify-center text-xs font-bold ring-4 ring-[#00a884]/10">1</div>
                            <span class="text-[10px] uppercase tracking-wider font-bold mt-2 text-[#00a884]">Email</span>
                        </div>
                        <div class="h-[2px] w-12 bg-gray-100 -mt-5"></div>
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center text-xs font-bold">2</div>
                            <span class="text-[10px] uppercase tracking-wider font-bold mt-2 text-gray-400">Reset</span>
                        </div>
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-gray-800">Lupa Password?</h1>
                <p class="text-sm text-gray-500 mt-2 mb-8 font-light leading-relaxed">Masukkan email terdaftar kamu. Kami akan mengirimkan tautan untuk mengatur ulang password.</p>

                {{-- Alert Success/Error --}}
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm flex items-center gap-3">
                        <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('password.email') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@email.com"
                            class="w-full h-12 px-4 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-4 focus:ring-[#00a884]/10 transition-all" />
                        @error('email')
                            <p class="text-xs text-red-500 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                        class="w-full h-12 bg-[#00a884] hover:bg-[#008f70] text-white rounded-xl text-sm font-bold shadow-lg shadow-[#00a884]/20 transition-all active:scale-[0.98]">
                        Kirim Link Reset
                    </button>
                </form>

                <div class="text-center mt-8">
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-[#00a884] hover:underline flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        Kembali ke Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let cur = 0;
                const slides = document.querySelectorAll('.slide');
                const dots = document.querySelectorAll('.dot');
                let timer = setInterval(() => changeSlide(1), 4000);

                function goSlide(n) {
                    slides[cur].classList.replace('opacity-100', 'opacity-0');
                    dots[cur].classList.remove('w-8', 'bg-white');
                    dots[cur].classList.add('w-2', 'bg-white/40');
                    cur = n;
                    slides[cur].classList.replace('opacity-0', 'opacity-100');
                    dots[cur].classList.remove('w-2', 'bg-white/40');
                    dots[cur].classList.add('w-8', 'bg-white');
                }

                function changeSlide(dir) {
                    clearInterval(timer);
                    goSlide((cur + dir + slides.length) % slides.length);
                    timer = setInterval(() => changeSlide(1), 4000);
                }
                window.goSlide = goSlide;
            });
        </script>
    @endpush
@endsection