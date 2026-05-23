<?php

use App\Models\Devices;
use App\Models\User;
use Illuminate\Support\Facades\{Auth, Broadcast};


Broadcast::channel('device.{deviceId}', function (User $user, $deviceId) {
    // Only dokter and nakes can subscribe to device channels
    if (!in_array($user->role, ['dokter', 'nakes'])) {
        return false;
    }

    // Verify the device exists
    return Devices::where('device_id', $deviceId)->exists();
});

Broadcast::channel('superadmin.dashboard', function (User $user) {
    // Only superadmin can subscribe to this channel
    return $user->role === 'superadmin';
});
