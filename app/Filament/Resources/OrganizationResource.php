<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-building-office';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $modelLabel = 'Organisasi';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profil')
                ->schema([
                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->disk('public')
                        ->directory('organizations')
                        ->image()
                        ->maxSize(2048),
                    TextInput::make('name')->label('Nama')->required(),
                    TextInput::make('tagline')->label('Tagline')->maxLength(255),
                    Textarea::make('general_notice')->label('Pengumuman Umum')->rows(2)->columnSpanFull(),
                ])->columns(2),

            Section::make('Kontak & Jam Layanan')
                ->schema([
                    TextInput::make('service_hours')->label('Jam Layanan'),
                    TextInput::make('contact_phone')->label('Telepon')->maxLength(50),
                    TextInput::make('contact_email')->label('Email')->email()->maxLength(100),
                    Textarea::make('address')->label('Alamat')->rows(2)->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('tagline')->label('Tagline')->toggleable(),
                TextColumn::make('service_hours')->label('Jam Layanan')->toggleable(),
                TextColumn::make('updated_at')->label('Diperbarui')->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
