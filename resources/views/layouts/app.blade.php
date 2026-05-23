<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <title>@yield('title', 'SATS Monitoring')</title>

    {{-- Fix FOUC: disable ALL transitions sebelum Alpine aktif --}}
    <style>
        .no-transition * {
            transition: none !important;
        }
    </style>

    {{-- Fix layout shift: tentukan lebar sidebar dari localStorage SEBELUM browser render --}}
    <script>
        (function () {
            // Tambah class no-transition agar tidak ada animasi saat halaman pertama load
            document.documentElement.classList.add('no-transition');

            // Baca state sidebar dari localStorage, fallback berdasarkan lebar layar
            var open = JSON.parse(
                localStorage.getItem('sidebarOpen') ??
                (window.innerWidth >= 1024 ? 'true' : 'false')
            );

            // Set CSS variable --sidebar-width SEBELUM apapun dirender
            // Ini yang mencegah layout shift karena sidebar sudah punya lebar yang benar dari awal
            document.documentElement.style.setProperty(
                '--sidebar-width',
                open ? '15rem' : '4rem'
            );
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body
    class="h-screen flex flex-col overflow-hidden"
    x-data="{
        sidebarOpen: JSON.parse(localStorage.getItem('sidebarOpen') ?? (window.innerWidth >= 1024 ? 'true' : 'false'))
    }"
    @sidebar-toggle.window="
        sidebarOpen = !sidebarOpen;
        localStorage.setItem('sidebarOpen', JSON.stringify(sidebarOpen));
        document.documentElement.style.setProperty('--sidebar-width', sidebarOpen ? '15rem' : '4rem');
    "
    @resize.window="
        if (window.innerWidth < 768 && sidebarOpen) {
            sidebarOpen = false;
            localStorage.setItem('sidebarOpen', 'false');
            document.documentElement.style.setProperty('--sidebar-width', '4rem');
        } else if (window.innerWidth >= 1024 && !sidebarOpen) {
            sidebarOpen = true;
            localStorage.setItem('sidebarOpen', 'true');
            document.documentElement.style.setProperty('--sidebar-width', '15rem');
        }
    "
    x-init="
        $nextTick(() => {
            document.documentElement.classList.remove('no-transition');
        })
    "
>
    {{-- Navbar (full width, top) --}}
    @include('components.navbar')

    {{-- Below navbar: sidebar + content --}}
    <div class="flex flex-1 min-h-0">

        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Content --}}
        <main class="flex-1 min-w-0 overflow-y-auto">
            @yield('content')
        </main>

    </div>

    @stack('scripts')
</body>
</html>