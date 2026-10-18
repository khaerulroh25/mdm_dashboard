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
     * Helper to map common hardware codes to human readable names.
     */
    public function getHumanModelNameAttribute(): string
    {
        $code = $this->hardware_model ?? '';
        $mapping = [
            'SM-A528B' => 'Samsung Galaxy A52s',
            'SM-M326B' => 'Samsung Galaxy M32 5G',
            'SM-G996B' => 'Samsung Galaxy S21+',
            'M2101K6G' => 'Xiaomi Redmi Note 10 Pro',
            'CPH2219'  => 'Oppo Reno 6',
            'V2434'    => 'Vivo Y29',
            'V2419'    => 'Vivo 19s',
            'Infinix X6882' => 'Infinix Hot 50i',
            'Infinix X6885' => 'Infinix Hot 60 Pro',
            'RMX3941'  => 'Realme C75',
        ];

        return $mapping[$code] ?? ($code ?: 'Unknown Android');
    }
}
