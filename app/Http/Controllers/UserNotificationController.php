<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(50)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'title'      => $n->title,
                'body'       => $n->body,
                'url'        => $n->url,
                'read'       => $n->isRead(),
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        $unread = UserNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread'        => $unread,
        ]);
    }

    public function markRead(Request $request, UserNotification $notification)
    {
        abort_if($notification->user_id !== $request->user()->id, 403);
        $notification->update(['read_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function markAllRead(Request $request)
    {
        UserNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return redirect()->route('frontend.profil', ['tab' => 'notifikasi']);
    }
}
