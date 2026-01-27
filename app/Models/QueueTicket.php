<?php

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueTicket extends Model
{
    protected $fillable = [
        'date_key',
        'service_id',
        'seq',
        'ticket_no',
        'status',
        'loket_id',
        'called_at',
        'served_at',
        'noshow_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
        'served_at' => 'datetime',
        'noshow_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function loket(): BelongsTo
    {
        return $this->belongsTo(Loket::class);
    }

    public function statusEnum(): TicketStatus
    {
        return TicketStatus::from($this->status);
    }
}
