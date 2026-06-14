@props(['align' => 'right'])

@php
    $user = auth()->user();
    $initials = strtoupper(substr($user->name, 0, 2));
@endphp

<div class="relative" x-data="{ open: false }" @click.outside="open = false">

    {{-- Trigger --}}
    <button @click="open = !open" class="flex items-center gap-2.5 cursor-pointer focus:outline-none">
        {{-- Avatar --}}
        @if(!empty($user->photo))
            <img src="{{ asset($user->photo) }}"
                 alt="{{ $user->formatted_name }}"
                 class="w-9 h-9 rounded-full object-cover ring-2 ring-white/20">
        @else
            <div class="w-9 h-9 rounded-full bg-white/20 flex items-center justify-center ring-2 ring-white/10">
                <span class="text-sm font-bold text-white">{{ $initials }}</span>
            </div>
        @endif

        {{-- Name & Role --}}
        <div class="flex flex-col items-start">
            <span class="text-sm font-semibold text-white leading-tight">{{ $user->formatted_name }}</span>
            <span class="text-[10px] text-white/50 font-medium leading-tight">{{ $user->role === 'nakes' ? 'Ners' : ucfirst($user->role) }}</span>
        </div>

        {{-- Chevron --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white/40 transition-transform duration-200"
             :class="open ? 'rotate-180' : ''"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-1"
         class="absolute {{ $align === 'left' ? 'left-0' : 'right-0' }} mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50"
         style="display: none;">

        {{-- User Info --}}
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-sm font-semibold text-gray-800">{{ $user->formatted_name }}</p>
            <p class="text-xs text-gray-400">{{ $user->role === 'nakes' ? 'Ners' : ucfirst($user->role) }}</p>
        </div>

        {{-- Menu Items --}}
        <div class="py-1">
            {{-- Edit Profile --}}
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit Profile
            </a>

            {{-- Separator --}}
            <div class="my-1 border-t border-gray-100"></div>

            {{-- Logout --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 hover:bg-pink-50 transition-colors cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
