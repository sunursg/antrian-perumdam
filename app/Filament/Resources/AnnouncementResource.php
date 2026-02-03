<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnnouncementResource\Pages;
use App\Models\Announcement;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnnouncementResource extends Resource
{
    protected static ?string $model = Announcement::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-megaphone';
    protected static \UnitEnum|string|null $navigationGroup = 'Pengaturan';
    protected static ?string $modelLabel = 'Pengumuman';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Konten')
                ->schema([
                    TextInput::make('title')->label('Judul')->required(),
                    Select::make('type')
                        ->label('Tipe')
                        ->options(['TEXT' => 'Text', 'VIDEO' => 'Video'])
                        ->default('TEXT')
                        ->required(),
                    Textarea::make('body')
                        ->label('Isi (Text)')
                        ->rows(3)
                        ->hidden(fn (Get $get) => $get('type') === 'VIDEO'),
                    FileUpload::make('media_path')
                        ->label('File Video')
                        ->disk('public')
                        ->directory('announcements')
                        ->acceptedFileTypes(['video/mp4', 'video/webm'])
                        ->hidden(fn (Get $get) => $get('type') === 'TEXT'),
                    TextInput::make('video_url')
                        ->label('URL Video (opsional)')
                        ->hidden(fn (Get $get) => $get('type') === 'TEXT'),
                ])->columns(2),

            Section::make('Pengaturan')
                ->schema([
                    Select::make('organization_id')
                        ->label('Organisasi')
                        ->relationship('organization', 'name')
                        ->preload(),
                    Forms\Components\Toggle::make('is_active')->label('Aktif')->default(true),
                    TextInput::make('priority')->label('Prioritas')->numeric()->default(0),
                    DateTimePicker::make('starts_at')->label('Mulai'),
                    DateTimePicker::make('ends_at')->label('Selesai'),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable(),
                TextColumn::make('type')->label('Tipe'),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('priority')->label('Prioritas'),
                TextColumn::make('starts_at')->label('Mulai')->since()->toggleable(),
                TextColumn::make('ends_at')->label('Selesai')->since()->toggleable(),
                TextColumn::make('updated_at')->label('Update')->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnnouncements::route('/'),
            'create' => Pages\CreateAnnouncement::route('/create'),
            'edit' => Pages\EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
