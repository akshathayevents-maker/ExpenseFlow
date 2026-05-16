<x-admin-layout title="Notifications">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Notifications</h4>
        @if($unreadCount > 0)
        <p class="text-muted mb-0 small">{{ $unreadCount }} unread</p>
        @else
        <p class="text-muted mb-0 small">All caught up</p>
        @endif
    </div>
    @if($unreadCount > 0)
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-check2-all me-1"></i> Mark All Read
        </button>
    </form>
    @endif
</div>

{{-- Filter --}}
<div class="mb-3">
    <a href="{{ route('notifications.index') }}"
       class="btn btn-sm {{ !request('unread') ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
    <a href="{{ route('notifications.index', ['unread' => 1]) }}"
       class="btn btn-sm {{ request('unread') === '1' ? 'btn-primary' : 'btn-outline-secondary' }}">
        Unread @if($unreadCount > 0)<span class="badge bg-danger ms-1">{{ $unreadCount }}</span>@endif
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($notifications as $notif)
        @php
            $icons  = \App\Models\AppNotification::typeIcons();
            $icon   = $icons['icon'][$notif->type]  ?? 'bi-bell';
            $iColor = $icons['color'][$notif->type] ?? 'secondary';
        @endphp
        <div class="d-flex align-items-start gap-3 p-3 border-bottom {{ $notif->isRead() ? '' : 'bg-primary-subtle' }}">
            <div class="flex-shrink-0 mt-1">
                <span class="text-{{ $iColor }} fs-5"><i class="bi {{ $icon }}"></i></span>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="fw-semibold small {{ $notif->isRead() ? 'text-muted' : '' }}">
                        {{ $notif->title }}
                    </div>
                    <div class="text-muted" style="font-size:.72rem;white-space:nowrap">
                        {{ $notif->created_at->diffForHumans() }}
                    </div>
                </div>
                <div class="text-muted small mt-1">{{ $notif->body }}</div>
                <div class="mt-2 d-flex gap-2">
                    @if(!$notif->isRead())
                    <form method="POST" action="{{ route('notifications.read', $notif) }}">
                        @csrf @method('PATCH')
                        <button class="btn btn-xs btn-outline-primary py-0 px-2" style="font-size:.75rem">
                            <i class="bi bi-check me-1"></i>
                            {{ $notif->link ? 'View & Mark Read' : 'Mark Read' }}
                        </button>
                    </form>
                    @elseif($notif->link)
                    <a href="{{ $notif->link }}" class="btn btn-xs btn-outline-secondary py-0 px-2" style="font-size:.75rem">
                        <i class="bi bi-arrow-right me-1"></i> View
                    </a>
                    @endif
                </div>
            </div>
            @if(!$notif->isRead())
            <div class="flex-shrink-0">
                <span class="badge bg-primary rounded-pill" style="font-size:.5rem">&nbsp;</span>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
            {{ request('unread') === '1' ? 'No unread notifications.' : 'No notifications yet.' }}
        </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
    <div class="card-footer bg-transparent border-top">{{ $notifications->links() }}</div>
    @endif
</div>
</x-admin-layout>
