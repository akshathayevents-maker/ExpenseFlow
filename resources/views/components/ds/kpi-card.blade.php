{{--
    Design System: KPI Metric Card
    Place inside .ef-ds-kpi-wrap > .ef-ds-kpi-grid

    Usage:
        <div class="ef-ds-kpi-wrap">
            <div class="ef-ds-kpi-grid" style="--kpi-cols:5">
                <x-ds.kpi-card
                    icon="bi-people"
                    label="Total Workforce"
                    value="42"
                    note="employees & managers"
                    accent="emerald"
                    value-color="c-emerald"
                />
                <x-ds.kpi-card
                    icon="bi-check-circle"
                    label="Active"
                    value="38"
                    note="with access"
                    accent="gold"
                    href="{{ route('admin.employees.index', ['status'=>'active']) }}"
                />
            </div>
        </div>

    Props:
        icon        — Bootstrap icon class, e.g. "bi-people"
        label       — Short uppercase label
        value       — Formatted string to display large
        note        — Optional sub-note below value
        accent      — Top-border color: emerald|gold|amber|danger|teal|bluegray|muted
        value-color — Value text color class: c-emerald|c-gold|c-amber|c-danger|c-teal|c-muted
        href        — Optional URL (makes the card a clickable link)
--}}

@props([
    'icon'       => 'bi-graph-up',
    'label'      => '',
    'value'      => '—',
    'note'       => null,
    'accent'     => '',
    'valueColor' => '',
    'href'       => null,
])

@if($href)
<a href="{{ $href }}" class="ef-ds-kpi" data-accent="{{ $accent }}">
@else
<div class="ef-ds-kpi" data-accent="{{ $accent }}">
@endif

    <i class="bi {{ $icon }} ef-ds-kpi-icon"></i>
    <div class="ef-ds-kpi-label">{{ $label }}</div>
    <div class="ef-ds-kpi-value {{ $valueColor }}">{{ $value }}</div>
    @if($note)
        <div class="ef-ds-kpi-note">{{ $note }}</div>
    @endif

@if($href)
</a>
@else
</div>
@endif
