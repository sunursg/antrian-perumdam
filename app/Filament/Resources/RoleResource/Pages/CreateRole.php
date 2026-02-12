<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function afterCreate(): void
    {
        $this->syncPermissionsFromForm();
    }

    private function syncPermissionsFromForm(): void
    {
        $data = $this->form->getState();
        $permissionIds = [];

        foreach ($data as $key => $value) {
            if (str_starts_with($key, 'perm_group_') && is_array($value)) {
                $permissionIds = array_merge($permissionIds, $value);
            }
        }

        $permissions = Permission::whereIn('id', array_unique(array_filter($permissionIds)))->get();
        $this->record->syncPermissions($permissions);
    }
}
