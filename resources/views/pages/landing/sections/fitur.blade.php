{{--FITUR SECTION --}}
<section id="fitur" class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
        {{-- Badge & Title --}}
        <div class="text-center mb-16" data-reveal>
            <span class="inline-block bg-emerald-100 text-emerald-700 text-sm font-semibold px-4 py-1.5 rounded-full mb-4">
                Fitur
            </span>
            <h2 class="text-4xl md:text-5xl font-bold text-emerald-900">
                Teknologi Cerdas untuk<br>Penyelamatan Nyawa
            </h2>
        </div>

        {{-- Feature Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8" data-reveal-stagger>

            {{-- Card 1: Real-Time Health Monitoring --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="h-52 overflow-hidden">
                    <img src="{{ asset('assets/fitur_1.png') }}"
                         alt="Real-Time Health Monitoring"
                         class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Real-Time Health Monitoring</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Pantau detak jantung, kadar oksigen (SpO2), dan suhu tubuh pasien secara langsung melalui koneksi IoT yang stabil dan andal.
                    </p>
                </div>
            </div>

            {{-- Card 2: Intelligent Urgency Classification --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="h-52 overflow-hidden">
                    <img src="{{ asset('assets/fitur_2.png') }}"
                         alt="Intelligent Urgency Classification"
                         class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Intelligent Urgency Classification</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Sistem otomatis mengkategorikan kondisi pasien menjadi Normal, Warning, atau Critical untuk memprioritaskan penanganan medis.
                    </p>
                </div>
            </div>

            {{-- Card 3: Predictive Edge Analytics --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="h-52 overflow-hidden">
                    <img src="{{ asset('assets/fitur_3.png') }}"
                         alt="Predictive Edge Analytics"
                         class="w-full h-full object-cover">
                </div>
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-3">Predictive Edge Analytics</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Machine Learning memprediksi tren kondisi vital pasien beberapa menit ke depan untuk antisipasi tindakan darurat.
                    </p>
                </div>
            </div>

        </div>
    </div>
</section>
