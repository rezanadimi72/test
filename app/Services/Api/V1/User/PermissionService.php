<?php

namespace App\Services\Api\V1\User;

use App\Http\BaseService;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionService extends BaseService
{
    public static function list(Request $request)
    {
        $permissions = new Permission();
        return $permissions->query()
            ->get();
    }
}
