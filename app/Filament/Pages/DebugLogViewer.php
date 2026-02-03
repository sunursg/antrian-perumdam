<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\File;
use BackedEnum;
use UnitEnum;

class DebugLogViewer extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-bug-ant';
    protected static string|UnitEnum|null $navigationGroup = 'Pengamanan';
    protected static ?string $navigationLabel = 'Debug Log Viewer';
    protected string $view = 'filament.pages.debug-log-viewer';

    public array $lines = [];

    public function mount(): void
    {
        $path = storage_path('logs/laravel.log');
        if (File::exists($path)) {
            $content = File::lines($path);
            $buffer = [];
            foreach ($content as $line) {
                $buffer[] = trim($line);
            }
            $this->lines = array_slice($buffer, -200);
        }
    }
}
