{{-- CARA KERJA SECTION --}}
<section id="cara-kerja" class="py-24 bg-emerald-900">
    <div class="max-w-5xl mx-auto px-6">
        {{-- Badge & Title --}}
        <div class="text-center mb-16 relative" data-reveal>
            <span class="inline-block bg-emerald-700 text-emerald-100 text-sm font-semibold px-4 py-1.5 rounded-full mb-4">
                Cara Kerja
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-3">
                Bagaimana SATS Bekerja?
            </h2>
            <p class="text-emerald-300 text-lg">
                Alur integrasi teknologi IoT dari ambulans hingga ke tangan dokter
            </p>
        </div>

        {{-- Stepper --}}
        <div class="space-y-0" data-reveal-stagger>

            {{-- Step 01 --}}
            <div class="flex items-start gap-8 py-8 border-b border-emerald-700">
                <div class="flex-shrink-0 w-16 text-right">
                    <span class="text-5xl font-black text-emerald-600">01</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-2">Wearable Sensor Deployment</h3>
                    <p class="text-emerald-200 leading-relaxed max-w-2xl">
                        Perangkat sensor dipasangkan pada pasien untuk mulai membaca detak jantung, suhu, dan kadar oksigen (SpO2).
                    </p>
                </div>
            </div>

            {{-- Step 02 --}}
            <div class="flex items-start gap-8 py-8 border-b border-emerald-700">
                <div class="flex-shrink-0 w-16 text-right">
                    <span class="text-5xl font-black text-emerald-600">02</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-2">Local Data Analytics</h3>
                    <p class="text-emerald-200 leading-relaxed max-w-2xl">
                        Mikrokontroler ESP32 memproses data secara lokal untuk mengklasifikasikan tingkat urgensi pasien menggunakan algoritma cerdas.
                    </p>
                </div>
            </div>

            {{-- Step 03 --}}
            <div class="flex items-start gap-8 py-8 border-b border-emerald-700">
                <div class="flex-shrink-0 w-16 text-right">
                    <span class="text-5xl font-black text-emerald-600">03</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-2">Cloud Synchronization</h3>
                    <p class="text-emerald-200 leading-relaxed max-w-2xl">
                        Data yang telah diproses dikirim secara instan melalui jaringan internet ke server pusat agar bisa diakses kapan saja dan di mana saja.
                    </p>
                </div>
            </div>

            {{-- Step 04 --}}
            <div class="flex items-start gap-8 py-8">
                <div class="flex-shrink-0 w-16 text-right">
                    <span class="text-5xl font-black text-emerald-600">04</span>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white mb-2">Clinical Decision Support</h3>
                    <p class="text-emerald-200 leading-relaxed max-w-2xl">
                        Dokter di rumah sakit memantau grafik vital sign melalui dashboard web dan memberikan instruksi medis sebelum ambulans tiba.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
