<?php

namespace App\Filament\Resources\LoketAssignmentResource\Pages;

use App\Filament\Resources\LoketAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoketAssignment extends EditRecord
{
    protected static string $resource = LoketAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
