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
        'enterprise_id',
        'default_policy_id',
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

    public function enterprise(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Enterprise::class, 'enterprise_id');
    }

    public function getEffectiveEnterpriseId(): string
    {
        return $this->enterprise?->enterprise_id
            ?? Enterprise::getActiveEnterprise()?->enterprise_id
            ?? config('amapi.enterprise_id');
    }

    public function getEffectivePolicyId(): string
    {
        return $this->default_policy_id ?: 'Mode_kiosk';
    }

    /**
     * Automatically generate a unique, human-readable branch code from the branch name.
     */
    public static function generateCode(string $name): string
    {
        $cleaned = preg_replace('/^(cabang|kantor|unit|wilayah|area)\s+/i', '', trim($name));
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $cleaned);
        $words = array_values(array_filter(explode(' ', strtoupper(trim($cleaned)))));

        if (empty($words)) {
            $base = 'CBG';
        } elseif (count($words) === 1) {
            $word = $words[0];
            $consonants = $word[0] . preg_replace('/[AEIOU]/', '', substr($word, 1));
            $base = substr($consonants, 0, 3);
            if (strlen($base) < 3) {
                $base = substr($word, 0, 3);
            }
        } else {
            $firstWord = $words[0];
            $consonants = $firstWord[0] . preg_replace('/[AEIOU]/', '', substr($firstWord, 1));
            $firstPart = substr($consonants, 0, 3);
            if (strlen($firstPart) < 3) {
                $firstPart = substr($firstWord, 0, 3);
            }
            $initials = '';
            for ($i = 1; $i < count($words); $i++) {
                $initials .= substr($words[$i], 0, 1);
            }
            $base = $firstPart . '-' . substr($initials, 0, 3);
        }

        $base = strtoupper($base);
        $code = $base;
        $counter = 2;

        while (self::where('code', $code)->exists()) {
            $code = "{$base}-{$counter}";
            $counter++;
        }

        return $code;
    }
}
