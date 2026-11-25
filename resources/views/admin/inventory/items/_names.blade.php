{{--
    English + Tamil name fields with duplicate protection.
    Live: as the admin types, existing items with the same / similar name or alias are listed.
    Server: InventoryItemController::duplicateWarning() flashes 'duplicates'; saving then needs confirm_duplicate.
    Params: $item (InventoryItem|null)
--}}
@php $dupes = session('duplicates', []); @endphp
<div style="grid-column:1/-1">
    @if($dupes)
        <div class="ef-dup-warn" role="alert">
            <div style="font-weight:700;margin-bottom:6px"><i class="bi bi-exclamation-triangle-fill"></i> Similar items already exist</div>
            <ul style="margin:0 0 8px;padding-left:18px">
                @foreach($dupes as $d)
                    <li><a href="{{ route('admin.inventory.items.show', $d['id']) }}" target="_blank" rel="noopener">{{ $d['name'] }}</a>@if($d['name_ta']) — <span lang="ta">{{ $d['name_ta'] }}</span>@endif
                        <span style="color:var(--ef-muted)">· {{ $d['category'] }} · {{ $d['unit'] }}@if($d['matched']) · matched “{{ $d['matched'] }}”@endif</span></li>
                @endforeach
            </ul>
            <div style="font-size:.84rem;margin-bottom:8px">If this is the same item, add the spelling as an alias on that item instead — one item keeps one stock balance.</div>
            <label style="display:flex;gap:8px;align-items:center;font-weight:600;cursor:pointer;min-height:44px">
                <input type="checkbox" name="confirm_duplicate" value="1"> This is a different item — save anyway
            </label>
        </div>
    @endif
</div>
<div style="grid-column:1/-1">
    <label class="ef-label" for="name">English name <span style="color:var(--ef-danger)">*</span></label>
    <input type="text" id="name" name="name" class="ef-input @error('name') --error @enderror"
           value="{{ old('name', $item?->name) }}" required maxlength="255" autocomplete="off" data-dup-check placeholder="e.g. Toor Dal">
    @error('name') <div class="ef-field-error">{{ $message }}</div> @enderror
</div>
<div style="grid-column:1/-1">
    <label class="ef-label" for="name_ta">Tamil name <span style="color:var(--ef-muted);font-weight:400">(தமிழ் பெயர்)</span></label>
    <input type="text" id="name_ta" name="name_ta" lang="ta" class="ef-input @error('name_ta') --error @enderror"
           value="{{ old('name_ta', $item?->name_ta) }}" maxlength="200" autocomplete="off" data-dup-check placeholder="e.g. துவரம் பருப்பு">
    @error('name_ta') <div class="ef-field-error">{{ $message }}</div> @enderror
    <div class="ef-dup-live" aria-live="polite"></div>
</div>
@include('inventory._item-picker')
@push('styles')
<style>
.ef-dup-warn{background:rgba(216,154,61,.09);border:1px solid rgba(216,154,61,.35);border-radius:var(--ef-radius,12px);padding:12px 14px;color:var(--ef-ink);font-size:.88rem}
.ef-dup-live{font-size:.8rem;color:var(--ef-amber,#b7791f);margin-top:6px}
.ef-dup-live a{font-weight:650}
</style>
@endpush
@push('scripts')
<script>
(function () {
    const box = document.querySelector('.ef-dup-live');
    const self = @json($item?->id);
    const base = @json(url('admin/inventory/items'));
    let t;
    document.querySelectorAll('[data-dup-check]').forEach(inp => inp.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(async () => {
            const qs = [...document.querySelectorAll('[data-dup-check]')].map(i => i.value.trim()).filter(v => v.length >= 2);
            const seen = new Map();
            for (const q of qs) {
                const d = await window.IcItemPicker.query(q);
                (d?.results || []).filter(r => r.id !== self && r.score >= 90).forEach(r => seen.set(r.id, r));
            }
            box.textContent = '';
            if (!seen.size) return;
            box.append('Already in the catalogue: ');
            [...seen.values()].slice(0, 4).forEach((r, i) => {
                if (i) box.append(', ');
                const a = document.createElement('a');
                a.href = base + '/' + r.id; a.target = '_blank'; a.rel = 'noopener';
                a.textContent = r.name + (r.name_ta ? ' — ' + r.name_ta : '');
                box.append(a);
            });
        }, 300);
    }));
})();
</script>
@endpush
