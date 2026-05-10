@extends('layouts.app')

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
            {{-- TODO: Ganti dengan data real dari backend --}}
            <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">Total: 5 pengguna</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-6 py-3 font-semibold text-gray-600 w-12">No</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Nama Lengkap</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Peran</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Status</th>
                        <th class="px-6 py-3 font-semibold text-gray-600">Email</th>
                        <th class="px-6 py-3 font-semibold text-gray-600 text-center">Aksi</th>
                    </tr>
                </thead>
                {{-- TODO: Ganti dengan data real dari backend (loop dari database) --}}
                <tbody class="divide-y divide-gray-100">
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-gray-500">1</td>
                        <td class="px-6 py-3 font-medium text-gray-700">Super Admin</td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-purple-700 bg-purple-50 px-2.5 py-1 rounded-full">Super Admin</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">admin@sats.id</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center">
                                <button @click="showDetail(0)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-gray-500">2</td>
                        <td class="px-6 py-3 font-medium text-gray-700">Dr. Andi</td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Dokter</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">andi@sats.id</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center">
                                <button @click="showDetail(1)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-gray-500">3</td>
                        <td class="px-6 py-3 font-medium text-gray-700">Suster Rina</td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">Perawat</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">rina@sats.id</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center">
                                <button @click="showDetail(2)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-gray-500">4</td>
                        <td class="px-6 py-3 font-medium text-gray-700">Dr. Budi</td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Dokter</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">budi@sats.id</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center">
                                <button @click="showDetail(3)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                            </div>
                        </td>
                    </tr>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-3 text-gray-500">5</td>
                        <td class="px-6 py-3 font-medium text-gray-700">Suster Dewi</td>
                        <td class="px-6 py-3">
                            <span class="text-xs font-medium text-pink-700 bg-pink-50 px-2.5 py-1 rounded-full">Perawat</span>
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Aktif
                            </span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">dewi@sats.id</td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-center">
                                <button @click="showDetail(4)" class="text-blue-600 hover:text-blue-800 text-xs font-medium bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-md transition-colors cursor-pointer">Detail</button>
                            </div>
                        </td>
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

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Detail Pengguna</h3>
                <button @click="showDetailModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            {{-- TODO: Ganti dengan data real dari backend --}}
            <div class="px-6 py-5 space-y-4" x-show="selectedUser">
                {{-- Avatar & Nama --}}
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl font-bold flex-shrink-0"
                        :class="{
                            'bg-purple-500': selectedUser?.role === 'Super Admin',
                            'bg-blue-500': selectedUser?.role === 'Dokter',
                            'bg-pink-500': selectedUser?.role === 'Perawat'
                        }"
                        x-text="selectedUser?.nama.charAt(0)"></div>
                    <div>
                        <h4 class="text-lg font-semibold text-gray-800" x-text="selectedUser?.nama"></h4>
                        <p class="text-sm text-gray-500" x-text="selectedUser?.email"></p>
                    </div>
                </div>

                {{-- Grid Info --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Nomor ID</p>
                        <p class="text-sm font-mono font-medium text-gray-700" x-text="selectedUser?.nomorId"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Peran</p>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                            :class="{
                                'text-purple-700 bg-purple-100': selectedUser?.role === 'Super Admin',
                                'text-blue-700 bg-blue-100': selectedUser?.role === 'Dokter',
                                'text-pink-700 bg-pink-100': selectedUser?.role === 'Perawat'
                            }"
                            x-text="selectedUser?.role"></span>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Status</p>
                        <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span x-text="selectedUser?.status"></span>
                        </span>
                    </div>
                    <div class="bg-gray-50 rounded-lg px-4 py-3">
                        <p class="text-xs text-gray-400 mb-1">Bergabung</p>
                        <p class="text-sm font-medium text-gray-700" x-text="selectedUser?.bergabung"></p>
                    </div>
                </div>

                {{-- Kontak --}}
                <div class="bg-gray-50 rounded-lg px-4 py-3">
                    <p class="text-xs text-gray-400 mb-1">No. Telepon</p>
                    <p class="text-sm text-gray-700" x-text="selectedUser?.telepon"></p>
                </div>
            </div>

            {{-- Footer --}}
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

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-lg font-semibold" style="color: rgb(0,62,48);">Tambah User Baru</h3>
                <button @click="showTambahModal = false" class="text-gray-400 hover:text-gray-600 cursor-pointer transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            {{-- TODO: Tambahkan action form ke backend (POST route) --}}
            <form @submit.prevent="tambahUser()" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nomor ID</label>
                    <input type="text" x-model="form.nomorId" placeholder="Contoh: USR-006"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                    <input type="text" x-model="form.nama" placeholder="Contoh: Dr. Sari"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Peran</label>
                    <select x-model="form.role"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all bg-white"
                        required>
                        <option value="" disabled selected>Pilih peran</option>
                        <option value="superadmin">Super Admin</option>
                        <option value="dokter">Dokter</option>
                        <option value="perawat">Perawat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                    <input type="email" x-model="form.email" placeholder="Contoh: sari@sats.id"
                        class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[rgba(0,83,63,0.3)] focus:border-[rgb(0,83,63)] transition-all"
                        required>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" @click="showTambahModal = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-all cursor-pointer hover:opacity-90"
                        style="background: rgb(0,83,63);">
                        Tambahkan
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>

@push('scripts')
<script>
    // TODO: Ganti data dummy dengan fetch dari backend
    const users = [
        { nomorId: 'USR-001', nama: 'Super Admin', role: 'Super Admin', status: 'Aktif', email: 'admin@sats.id', telepon: '0812-0000-0001', bergabung: '01 Jan 2026' },
        { nomorId: 'USR-002', nama: 'Dr. Andi', role: 'Dokter', status: 'Aktif', email: 'andi@sats.id', telepon: '0812-0000-0002', bergabung: '05 Jan 2026' },
        { nomorId: 'USR-003', nama: 'Suster Rina', role: 'Perawat', status: 'Aktif', email: 'rina@sats.id', telepon: '0812-0000-0003', bergabung: '10 Jan 2026' },
        { nomorId: 'USR-004', nama: 'Dr. Budi', role: 'Dokter', status: 'Aktif', email: 'budi@sats.id', telepon: '0812-0000-0004', bergabung: '15 Jan 2026' },
        { nomorId: 'USR-005', nama: 'Suster Dewi', role: 'Perawat', status: 'Aktif', email: 'dewi@sats.id', telepon: '0812-0000-0005', bergabung: '20 Jan 2026' },
    ];

    function manajemenUser() {
        return {
            showTambahModal: false,
            showDetailModal: false,
            selectedUser: null,
            form: {
                nomorId: '',
                nama: '',
                role: '',
                email: ''
            },
            showDetail(index) {
                this.selectedUser = users[index];
                this.showDetailModal = true;
            },
            tambahUser() {
                // TODO: Kirim data ke backend (POST /superadmin/manajemen-user)
                console.log('Tambah user:', this.form);
                this.showTambahModal = false;
                this.form = { nomorId: '', nama: '', role: '', email: '' };
                // TODO: Refresh tabel setelah berhasil tambah
            }
        }
    }
</script>
@endpush
@endsection
