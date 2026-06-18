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

/* ── Content layer sits above background ── */
.mc-content {
    position: relative;
    z-index: 1;
    padding: 0 20mm;
}

/* ── Vertical zones ─────────────────────────────────────────────
   57mm top spacer  → clears AKSHATHAY header + swirl
   42mm bot spacer  → clears golden footer bar               ── */
.mc-zone-top { height: 57mm; }
.mc-zone-bot  { height: 42mm; }

/* ── Venue pill (full-width, centered, dark brown capsule) ── */
.mc-venue-pill {
    display: block;
    background: #3d2010;
    color: #f0ece4;
    border-radius: 999px;
    padding: 6px 28px;
    text-align: center;
    font-size: 14px;
    line-height: 1.5;
    margin-bottom: 5mm;
}

/* ── Pax / Date pills (small, left / right) ── */
.mc-meta-tbl { width: 100%; margin-bottom: 6mm; border-collapse: collapse; }
.mc-pill {
    display: inline-block;
    background: #3d2010;
    color: #f0ece4;
    border-radius: 999px;
    padding: 4px 14px;
    font-size: 12px;
    line-height: 1.4;
}

/* ── Section title ── */
.mc-title { text-align: center; margin-bottom: 3mm; }
.mc-title-ta   { font-size: 28px; font-weight: bold; line-height: 1.3; }
.mc-title-en   { font-size: 22px; font-weight: bold; letter-spacing: 0.06em; line-height: 1.3; }
.mc-title-bi-ta { font-size: 24px; font-weight: bold; line-height: 1.3; }
.mc-title-bi-en { font-size: 14px; font-weight: normal; letter-spacing: 0.12em; color: #5a3820; line-height: 1.5; }

/* ── Decorative gold rule ── */
.mc-rule {
    border: none;
    border-top: 2px solid #c9a227;
    margin: 2mm 55mm 5mm 55mm;
}

/* ── Category sub-header (underlined, shown when 2+ categories) ── */
.mc-cat { text-align: center; margin-top: 8px; margin-bottom: 2px; }
.mc-cat-label    { font-size: 14px; font-weight: bold; text-decoration: underline; color: #3d2010; }
.mc-cat-label-ta { font-size: 13px; }

/* ── Item lines ── */
.mc-items { text-align: center; }
.mc-item-en   { font-size: 15px; color: #3d2010; line-height: 1.6; }
.mc-item-ta   { font-size: 15px; color: #3d2010; line-height: 1.6; }
.mc-item-bi-a { font-size: 14px; color: #3d2010; line-height: 1.45; }
.mc-item-bi-b { font-size: 13px; color: #5a3820; line-height: 1.35; }

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
    <div class="mc-items">
        @foreach($sorted as $cKey => $cItems)
        @php
            $meta  = $categories[$cKey] ?? null;
            $catEn = $meta['en'] ?? ($cItems[0]['category_en'] ?? '');
            $catTa = $meta['ta'] ?? ($cItems[0]['category_ta'] ?? '');
        @endphp

        @if($multiCat)
        <div class="mc-cat">
            @if($lang === 'en')
                <span class="mc-cat-label">{{ $catEn }}</span>
            @elseif($lang === 'ta')
                <span class="mc-cat-label mc-cat-label-ta">{{ $catTa ?: $catEn }}</span>
            @else
                <span class="mc-cat-label">{{ $catEn }}</span>
                @if($catTa)<br><span class="mc-cat-label mc-cat-label-ta">{{ $catTa }}</span>@endif
            @endif
        </div>
        @endif

        @foreach($cItems as $item)
            @if($lang === 'en')
            <div class="mc-item-en">{{ $item['item_en'] }}</div>
            @elseif($lang === 'ta')
            <div class="mc-item-ta">{{ $item['item_ta'] ?: $item['item_en'] }}</div>
            @else
            <div class="mc-item-bi-a">{{ $item['item_en'] }}</div>
            @if(!empty($item['item_ta']))
            <div class="mc-item-bi-b">{{ $item['item_ta'] }}</div>
            @endif
            @endif
        @endforeach

        @endforeach
    </div>

    {{-- Bottom spacer — clears golden footer bar --}}
    <div class="mc-zone-bot"></div>

</div>
</div>
@endforeach

</body>
</html>
