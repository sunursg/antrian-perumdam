<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $event,
        string $description = '',
        ?Model $subject = null,
        ?Model $causer = null,
        array $properties = [],
        ?string $ip = null,
    ): ActivityLog {
        return ActivityLog::create([
            'event' => $event,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'causer_type' => $causer?->getMorphClass(),
            'causer_id' => $causer?->getKey(),
            'properties' => $properties ?: null,
            'ip_address' => $ip,
        ]);
    }
}
