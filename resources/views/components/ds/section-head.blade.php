{{--
    Design System: Section Header
    Compact label bar at the top of list/table sections.

    Usage:
        <x-ds.section-head title="Workforce Directory" count="42 members · 1–25 shown">
            (optional right-side slot)
            <x-slot:right>
                <a href="#" class="ef-btn ef-btn-icon"><i class="bi bi-download"></i></a>
            </x-slot:right>
        </x-ds.section-head>
--}}

@props([
    'title' => '',
    'count' => null,
])

<div class="ef-ds-section-head">
    <span class="ef-ds-section-title">{{ $title }}</span>

    <div style="display:flex;align-items:center;gap:10px">
        @if($count)
            <span class="ef-ds-section-count">{{ $count }}</span>
        @endif
        @if(isset($right))
            {{ $right }}
        @endif
    </div>
</div>
