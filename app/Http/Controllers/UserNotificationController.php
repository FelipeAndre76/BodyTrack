<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');
        $status = $request->query('status', 'all');

        $baseQuery = UserNotification::where('user_id', Auth::id());

        $notifications = (clone $baseQuery)
            ->when($type !== 'all', fn ($query) => $query->where('type', $type))
            ->when($status === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->when($status === 'read', fn ($query) => $query->whereNotNull('read_at'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $availableTypes = (clone $baseQuery)
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->filter()
            ->values();

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'unread' => (clone $baseQuery)->whereNull('read_at')->count(),
            'social' => (clone $baseQuery)->where('type', 'social')->count(),
            'system' => (clone $baseQuery)->whereIn('type', ['system', 'info'])->count(),
        ];

        return view('notifications.index', compact('notifications', 'availableTypes', 'stats', 'type', 'status'));
    }

    public function latest()
    {
        $notifications = UserNotification::where('user_id', Auth::id())
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon,
                'link_url' => $notification->link_url ?: '#',
                'unread' => is_null($notification->read_at),
                'created_at' => $notification->created_at?->diffForHumans(),
            ]);

        return response()->json([
            'unread_notifications' => UserNotification::where('user_id', Auth::id())->whereNull('read_at')->count(),
            'latest_id' => $notifications->max('id') ?? 0,
            'notifications' => $notifications,
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        UserNotification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (!$request->expectsJson()) {
            return back();
        }

        return response()->json([
            'message' => 'Notificações marcadas como lidas.',
            'unread_notifications' => 0,
        ]);
    }

    public function clear(Request $request)
    {
        UserNotification::where('user_id', Auth::id())->delete();

        if (!$request->expectsJson()) {
            return back();
        }

        return response()->json([
            'message' => 'Notificações limpas.',
            'unread_notifications' => 0,
        ]);
    }

    public function markAsRead(UserNotification $notification)
    {
        $this->authorizeNotification($notification);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back();
    }

    public function destroy(UserNotification $notification)
    {
        $this->authorizeNotification($notification);
        $notification->delete();

        return back();
    }

    private function authorizeNotification(UserNotification $notification): void
    {
        abort_if($notification->user_id !== Auth::id(), 403);
    }
}
