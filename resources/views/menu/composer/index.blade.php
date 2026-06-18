<x-admin-layout title="Menu Composer">
@push('styles')
<style>
:root {
    --mc-gold: #a0723a; --mc-gold-hi: #b8832a; --mc-gold-dim: rgba(160,114,58,.12);
    --mc-surface: #fff; --mc-page: #f7f5f2; --mc-border: #e8e2d8;
    --mc-ink: #1c1712; --mc-muted: #7a6e62; --mc-faint: #c0b8ac;
    --mc-radius: 16px; --mc-r-sm: 10px;
}
.mc-wrap { max-width: 900px; margin: 0 auto; }
.mc-page-hdr { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.mc-page-title { font-size: 1.4rem; font-weight: 800; color: var(--mc-ink); flex: 1; }
.mc-new-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--mc-gold); color: #fff; border: none;
    border-radius: var(--mc-r-sm); padding: 10px 20px;
    font-size: .92rem; font-weight: 700; text-decoration: none; transition: background .12s;
}
.mc-new-btn:hover { background: var(--mc-gold-hi); color: #fff; }
.mc-tpl-btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: transparent; color: var(--mc-gold); border: 1.5px solid var(--mc-gold);
    border-radius: var(--mc-r-sm); padding: 9px 18px;
    font-size: .88rem; font-weight: 700; text-decoration: none; transition: background .12s;
}
.mc-tpl-btn:hover { background: var(--mc-gold-dim); color: var(--mc-gold); }

.mc-empty { text-align: center; padding: 64px 24px; }
.mc-empty-icon { font-size: 3rem; color: var(--mc-faint); margin-bottom: 16px; }
.mc-empty-title { font-size: 1.05rem; font-weight: 800; color: var(--mc-ink); margin-bottom: 6px; }
.mc-empty-sub { font-size: .88rem; color: var(--mc-muted); margin-bottom: 24px; }

.mc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.mc-card {
    background: var(--mc-surface); border: 1.5px solid var(--mc-border);
    border-radius: var(--mc-radius); padding: 20px 20px 16px;
    transition: box-shadow .15s, border-color .15s; display: flex; flex-direction: column;
}
.mc-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.08); border-color: var(--mc-gold); }
.mc-card-title { font-size: 1rem; font-weight: 800; color: var(--mc-ink); margin-bottom: 6px; line-height: 1.3; }
.mc-card-meta { font-size: .8rem; color: var(--mc-muted); margin-bottom: 12px; display: flex; flex-direction: column; gap: 3px; }
.mc-card-meta span { display: flex; align-items: center; gap: 5px; }
.mc-card-count {
    display: inline-block; padding: 3px 10px;
    background: var(--mc-gold-dim); color: var(--mc-gold);
    border-radius: 999px; font-size: .76rem; font-weight: 700; margin-bottom: 14px;
}
.mc-card-actions { display: flex; gap: 8px; margin-top: auto; flex-wrap: wrap; }
.mc-btn-open {
    flex: 1; padding: 9px 12px; background: var(--mc-gold); color: #fff;
    border: none; border-radius: var(--mc-r-sm); font-size: .88rem;
    font-weight: 700; text-decoration: none; text-align: center; transition: background .12s;
}
.mc-btn-open:hover { background: var(--mc-gold-hi); color: #fff; }
.mc-btn-dupe {
    padding: 9px 12px; color: #1e6ab5; border: 1.5px solid #bdd7f5;
    background: transparent; border-radius: var(--mc-r-sm); cursor: pointer;
    font-size: .88rem; font-weight: 600; text-decoration: none;
}
.mc-btn-dupe:hover { background: #e8f1fc; }
.mc-btn-del {
    padding: 9px 12px; color: #dc2626; border: 1.5px solid #fee2e2;
    background: transparent; border-radius: var(--mc-r-sm); cursor: pointer;
    font-size: .88rem; font-weight: 600;
}
.mc-btn-del:hover { background: #fee2e2; }

/* Templates quick-load */
.mc-tpl-section { margin-bottom: 28px; }
.mc-tpl-section-title { font-size: .82rem; font-weight: 700; color: var(--mc-muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 10px; }
.mc-tpl-pills { display: flex; gap: 8px; flex-wrap: wrap; }
.mc-tpl-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; background: #fdf8f3; border: 1px solid #e8dece;
    border-radius: 999px; font-size: .82rem; font-weight: 600; color: var(--mc-gold);
    text-decoration: none; cursor: pointer;
}
.mc-tpl-pill:hover { background: var(--mc-gold-dim); border-color: var(--mc-gold); }
.mc-tpl-pill form { margin: 0; padding: 0; }
.mc-tpl-pill button { background: none; border: none; padding: 0; cursor: pointer; font-size: .82rem; font-weight: 600; color: var(--mc-gold); }

/* Scroll sentinel & loader */
.mc-scroll-sentinel { height: 1px; }
.mc-scroll-loader {
    display: none; text-align: center; padding: 24px;
    color: var(--mc-muted); font-size: .88rem;
}
.mc-scroll-loader.show { display: block; }
.mc-scroll-spinner {
    display: inline-block; width: 20px; height: 20px;
    border: 2px solid var(--mc-faint); border-top-color: var(--mc-gold);
    border-radius: 50%; animation: mc-spin .7s linear infinite; vertical-align: middle; margin-right: 6px;
}
@keyframes mc-spin { to { transform: rotate(360deg); } }

@media (max-width: 767.98px) {
    .mc-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

<div class="mc-wrap">
    <div class="mc-page-hdr">
        <h1 class="mc-page-title">
            <i class="bi bi-pencil-square me-2" style="color:var(--mc-gold)"></i>Menu Composer
        </h1>
        <a href="{{ route('menu.templates.index') }}" class="mc-tpl-btn">
            <i class="bi bi-collection"></i>
            Templates{{ $templates->isNotEmpty() ? ' (' . $templates->count() . ')' : '' }}
        </a>
        <a href="{{ route('menu.composer.create') }}" class="mc-new-btn">
            <i class="bi bi-plus-lg"></i> New Menu
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert" style="border-radius:var(--mc-r-sm)">
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
            <form method="POST" action="{{ route('menu.templates.load', $tpl) }}" style="display:inline">
                @csrf
                <button type="submit" class="mc-tpl-pill">
                    <i class="bi bi-collection"></i> {{ $tpl->name }}
                </button>
            </form>
            @endforeach
        </div>
    </div>
    @endif

    @if($drafts->isEmpty())
        <div class="mc-empty">
            <div class="mc-empty-icon"><i class="bi bi-file-earmark-text"></i></div>
            <p class="mc-empty-title">No saved menus yet</p>
            <p class="mc-empty-sub">Create a menu and save a draft to access it here.</p>
            <a href="{{ route('menu.composer.create') }}" class="mc-new-btn">
                <i class="bi bi-plus-lg"></i> Create First Menu
            </a>
        </div>
    @else
        <div class="mc-grid" id="mcDraftGrid">
            @include('menu.composer._draft_cards', ['drafts' => $drafts])
        </div>

        {{-- Sentinel watched by IntersectionObserver --}}
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
        .catch(function () {
            loading = false;
            loader.classList.remove('show');
        });
    }

    var observer = new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) loadMore();
    }, { rootMargin: '200px' });

    observer.observe(sentinel);
})();
</script>
@endif

</x-admin-layout>
