{{-- Names & search terms + purchase units for one item (admin). Params: $item (aliases, unitConversions loaded) --}}
@php
    $generic = $item->aliases->whereNull('vendor_id');
    $supplier = $item->aliases->whereNotNull('vendor_id');
    $units = config('inventory.units');
    $baseDim = $units[$item->unit]['dimension'] ?? null;
    $packUnits = collect($units)->filter(fn ($u, $code) => $u['dimension'] !== $baseDim);
@endphp
<div class="ef-cat-grid">
    <x-ds.card title="Names & search terms">
        <p class="ef-cat-help">Staff can find this item by any of these. Each spelling belongs to one item only.</p>
        <div class="ef-cat-names">
            <div><span class="ef-cat-k">English</span> {{ $item->name }}</div>
            <div><span class="ef-cat-k">தமிழ்</span> @if($item->name_ta)<span lang="ta">{{ $item->name_ta }}</span>@else<a href="{{ route('admin.inventory.items.edit', $item) }}">Add Tamil name</a>@endif</div>
        </div>
        <div class="ef-cat-chips">
            @forelse($generic as $a)
                <form method="POST" action="{{ route('admin.inventory.items.aliases.destroy', [$item, $a]) }}" class="ef-cat-chip" data-confirm="Remove “{{ $a->alias }}” as a search term for {{ $item->name }}?">
                    @csrf @method('DELETE')
                    <span @if($a->language === 'ta') lang="ta" @endif>{{ $a->alias }}</span>
                    <small>{{ ['en' => 'EN', 'ta' => 'TA', 'tanglish' => 'Tanglish'][$a->language] ?? 'EN' }}</small>
                    <button type="submit" aria-label="Remove {{ $a->alias }}"><i class="bi bi-x"></i></button>
                </form>
            @empty
                <span class="ef-cat-help" style="margin:0">No extra spellings yet.</span>
            @endforelse
        </div>
        <form method="POST" action="{{ route('admin.inventory.items.aliases.store', $item) }}" class="ef-cat-add">
            @csrf
            <input type="text" name="alias" class="ef-input @error('alias') --error @enderror" value="{{ old('alias') }}" maxlength="200" required placeholder="e.g. Parupu, பருப்பு" aria-label="New spelling">
            <select name="language" class="ef-select" aria-label="Language">
                @foreach(\App\Models\InventoryItemAlias::LANGUAGES as $k => $l)<option value="{{ $k }}" @selected(old('language', 'tanglish') === $k)>{{ $l }}</option>@endforeach
            </select>
            <button type="submit" class="ef-btn ef-btn-dark"><i class="bi bi-plus-lg"></i> Add</button>
        </form>
        @error('alias') <div class="ef-field-error">{{ $message }}</div> @enderror
        @if($supplier->isNotEmpty())
            <div class="ef-cat-sub">Supplier invoice wording (learned from purchases)</div>
            <ul class="ef-cat-list">
                @foreach($supplier as $a)<li>“{{ $a->alias }}” <span>· {{ $a->vendor?->name }}@if($a->confirmations) · used {{ $a->confirmations }}×@endif</span></li>@endforeach
            </ul>
        @endif
    </x-ds.card>

    <x-ds.card title="Purchase units">
        <p class="ef-cat-help">Stock is kept in <strong>{{ $item->unit }}</strong>. {{ $item->unit }}-family units convert automatically; add a pack unit only with its real size for <em>this</em> item.</p>
        <ul class="ef-cat-list">
            @forelse($item->unitConversions as $c)
                <li style="display:flex;align-items:center;gap:8px;justify-content:space-between">
                    <span>1 <strong>{{ $c->unit }}</strong> = {{ rtrim(rtrim(number_format($c->factor, 3, '.', ''), '0'), '.') }} {{ $item->unit }}@if($c->notes) <span>· {{ $c->notes }}</span>@endif</span>
                    <form method="POST" action="{{ route('admin.inventory.items.units.destroy', [$item, $c]) }}" data-confirm="Stop accepting {{ $c->unit }} for {{ $item->name }}? Past entries keep their quantities.">
                        @csrf @method('DELETE')
                        <button type="submit" class="ef-btn" style="min-height:36px;padding:0 10px" aria-label="Remove {{ $c->unit }}"><i class="bi bi-trash"></i></button>
                    </form>
                </li>
            @empty
                <li><span>No pack units yet.</span></li>
            @endforelse
        </ul>
        <form method="POST" action="{{ route('admin.inventory.items.units.store', $item) }}" class="ef-cat-add">
            @csrf
            <span style="align-self:center">1</span>
            <select name="unit" class="ef-select" aria-label="Pack unit" required>
                @foreach($packUnits as $code => $u)<option value="{{ $code }}" @selected(old('unit') === $code)>{{ $code }}</option>@endforeach
            </select>
            <span style="align-self:center">=</span>
            <input type="number" name="factor" class="ef-input @error('factor') --error @enderror" min="0.001" step="0.001" required value="{{ old('factor') }}" inputmode="decimal" placeholder="25" aria-label="Size in {{ $item->unit }}" style="max-width:110px">
            <span style="align-self:center">{{ $item->unit }}</span>
            <button type="submit" class="ef-btn ef-btn-dark"><i class="bi bi-plus-lg"></i> Save</button>
        </form>
        @error('unit') <div class="ef-field-error">{{ $message }}</div> @enderror
        @error('factor') <div class="ef-field-error">{{ $message }}</div> @enderror
    </x-ds.card>
</div>
@push('styles')
<style>
.ef-cat-grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fit,minmax(min(100%,340px),1fr));margin-top:14px}
.ef-cat-help{font-size:.82rem;color:var(--ef-faint);margin:0 0 10px}
.ef-cat-names{display:grid;gap:4px;margin-bottom:10px;font-size:.92rem}
.ef-cat-k{display:inline-block;min-width:64px;font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-faint)}
.ef-cat-chips{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px}
.ef-cat-chip{display:inline-flex;align-items:center;gap:6px;background:rgba(184,137,62,.1);border:1px solid rgba(184,137,62,.25);border-radius:999px;padding:2px 4px 2px 10px;font-size:.84rem;margin:0}
.ef-cat-chip small{font-size:.64rem;font-weight:700;color:var(--ef-gold)}
.ef-cat-chip button{border:0;background:none;width:32px;height:32px;border-radius:50%;color:var(--ef-muted);cursor:pointer}
.ef-cat-chip button:hover{background:rgba(220,53,69,.1);color:var(--ef-danger)}
.ef-cat-add{display:flex;flex-wrap:wrap;gap:8px}
.ef-cat-add .ef-input{flex:1 1 160px;min-width:0}
.ef-cat-add .ef-select{flex:0 1 auto;width:auto}
.ef-cat-sub{font-size:.72rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-faint);margin:14px 0 6px}
.ef-cat-list{list-style:none;margin:0 0 12px;padding:0;display:grid;gap:6px;font-size:.88rem}
.ef-cat-list span{color:var(--ef-muted)}
</style>
@endpush
<div class="modal fade" id="efCatConfirm" tabindex="-1" aria-labelledby="efCatConfirmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="efCatConfirmTitle">Please confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body" data-msg></div>
            <div class="modal-footer">
                <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="ef-btn ef-btn-dark" data-ok>Remove</button>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    const el = document.getElementById('efCatConfirm');
    if (!el || !window.bootstrap) return;
    const modal = bootstrap.Modal.getOrCreateInstance(el);
    let pending = null;
    document.querySelectorAll('form[data-confirm]').forEach(f => f.addEventListener('submit', e => {
        if (f.dataset.confirmed) return;
        e.preventDefault();
        pending = f;
        el.querySelector('[data-msg]').textContent = f.dataset.confirm;
        modal.show();
    }));
    el.querySelector('[data-ok]').addEventListener('click', () => {
        if (!pending) return;
        pending.dataset.confirmed = '1';
        modal.hide();
        pending.requestSubmit ? pending.requestSubmit() : pending.submit();
    });
})();
</script>
@endpush
