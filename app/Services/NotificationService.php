<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    public function send(
        User    $user,
        string  $type,
        string  $title,
        ?string $body  = null,
        ?string $link  = null,
        array   $data  = []
    ): AppNotification {
        return AppNotification::create([
            'user_id' => $user->id,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'link'    => $link,
            'data'    => $data ?: null,
        ]);
    }

    public function sendToAdmins(
        string  $type,
        string  $title,
        ?string $body  = null,
        ?string $link  = null,
        array   $data  = []
    ): void {
        User::where('role', 'admin')->get()->each(
            fn ($u) => $this->send($u, $type, $title, $body, $link, $data)
        );
    }

    public function sendToManagers(
        string  $type,
        string  $title,
        ?string $body  = null,
        ?string $link  = null,
        array   $data  = []
    ): void {
        User::whereIn('role', ['admin', 'manager'])->get()->each(
            fn ($u) => $this->send($u, $type, $title, $body, $link, $data)
        );
    }

    public function markAsRead(AppNotification $notification): void
    {
        $notification->markRead();
    }

    public function markAllRead(User $user): void
    {
        AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function getUnreadCount(User $user): int
    {
        return AppNotification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    public function getRecent(User $user, int $limit = 8): \Illuminate\Database\Eloquent\Collection
    {
        return AppNotification::where('user_id', $user->id)
            ->latest()
            ->limit($limit)
            ->get();
    }
}
