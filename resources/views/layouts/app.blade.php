<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SATS</title>
    @vite('resources/css/app.css')
</head>
<body class="h-screen bg-[rgb(251, 242, 238)]">

    @include('components.navbar')

    <main class="p-6">
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>