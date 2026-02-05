<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;

use Filament\Resources\Resource;
use Filament\Schemas\Schema; 
use Filament\Schemas\Components\Section; 

use Filament\Forms\Components\TextInput; 
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Actions\EditAction;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static \UnitEnum|string|null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Layanan';
    protected static ?string $modelLabel = 'Layanan';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Layanan')
                ->schema([
                    TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->maxLength(10)
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('Deskripsi / Aturan Singkat')
                        ->rows(2)
                        ->columnSpanFull(),

                    Toggle::make('requires_confirmation')
                        ->label('Butuh konfirmasi khusus')
                        ->helperText('Aktifkan untuk layanan yang memerlukan persetujuan sebelum ambil tiket (mis. Layanan Pelanggan).')
                        ->default(false),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])
                ->columns(3),

            Section::make('Aturan Operasional')
                ->schema([
                    TextInput::make('daily_quota')
                        ->label('Kuota Harian')
                        ->numeric()
                        ->required(),

                    TextInput::make('avg_service_minutes')
                        ->label('Rata-rata (menit)')
                        ->numeric()
                        ->required(),

                    TimePicker::make('open_at')
                        ->label('Buka')
                        ->required(),

                    TimePicker::make('close_at')
                        ->label('Tutup')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')->label('Kode')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('daily_quota')->label('Kuota')->sortable(),
                IconColumn::make('requires_confirmation')->label('Butuh Konfirmasi')->boolean(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('updated_at')->label('Update')->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }
}
