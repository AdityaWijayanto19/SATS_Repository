@extends('layouts.app')
@section('title', 'SATS - Edit Profil')

@section('content')
<div class="min-h-full p-8" style="background: rgba(230,238,236,0.5);"
     x-data="profileEditor()">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Edit Profil</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola informasi profil dan foto Anda</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="grid grid-cols-3 gap-6">
        @csrf
        @method('PUT')

        {{-- Left: Form Fields (2 kolom) --}}
        <div class="col-span-2 space-y-6">

            {{-- Card: Informasi Dasar --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Informasi Dasar</h2>
                </div>
                <div class="px-6 py-5 space-y-4">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors @error('name') border-red-400 @enderror">
                        @error('name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Role (read-only) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                        <input type="text" value="{{ ucfirst($user->role) }}" disabled
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
                    </div>
                </div>
            </div>

            {{-- Card: Ubah Password --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Ubah Password</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Kosongkan jika tidak ingin mengubah password</p>
                </div>
                <div class="px-6 py-5 space-y-4">
                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password Baru</label>
                        <input type="password" id="password" name="password"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors @error('password') border-red-400 @enderror"
                               placeholder="Minimal 8 karakter">
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password Confirmation --}}
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-colors"
                               placeholder="Ulangi password baru">
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-2.5 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer"
                    style="background: rgb(0,75,58);"
                    onmouseover="this.style.background='rgb(0,62,48)'"
                    onmouseout="this.style.background='rgb(0,75,58)'">
                    Simpan Perubahan
                </button>
            </div>
        </div>

        {{-- Right: Photo Selection (1 kolom) --}}
        <div class="col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden sticky top-8">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Foto Profil</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Pilih avatar default sesuai role Anda</p>
                </div>
                <div class="px-6 py-5">

                    {{-- Preview --}}
                    <div class="flex justify-center mb-5">
                        <div class="w-24 h-24 rounded-full overflow-hidden ring-4 ring-gray-100 bg-gray-100 flex items-center justify-center">
                            <template x-if="selectedPhoto">
                                <img :src="'/' + selectedPhoto" alt="Preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!selectedPhoto">
                                <span class="text-2xl font-bold text-gray-400" x-text="'{{ strtoupper(substr($user->name, 0, 2)) }}'"></span>
                            </template>
                        </div>
                    </div>

                    {{-- Avatar Grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($avatars as $index => $avatar)
                            <label class="cursor-pointer group">
                                <input type="radio" name="photo" value="{{ $avatar }}"
                                       class="sr-only peer"
                                       {{ $user->photo === $avatar ? 'checked' : '' }}
                                       x-model="selectedPhoto">
                                <div class="rounded-lg overflow-hidden border-2 border-gray-200 peer-checked:border-emerald-500 peer-checked:ring-2 peer-checked:ring-emerald-200 transition-all group-hover:border-gray-300">
                                    <img src="{{ asset($avatar) }}" alt="Avatar {{ $index + 1 }}" class="w-full aspect-square object-cover">
                                </div>
                            </label>
                        @endforeach
                    </div>

                    {{-- Remove photo --}}
                    <label class="cursor-pointer mt-3 block">
                        <input type="radio" name="photo" value="" class="sr-only" x-model="selectedPhoto">
                        <div class="text-center py-2 text-xs text-gray-400 hover:text-gray-600 transition-colors"
                             :class="selectedPhoto === '' ? 'text-emerald-600 font-medium' : ''">
                            Hapus foto (gunakan inisial)
                        </div>
                    </label>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
function profileEditor() {
    return {
        selectedPhoto: '{{ $user->photo ?? '' }}',
    };
}
</script>
@endpush
@endsection
