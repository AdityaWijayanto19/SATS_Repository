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
        <!-- Profile -->
        <div class="flex items-center gap-2">
            <!-- Icon profil -->
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-5 h-5 text-white opacity-70"
                viewBox="0 0 24 24"
                fill="currentColor">
                <path fill-rule="evenodd"
                    d="M12 2a5 5 0 1 1 0 10A5 5 0 0 1 12 2zm0 12c-5.33 0-8 2.67-8 4v1a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-1c0-1.33-2.67-4-8-4z"
                    clip-rule="evenodd" />
            </svg>

            <!-- Name -->
            <span class="text-md font-medium text-white/70">
                {{ auth()->user()->name ?? 'Dr. User' }}
            </span>
        </div>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="text-[rgba(0,75,57,0.51)] text-sm font-medium bg-red-200 px-4 py-1.5 rounded-md transition cursor-pointer hover:bg-red-500 hover:text-white">
                Logout
            </button>
        </form>

    </div>

</nav>