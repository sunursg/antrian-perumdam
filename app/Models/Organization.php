<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'tagline',
        'logo_path',
        'address',
        'contact_phone',
        'contact_email',
        'service_hours',
        'general_notice',
    ];

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }
}
