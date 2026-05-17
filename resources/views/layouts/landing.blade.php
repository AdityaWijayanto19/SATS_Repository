<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <title>SATS Website</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white antialiased">

    {{-- Navbar Landing --}}
    @include('components.landing-navbar')

    {{-- Content --}}
    @yield('content')

    {{-- Footer --}}
    @include('components.landing-footer')

</body>
</html>
