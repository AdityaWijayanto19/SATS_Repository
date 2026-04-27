@extends('layouts.auth')

@section('content')
    <div class="pt-3 flex items-center justify-center bg-[rgb(251, 242, 238)]">
        <div class="flex w-full max-w-5xl min-h-50 rounded-2xl overflow-hidden shadow-sm border border-gray-100">

            {{-- Kiri: Image Slider --}}
            <div class="relative flex-1 bg-[#00a884] overflow-hidden" id="slider">
                <button onclick="changeSlide(-1)" class="nav-btn left-4">&#8249;</button>
                <button onclick="changeSlide(1)" class="nav-btn right-4">&#8250;</button>

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
                        <div class="w-full h-full rounded-l-xl bg-white/10 flex items-center justify-center text-8xl">
                            <div class="relative w-full h-full rounded-l-xl overflow-hidden">
                                <img src="{{ asset($slide['image']) }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/30"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 z-10">
                                    <h2 class="text-white text-3xl font-medium">
                                        {{ $slide['title'] }}
                                    </h2>
                                    <p class="text-white/80 text-xl mt-2 leading-relaxed">
                                        {{ $slide['sub'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Gradient overlay --}}
                <div class="absolute bottom-0 inset-x-0 h-34 bg-gradient-to-t from-[#007a60]/50"></div>
                {{-- Dots --}}
                <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    @foreach($slides as $i => $slide)
                    <button onclick="goSlide({{ $i }})"
                        class="dot h-2 rounded-full bg-white/40 transition-all {{ $i === 0 ? 'w-6 bg-white' : 'w-2' }}">
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Kanan Form Login --}}
            <div class="w-[400px] bg-white flex flex-col justify-center px-10 py-12 border-l border-gray-100">
                {{-- Logo --}}
                <div class="flex items-center gap-3 mb-8">
                    <span class="text-5xl font-medium text-gray-800">Login</span>
                </div>

                <h1 class="text-2xl font-medium text-gray-800">Selamat datang kembali</h1>
                <p class="text-sm text-gray-500 mt-1 mb-8">Masuk ke akun kamu untuk melanjutkan</p>

                <form action="{{ route('login.process') }}" method="POST">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="email" placeholder="nama@email.com"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password" placeholder="••••••••"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    </div>

                    <div class="text-right mb-6">
                    <a href="{{ route('password.forgot') }}" class="text-sm text-[#00a884] hover:cursor-pointer hover:underline">Lupa password?</a>
                    </div>

                    <button type="submit"
                    class="w-full h-11 bg-[#00a884] hover:bg-[#008f70] hover:cursor-pointer text-white rounded-lg text-sm font-medium transition">
                    Masuk
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    Belum punya akun?
                    <a class="text-[#00a884] hover:cursor-pointer hover:underline">Hubungi superadmin</a>
                </p>
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
                    dots[cur].classList.remove('w-6', 'bg-white');
                    dots[cur].classList.add('w-2', 'bg-white/40');

                    cur = n;

                    slides[cur].classList.replace('opacity-0', 'opacity-100');
                    dots[cur].classList.remove('w-2', 'bg-white/40');
                    dots[cur].classList.add('w-6', 'bg-white');
                }

                function changeSlide(dir) {
                    clearInterval(timer);
                    goSlide((cur + dir + slides.length) % slides.length);
                    timer = setInterval(() => changeSlide(1), 4000);
                }

                window.goSlide = goSlide;
                window.changeSlide = changeSlide;

            });
        </script>
    @endpush
@endsection