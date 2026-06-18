<x-admin-layout title="Menu Composer">
@push('styles')
<style>
:root {
    --mc-gold: #a0723a; --mc-gold-hi: #b8832a; --mc-gold-dim: rgba(160,114,58,.1);
    --mc-gold-mid: rgba(160,114,58,.18);
    --mc-surface: #fff; --mc-page: #f7f5f2; --mc-border: #e8e2d8;
    --mc-ink: #1c1712; --mc-muted: #7a6e62; --mc-faint: #c0b8ac;
    --mc-radius: 18px; --mc-r-sm: 10px;
}

/* ── Page shell ── */
.mc-wrap { max-width: 1200px; margin: 0 auto; padding-bottom: 60px; }
.mc-page-hdr {
    display: flex; align-items: center; gap: 12px;
    margin-bottom: 28px; flex-wrap: wrap;
}
.mc-page-title { font-size: 1.5rem; font-weight: 800; color: var(--mc-ink); flex: 1; margin: 0; }
.mc-page-title-sub { font-size: .85rem; font-weight: 400; color: var(--mc-muted); margin-left: 6px; }
.mc-new-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--mc-gold); color: #fff; border: none;
    border-radius: var(--mc-r-sm); padding: 11px 22px;
    font-size: .92rem; font-weight: 700; text-decoration: none;
    transition: background .15s, transform .12s, box-shadow .15s;
    box-shadow: 0 2px 8px rgba(160,114,58,.3);
}
.mc-new-btn:hover { background: var(--mc-gold-hi); color: #fff; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(160,114,58,.4); }

/* ── Grid ── */
.mc-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    align-items: start;   /* prevents grid from stretching cards */
}
@media (max-width: 1024px) { .mc-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px)  { .mc-grid { grid-template-columns: 1fr; } }

/* ── Card — fixed height, never grows ── */
.mc-card {
    background: var(--mc-surface);
    border: 1.5px solid var(--mc-border);
    border-radius: var(--mc-radius);
    display: flex; flex-direction: column;
    height: 300px;
    overflow: hidden;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.mc-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 36px rgba(0,0,0,.1), 0 2px 8px rgba(160,114,58,.14);
    border-color: var(--mc-gold);
}
.mc-card-accent {
    height: 3px; flex-shrink: 0;
    background: linear-gradient(90deg, var(--mc-gold), #d4a85a);
}
.mc-card-inner {
    padding: 14px 18px 8px;
    display: flex; flex-direction: column; gap: 9px;
    flex: 1; overflow: hidden;
}

/* P1 — Title */
.mc-card-top {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;
    flex-shrink: 0;
}
.mc-card-name {
    font-size: 1rem; font-weight: 800; color: var(--mc-ink);
    line-height: 1.3; flex: 1; min-width: 0;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.mc-card-age {
    display: inline-flex; align-items: center; gap: 3px; flex-shrink: 0;
    color: var(--mc-faint); font-size: .68rem; white-space: nowrap; padding-top: 2px;
}

/* P2 — Venue + Date, prominent */
.mc-card-where { display: flex; flex-direction: column; gap: 2px; flex-shrink: 0; }
.mc-where-row {
    display: flex; align-items: center; gap: 6px;
    font-size: .9rem; font-weight: 600; color: #241c14; line-height: 1.35;
}
.mc-where-row .bi { color: var(--mc-gold); font-size: .78rem; flex-shrink: 0; }
.mc-where-empty { font-size: .74rem; color: var(--mc-faint); font-style: italic; }

/* P3 — Pax */
.mc-pax-line {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: .82rem; font-weight: 700; color: var(--mc-gold);
    background: var(--mc-gold-dim); border-radius: 999px;
    padding: 3px 12px; align-self: flex-start; flex-shrink: 0;
}
.mc-pax-line .bi { font-size: .75rem; }

/* P4 — Section summary, fills flex space, clips cleanly */
.mc-sec-summary {
    flex: 1; overflow: hidden;
    display: flex; flex-direction: column; gap: 2px;
}
.mc-sec-line {
    display: flex; align-items: center; justify-content: space-between;
    padding: 3px 8px; border-radius: 6px; background: #faf8f5;
    flex-shrink: 0;
}
.mc-sec-name {
    font-size: .78rem; font-weight: 600; color: var(--mc-ink);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 75%;
}
.mc-sec-count {
    font-size: .72rem; color: var(--mc-muted); flex-shrink: 0;
    background: #ede8e0; border-radius: 999px; padding: 1px 7px; font-weight: 600;
}
.mc-sec-more {
    font-size: .72rem; color: var(--mc-gold); font-weight: 600;
    padding: 2px 8px; flex-shrink: 0;
}
.mc-sec-none {
    font-size: .75rem; color: var(--mc-faint); font-style: italic;
    padding: 8px 0;
}

/* Footer */
.mc-card-foot {
    padding: 9px 14px 11px; flex-shrink: 0;
    display: flex; align-items: center; gap: 7px;
    border-top: 1px solid var(--mc-border);
}
.mc-btn-open {
    flex: 1; padding: 8px 12px;
    background: var(--mc-gold); color: #fff; border: none; border-radius: 8px;
    font-size: .83rem; font-weight: 700; text-decoration: none; text-align: center;
    transition: background .12s, box-shadow .12s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.mc-btn-open:hover { background: var(--mc-gold-hi); color: #fff; box-shadow: 0 3px 10px rgba(160,114,58,.3); }
.mc-icon-btn {
    width: 34px; height: 34px; border-radius: 7px; border: 1.5px solid;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: .84rem; background: transparent;
    transition: background .12s, color .12s; flex-shrink: 0;
}
.mc-icon-btn--dupe { border-color: #bdd7f5; color: #1e6ab5; }
.mc-icon-btn--dupe:hover { background: #e8f1fc; }
.mc-icon-btn--del  { border-color: #fee2e2; color: #dc2626; }
.mc-icon-btn--del:hover  { background: #fee2e2; }

/* ── Templates strip ── */
.mc-tpl-section { margin-bottom: 28px; }
.mc-tpl-section-title { font-size: .8rem; font-weight: 700; color: var(--mc-muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 10px; }
.mc-tpl-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.mc-tpl-pill-wrap { display: inline-flex; align-items: center; background: #fdf8f3; border: 1px solid #e8dece; border-radius: 999px; overflow: hidden; }
.mc-tpl-pill-wrap:hover { border-color: var(--mc-gold); }
.mc-tpl-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px 6px 14px; background: none; border: none;
    font-size: .82rem; font-weight: 600; color: var(--mc-gold); cursor: pointer;
}
.mc-tpl-pill:hover { background: var(--mc-gold-dim); }
.mc-tpl-del {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0 10px 0 4px; background: none; border: none;
    color: #c0a090; font-size: .85rem; cursor: pointer; line-height: 1;
}
.mc-tpl-del:hover { color: #c0392b; }

/* ── Empty state ── */
.mc-empty {
    text-align: center;
    padding: 80px 24px 60px;
    border: 2px dashed var(--mc-border);
    border-radius: var(--mc-radius);
    background: #faf8f5;
}
.mc-empty-art {
    width: 80px; height: 80px; margin: 0 auto 20px;
    background: var(--mc-gold-dim); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
}
.mc-empty-art i { font-size: 2.2rem; color: var(--mc-gold); }
.mc-empty-title { font-size: 1.15rem; font-weight: 800; color: var(--mc-ink); margin-bottom: 8px; }
.mc-empty-sub { font-size: .88rem; color: var(--mc-muted); margin-bottom: 28px; max-width: 320px; margin-left: auto; margin-right: auto; }
.mc-empty-hint {
    display: flex; align-items: center; justify-content: center; gap: 18px;
    flex-wrap: wrap; margin-bottom: 28px;
}
.mc-empty-step {
    display: flex; align-items: center; gap: 7px;
    font-size: .8rem; color: var(--mc-muted);
}
.mc-empty-step-num {
    width: 22px; height: 22px; border-radius: 50%;
    background: var(--mc-gold); color: #fff;
    font-size: .72rem; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ── Scroll ── */
.mc-scroll-sentinel { height: 1px; }
.mc-scroll-loader { display: none; text-align: center; padding: 24px; color: var(--mc-muted); font-size: .88rem; }
.mc-scroll-loader.show { display: block; }
.mc-scroll-spinner {
    display: inline-block; width: 20px; height: 20px;
    border: 2px solid var(--mc-faint); border-top-color: var(--mc-gold);
    border-radius: 50%; animation: mc-spin .7s linear infinite; vertical-align: middle; margin-right: 6px;
}
@keyframes mc-spin { to { transform: rotate(360deg); } }
</style>
@endpush

<div class="mc-wrap">
    <div class="mc-page-hdr">
        <h1 class="mc-page-title">
            <i class="bi bi-pencil-square me-2" style="color:var(--mc-gold)"></i>Menu Composer
            @if($drafts->total() > 0)
                <span class="mc-page-title-sub">{{ $drafts->total() }} {{ Str::plural('menu', $drafts->total()) }}</span>
            @endif
        </h1>
        <a href="{{ route('menu.composer.create') }}" class="mc-new-btn">
            <i class="bi bi-plus-lg"></i> New Menu
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:var(--mc-r-sm)">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Quick-load templates --}}
    @if($templates->isNotEmpty())
    <div class="mc-tpl-section">
        <div class="mc-tpl-section-title"><i class="bi bi-lightning-charge me-1"></i> Load Template</div>
        <div class="mc-tpl-pills">
            @foreach($templates as $tpl)
            <div class="mc-tpl-pill-wrap">
                <form method="POST" action="{{ route('menu.templates.load', $tpl) }}" style="display:inline">
                    @csrf
                    <button type="submit" class="mc-tpl-pill">
                        <i class="bi bi-collection"></i> {{ $tpl->name }}
                    </button>
                </form>
                <form method="POST" action="{{ route('menu.templates.destroy', $tpl) }}" style="display:inline"
                      onsubmit="return confirm('Delete template \'{{ addslashes($tpl->name) }}\'?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="mc-tpl-del" title="Delete template">
                        <i class="bi bi-x"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($drafts->isEmpty())
    <div class="mc-empty">
        <div class="mc-empty-art"><i class="bi bi-journal-richtext"></i></div>
        <p class="mc-empty-title">Design Your First Menu</p>
        <p class="mc-empty-sub">Build beautiful catering menus with sections, items, and pax counts — then export as a branded PDF.</p>
        <div class="mc-empty-hint">
            <div class="mc-empty-step">
                <span class="mc-empty-step-num">1</span> Create a menu
            </div>
            <i class="bi bi-arrow-right" style="color:var(--mc-faint);font-size:.75rem"></i>
            <div class="mc-empty-step">
                <span class="mc-empty-step-num">2</span> Add sections &amp; items
            </div>
            <i class="bi bi-arrow-right" style="color:var(--mc-faint);font-size:.75rem"></i>
            <div class="mc-empty-step">
                <span class="mc-empty-step-num">3</span> Export branded PDF
            </div>
        </div>
        <a href="{{ route('menu.composer.create') }}" class="mc-new-btn">
            <i class="bi bi-plus-lg"></i> Create First Menu
        </a>
    </div>
    @else
        <div class="mc-grid" id="mcDraftGrid">
            @include('menu.composer._draft_cards', ['drafts' => $drafts])
        </div>
        <div class="mc-scroll-sentinel" id="mcSentinel"></div>
        <div class="mc-scroll-loader" id="mcLoader">
            <span class="mc-scroll-spinner"></span> Loading more…
        </div>
    @endif
</div>

@if($drafts->isNotEmpty())
<script>
(function () {
    var nextPage = {{ $drafts->currentPage() + 1 }};
    var hasMore  = {{ $drafts->hasMorePages() ? 'true' : 'false' }};
    var loading  = false;
    var grid     = document.getElementById('mcDraftGrid');
    var sentinel = document.getElementById('mcSentinel');
    var loader   = document.getElementById('mcLoader');

    if (!hasMore || !sentinel) return;

    function loadMore() {
        if (loading || !hasMore) return;
        loading = true;
        loader.classList.add('show');
        fetch('{{ route('menu.composer.index') }}?page=' + nextPage, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.html) {
                var tmp = document.createElement('div');
                tmp.innerHTML = data.html;
                while (tmp.firstChild) grid.appendChild(tmp.firstChild);
            }
            hasMore  = data.hasMore;
            nextPage = data.nextPage;
            loading  = false;
            loader.classList.remove('show');
            if (!hasMore) observer.disconnect();
        })
        .catch(function () { loading = false; loader.classList.remove('show'); });
    }

    var observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' });

    observer.observe(sentinel);
})();
</script>
@endif

</x-admin-layout>
