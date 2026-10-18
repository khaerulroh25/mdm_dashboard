<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Policy extends Model
{
    use HasFactory;

    protected $fillable = [
        'policy_id',
        'name',
        'description',
        'camera_disabled',
        'wifi_config_disabled',
        'factory_reset_disabled',
        'install_apps_disabled',
        'allow_untrusted_apps',
        'safe_boot_disabled',
        'location_mode',
        'play_store_mode',
        'raw_policy_json',
    ];

    protected function casts(): array
    {
        return [
            'camera_disabled' => 'boolean',
            'wifi_config_disabled' => 'boolean',
            'factory_reset_disabled' => 'boolean',
            'install_apps_disabled' => 'boolean',
            'allow_untrusted_apps' => 'boolean',
            'safe_boot_disabled' => 'boolean',
            'raw_policy_json' => 'array',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'policy_id', 'policy_id');
    }

    public function applications(): BelongsToMany
    {
        return $this->belongsToMany(App::class, 'policy_applications')
            ->withPivot(['id', 'install_type', 'auto_update'])
            ->withTimestamps();
    }

    public function policyApplications(): HasMany
    {
        return $this->hasMany(PolicyApplication::class);
    }
}
