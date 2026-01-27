<?php

namespace App\Enums;

enum TicketStatus: string
{
    case MENUNGGU = 'MENUNGGU';
    case DIPANGGIL = 'DIPANGGIL';
    case SELESAI = 'SELESAI';
    case NO_SHOW = 'NO_SHOW';
}
