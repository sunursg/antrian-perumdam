<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LoketResource\Pages;
use App\Models\Loket;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class LoketResource extends Resource
{
    protected static ?string $model = Loket::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static \UnitEnum|string|null $navigationGroup = 'Master';
    protected static ?string $modelLabel = 'Loket';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
                Forms\Components\TextInput::make('code')->label('Kode')->required()->maxLength(10)->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')->label('Nama')->required()->maxLength(100),
                Forms\Components\Select::make('service_id')
                    ->label('Layanan')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('service.name')->label('Layanan')->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Aktif')->boolean(),
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
            'index' => Pages\ListLokets::route('/'),
            'create' => Pages\CreateLoket::route('/create'),
            'edit' => Pages\EditLoket::route('/{record}/edit'),
        ];
    }
}
