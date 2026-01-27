<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loket extends Model
{
    protected $fillable = ['code', 'name', 'service_id', 'is_active'];

    protected $casts = ['is_active' => 'bool'];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(QueueTicket::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LoketAssignment::class);
    }
}
