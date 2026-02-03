<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'requires_confirmation',
        'daily_quota',
        'open_at',
        'close_at',
        'avg_service_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'requires_confirmation' => 'bool',
    ];

    public function lokets(): HasMany
    {
        return $this->hasMany(Loket::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }
}
