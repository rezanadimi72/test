<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Events\Api\V1\User\PeoplePermission\PeopleDeletePermissionEvent;
use App\Events\Api\V1\User\PeoplePermission\PeopleSetPermissionEvent;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\User\PeoplePermission\PeoplePermissionResource;
use App\Models\People;
use App\Models\Permission;
use App\Models\User;

class PeoplePermissionController extends Controller
{
    public function index(People $people)
    {
        $this->authorize('getPeoplePermission', auth()->user());
        $user = User::find($people->id);
        return PeoplePermissionResource::collection($user->getAllPermissions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(People $people, Permission $permission)
    {
        $this->authorize('setPeoplePermission', auth()->user());
        $user = User::find($people->id);
        $user->givePermissionTo($permission->name);
        event(new PeopleSetPermissionEvent($people, $permission));
        return PeoplePermissionResource::collection($user->getAllPermissions());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(People $people, Permission $permission)
    {
        $this->authorize('deletePeoplePermission', auth()->user());
        $user = User::find($people->id);
        $user->revokePermissionTo($permission->name);
        event(new PeopleDeletePermissionEvent($people, $permission));
        return PeoplePermissionResource::collection($user->getAllPermissions());
    }
}
