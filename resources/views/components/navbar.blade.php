<nav class="flex items-center justify-between px-5 h-18 flex-shrink-0" style="background: rgb(0,75,58);">

    <!-- Left: Logo -->
    <div class="flex items-center gap-2">
        <img src="{{ asset('assets/logo.png') }}" class="w-10 h-10 object-contain">
        <div>
            <p class="text-white font-semibold text-base">SATS</p>
            <p class="text-white/70 font-medium text-xs">Smart Ambulance Telemedicine System</p>
        </div>
    </div>

    <div class="flex items-center gap-8">
        <!-- Profile Dropdown -->
        <x-profile-dropdown />
    </div>

</nav>
