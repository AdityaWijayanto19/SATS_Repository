<style>
    #hero-canvas-container {
        position: absolute;
        inset: 0;
        z-index: 0;
    }
    #hero-canvas-container canvas {
        width: 100% !important;
        height: 100% !important;
        display: block;
    }
</style>

<section id="beranda" class="relative min-h-screen flex items-center justify-center overflow-hidden" style="background: #001a14;">

    {{-- Three.js Canvas Background --}}
    <div id="hero-canvas-container"></div>

    {{-- Overlay gelap tipis supaya teks terbaca --}}
    <div class="absolute inset-0 z-[1]" style="background: radial-gradient(ellipse at center, rgba(0,26,20,0.3) 0%, rgba(0,26,20,0.7) 100%);"></div>

    {{-- Konten Tengah --}}
    <div class="relative z-10 text-center px-6 max-w-3xl mx-auto">

        {{-- Judul --}}
        <h1 class="text-4xl md:text-6xl font-bold leading-tight mb-5" style="color: #ecfdf5;">
            Smart Ambulance<br>Telemedicine System
        </h1>

        {{-- Subjudul --}}
        <p class="text-base md:text-lg leading-relaxed mb-8 mx-auto" style="color: rgba(209,250,229,0.7); max-width: 520px;">
            Solusi pemantauan kondisi vital pasien secara real-time dari ambulans menuju IGD.
            Membantu tenaga medis mengambil keputusan lebih cepat dengan dukungan Edge Machine Learning.
        </p>

        {{-- Tombol CTA --}}
        @guest
            <a href="{{ route('login') }}"
                class="inline-flex items-center gap-2 px-7 py-3 text-white font-semibold rounded-xl text-sm transition-all hover:shadow-lg hover:translate-y-[-1px]"
                style="background: rgb(16,185,129);">
                Get Started
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        @else
            @php
                $dashboardRoute = match(auth()->user()->role) {
                    'superadmin' => route('superadmin.dashboard'),
                    'dokter' => route('dokter.dashboard'),
                    'nakes' => route('dashboard'),
                    default => route('dashboard'),
                };
            @endphp
            <a href="{{ $dashboardRoute }}"
                class="inline-flex items-center gap-2 px-7 py-3 text-white font-semibold rounded-xl text-sm transition-all hover:shadow-lg hover:translate-y-[-1px]"
                style="background: rgb(16,185,129);">
                Buka Dashboard
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </a>
        @endguest

    </div>

</section>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('hero-canvas-container');
    if (!container || typeof THREE === 'undefined') return;

    // =====================================================
    // SHADERS
    // =====================================================
    const vertexShader = `
        precision highp float;
        void main() {
            gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
        }
    `;

    const fragmentShader = `
        precision highp float;

        uniform float iTime;
        uniform vec3  iResolution;
        uniform float animationSpeed;

        uniform int topLineCount;
        uniform int middleLineCount;
        uniform int bottomLineCount;

        uniform float topLineDistance;
        uniform float middleLineDistance;
        uniform float bottomLineDistance;

        uniform vec3 topWavePosition;
        uniform vec3 middleWavePosition;
        uniform vec3 bottomWavePosition;

        uniform vec2 iMouse;
        uniform float bendRadius;
        uniform float bendStrength;
        uniform float bendInfluence;

        uniform vec3 lineGradient[8];
        uniform int lineGradientCount;

        mat2 rotate(float r) {
            return mat2(cos(r), sin(r), -sin(r), cos(r));
        }

        vec3 getLineColor(float t) {
            if (lineGradientCount <= 0) return vec3(1.0);
            if (lineGradientCount == 1) return lineGradient[0];

            float clampedT = clamp(t, 0.0, 0.9999);
            float scaled = clampedT * float(lineGradientCount - 1);
            int idx = int(floor(scaled));
            float f = fract(scaled);
            int idx2 = min(idx + 1, lineGradientCount - 1);
            return mix(lineGradient[idx], lineGradient[idx2], f) * 0.5;
        }

        float wave(vec2 uv, float offset, vec2 screenUv, vec2 mouseUv) {
            float time = iTime * animationSpeed;
            float x_offset   = offset;
            float x_movement = time * 0.1;
            float amp        = sin(offset + time * 0.2) * 0.3;
            float y          = sin(uv.x + x_offset + x_movement) * amp;

            vec2 d = screenUv - mouseUv;
            float influence = exp(-dot(d, d) * bendRadius);
            float bendOffset = (mouseUv.y - screenUv.y) * influence * bendStrength * bendInfluence;
            y += bendOffset;

            float m = uv.y - y;
            return 0.0175 / max(abs(m) + 0.01, 1e-3) + 0.01;
        }

        void mainImage(out vec4 fragColor, in vec2 fragCoord) {
            vec2 baseUv = (2.0 * fragCoord - iResolution.xy) / iResolution.y;
            baseUv.y *= -1.0;

            vec3 col = vec3(0.0);
            vec2 mouseUv = (2.0 * iMouse - iResolution.xy) / iResolution.y;
            mouseUv.y *= -1.0;

            // Bottom layer
            for (int i = 0; i < 8; ++i) {
                if (i >= bottomLineCount) break;
                float fi = float(i);
                float t = fi / max(float(bottomLineCount - 1), 1.0);
                vec3 lineCol = getLineColor(t);
                float angle = bottomWavePosition.z * log(length(baseUv) + 1.0);
                vec2 ruv = baseUv * rotate(angle);
                col += lineCol * wave(
                    ruv + vec2(bottomLineDistance * fi + bottomWavePosition.x, bottomWavePosition.y),
                    1.5 + 0.2 * fi, baseUv, mouseUv
                ) * 0.2;
            }

            // Middle layer
            for (int i = 0; i < 8; ++i) {
                if (i >= middleLineCount) break;
                float fi = float(i);
                float t = fi / max(float(middleLineCount - 1), 1.0);
                vec3 lineCol = getLineColor(t);
                float angle = middleWavePosition.z * log(length(baseUv) + 1.0);
                vec2 ruv = baseUv * rotate(angle);
                col += lineCol * wave(
                    ruv + vec2(middleLineDistance * fi + middleWavePosition.x, middleWavePosition.y),
                    2.0 + 0.15 * fi, baseUv, mouseUv
                );
            }

            // Top layer
            for (int i = 0; i < 8; ++i) {
                if (i >= topLineCount) break;
                float fi = float(i);
                float t = fi / max(float(topLineCount - 1), 1.0);
                vec3 lineCol = getLineColor(t);
                float angle = topWavePosition.z * log(length(baseUv) + 1.0);
                vec2 ruv = baseUv * rotate(angle);
                ruv.x *= -1.0;
                col += lineCol * wave(
                    ruv + vec2(topLineDistance * fi + topWavePosition.x, topWavePosition.y),
                    1.0 + 0.2 * fi, baseUv, mouseUv
                ) * 0.1;
            }

            fragColor = vec4(col, 1.0);
        }

        void main() {
            vec4 color = vec4(0.0);
            mainImage(color, gl_FragCoord.xy);
            gl_FragColor = color;
        }
    `;

    // =====================================================
    // SETUP THREE.JS
    // =====================================================
    const scene = new THREE.Scene();
    const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);

    const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';
    container.appendChild(renderer.domElement);

    // =====================================================
    // WARNA EMERALD GRADIENT
    // =====================================================
    const emeraldGradient = [
        new THREE.Vector3(6, 95, 70),      // #065f46 dark emerald
        new THREE.Vector3(5, 150, 105),    // #059669 emerald
        new THREE.Vector3(16, 185, 129),   // #10b981 emerald-500
        new THREE.Vector3(52, 211, 153),   // #34d399 emerald-400
        new THREE.Vector3(110, 231, 183),  // #6ee7b7 emerald-300
    ];

    // =====================================================
    // UNIFORMS
    // =====================================================
    const uniforms = {
        iTime:             { value: 0 },
        iResolution:       { value: new THREE.Vector3(1, 1, 1) },
        animationSpeed:    { value: 0.8 },

        topLineCount:      { value: 6 },
        middleLineCount:   { value: 6 },
        bottomLineCount:   { value: 5 },

        topLineDistance:    { value: 0.08 },
        middleLineDistance: { value: 0.06 },
        bottomLineDistance: { value: 0.05 },

        topWavePosition:   { value: new THREE.Vector3(10.0, 0.5, -0.4) },
        middleWavePosition:{ value: new THREE.Vector3(5.0, 0.0, 0.2) },
        bottomWavePosition:{ value: new THREE.Vector3(2.0, -0.7, 0.4) },

        iMouse:            { value: new THREE.Vector2(-1000, -1000) },
        bendRadius:        { value: 5.0 },
        bendStrength:      { value: -0.5 },
        bendInfluence:     { value: 0 },

        lineGradient:      { value: Array.from({ length: 8 }, () => new THREE.Vector3(1, 1, 1)) },
        lineGradientCount: { value: emeraldGradient.length }
    };

    // Set gradient colors
    emeraldGradient.forEach(function(color, i) {
        uniforms.lineGradient.value[i].set(color.x / 255, color.y / 255, color.z / 255);
    });

    // =====================================================
    // MESH
    // =====================================================
    const material = new THREE.ShaderMaterial({
        uniforms: uniforms,
        vertexShader: vertexShader,
        fragmentShader: fragmentShader
    });

    const geometry = new THREE.PlaneGeometry(2, 2);
    const mesh = new THREE.Mesh(geometry, material);
    scene.add(mesh);

    // =====================================================
    // RESIZE
    // =====================================================
    function setSize() {
        var width = container.clientWidth || 1;
        var height = container.clientHeight || 1;
        renderer.setSize(width, height, false);
        uniforms.iResolution.value.set(renderer.domElement.width, renderer.domElement.height, 1);
    }
    setSize();

    var resizeObserver = new ResizeObserver(setSize);
    resizeObserver.observe(container);

    // =====================================================
    // MOUSE INTERACTION
    // =====================================================
    var targetMouse = new THREE.Vector2(-1000, -1000);
    var currentMouse = new THREE.Vector2(-1000, -1000);
    var targetInfluence = 0;
    var currentInfluence = 0;
    var mouseDamping = 0.05;

    renderer.domElement.addEventListener('pointermove', function(e) {
        var rect = renderer.domElement.getBoundingClientRect();
        var x = e.clientX - rect.left;
        var y = e.clientY - rect.top;
        var dpr = renderer.getPixelRatio();
        targetMouse.set(x * dpr, (rect.height - y) * dpr);
        targetInfluence = 1.0;
    });

    renderer.domElement.addEventListener('pointerleave', function() {
        targetInfluence = 0.0;
    });

    // =====================================================
    // RENDER LOOP
    // =====================================================
    var clock = new THREE.Clock();

    function animate() {
        requestAnimationFrame(animate);
        uniforms.iTime.value = clock.getElapsedTime();

        currentMouse.lerp(targetMouse, mouseDamping);
        uniforms.iMouse.value.copy(currentMouse);
        currentInfluence += (targetInfluence - currentInfluence) * mouseDamping;
        uniforms.bendInfluence.value = currentInfluence;

        renderer.render(scene, camera);
    }
    animate();
});
</script>
@endpush
