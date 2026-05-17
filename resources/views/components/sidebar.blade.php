<aside
    x-data="{
        hoveredItem: null,
        openSubs: {},
        toggleSub(key) { this.openSubs[key] = !this.openSubs[key]; },
        isSubOpen(key) { return this.openSubs[key] || false; }
    }"
    {{--
        KUNCI FIX LAYOUT SHIFT:
        - style="width: var(--sidebar-width, 4rem)"  → dipakai SEBELUM Alpine aktif.
          Nilainya sudah benar karena --sidebar-width di-set dari localStorage
          via inline script di <head> (sebelum browser render apapun).
        - :style="{ width: sidebarOpen ? '15rem' : '4rem' }" → dipakai SETELAH Alpine aktif.
          Nilainya sama persis, jadi tidak ada perubahan visual = tidak ada shift.
        - Hapus :class="sidebarOpen ? 'w-60' : 'w-16'" karena Tailwind class
          baru aktif setelah Alpine mount, itulah yang menyebabkan flash sebelumnya.
    --}}
    style="width: var(--sidebar-width, 4rem); background: rgb(0,83,63);"
    :style="{ width: sidebarOpen ? '15rem' : '4rem' }"
    class="flex-shrink-0 flex flex-col h-full transition-[width] duration-300 ease-in-out"
>

    {{-- Header --}}
    <div class="flex items-center h-18 pl-5 px-4 flex-shrink-0" :class="sidebarOpen ? 'justify-between' : 'justify-center'">
        {{-- Logo --}}
        <div x-show="sidebarOpen" style="display: none;" class="flex items-center gap-2 overflow-hidden">
            <!-- <img src="{{ asset('assets/logo.png') }}" class="w-10 h-10 object-contain flex-shrink-0"> -->
            <span class="text-white font-semibold text-base whitespace-nowrap">SATS <br>
                <span class="text-xs font-normal">
                    Monitoring Sidebar
                </span>
            </span>
        </div>

        {{-- Toggle Button --}}
        <button
            @click="
                sidebarOpen = !sidebarOpen;
                localStorage.setItem('sidebarOpen', JSON.stringify(sidebarOpen));
                document.documentElement.style.setProperty('--sidebar-width', sidebarOpen ? '15rem' : '4rem');
            "
            class="text-white/70 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors flex-shrink-0 cursor-pointer"
        >
            <svg x-show="sidebarOpen" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            <svg x-show="!sidebarOpen" style="display: none;" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>

    {{-- Separator --}}
    <div class="mx-4 border-t border-white/10"></div>

    {{-- Menu --}}
    <nav class="flex-1 flex flex-col gap-0.5 px-2 py-4 overflow-y-auto overflow-x-hidden">

        {{-- Define menu items per role --}}
        @php
            $role = auth()->user()->role;

            if ($role === 'superadmin') {
                $menuItems = [
                    [
                        'key' => 'dashboard',
                        'label' => 'Dashboard',
                        'route' => route('superadmin.dashboard'),
                        'routeIs' => 'superadmin.dashboard',
                        'icon' => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
                        'children' => [],
                    ],
                    [
                        'key' => 'manajemen-alat',
                        'label' => 'Manajemen Alat',
                        'route' => route('superadmin.manajemen-alat'),
                        'routeIs' => 'superadmin.manajemen-alat',
                        'icon' => '<path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.07-.94l2.03-1.58a.49.49 0 0 0 .12-.61l-1.92-3.32a.49.49 0 0 0-.59-.22l-2.39.96c-.5-.36-1.04-.67-1.62-.94l-.36-2.54A.484.484 0 0 0 14 2h-4a.484.484 0 0 0-.48.41l-.36 2.54c-.58.27-1.13.58-1.62.94l-2.39-.96a.48.48 0 0 0-.59.22L2.74 8.87a.48.48 0 0 0 .12.61l2.03 1.58c-.05.3-.07.62-.07.94s.02.64.07.94l-2.03 1.58a.49.49 0 0 0-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.36 1.04.67 1.62.94l.36 2.54c.05.28.3.48.48.48h4c.28 0 .46-.2.48-.41l.36-2.54c.58-.27 1.13-.58 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32a.49.49 0 0 0-.12-.61l-2.01-1.58zM12 15.6a3.6 3.6 0 1 1 0-7.2 3.6 3.6 0 0 1 0 7.2z"/>',
                        'children' => [],
                    ],
                    [
                        'key' => 'manajemen-user',
                        'label' => 'Manajemen User',
                        'route' => route('superadmin.manajemen-user'),
                        'routeIs' => 'superadmin.manajemen-user',
                        'icon' => '<path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>',
                        'children' => [],
                    ],
                    [
                        'key' => 'laporan',
                        'label' => 'Laporan',
                        'route' => route('superadmin.laporan'),
                        'routeIs' => 'superadmin.laporan',
                        'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM8 13h8v1.5H8V13zm0 3h8v1.5H8V16zm0-6h3v1.5H8V10z"/>',
                        'children' => [],
                    ],
                ];
            } elseif ($role === 'dokter') {
                $menuItems = [
                    [
                        'key' => 'dashboard',
                        'label' => 'Dashboard',
                        'route' => route('dokter.dashboard'),
                        'routeIs' => 'dokter.dashboard',
                        'icon' => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
                        'children' => [],
                    ],
                    [
                        'key' => 'input-data',
                        'label' => 'Input Data Pasien',
                        'route' => route('dokter.input-data-pasien'),
                        'routeIs' => 'dokter.input-data-pasien',
                        'icon' => '<path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 3c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-2 4h4v2h-4v4h-2v-4H7v-2h2V7h2v3z"/>',
                        'children' => [],
                    ],
                    [
                        'key' => 'laporan',
                        'label' => 'Laporan',
                        'route' => route('dokter.laporan'),
                        'routeIs' => 'dokter.laporan',
                        'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM8 13h8v1.5H8V13zm0 3h8v1.5H8V16zm0-6h3v1.5H8V10z"/>',
                        'children' => [],
                    ],
                ];
            } else {
                $menuItems = [
                    [
                        'key' => 'dashboard',
                        'label' => 'Dashboard',
                        'route' => route('dashboard'),
                        'routeIs' => 'dashboard',
                        'icon' => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
                        'children' => [],
                    ],
                    [
                        'key' => 'input-data',
                        'label' => 'Input Data Pasien',
                        'route' => route('input-data-pasien'),
                        'routeIs' => 'input-data-pasien',
                        'icon' => '<path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2zm-7 3c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-2 4h4v2h-4v4h-2v-4H7v-2h2V7h2v3z"/>',
                        'children' => [],
                    ],
                    [
                        'key' => 'laporan',
                        'label' => 'Laporan',
                        'route' => route('laporan.index'),
                        'routeIs' => 'laporan.index',
                        'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 7V3.5L18.5 9H13zM8 13h8v1.5H8V13zm0 3h8v1.5H8V16zm0-6h3v1.5H8V10z"/>',
                        'children' => [],
                    ],
                ];
            }

            // Check if any child route is active (for parent highlighting)
            foreach ($menuItems as &$item) {
                $item['childActive'] = false;
                if (!empty($item['children'])) {
                    foreach ($item['children'] as $child) {
                        if (request()->routeIs($child['routeIs'])) {
                            $item['childActive'] = true;
                            break;
                        }
                    }
                }
            }
            unset($item);
        @endphp

        {{-- Render Menu Items --}}
        @foreach($menuItems as $item)
            @php
                $hasChildren = !empty($item['children']);
                $isActive = request()->routeIs($item['routeIs']) || ($hasChildren && $item['childActive']);
            @endphp

            {{-- Parent Menu Item --}}
            <div
                class="relative"
                @mouseenter="if (!sidebarOpen) hoveredItem = '{{ $item['key'] }}'"
                @mouseleave="if (!sidebarOpen) hoveredItem = null"
            >
                <a
                    href="{{ $hasChildren ? 'javascript:void(0)' : $item['route'] }}"
                    @if($hasChildren) @click="if (sidebarOpen) toggleSub('{{ $item['key'] }}')" @endif
                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-all duration-150
                        {{ $isActive
                            ? 'bg-white/15 text-white'
                            : 'text-white/60 hover:text-white hover:bg-white/[0.06]'
                        }}"
                    :class="!sidebarOpen && 'justify-center'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                        {!! $item['icon'] !!}
                    </svg>
                    <span
                        x-show="sidebarOpen"
                        style="display: none;"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="text-sm font-medium whitespace-nowrap overflow-hidden"
                    >{{ $item['label'] }}</span>
                    @if($hasChildren)
                        <svg
                            x-show="sidebarOpen"
                            style="display: none;"
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-4 h-4 ml-auto flex-shrink-0 transition-transform duration-200"
                            :class="isSubOpen('{{ $item['key'] }}') ? 'rotate-180' : ''"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        >
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    @endif
                </a>

                {{-- Sub-menu: Expanded State (inline) --}}
                @if($hasChildren)
                    <div
                        x-show="sidebarOpen && isSubOpen('{{ $item['key'] }}')"
                        style="display: none;"
                        x-collapse
                        class="ml-5 pl-3 border-l border-white/10 flex flex-col gap-0.5 mt-0.5 mb-1"
                    >
                        @foreach($item['children'] as $child)
                            @php $childActive = request()->routeIs($child['routeIs']); @endphp
                            <a
                                href="{{ $child['route'] }}"
                                class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm cursor-pointer transition-all duration-150
                                    {{ $childActive
                                        ? 'bg-white/15 text-white font-medium'
                                        : 'text-white/50 hover:text-white hover:bg-white/[0.06]'
                                    }}"
                            >
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Sub-menu: Collapsed State (flyout) --}}
                @if($hasChildren)
                    <div
                        x-show="!sidebarOpen && hoveredItem === '{{ $item['key'] }}'"
                        style="display: none; background: rgb(0,83,63);"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-x-1"
                        x-transition:enter-end="opacity-100 translate-x-0"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-x-0"
                        x-transition:leave-end="opacity-0 translate-x-1"
                        class="absolute left-full top-0 ml-2 w-48 rounded-lg shadow-xl py-2 z-50"
                    >
                        <p class="px-4 py-1.5 text-xs font-semibold text-white/40 uppercase tracking-wider">{{ $item['label'] }}</p>
                        @foreach($item['children'] as $child)
                            @php $childActive = request()->routeIs($child['routeIs']); @endphp
                            <a
                                href="{{ $child['route'] }}"
                                class="flex items-center px-4 py-2 text-sm cursor-pointer transition-colors
                                    {{ $childActive
                                        ? 'bg-white/15 text-white font-medium'
                                        : 'text-white/60 hover:text-white hover:bg-white/[0.06]'
                                    }}"
                            >
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

    </nav>

    {{-- Separator --}}
    <div class="mx-4 border-t border-white/10"></div>

    {{-- Sign Out --}}
    <div class="px-2 py-4 flex-shrink-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg cursor-pointer transition-all duration-150 w-full text-white/60 hover:text-white hover:bg-white/[0.06]"
                :class="!sidebarOpen && 'justify-center'"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span
                    x-show="sidebarOpen"
                    style="display: none;"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="text-sm font-medium whitespace-nowrap"
                >Sign Out</span>
            </button>
        </form>
    </div>

</aside>