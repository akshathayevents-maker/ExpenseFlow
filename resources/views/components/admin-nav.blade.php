{{--
    Admin / manager sidebar body — rendered from config/admin_navigation.php via App\Support\AdminNavigation.
    One accordion (one group open at a time), the group holding the current page opens automatically.
--}}
@php
    $efNav = \App\Support\AdminNavigation::for(auth()->user());
    $nav = $efNav->build();
@endphp

<button type="button" class="sb-search" data-cmdk-open aria-label="Search pages and actions (Ctrl K)" title="Search · Ctrl K">
    <i class="bi bi-search"></i><span class="sb-label">Search or jump to…</span><kbd class="sb-label">Ctrl K</kbd>
</button>

@if($nav['dashboard'])
    <div class="sidebar-standalone pt-1">
        <a href="{{ $nav['dashboard']['url'] }}" class="nav-link {{ $nav['dashboard']['active'] ? 'active' : '' }}" title="Dashboard"
           @if($nav['dashboard']['active']) aria-current="page" @endif>
            <i class="bi {{ $nav['dashboard']['icon'] }}"></i><span class="sb-label">Dashboard</span>
        </a>
    </div>
@endif

<div id="admin-nav-accordion">
@foreach($nav['groups'] as $key => $g)
    <button class="sidebar-group-btn {{ $g['active'] ? 'has-active' : '' }}" type="button"
            data-bs-toggle="collapse" data-bs-target="#nav-{{ $key }}" aria-controls="nav-{{ $key }}"
            aria-expanded="{{ $g['active'] ? 'true' : 'false' }}" title="{{ $g['label'] }}" data-nav-group="{{ $key }}">
        <i class="bi {{ $g['icon'] }} sb-grp-icon"></i>
        <span class="sb-grp-label">{{ $g['label'] }}</span>
        <i class="bi bi-chevron-down sidebar-chevron"></i>
    </button>
    <div class="collapse sidebar-group-body {{ $g['active'] ? 'show' : '' }}" data-bs-parent="#admin-nav-accordion" id="nav-{{ $key }}">
        @foreach($g['items'] as $it)
            @if($it['section'])<div class="sb-sub">{{ $it['section'] }}</div>@endif
            <a href="{{ $it['url'] }}" class="nav-link {{ $it['active'] ? 'active' : '' }}" title="{{ $it['label'] }}"
               @if($it['active']) aria-current="page" @endif>
                <i class="bi {{ $it['icon'] }}"></i> {{ $it['label'] }}
            </a>
        @endforeach
    </div>
@endforeach
</div>
