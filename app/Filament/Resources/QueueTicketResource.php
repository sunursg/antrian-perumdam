<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueTicketResource\Pages;
use App\Models\QueueTicket;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QueueTicketResource extends Resource
{
    protected static ?string $model = QueueTicket::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-ticket';
    protected static \UnitEnum|string|null $navigationGroup = 'Operasional'; 
    protected static ?string $modelLabel = 'Tiket Antrian'; 

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_no')->label('Tiket')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan'),
                Tables\Columns\TextColumn::make('loket.code')->label('Loket')->toggleable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Ambil')->dateTime('d/m H:i')->sortable(),
                Tables\Columns\TextColumn::make('called_at')->label('Dipanggil')->dateTime('d/m H:i')->toggleable(),
                Tables\Columns\TextColumn::make('served_at')->label('Selesai')->dateTime('d/m H:i')->toggleable(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['service:id,name', 'loket:id,code']);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQueueTickets::route('/'),
            'view' => Pages\ViewQueueTicket::route('/{record}'),
        ];
    }
}
