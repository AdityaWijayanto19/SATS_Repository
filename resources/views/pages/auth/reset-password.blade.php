@extends('layouts.auth')

@section('content')
    <div class="flex items-center justify-center bg-white px-4">
        <div class="flex w-full max-w-5xl mt-9 min-h-[500px] rounded-2xl overflow-hidden shadow-2xl shadow-gray-200 border border-gray-100">

            {{-- Image Slider --}}
            <div class="relative flex-1 bg-[#00a884] overflow-hidden hidden md:block" id="slider">
                {{-- isi slider --}}
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

            {{-- Form Reset Password --}}
            <div class="w-full md:w-[450px] bg-white flex flex-col justify-center px-10 py-12">
                
                <!-- Step Indicator -->
                <div class="mb-10">
                    <div class="flex items-center justify-center gap-4">
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-[#00a884] text-white rounded-full flex items-center justify-center text-xs font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="text-[10px] uppercase tracking-wider font-bold mt-2 text-[#00a884]">Email</span>
                        </div>
                        <div class="h-[2px] w-12 bg-[#00a884] -mt-5"></div>
                        <div class="flex flex-col items-center">
                            <div class="w-8 h-8 bg-[#00a884] text-white rounded-full flex items-center justify-center text-xs font-bold ring-4 ring-[#00a884]/10">2</div>
                            <span class="text-[10px] uppercase tracking-wider font-bold mt-2 text-[#00a884]">Reset</span>
                        </div>
                    </div>
                </div>

                <h1 class="text-2xl font-bold text-gray-800">Password Baru</h1>
                <p class="text-sm text-gray-500 mt-2 mb-8 font-light leading-relaxed">Keamanan akun Anda penting. Silakan buat password baru yang kuat.</p>

                <form action="{{ route('password.update') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full h-12 px-4 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-4 focus:ring-[#00a884]/10 transition-all" />
                        @error('password')
                            <p class="text-xs text-red-500 mt-2 leading-relaxed">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Ulangi Password</label>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                            class="w-full h-12 px-4 rounded-xl border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-4 focus:ring-[#00a884]/10 transition-all" />
                    </div>

                    <button type="submit"
                        class="w-full h-12 bg-[#00a884] hover:bg-[#008f70] text-white rounded-xl text-sm font-bold shadow-lg shadow-[#00a884]/20 transition-all active:scale-[0.98] mt-2">
                        Perbarui Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Script slider berjalan
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