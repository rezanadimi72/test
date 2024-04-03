<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\User\Info\MeResource;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    public function me(Request $request)
    {
        return MeResource::collection($request->user());
    }
}
