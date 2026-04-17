<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Ambulance Telemedicine System</title>
    @vite('resources/css/app.css')
</head>
<body class="h-screen bg-[rgb(251, 242, 238)]">
    <!-- CONTENT -->
    <main class="p-6">
        @yield('content')
    </main>
    
    @stack('scripts')
</body>
</html>