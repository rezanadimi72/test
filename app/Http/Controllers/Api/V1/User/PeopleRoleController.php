<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Events\Api\V1\User\PeoplePermission\PeopleDeletePermissionEvent;
use App\Events\Api\V1\User\PeopleRole\PeopleDeleteRoleEvent;
use App\Events\Api\V1\User\PeopleRole\PeopleSetRoleEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\User\PeoplePermission\PeoplePermissionResource;
use App\Http\Resources\Api\V1\User\PeopleRole\PeopleRoleResource;
use App\Models\People;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class PeopleRoleController extends Controller
{
    public function index(People $people)
    {
        $this->authorize('getPeopleRole', auth()->user());
        $user = User::find($people->id);
        return PeopleRoleResource::collection($user->roles()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(People $people, Role $role)
    {
        $this->authorize('setPeopleRole', auth()->user());
        $user = User::find($people->id);
        $user->assignRole($role->name);
        event(new PeopleSetRoleEvent($people, $role));
        return PeopleRoleResource::collection($user->roles()->get());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(People $people, Role $role)
    {
        $this->authorize('deletePeopleRole', auth()->user());
        $user = User::find($people->id);
        $user->removeRole($role->name);
        event(new PeopleDeleteRoleEvent($people, $role));
        return PeopleRoleResource::collection($user->roles()->get());
    }
}
