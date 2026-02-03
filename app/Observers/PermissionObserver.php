<?php

namespace App\Observers;

use App\Support\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionObserver
{
    public function saved(Permission $permission): void
    {
        ActivityLogger::log('permission.saved', 'Permission diperbarui.', $permission, Auth::user(), ['name' => $permission->name]);
    }

    public function deleted(Permission $permission): void
    {
        ActivityLogger::log('permission.deleted', 'Permission dihapus.', $permission, Auth::user(), ['name' => $permission->name]);
    }
}
