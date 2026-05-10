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

        // Dokter
        User::create([
            'name' => 'Dr. Andi',
            'email' => 'andi@sats.id',
            'password' => Hash::make('password'),
            'role' => 'dokter',
        ]);

        // Nakes (perawat)
        User::create([
            'name' => 'Suster Rina',
            'email' => 'rina@sats.id',
            'password' => Hash::make('password'),
            'role' => 'nakes',
        ]);
    }
}
