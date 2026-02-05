<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class AdminQuickLinks extends Widget
{
    protected string $view = 'filament.widgets.admin-quick-links';

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('SUPER_ADMIN') ?? false;
    }
}
