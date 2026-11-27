<!DOCTYPE html>
<html lang="{{ $lang === 'ta' ? 'ta' : 'en' }}">
<head>
<meta charset="UTF-8">
<style>

{{-- ════ TAMIL FONT ════ --}}
@if($fontPath)
@font-face {
    font-family: 'NotoSansTamil';
    src: url("file://{{ $fontPath }}") format('truetype');
    font-weight: normal;
    font-style: normal;
}
@font-face {
    font-family: 'NotoSansTamil';
    src: url("file://{{ str_replace('-Regular', '-Bold', $fontPath) }}") format('truetype');
    font-weight: bold;
    font-style: normal;
}
@endif

@page { size: A4 portrait; margin: 0; }
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: @if($fontPath) 'NotoSansTamil', @endif sans-serif;
    color: #3d2010;
    background: transparent;
}

/* ── One section = one page ───────────────────────────────────────
   Chrome print model: background-image on the page div, NOT
   position:fixed (that only stamps on page 1 in Chrome).       ── */
.mc-page {
    position: relative;
    width: 210mm;
    height: 297mm;
    overflow: hidden;
    page-break-after: always;
@if(!empty($letterheadBg) && file_exists($letterheadBg))
    background-image: url("file://{{ $letterheadBg }}");
    background-size: 210mm 297mm;
    background-repeat: no-repeat;
    background-position: top left;
@endif
}
.mc-page--last {
    position: relative;
    width: 210mm;
    height: 297mm;
    overflow: hidden;
    page-break-after: auto;
@if(!empty($letterheadBg) && file_exists($letterheadBg))
    background-image: url("file://{{ $letterheadBg }}");
    background-size: 210mm 297mm;
    background-repeat: no-repeat;
    background-position: top left;
@endif
}

/* ── Content box: always between the letterhead header (57mm) and its footer band (≈44mm).
   Absolutely positioned with overflow hidden → text can never run into the footer. ── */
.mc-content {
    position: absolute;
    z-index: 1;
    top: 55mm; bottom: 46mm; left: 16mm; right: 16mm;
    overflow: hidden;
    display: flex; flex-direction: column;
}
.mc-zone-top, .mc-zone-bot { display: none; }

/* ── Venue / pax / date ── */
.mc-venue-pill {
    display: block; background: #3d2010; color: #f6efe2; border-radius: 999px;
    padding: 5px 24px; text-align: center; font-size: 13px; line-height: 1.5; letter-spacing: .02em;
    margin-bottom: 3mm;
}
.mc-meta-tbl { width: 100%; margin-bottom: 3mm; border-collapse: collapse; }
.mc-pill { display: inline-block; background: #3d2010; color: #f6efe2; border-radius: 999px; padding: 3px 12px; font-size: 11px; line-height: 1.4; }

/* ── Section title ── */
.mc-title { text-align: center; }
.mc-title-ta    { font-size: 26px; font-weight: bold; line-height: 1.25; }
.mc-title-en    { font-family: Georgia, 'DejaVu Serif', serif; font-size: 24px; font-weight: bold; letter-spacing: .14em; line-height: 1.25; }
.mc-title-bi-ta { font-size: 22px; font-weight: bold; line-height: 1.3; }
.mc-title-bi-en { font-family: Georgia, 'DejaVu Serif', serif; font-size: 13px; letter-spacing: .3em; color: #7a5230; line-height: 1.4; }
.mc-rule { border: none; height: 10px; margin: 1.5mm auto 4mm; width: 60mm;
    background: radial-gradient(circle, #c9a227 3px, transparent 3.5px) center/10px 10px no-repeat,
                linear-gradient(#c9a227, #c9a227) left center/42% 1.5px no-repeat,
                linear-gradient(#c9a227, #c9a227) right center/42% 1.5px no-repeat; }

/* ── Items: category blocks flow into columns; a block never splits ── */
.mc-items { flex: 1; min-height: 0; column-count: var(--cols, 1); column-gap: 8mm; column-fill: balance; font-size: var(--fs, 15px); }
.mc-items.--c2, .mc-items.--c3 { column-rule: 1px solid rgba(201,162,39,.45); }
.mc-block { break-inside: avoid; page-break-inside: avoid; text-align: center; padding: 0 0 .7em; }
.mc-cat { margin: 0 0 .3em; line-height: 1.25; }
.mc-cat-label { display: inline-block; font-size: .82em; font-weight: bold; letter-spacing: .14em; text-transform: uppercase; color: #8a5a10; border-bottom: 1px solid #c9a227; padding-bottom: 1px; }
.mc-cat-label-ta { display: block; font-size: .8em; font-weight: bold; color: #8a5a10; letter-spacing: 0; text-transform: none; border: 0; margin-top: 1px; }
.mc-item { line-height: 1.32; padding: .12em 0; }
.mc-item-en   { font-size: 1em; color: #2f1a0c; }
.mc-item-ta   { font-size: 1em; color: #2f1a0c; }
.mc-item-bi-a { display: block; font-size: 1em; color: #2f1a0c; font-weight: bold; }
.mc-item-bi-b { display: block; font-size: .86em; color: #6b4426; }
</style>
</head>
<body>

@php
    $catOrder = array_keys($categories);

    $pages = array_values(array_filter($content, fn($s) => !empty($s['items'])));
    $total = count($pages);

    $dateStr = null;
    if (!empty($event_date)) {
        try   { $dateStr = 'Date : ' . \Carbon\Carbon::parse($event_date)->format('d-m-Y'); }
        catch (\Throwable $e) { $dateStr = 'Date : ' . $event_date; }
    }
@endphp

@foreach($pages as $pi => $section)
@php
    $isLast  = ($pi === $total - 1);
    $labelEn = $section['label_en'] ?? '';
    $labelTa = $section['label_ta'] ?? '';
    $pax     = $section['people_count'] ?? null;
    $paxStr  = $pax ? number_format($pax) . ' Packs' : null;

    $catGroups = [];
    foreach (($section['items'] ?? []) as $item) {
        $catGroups[$item['category_key']][] = $item;
    }
    $sorted = [];
    foreach ($catOrder as $k) {
        if (isset($catGroups[$k])) $sorted[$k] = $catGroups[$k];
    }
    foreach ($catGroups as $k => $v) {
        if (!isset($sorted[$k])) $sorted[$k] = $v;
    }
    $multiCat = count($sorted) > 1;
    // Fit: estimate printed lines, choose 1–3 columns and a font size (the inline script refines it in Chrome).
    $perLine = $lang === 'bi' ? 2 : 1;
    $lines = 0;
    foreach ($sorted as $ci) { $lines += ($multiCat ? $perLine : 0) + count($ci) * $perLine + 1; }
    $cols = $lines <= 24 ? 1 : ($lines <= 60 ? 2 : 3);
    $perCol = (int) ceil($lines / $cols);
    $fs = round(max(10, min(16, 16 * 28 / max(28, $perCol))), 1);
@endphp

<div class="{{ $isLast ? 'mc-page--last' : 'mc-page' }}">
<div class="mc-content">

    {{-- Top spacer — clears letterhead header + swirl --}}
    <div class="mc-zone-top"></div>

    {{-- Venue pill --}}
    @if(!empty($venue))
    <span class="mc-venue-pill">@if($lang !== 'en')இடம் : @else Venue : @endif{{ $venue }}</span>
    @endif

    {{-- Pax left / Date right --}}
    @if($paxStr || $dateStr)
    <table class="mc-meta-tbl" cellpadding="0" cellspacing="0">
        <tr>
            <td width="48%" align="left" valign="middle">
                @if($paxStr)<span class="mc-pill">{{ $paxStr }}</span>@endif
            </td>
            <td width="4%"></td>
            <td width="48%" align="right" valign="middle">
                @if($dateStr)<span class="mc-pill">{{ $dateStr }}</span>@endif
            </td>
        </tr>
    </table>
    @endif

    {{-- Section title --}}
    <div class="mc-title">
        @if($lang === 'en')
            <span class="mc-title-en">{{ mb_strtoupper($labelEn) }}</span>
        @elseif($lang === 'ta')
            <span class="mc-title-ta">{{ $labelTa ?: $labelEn }}</span>
        @else
            <span class="mc-title-bi-en">{{ mb_strtoupper($labelEn) }}</span>
            @if($labelTa)
            <br><span class="mc-title-bi-ta">{{ $labelTa }}</span>
            @endif
        @endif
    </div>

    {{-- Decorative gold rule --}}
    <hr class="mc-rule">

    {{-- Items --}}
    <div class="mc-items --c{{ $cols }}" style="--cols:{{ $cols }};--fs:{{ $fs }}px" data-fit>
        @foreach($sorted as $cKey => $cItems)
        @php
            $meta  = $categories[$cKey] ?? null;
            $catEn = $meta['en'] ?? ($cItems[0]['category_en'] ?? '');
            $catTa = $meta['ta'] ?? ($cItems[0]['category_ta'] ?? '');
        @endphp
        <div class="mc-block">
            @if($multiCat)
            <div class="mc-cat">
                @if($lang === 'en')
                    <span class="mc-cat-label">{{ $catEn }}</span>
                @elseif($lang === 'ta')
                    <span class="mc-cat-label mc-cat-label-ta" style="font-size:.9em">{{ $catTa ?: $catEn }}</span>
                @else
                    <span class="mc-cat-label">{{ $catEn }}</span>
                    @if($catTa && $catTa !== $catEn)<span class="mc-cat-label-ta">{{ $catTa }}</span>@endif
                @endif
            </div>
            @endif
            @foreach($cItems as $item)
                @php $ta = trim((string) ($item['item_ta'] ?? '')); $en = trim((string) ($item['item_en'] ?? '')); @endphp
                <div class="mc-item">
                    @if($lang === 'en')
                        <span class="mc-item-en">{{ $en }}</span>
                    @elseif($lang === 'ta')
                        <span class="mc-item-ta">{{ $ta ?: $en }}</span>
                    @else
                        <span class="mc-item-bi-a">{{ $en }}</span>
                        @if($ta !== '' && mb_strtolower($ta) !== mb_strtolower($en))<span class="mc-item-bi-b">{{ $ta }}</span>@endif
                    @endif
                </div>
            @endforeach
        </div>
        @endforeach
    </div>

    {{-- Bottom spacer — clears golden footer bar --}}
    <div class="mc-zone-bot"></div>

</div>
</div>
@endforeach

<script>
// Chrome renders this before printing: shrink the item font until every column fits the box (never below 9px),
// adding a column first if a single column overflows. dompdf ignores it and uses the server-side estimate.
document.querySelectorAll('[data-fit]').forEach(function (box) {
    var fs = parseFloat(getComputedStyle(box).fontSize), cols = parseInt(box.style.getPropertyValue('--cols'), 10) || 1;
    function over() { return box.scrollHeight > box.clientHeight + 1 || box.scrollWidth > box.clientWidth + 1; }
    for (var i = 0; i < 60 && over(); i++) {
        if (cols < 3 && fs <= 12) { cols++; box.style.setProperty('--cols', cols); box.classList.add('--c' + cols); fs = Math.min(fs + 3, 15); }
        else if (fs > 9) { fs -= 0.5; }
        else break;
        box.style.setProperty('--fs', fs + 'px');
    }
});
</script>
</body>
</html>
