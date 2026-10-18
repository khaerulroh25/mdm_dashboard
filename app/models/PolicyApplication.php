<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyApplication extends Model
{
    use HasFactory;

    protected $table = 'policy_applications';

    protected $fillable = [
        'policy_id',
        'app_id',
        'install_type',
        'auto_update',
    ];

    protected function casts(): array
    {
        return [
            'auto_update' => 'boolean',
        ];
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }
}
