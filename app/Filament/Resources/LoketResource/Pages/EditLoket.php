<?php

namespace App\Filament\Resources\LoketResource\Pages;

use App\Filament\Resources\LoketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoket extends EditRecord
{
    protected static string $resource = LoketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
