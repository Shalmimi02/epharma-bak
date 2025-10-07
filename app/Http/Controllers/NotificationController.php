<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Resources\NotificationResource;

class NotificationController extends Controller
{
    public function index() 
    {
        $notifications = Notification::latest();

        if (isset($_GET['req_count'])) return $this->filterByColumn('notifications', $notifications)->count();

        return NotificationResource::collection($this->AsdecodefilterBy('notifications', $notifications));
    }

    public function readAll ($userId)
    {
        $user = User::find($userId);
        $user->unreadNotifications->markAsRead();
    }
}
