<?php

namespace App\Services\Api\V1\User;

use App\Http\BaseService;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleService extends BaseService
{
    public static function list(Request $request)
    {
        $roles = new Role();
        return $roles->query()
            ->get();
    }
}
