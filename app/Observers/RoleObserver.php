<?php

namespace App\Observers;

use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RoleObserver
{
    public function saved(Role $role): void
    {
        ActivityLogger::log('role.saved', 'Role diperbarui.', $role, Auth::user(), ['name' => $role->name]);
    }

    public function deleted(Role $role): void
    {
        ActivityLogger::log('role.deleted', 'Role dihapus.', $role, Auth::user(), ['name' => $role->name]);
    }
}
