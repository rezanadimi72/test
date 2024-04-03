<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\User\Notification\ListNotificationResource;
use App\Http\Resources\Api\V1\User\Notification\NotificationResource;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return ListNotificationResource::collection(auth()->user()->unreadNotifications);
    }

    /**
     * Display the specified resource.
     */
    public function show(DatabaseNotification $notification)
    {
        $notification->markAsRead();
        return NotificationResource::collection($notification);
    }


}
