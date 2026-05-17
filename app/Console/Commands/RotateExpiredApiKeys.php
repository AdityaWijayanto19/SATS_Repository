<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RotateExpiredApiKeys extends Command
{
    protected $signature = 'apikey:rotate-expired';
    protected $description = 'Rotate API keys that have expired or are about to expire';

    public function handle(): int
    {
        $this->info('Starting API key rotation...');

        // ---------------------------------------------------------------
        // Block 1: Keys expiring within 30 days (GRACE PERIOD)
        //
        // IMPORTANT: These keys must REMAIN ACTIVE until their exact
        // expires_at timestamp is reached. We only log a warning as an
        // audit trail so operators can plan key rotation proactively.
        // We do NOT toggle is_active to false here — doing so would
        // immediately disconnect ambulances that are currently on the road.
        // ---------------------------------------------------------------
        $expiringKeys = ApiKey::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30))
            ->where('expires_at', '>', now())
            ->where('is_active', true)
            ->get();

        if ($expiringKeys->isEmpty()) {
            $this->info('No expiring keys found.');
        } else {
            $this->info("Found {$expiringKeys->count()} keys expiring soon (grace period — NOT deactivated).");

            foreach ($expiringKeys as $key) {
                /** @var ApiKey $key */
                $daysRemaining = now()->diffInDays($key->expires_at, false);

                $this->warn(
                    "⚠ Key #{$key->id} for device [{$key->device_id}] expires in ~{$daysRemaining} days ({$key->expires_at->toDateTimeString()}). "
                    . "Key remains ACTIVE — plan rotation."
                );

                // Audit log only — no state change
                Log::channel('device-audit')->warning(
                    'API key approaching expiration (grace period)',
                    [
                        'device_id'        => $key->device_id,
                        'key_id'           => $key->id,
                        'expires_at'       => $key->expires_at->toDateTimeString(),
                        'days_remaining'   => (int) $daysRemaining,
                        'is_active'        => true, // Remains active
                        'action'           => 'audit_only',
                    ]
                );
            }
        }

        // ---------------------------------------------------------------
        // Block 2: Keys that have ALREADY expired (expires_at <= now)
        //
        // These keys are safe to deactivate — their expiry timestamp has
        // been reached, so the device should have been rotated already.
        // We fire an emergency log because this indicates the device may
        // have lost connectivity.
        // ---------------------------------------------------------------
        $expiredKeys = ApiKey::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('is_active', true)
            ->get();

        if ($expiredKeys->isNotEmpty()) {
            $this->error("Found {$expiredKeys->count()} expired keys — deactivating.");

            foreach ($expiredKeys as $key) {
                /** @var ApiKey $key */
                $key->update(['is_active' => false]);

                $this->error("✗ Key #{$key->id} for device [{$key->device_id}] DEACTIVATED (expired at: {$key->expires_at})");

                Log::channel('device-audit')->emergency(
                    'API key deactivated (expired)',
                    [
                        'device_id'  => $key->device_id,
                        'key_id'     => $key->id,
                        'expired_at' => $key->expires_at->toDateTimeString(),
                        'is_active'  => false,
                        'action'     => 'deactivated',
                    ]
                );
            }
        } else {
            $this->info('No expired keys to deactivate.');
        }

        $graceCount = $expiringKeys->count();
        $deactivatedCount = $expiredKeys->count();

        $this->newLine();
        $this->info("Rotation summary: {$graceCount} keys in grace period (active), {$deactivatedCount} keys deactivated.");

        return 0;
    }
}
