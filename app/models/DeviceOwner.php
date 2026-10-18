<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceOwner extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'nama',
        'id_anggota',
        'telepon',
        'departemen',
        'catatan',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }
}
