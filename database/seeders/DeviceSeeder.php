<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Devices;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test devices
        Devices::create([
            'device_id' => 'DEVICE_01',
            'status' => 'online',
            'last_seen' => now(),
        ]);

        Devices::create([
            'device_id' => 'DEVICE_02',
            'status' => 'online',
            'last_seen' => now(),
        ]);

        // Create API keys for devices
        // Plain key: test_key_device_01 (untuk Postman)
        ApiKey::create([
            'device_id' => 'DEVICE_01',
            'key_hash' => Hash::make('test_key_device_01'),
            'name' => 'Test Key Device 01',
            'is_active' => true,
        ]);

        ApiKey::create([
            'device_id' => 'DEVICE_02',
            'key_hash' => Hash::make('test_key_device_02'),
            'name' => 'Test Key Device 02',
            'is_active' => true,
        ]);
    }
}
