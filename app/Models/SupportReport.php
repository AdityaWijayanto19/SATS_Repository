<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportReport extends Model
{
    protected $table = 'reports';

    protected $fillable = [
        'category',
        'device_id',
        'role_requested',
        'institution',
        'full_name',
        'email',
        'phone',
        'urgency',
        'detail',
        'attachment_path',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    // Scope: filter by status
    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    // Scope: filter by category
    public function scopeOfCategory($query, ?string $category)
    {
        return $category ? $query->where('category', $category) : $query;
    }

    // Scope: filter by urgency
    public function scopeOfUrgency($query, ?string $urgency)
    {
        return $urgency ? $query->where('urgency', $urgency) : $query;
    }

    // Scope: search by name or email
    public function scopeSearch($query, ?string $search)
    {
        if (!$search) return $query;

        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Accessor: category label (Indonesian)
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'kendala_perangkat' => 'Kendala Perangkat',
            'kendala_aplikasi' => 'Kendala Aplikasi',
            'request_akun' => 'Request Akun',
            'lainnya' => 'Lainnya',
            default => $this->category,
        };
    }

    // Accessor: urgency label (Indonesian)
    public function getUrgencyLabelAttribute(): string
    {
        return match ($this->urgency) {
            'rendah' => 'Rendah',
            'sedang' => 'Sedang',
            'darurat' => 'Darurat',
            default => $this->urgency,
        };
    }

    // Accessor: status label (Indonesian)
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'baru' => 'Baru',
            'diproses' => 'Dalam Proses',
            'selesai' => 'Selesai',
            default => $this->status,
        };
    }
}
