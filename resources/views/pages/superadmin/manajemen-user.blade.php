@extends('layouts.app')
@section('title', 'SATS Monitoring - Manajemen User')

@section('content')
<div class="min-h-full p-8" style="background: rgba(230,238,236,0.5);" x-data="manajemenUser()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Manajemen User</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola akun pengguna sistem SATS</p>
        </div>
        <button @click="showTambahModal = true"
            class="flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium text-white cursor-pointer transition-all duration-150 hover:opacity-90"
            style="background: rgb(0,83,63);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/>
            </svg>
            Tambah User
        </button>
    </div>

    {{-- Tabel Pengguna --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Daftar Pengguna</h2>
                <p class="text-xs text-gray-400 mt-0.5">Personil medis dan admin yang terdaftar</p>
            </div>
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Total: <span x-text="users.length"></span> pengguna</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 font-semibold text-gray-600 w-12">No</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Nama Lengkap</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Peran</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Email</th>
                        <th class="px-6 py-3 font-semibold text-gray-600 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="(user, index) in users" :key="user.id">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-gray-500" x-text="index + 1"></td>
                            <td class="px-6 py-3 font-medium text-gray-700" x-text="user.nama"></td>
                            <td class="px-6 py-3">
                                <span class="text-xs font-medium px-2.5 py-1 rounded-full"
                                    :class="{
                                        'text-purple-700 bg-purple-50': user.role === 'superadmin',
                                        'text-blue-700 bg-blue-50': user.role === 'dokter',
                                        'text-pink-700 bg-pink-50': user.role === 'nakes'
                                    }"
                                    x-text="user.role_label"></span>
                            </td>
                            <td class="px-6 py-3 text-gray-500" x-text="user.email"></td>
                            <td class="px-6 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="showDetail(index)"
                                        class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">
                                        Detail
                                    </button>
                                    <button @click="showDeleteConfirm(user)"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="users.length === 0">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">Belum ada pengguna terdaftar</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Detail User --}}
    <div x-show="showDetailModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">

        <div class="absolute inset-0 bg-black/40" @click="showDetailModal = false"></div>

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Detail Pengguna</h3>
                <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-4" x-show="selectedUser">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
                        :class="{
                            'bg-purple-500': selectedUser?.role === 'superadmin',
                            'bg-blue-500': selectedUser?.role === 'dokter',
                            'bg-pink-500': selectedUser?.role === 'nakes'
                        }"
                        x-text="selectedUser?.nama.charAt(0)"></div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800" x-text="selectedUser?.nama"></h4>
                        <p class="text-sm text-gray-500" x-text="selectedUser?.email"></p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Nomor ID</p>
                        <p class="text-sm font-mono font-medium text-gray-700" x-text="'#' + selectedUser?.id"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Peran</p>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                            :class="{
                                'text-purple-700 bg-purple-100': selectedUser?.role === 'superadmin',
                                'text-blue-700 bg-blue-100': selectedUser?.role === 'dokter',
                                'text-pink-700 bg-pink-100': selectedUser?.role === 'nakes'
                            }"
                            x-text="selectedUser?.role_label"></span>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3 col-span-2">
                        <p class="text-xs text-gray-400 mb-1">Bergabung</p>
                        <p class="text-sm font-medium text-gray-700" x-text="selectedUser?.bergabung"></p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-100">
                <button @click="showDetailModal = false"
                    class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Tambah User --}}
    <div x-show="showTambahModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">

        <div class="absolute inset-0 bg-black/40" @click="showTambahModal = false"></div>

        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Tambah User Baru</h3>
                <button @click="showTambahModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            {{-- Error Summary --}}
            <div x-show="errorMessage" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                <span x-text="errorMessage"></span>
            </div>

            <form @submit.prevent="tambahUser()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" x-model="form.name" placeholder="Contoh: dr. Sari"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                    <template x-if="errors.name"><p class="text-xs text-red-500 mt-1" x-text="errors.name[0]"></p></template>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Peran</label>
                    <select x-model="form.role"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all bg-white"
                        required>
                        <option value="" disabled selected>Pilih peran</option>
                        <option value="superadmin">Super Admin</option>
                        <option value="dokter">Dokter</option>
                        <option value="nakes">Perawat (Nakes)</option>
                    </select>
                    <template x-if="errors.role"><p class="text-xs text-red-500 mt-1" x-text="errors.role[0]"></p></template>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" x-model="form.email" placeholder="Contoh: sari@sats.id"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                    <template x-if="errors.email"><p class="text-xs text-red-500 mt-1" x-text="errors.email[0]"></p></template>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" x-model="form.password" placeholder="Minimal 8 karakter"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                    <template x-if="errors.password"><p class="text-xs text-red-500 mt-1" x-text="errors.password[0]"></p></template>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showTambahModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="loading"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-all cursor-pointer hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                        style="background: rgb(0,83,63);">
                        <span x-show="!loading">Tambahkan</span>
                        <span x-show="loading">Memproses...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi Hapus --}}
    <div x-show="showDeleteModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-black/40" @click="showDeleteModal = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div class="px-6 py-5 text-center">
                <div class="mx-auto w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mb-4">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-1">Hapus User?</h3>
                <p class="text-sm text-gray-500 mb-1">User <span class="font-medium text-gray-700" x-text="deleteTarget?.nama"></span></p>
                <p class="text-sm text-gray-500 mb-6">Akun ini akan dihapus secara permanen.</p>
                <div class="flex gap-3">
                    <button @click="showDeleteModal = false" class="flex-1 h-10 border border-gray-200 rounded-lg text-sm text-gray-600 font-medium hover:bg-gray-50 cursor-pointer transition">Batal</button>
                    <button @click="confirmDelete()" class="flex-1 h-10 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium cursor-pointer transition">Ya, Hapus</button>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    const initialUsers = @json($users);

    function manajemenUser() {
        return {
            users: initialUsers,
            showTambahModal: false,
            showDetailModal: false,
            showDeleteModal: false,
            selectedUser: null,
            deleteTarget: null,
            loading: false,
            errorMessage: '',
            errors: {},
            form: {
                name: '',
                role: '',
                email: '',
                password: ''
            },

            showDetail(index) {
                this.selectedUser = this.users[index];
                this.showDetailModal = true;
            },

            showDeleteConfirm(user) {
                this.deleteTarget = user;
                this.showDeleteModal = true;
            },

            async tambahUser() {
                this.loading = true;
                this.errorMessage = '';
                this.errors = {};

                try {
                    const response = await fetch('{{ route("superadmin.manajemen-user.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(this.form),
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Tambahkan user baru ke list
                        this.users.push(result.data);
                        this.showTambahModal = false;
                        this.form = { name: '', role: '', email: '', password: '' };
                    } else if (result.errors) {
                        this.errors = result.errors;
                        this.errorMessage = 'Silakan periksa kembali form Anda.';
                    } else {
                        this.errorMessage = result.message || 'Gagal menambahkan user.';
                    }
                } catch (error) {
                    this.errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                } finally {
                    this.loading = false;
                }
            },

            async confirmDelete() {
                if (!this.deleteTarget) return;

                try {
                    const response = await fetch(`/superadmin/manajemen-user/${this.deleteTarget.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.users = this.users.filter(u => u.id !== this.deleteTarget.id);
                        this.showDeleteModal = false;
                        this.deleteTarget = null;
                    } else {
                        alert(result.message || 'Gagal menghapus user.');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                }
            },
        }
    }
</script>
@endpush
@endsection
