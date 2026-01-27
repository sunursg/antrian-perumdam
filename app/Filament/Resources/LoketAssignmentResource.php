<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoketAssignmentResource\Pages;
use App\Models\LoketAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoketAssignmentResource extends Resource
{
    protected static ?string $model = LoketAssignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Akses';
    protected static ?string $modelLabel = 'Penugasan Loket';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Operator')
                ->relationship('user', 'name')
                ->searchable()
                ->required(),
            Forms\Components\Select::make('loket_id')
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
                Tables\Columns\TextColumn::make('user.name')->label('Operator')->searchable(),
                Tables\Columns\TextColumn::make('loket.name')->label('Loket')->searchable(),
                Tables\Columns\TextColumn::make('loket.service.name')->label('Layanan')->toggleable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoketAssignments::route('/'),
            'create' => Pages\CreateLoketAssignment::route('/create'),
            'edit' => Pages\EditLoketAssignment::route('/{record}/edit'),
        ];
    }
}
