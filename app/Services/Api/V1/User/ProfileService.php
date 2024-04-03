<?php

namespace App\Services\Api\V1\User;

use App\Events\Api\V1\User\Profile\SetAvatarEvent;
use App\Events\Api\V1\User\Profile\UnsetAvatarEvent;
use App\Http\BaseService;
use App\Http\Requests\Api\V1\User\Profile\SetAvatarRequest;
use App\Models\People;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

class ProfileService extends BaseService
{
    public static function setAvatar(SetAvatarRequest $request): User
    {
        /**
         * @var $user User
         */
        $user = auth('api')->user();
        $filenameWithExt = $request->file->getClientOriginalName();
        //Get just filename
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $extension = $request->file->getClientOriginalExtension();
        // Filename to store
        $fileNameToStore = auth('api')->id() . '_' . time() . '.' . $extension;
        // Upload Image
        if (!$request->file('file')->storeAs(User::AVATAR_PATH, $fileNameToStore))
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'error in upload');
        $user->image = $fileNameToStore;
        if (!$user->save())
            abort(Response::HTTP_FAILED_DEPENDENCY, __("api/v1/user/profile.avatar.error_set_avatar"));
        event(new SetAvatarEvent($user));
        return $user;
    }

    public static function unsetAvatar(): User
    {
        /**
         * @var $user User
         */
        $user = auth('api')->user();
        $user->image = User::DEFAULT_USER_AVATAR;
        if (!$user->save())
            abort(Response::HTTP_FAILED_DEPENDENCY, __("api/v1/user/profile.avatar.error_set_avatar"));
        event(new UnsetAvatarEvent($user));
        return $user;
    }
}
