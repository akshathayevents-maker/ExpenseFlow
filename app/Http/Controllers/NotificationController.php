<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService) {}

    public function index(Request $request): View
    {
        $user = auth()->user();

        $notifications = AppNotification::where('user_id', $user->id)
            ->when($request->get('unread') === '1', fn ($q) => $q->whereNull('read_at'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = $this->notificationService->getUnreadCount($user);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markRead(AppNotification $notification): RedirectResponse
    {
        if ($notification->user_id !== auth()->id()) abort(403);

        $this->notificationService->markAsRead($notification);

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead(): RedirectResponse
    {
        $this->notificationService->markAllRead(auth()->user());
        return back()->with('success', 'All notifications marked as read.');
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(auth()->user()),
        ]);
    }
}
