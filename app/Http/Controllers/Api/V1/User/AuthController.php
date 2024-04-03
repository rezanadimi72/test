<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Enums\TokenAbility;
use App\Events\Api\V1\User\Auth\UserLoginEvent;
use App\Http\Resources\Api\V1\User\Auth\LoginResource;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Responses\LogoutResponse;
use Spatie\Permission\Exceptions\UnauthorizedException;

class AuthController extends AuthenticatedSessionController
{
    public function login(LoginRequest $request)
    {
        return $this->loginPipeline($request)->then(function ($request) {
            /**
             * @var User $user
             */
            $user = auth()->user();
            $token = $user->createToken('token', ['*'], now()->addMinute(config('sanctum.expiration')));
            // $refresh_token = $user->createToken('refresh_token', [TokenAbility::REFRESH_TOKEN_ACCESS->value], now()->addMinute(config('sanctum.refresh_expiration')));
            event(new UserLoginEvent($user));
            return LoginResource::collection([
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_in' => $token->accessToken->expires_at->timestamp
                //'refresh_token' => $refresh_token->plainTextToken,
            ]);
        });
    }

    public function logout(Request $request)
    {
        auth('api')->user()->currentAccessToken()->delete();
    }
}
