<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoketAssignmentResource\Pages;
use App\Models\LoketAssignment;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class LoketAssignmentResource extends Resource
{
    protected static ?string $model = LoketAssignment::class;

    // Filament v4 typed props
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-link';
    protected static \UnitEnum|string|null $navigationGroup = 'Akses';
    protected static ?string $modelLabel = 'Penugasan Loket';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Operator')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),

            Select::make('loket_id')
                ->label('Loket')
                ->relationship('loket', 'name')
                ->searchable()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Operator')
                    ->searchable(),

                Tables\Columns\TextColumn::make('loket.name')
                    ->label('Loket')
                    ->searchable(),

                Tables\Columns\TextColumn::make('loket.service.name')
                    ->label('Layanan')
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLoketAssignments::route('/'),
            'create' => Pages\CreateLoketAssignment::route('/create'),
            'edit'   => Pages\EditLoketAssignment::route('/{record}/edit'),
        ];
    }
}
