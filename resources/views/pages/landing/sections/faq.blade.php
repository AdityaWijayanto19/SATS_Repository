{{-- FAQ SECTION --}}
<section id="faq" class="py-24 bg-white">
    <div class="max-w-3xl mx-auto px-6" data-reveal>
        {{-- Badge & Title --}}
        <div class="text-center mb-16">
            <span class="inline-block bg-emerald-100 text-emerald-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-4">
                FAQ
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-emerald-900">
                Pertanyaan yang Sering Diajukan
            </h2>
        </div>

        {{-- Accordion --}}
        <div class="space-y-3" x-data="{ openFaq: null }">

            @php
                $faqs = [
                    [
                        'q' => 'Apa itu SATS?',
                        'a' => 'SATS (Smart Ambulance Telemedicine System) adalah sistem pemantauan kesehatan pasien secara real-time selama transportasi ambulans. Sistem ini menghubungkan perangkat IoT yang dipasang pada pasien dengan tenaga medis (nakes dan dokter) melalui dashboard monitoring berbasis web.',
                    ],
                    [
                        'q' => 'Data vital apa saja yang dipantau oleh SATS?',
                        'a' => 'SATS memantau tiga data vital utama: Heart Rate (detak jantung dalam bpm), SpO2 (saturasi oksigen dalam %), dan Temperature (suhu tubuh dalam °C). Data ini dikirim secara real-time dari perangkat sensor IoT ke dashboard.',
                    ],
                    [
                        'q' => 'Bagaimana sistem menentukan kondisi pasien?',
                        'a' => 'SATS menggunakan model Machine Learning untuk mengklifikasikan kondisi pasien menjadi tiga kategori: Normal (kondisi stabil), Warning (perlu perhatian), dan Critical (kondisi kritis). Selain itu, sistem juga menyediakan prediksi tren kondisi pasien beberapa menit ke depan.',
                    ],
                    [
                        'q' => 'Siapa saja yang bisa menggunakan sistem ini?',
                        'a' => 'SATS memiliki tiga jenis pengguna: Nakes (tenaga kesehatan di ambulans yang memasang perangkat dan melaporkan kondisi pasien), Dokter (tenaga medis di rumah sakit yang memantau dan memberikan instruksi), dan Superadmin (pengelola sistem yang mengatur perangkat dan pengguna).',
                    ],
                    [
                        'q' => 'Apakah komunikasi antara nakes dan dokter bisa dilakukan secara real-time?',
                        'a' => 'Ya. SATS memiliki fitur chat terintegrasi yang memungkinkan nakes mengirim laporan kondisi pasien dan dokter mengirim instruksi medis secara langsung. Komunikasi ini menggunakan WebSocket (Laravel Reverb) sehingga pesan tersampaikan secara instan tanpa perlu refresh halaman.',
                    ],
                    [
                        'q' => 'Bagaimana cara kerja perangkat IoT dalam sistem ini?',
                        'a' => 'Perangkat IoT (seperti ESP32 dengan sensor MAX30102 dan DS18B20) dipasang pada pasien untuk membaca data vital. Data tersebut dikirim ke server melalui API dengan autentikasi API Key. Server kemudian memproses data, menjalankan klasifikasi ML, dan menyiarkan hasilnya ke semua dashboard yang terhubung secara real-time.',
                    ],
                ];
            @endphp

            @foreach($faqs as $i => $faq)
                <div class="border border-gray-200 rounded-xl overflow-hidden hover:border-emerald-300 transition-colors">
                    <button
                        @click="openFaq = openFaq === {{ $i }} ? null : {{ $i }}"
                        class="w-full flex items-center justify-between px-6 py-4 text-left cursor-pointer hover:bg-gray-50 transition-colors">
                        <span class="font-semibold text-gray-800 pr-4">{{ $faq['q'] }}</span>
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0 transition-transform duration-300"
                            :class="openFaq === {{ $i }} ? 'rotate-180' : ''"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div
                        x-show="openFaq === {{ $i }}"
                        x-collapse
                        class="px-6 pb-4 text-gray-600 leading-relaxed">
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</section>
