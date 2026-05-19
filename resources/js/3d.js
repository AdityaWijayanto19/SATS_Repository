import * as THREE from 'three';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

// LANGSUNG TEMPEL KE GLOBAL WINDOW BIAR APILNE BISA BACA DI MANAPUN
window.dashboard = function() {
    return {
        // State Alpine untuk menampung angka medis SATS
        heartRate: 75,
        spo2: 98,

        init() {
            // 1. Inisialisasi Three.js Dashboard setelah komponen siap
            this.initThreeJS();

            // 2. Simulasi Data Real-time (Bisa diganti Laravel Echo nanti)
            setInterval(() => {
                this.heartRate = Math.floor(Math.random() * (85 - 70 + 1)) + 70;
                this.spo2 = Math.floor(Math.random() * (100 - 97 + 1)) + 97;
            }, 2000);
        },

        initThreeJS() {
            const container = document.getElementById('canvas-3d');
            if (!container) return;

            // --- SETUP SCENE, CAMERA, & RENDERER ---
            const scene = new THREE.Scene();

            const camera = new THREE.PerspectiveCamera(40, container.clientWidth / container.clientHeight, 0.1, 1000);
            camera.position.set(0, 0, 6);

            const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
            renderer.setSize(container.clientWidth, container.clientHeight);
            renderer.setPixelRatio(window.devicePixelRatio);
            container.appendChild(renderer.domElement);

            // --- LIGHTING (Agar tekstur PBR keluar kilaunya) ---
            const ambientLight = new THREE.AmbientLight(0xffffff, 1.5);
            scene.add(ambientLight);

            const directionalLight = new THREE.DirectionalLight(0xffffff, 2.0);
            directionalLight.position.set(5, 8, 5);
            scene.add(directionalLight);

            // --- ORBIT CONTROLS ---
            const controls = new OrbitControls(camera, renderer.domElement);
            controls.enableDamping = true;
            controls.dampingFactor = 0.05;

            // --- LOAD MODEL 3D MONITOR (.GLB) ---
            const loader = new GLTFLoader();

            loader.load('/assets/models/monitor-3d.glb', (gltf) => {
                const model = gltf.scene;

                // Menengahkan koordinat model
                const box = new THREE.Box3().setFromObject(model);
                const center = box.getCenter(new THREE.Vector3());
                model.position.x += (model.position.x - center.x);
                model.position.y += (model.position.y - center.y);
                model.position.z += (model.position.z - center.z);

                // Geser sedikit ke kiri biar area kanan muat buat kotak angka HTML
                model.position.x = -0.8;

                scene.add(model);
            }, undefined, (error) => {
                console.error('Gagal load model 3D SATS:', error);
            });

            // --- ANIMATION LOOP ---
            const animate = () => {
                requestAnimationFrame(animate);
                controls.update();
                renderer.render(scene, camera);
            };
            animate();

            // --- HANDLE RESIZE WINDOW ---
            window.addEventListener('resize', () => {
                camera.aspect = container.clientWidth / container.clientHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(container.clientWidth, container.clientHeight);
            });
        }
    }
}
