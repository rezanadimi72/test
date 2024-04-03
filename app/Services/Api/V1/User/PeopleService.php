<?php

namespace App\Services\Api\V1\User;

use App\Enums\PeopleStatusEnum;
use App\Enums\UserMetaEnum;
use App\Events\Api\V1\User\Auth\ActiveUserEvent;
use App\Events\Api\V1\User\Auth\DeactiveUserEvent;
use App\Events\Api\V1\User\People\PeopleCreatedEvent;
use App\Events\Api\V1\User\People\PeopleResetPasswordEvent;
use App\Events\Api\V1\User\People\PeopleSetAvatarEvent;
use App\Events\Api\V1\User\People\PeopleUnsetAvatarEvent;
use App\Events\Api\V1\User\People\PeopleUpdatedEvent;
use App\Http\Requests\Api\V1\User\People\CreatePeopleRequest;
use App\Http\Requests\Api\V1\User\People\UpdatePeopleRequest;
use App\Http\Requests\Api\V1\User\Profile\SetAvatarRequest;
use App\Http\Resources\Api\V1\User\People\ListPeopleResource;
use App\Models\People;
use App\Models\User;
use App\Models\UserMeta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class PeopleService extends \App\Http\BaseService
{
    public static function list(Request $request)
    {
        $peoples = new User();
        $page = $request->page ?? 1;

        /**
         * @var Builder $data
         */
        $data = $peoples->query()
            ->when($request->role, function ($q) use ($request) {
                return $q->role($request->role);
            })
            ->where($peoples->getTable() . '.id', '!=', auth()->id())
            ->where('email', '!=', 'reza.nadimi88@gmail.com')
            ;
        if (!empty($request->s)) {
            $page = 1;
            $data = $data->whereRaw("name like '%" . $request->s . "%' or family like '%" . $request->s . "%' or email like '%" . $request->s . "%'");
        }
        return $data->paginate($request->perPage, ['*'], 'page', $page);
    }

    public static function store(CreatePeopleRequest $request)
    {
        /**
         * @var User $people
         */
        $people = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'family' => $request->family,
            'password' => Hash::make($request->password),
        ]);
        if (!empty($request->mobile)) UserMeta::dispatch($people->id, UserMetaEnum::MOBILE->value, $request->mobile, UserMetaEnum::MOBILE->label(), false);
        if (!empty($request->telegram)) UserMeta::dispatch($people->id, UserMetaEnum::TELEGRAM->value, $request->telegram, UserMetaEnum::TELEGRAM->label(), false);
        if (!empty($request->whatsapp)) UserMeta::dispatch($people->id, UserMetaEnum::WHATSAPP->value, $request->whatsapp, UserMetaEnum::WHATSAPP->label(), false);
        if (!empty($request->role_id))
            foreach ($request->role_id as $item) {
                $people->assignRole(Role::findById($item, 'api')->name);
            }
        event(new PeopleCreatedEvent($people, auth()->user()));
        return $people;
    }

    public static function update(UpdatePeopleRequest $request, People $user)
    {
        $userParamToEdit = [
            'name',
            'family'
        ];
        foreach ($userParamToEdit as $item) {
            if (!empty($request->{$item}))
                $user->{$item} = $request->{$item};
        }
        if (!empty($request->mobile))
            UserMeta::dispatch($user->id, UserMetaEnum::MOBILE->value, $request->mobile, UserMetaEnum::MOBILE->label(), false);
        if (!empty($request->telegram))
            UserMeta::dispatch($user->id, UserMetaEnum::TELEGRAM->value, $request->telegram, UserMetaEnum::TELEGRAM->label(), false);
        if (!empty($request->whatsapp))
            UserMeta::dispatch($user->id, UserMetaEnum::WHATSAPP->value, $request->whatsapp, UserMetaEnum::WHATSAPP->label(), false);

        if ($user->save())
            event(new PeopleUpdatedEvent($user, auth()->user(), $request->all()));
        return $user;
    }

    public static function setAvatar(SetAvatarRequest $request, People $people)
    {
        $filenameWithExt = $request->file->getClientOriginalName();
        //Get just filename
        $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);
        $extension = $request->file->getClientOriginalExtension();
        // Filename to store
        $fileNameToStore = auth('api')->id() . '_' . time() . '.' . $extension;
        // Upload Image
        if (!$request->file('file')->storeAs(User::AVATAR_PATH, $fileNameToStore))
            abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'error in upload');
        $people->image = $fileNameToStore;
        if (!$people->save())
            abort(Response::HTTP_FAILED_DEPENDENCY, __("api/v1/user/profile.avatar.error_set_avatar"));
        event(new PeopleSetAvatarEvent($people));
        return $people;
    }

    public static function unsetAvatar(People $people)
    {
        $people->image = User::DEFAULT_USER_AVATAR;
        if (!$people->save())
            abort(Response::HTTP_FAILED_DEPENDENCY, __("api/v1/user/profile.avatar.error_set_avatar"));
        event(new PeopleUnsetAvatarEvent($people));
        return $people;
    }

    public static function resetPassword(People $people, string $new_password)
    {
        $people->password = Hash::make($new_password);
        if (!$people->save())
            abort(Response::HTTP_FAILED_DEPENDENCY, __("api/v1/user/people.message.error.reset_password_error"));
        event(new PeopleResetPasswordEvent($people));
        return $people;
    }

    public static function deActive(People $people): People
    {
        $people->status = PeopleStatusEnum::STATUS_DEACTIVE->value;
        if ($people->id != auth()->id() && $people->save()) {
            event(new DeactiveUserEvent($people));
        }
        return $people;
    }

    public static function active(People $people): People
    {
        $people->status = PeopleStatusEnum::STATUS_ACTIVE->value;
        if ($people->id != auth()->id() && $people->save()) {
            event(new ActiveUserEvent($people));
        }
        return $people;
    }
}
