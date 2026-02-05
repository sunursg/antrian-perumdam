<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class OperatorConsole extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Operator Loket';
    protected static string|\UnitEnum|null $navigationGroup = 'Operasional';
    protected static ?string $slug = 'operator-console';

    protected string $view = 'filament.pages.operator-console';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['ADMIN', 'SUPER_ADMIN']) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['ADMIN', 'SUPER_ADMIN']) ?? false;
    }
}
