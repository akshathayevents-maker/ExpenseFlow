<x-admin-layout title="Review Bill — {{ $bill->invoice_number ?: $bill->original_filename }}">
@include('inventory._styles')

@push('styles')
<style>
/* ── Inventory bill review (mobile-first; --ef-* tokens + shared ic-* inventory styles) ── */
.ib-page{max-width:1320px;margin:0 auto;padding-bottom:8px}
.ib-page input,.ib-page select{scroll-margin-top:96px;scroll-margin-bottom:130px}
.ib-faint{color:var(--ef-faint)}
.ib-cap{display:block;font-size:.7rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--ef-faint);margin-bottom:3px}
.ib-link{background:none;border:0;padding:6px 2px;min-height:36px;color:var(--ef-gold);font-weight:700;font-size:.86rem;cursor:pointer;text-decoration:underline;text-underline-offset:3px}
.ib-link--muted{color:var(--ef-muted)}
.ib-ferr{display:block;color:var(--ef-danger);font-size:.76rem;font-weight:600;margin-top:3px;line-height:1.3}
.ib-in.--bad,.ib-f:has(.ib-ferr) .ib-in{border-color:var(--ef-danger)}

/* header */
.ib-head{display:flex;align-items:flex-start;gap:10px;margin:2px 0 14px}
.ib-back{flex:0 0 auto;width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;border-radius:12px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);text-decoration:none;font-size:1.1rem}
.ib-back:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.ib-title{flex:1;min-width:0}
.ib-title h1{font-size:1.15rem;font-weight:800;letter-spacing:-.01em;color:var(--ef-ink);margin:0;line-height:1.25;overflow-wrap:anywhere}
.ib-sub{display:flex;flex-wrap:wrap;gap:2px 10px;margin-top:3px;font-size:.84rem;color:var(--ef-muted)}
.ib-sub span{overflow-wrap:anywhere}
.ib-head .ic-chip{margin-top:8px}

/* banners */
.ib-banner{display:flex;gap:10px;align-items:flex-start;border-radius:12px;padding:10px 12px;margin-bottom:10px;font-size:.88rem;line-height:1.4}
.ib-banner i{margin-top:2px}
.ib-banner--warn{background:rgba(216,154,61,.12);border:1px solid rgba(216,154,61,.35);color:var(--ef-ink)}
.ib-banner--info{background:rgba(13,148,136,.07);border:1px solid rgba(13,148,136,.22);color:var(--ef-ink)}
.ib-banner--ok{background:rgba(15,123,95,.08);border:1px solid rgba(15,123,95,.22);color:var(--ef-ink)}
.ib-banner a{color:var(--ef-gold);font-weight:700}

/* summary tiles */
.ib-stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-bottom:12px}
.ib-stat{background:var(--ef-surface);border:1px solid var(--ef-border);border-radius:12px;padding:9px 12px;min-width:0}
.ib-stat strong{display:block;font-size:.98rem;font-weight:750;color:var(--ef-ink);overflow-wrap:anywhere;line-height:1.3}
.ib-stat--wide{grid-column:1 / -1}
.ib-stat--accent{border-color:rgba(184,137,62,.45);background:rgba(184,137,62,.06)}
.ib-stat .ib-meter{height:4px;border-radius:4px;background:var(--ef-border);margin-top:6px;overflow:hidden}
.ib-stat .ib-meter i{display:block;height:100%;background:var(--ef-emerald);border-radius:4px;transition:width .2s}
@media (min-width:768px){
  .ib-stats{grid-template-columns:repeat(auto-fit,minmax(150px,1fr))}
  .ib-stat--wide{grid-column:span 2}
  .ib-head h1{font-size:1.35rem}
}

/* layout: document panel beside the review only on wide screens */
.ib-layout{display:block}
.ib-sec{background:var(--ef-surface);border:1px solid var(--ef-border);border-radius:var(--ef-radius);padding:14px;margin-bottom:12px;box-shadow:var(--ef-shadow)}
.ib-sec-h{display:flex;align-items:center;gap:8px;margin:0 0 10px;font-size:1rem;font-weight:750;color:var(--ef-ink)}
.ib-sec-h small{font-weight:600;color:var(--ef-faint);font-size:.8rem}
.ib-sec-h .ib-sp{flex:1}

/* document card */
.ib-doc-strip{display:flex;align-items:center;gap:12px;min-width:0}
.ib-doc-ico{flex:0 0 auto;width:44px;height:44px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.35rem;background:var(--ef-bg-subtle);color:var(--ef-gold)}
.ib-doc-ico.--pdf{color:var(--ef-danger)}
.ib-doc-meta{flex:1;min-width:0}
.ib-doc-name{font-weight:700;color:var(--ef-ink);font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ib-doc-info{font-size:.78rem;color:var(--ef-muted);display:flex;flex-wrap:wrap;gap:0 8px}
.ib-doc-acts{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.ib-doc-acts>*{flex:1 1 130px}
.ib-doc-acts form{display:flex}
.ib-doc-acts form .ic-btn{flex:1}
.ib-btn-arm{border-color:var(--ef-danger)!important;color:var(--ef-danger)!important;background:rgba(200,75,68,.06)!important}
.ib-doc-preview{display:none}
@media (min-width:1400px){
  .ib-layout.--img{display:grid;grid-template-columns:minmax(340px,420px) minmax(0,1fr);gap:16px;align-items:start}
  .ib-layout.--img .ib-doc{position:sticky;top:calc(var(--tb-height,64px) + 14px)}
  .ib-layout.--img .ib-doc-preview{display:block;margin-top:12px;border-radius:10px;background:var(--ef-bg-subtle);height:calc(100vh - var(--tb-height,64px) - 230px);min-height:360px;overflow:auto}
  .ib-doc-preview img{display:block;width:100%;height:auto}
  .ib-doc-preview iframe{width:100%;height:100%;border:0}
}

/* invoice details (collapsible) */
details.ib-sec>summary{list-style:none;display:flex;align-items:center;gap:8px;cursor:pointer;min-height:32px;font-size:1rem;font-weight:750;color:var(--ef-ink)}
details.ib-sec>summary::-webkit-details-marker{display:none}
details.ib-sec>summary .bi-chevron-down{margin-left:auto;transition:transform .15s;color:var(--ef-faint)}
details.ib-sec[open]>summary .bi-chevron-down{transform:rotate(180deg)}
details.ib-sec>summary small{font-weight:600;color:var(--ef-faint);font-size:.8rem}
.ib-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px}
.ib-fields .--wide{grid-column:1 / -1}
@media (min-width:768px){.ib-fields{grid-template-columns:repeat(4,minmax(0,1fr))}.ib-fields .--half{grid-column:span 2}}
.ib-page .ic-input,.ib-page .ic-select{min-height:44px;font-size:16px} /* 16px: no iOS zoom on focus */
.ib-page .ic-input[readonly]{background:var(--ef-bg-subtle);color:var(--ef-muted)}

/* line items */
.ib-lines{container-type:inline-size}
.ib-lines-head{display:none}
.ib-line{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:8px;border:1px solid var(--ef-border);border-radius:14px;padding:12px;margin-bottom:10px;background:var(--ef-surface)}
.ib-c-main,.ib-more,.ib-removed{grid-column:1 / -1}
.ib-c-qty,.ib-c-unit,.ib-c-rate,.ib-c-tax{grid-column:span 2}
.ib-c-total,.ib-c-act{grid-column:span 3}
.ib-c-qty{order:1}.ib-c-unit{order:2}.ib-c-rate{order:3}.ib-c-total{order:4}.ib-c-act{order:5}.ib-c-tax{order:6}.ib-more{order:7}.ib-removed{order:8}
.ib-f{display:block;margin:0;min-width:0}
.ib-num-in{text-align:right;font-variant-numeric:tabular-nums}
.ib-total-in{font-weight:750}
.ib-line-top{display:flex;flex-wrap:wrap;align-items:flex-start;gap:6px 10px}
.ib-num{flex:0 0 auto;width:26px;height:26px;border-radius:50%;background:var(--ef-bg-subtle);color:var(--ef-muted);font-size:.76rem;font-weight:800;display:flex;align-items:center;justify-content:center;margin-top:1px}
.ib-orig{flex:1 1 150px;min-width:0}
.ib-orig-text{display:block;font-weight:750;font-size:.98rem;color:var(--ef-ink);line-height:1.3;overflow-wrap:anywhere}
.ib-orig .ib-cap{margin-bottom:0;font-size:.66rem}
.ib-chip{flex:0 0 auto;font-size:.72rem;padding:3px 9px;margin-left:auto}
.ib-match{display:flex;gap:8px;align-items:flex-start;margin-top:8px;padding:6px 10px;border-radius:10px;background:var(--ef-bg-subtle);border-left:3px solid var(--ef-emerald)}
.ib-line[data-state=low] .ib-match{border-left-color:var(--ef-amber)}
.ib-line[data-state=none] .ib-match{border-left-color:var(--ef-danger)}
.ib-match-arrow{color:var(--ef-faint);margin-top:8px}
.ib-match-body{flex:1;min-width:0}
.ib-match-view{display:flex;align-items:center;gap:2px 10px;flex-wrap:wrap;min-height:36px}
.ib-match-name{flex:1 1 120px;min-width:0;line-height:1.3}
.ib-match-name strong{font-size:.95rem;color:var(--ef-ink);overflow-wrap:anywhere}
.ib-match-unit{font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.ib-match-unit:not(:empty)::before{content:' · '}
.ib-match-acts{display:flex;gap:12px;margin-left:auto}
.ib-match-help{font-size:.8rem;color:var(--ef-muted);margin-top:4px;display:flex;flex-wrap:wrap;align-items:center;gap:0 10px}
.ib-match-help .ib-link{min-height:30px;padding:2px}
.ib-c-act{display:flex;justify-content:flex-end;align-items:flex-end;gap:6px}
.ib-icon{width:44px;height:44px;flex:0 0 auto;display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:11px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-muted);font-size:1.05rem;cursor:pointer}
.ib-icon:hover,.ib-icon[aria-expanded=true]{border-color:var(--ef-gold);color:var(--ef-ink)}
.ib-icon--del:hover{border-color:var(--ef-danger);color:var(--ef-danger)}
.ib-icon-txt{display:none}
.ib-more{display:none;grid-template-columns:minmax(0,1fr);gap:10px;padding-top:10px;border-top:1px dashed var(--ef-border)}
.ib-line.--more .ib-more{display:grid}
.ib-removed{display:flex;align-items:center;justify-content:space-between;gap:10px;font-size:.86rem;color:var(--ef-muted)}
.ib-removed[hidden]{display:none}
.ib-line.--removed{background:var(--ef-bg-subtle);border-style:dashed;padding:6px 12px}
.ib-line.--removed>:not(.ib-removed){display:none!important}
.ib-line.--new{border-color:rgba(184,137,62,.55)}
.ib-empty{text-align:center;padding:26px 12px;color:var(--ef-muted);font-size:.9rem}
.ib-add{width:100%;border-style:dashed}

@container (max-width:819.98px){
  .ib-line:not(.--more) .ib-c-tax[data-zero]{display:none}
}
@container (min-width:520px){
  .ib-line{grid-template-columns:repeat(4,minmax(0,1fr)) auto}
  .ib-c-qty,.ib-c-unit,.ib-c-rate,.ib-c-total,.ib-c-tax{grid-column:span 1}
  .ib-c-act{grid-column:auto}
  .ib-more{grid-template-columns:repeat(2,minmax(0,1fr))}
}
/* wide: a compact table row per line */
@container (min-width:820px){
  .ib-lines-head,.ib-line{grid-template-columns:minmax(0,1fr) 74px 70px 88px 62px 100px 86px;gap:8px}
  .ib-line .ib-icon{width:40px;height:44px}
  .ib-line .ib-chip{margin-left:0}
  .ib-orig .ib-cap{display:none}
  .ib-match-acts{gap:10px}
  .ib-line>.ib-f .ic-input{padding-left:8px;padding-right:8px}
  .ib-lines-head{display:grid;padding:0 4px 8px;border-bottom:2px solid var(--ef-border);font-size:.7rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--ef-faint)}
  .ib-lines-head .r{text-align:right}
  .ib-line{border:0;border-bottom:1px solid var(--ef-border);border-radius:0;margin:0;padding:12px 4px;align-items:start;background:none}
  .ib-line.--new{background:rgba(184,137,62,.05)}
  .ib-line.--removed{border-radius:10px;margin:6px 0}
  .ib-c-main,.ib-c-qty,.ib-c-unit,.ib-c-rate,.ib-c-tax,.ib-c-total{grid-column:auto;order:0}
  .ib-c-total{order:1}.ib-c-act{order:2;grid-column:auto;align-items:flex-start}
  .ib-more{order:3}
  .ib-line>.ib-f>.ib-cap{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0)}
  .ib-match{margin-top:8px}
}

/* totals */
.ib-totals{display:grid;gap:0}
.ib-trow{display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--ef-border);font-size:.92rem}
.ib-trow:last-child{border-bottom:0}
.ib-trow span{color:var(--ef-muted)}
.ib-trow b{font-variant-numeric:tabular-nums;color:var(--ef-ink)}
.ib-trow.--big b{font-size:1.1rem}
.ib-diff{margin-top:10px}

/* sticky action bar (sits at the end of the form; never covers the last line once scrolled to) */
.ib-bar{position:sticky;bottom:0;z-index:30;display:grid;grid-template-columns:minmax(0,1fr) auto auto;align-items:center;gap:8px;margin-top:12px;padding:10px 12px calc(10px + env(safe-area-inset-bottom,0px));background:var(--ef-surface);border:1px solid var(--ef-border);border-radius:16px 16px 0 0;box-shadow:0 -8px 22px rgba(0,0,0,.07)}
.ib-bar-sum{min-width:0;line-height:1.2}
.ib-bar-sum small{display:block;font-size:.68rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--ef-faint)}
.ib-bar-sum b{font-size:1.15rem;font-weight:800;color:var(--ef-ink);font-variant-numeric:tabular-nums}
.ib-dirty{display:inline-flex;align-items:center;gap:4px;margin-left:6px;font-size:.74rem;font-weight:700;color:#7D5218}
.ib-dirty[hidden]{display:none}
.ib-bar .ic-btn{min-height:48px;padding:10px 16px;white-space:nowrap}
.ib-short{display:none}
@media (max-width:519.98px){
  .ib-bar{grid-template-columns:repeat(2,minmax(0,1fr));margin-left:-4px;margin-right:-4px}
  .ib-bar-sum{grid-column:1 / -1;display:flex;align-items:baseline;justify-content:space-between;gap:8px}
  .ib-bar .ic-btn{padding:10px 10px;font-size:.95rem}
  .ib-bar .ib-only{grid-column:1 / -1}
  .ib-long{display:none}.ib-short{display:inline}
}
@media (max-width:359.98px){.ib-bar .ic-btn{font-size:.9rem;gap:5px;padding:10px 6px}.ib-bar .ic-btn .bi{display:none}}

/* OCR debug */
.ib-debug pre{background:var(--ef-bg-subtle);border-radius:8px;padding:10px;font-size:.72rem;max-height:260px;overflow:auto;white-space:pre-wrap;overflow-wrap:anywhere;margin:0}
.ib-debug-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:12px;margin-top:12px}
@media (min-width:768px){.ib-debug-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}

/* full-screen document viewer */
.ib-viewer{width:100vw;height:100dvh;max-width:none;max-height:none;margin:0;padding:0;border:0;background:#141414;color:#fff}
.ib-viewer::backdrop{background:rgba(0,0,0,.6)}
.ib-viewer[open]{display:flex;flex-direction:column}
.ib-viewer-bar{display:flex;align-items:center;gap:8px;padding:calc(8px + env(safe-area-inset-top,0px)) 10px 8px;background:#1d1d1d}
.ib-viewer-bar span{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-weight:650;font-size:.9rem}
.ib-viewer-bar a,.ib-viewer-bar button{min-width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:10px;border:1px solid rgba(255,255,255,.18);background:none;color:#fff;text-decoration:none;font-size:.9rem;padding:0 10px;cursor:pointer}
.ib-viewer-body{flex:1;overflow:auto;display:flex;align-items:flex-start;justify-content:center;-webkit-overflow-scrolling:touch}
.ib-viewer-body img{max-width:100%;height:auto;cursor:zoom-in;margin:auto}
.ib-viewer-body img.--zoom{max-width:none;width:220%;cursor:zoom-out}
.ib-viewer-body iframe{width:100%;height:100%;border:0;background:#fff}
.ib-viewer-hint{font-size:.75rem;color:rgba(255,255,255,.6);text-align:center;padding:6px 10px calc(6px + env(safe-area-inset-bottom,0px))}
</style>
@endpush

@php
    $locked     = $bill->isImported() || (bool) $bill->purchase_id;
    $canRerun   = ! $locked;
    $itemsById  = $inventoryItems->keyBy('id');
    $statusTone = ['secondary' => 'neutral', 'info' => 'info', 'primary' => 'info', 'warning' => 'warn', 'success' => 'ok', 'danger' => 'bad'];
    $statusChip = $statusTone[\App\Models\InventoryBillUpload::statuses()[$bill->status] ?? 'secondary'] ?? 'neutral';
    $statusLabel = \App\Models\InventoryBillUpload::statusLabels()[$bill->status] ?? ucfirst($bill->status);
    $confidence = $bill->extracted_json['ocr_confidence'] ?? null;
    $confPct    = $confidence !== null ? (int) round($confidence * 100) : null;
    $money      = fn ($v) => '₹' . number_format((float) $v, 2);

    $savedById = $bill->items->pluck('inventory_item_id', 'id');
    $makeLine = function (array $r) use ($matchInfo, $savedById) {
        $id   = $r['id'] ?? null;
        $sel  = ($r['inventory_item_id'] ?? null) ?: null;
        $info = $id ? ($matchInfo[$id] ?? null) : null;
        if ($sel) {
            $state = ($id && (string) $savedById[$id] === (string) $sel) ? 'matched'
                   : (($info && (string) $info['item_id'] === (string) $sel) ? 'auto' : 'matched');
        } else {
            $state = $info ? 'low' : 'none';
        }
        $num = fn ($v, $d) => $v === null || $v === '' ? $d : (is_numeric($v) ? (string) (0 + $v) : $v);
        return [
            'id'                 => $id,
            'item_name'          => $r['item_name'] ?? '',
            'original_item_name' => $r['original_item_name'] ?? null,
            'inventory_item_id'  => $sel,
            'quantity'           => $num($r['quantity'] ?? null, ''),
            'unit'               => $r['unit'] ?? '',
            'unit_price'         => $num($r['unit_price'] ?? null, '0'),
            'tax_percent'        => $num($r['tax_percent'] ?? null, '0'),
            'total'              => $num($r['total'] ?? null, '0'),
            'category_id'        => $r['category_id'] ?? null,
            'imported'           => (bool) ($r['imported'] ?? false),
            'state'              => $state,
            'conf'               => $info['confidence'] ?? null,
            'suggest'            => $info && ! $sel ? ['id' => $info['item_id'], 'name' => $info['name'], 'unit' => $info['unit']] : null,
        ];
    };

    $oldItems = old('items');
    $lines = [];
    if (is_array($oldItems)) {
        foreach ($oldItems as $k => $r) { $lines[(int) $k] = $makeLine((array) $r); }
    } else {
        foreach ($bill->items->values() as $k => $row) {
            $lines[$k] = $makeLine([
                'id' => $row->id, 'item_name' => $row->item_name, 'original_item_name' => $row->original_item_name,
                'inventory_item_id' => $matchMap[$row->id] ?? null, 'quantity' => $row->quantity, 'unit' => $row->unit,
                'unit_price' => $row->unit_price, 'tax_percent' => $row->tax_percent, 'total' => $row->total,
                'category_id' => $row->category_id, 'imported' => $row->imported,
            ]);
        }
    }
    $nextIdx      = $lines ? max(array_keys($lines)) + 1 : 0;
    $matchedCount = collect($lines)->filter(fn ($l) => $l['inventory_item_id'])->count();
    $linesTotal   = collect($lines)->sum(fn ($l) => (float) $l['total']);
    $billTotal    = (float) old('total_amount', $bill->total_amount);
    $headerErrors = collect(['vendor_name', 'invoice_number', 'invoice_date', 'gst_number', 'subtotal', 'tax_amount', 'total_amount', 'notes'])
        ->contains(fn ($f) => $errors->has($f));
    $title = $bill->invoice_number ? 'Bill ' . $bill->invoice_number : 'Review bill';
@endphp

<div class="ib-page">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="ib-head">
        <a href="{{ route('admin.inventory.bills.index') }}" class="ib-back" aria-label="Back to bills"><i class="bi bi-arrow-left"></i></a>
        <div class="ib-title">
            <h1>{{ $title }}</h1>
            <div class="ib-sub">
                @if($bill->vendor_name)<span><i class="bi bi-shop me-1"></i>{{ $bill->vendor_name }}</span>@endif
                @if($bill->invoice_date)<span><i class="bi bi-calendar3 me-1"></i>{{ $bill->invoice_date->format('d M Y') }}</span>@endif
                @if(! $bill->vendor_name && ! $bill->invoice_date)<span>{{ $bill->original_filename }}</span>@endif
            </div>
        </div>
        <span class="ic-chip ic-chip--{{ $statusChip }}">{{ $statusLabel }}</span>
    </div>

    {{-- ── Banners ────────────────────────────────────────────────────────── --}}
    @if($duplicate)
        <div class="ib-banner ib-banner--warn" role="alert">
            <i class="bi bi-exclamation-triangle-fill" style="color:var(--ef-amber)"></i>
            <div><strong>Possible duplicate.</strong> Invoice <strong>{{ $bill->invoice_number }}</strong> from {{ $bill->vendor_name }}
                was already uploaded on {{ $duplicate->created_at->format('d M Y') }}.
                <a href="{{ route('admin.inventory.bills.show', $duplicate) }}">View existing →</a></div>
        </div>
    @endif
    @if($bill->purchase_id)
        <div class="ib-banner ib-banner--ok">
            <i class="bi bi-link-45deg" style="color:var(--ef-emerald)"></i>
            <div>A purchase was created from this bill. Corrections, approval and goods receipt now happen on the purchase.
                <a href="{{ route('inventory.purchases.show', $bill->purchase_id) }}">Open purchase →</a></div>
        </div>
    @elseif($bill->isImported())
        <div class="ib-banner ib-banner--ok">
            <i class="bi bi-check-circle-fill" style="color:var(--ef-emerald)"></i>
            <div>This bill was imported into stock ({{ $bill->items->where('imported', true)->count() }} item(s)). Reviewed by {{ optional($bill->reviewer)->name ?? '—' }}.</div>
        </div>
    @endif
    @if($bill->extracted_json && ($bill->extracted_json['ocr_message'] ?? null))
        <div class="ib-banner ib-banner--info">
            <i class="bi bi-info-circle" style="color:var(--ef-teal)"></i>
            <div>{{ $bill->extracted_json['ocr_message'] }}</div>
        </div>
    @endif
    @if($errors->any())
        <div class="ic-err" role="alert">
            <strong><i class="bi bi-exclamation-octagon me-1"></i>Please fix {{ $errors->count() === 1 ? 'the highlighted field' : 'the ' . $errors->count() . ' highlighted fields' }} and save again.</strong>
        </div>
    @endif

    {{-- ── Summary tiles ──────────────────────────────────────────────────── --}}
    <div class="ib-stats">
        <div class="ib-stat ib-stat--accent"><span class="ib-cap">Bill total</span><strong data-stat-bill>{{ $billTotal > 0 ? $money($billTotal) : 'Not captured' }}</strong></div>
        <div class="ib-stat"><span class="ib-cap">Items</span><strong data-stat-count>{{ count($lines) }}</strong></div>
        <div class="ib-stat"><span class="ib-cap">Matched</span>
            <strong data-stat-matched>{{ $matchedCount }} of {{ count($lines) }}</strong>
            <div class="ib-meter"><i data-stat-meter style="width:{{ count($lines) ? round($matchedCount / count($lines) * 100) : 0 }}%"></i></div>
        </div>
        @if($confPct !== null)
            <div class="ib-stat"><span class="ib-cap">OCR read quality</span>
                <strong style="color:{{ $confPct >= 80 ? 'var(--ef-emerald)' : ($confPct >= 60 ? 'var(--ef-amber)' : 'var(--ef-danger)') }}">{{ $confPct }}%</strong></div>
        @endif
    </div>

    <div class="ib-layout {{ $bill->isImage() ? '--img' : '' }}">

        {{-- ── Original document ──────────────────────────────────────────── --}}
        <aside class="ib-doc">
            <section class="ib-sec" aria-label="Uploaded bill">
                <div class="ib-doc-strip">
                    <span class="ib-doc-ico {{ $bill->isPdf() ? '--pdf' : '' }}"><i class="bi {{ $bill->isPdf() ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i></span>
                    <div class="ib-doc-meta">
                        <div class="ib-doc-name" title="{{ $bill->original_filename }}">{{ $bill->original_filename }}</div>
                        <div class="ib-doc-info">
                            <span>Read by {{ $bill->ocr_provider ?? 'manual entry' }}</span>
                            <span>Uploaded {{ $bill->created_at->diffForHumans() }}{{ $bill->uploader ? ' by ' . $bill->uploader->name : '' }}</span>
                        </div>
                    </div>
                </div>
                <div class="ib-doc-acts">
                    @if($bill->isImage())
                        <button type="button" class="ic-btn ic-btn--sm" data-open-viewer><i class="bi bi-arrows-fullscreen"></i> View bill</button>
                    @else
                        <a href="{{ route('admin.inventory.bills.file', $bill) }}" target="_blank" rel="noopener" class="ic-btn ic-btn--sm"><i class="bi bi-eye"></i> Open PDF</a>
                    @endif
                    @if($canRerun)
                        <form method="POST" action="{{ route('admin.inventory.bills.rerun-ocr', $bill) }}" id="rerunOcrForm">
                            @csrf
                            <button type="submit" class="ic-btn ic-btn--sm" id="btnRerunOcr" data-no-loading title="Scan the bill again — replaces every line below">
                                <i class="bi bi-arrow-repeat"></i> <span data-rerun-label>Re-run OCR</span>
                            </button>
                        </form>
                    @endif
                </div>
                @if($bill->isImage())
                <div class="ib-doc-preview" data-doc-preview data-kind="{{ $bill->isPdf() ? 'pdf' : 'image' }}" data-src="{{ route('admin.inventory.bills.file', $bill) }}"></div>
                @endif
            </section>
        </aside>

        {{-- ── Review form ────────────────────────────────────────────────── --}}
        <div class="ib-main">
            <form method="POST" action="{{ route('admin.inventory.bills.update', $bill) }}" id="reviewForm" data-ic-form>
                @csrf @method('PUT')

                {{-- Invoice details --}}
                <details class="ib-sec" id="ibDetails" @if($headerErrors) open data-force-open @endif>
                    <summary><i class="bi bi-receipt" style="color:var(--ef-gold)"></i> Invoice details
                        <small>{{ $locked ? 'read only' : 'tap to correct' }}</small><i class="bi bi-chevron-down"></i></summary>
                    <div class="ib-fields">
                        <label class="ib-f --half --wide"><span class="ib-cap">Supplier</span>
                            <input type="text" name="vendor_name" class="ic-input ib-in" maxlength="255" value="{{ old('vendor_name', $bill->vendor_name) }}" @readonly($locked)>
                            @error('vendor_name')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                        <label class="ib-f"><span class="ib-cap">Invoice no.</span>
                            <input type="text" name="invoice_number" class="ic-input ib-in" maxlength="100" value="{{ old('invoice_number', $bill->invoice_number) }}" @readonly($locked)>
                            @error('invoice_number')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                        <label class="ib-f"><span class="ib-cap">Invoice date</span>
                            <input type="date" name="invoice_date" class="ic-input ib-in" value="{{ old('invoice_date', $bill->invoice_date?->format('Y-m-d')) }}" @readonly($locked)>
                            @error('invoice_date')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                        <label class="ib-f"><span class="ib-cap">GST no.</span>
                            <input type="text" name="gst_number" class="ic-input ib-in" maxlength="20" value="{{ old('gst_number', $bill->gst_number) }}" @readonly($locked)>
                            @error('gst_number')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                        <label class="ib-f"><span class="ib-cap">Subtotal ₹</span>
                            <input type="number" name="subtotal" class="ic-input ib-in ib-num-in" step="0.01" min="0" inputmode="decimal" value="{{ old('subtotal', number_format((float) $bill->subtotal, 2, '.', '')) }}" @readonly($locked)>
                            @error('subtotal')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                        <label class="ib-f"><span class="ib-cap">Tax ₹</span>
                            <input type="number" name="tax_amount" class="ic-input ib-in ib-num-in" step="0.01" min="0" inputmode="decimal" value="{{ old('tax_amount', number_format((float) $bill->tax_amount, 2, '.', '')) }}" @readonly($locked)>
                            @error('tax_amount')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                        <label class="ib-f"><span class="ib-cap">Bill total ₹</span>
                            <input type="number" name="total_amount" class="ic-input ib-in ib-num-in ib-total-in" data-bill-total step="0.01" min="0" inputmode="decimal" value="{{ old('total_amount', number_format((float) $bill->total_amount, 2, '.', '')) }}" @readonly($locked)>
                            @error('total_amount')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                        <label class="ib-f --wide"><span class="ib-cap">Notes</span>
                            <input type="text" name="notes" class="ic-input ib-in" maxlength="1000" value="{{ old('notes', $bill->notes) }}" @readonly($locked)>
                            @error('notes')<span class="ib-ferr">{{ $message }}</span>@enderror</label>
                    </div>
                </details>

                {{-- Line items --}}
                <section class="ib-sec" aria-labelledby="ibLinesH">
                    <h2 class="ib-sec-h" id="ibLinesH"><i class="bi bi-list-check" style="color:var(--ef-gold)"></i> Line items
                        <small data-lines-count>{{ count($lines) }} {{ \Illuminate\Support\Str::plural('line', count($lines)) }}</small></h2>
                    @unless($locked)
                        <p class="ic-hint" style="margin:-4px 0 10px">Check each line is linked to the right stock item. Fix quantity or rate where the scan misread them.</p>
                    @endunless

                    <div class="ib-lines">
                        <div class="ib-lines-head" aria-hidden="true">
                            <span>Bill item → stock item</span><span class="r">Qty</span><span>Unit</span><span class="r">Rate ₹</span><span class="r">Tax %</span><span class="r">Amount ₹</span><span></span>
                        </div>
                        <div id="ibLines">
                            @foreach($lines as $i => $line)
                                @include('admin.inventory.bills._line', ['i' => $i, 'line' => $line])
                            @endforeach
                        </div>
                        <div class="ib-empty" data-empty @if(count($lines)) hidden @endif>
                            <i class="bi bi-inbox" style="font-size:1.6rem;color:var(--ef-faint)"></i>
                            <div>No lines were read from this bill.{{ $locked ? '' : ' Add them by hand below.' }}</div>
                        </div>
                    </div>

                    @unless($locked)
                        <button type="button" class="ic-btn ic-btn--sm ib-add" id="btnAddRow" style="margin-top:10px"><i class="bi bi-plus-lg"></i> Add line</button>
                    @endunless
                </section>

                {{-- Totals --}}
                <section class="ib-sec" aria-labelledby="ibTotH">
                    <h2 class="ib-sec-h" id="ibTotH"><i class="bi bi-calculator" style="color:var(--ef-gold)"></i> Totals</h2>
                    <div class="ib-totals">
                        @if((float) $bill->subtotal > 0)
                            <div class="ib-trow"><span>Subtotal on bill</span><b>{{ $money($bill->subtotal) }}</b></div>
                        @endif
                        @if((float) $bill->tax_amount > 0)
                            <div class="ib-trow"><span>Tax on bill</span><b>{{ $money($bill->tax_amount) }}</b></div>
                        @endif
                        <div class="ib-trow"><span>Total of lines</span><b data-sum-lines>{{ $money($linesTotal) }}</b></div>
                        <div class="ib-trow --big"><span>Bill total</span><b data-sum-bill>{{ $billTotal > 0 ? $money($billTotal) : '—' }}</b></div>
                    </div>
                    <div class="ib-banner ib-banner--warn ib-diff" data-diff @if($billTotal <= 0 || abs($billTotal - $linesTotal) < 0.5) hidden @endif>
                        <i class="bi bi-exclamation-triangle-fill" style="color:var(--ef-amber)"></i>
                        <div data-diff-text>Lines add up to {{ $money($linesTotal) }} but the bill total is {{ $money($billTotal) }}
                            (difference {{ $money(abs($billTotal - $linesTotal)) }}). Check for a misread or missing line.</div>
                    </div>
                    <div class="ib-banner ib-banner--info ib-diff" data-nototal @if($billTotal > 0) hidden @endif>
                        <i class="bi bi-info-circle" style="color:var(--ef-teal)"></i>
                        <div>The bill total was not read. Enter it under Invoice details so the lines can be checked against it.</div>
                    </div>
                </section>

                {{-- Sticky actions --}}
                <div class="ib-bar ic-sticky" role="region" aria-label="Bill actions">
                    <div class="ib-bar-sum">
                        <small>Total of lines</small>
                        <span><b data-sum-lines>{{ $money($linesTotal) }}</b><span class="ib-dirty" data-dirty hidden><i class="bi bi-dot"></i>Unsaved</span></span>
                    </div>
                    @if(! $locked)
                        <button type="submit" data-no-loading class="ic-btn {{ $bill->canImport() ? '' : 'ib-only ic-btn--dark' }}"><i class="bi bi-check2"></i> <span class="ib-long">Save review</span><span class="ib-short">Save</span></button>
                        @if($bill->canImport())
                            <button type="submit" name="next" value="purchase" data-no-loading class="ic-btn ic-btn--gold">
                                <i class="bi bi-cart-check"></i> <span class="ib-long">Save &amp; create purchase</span><span class="ib-short">Create purchase</span>
                            </button>
                        @endif
                    @elseif($bill->purchase_id)
                        <a href="{{ route('inventory.purchases.show', $bill->purchase_id) }}" class="ic-btn ic-btn--dark ib-only" style="grid-column:-2 / -1"><i class="bi bi-receipt"></i> Open purchase</a>
                    @else
                        <a href="{{ route('admin.inventory.items.index') }}" class="ic-btn ib-only" style="grid-column:-2 / -1"><i class="bi bi-boxes"></i> View inventory</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ── OCR debug ──────────────────────────────────────────────────────── --}}
    @if($bill->extracted_json)
        @php $json = $bill->extracted_json; @endphp
        <details class="ib-sec ib-debug" style="margin-top:12px">
            <summary><i class="bi bi-bug" style="color:var(--ef-faint)"></i> OCR debug info
                <small>{{ $bill->ocr_provider ?? 'tesseract' }}{{ $confPct !== null ? ', ' . $confPct . '%' : '' }}</small><i class="bi bi-chevron-down"></i></summary>
            <div class="ib-debug-grid">
                <div>
                    <span class="ib-cap">Extracted invoice fields</span>
                    <pre>{{ json_encode([
                        'vendor_name'    => $json['vendor_name']    ?? null,
                        'invoice_number' => $json['invoice_number'] ?? null,
                        'invoice_date'   => $json['invoice_date']   ?? null,
                        'gst_number'     => $json['gst_number']     ?? null,
                        'subtotal'       => $json['subtotal']       ?? 0,
                        'tax_amount'     => $json['tax_amount']     ?? 0,
                        'total_amount'   => $json['total_amount']   ?? 0,
                        'ocr_provider'   => $json['ocr_provider']   ?? null,
                        'ocr_confidence' => $json['ocr_confidence'] ?? null,
                        'item_count'     => count($json['items']    ?? []),
                    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                <div>
                    <span class="ib-cap">OCR raw text</span>
                    <pre>{{ $json['full_text'] ?? '(not stored)' }}</pre>
                </div>
            </div>
        </details>
    @endif

    {{-- ── Full-screen viewer (images) ───────────────────────────────────── --}}
    @if($bill->isImage())
        <dialog class="ib-viewer" id="ibViewer" aria-label="Bill image">
            <div class="ib-viewer-bar">
                <span>{{ $bill->original_filename }}</span>
                <a href="{{ route('admin.inventory.bills.file', $bill) }}" target="_blank" rel="noopener" aria-label="Open original in new tab"><i class="bi bi-box-arrow-up-right"></i></a>
                <button type="button" data-close-viewer aria-label="Close"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="ib-viewer-body"><img alt="Uploaded bill" data-viewer-img></div>
            <div class="ib-viewer-hint">Tap the bill to zoom · scroll to move around</div>
        </dialog>
    @endif

    @unless($locked)
        <template id="ibLineTpl">
            @include('admin.inventory.bills._line', ['i' => '__IDX__', 'line' => [
                'id' => null, 'item_name' => '', 'original_item_name' => null, 'inventory_item_id' => null,
                'quantity' => '1', 'unit' => '', 'unit_price' => '0', 'tax_percent' => '0', 'total' => '0',
                'category_id' => null, 'imported' => false, 'state' => 'none', 'conf' => null, 'suggest' => null,
            ]])
        </template>
    @endunless
</div>

@include('inventory._item-picker')
@push('scripts')
<script>
(function () {
    const form   = document.getElementById('reviewForm');
    const list   = document.getElementById('ibLines');
    const locked = @json($locked);
    const fmt    = n => '₹' + (Number(n) || 0).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    const $$     = (sel, root) => [...(root || document).querySelectorAll(sel)];
    let nextIdx  = {{ $nextIdx }};

    /* ── Invoice details open on wide screens / when they hold an error ── */
    const details = document.getElementById('ibDetails');
    if (details && matchMedia('(min-width: 992px)').matches) details.open = true;

    /* ── Totals, counts, matched meter ── */
    const liveLines = () => $$('[data-line]', list).filter(l => !l.classList.contains('--removed'));
    function refresh() {
        const lines = liveLines();
        const sum = lines.reduce((s, l) => s + (parseFloat(l.querySelector('[data-total]')?.value) || 0), 0);
        const matched = lines.filter(l => l.querySelector('[data-match-id]').value).length;
        const billIn = form.querySelector('[data-bill-total]');
        const bill = parseFloat(billIn?.value) || 0;
        $$('[data-sum-lines]').forEach(e => e.textContent = fmt(sum));
        $$('[data-sum-bill], [data-stat-bill]').forEach(e => e.textContent = bill > 0 ? fmt(bill) : (e.matches('[data-stat-bill]') ? 'Not captured' : '—'));
        $$('[data-stat-count]').forEach(e => e.textContent = lines.length);
        $$('[data-stat-matched]').forEach(e => e.textContent = matched + ' of ' + lines.length);
        $$('[data-stat-meter]').forEach(e => e.style.width = (lines.length ? Math.round(matched / lines.length * 100) : 0) + '%');
        $$('[data-lines-count]').forEach(e => e.textContent = lines.length + (lines.length === 1 ? ' line' : ' lines'));
        const diff = Math.abs(bill - sum);
        const d = document.querySelector('[data-diff]');
        if (d) {
            d.hidden = !(bill > 0 && diff >= 0.5);
            d.querySelector('[data-diff-text]').textContent =
                'Lines add up to ' + fmt(sum) + ' but the bill total is ' + fmt(bill) + ' (difference ' + fmt(diff) + '). Check for a misread or missing line.';
        }
        const nt = document.querySelector('[data-nototal]');
        if (nt) nt.hidden = bill > 0;
        const empty = document.querySelector('[data-empty]');
        if (empty) empty.hidden = lines.length > 0;
        lines.forEach((l, i) => { const n = l.querySelector('[data-num]'); if (n) n.textContent = i + 1; });
    }

    if (locked) { refresh(); return; }

    /* ── Dirty marker ── */
    const dirty = () => $$('[data-dirty]').forEach(e => e.hidden = false);
    form.addEventListener('input', dirty);
    form.addEventListener('change', dirty);
    form.querySelector('[data-bill-total]')?.addEventListener('input', refresh);

    /* ── Match state helpers ── */
    const CHIPS = {
        matched: ['ic-chip--ok', 'bi-check2-circle', 'Matched'],
        none:    ['ic-chip--bad', 'bi-exclamation-circle', 'Not matched'],
    };
    function setChip(line, state) {
        line.dataset.state = state;
        const c = line.querySelector('[data-chip]'), [cls, icon, text] = CHIPS[state];
        c.className = 'ic-chip ' + cls + ' ib-chip';
        c.innerHTML = '<i class="bi ' + icon + '"></i><span></span>';
        c.querySelector('span').textContent = text;
    }
    function showSearch(line, on) {
        line.querySelector('[data-match-view]').hidden = on;
        line.querySelector('[data-match-search]').hidden = !on;
        line.querySelector('[data-act="cancel-change"]').hidden = !line.querySelector('[data-match-id]').value;
    }
    function setMatch(line, item) {
        line.querySelector('[data-match-id]').value = item ? item.id : '';
        if (item) {
            line.querySelector('[data-match-name]').textContent = item.name;
            line.querySelector('[data-match-unit]').textContent = item.unit ? 'Stock unit: ' + item.unit : '';
            const unitIn = line.querySelector('input[name$="[unit]"]');
            if (unitIn && !unitIn.value && item.unit) unitIn.value = item.unit;
            line.querySelector('[data-suggest-wrap]')?.remove();
        }
        setChip(line, item ? 'matched' : 'none');
        showSearch(line, !item);
        const p = line.querySelector('[data-picker]');
        if (p) p.value = '';
        dirty(); refresh();
    }

    /* ── Per-line wiring ── */
    function initLine(line) {
        const picker = line.querySelector('[data-picker]');
        if (picker && window.IcItemPicker) {
            window.IcItemPicker.attach(picker, { onPick: r => setMatch(line, {id: r.id, name: r.name, unit: r.unit}) });
        }
        ['[data-qty]', '[data-rate]', '[data-tax]'].forEach(sel => line.querySelector(sel)?.addEventListener('input', () => {
            const q = parseFloat(line.querySelector('[data-qty]').value) || 0;
            const r = parseFloat(line.querySelector('[data-rate]').value) || 0;
            const t = parseFloat(line.querySelector('[data-tax]').value) || 0;
            line.querySelector('[data-total]').value = (q * r * (1 + t / 100)).toFixed(2);
            refresh();
        }));
        line.querySelector('[data-total]')?.addEventListener('input', refresh);
        const tax = line.querySelector('[data-tax]');
        tax?.addEventListener('change', () => tax.closest('.ib-c-tax').toggleAttribute('data-zero', !(parseFloat(tax.value) > 0)));
        // a hand-added line: what the user types is also the "on bill" text
        if (line.classList.contains('--new')) {
            const name = line.querySelector('[data-name]'), orig = line.querySelector('[data-orig]'), txt = line.querySelector('[data-orig-text]');
            name.addEventListener('input', () => { orig.value = name.value; txt.textContent = name.value.trim() || 'New line'; });
        }
    }

    list.addEventListener('click', e => {
        const btn = e.target.closest('[data-act]');
        if (!btn) return;
        const line = btn.closest('[data-line]');
        switch (btn.dataset.act) {
            case 'change':
                showSearch(line, true);
                line.querySelector('[data-picker]')?.focus();
                break;
            case 'cancel-change':
                showSearch(line, false);
                break;
            case 'unmatch':
                setMatch(line, null);
                break;
            case 'suggest':
                setMatch(line, {id: btn.dataset.id, name: btn.dataset.name, unit: btn.dataset.unit});
                break;
            case 'more': {
                const open = line.classList.toggle('--more');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                break;
            }
            case 'remove':
                line.classList.add('--removed');
                line.querySelector('[data-removed]').hidden = false;
                $$('input, select', line).forEach(f => { if (!f.disabled) { f.disabled = true; f.dataset.offByRemove = '1'; } });
                dirty(); refresh();
                line.querySelector('[data-act="undo"]').focus();
                break;
            case 'undo':
                line.classList.remove('--removed');
                line.querySelector('[data-removed]').hidden = true;
                $$('[data-off-by-remove]', line).forEach(f => { f.disabled = false; delete f.dataset.offByRemove; });
                refresh();
                break;
        }
    });

    document.getElementById('btnAddRow')?.addEventListener('click', () => {
        const tpl = document.getElementById('ibLineTpl').innerHTML.replaceAll('__IDX__', String(nextIdx++));
        list.insertAdjacentHTML('beforeend', tpl);
        const line = list.lastElementChild;
        line.classList.add('--new', '--more');
        line.querySelector('[data-act="more"]').setAttribute('aria-expanded', 'true');
        initLine(line);
        refresh(); dirty();
        line.querySelector('[data-name]').focus();
    });

    $$('[data-line]', list).forEach(initLine);

    /* An invalid field hidden in a collapsed panel: open it so the browser can show the message. */
    form.addEventListener('invalid', e => {
        const line = e.target.closest('[data-line]');
        if (line && e.target.closest('[data-more]')) { line.classList.add('--more'); line.querySelector('[data-act="more"]')?.setAttribute('aria-expanded', 'true'); }
        if (e.target.closest('#ibDetails')) details.open = true;
    }, true);

    /* ── Re-run OCR: second tap confirms (it replaces all lines) ── */
    const rerun = document.getElementById('rerunOcrForm');
    if (rerun) {
        const btn = document.getElementById('btnRerunOcr'), label = btn.querySelector('[data-rerun-label]');
        let armed = false, t = null;
        rerun.addEventListener('submit', e => {
            if (!armed) {
                e.preventDefault();
                armed = true;
                btn.classList.add('ib-btn-arm');
                label.textContent = 'Tap again to re-scan';
                t = setTimeout(() => { armed = false; btn.classList.remove('ib-btn-arm'); label.textContent = 'Re-run OCR'; }, 5000);
                return;
            }
            clearTimeout(t);
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Reading bill…';
        });
    }

    refresh();
})();

/* ── Document: inline preview only on wide screens; full-screen viewer for images ── */
(function () {
    const pv = document.querySelector('[data-doc-preview]');
    const wide = matchMedia('(min-width: 1400px)');
    function load() {
        if (!pv || !wide.matches || pv.dataset.loaded) return;
        pv.dataset.loaded = '1';
        {
            const img = document.createElement('img'); img.src = pv.dataset.src; img.alt = 'Uploaded bill'; img.loading = 'lazy';
            img.style.cursor = 'zoom-in'; img.addEventListener('click', openViewer); pv.appendChild(img);
        }
    }
    wide.addEventListener?.('change', load);
    load();

    const dlg = document.getElementById('ibViewer');
    function openViewer() {
        if (!dlg) return;
        const img = dlg.querySelector('[data-viewer-img]');
        if (!img.src) img.src = pv.dataset.src;
        img.classList.remove('--zoom');
        dlg.showModal();
    }
    document.querySelector('[data-open-viewer]')?.addEventListener('click', openViewer);
    dlg?.querySelector('[data-close-viewer]')?.addEventListener('click', () => dlg.close());
    dlg?.querySelector('[data-viewer-img]')?.addEventListener('click', e => e.currentTarget.classList.toggle('--zoom'));
})();
</script>
@endpush

</x-admin-layout>
