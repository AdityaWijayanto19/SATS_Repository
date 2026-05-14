<?php

use App\Models\User;
use Illuminate\Support\Facades\{Auth, Broadcast};


Broadcast::channel('device.{deviceId}', function (User $user, $deviceId) {
    return Auth::check();
});
