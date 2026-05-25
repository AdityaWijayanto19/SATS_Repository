<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Command untuk rotasi API key perangkat IoT.
 *
 * Dijalankan via scheduler: php artisan apikey:rotate-expired
 *
 * Dua blok utama:
 * 1. Key dalam masa tenggang (30 hari sebelum expired) → catat peringatan, tetap aktif
 * 2. Key yang sudah expired → nonaktifkan + log emergency
 */
class RotateExpiredApiKeys extends Command
{
    protected $signature = 'apikey:rotate-expired';
    protected $description = 'Rotasi API key yang sudah expired atau akan segera expired';

    public function handle(): int
    {
        $this->info('Memulai rotasi API key...');

        // ---------------------------------------------------------------
        // Block 1: Key yang akan expired dalam 30 hari (MASA TENGGANG)
        //
        // PENTING: Key ini tetap AKTIF sampai tanggal expires_at tercapai.
        // Kita hanya catat peringatan sebagai audit trail supaya admin
        // bisa merencanakan rotasi key secara proaktif.
        // Kita TIDAK menonaktifkan (is_active = false) di sini karena
        // bisa langsung memutus koneksi ambulans yang sedang di jalan.
        // ---------------------------------------------------------------
        $expiringKeys = ApiKey::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30))
            ->where('expires_at', '>', now())
            ->where('is_active', true)
            ->get();

        if ($expiringKeys->isEmpty()) {
            $this->info('Tidak ada key yang akan segera expired.');
        } else {
            $this->info("Ditemukan {$expiringKeys->count()} key yang akan segera expired (masa tenggang — TIDAK dinonaktifkan).");

            foreach ($expiringKeys as $key) {
                /** @var ApiKey $key */
                $daysRemaining = now()->diffInDays($key->expires_at, false);

                $this->warn(
                    "⚠ Key #{$key->id} untuk perangkat [{$key->device_id}] expired dalam ~{$daysRemaining} hari ({$key->expires_at->toDateTimeString()}). "
                    . "Key tetap AKTIF — rencanakan rotasi."
                );

                // Hanya catat audit — tidak ada perubahan status
                Log::channel('device-audit')->warning(
                    'API key mendekati masa expired (masa tenggang)',
                    [
                        'device_id'        => $key->device_id,
                        'key_id'           => $key->id,
                        'expires_at'       => $key->expires_at->toDateTimeString(),
                        'days_remaining'   => (int) $daysRemaining,
                        'is_active'        => true, // Tetap aktif
                        'action'           => 'audit_only',
                    ]
                );
            }
        }

        // ---------------------------------------------------------------
        // Block 2: Key yang SUDAH expired (expires_at <= sekarang)
        //
        // Key ini aman untuk dinonaktifkan karena waktu expired-nya
        // sudah terlewati, artinya perangkat seharusnya sudah di-rotasi.
        // Kita catat log emergency karena perangkat mungkin sudah
        // kehilangan koneksi.
        // ---------------------------------------------------------------
        $expiredKeys = ApiKey::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('is_active', true)
            ->get();

        if ($expiredKeys->isNotEmpty()) {
            $this->error("Ditemukan {$expiredKeys->count()} key expired — menonaktifkan.");

            foreach ($expiredKeys as $key) {
                /** @var ApiKey $key */
                $key->update(['is_active' => false]);

                $this->error("✗ Key #{$key->id} untuk perangkat [{$key->device_id}] DINONAKTIFKAN (expired pada: {$key->expires_at})");

                Log::channel('device-audit')->emergency(
                    'API key dinonaktifkan (expired)',
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
            $this->info('Tidak ada key expired yang perlu dinonaktifkan.');
        }

        $graceCount = $expiringKeys->count();
        $deactivatedCount = $expiredKeys->count();

        $this->newLine();
        $this->info("Ringkasan rotasi: {$graceCount} key dalam masa tenggang (aktif), {$deactivatedCount} key dinonaktifkan.");

        return 0;
    }
}
