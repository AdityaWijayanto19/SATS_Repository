<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Superadmin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sats.id',
            'password' => Hash::make('password'),
            'role' => 'superadmin',
        ]);

        // Dokter (3 akun)
        User::create([
            'name' => 'dr.Andi Wijaya',
            'email' => 'andi@sats.id',
            'password' => Hash::make('password'),
            'role' => 'dokter',
        ]);

        User::create([
            'name' => 'dr.Budi Santoso',
            'email' => 'budi@sats.id',
            'password' => Hash::make('password'),
            'role' => 'dokter',
        ]);

        User::create([
            'name' => 'dr.Citra Dewi',
            'email' => 'citra@sats.id',
            'password' => Hash::make('password'),
            'role' => 'dokter',
        ]);

        // Nakes (3 akun)
        User::create([
            'name' => 'Suster Rina',
            'email' => 'rina@sats.id',
            'password' => Hash::make('password'),
            'role' => 'nakes',
        ]);

        User::create([
            'name' => 'Perawat Dian',
            'email' => 'dian@sats.id',
            'password' => Hash::make('password'),
            'role' => 'nakes',
        ]);

        User::create([
            'name' => 'Perawat Eka',
            'email' => 'eka@sats.id',
            'password' => Hash::make('password'),
            'role' => 'nakes',
        ]);
    }
}
