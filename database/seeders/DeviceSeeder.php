<?php

namespace Database\Seeders;

use App\Models\ApiKey;
use App\Models\Devices;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        $devices = [
            'DEV_01' => 'SATS Wearable-01',
            'DEV_02' => 'SATS Wearable-02',
            'DEV_03' => 'SATS Wearable-03',
        ];

        foreach ($devices as $deviceId => $name) {
            Devices::create([
                'device_id' => $deviceId,
                'status' => 'offline',
            ]);

            $plainKey = 'sats_' . Str::random(8);

            ApiKey::create([
                'device_id' => $deviceId,
                'key_hash' => ApiKey::hashKey($plainKey),
                'name' => $name,
                'is_active' => true,
            ]);

            $this->command->info("Device: {$deviceId} | Name: {$name} | API Key: {$plainKey}");
        }
    }
}
