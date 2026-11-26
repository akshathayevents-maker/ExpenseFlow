{{-- Verification badge. Only shows "Verified ✓" for an explicit admin verification. $recipe; optional $small --}}
@php
    $vs = $recipe->verification_status ?? 'unverified';
    $isV = method_exists($recipe, 'isVerified') ? $recipe->isVerified() : false;
    $cfg = $isV ? ['--ok', 'bi-patch-check-fill', 'Verified'] : match ($vs) {
        'ai_generated' => ['--ai', 'bi-stars', 'AI generated · not verified'],
        'needs_review' => ($recipe->source ?? null) === 'starter'
            ? ['--info', 'bi-box-seam', 'Curated starter · not verified'] : ['--warn', 'bi-exclamation-circle', 'Needs review'],
        'reviewed' => ['--info', 'bi-eye', 'Reviewed · not verified'],
        'archived' => ['--muted', 'bi-archive', 'Archived'],
        default => null,
    };
@endphp
@once
<style>
.rv-badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:999px;font-size:.72rem;font-weight:750;white-space:nowrap;line-height:1.5;vertical-align:middle}
.rv-badge.--ok{background:rgba(46,125,90,.12);color:#1f6a48}
.rv-badge.--ai{background:rgba(124,77,255,.1);color:#5b38b8}
.rv-badge.--warn{background:rgba(216,154,61,.16);color:#7D5218}
.rv-badge.--info{background:rgba(59,110,165,.12);color:#2c5784}
.rv-badge.--muted{background:rgba(20,20,18,.07);color:var(--ef-muted)}
</style>
@endonce
@if($cfg)
    <span class="rv-badge {{ $cfg[0] }}" @if($isV && $recipe->verified_at) title="Verified {{ $recipe->verified_at->format('d M Y') }}{{ $recipe->verifier ? ' by ' . $recipe->verifier->name : '' }} · revision {{ $recipe->revision }}" @endif>
        <i class="bi {{ $cfg[1] }}"></i>{{ $cfg[2] }}</span>
@endif
