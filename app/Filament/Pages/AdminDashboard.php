<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard;

class AdminDashboard extends Dashboard
{
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->hasRole('SUPER_ADMIN') || $user->email === 'superadmin@demo.test');
    }
}
