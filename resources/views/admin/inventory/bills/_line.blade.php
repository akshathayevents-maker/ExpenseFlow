{{--
    One bill line on the review page. Desktop: a grid row. Mobile (< 992px): a card.
    Rendered per line and once inside <template> with $i = '__IDX__' for "Add line".
    $line keys: id, item_name, original_item_name, inventory_item_id, quantity, unit, unit_price,
                tax_percent, total, category_id, imported, state (matched|auto|low|none), conf, suggest
--}}
@php
    $n        = is_int($i) ? $i + 1 : '';
    $ro       = $locked || ! empty($line['imported']);
    $sel      = $line['inventory_item_id'] ? ($itemsById[$line['inventory_item_id']] ?? null) : null;
    $state    = $sel ? $line['state'] : (($line['state'] ?? 'none') === 'low' ? 'low' : 'none');
    $origText = $line['original_item_name'] ?: $line['item_name'];
    $err      = fn ($f) => is_int($i) ? $errors->first("items.$i.$f") : null;
    $moreErr  = $err('item_name') || $err('category_id');
    $chip = [
        'matched' => ['ic-chip--ok',   'bi-check2-circle',       'Matched'],
        'auto'    => ['ic-chip--ok',   'bi-magic',               'Auto-matched' . (isset($line['conf']) ? ' ' . $line['conf'] . '%' : '')],
        'low'     => ['ic-chip--warn', 'bi-question-circle',     'Check match'],
        'none'    => ['ic-chip--bad',  'bi-exclamation-circle',  'Not matched'],
    ][$state];
@endphp
<div class="ib-line{{ $moreErr ? ' --more' : '' }}" data-line data-state="{{ $state }}">
    <input type="hidden" name="items[{{ $i }}][id]" value="{{ $line['id'] }}">
    <input type="hidden" name="items[{{ $i }}][original_item_name]" value="{{ $origText }}" data-orig>
    <input type="hidden" name="items[{{ $i }}][inventory_item_id]" value="{{ $line['inventory_item_id'] }}" data-match-id>

    {{-- Bill text + match --}}
    <div class="ib-c-main">
        <div class="ib-line-top">
            <span class="ib-num" data-num>{{ $n }}</span>
            <div class="ib-orig">
                <span class="ib-cap">On bill</span>
                <span class="ib-orig-text" data-orig-text>{{ $origText !== '' ? $origText : 'New line' }}</span>
            </div>
            <span class="ic-chip {{ $chip[0] }} ib-chip" data-chip><i class="bi {{ $chip[1] }}"></i><span>{{ $chip[2] }}</span></span>
        </div>

        <div class="ib-match">
            <i class="bi bi-arrow-return-right ib-match-arrow" aria-hidden="true"></i>
            <div class="ib-match-body">
                <div class="ib-match-view" data-match-view @if(! $sel) hidden @endif>
                    <div class="ib-match-name">
                        <strong data-match-name>{{ $sel->name ?? '' }}</strong>
                        <span class="ib-match-unit" data-match-unit>{{ $sel ? 'Stock unit: ' . $sel->unit : '' }}</span>
                    </div>
                    @unless($ro)
                    <span class="ib-match-acts">
                        <button type="button" class="ib-link" data-act="change">Change</button>
                        <button type="button" class="ib-link ib-link--muted" data-act="unmatch">Unmatch</button>
                    </span>
                    @endunless
                </div>
                @unless($ro)
                <div class="ib-match-search" data-match-search @if($sel) hidden @endif>
                    <div><input type="text" class="ic-input ib-in" data-picker
                           placeholder="Search item (English / தமிழ்)" aria-label="Search inventory item for line {{ $n }}"></div>
                    <div class="ib-match-help">
                        @if($state === 'low' && ! empty($line['suggest']))
                            <span data-suggest-wrap>Did you mean
                                <button type="button" class="ib-link" data-act="suggest"
                                        data-id="{{ $line['suggest']['id'] }}" data-name="{{ $line['suggest']['name'] }}" data-unit="{{ $line['suggest']['unit'] }}">{{ $line['suggest']['name'] }}</button>
                                <span class="ib-faint">({{ $line['conf'] }}% sure)</span>?</span>
                        @else
                            <span class="ib-faint" data-none-help>No match yet — pick an item now or on the purchase form.</span>
                        @endif
                        <button type="button" class="ib-link" data-act="cancel-change" @if(! $sel) hidden @endif>Keep current</button>
                    </div>
                </div>
                @else
                    @unless($sel)<span class="ib-faint">Not matched to a stock item</span>@endunless
                @endunless
                @if($err('inventory_item_id'))<div class="ib-ferr">{{ $err('inventory_item_id') }}</div>@endif
            </div>
        </div>
    </div>

    {{-- Numbers --}}
    <label class="ib-c-qty ib-f">
        <span class="ib-cap">Qty</span>
        <input type="number" name="items[{{ $i }}][quantity]" class="ic-input ib-in ib-num-in" data-qty inputmode="decimal"
               step="0.001" min="0.001" value="{{ $line['quantity'] }}" required @readonly($ro)>
        @if($err('quantity'))<span class="ib-ferr">{{ $err('quantity') }}</span>@endif
    </label>
    <label class="ib-c-unit ib-f">
        <span class="ib-cap">Unit</span>
        <input type="text" name="items[{{ $i }}][unit]" class="ic-input ib-in" value="{{ $line['unit'] }}" placeholder="kg, pcs…"
               maxlength="50" @readonly($ro)>
        @if($err('unit'))<span class="ib-ferr">{{ $err('unit') }}</span>@endif
    </label>
    <label class="ib-c-rate ib-f">
        <span class="ib-cap">Rate ₹</span>
        <input type="number" name="items[{{ $i }}][unit_price]" class="ic-input ib-in ib-num-in" data-rate inputmode="decimal"
               step="0.01" min="0" value="{{ $line['unit_price'] }}" required @readonly($ro)>
        @if($err('unit_price'))<span class="ib-ferr">{{ $err('unit_price') }}</span>@endif
    </label>
    <label class="ib-c-tax ib-f" @if((float) $line['tax_percent'] == 0) data-zero @endif>
        <span class="ib-cap">Tax %</span>
        <input type="number" name="items[{{ $i }}][tax_percent]" class="ic-input ib-in ib-num-in" data-tax inputmode="decimal"
               step="0.01" min="0" max="100" value="{{ $line['tax_percent'] }}" @readonly($ro)>
        @if($err('tax_percent'))<span class="ib-ferr">{{ $err('tax_percent') }}</span>@endif
    </label>
    <label class="ib-c-total ib-f">
        <span class="ib-cap">Amount ₹</span>
        <input type="number" name="items[{{ $i }}][total]" class="ic-input ib-in ib-num-in ib-total-in" data-total inputmode="decimal"
               step="0.01" min="0" value="{{ $line['total'] }}" required @readonly($ro)>
        @if($err('total'))<span class="ib-ferr">{{ $err('total') }}</span>@endif
    </label>

    {{-- Row actions --}}
    <div class="ib-c-act">
        <button type="button" class="ib-icon" data-act="more" aria-expanded="{{ $moreErr ? 'true' : 'false' }}" title="More details" aria-label="More details for line {{ $n }}">
            <i class="bi bi-sliders2"></i><span class="ib-icon-txt">Details</span>
        </button>
        @unless($ro)
        <button type="button" class="ib-icon ib-icon--del" data-act="remove" title="Remove line" aria-label="Remove line {{ $n }}">
            <i class="bi bi-trash3"></i><span class="ib-icon-txt">Remove</span>
        </button>
        @else
            @if(! empty($line['imported']))<i class="bi bi-check-circle-fill" style="color:var(--ef-emerald)" title="Imported"></i>@endif
        @endunless
    </div>

    {{-- Secondary details --}}
    <div class="ib-more" data-more>
        <label class="ib-f">
            <span class="ib-cap">Item name to save</span>
            <input type="text" name="items[{{ $i }}][item_name]" class="ic-input ib-in" data-name maxlength="255"
                   value="{{ $line['item_name'] }}" required @readonly($ro)>
            @if($err('item_name'))<span class="ib-ferr">{{ $err('item_name') }}</span>@endif
        </label>
        <label class="ib-f">
            <span class="ib-cap">Category <span class="ib-faint">(for new items)</span></span>
            <select name="items[{{ $i }}][category_id]" class="ic-select ib-in" @disabled($ro)>
                <option value="">— None —</option>
                @foreach($categories as $catId => $catName)
                    <option value="{{ $catId }}" @selected((string) $line['category_id'] === (string) $catId)>{{ $catName }}</option>
                @endforeach
            </select>
            @if($err('category_id'))<span class="ib-ferr">{{ $err('category_id') }}</span>@endif
        </label>
    </div>

    <div class="ib-removed" data-removed hidden>
        <span><i class="bi bi-trash3"></i> Line removed — it will be dropped when you save.</span>
        <button type="button" class="ib-link" data-act="undo">Undo</button>
    </div>
</div>
