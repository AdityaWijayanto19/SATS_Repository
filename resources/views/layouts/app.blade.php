<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Ambulance Telemedicine System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-screen flex flex-col">

    {{-- Navbar --}}
    @include('components.navbar')

    <div class="flex flex-1 w-full overflow-hidden">

        {{-- Sidebar --}}
        @include('components.sidebar')

        {{-- Content --}}
        <main class="flex-1 w-full overflow-y-auto">
            @yield('content')
        </main>

    </div>

    @stack('scripts')
</body>

</html>
