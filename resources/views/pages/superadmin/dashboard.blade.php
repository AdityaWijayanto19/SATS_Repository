@extends('layouts.app')
@section('title', 'SATS Monitoring - Dashboard')

@section('content')
<div class="min-h-full p-8" style="background: rgba(230,238,236,0.5);"
     x-data="superadminDashboard()"
     x-init="init()">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold" style="color: rgb(0,62,48);">Dashboard Superadmin</h1>
        <p class="text-sm text-gray-500 mt-1">Monitoring sistem dan perangkat SATS</p>
    </div>

    {{-- ==================== STAT CARDS ==================== --}}
    <div class="grid grid-cols-5 gap-5 mb-6">

        {{-- Total Alat Terdaftar --}}
        <div class="rounded-xl p-5 border" style="background: rgba(0,83,63,0.05); border-color: rgba(0,83,63,0.15);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(0,83,63,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" style="color: rgb(0,62,48);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 3v1.5M4.5 8.25H3m18 0h-1.5M4.5 12H3m18 0h-1.5m-15 3.75H3m18 0h-1.5M8.25 19.5V21M12 3v1.5m0 15V21m3.75-18v1.5m0 15V21m-9-1.5h10.5a2.25 2.25 0 002.25-2.25V6.75a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 6.75v10.5a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold" style="color: rgb(0,62,48);" x-text="totalDevices"></p>
            <p class="text-sm text-gray-500">Total Alat Terdaftar</p>
        </div>

        {{-- Alat Aktif (Online) --}}
        <div class="rounded-xl p-5 border" style="background: rgba(16,185,129,0.05); border-color: rgba(16,185,129,0.2);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Online
                </span>
            </div>
            <p class="text-2xl font-bold text-emerald-700" x-text="activeDeviceCount"></p>
            <p class="text-sm text-gray-500">Alat Aktif</p>
        </div>

        {{-- Alat Non-Aktif (Offline) --}}
        <div class="rounded-xl p-5 border" style="background: rgba(236,72,153,0.05); border-color: rgba(236,72,153,0.2);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(236,72,153,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-pink-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <span class="flex items-center gap-1.5 text-xs font-medium text-pink-600 bg-pink-50 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-pink-500"></span>
                    Offline
                </span>
            </div>
            <p class="text-2xl font-bold text-pink-600" x-text="inactiveDeviceCount"></p>
            <p class="text-sm text-gray-500">Alat Non-Aktif</p>
        </div>

        {{-- Total Pengguna --}}
        <div class="rounded-xl p-5 border" style="background: rgba(59,130,246,0.05); border-color: rgba(59,130,246,0.2);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(59,130,246,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-blue-600" x-text="totalUsers"></p>
            <p class="text-sm text-gray-500">Total Pengguna</p>
        </div>

        {{-- Pengguna Online --}}
        <div class="rounded-xl p-5 border" style="background: rgba(139,92,246,0.05); border-color: rgba(139,92,246,0.2);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: rgba(139,92,246,0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <span class="flex items-center gap-1.5 text-xs font-medium text-violet-600 bg-violet-50 px-2.5 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-violet-500 animate-pulse"></span>
                    Online
                </span>
            </div>
            <p class="text-2xl font-bold text-violet-600" x-text="onlineUsers"></p>
            <p class="text-sm text-gray-500">Pengguna Online</p>
        </div>

    </div>

    {{-- ==================== KONTEN BAWAH: TABEL + LOG ==================== --}}
    <div class="grid grid-cols-3 gap-5">

        {{-- Tabel Perangkat Aktif (2 kolom) --}}
        <div class="col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Perangkat Aktif</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Perangkat yang sedang aktif memantau pasien</p>
                </div>
                <span class="flex items-center gap-1.5 text-xs font-medium text-emerald-600 bg-emerald-50 px-3 py-1 rounded-full">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span x-text="activeDevices.length + ' Aktif'"></span>
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="px-6 py-3 font-semibold text-gray-600">ID Perangkat</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Kondisi Pasien</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Nakes</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Dokter</th>
                            <th class="px-6 py-3 font-semibold text-gray-600">Terakhir Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-if="activeDevices.length === 0">
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                    Tidak ada perangkat aktif
                                </td>
                            </tr>
                        </template>
                        <template x-for="device in activeDevices" :key="device.device_id">
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-3 font-mono text-gray-700" x-text="device.device_id"></td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full"
                                        :class="{
                                            'text-emerald-700 bg-emerald-50': device.status === 'normal',
                                            'text-amber-700 bg-amber-50': device.status === 'warning',
                                            'text-red-700 bg-red-50': device.status === 'critical'
                                        }">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                            :class="{
                                                'bg-emerald-500': device.status === 'normal',
                                                'bg-amber-500': device.status === 'warning',
                                                'bg-red-500': device.status === 'critical'
                                            }"></span>
                                        <span x-text="device.status.charAt(0).toUpperCase() + device.status.slice(1)"></span>
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-gray-600" x-text="device.nakes_name"></td>
                                <td class="px-6 py-3 text-gray-400 italic" x-text="device.dokter_name"></td>
                                <td class="px-6 py-3 text-gray-400 text-xs" x-text="device.updated_at"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Log Aktivitas Terbaru (1 kolom) --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold" style="color: rgb(0,62,48);">Log Aktivitas</h2>
                <p class="text-xs text-gray-400 mt-0.5">Riwayat aktivitas sistem terbaru</p>
            </div>

            <div class="px-6 py-4 space-y-4 max-h-[400px] overflow-y-auto">
                <template x-if="activityLogs.length === 0">
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada aktivitas</p>
                </template>
                <template x-for="(log, index) in activityLogs" :key="log.id">
                    <div class="flex gap-3">
                        <div class="flex flex-col items-center">
                            <div class="w-2.5 h-2.5 rounded-full mt-1.5 flex-shrink-0"
                                :class="{
                                    'bg-blue-500': log.icon === 'blue',
                                    'bg-red-500': log.icon === 'red',
                                    'bg-gray-500': log.icon === 'gray',
                                    'bg-emerald-500': log.icon === 'emerald',
                                    'bg-violet-500': log.icon === 'violet',
                                    'bg-amber-500': log.icon === 'amber',
                                    'bg-indigo-500': log.icon === 'indigo',
                                    'bg-green-500': log.icon === 'green',
                                }"></div>
                            <div class="w-0.5 h-full bg-gray-200 flex-1"
                                x-show="index < activityLogs.length - 1"></div>
                        </div>
                        <div class="pb-4">
                            <p class="text-sm text-gray-700" x-text="log.message"></p>
                            <p class="text-xs text-gray-400 mt-1" x-text="log.created_at"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
function superadminDashboard() {
    return {
        // Reactive counts for stat cards
        totalDevices: @json($totalDevices),
        activeDeviceCount: @json($activeDevices),
        inactiveDeviceCount: @json($inactiveDevices),
        totalUsers: @json($totalUsers),
        onlineUsers: @json($onlineUsers),

        // Active device list for table
        activeDevices: @json($activeDeviceList ?? []),
        channels: [],

        // Activity logs
        activityLogs: @json($activityLogs ?? []),

        init() {
            this.subscribeToGlobalChannel();
            this.subscribeToAllDevices();
            this.pollOnlineUsers();
            setInterval(() => this.pollOnlineUsers(), 5000);
        },

        subscribeToGlobalChannel() {
            window.Echo.channel('superadmin.dashboard')
                .listen('.activity.log.created', (e) => {
                    this.activityLogs.unshift({
                        id: e.id,
                        type: e.type,
                        message: e.message,
                        icon: e.icon,
                        user_name: e.user_name,
                        user_role: e.user_role,
                        device_id: e.device_id,
                        created_at: e.created_at,
                    });
                    if (this.activityLogs.length > 50) {
                        this.activityLogs = this.activityLogs.slice(0, 50);
                    }
                });

            window.Echo.private('superadmin.dashboard')
                .listen('.device.status.changed.global', (e) => {
                    if (e.status === 'online' && e.device_data) {
                        const exists = this.activeDevices.find(d => d.device_id === e.device_id);
                        if (!exists) {
                            this.activeDevices.push(e.device_data);
                            this.activeDeviceCount++;
                            this.inactiveDeviceCount--;
                            this.subscribeToDevice(e.device_id);
                        } else {
                            const index = this.activeDevices.findIndex(d => d.device_id === e.device_id);
                            this.activeDevices[index] = {
                                ...this.activeDevices[index],
                                ...e.device_data,
                            };
                        }
                    } else if (e.status === 'offline') {
                        const wasActive = this.activeDevices.find(d => d.device_id === e.device_id);
                        if (wasActive) {
                            this.activeDevices = this.activeDevices.filter(d => d.device_id !== e.device_id);
                            this.activeDeviceCount--;
                            this.inactiveDeviceCount++;
                            this.unsubscribeDevice(e.device_id);
                        }
                    }
                });
        },

        subscribeToAllDevices() {
            this.activeDevices.forEach(device => {
                this.subscribeToDevice(device.device_id);
            });
        },

        subscribeToDevice(deviceId) {
            if (this.channels.find(c => c.deviceId === deviceId)) return;

            const channel = window.Echo.private(`device.${deviceId}`)
                .listen('.sensor.data.received', (e) => {
                    this.handleSensorUpdate(e);
                });
            this.channels.push({ deviceId, channel });
        },

        handleSensorUpdate(event) {
            const index = this.activeDevices.findIndex(d => d.device_id === event.device_id);
            if (index !== -1) {
                this.activeDevices[index] = {
                    ...this.activeDevices[index],
                    status: event.latest.status,
                    heart_rate: event.latest.heart_rate,
                    spo2: event.latest.spo2,
                    temperature: event.latest.temperature,
                    updated_at: event.latest.created_at,
                };
            }
        },

        unsubscribeDevice(deviceId) {
            const index = this.channels.findIndex(c => c.deviceId === deviceId);
            if (index !== -1) {
                window.Echo.leave(`device.${deviceId}`);
                this.channels.splice(index, 1);
            }
        },

        async pollOnlineUsers() {
            try {
                const response = await fetch('/api/online-users-count');
                const data = await response.json();
                if (data.success) {
                    this.onlineUsers = data.count;
                }
            } catch (error) {
                console.error('Failed to fetch online users count:', error);
            }
        },
    };
}
</script>
@endpush

@endsection
