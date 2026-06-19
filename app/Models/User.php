<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Mass assignable
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'photo',
        'last_activity',
    ];

    /**
     * Hidden fields
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'nakes_id');
    }

    public function deviceConfig()
    {
        return $this->hasOne(NakesDeviceConfig::class, 'user_id');
    }

    /**
     * Nama dengan prefix berdasarkan role: dr. untuk dokter, Ns. untuk nakes.
     * Menghapus prefix lama jika ada (dr., dr, Ns., Ns, Suster, dll).
     */
    protected function formattedName(): Attribute
    {
        return Attribute::make(
            get: function () {
                $name = trim($this->name);

                // Hapus prefix lama yang umum
                $name = preg_replace('/^(dr\.?\s*|Ns\.?\s*|Suster\s*|Perawat\s*|Ners\s*)/i', '', $name);
                $name = trim($name);

                return match ($this->role) {
                    'dokter' => 'dr. ' . $name,
                    'nakes'  => 'Ns. ' . $name,
                    default  => $name,
                };
            }
        );
    }
}
