<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\People\UpdatePeopleRequest;
use App\Http\Requests\Api\V1\User\Profile\SetAvatarRequest;
use App\Http\Resources\Api\V1\User\Info\MeResource;
use App\Http\Resources\Api\V1\User\People\ShowResource;
use App\Models\People;
use App\Services\Api\V1\User\PeopleService;
use App\Services\Api\V1\User\ProfileService;

class ProfileController extends Controller
{
    public function set_avatar(SetAvatarRequest $request)
    {
        $this->authorize('userSetAvatar', auth()->user());
        return MeResource::collection(ProfileService::setAvatar($request));
    }

    public function unset_avatar()
    {
        $this->authorize('userUnsetAvatar', auth()->user());
        return MeResource::collection(ProfileService::unsetAvatar());
    }

    public function show()
    {
        $this->authorize('userShowProfile', auth()->user());
        return MeResource::collection(auth()->user());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePeopleRequest $request)
    {
        $this->authorize('userUpdateProfile', auth()->user());
        $people = People::find(auth()->id());
        return ShowResource::collection(PeopleService::update($request, $people));
    }
}
