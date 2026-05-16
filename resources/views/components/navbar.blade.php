<nav class="flex items-center justify-between px-10 h-18" style="background: rgb(0,75,58);">
    
    <!-- Logo -->
    <div class="flex items-center gap-2">
        <img src="{{ asset('assets/logo.png') }}" class="w-12 h-12 object-contain">
        <div class="gap">
            <p class="text-white font-medium text-lg">SATS</p>
            <p class="text-white font-medium text-sm ">Smart Ambulance Telemedicine System</p>
        </div>
    </div>

    <div class="flex items-center gap-8">
        <!-- Profile Dropdown -->
        <x-profile-dropdown />
    </div>

</nav>