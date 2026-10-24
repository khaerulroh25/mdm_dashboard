<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'contact_phone',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'branch_id');
    }

    public function activeDevices(): HasMany
    {
        return $this->hasMany(Device::class, 'branch_id')->where('state', '!=', 'DELETED');
    }
}
