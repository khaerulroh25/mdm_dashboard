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
        'imei',
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

    /**
     * Map Android API level to human OS version number.
     */
    public static function mapApiLevelToVersion(?int $apiLevel): ?string
    {
        if (!$apiLevel) {
            return null;
        }

        return match ($apiLevel) {
            36 => '16',
            35 => '15',
            34 => '14',
            33 => '13',
            32 => '12L',
            31 => '12',
            30 => '11',
            29 => '10',
            28 => '9',
            27 => '8.1',
            26 => '8.0',
            25 => '7.1',
            24 => '7.0',
            23 => '6.0',
            22 => '5.1',
            21 => '5.0',
            default => (string)$apiLevel,
        };
    }

    /**
     * Accessor for android_version: falls back to apiLevel mapping if not explicitly set.
     */
    public function getAndroidVersionAttribute($value): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        $apiLevel = $this->raw_device_data['apiLevel'] ?? null;
        return $apiLevel ? self::mapApiLevelToVersion((int)$apiLevel) : null;
    }

    /**
     * Accessor for IMEI: returns column value or extracts from network_info / raw_device_data.
     */
    public function getImeiAttribute($value = null): ?string
    {
        if (!empty($value)) {
            return $value;
        }

        if (!empty($this->network_info['imei'])) {
            return $this->network_info['imei'];
        }

        if (!empty($this->raw_device_data['networkInfo']['imei'])) {
            return $this->raw_device_data['networkInfo']['imei'];
        }

        if (!empty($this->network_info['telephonyInfos']) && is_array($this->network_info['telephonyInfos'])) {
            foreach ($this->network_info['telephonyInfos'] as $sim) {
                if (!empty($sim['imei'])) {
                    return $sim['imei'];
                }
            }
        }

        if (!empty($this->raw_device_data['networkInfo']['telephonyInfos']) && is_array($this->raw_device_data['networkInfo']['telephonyInfos'])) {
            foreach ($this->raw_device_data['networkInfo']['telephonyInfos'] as $sim) {
                if (!empty($sim['imei'])) {
                    return $sim['imei'];
                }
            }
        }

        return null;
    }
}

