{{-- HERO SECTION --}}
<section id="beranda" class="relative min-h-screen flex items-center overflow-hidden">

    {{-- Background Image --}}
    <div class="absolute inset-0">
        <img src="{{ asset('assets/walpaper_ambulance.jpeg') }}" alt="Ambulans SATS"
            class="w-full h-full object-cover">
        {{-- Overlay Gradien Putih dari Kiri --}}
        <div class="absolute inset-0"
            style="background: linear-gradient(90deg, rgba(255,255,255,0.97) 0%, rgba(255,255,255,0.92) 35%, rgba(255,255,255,0.6) 55%, rgba(255,255,255,0) 75%);">
        </div>
    </div>

    {{-- Konten --}}
    <div class="relative z-10 max-w-6xl mx-auto px-10 py-32 w-full">
        <div class="max-w-xl">

            {{-- Badge Lokasi --}}
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full mb-6"
                style="background: rgba(0,83,63,0.08); border: 1px solid rgba(0,83,63,0.15);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none"
                    stroke="rgb(0,83,63)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span class="text-xs font-semibold" style="color: rgb(0,83,63);">Beranda</span>
            </div>

            {{-- Judul Utama --}}
            <h1 class="text-4xl md:text-5xl font-bold leading-tight mb-5" style="color: rgb(0,62,48);">
                Smart Ambulance<br>Telemedicine System
            </h1>

            {{-- Sub-judul --}}
            <p class="text-base md:text-lg leading-relaxed mb-8" style="color: rgba(0,62,48,0.6); max-width: 480px;">
                Solusi pemantauan kondisi vital pasien secara real-time dari ambulans menuju IGD.
                Membantu tenaga medis mengambil keputusan lebih cepat dengan dukungan Edge Machine Learning.
            </p>

            {{-- Tombol CTA --}}
            @guest
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-2 px-7 py-3 text-white font-semibold rounded-xl text-sm transition-all hover:shadow-lg hover:translate-y-[-1px]"
                    style="background: rgb(0,75,58);">
                    Login
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            @else
                @php
                    $dashboardRoute = match(auth()->user()->role) {
                        'superadmin' => route('superadmin.dashboard'),
                        'dokter' => route('dokter.dashboard'),
                        'nakes' => route('dashboard'),
                        default => route('dashboard'),
                    };
                @endphp
                <a href="{{ $dashboardRoute }}"
                    class="inline-flex items-center gap-2 px-7 py-3 text-white font-semibold rounded-xl text-sm transition-all hover:shadow-lg hover:translate-y-[-1px]"
                    style="background: rgb(0,75,58);">
                    Buka Dashboard
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            @endguest

        </div>
    </div>

</section>
