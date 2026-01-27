<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QueueEvent extends Model
{
    protected $fillable = [
        'type',
        'ticket_no',
        'service_code',
        'loket_code',
        'status',
        'payload',
        'occurred_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];
}
