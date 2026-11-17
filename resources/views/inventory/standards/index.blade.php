<x-admin-layout title="Recipe Standards">
@include('inventory._styles')
<div class="ic-wrap">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.home') }}"><i class="bi bi-arrow-left"></i> Store</a>
        <h1>Recipe standards</h1>
    </div>
    <p class="ic-note">Standards are built from what the kitchen actually used. Nothing is compulsory: staff always see and can change the suggested quantities. Saving a change creates a new version; earlier requests keep the version they used.</p>
    <div class="ic-card">
        <h2>Preparations with recorded usage</h2>
        @forelse($preparations as $p)
            <a class="ic-list-card" href="{{ route('inventory.standards.show', ['name' => $p['name']]) }}">
                <div class="ic-list-main"><div class="ic-list-title">{{ $p['name'] }}</div>
                    <div class="ic-list-sub">{{ $p['times'] }} time(s) recorded · about {{ $p['avg_guests'] }} people</div></div>
                @if($p['standard'])<span class="ic-chip ic-chip--ok">Standard v{{ $p['standard']->version }}</span>@else<span class="ic-chip ic-chip--neutral">No standard</span>@endif
            </a>
        @empty
            <p class="ic-note">No usage recorded yet. Standards become available after the kitchen records usage on material requests that have a preparation name and number of people.</p>
        @endforelse
    </div>
    @if($versions->isNotEmpty())
        <div class="ic-card">
            <h2>Version history</h2>
            <table class="ic-table"><thead><tr><th>Recipe</th><th>Version</th><th>Saved</th><th>State</th></tr></thead><tbody>
            @foreach($versions as $v)
                <tr><td data-l="Recipe">{{ $v->name }}</td><td data-l="Version">v{{ $v->version }}</td><td data-l="Saved">{{ $v->created_at->format('d M Y') }}</td>
                    <td data-l="State">{{ $v->superseded_at ? 'Replaced ' . $v->superseded_at->format('d M Y') : 'Current' }}</td></tr>
            @endforeach
            </tbody></table>
        </div>
    @endif
</div>
</x-admin-layout>
