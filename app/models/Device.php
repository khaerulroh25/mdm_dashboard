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
        'branch_id',
        'device_id',
        'hardware_model',
        'marketing_name',
        'brand',
        'manufacturer',
        'serial_number',
        'imei',
        'android_version',
        'latitude',
        'longitude',
        'location_accuracy',
        'location_updated_at',
        'agent_android_id',
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
            'latitude' => 'float',
            'longitude' => 'float',
            'location_accuracy' => 'float',
            'location_updated_at' => 'datetime',
            'network_info' => 'array',
            'memory_info' => 'array',
            'raw_device_data' => 'array',
            'last_status_report' => 'datetime',
            'enrolled_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
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

    /**
     * Scope a query to only include devices accessible by the given user (or current user).
     */
    public function scopeForUser($query, ?User $user = null)
    {
        $user = $user ?? auth()->user();
        if ($user && $user->isBranchScoped()) {
            $query->where('branch_id', $user->branch_id);
        }
        return $query;
    }

    /**
     * Check if the given user (or current authenticated user) can access this device.
     */
    public function canBeAccessedBy(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->isBranchScoped()) {
            return (int) $this->branch_id === (int) $user->branch_id;
        }
        return true;
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

    /**
     * Normalize Indonesian phone number for display (e.g. +62857... -> 0857...).
     */
    public static function formatLocalPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        $trimmed = trim($phone);
        if (str_starts_with($trimmed, '+62')) {
            return '0' . substr($trimmed, 3);
        }
        if (str_starts_with($trimmed, '62') && strlen($trimmed) >= 10) {
            return '0' . substr($trimmed, 2);
        }
        return $trimmed;
    }

    /**
     * Format phone number for WhatsApp link (628...).
     */
    public static function formatWhatsappPhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (empty($digits)) {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            return '62' . substr($digits, 1);
        }
        return $digits;
    }

    /**
     * Extract installed SIM cards info from network_info / raw_device_data.telephonyInfos.
     */
    public function getSimCardsAttribute(): array
    {
        $telephony = $this->network_info['telephonyInfos']
            ?? $this->raw_device_data['networkInfo']['telephonyInfos']
            ?? [];

        if (!is_array($telephony) || empty($telephony)) {
            return [];
        }

        $fallbackOperator = $this->network_info['networkOperatorName']
            ?? $this->raw_device_data['networkInfo']['networkOperatorName']
            ?? null;

        $sims = [];
        foreach (array_values($telephony) as $idx => $sim) {
            $rawPhone = !empty($sim['phoneNumber']) ? trim($sim['phoneNumber']) : null;
            $localPhone = self::formatLocalPhone($rawPhone);
            $waPhone = self::formatWhatsappPhone($rawPhone);
            $carrier = !empty($sim['carrierName']) ? trim($sim['carrierName']) : $fallbackOperator;

            $sims[] = [
                'slot'             => $idx + 1,
                'raw_phone'        => $rawPhone,
                'phone_number'     => $localPhone,
                'whatsapp_phone'   => $waPhone,
                'carrier_name'     => $carrier ?: 'Operator Seluler',
                'icc_id'           => $sim['iccId'] ?? null,
                'activation_state' => $sim['activationState'] ?? null,
            ];
        }

        return $sims;
    }

    /**
     * Get list of non-empty local phone numbers detected from installed SIM cards.
     */
    public function getDetectedPhoneNumbersAttribute(): array
    {
        $numbers = [];
        foreach ($this->sim_cards as $sim) {
            if (!empty($sim['phone_number'])) {
                $numbers[] = $sim['phone_number'];
            }
        }
        return array_values(array_unique($numbers));
    }

    /**
     * Get management mode from raw_device_data (e.g. DEVICE_OWNER or PROFILE_OWNER).
     */
    public function getManagementModeAttribute(): string
    {
        return $this->raw_device_data['managementMode'] ?? 'DEVICE_OWNER';
    }

    /**
     * Get ownership from raw_device_data (e.g. COMPANY_OWNED or PERSONALLY_OWNED).
     */
    public function getOwnershipAttribute(): string
    {
        return $this->raw_device_data['ownership'] ?? 'COMPANY_OWNED';
    }

    /**
     * Check if device is enrolled as a personal Work Profile.
     */
    public function getIsWorkProfileAttribute(): bool
    {
        return $this->management_mode === 'PROFILE_OWNER' || $this->ownership === 'PERSONALLY_OWNED';
    }

    /**
     * Human-friendly management mode label.
     */
    public function getManagementModeLabelAttribute(): string
    {
        return $this->is_work_profile ? 'Work Profile (Pribadi)' : 'Fully Managed (Perusahaan)';
    }

    /**
     * Get the latest reported battery level (percentage) if available.
     */
    public function getBatteryLevelAttribute(): ?float
    {
        $events = $this->raw_device_data['powerManagementEvents'] ?? null;
        if (!empty($events) && is_array($events)) {
            $latestLevel = null;
            $latestTimestamp = -1;

            foreach ($events as $event) {
                if (isset($event['batteryLevel']) && $event['batteryLevel'] !== null) {
                    $ts = !empty($event['createTime']) ? strtotime($event['createTime']) : 0;
                    if ($ts >= $latestTimestamp) {
                        $latestTimestamp = $ts;
                        $latestLevel = round((float)$event['batteryLevel'], 1);
                    }
                }
            }

            if ($latestLevel !== null) {
                return $latestLevel;
            }
        }

        return null;
    }

    /**
     * Get Bootstrap Icon class based on battery level percentage.
     */
    public function getBatteryIconAttribute(): string
    {
        $level = $this->battery_level;
        if ($level === null) {
            return 'bi-battery';
        }
        if ($level >= 75) {
            return 'bi-battery-full';
        }
        if ($level >= 25) {
            return 'bi-battery-half';
        }
        return 'bi-battery';
    }

    /**
     * Get semantic text color class based on battery level percentage.
     */
    public function getBatteryColorClassAttribute(): string
    {
        $level = $this->battery_level;
        if ($level === null) {
            return 'text-muted';
        }
        if ($level >= 50) {
            return 'text-success';
        }
        if ($level >= 20) {
            return 'text-warning';
        }
        return 'text-danger';
    }

    /**
     * Get semantic badge classes based on battery level percentage.
     */
    public function getBatteryBadgeClassAttribute(): string
    {
        $level = $this->battery_level;
        if ($level === null) {
            return 'bg-light text-muted border';
        }
        if ($level >= 50) {
            return 'bg-success bg-opacity-10 text-success border border-success-subtle';
        }
        if ($level >= 20) {
            return 'bg-warning bg-opacity-10 text-warning border border-warning-subtle';
        }
        return 'bg-danger bg-opacity-10 text-danger border border-danger-subtle';
    }

    /**
     * Get the effective latest interaction timestamp across Google AMAPI and MDM Agent GPS.
     */
    public function getEffectiveLastSeenAttribute(): ?\Carbon\Carbon
    {
        if ($this->location_updated_at && $this->last_status_report) {
            return $this->location_updated_at->gt($this->last_status_report)
                ? $this->location_updated_at
                : $this->last_status_report;
        }

        return $this->location_updated_at ?? $this->last_status_report;
    }

    /**
     * Check if device is actively online (reported status within the last 24 hours).
     */
    public function getIsOnlineAttribute(): bool
    {
        $lastSeen = $this->effective_last_seen;
        if (!$lastSeen) {
            return false;
        }

        return $lastSeen->diffInHours(now()) <= 24;
    }

    /**
     * Human-friendly online status description.
     */
    public function getOnlineStatusLabelAttribute(): string
    {
        if ($this->is_online) {
            return 'Aktif Online';
        }

        $lastSeen = $this->effective_last_seen;
        if ($lastSeen) {
            return 'Offline (' . $lastSeen->diffForHumans() . ')';
        }

        return 'Belum Pernah Lapor';
    }

    /**
     * Check if device has valid GPS coordinates recorded.
     */
    public function getHasLocationAttribute(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Direct Google Maps link for the device's coordinates.
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if (!$this->has_location) {
            return null;
        }
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Scope for devices triggering offline alerts (> 24 hours without contact).
     * Excludes deleted devices and settled/completed loans (policy 'Lunas').
     * Checks both AMAPI status report and MDM Agent location updates.
     */
    public function scopeOfflineAlert($query, ?string $severity = null)
    {
        $cutoff24h = now()->subHours(24);

        // A device is an offline alert candidate if it has NO recent interaction (neither AMAPI nor Agent location) in the last 24h
        $query->where('state', '!=', 'DELETED')
              ->where('policy_id', '!=', 'Lunas')
              ->where(function ($q) use ($cutoff24h) {
                  $q->where(function ($sub) use ($cutoff24h) {
                      $sub->whereNull('last_status_report')
                          ->orWhere('last_status_report', '<', $cutoff24h);
                  })->where(function ($sub) use ($cutoff24h) {
                      $sub->whereNull('location_updated_at')
                          ->orWhere('location_updated_at', '<', $cutoff24h);
                  });
              });

        $latestSql = 'COALESCE(GREATEST(COALESCE(last_status_report, location_updated_at), COALESCE(location_updated_at, last_status_report)), last_status_report, location_updated_at)';

        if ($severity === 'warning') {
            // 1 s/d 3 hari (24 - 72 jam)
            $query->where(function ($q) use ($latestSql, $cutoff24h) {
                $q->whereBetween(\Illuminate\Support\Facades\DB::raw($latestSql), [now()->subHours(72), $cutoff24h]);
            });
        } elseif ($severity === 'high') {
            // 3 s/d 7 hari (72 - 168 jam)
            $query->where(function ($q) use ($latestSql) {
                $q->whereBetween(\Illuminate\Support\Facades\DB::raw($latestSql), [now()->subHours(168), now()->subHours(72)]);
            });
        } elseif ($severity === 'critical') {
            // > 7 hari (168 jam) atau tidak pernah lapor sama sekali
            $query->where(function ($q) use ($latestSql) {
                $q->where(function ($sub) {
                    $sub->whereNull('last_status_report')->whereNull('location_updated_at');
                })->orWhere(\Illuminate\Support\Facades\DB::raw($latestSql), '<', now()->subHours(168));
            });
        }

        return $query;
    }

    /**
     * Get offline duration in hours.
     */
    public function getOfflineDurationHoursAttribute(): int
    {
        $lastSeen = $this->effective_last_seen ?? $this->created_at;
        return (int) $lastSeen->diffInHours(now());
    }

    /**
     * Get offline risk classification metadata.
     */
    public function getOfflineRiskAttribute(): array
    {
        $lastSeen = $this->effective_last_seen;
        if (!$lastSeen) {
            $days = (int) max(1, $this->created_at->diffInDays(now()));
            return [
                'level'        => 'critical',
                'label'        => 'Kritis (>7 Hari / Belum Lapor)',
                'badge_class'  => 'bg-danger text-white',
                'badge_subtle' => 'bg-danger bg-opacity-10 text-danger border border-danger-subtle',
                'days'         => $days,
                'icon'         => 'bi-exclamation-octagon-fill',
                'description'  => 'Perangkat belum pernah melaporkan status sejak registrasi',
            ];
        }

        $hours = $this->offline_duration_hours;
        $days = (int) round($hours / 24);

        if ($hours <= 72) {
            return [
                'level'        => 'warning',
                'label'        => 'Peringatan Dini (1-3 Hari)',
                'badge_class'  => 'bg-warning text-dark',
                'badge_subtle' => 'bg-warning bg-opacity-15 text-dark border border-warning-subtle',
                'days'         => max(1, $days),
                'icon'         => 'bi-exclamation-triangle-fill',
                'description'  => "Offline selama {$days} hari ({$lastSeen->diffForHumans()})",
            ];
        }

        if ($hours <= 168) {
            return [
                'level'        => 'high',
                'label'        => 'Siaga Tinggi (3-7 Hari)',
                'badge_class'  => 'bg-danger bg-opacity-75 text-white',
                'badge_subtle' => 'bg-danger bg-opacity-10 text-danger border border-danger-subtle',
                'days'         => $days,
                'icon'         => 'bi-exclamation-circle-fill',
                'description'  => "Offline selama {$days} hari ({$lastSeen->diffForHumans()})",
            ];
        }

        return [
            'level'        => 'critical',
            'label'        => 'Kritis (>7 Hari)',
            'badge_class'  => 'bg-danger text-white',
            'badge_subtle' => 'bg-danger bg-opacity-10 text-danger border border-danger-subtle',
            'days'         => $days,
            'icon'         => 'bi-shield-fill-x',
            'description'  => "Offline selama {$days} hari ({$lastSeen->diffForHumans()})",
        ];
    }

    /**
     * Get preferred WhatsApp recipient number for this device (owner phone or detected SIM).
     */
    public function getPreferredWhatsappNumberAttribute(): ?string
    {
        if (!empty($this->owner?->telepon)) {
            $formatted = self::formatWhatsappPhone($this->owner->telepon);
            if ($formatted) {
                return $formatted;
            }
        }

        foreach ($this->sim_cards as $sim) {
            if (!empty($sim['whatsapp_phone'])) {
                return $sim['whatsapp_phone'];
            }
        }

        return null;
    }

    /**
     * Friendly WhatsApp reminder message URL for customer follow-up.
     */
    public function getWhatsappAlertUrlAttribute(): ?string
    {
        $phone = $this->preferred_whatsapp_number;
        if (!$phone) {
            return null;
        }

        $nama = $this->owner?->nama ?? 'Pelanggan';
        $model = $this->display_name;
        $cabang = $this->branch?->name ?? 'Layanan Kami';
        $durasi = $this->offline_risk['days'];

        $msg = "Halo Bpk/Ibu *{$nama}*,\n\n"
             . "Kami dari tim administrasi *{$cabang}* menginformasikan bahwa sistem kami mendeteksi perangkat cicilan Anda (*{$model}*) saat ini sedang *tidak terhubung ke jaringan internet* (offline sekitar {$durasi} hari).\n\n"
             . "Mohon kesediaannya untuk menyalakan perangkat dan memastikan koneksi internet/data seluler aktif agar status perangkat tetap sinkron dengan sistem kami.\n\n"
             . "Jika terdapat kendala teknis atau pertanyaan, silakan membalas pesan ini. Terima kasih.";

        return "https://wa.me/{$phone}?text=" . rawurlencode($msg);
    }
}

