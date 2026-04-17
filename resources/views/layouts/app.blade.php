<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Ambulance Telemedicine System</title>
    @vite('resources/css/app.css')
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