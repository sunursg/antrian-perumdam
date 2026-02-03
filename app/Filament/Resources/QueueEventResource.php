<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueEventResource\Pages;
use App\Models\QueueEvent;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QueueEventResource extends Resource
{
    protected static ?string $model = QueueEvent::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clock';
    protected static \UnitEnum|string|null $navigationGroup = 'Operasional'; 
    protected static ?string $modelLabel = 'Riwayat Panggilan'; 

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('occurred_at')->label('Waktu')->dateTime('d M Y H:i:s')->sortable(),
                Tables\Columns\TextColumn::make('ticket_no')->label('Tiket')->searchable(),
                Tables\Columns\TextColumn::make('loket_code')->label('Loket'),
                Tables\Columns\TextColumn::make('service_code')->label('Layanan'),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('type')->label('Tipe'),
                Tables\Columns\TextColumn::make('payload->actor_name')->label('Pelaksana')->toggleable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQueueEvents::route('/'),
        ];
    }
}
