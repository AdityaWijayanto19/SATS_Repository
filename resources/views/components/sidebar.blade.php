<aside class="w-60 flex-shrink-0 flex flex-col py-5" style="background: rgb(0,83,63);">
    <nav class="flex flex-col gap-0.5">

        @if(auth()->user()->role === 'superadmin')

        {{-- ===================== SUPERADMIN MENU ===================== --}}

        <!-- Dashboard -->
        <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('superadmin.dashboard') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            Dashboard
        </a>

        <!-- Manajemen Alat -->
        <a href="{{ route('superadmin.manajemen-alat') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('superadmin.manajemen-alat') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96c-.5-.36-1.04-.67-1.62-.94l-.36-2.54A.484.484 0 0 0 14 2h-4a.484.484 0 0 0-.48.41l-.36 2.54c-.58.27-1.13.58-1.62.94l-2.39-.96a.48.48 0 0 0-.59.22L2.74 8.87a.48.48 0 0 0 .12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.36 1.04.67 1.62.94l.36 2.54c.05.28.3.48.48.48h4c.28 0 .46-.2.48-.41l.36-2.54c.58-.27 1.13-.58 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.49.49 0 0 0-.12-.61l-2.01-1.58zM12 15.6a3.6 3.6 0 1 1 0-7.2 3.6 3.6 0 0 1 0 7.2z"/>
            </svg>
            Manajemen Alat
        </a>

        <!-- Manajemen User -->
        <a href="{{ route('superadmin.manajemen-user') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('superadmin.manajemen-user') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
            </svg>
            Manajemen User
        </a>

        <!-- Laporan -->
        <a href="{{ route('superadmin.laporan') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('superadmin.laporan') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM8 13h8v1.5H8V13zm0 3h8v1.5H8V16zm0-6h3v1.5H8V10z"/>
            </svg>
            Laporan
        </a>

        @elseif(auth()->user()->role === 'dokter')

        {{-- ===================== DOKTER MENU ===================== --}}

        <!-- Dashboard -->
        <a href="{{ route('dokter.dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('dokter.dashboard') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            Dashboard
        </a>

        <!-- Input Data Pasien -->
        <a href="{{ route('dokter.input-data-pasien') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('dokter.input-data-pasien') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 3a1 1 0 0 1 1 1v3h3a1 1 0 0 1 0 2h-3v3a1 1 0 0 1-2 0v-3H8a1 1 0 0 1 0-2h3V7a1 1 0 0 1 1-1z"/>
            </svg>
            Input Data Pasien
        </a>

        <!-- Laporan -->
        <a href="{{ route('dokter.laporan') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('dokter.laporan') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM8 13h8v1.5H8V13zm0 3h8v1.5H8V16zm0-6h3v1.5H8V10z"/>
            </svg>
            Laporan
        </a>

        @else

        {{-- ===================== NAKES MENU ===================== --}}

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('dashboard') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>
            </svg>
            Dashboard
        </a>

        <!-- Input Data Pasien -->
        <a href="{{ route('input-data-pasien') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('input-data-pasien') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 3a1 1 0 0 1 1 1v3h3a1 1 0 0 1 0 2h-3v3a1 1 0 0 1-2 0v-3H8a1 1 0 0 1 0-2h3V7a1 1 0 0 1 1-1z"/>
            </svg>
            Input Data Pasien
        </a>

        <!-- Laporan -->
        <a href="{{ route('laporan.index') }}" class="flex items-center gap-2 px-5 py-2.5 text-md font-medium cursor-pointer transition-all duration-150 border-l-2 rounded-r-lg
                  {{ request()->routeIs('laporan.index') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white hover:border-white/30 hover:bg-white/[0.06]' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM8 13h8v1.5H8V13zm0 3h8v1.5H8V16zm0-6h3v1.5H8V10z"/>
            </svg>
            Laporan
        </a>

        @endif

    </nav>
</aside>
