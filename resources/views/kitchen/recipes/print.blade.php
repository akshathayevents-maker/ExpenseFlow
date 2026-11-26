@php
    $fmt = fn ($n) => $n === null ? null : rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');
    $show = fn ($l) => $lang === 'both' || $lang === $l;
    $missingTa = '<span class="miss">தமிழ் இல்லை (Tamil missing)</span>';
@endphp
<!doctype html>
<html lang="{{ $lang === 'ta' ? 'ta' : 'en' }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $recipe->name }} — {{ $people }} {{ $recipe->yield_unit }}</title>
<style>
:root{--ink:#151513;--muted:#5f594f;--line:#d9d3c8}
*{box-sizing:border-box}
body{margin:0;background:#fff;color:var(--ink);font:15px/1.5 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
.ta,[lang=ta]{font-family:"Noto Sans Tamil","Latha","Vijaya","Nirmala UI",system-ui,sans-serif;line-height:1.7}
.page{max-width:820px;margin:0 auto;padding:18px 16px 40px}
.bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:16px;padding:10px;border:1px solid var(--line);border-radius:12px;background:#faf8f4}
.bar a,.bar button{min-height:40px;padding:6px 12px;border:1px solid var(--line);border-radius:10px;background:#fff;color:var(--ink);text-decoration:none;font-weight:700;font-size:.88rem;cursor:pointer}
.bar a.on{background:var(--ink);color:#fff;border-color:var(--ink)}
.bar form{display:flex;gap:6px;align-items:center;margin:0}
.bar input{width:90px;min-height:40px;border:1px solid var(--line);border-radius:10px;padding:4px 8px;font-size:16px}
h1{margin:0;font-size:1.6rem;line-height:1.2}
h1 .ta{display:block;font-size:1.2rem;font-weight:700;color:#333}
.meta{display:flex;flex-wrap:wrap;gap:6px 18px;margin:8px 0 12px;color:var(--muted);font-size:.9rem}
.meta b{color:var(--ink)}
.warn{border:1px solid #d89a3d;background:#fdf6ea;border-radius:10px;padding:8px 12px;margin:10px 0;font-size:.9rem}
.status{display:inline-block;padding:2px 10px;border-radius:999px;font-size:.78rem;font-weight:800;border:1px solid currentColor}
.status.ok{color:#1f6a48}.status.no{color:#9a6413}
h2{font-size:1.05rem;margin:18px 0 6px;padding-bottom:4px;border-bottom:2px solid var(--ink)}
table{width:100%;border-collapse:collapse;font-size:.95rem}
th,td{text-align:left;padding:6px 6px;border-bottom:1px solid var(--line);vertical-align:top}
th{font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)}
td.q{text-align:right;white-space:nowrap;font-weight:800;font-variant-numeric:tabular-nums}
td.q small{display:block;font-weight:500;color:var(--muted)}
.step{display:grid;grid-template-columns:34px 1fr;gap:10px;padding:10px 0;border-bottom:1px solid var(--line);break-inside:avoid}
.num{width:30px;height:30px;border-radius:50%;background:var(--ink);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800}
.step p{margin:2px 0}
.lbl{font-size:.7rem;font-weight:800;color:var(--muted);text-transform:uppercase;margin-right:6px}
.miss{color:#9a6413;font-style:italic}
.notes{border-left:4px solid #d89a3d;background:#fdf6ea;padding:8px 12px;margin:12px 0;border-radius:6px;font-size:.92rem}
.foot{margin-top:18px;font-size:.78rem;color:var(--muted)}
@media (max-width:520px){h1{font-size:1.3rem}table{font-size:.88rem}}
@media print{.bar{display:none}.page{padding:0}body{font-size:12pt}h2{break-after:avoid}a{color:inherit;text-decoration:none}}
</style>
</head>
<body>
<div class="page">
    <div class="bar">
        @foreach(['en' => 'English', 'ta' => 'தமிழ்', 'both' => 'Both'] as $k => $l)
            <a class="{{ $lang === $k ? 'on' : '' }}" href="{{ request()->fullUrlWithQuery(['lang' => $k]) }}" @if($k === 'ta') lang="ta" @endif>{{ $l }}</a>
        @endforeach
        <form method="GET"><input type="hidden" name="lang" value="{{ $lang }}">
            <label for="p">For</label><input id="p" type="number" name="people" min="1" max="5000" value="{{ $people }}" inputmode="numeric">
            <button type="submit">Update</button></form>
        <button type="button" onclick="window.print()">🖨 Print</button>
    </div>

    <h1>@if($show('en')){{ $recipe->name }}@endif
        @if($show('ta'))<span class="ta" lang="ta">{!! $recipe->name_ta ? e($recipe->name_ta) : $missingTa !!}</span>@endif</h1>
    <div class="meta">
        <span>For <b>{{ $people }} {{ $recipe->yield_unit }}</b> (base recipe {{ $fmt($recipe->yield_per_batch) }})</span>
        @if($recipe->prep_time_minutes)<span>Prep <b>{{ $recipe->prep_time_minutes }} min</b></span>@endif
        @if($recipe->cook_time_minutes)<span>Cook <b>{{ $recipe->cook_time_minutes }} min</b> per batch</span>@endif
        <span class="status {{ $recipe->isVerified() ? 'ok' : 'no' }}">{{ $recipe->isVerified() ? 'Verified ✓' : 'NOT VERIFIED — check before use' }}</span>
    </div>
    @if($plan['note'])
        <div class="warn">@if($show('en'))<div>⚠ {{ $plan['note'] }}</div>@endif @if($show('ta'))<div class="ta" lang="ta">⚠ {{ $plan['note_ta'] }}</div>@endif</div>
    @endif

    <h2>@if($show('en'))Ingredients @endif @if($show('ta'))<span class="ta" lang="ta">பொருட்கள்</span>@endif</h2>
    <table>
        <thead><tr><th>#</th><th>Item</th><th style="text-align:right">For {{ $people }}</th></tr></thead>
        <tbody>
        @foreach($scaled as $n => $row)
            @php $ing = $row['ingredient']; @endphp
            <tr>
                <td>{{ $n + 1 }}</td>
                <td>@if($show('en')){{ $ing->ingredient_name }}@endif
                    @if($show('ta'))<span class="ta" lang="ta">{{ $show('en') ? ' · ' : '' }}{!! $ing->displayNameTa() ? e($ing->displayNameTa()) : '<span class="miss">—</span>' !!}</span>@endif
                    @if($ing->prep_note)<div style="font-size:.82rem;color:var(--muted)">{{ $ing->prep_note }}</div>@endif
                    @if($ing->is_optional)<div style="font-size:.78rem;color:var(--muted)">optional</div>@endif</td>
                @php
                    [$qShow, $uShow] = [$row['quantity'], $ing->unit];
                    if ($qShow !== null && $qShow >= 1000 && in_array($uShow, ['gram', 'ml'], true)) { [$qShow, $uShow] = [$qShow / 1000, $uShow === 'gram' ? 'kg' : 'litre']; }
                @endphp
                <td class="q">@if($row['quantity'] !== null){{ $fmt($qShow) }} {{ $uShow }}{{ $ing->is_approximate ? ' (approx.)' : '' }}
                        <small>{{ $fmt($row['per_serving'] * 1000 >= 1 && in_array($ing->unit, ['kg', 'litre'], true) ? $row['per_serving'] * 1000 : $row['per_serving']) }}{{ in_array($ing->unit, ['kg', 'litre'], true) ? ($ing->unit === 'kg' ? ' g' : ' ml') : ' ' . $ing->unit }} per serving</small>
                    @else{{ $ing->quantity_note ?: 'As required' }}@endif</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if($recipe->kitchen_notes || $recipe->kitchen_notes_ta)
        <div class="notes">@if($show('en') && $recipe->kitchen_notes)<div><b>Kitchen notes:</b> {{ $recipe->kitchen_notes }}</div>@endif
            @if($show('ta'))<div class="ta" lang="ta"><b>சமையல் குறிப்பு:</b> {!! $recipe->kitchen_notes_ta ? e($recipe->kitchen_notes_ta) : $missingTa !!}</div>@endif</div>
    @endif

    <h2>@if($show('en'))Preparation @endif @if($show('ta'))<span class="ta" lang="ta">செய்முறை</span>@endif</h2>
    @foreach($recipe->sops as $sop)
        <div class="step">
            <div class="num">{{ $sop->step_number }}</div>
            <div>
                <p><b>@if($show('en')){{ $sop->title }}@endif @if($show('ta') && $sop->title_ta)<span class="ta" lang="ta">{{ $show('en') ? '· ' : '' }}{{ $sop->title_ta }}</span>@endif</b>
                    @if($sop->duration_minutes) <span style="color:var(--muted)">({{ $sop->duration_minutes }} min)</span>@endif</p>
                @if($show('en'))<p>@if($lang === 'both')<span class="lbl">English</span>@endif{{ $sop->instruction }}</p>@endif
                @if($show('ta'))<p class="ta" lang="ta">@if($lang === 'both')<span class="lbl">தமிழ்</span>@endif{!! $sop->instruction_ta ? e($sop->instruction_ta) : $missingTa !!}</p>@endif
                @if($sop->note || $sop->note_ta)<p style="font-size:.86rem;color:var(--muted)">ⓘ @if($show('en')){{ $sop->note }}@endif @if($show('ta'))<span class="ta" lang="ta">{{ $sop->note_ta }}</span>@endif</p>@endif
            </div>
        </div>
    @endforeach

    <div class="foot">Printed {{ now('Asia/Kolkata')->format('d M Y, h:i A') }} · revision {{ $recipe->revision }} · quantities scaled from the base recipe; cooking times are per batch.</div>
</div>
</body>
</html>
