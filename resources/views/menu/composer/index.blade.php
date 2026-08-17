<x-admin-layout title="Menu Composer">
@push('styles')
<style>
:root {
    /* Elegant, restrained bronze/caramel — reserved for CTAs & selected
       states, not decoration. */
    --mc-gold: #A97935; --mc-gold-hi: #8C632A; --mc-gold-dim: rgba(169,121,53,.09);
    --mc-gold-mid: rgba(169,121,53,.16);
    --mc-surface: #FFFFFF; --mc-page: #F7F5F0; --mc-border: #E8E3DA;
    --mc-ink: #20201D; --mc-muted: #77736B; --mc-faint: #B4AEA2;
    --mc-radius: 18px; --mc-r-sm: 10px;

    /* Future/upcoming = muted teal · past = muted gray · today = bronze */
    --mc-future: #3F7D6E; --mc-future-dim: rgba(63,125,110,.1);
    --mc-past: #8A8579; --mc-past-dim: rgba(138,133,121,.1);
    --mc-past-border: #EDEAE3; --mc-past-surface: #FBFAF8;
}

/* ── Page shell ── */
.mc-wrap { max-width: 1280px; margin: 0 auto; padding-bottom: 60px; }
.mc-page-hdr {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
    margin-bottom: 16px; flex-wrap: wrap;
}
.mc-page-title-row { display: flex; align-items: center; gap: 8px; }
.mc-page-title { font-size: 1.5rem; font-weight: 800; color: var(--mc-ink); margin: 0; line-height: 1.2; }
.mc-page-count {
    font-size: .72rem; font-weight: 700; color: var(--mc-gold);
    background: var(--mc-gold-dim); border-radius: 999px; padding: 2px 10px;
}
.mc-page-sub { font-size: .85rem; color: var(--mc-muted); margin-top: 2px; }
.mc-new-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--mc-gold); color: #fff; border: none;
    border-radius: var(--mc-r-sm); padding: 0 20px; height: 44px;
    font-size: .88rem; font-weight: 700; text-decoration: none;
    transition: background .15s, box-shadow .15s;
    box-shadow: 0 2px 8px rgba(160,114,58,.25);
    flex-shrink: 0;
}
.mc-new-btn:hover { background: var(--mc-gold-hi); color: #fff; box-shadow: 0 4px 14px rgba(160,114,58,.35); }
@media (max-width: 575.98px) {
    .mc-page-hdr { flex-direction: column; }
    .mc-new-btn { width: 100%; justify-content: center; }
}

/* ── Grid ── */
.mc-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    align-items: start;
}
@media (max-width: 1024px) { .mc-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 640px)  { .mc-grid { grid-template-columns: 1fr; } }

/* ── Card — content-driven height, no fixed height, no empty space ── */
.mc-card {
    background: var(--mc-surface);
    border: 1px solid var(--mc-border);
    border-radius: 12px;
    box-shadow: 0 1px 2px rgba(0,0,0,.03);
    display: flex; flex-direction: column;
    overflow: hidden;
    transition: border-color .15s ease, box-shadow .15s ease;
    position: relative;
}
.mc-card:hover { border-color: var(--mc-gold); box-shadow: 0 2px 10px rgba(0,0,0,.06); }

/* Today — tasteful bronze accent, not a full-card highlight */
.mc-card.--today { border-left: 3px solid var(--mc-gold); }
.mc-card.--today:hover { border-color: var(--mc-border); border-left-color: var(--mc-gold); }

/* Past — muted, never opacity (stays fully readable), thin gray accent */
.mc-card.--past {
    background: var(--mc-past-surface);
    border-color: var(--mc-past-border);
    border-left: 3px solid var(--mc-past-border);
}
.mc-card.--past:hover { border-color: var(--mc-past-border); box-shadow: 0 1px 4px rgba(0,0,0,.04); }
.mc-card.--past .mc-card-name { color: var(--mc-muted); }
.mc-card.--past .mc-card-meta { color: var(--mc-faint); }
.mc-card.--past .mc-sec-icon { color: var(--mc-faint); }
.mc-card.--past .mc-sec-line { background: #F5F3EE; }
.mc-card.--past .mc-btn-open { background: var(--mc-surface); color: var(--mc-ink-2, var(--mc-ink)); border: 1px solid var(--mc-border); }
.mc-card.--past .mc-btn-open:hover { background: #F5F3EE; }
.mc-card-hit { position: absolute; inset: 0; z-index: 1; }
.mc-card-hit:focus-visible { outline: 2px solid var(--mc-gold); outline-offset: -2px; }

.mc-card-inner {
    padding: 16px 16px 12px;
    display: flex; flex-direction: column; gap: 8px;
}

/* P1 — Title + updated (subtle, top-right) */
.mc-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
.mc-card-name {
    font-size: 1.05rem; font-weight: 700; color: var(--mc-ink);
    line-height: 1.3; flex: 1; min-width: 0; overflow-wrap: anywhere;
}
.mc-card-age { flex-shrink: 0; color: var(--mc-faint); font-size: .68rem; white-space: nowrap; padding-top: 2px; }

/* P2 — Venue · Date · Pax, one compact secondary line */
.mc-card-meta { font-size: .8rem; color: var(--mc-muted); line-height: 1.4; }
.mc-card-meta .sep { margin: 0 5px; color: var(--mc-faint); }
.mc-card-pax { font-weight: 700; color: var(--mc-ink-2, var(--mc-ink)); }

/* Date status badge — the ●/○ dot carries meaning alongside the label
   (not color alone), so it still reads correctly without color vision. */
.mc-status {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
    margin-top: 2px;
}
.mc-status::before { content: '●'; font-size: .6rem; line-height: 1; }
.mc-status.--today  { color: var(--mc-gold); }
.mc-status.--future { color: var(--mc-future); }
.mc-status.--past   { color: var(--mc-faint); }
.mc-status.--past::before { content: '○'; }

/* P3 — Meals section */
.mc-meals-label {
    font-size: .66rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    color: var(--mc-faint); margin-top: 4px;
}
.mc-sec-summary { display: flex; flex-direction: column; gap: 4px; }
.mc-sec-line {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 8px; border-radius: 6px; background: #faf8f5;
}
.mc-sec-icon { flex-shrink: 0; width: 16px; text-align: center; color: var(--mc-gold); font-size: .78rem; }
.mc-sec-name {
    font-size: .82rem; font-weight: 600; color: var(--mc-ink);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; min-width: 0;
}
.mc-sec-count { font-size: .74rem; color: var(--mc-muted); flex-shrink: 0; font-weight: 600; }
.mc-sec-more { font-size: .72rem; color: var(--mc-gold); font-weight: 600; padding: 2px 8px; }
.mc-sec-none { font-size: .78rem; color: var(--mc-faint); font-style: italic; }

/* Footer */
.mc-card-foot {
    margin-top: auto;
    padding: 10px 12px; display: flex; align-items: center; gap: 8px;
    border-top: 1px solid var(--mc-border);
    position: relative; z-index: 2;
}
.mc-btn-open {
    flex: 1; height: 40px;
    background: var(--mc-gold); color: #fff; border: none; border-radius: 8px;
    font-size: .85rem; font-weight: 700; text-decoration: none; text-align: center;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.mc-btn-open:hover { background: var(--mc-gold-hi); color: #fff; }
.mc-more-btn {
    width: 40px; height: 40px; flex-shrink: 0; border-radius: 8px; border: 1px solid var(--mc-border);
    background: var(--mc-surface); color: var(--mc-muted); display: flex; align-items: center; justify-content: center;
}
.mc-more-btn:hover { background: #faf8f5; color: var(--mc-ink); }
.mc-more-menu .dropdown-item { font-size: .84rem; padding: .5rem .75rem; }
.mc-more-menu .dropdown-item.text-danger:hover { background: rgba(220,38,38,.08); }

/* ── Templates strip ── */
/* ── Search ── */
.mc-search-wrap { position: relative; margin-bottom: 20px; max-width: 420px; }
.mc-search-wrap > .bi-search { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--mc-faint); font-size: .85rem; pointer-events: none; }
.mc-search {
    width: 100%; height: 44px; border: 1px solid var(--mc-border); border-radius: var(--mc-r-sm);
    padding: 0 40px 0 38px; font-size: .88rem; color: var(--mc-ink); background: var(--mc-surface);
    outline: none; transition: border-color .15s, box-shadow .15s;
}
.mc-search:focus { border-color: var(--mc-gold); box-shadow: 0 0 0 3px var(--mc-gold-dim); }
.mc-search-clear {
    position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
    width: 32px; height: 32px; border: none; background: none; color: var(--mc-faint);
    border-radius: 8px; display: flex; align-items: center; justify-content: center;
}
.mc-search-clear:hover { background: var(--mc-page); color: var(--mc-muted); }

/* ── Lazy-load error / retry ── */
.mc-load-error {
    grid-column: 1 / -1; display: none; flex-direction: column; align-items: center; gap: 10px;
    padding: 20px; text-align: center; color: var(--mc-muted); font-size: .86rem;
}
.mc-load-error.show { display: flex; }
.mc-retry-btn {
    display: inline-flex; align-items: center; gap: 6px;
    height: 38px; padding: 0 16px; border-radius: 8px; border: 1px solid var(--mc-border);
    background: var(--mc-surface); color: var(--mc-ink); font-size: .82rem; font-weight: 650; cursor: pointer;
}
.mc-retry-btn:hover { border-color: var(--mc-gold); color: var(--mc-gold); }

/* ── No search results ── */
.mc-no-results { grid-column: 1 / -1; text-align: center; padding: 48px 20px; color: var(--mc-muted); }
.mc-no-results i { font-size: 1.8rem; color: var(--mc-faint); margin-bottom: 10px; display: block; }
.mc-no-results .t { font-weight: 700; color: var(--mc-ink); margin-bottom: 4px; }

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

/* ── Scroll / lazy load ── */
.mc-scroll-sentinel { height: 1px; }
.mc-scroll-loader { display: none; }
.mc-scroll-loader.show { display: contents; }
.mc-scroll-end {
    grid-column: 1 / -1; text-align: center; padding: 8px; color: var(--mc-faint); font-size: .78rem;
}

/* Skeleton cards — mirror the real card shape so the layout doesn't jump */
.mc-skel { pointer-events: none; }
.mc-skel-bar {
    background: linear-gradient(90deg, #F0EDE6 25%, #F7F5F0 37%, #F0EDE6 63%);
    background-size: 400% 100%;
    animation: mc-shimmer 1.4s ease infinite;
    border-radius: 5px;
}
.mc-skel .mc-card-name.mc-skel-bar { height: 16px; width: 65%; }
.mc-skel .mc-card-meta.mc-skel-bar { height: 12px; width: 80%; margin-top: 2px; }
.mc-skel .mc-status.mc-skel-bar { height: 11px; width: 30%; }
.mc-skel .mc-status.mc-skel-bar::before { content: none; }
.mc-skel .mc-sec-line .mc-skel-bar { height: 26px; width: 100%; border-radius: 6px; }
.mc-skel .mc-sec-line { background: transparent; padding: 0; }
.mc-skel .mc-btn-open.mc-skel-bar { height: 40px; border-radius: 8px; }
.mc-skel .mc-more-btn { visibility: hidden; }
@keyframes mc-shimmer { 0% { background-position: 100% 0; } 100% { background-position: 0 0; } }
@media (prefers-reduced-motion: reduce) { .mc-skel-bar { animation: none; } }
</style>
@endpush

<div class="mc-wrap">
    <div class="mc-page-hdr">
        <div>
            <div class="mc-page-title-row">
                <h1 class="mc-page-title">Menu Composer</h1>
                @if($drafts->total() > 0)
                    <span class="mc-page-count">{{ $drafts->total() }} {{ Str::plural('menu', $drafts->total()) }}</span>
                @endif
            </div>
            <div class="mc-page-sub">Manage event menus and meal selections.</div>
        </div>
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

    {{-- Search --}}
    @unless($drafts->isEmpty() && empty($filters['search']))
    <div class="mc-search-wrap">
        <i class="bi bi-search"></i>
        <input type="text" id="mcSearchInput" class="mc-search" placeholder="Search menus…" autocomplete="off"
               aria-label="Search menus" value="{{ $filters['search'] ?? '' }}">
        <button type="button" id="mcSearchClear" class="mc-search-clear" aria-label="Clear search"
                style="display:{{ ($filters['search'] ?? '') ? '' : 'none' }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    @endunless

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

    @if($drafts->isEmpty() && empty($filters['search']))
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
            <div class="mc-no-results" id="mcNoResults" style="display:{{ $drafts->isEmpty() ? '' : 'none' }}">
                <i class="bi bi-search"></i>
                <div class="t">No menus found</div>
                <div>No menus match your search.</div>
            </div>
            <div class="mc-scroll-sentinel" id="mcSentinel"></div>
            <div class="mc-scroll-loader" id="mcLoader">
                @for($i = 0; $i < 3; $i++)
                <div class="mc-card mc-skel" aria-hidden="true">
                    <div class="mc-card-inner">
                        <div class="mc-card-top">
                            <div class="mc-card-name mc-skel-bar">&nbsp;</div>
                        </div>
                        <div class="mc-card-meta mc-skel-bar">&nbsp;</div>
                        <span class="mc-status mc-skel-bar">&nbsp;</span>
                        <div class="mc-meals-label">&nbsp;</div>
                        <div class="mc-sec-summary">
                            <div class="mc-sec-line"><div class="mc-skel-bar" style="width:100%"></div></div>
                            <div class="mc-sec-line"><div class="mc-skel-bar" style="width:100%"></div></div>
                        </div>
                    </div>
                    <div class="mc-card-foot">
                        <div class="mc-btn-open mc-skel-bar"></div>
                        <div class="mc-more-btn"><i class="bi bi-three-dots"></i></div>
                    </div>
                </div>
                @endfor
            </div>
            <div class="mc-load-error" id="mcLoadError">
                <span>Unable to load more menus.</span>
                <button type="button" class="mc-retry-btn" id="mcRetryBtn"><i class="bi bi-arrow-clockwise"></i> Retry</button>
            </div>
        </div>
        <div class="mc-scroll-end" id="mcScrollEnd" style="display:none"></div>
    @endif
</div>

@unless($drafts->isEmpty() && empty($filters['search']))
<script>
(function () {
    var indexUrl  = '{{ route('menu.composer.index') }}';
    var nextPage  = {{ $drafts->currentPage() + 1 }};
    var hasMore   = {{ $drafts->hasMorePages() ? 'true' : 'false' }};
    var total     = {{ $drafts->total() }};
    var search    = {!! json_encode($filters['search'] ?? '') !!};
    var loading   = false;

    var grid       = document.getElementById('mcDraftGrid');
    var sentinel   = document.getElementById('mcSentinel');
    var loader     = document.getElementById('mcLoader');
    var loadError  = document.getElementById('mcLoadError');
    var retryBtn   = document.getElementById('mcRetryBtn');
    var endMarker  = document.getElementById('mcScrollEnd');
    var noResults  = document.getElementById('mcNoResults');
    var searchInput = document.getElementById('mcSearchInput');
    var searchClear = document.getElementById('mcSearchClear');

    // requestToken guards against a slow, stale search response landing
    // after a newer one — only the response matching the current token
    // is allowed to touch the DOM.
    var requestToken = 0;

    var observer = null;
    if (sentinel) {
        observer = new IntersectionObserver(function (entries) {
            if (entries[0].isIntersecting) loadMore();
        }, { rootMargin: '200px' });
    }

    function setEndMarker(show) {
        if (!endMarker) return;
        if (show) {
            endMarker.textContent = 'All ' + total + ' ' + (total === 1 ? 'menu' : 'menus') + ' loaded';
            endMarker.style.display = '';
        } else {
            endMarker.style.display = 'none';
        }
    }

    function loadMore() {
        if (loading || !hasMore || !grid || !sentinel) return;
        loading = true;
        var myToken = requestToken;
        if (loadError) loadError.classList.remove('show');
        loader.classList.add('show');

        var url = indexUrl + '?page=' + nextPage + (search ? '&search=' + encodeURIComponent(search) : '');
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { if (!res.ok) throw new Error('http ' + res.status); return res.json(); })
            .then(function (data) {
                if (myToken !== requestToken) return; // superseded by a newer search
                loader.classList.remove('show');
                if (data.html) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = data.html;
                    // Insert before the sentinel/loader so they stay pinned to
                    // the bottom of the grid as more real cards arrive.
                    while (tmp.firstChild) grid.insertBefore(tmp.firstChild, sentinel);
                }
                hasMore  = data.hasMore;
                nextPage = data.nextPage;
                total    = data.total;
                loading  = false;
                if (!hasMore) {
                    if (observer) observer.disconnect();
                    setEndMarker(true);
                } else {
                    setEndMarker(false);
                }
            })
            .catch(function () {
                if (myToken !== requestToken) return;
                loading = false;
                loader.classList.remove('show');
                if (loadError) loadError.classList.add('show');
            });
    }

    if (observer && sentinel) observer.observe(sentinel);
    if (retryBtn) retryBtn.addEventListener('click', loadMore);

    // ── Search ────────────────────────────────────────────────────────
    if (searchInput) {
        var debounceTimer = null;

        function runSearch(term) {
            requestToken++; // invalidate any in-flight loadMore()/search response
            var myToken = requestToken;
            loading = false;
            if (loadError) loadError.classList.remove('show');
            setEndMarker(false);
            if (observer) observer.disconnect();

            if (loader) loader.classList.add('show');

            var url = indexUrl + '?page=1' + (term ? '&search=' + encodeURIComponent(term) : '');
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { if (!res.ok) throw new Error('http ' + res.status); return res.json(); })
                .then(function (data) {
                    if (myToken !== requestToken || !grid || !sentinel) return;
                    if (loader) loader.classList.remove('show');

                    // Clear every previously rendered real card (but keep the
                    // sentinel/loader/no-results/error control elements).
                    grid.querySelectorAll('.mc-card:not(.mc-skel)').forEach(function (el) { el.remove(); });

                    if (data.html) {
                        var tmp = document.createElement('div');
                        tmp.innerHTML = data.html;
                        while (tmp.firstChild) grid.insertBefore(tmp.firstChild, sentinel);
                    }

                    search   = term;
                    hasMore  = data.hasMore;
                    nextPage = data.nextPage;
                    total    = data.total;

                    var isEmpty = grid.querySelectorAll('.mc-card:not(.mc-skel)').length === 0;
                    if (noResults) noResults.style.display = isEmpty ? '' : 'none';

                    if (hasMore && observer) {
                        observer.observe(sentinel);
                    } else if (!isEmpty) {
                        setEndMarker(true);
                    }
                })
                .catch(function () {
                    if (myToken !== requestToken) return;
                    if (loader) loader.classList.remove('show');
                    if (loadError) loadError.classList.add('show');
                });
        }

        searchInput.addEventListener('input', function () {
            var term = searchInput.value.trim();
            if (searchClear) searchClear.style.display = term ? '' : 'none';
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () { runSearch(term); }, 300);
        });

        if (searchClear) {
            searchClear.addEventListener('click', function () {
                searchInput.value = '';
                searchClear.style.display = 'none';
                clearTimeout(debounceTimer);
                runSearch('');
                searchInput.focus();
            });
        }
    }
})();
</script>
@endunless

</x-admin-layout>
