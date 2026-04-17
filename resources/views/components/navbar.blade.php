<nav class="flex items-center justify-between px-6 h-14" style="background: rgb(0,75,58);">
    <span class="text-white font-medium text-lg tracking-wide">SATS</span>

    <div class="flex items-center gap-4">
        <span class="text-sm" style="color: rgba(255,255,255,0.7);">{{ auth()->user()->name ?? 'Super Admin' }}</span>
        <form method="POST">
            @csrf
            <button type="submit"
                class="text-white text-xs px-4 py-1.5 rounded-md transition"
                style="background: rgba(255,255,255,0.12); border: 0.5px solid rgba(255,255,255,0.25);">
                Logout
            </button>
        </form>
    </div>
</nav>