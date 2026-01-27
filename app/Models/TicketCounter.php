<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCounter extends Model
{
    protected $fillable = ['date_key', 'service_id', 'last_seq'];
}
