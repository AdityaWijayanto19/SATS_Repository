<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class ApiKey extends Model
{
    protected $fillable = [
        'device_id',
        'key_hash',
        'name',
        'is_active',
        'rate_limit_per_minute',
        'last_used',
        'last_used_ip',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship: Belong to Device
     */
    public function device()
    {
        return $this->belongsTo(Devices::class, 'device_id', 'device_id');
    }

    /**
     * Hash API key (call ini saat create)
     * Usage: ApiKey::hashKey($plainKey)
     */
    public static function hashKey(string $plainKey): string
    {
        return Hash::make($plainKey);
    }

    /**
     * Verify plain key terhadap hashed key
     * Usage: $apiKey->verifyKey($plainKey)
     */
    public function verifyKey(string $plainKey): bool
    {
        return Hash::check($plainKey, $this->key_hash);
    }

    /**
     * Find & validate API key
     * Return: ApiKey|null (kalau valid & active)
     * Optimized: timeout 5 detik untuk DB query
     */
    public static function findValidKey(string $plainKey, string $deviceId)
    {
        try {
            // Cari key untuk device ini yang active
            // Optimize: select only needed columns
            $apiKey = self::select(['id', 'device_id', 'key_hash', 'is_active', 'expires_at'])
                ->where('device_id', $deviceId)
                ->where('is_active', true)
                ->timeout(5) // 5 second timeout
                ->first();

            if (!$apiKey) {
                return null;
            }

            // Check expiration
            if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
                return null;
            }

            // Verify key hash
            if (!$apiKey->verifyKey($plainKey)) {
                return null;
            }

            return $apiKey;
        } catch (\Exception $e) {
            // Log error
            \Log::error('ApiKey validation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update last_used timestamp & IP (call saat device authenticate)
     */
    public function updateLastUsed(string $ip = null): void
    {
        $this->update([
            'last_used' => now(),
            'last_used_ip' => $ip,
        ]);
    }

    /**
     * Check if key active & not expired
     */
    public function isValid(): bool
    {
        return $this->is_active &&
               (!$this->expires_at || $this->expires_at->isFuture());
    }
}
