<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'device_id',
        'issued_by_user_id',
        'command_type',
        'command_params',
        'status',
        'amapi_command_id',
        'error_message',
        'executed_at',
    ];

    protected function casts(): array
    {
        return [
            'command_params' => 'array',
            'executed_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', 'device_id');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by_user_id');
    }
}
