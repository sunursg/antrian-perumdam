<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-shield-check';
    protected static \UnitEnum|string|null $navigationGroup = 'Manajemen Pengguna';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('SUPER_ADMIN') ?? false;
    }

    protected static ?string $modelLabel = 'Peran & Hak Akses';
    protected static ?string $pluralModelLabel = 'Peran & Hak Akses';

    /**
     * Permission groups — maps a display label to a prefix filter.
     * All permissions matching the prefix will appear as checkboxes.
     */
    private static function permissionGroups(): array
    {
        return [
            'Dashboard'            => 'dashboard.',
            'Layanan (Service)'    => 'service.',
            'Loket'                => 'loket.',
            'Penugasan Loket'      => 'loket-assignment.',
            'Organisasi'           => 'organization.',
            'Pengumuman'           => 'announcement.',
            'Tiket Antrian'        => 'queue-ticket.',
            'Event Antrian'        => 'queue-event.',
            'Operasi Antrian'      => 'queue.',
            'Display & Kiosk'      => 'display.,kiosk.',
            'Pengguna'             => 'user.',
            'Role'                 => 'role.',
            'Permission'           => 'permission.',
            'Activity Log'         => 'activity-log.',
            'Debug Log'            => 'debug-log.',
            'Sistem'               => 'settings.',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        $components = [
            Section::make('Detail Role')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Role')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->placeholder('contoh: ADMIN'),
                    TextInput::make('guard_name')
                        ->label('Guard')
                        ->default(config('auth.defaults.guard', 'web'))
                        ->required(),
                ])
                ->columns(2),
        ];

        // Build a CheckboxList section per permission group
        $guard = config('auth.defaults.guard', 'web');
        $allPermissions = Permission::where('guard_name', $guard)
            ->orderBy('name')
            ->pluck('name', 'id');

        $sections = [];
        foreach (self::permissionGroups() as $label => $prefixes) {
            $prefixList = array_map('trim', explode(',', $prefixes));
            $filtered = $allPermissions->filter(function ($name) use ($prefixList) {
                foreach ($prefixList as $prefix) {
                    if (str_starts_with($name, $prefix)) {
                        return true;
                    }
                }
                return false;
            });

            if ($filtered->isEmpty()) {
                continue;
            }

            $sections[] = CheckboxList::make('perm_group_' . md5($label))
                ->label($label)
                ->options($filtered->toArray())
                ->columns(3)
                ->bulkToggleable()
                ->dehydrated(false)
                ->afterStateHydrated(function ($component, $state, $record) use ($filtered) {
                    if (!$record) return;
                    $assigned = $record->permissions()->pluck('id')->toArray();
                    $component->state(
                        $filtered->keys()->filter(fn ($id) => in_array($id, $assigned))->values()->toArray()
                    );
                });
        }

        if (!empty($sections)) {
            $components[] = Section::make('Hak Akses (Permissions)')
                ->description('Centang permission yang ingin diberikan ke role ini.')
                ->schema($sections)
                ->collapsible();
        }

        return $schema->components($components);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Role')->searchable()->sortable(),
                TextColumn::make('guard_name')->label('Guard'),
                TextColumn::make('permissions_count')->counts('permissions')->label('Jumlah Permission'),
                TextColumn::make('users_count')->counts('users')->label('Jumlah User'),
            ])
            ->defaultSort('name')
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
            'index'  => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit'   => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
