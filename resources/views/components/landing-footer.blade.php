{{-- FOOTER --}}
<footer class="bg-emerald-950 text-white">
    <div class="max-w-7xl mx-auto px-6 py-16">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12" data-reveal-stagger>

            {{-- Kolom Brand --}}
            <div>
                <div class="flex items-center gap-2.5 mb-4">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 object-contain">
                        <div class="gap">
                            <p class="text-white font-medium text-lg">SATS</p>
                            <p class="text-white font-medium text-sm ">Smart Ambulance Telemedicine System</p>
                        </div>
                    </div>
                </div>
                <p class="text-emerald-300 text-sm leading-relaxed max-w-xs">
                    Sistem telemedicine berbasis IoT yang menghubungkan data vital pasien di ambulans dengan dokter di rumah sakit secara real-time.
                </p>
            </div>

            {{-- Kolom Navigasi --}}
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Navigasi</h4>
                <ul class="space-y-2.5">
                    <li>
                        <a href="#beranda" class="text-emerald-300 hover:text-white text-sm transition-colors">Beranda</a>
                    </li>
                    <li>
                        <a href="#tentang" class="text-emerald-300 hover:text-white text-sm transition-colors">Tentang SATS</a>
                    </li>
                    <li>
                        <a href="#fitur" class="text-emerald-300 hover:text-white text-sm transition-colors">Fitur</a>
                    </li>
                    <li>
                        <a href="#cara-kerja" class="text-emerald-300 hover:text-white text-sm transition-colors">Cara Kerja</a>
                    </li>
                </ul>
            </div>

            {{-- Kolom Kontak --}}
            <div>
                <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Kontak</h4>
                <ul class="space-y-2.5">
                    <li class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-emerald-300 text-sm">Universitas Brawijaya, Indonesia</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-emerald-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="text-emerald-300 text-sm">satsinformasi@gmail.com</span>
                    </li>
                </ul>
            </div>

        </div>
    </div>

    {{-- Copyright Bar --}}
    <div class="border-t border-emerald-800">
        <div class="max-w-7xl mx-auto px-6 py-5 flex flex-col md:flex-row items-center justify-between gap-2">
            <p class="text-emerald-400 text-xs">
                &copy; {{ date('Y') }} SATS. All rights reserved.
            </p>
            <p class="text-emerald-500 text-xs">
                Smart Ambulance Telemedicine System
            </p>
        </div>
    </div>
</footer>
