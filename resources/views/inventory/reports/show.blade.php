<x-admin-layout :title="$data['title']">
@include('inventory._styles')
@php $fmt = fn ($v) => is_float($v) ? rtrim(rtrim(number_format($v, 3, '.', ','), '0'), '.') : $v; @endphp
<div class="ic-wrap">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.reports.index', array_filter($f)) }}"><i class="bi bi-arrow-left"></i> Loss control</a>
        <h1>{{ $data['title'] }}</h1>
        <a class="ic-btn ic-btn--sm" href="{{ route('inventory.reports.show', [$report] + array_filter($f) + ['format' => 'csv']) }}"><i class="bi bi-download"></i> CSV</a>
    </div>
    <div class="ic-tabs">
        @foreach($reports as $k => $l)<a class="ic-tab {{ $k === $report ? '--on' : '' }}" href="{{ route('inventory.reports.show', [$k] + array_filter($f)) }}">{{ $l }}</a>@endforeach
    </div>
    @include('inventory.reports._filters')
    @if($data['note'])<p class="ic-note"><i class="bi bi-info-circle"></i> {{ $data['note'] }}</p>@endif
    <div class="ic-card" style="padding:8px;overflow-x:auto">
        <table class="ic-table">
            <thead><tr>@foreach($data['columns'] as $l)<th>{{ $l }}</th>@endforeach<th></th></tr></thead>
            <tbody>
            @forelse($data['rows'] as $row)
                <tr style="{{ ! empty($row['_flag']) ? 'background:rgba(216,154,61,.12)' : '' }}">
                    @foreach($data['columns'] as $k => $l)<td data-l="{{ $l }}">{{ $fmt($row[$k] ?? '—') ?? '—' }}</td>@endforeach
                    <td>@if(! empty($row['_link']))<a href="{{ $row['_link'] }}" aria-label="Open"><i class="bi bi-box-arrow-up-right"></i></a>@endif</td>
                </tr>
            @empty
                <tr><td colspan="{{ count($data['columns']) + 1 }}" class="ic-note" style="text-align:center;padding:24px">No data for these filters.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="ic-note">{{ count($data['rows']) }} row(s).</p>
</div>
</x-admin-layout>
