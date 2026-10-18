<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'hardware_model',
        'marketing_name',
        'brand',
        'manufacturer',
        'serial_number',
        'android_version',
        'state',
        'policy_id',
        'network_info',
        'memory_info',
        'raw_device_data',
        'last_status_report',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'network_info' => 'array',
            'memory_info' => 'array',
            'raw_device_data' => 'array',
            'last_status_report' => 'datetime',
            'enrolled_at' => 'datetime',
        ];
    }

    public function owner(): HasOne
    {
        return $this->hasOne(DeviceOwner::class, 'device_id', 'device_id');
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class, 'device_id', 'device_id');
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class, 'policy_id', 'policy_id');
    }

    public function getShortIdAttribute(): string
    {
        return basename($this->device_id);
    }

    /**
     * Get human friendly marketing name (e.g. Vivo Y29 5G).
     */
    public function getHumanModelNameAttribute(): string
    {
        if (!empty($this->marketing_name)) {
            return $this->marketing_name;
        }

        return $this->hardware_model ?: 'Perangkat Android';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->getHumanModelNameAttribute();
    }
}
