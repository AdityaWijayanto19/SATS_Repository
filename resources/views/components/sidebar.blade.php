<aside class="w-48 flex-shrink-0 flex flex-col py-5" style="background: rgb(0,83,63);">
    <nav class="flex flex-col gap-0.5">
        <a
           class="px-5 py-2.5 text-sm transition-all border-l-2
                  {{ request()->routeIs('dashboard') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white/90 hover:bg-white/[0.06]' }}">
            Dashboard
        </a>
        <a
           class="px-5 py-2.5 text-sm transition-all border-l-2
                  {{ request()->routeIs('alat.*') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white/90 hover:bg-white/[0.06]' }}">
            Manajemen Alat
        </a>
        <a
           class="px-5 py-2.5 text-sm transition-all border-l-2
                  {{ request()->routeIs('user.*') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white/90 hover:bg-white/[0.06]' }}">
            Manajemen User
        </a>
        <a
           class="px-5 py-2.5 text-sm transition-all border-l-2
                  {{ request()->routeIs('laporan.*') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white/90 hover:bg-white/[0.06]' }}">
            Laporan
        </a>

        <div class="mx-5 my-3" style="border-top: 0.5px solid rgba(255,255,255,0.12);"></div>

        <a 
           class="px-5 py-2.5 text-sm transition-all border-l-2
                  {{ request()->routeIs('settings') ? 'text-white border-[#7de0c0] bg-white/10' : 'text-white/60 border-transparent hover:text-white/90 hover:bg-white/[0.06]' }}">
            Settings
        </a>
    </nav>
</aside>