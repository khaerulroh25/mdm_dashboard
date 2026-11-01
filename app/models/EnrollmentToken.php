<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EnrollmentToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'policy_id',
        'branch_id',
        'enterprise_id',
        'owner_data',
        'is_one_time',
        'is_used',
        'used_at',
        'used_by_device_id',
        'expires_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'owner_data' => 'array',
            'is_one_time' => 'boolean',
            'is_used' => 'boolean',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    /**
     * Branch where this enrolled device should belong.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * User who generated this enrollment token.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Device that consumed this token.
     */
    public function usedByDevice(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'used_by_device_id');
    }

    /**
     * Check if the token is currently active and can be used for enrollment.
     */
    public function isValid(): bool
    {
        if ($this->is_used && $this->is_one_time) {
            return false;
        }

        return $this->expires_at->isFuture();
    }

    /**
     * Mark token as consumed by a device.
     */
    public function markAsUsed(int $deviceId): void
    {
        $this->update([
            'is_used'            => true,
            'used_at'            => now(),
            'used_by_device_id'  => $deviceId,
        ]);
    }

    /**
     * Generate a cryptographically secure, unique enrollment token string.
     */
    public static function generateUniqueToken(string $prefix = 'MDM-ENR-'): string
    {
        do {
            $token = $prefix . strtoupper(Str::random(12));
        } while (static::where('token', $token)->exists());

        return $token;
    }
}
