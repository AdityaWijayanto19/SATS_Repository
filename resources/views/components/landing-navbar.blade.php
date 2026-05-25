<nav class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-10 h-18"
    style="background: linear-gradient(135deg, rgba(0,75,58,0.95) 0%, rgba(0,83,63,0.9) 100%); backdrop-filter: blur(8px);">

    {{-- Logo + Identitas --}}
    <a href="{{ url('/') }}" class="flex items-center gap-2.5">
        <!-- Logo -->
        <div class="flex items-center gap-2">
            <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 object-contain">
            <div class="gap">
                <p class="text-white font-medium text-lg">SATS</p>
                <p class="text-white font-medium text-sm ">Smart Ambulance Telemedicine System</p>
            </div>
        </div>        
    </a>

    {{-- Menu Navigasi --}}
    <div class="flex items-center gap-1">

        {{-- Menu Navigasi Utama --}}
        <a href="#beranda"
            class="px-3.5 py-2 text-sm font-semibold text-white/80 hover:text-white rounded-lg transition-colors">
            Beranda
        </a>
        <a href="#tentang"
            class="px-3.5 py-2 text-sm font-semibold text-white/80 hover:text-white rounded-lg transition-colors">
            Tentang SATS
        </a>
        <a href="#fitur"
            class="px-3.5 py-2 text-sm font-semibold text-white/80 hover:text-white rounded-lg transition-colors">
            Fitur
        </a>
        <a href="#cara-kerja"
            class="px-3.5 py-2 text-sm font-semibold text-white/80 hover:text-white rounded-lg transition-colors">
            Cara Kerja
        </a>
        <a href="#faq"
            class="px-3.5 py-2 text-sm font-semibold text-white/80 hover:text-white rounded-lg transition-colors">
            FAQ
        </a>

        {{-- KONDISI USER SUDAH LOGIN --}}
        @auth
            {{-- Menu Dashboard --}}
            @php
                $dashboardRoute = match(auth()->user()->role) {
                    'superadmin' => route('superadmin.dashboard'),
                    'dokter' => route('dokter.dashboard'),
                    'nakes' => route('dashboard'),
                    default => route('dashboard'),
                };
            @endphp
            <a href="{{ $dashboardRoute }}"
                class="px-3.5 py-2 text-sm font-semibold text-white/80 hover:text-white rounded-lg transition-colors">
                Dashboard
            </a>

            {{-- Separator --}}
            <div class="w-px h-6 bg-white/20 mx-2"></div>

            {{-- Profil Dropdown --}}
            <div class="mr-2">
                <x-profile-dropdown />
            </div>

        {{-- KONDISI USER BELUM LOGIN --}}
        @else
            {{-- Tombol Login --}}
            <a href="{{ route('login') }}"
                class="ml-2 px-5 py-1.5 text-sm font-semibold rounded-lg transition-all cursor-pointer"
                style="background: rgba(255,255,255,0.95); color: rgb(0,75,58);"
                onmouseover="this.style.background='#fff'; this.style.transform='scale(1.03)'"
                onmouseout="this.style.background='rgba(255,255,255,0.95)'; this.style.transform='scale(1)'">
                Login
            </a>
        @endauth
    </div>
</nav>
