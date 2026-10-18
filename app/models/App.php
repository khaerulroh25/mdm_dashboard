<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class App extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_name',
        'app_name',
        'icon_url',
        'developer',
    ];

    public function policies(): BelongsToMany
    {
        return $this->belongsToMany(Policy::class, 'policy_applications')
            ->withPivot(['id', 'install_type', 'auto_update'])
            ->withTimestamps();
    }

    public function policyApplications(): HasMany
    {
        return $this->hasMany(PolicyApplication::class);
    }
}
