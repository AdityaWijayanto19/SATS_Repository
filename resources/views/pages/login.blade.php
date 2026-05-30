@extends('layouts.auth')

@section('content')
    <div class="pt-3 flex items-center justify-center bg-[rgb(251, 242, 238)]" x-data="reportModal()">
        <div class="flex w-full max-w-5xl min-h-50 rounded-2xl overflow-hidden shadow-sm border border-gray-100">

            {{-- Kiri: Image Slider --}}
            <div class="relative flex-1 bg-[#00a884] overflow-hidden" id="slider">
                <button onclick="changeSlide(-1)" class="nav-btn left-4">&#8249;</button>
                <button onclick="changeSlide(1)" class="nav-btn right-4">&#8250;</button>

                @php
                    $slides = [
                        ['image' => 'assets/ambulance_1.jpg', 'title' => 'Smart Ambulance Telemedicine System', 'sub' => 'Monitoring kondisi pasien secara real-time selama perjalanan ambulans.'],
                        ['image' => 'assets/ambulance_2.jpg', 'title' => 'Deteksi Kondisi Cepat', 'sub' => 'Klasifikasi otomatis dari normal, warning, hingga critical secara instan.'],
                        ['image' => 'assets/dokter.jpg', 'title' => 'Prediksi Berbasis AI', 'sub' => 'Memprediksi perubahan kondisi pasien sebelum terlambat.'],
                        ['image' => 'assets/vital_sign.jpg', 'title' => 'Terhubung ke Rumah Sakit', 'sub' => 'Data dikirim real-time untuk mendukung keputusan medis lebih cepat.']
                    ];
                @endphp

                @foreach($slides as $i => $slide)
                    <div class="slide absolute inset-0 flex flex-col items-center justify-center transition-opacity duration-700 {{ $i === 0 ? 'opacity-100' : 'opacity-0' }}">
                        <div class="w-full h-full rounded-l-xl bg-white/10 flex items-center justify-center text-8xl">
                            <div class="relative w-full h-full rounded-l-xl overflow-hidden">
                                <img src="{{ asset($slide['image']) }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/30"></div>
                                <div class="absolute inset-0 flex flex-col items-center justify-center text-center px-6 z-10">
                                    <h2 class="text-white text-3xl font-medium">
                                        {{ $slide['title'] }}
                                    </h2>
                                    <p class="text-white/80 text-xl mt-2 leading-relaxed">
                                        {{ $slide['sub'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Gradient overlay --}}
                <div class="absolute bottom-0 inset-x-0 h-34 bg-gradient-to-t from-[#007a60]/50"></div>
                {{-- Dots --}}
                <div class="absolute bottom-5 left-1/2 -translate-x-1/2 flex gap-2 z-10">
                    @foreach($slides as $i => $slide)
                    <button onclick="goSlide({{ $i }})"
                        class="dot h-2 rounded-full bg-white/40 transition-all {{ $i === 0 ? 'w-6 bg-white' : 'w-2' }}">
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Kanan Form Login --}}
            <div class="w-[400px] bg-white flex flex-col justify-center px-10 py-12 border-l border-gray-100">
                {{-- Logo --}}
                <div class="flex items-center gap-3 mb-8">
                    <span class="text-5xl font-medium text-gray-800">Login</span>
                </div>

                <h1 class="text-2xl font-medium text-gray-800">Selamat datang kembali</h1>
                <p class="text-sm text-gray-500 mt-1 mb-8">Masuk ke akun kamu untuk melanjutkan</p>

                <form action="{{ route('login.process') }}" method="POST">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Email</label>
                    <input type="email" name="email" placeholder="nama@email.com"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                    @error('email')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-2">
                    <label class="block text-sm font-medium text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password" placeholder="••••••••"
                        class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                    @error('password')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                    </div>

                    <div class="text-right mb-6">
                    <a href="{{ route('password.forgot') }}" class="text-sm text-[#00a884] hover:cursor-pointer hover:underline">Lupa password?</a>
                    </div>

                    <button type="submit"
                    class="w-full h-11 bg-[#00a884] hover:bg-[#008f70] hover:cursor-pointer text-white rounded-lg text-sm font-medium transition">
                    Masuk
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    Belum punya akun?
                    <a @click="showModal = true" class="text-[#00a884] hover:cursor-pointer hover:underline">Hubungi superadmin</a>
                </p>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- MODAL: Hubungi Superadmin                   --}}
        {{-- ============================================ --}}
        <div x-show="showModal" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40" @click="showModal = false"></div>

            {{-- Modal Content --}}
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Hubungi Superadmin</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Sampaikan kendala atau request akun baru</p>
                    </div>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 hover:cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Success Message --}}
                <div x-show="successMessage" class="mx-6 mt-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    <span x-text="successMessage"></span>
                </div>

                {{-- Error Summary --}}
                <div x-show="errorMessage" class="mx-6 mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                    <span x-text="errorMessage"></span>
                </div>

                {{-- Form --}}
                <form x-show="!successMessage" @submit.prevent="submitForm()" class="px-6 py-4 space-y-4">
                    {{-- Kategori --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select x-model="form.category" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition bg-white">
                            <option value="">Pilih kategori</option>
                            <option value="kendala_perangkat">Kendala Perangkat</option>
                            <option value="kendala_aplikasi">Kendala Aplikasi</option>
                            <option value="request_akun">Request Akun Baru</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                        <template x-if="errors.category"><p class="text-xs text-red-500 mt-1" x-text="errors.category[0]"></p></template>
                    </div>

                    {{-- ID Perangkat (conditional) --}}
                    <div x-show="form.category === 'kendala_perangkat'" x-transition>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">ID Perangkat <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.device_id" placeholder="Contoh: DEVICE_01" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                        <template x-if="errors.device_id"><p class="text-xs text-red-500 mt-1" x-text="errors.device_id[0]"></p></template>
                    </div>

                    {{-- Role Diminta (conditional) --}}
                    <div x-show="form.category === 'request_akun'" x-transition>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Role Diminta <span class="text-red-500">*</span></label>
                        <select x-model="form.role_requested" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition bg-white">
                            <option value="">Pilih role</option>
                            <option value="nakes">Nakes (Perawat)</option>
                            <option value="dokter">Dokter</option>
                        </select>
                        <template x-if="errors.role_requested"><p class="text-xs text-red-500 mt-1" x-text="errors.role_requested[0]"></p></template>
                    </div>

                    {{-- Instansi (conditional) --}}
                    <div x-show="form.category === 'request_akun'" x-transition>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Instansi <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.institution" placeholder="Nama RS / organisasi" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                        <template x-if="errors.institution"><p class="text-xs text-red-500 mt-1" x-text="errors.institution[0]"></p></template>
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.full_name" placeholder="Nama lengkap Anda" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                        <template x-if="errors.full_name"><p class="text-xs text-red-500 mt-1" x-text="errors.full_name[0]"></p></template>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Email <span class="text-red-500">*</span></label>
                        <input type="email" x-model="form.email" placeholder="nama@email.com" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                        <template x-if="errors.email"><p class="text-xs text-red-500 mt-1" x-text="errors.email[0]"></p></template>
                    </div>

                    {{-- No. HP --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">No. HP / WhatsApp</label>
                        <input type="text" x-model="form.phone" placeholder="08xxxxxxxxxx (opsional)" class="w-full h-10 px-3 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition" />
                    </div>

                    {{-- Urgensi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Urgensi <span class="text-red-500">*</span></label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="form.urgency" value="rendah" class="accent-[#00a884]" />
                                <span class="text-sm text-gray-600">Rendah</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="form.urgency" value="sedang" class="accent-[#00a884]" />
                                <span class="text-sm text-gray-600">Sedang</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" x-model="form.urgency" value="darurat" class="accent-red-500" />
                                <span class="text-sm text-gray-600">Darurat</span>
                            </label>
                        </div>
                        <template x-if="errors.urgency"><p class="text-xs text-red-500 mt-1" x-text="errors.urgency[0]"></p></template>
                    </div>

                    {{-- Detail Kendala --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Detail Kendala <span class="text-red-500">*</span></label>
                        <textarea x-model="form.detail" rows="3" maxlength="1000" placeholder="Jelaskan kendala atau kebutuhan Anda..." class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm focus:outline-none focus:border-[#00a884] focus:ring-2 focus:ring-[#00a884]/10 transition resize-none"></textarea>
                        <p class="text-xs text-gray-400 mt-1"><span x-text="form.detail.length"></span>/1000 karakter</p>
                        <template x-if="errors.detail"><p class="text-xs text-red-500 mt-1" x-text="errors.detail[0]"></p></template>
                    </div>

                    {{-- Upload Bukti (conditional) --}}
                    <div x-show="form.category === 'kendala_perangkat' || form.category === 'kendala_aplikasi'" x-transition>
                        <label class="block text-sm font-medium text-gray-600 mb-1.5">Upload Bukti</label>
                        <input type="file" @change="handleFile($event)" accept=".jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#00a884]/10 file:text-[#00a884] hover:file:bg-[#00a884]/20 file:cursor-pointer" />
                        <p class="text-xs text-gray-400 mt-1">JPG, JPEG, atau PNG. Maks 2MB.</p>
                        <template x-if="errors.attachment"><p class="text-xs text-red-500 mt-1" x-text="errors.attachment[0]"></p></template>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showModal = false" class="flex-1 h-10 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 hover:cursor-pointer transition">
                            Batal
                        </button>
                        <button type="submit" :disabled="loading" class="flex-1 h-10 bg-[#00a884] hover:bg-[#008f70] text-white rounded-lg text-sm font-medium hover:cursor-pointer transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Kirim Pesan</span>
                            <span x-show="loading" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Mengirim...
                            </span>
                        </button>
                    </div>
                </form>

                {{-- Footer setelah sukses --}}
                <div x-show="successMessage" class="px-6 py-4">
                    <button @click="resetForm()" class="w-full h-10 bg-[#00a884] hover:bg-[#008f70] text-white rounded-lg text-sm font-medium hover:cursor-pointer transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Alpine.js CDN (belum ada di auth layout) --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <script>
            // ==========================================
            // Report Modal Alpine.js Component
            // ==========================================
            function reportModal() {
                return {
                    showModal: false,
                    loading: false,
                    successMessage: '',
                    errorMessage: '',
                    errors: {},
                    form: {
                        category: '',
                        device_id: '',
                        role_requested: '',
                        institution: '',
                        full_name: '',
                        email: '',
                        phone: '',
                        urgency: 'sedang',
                        detail: '',
                    },
                    attachment: null,

                    handleFile(event) {
                        this.attachment = event.target.files[0];
                    },

                    async submitForm() {
                        this.loading = true;
                        this.errors = {};
                        this.errorMessage = '';
                        this.successMessage = '';

                        try {
                            const formData = new FormData();
                            for (const key in this.form) {
                                if (this.form[key]) {
                                    formData.append(key, this.form[key]);
                                }
                            }
                            if (this.attachment) {
                                formData.append('attachment', this.attachment);
                            }

                            const response = await fetch('{{ route("report.store") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                },
                                body: formData,
                            });

                            const data = await response.json();

                            if (data.success) {
                                this.successMessage = data.message;
                            } else if (response.status === 429) {
                                this.errorMessage = data.message;
                            } else {
                                this.errorMessage = data.message || 'Terjadi kesalahan.';
                            }
                        } catch (error) {
                            if (error.response) {
                                const data = await error.response.json();
                                if (data.errors) {
                                    this.errors = data.errors;
                                } else {
                                    this.errorMessage = data.message || 'Terjadi kesalahan.';
                                }
                            } else {
                                this.errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                            }
                        } finally {
                            this.loading = false;
                        }
                    },

                    resetForm() {
                        this.showModal = false;
                        this.successMessage = '';
                        this.errorMessage = '';
                        this.errors = {};
                        this.form = {
                            category: '',
                            device_id: '',
                            role_requested: '',
                            institution: '',
                            full_name: '',
                            email: '',
                            phone: '',
                            urgency: 'sedang',
                            detail: '',
                        };
                        this.attachment = null;
                    },
                };
            }

            // ==========================================
            // Image Slider (existing)
            // ==========================================
            document.addEventListener('DOMContentLoaded', function () {
                let cur = 0;
                const slides = document.querySelectorAll('.slide');
                const dots = document.querySelectorAll('.dot');
                let timer = setInterval(() => changeSlide(1), 4000);

                function goSlide(n) {
                    slides[cur].classList.replace('opacity-100', 'opacity-0');
                    dots[cur].classList.remove('w-6', 'bg-white');
                    dots[cur].classList.add('w-2', 'bg-white/40');

                    cur = n;

                    slides[cur].classList.replace('opacity-0', 'opacity-100');
                    dots[cur].classList.remove('w-2', 'bg-white/40');
                    dots[cur].classList.add('w-6', 'bg-white');
                }

                function changeSlide(dir) {
                    clearInterval(timer);
                    goSlide((cur + dir + slides.length) % slides.length);
                    timer = setInterval(() => changeSlide(1), 4000);
                }

                window.goSlide = goSlide;
                window.changeSlide = changeSlide;
            });
        </script>
    @endpush
@endsection
