<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        Gate::authorize('notifications.view');

        $notifications = Notification::latest()->paginate(20);

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function unread(): JsonResponse
    {
        Gate::authorize('notifications.view');

        $notifications = Notification::unread()->latest()->limit(8)->get();

        return response()->json([
            'count' => Notification::unread()->count(),
            'items' => $notifications->map(fn (Notification $notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->typeIcon(),
                'color' => $notification->typeColor(),
                'created_at' => $notification->created_at->diffForHumans(),
            ]),
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        Gate::authorize('notifications.view');

        $notification->markAsRead();

        return back();
    }

    public function markAllAsRead(): RedirectResponse
    {
        Gate::authorize('notifications.view');

        Notification::unread()->update(['is_read' => true]);

        return back();
    }
}
