<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\People\CreatePeopleRequest;
use App\Http\Requests\Api\V1\User\People\ResetPasswordRequest;
use App\Http\Requests\Api\V1\User\People\UpdatePeopleRequest;
use App\Http\Requests\Api\V1\User\Profile\SetAvatarRequest;
use App\Http\Resources\Api\V1\User\People\ListPeopleResource;
use App\Http\Resources\Api\V1\User\People\ShowResource;
use App\Http\Resources\Api\V1\User\People\StorePeopleResource;
use App\Models\People;
use App\Models\User;
use App\Services\Api\V1\User\PeopleService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PeopleController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(People::class, 'people', ['except' => ['show']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return ListPeopleResource::collection(PeopleService::list($request));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePeopleRequest $request)
    {
        return StorePeopleResource::collection(PeopleService::store($request));
    }

    /**
     * Display the specified resource.
     */
    public function show(People $people)
    {
        $this->authorize('view', $people);
        return ShowResource::collection($people);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(People $people, UpdatePeopleRequest $request)
    {
        return ShowResource::collection(PeopleService::update($request, $people));
    }

    public function set_avatar(People $people, SetAvatarRequest $request)
    {
        $this->authorize('setPeopleAvatar', auth()->user());
        return ShowResource::collection(PeopleService::setAvatar($request, $people));
    }

    public function unset_avatar(People $people)
    {
        $this->authorize('unsetPeopleAvatar', auth()->user());
        return ShowResource::collection(PeopleService::unsetAvatar($people));
    }

    public function reset_password(People $people, ResetPasswordRequest $request)
    {
        $this->authorize('resetPeoplePassword', auth()->user());
        return ShowResource::collection(PeopleService::resetPassword($people, $request->new_password));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(People $people)
    {
        //
    }

    public function deactive(People $people)
    {
        $this->authorize('deActiveUser', auth()->user());
        return ShowResource::collection(PeopleService::deActive($people));
    }

    public function active(People $people)
    {
        $this->authorize('activeUser', auth()->user());
        return ShowResource::collection(PeopleService::active($people));
    }
}
