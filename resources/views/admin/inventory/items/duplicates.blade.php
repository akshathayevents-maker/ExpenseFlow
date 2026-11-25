<x-admin-layout title="Possible Duplicate Items">
<x-ds.hero eyebrow="Item Catalogue" title="Possible duplicates"
    :meta="[['icon' => 'bi-intersect', 'text' => 'Items with the same spelling, Tamil name, or an alias equal to another item\'s name']]">
    <x-slot:actions>
        <a href="{{ route('admin.inventory.items.index') }}" class="ef-btn"><i class="bi bi-arrow-left"></i> Item Catalogue</a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card>
    <p style="font-size:.86rem;color:var(--ef-muted);margin:0 0 12px">
        Nothing here is merged automatically. If two rows are the same product, keep the one with stock history,
        deactivate the other, and add its spelling as a search term on the one you keep. Items that already have stock movements are marked.
    </p>
    @forelse($groups as $g)
        <div class="ef-dup-group">
            @foreach($g as $i)
                <a class="ef-dup-row" href="{{ route('admin.inventory.items.show', $i) }}">
                    <span class="ef-dup-name">{{ $i->name }}@if($i->name_ta) <span lang="ta">— {{ $i->name_ta }}</span>@endif</span>
                    <span class="ef-dup-meta">{{ $i->category?->name }} · {{ $i->unit }} · stock {{ rtrim(rtrim(number_format($i->current_stock, 3, '.', ''), '0'), '.') }}
                        · {{ $i->status }}@if($i->transactions_count) · <strong>{{ $i->transactions_count }} movements</strong>@endif</span>
                </a>
            @endforeach
        </div>
    @empty
        <div style="padding:20px;text-align:center;color:var(--ef-muted)"><i class="bi bi-check2-circle" style="font-size:1.6rem;color:var(--ef-emerald)"></i><div>No likely duplicates found.</div></div>
    @endforelse
</x-ds.card>
@push('styles')
<style>
.ef-dup-group{border:1px solid var(--ef-line,#e9e4da);border-radius:12px;margin-bottom:10px;overflow:hidden}
.ef-dup-row{display:flex;flex-wrap:wrap;justify-content:space-between;gap:4px 12px;padding:11px 14px;min-height:44px;color:var(--ef-ink);text-decoration:none;border-top:1px solid var(--ef-line,#e9e4da)}
.ef-dup-row:first-child{border-top:0}
.ef-dup-row:hover{background:rgba(184,137,62,.07)}
.ef-dup-name{font-weight:650}
.ef-dup-name span{font-weight:500;color:var(--ef-muted)}
.ef-dup-meta{font-size:.8rem;color:var(--ef-muted)}
</style>
@endpush
</x-admin-layout>
