<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'enterprise_id',
        'name',
        'admin_email',
        'admin_phone',
        'signup_url_name',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata'  => 'array',
    ];

    /**
     * Get the currently active enterprise.
     */
    public static function getActiveEnterprise(): ?self
    {
        return self::where('is_active', true)->first()
            ?? self::first();
    }
}
