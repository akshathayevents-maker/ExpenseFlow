{{--
    Navigation chrome for admin / manager: styles, desktop collapse, command palette (Ctrl/⌘ K) and
    mobile drawer behaviour. The palette only lists pages/actions this user can open (AdminNavigation).
--}}
@php $efNavIndex = \App\Support\AdminNavigation::for(auth()->user())->searchIndex(); @endphp
<style>
/* ── search entry in sidebar ─────────────────────────────────────── */
.sb-search{display:flex;align-items:center;gap:10px;width:calc(100% - 24px);margin:10px 12px 4px;padding:9px 12px;border-radius:10px;
  border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.05);color:rgba(255,255,255,.62);font-size:.82rem;cursor:pointer;text-align:left}
.sb-search:hover{background:rgba(255,255,255,.09);color:#fff}
.sb-search kbd{margin-left:auto;font-size:.66rem;background:rgba(255,255,255,.1);color:rgba(255,255,255,.6);border-radius:5px;padding:2px 6px;font-family:inherit}
.sidebar-group-body .sb-sub{padding:9px 0 3px 1.6rem;font-size:.6rem;font-weight:760;letter-spacing:.14em;text-transform:uppercase;color:rgba(255,255,255,.36)}
/* ── desktop collapsed rail ──────────────────────────────────────── */
#sb-collapse{display:none}
@media (min-width:992px){
  #sb-collapse{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border:0;border-radius:8px;background:transparent;color:#fff;margin-right:4px}
  #sb-collapse:hover{background:rgba(255,255,255,.1)}
  body.sb-collapsed{--sb-width:72px}
  body.sb-collapsed #sidebar .sb-user-strip>div:not(.sb-user-avatar),body.sb-collapsed #sidebar .sb-user-online-dot,body.sb-collapsed #sidebar .sb-label,
  body.sb-collapsed #sidebar .sb-grp-label,body.sb-collapsed #sidebar .sidebar-chevron,body.sb-collapsed #sidebar .sidebar-group-body,
  body.sb-collapsed #sidebar .sb-meta,body.sb-collapsed #grp-account-wrap{display:none!important}
  body.sb-collapsed #sidebar .sb-user-strip{justify-content:center;padding:12px 0}
  body.sb-collapsed #sidebar .sb-search{width:44px;margin:10px auto 4px;justify-content:center;padding:9px 0}
  body.sb-collapsed #sidebar .sidebar-group-btn,body.sb-collapsed #sidebar .sidebar-standalone .nav-link{justify-content:center;padding-left:0;padding-right:0;font-size:0}
  body.sb-collapsed #sidebar .sidebar-standalone .nav-link i{font-size:1.05rem;margin:0}
  body.sb-collapsed #sidebar .sidebar-group-btn.has-active{box-shadow:inset 3px 0 0 #d6b97a}
  body.sb-collapsed #sidebar .sb-signout-btn{font-size:0;justify-content:center}
  body.sb-collapsed #sidebar .sb-signout-btn i{font-size:1rem}
  #sidebar,#main-content,#topbar .brand{transition:width .2s ease,margin-left .2s ease}
}
/* ── breadcrumbs ─────────────────────────────────────────────────── */
.ef-crumbs{display:flex;flex-wrap:wrap;align-items:center;gap:4px 8px;margin:-8px 0 14px;font-size:.78rem;color:var(--ef-muted,#6b6459)}
.ef-crumbs a{color:inherit;text-decoration:none;font-weight:650}.ef-crumbs a:hover{color:var(--ef-ink,#151513)}
.ef-crumbs .bi{font-size:.66rem;opacity:.6}
@media (max-width:991.98px){.ef-crumbs{margin:-4px 0 10px}}
/* phones: the menu is hidden and the page title already names the page — the trail only costs space */
@media (max-width:767.98px){.ef-crumbs{display:none}}
/* ── topbar quick actions ───────────────────────────────────────── */
.ef-tb-btn{display:inline-flex;align-items:center;gap:6px;min-height:34px;padding:4px 10px;border-radius:9px;border:1px solid rgba(255,255,255,.18);background:transparent;color:#fff;font-size:.82rem;font-weight:650}
.ef-tb-btn:hover{background:rgba(255,255,255,.1);color:#fff}
@media (max-width:575.98px){.ef-tb-btn .ef-tb-txt{display:none}.ef-tb-btn{padding:4px 8px}}
.ef-qa-menu{min-width:240px;padding:6px;border-radius:12px;border:1px solid rgba(0,0,0,.08);box-shadow:0 12px 32px rgba(0,0,0,.14)}
.ef-qa-menu .dropdown-item{display:flex;align-items:center;gap:10px;border-radius:8px;padding:9px 10px;font-size:.88rem;min-height:40px}
.ef-qa-menu .dropdown-item i{color:#b8893e;width:18px;text-align:center}
/* ── command palette ─────────────────────────────────────────────── */
.cmdk{border:0;padding:0;border-radius:16px;width:min(560px,calc(100vw - 24px));max-height:min(560px,calc(100dvh - 48px));box-shadow:0 24px 60px rgba(0,0,0,.3);overflow:hidden}
.cmdk::backdrop{background:rgba(10,12,10,.5);backdrop-filter:blur(2px)}
.cmdk-in{display:flex;align-items:center;gap:10px;padding:12px 14px;border-bottom:1px solid #ece7de}
.cmdk-in input{flex:1;border:0;outline:0;font-size:16px;background:transparent;min-width:0}
.cmdk-in kbd{font-size:.68rem;background:#f1ede6;border-radius:5px;padding:2px 6px;color:#6b6459;font-family:inherit}
.cmdk-list{list-style:none;margin:0;padding:6px;overflow-y:auto;max-height:min(460px,calc(100dvh - 120px))}
.cmdk-list li a{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:10px;color:#151513;text-decoration:none;min-height:44px}
.cmdk-list li a i{color:#b8893e;width:18px;text-align:center}
.cmdk-list li a small{margin-left:auto;font-size:.72rem;color:#8d867a;white-space:nowrap}
.cmdk-list li a.--on,.cmdk-list li a:hover{background:rgba(184,137,62,.12)}
.cmdk-head{padding:8px 12px 4px;font-size:.66rem;font-weight:760;letter-spacing:.12em;text-transform:uppercase;color:#8d867a}
.cmdk-empty{padding:18px;text-align:center;color:#8d867a;font-size:.88rem}
</style>

<dialog class="cmdk" id="cmdk" aria-label="Search pages and actions">
    <div class="cmdk-in"><i class="bi bi-search" style="color:#8d867a"></i>
        <input type="text" id="cmdkInput" placeholder="Search pages, actions, stock items…" autocomplete="off" aria-label="Search" aria-controls="cmdkList">
        <kbd>Esc</kbd></div>
    <ul class="cmdk-list" id="cmdkList" role="listbox"></ul>
</dialog>

<script>
(() => {
    const PAGES = @json($efNavIndex);
    const ITEM_SEARCH = @json(route('inventory.items.search'));
    const STOCK = @json(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isStorekeeper() ? route('inventory.stock') : null);
    const ITEM_SHOW = @json(auth()->user()->isAdmin() ? url('admin/inventory/items') : null);
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
    const dlg = document.getElementById('cmdk'), input = document.getElementById('cmdkInput'), list = document.getElementById('cmdkList');
    let active = 0, seq = 0;

    // ── desktop collapse (remembered per device) ──
    const KEY = 'ef-sb-collapsed';
    try { if (localStorage.getItem(KEY) === '1') document.body.classList.add('sb-collapsed'); } catch (e) {}
    document.getElementById('sb-collapse')?.addEventListener('click', () => {
        const on = document.body.classList.toggle('sb-collapsed');
        try { localStorage.setItem(KEY, on ? '1' : '0'); } catch (e) {}
    });
    // collapsed rail: clicking a group expands the sidebar and opens that group
    document.querySelectorAll('#sidebar [data-nav-group]').forEach(b => b.addEventListener('click', () => {
        if (document.body.classList.contains('sb-collapsed')) {
            document.body.classList.remove('sb-collapsed');
            try { localStorage.setItem(KEY, '0'); } catch (e) {}
        }
    }));
    // mobile: close the drawer once a link is chosen
    document.querySelectorAll('#sidebar a.nav-link').forEach(a => a.addEventListener('click', () => {
        if (innerWidth < 992) { document.getElementById('sidebar').classList.remove('show'); document.getElementById('sidebar-overlay')?.classList.remove('show'); }
    }));

    // ── command palette ──
    const score = (p, q) => {
        const t = p.t.toLowerCase(), g = (p.g || '').toLowerCase(), k = (p.k || '').toLowerCase();
        if (t.startsWith(q)) return 3; if (t.includes(q)) return 2; if (g.includes(q) || k.includes(q)) return 1; return 0;
    };
    function render(rows) {
        if (!rows.length) { list.innerHTML = '<li class="cmdk-empty">Nothing found</li>'; return; }
        let html = '', last = null;
        rows.forEach((r, i) => {
            const head = r.h || (r.g === 'Create' ? 'Create' : 'Pages');
            if (head !== last) { html += '<li class="cmdk-head">' + esc(head) + '</li>'; last = head; }
            html += '<li><a href="' + esc(r.u) + '" role="option" data-i="' + i + '" class="' + (i === active ? '--on' : '') + '"><i class="bi ' + esc(r.i) + '"></i><span>' + esc(r.t) + '</span>' + (r.g && r.g !== 'Create' ? '<small>' + esc(r.g) + '</small>' : '') + '</a></li>';
        });
        list.innerHTML = html;
    }
    let rows = [];
    async function search() {
        const q = input.value.trim().toLowerCase(); active = 0;
        rows = q ? PAGES.map(p => [p, score(p, q)]).filter(x => x[1]).sort((a, b) => b[1] - a[1]).map(x => x[0]).slice(0, 12)
                 : PAGES.filter(p => p.g === 'Create').concat(PAGES.filter(p => p.g !== 'Create').slice(0, 8));
        render(rows);
        if (q.length >= 2 && (STOCK || ITEM_SHOW)) {
            const my = ++seq;
            try {
                const r = await fetch(ITEM_SEARCH + '?q=' + encodeURIComponent(q), {headers: {Accept: 'application/json'}});
                if (!r.ok || my !== seq) return;
                const d = await r.json();
                const items = (d.results || []).slice(0, 5).map(it => ({t: it.name + (it.name_ta ? ' · ' + it.name_ta : ''), g: 'Stock item', h: 'Stock items', i: 'bi-box',
                    u: ITEM_SHOW ? ITEM_SHOW + '/' + it.id : STOCK + '?q=' + encodeURIComponent(it.name)}));
                rows = rows.concat(items); render(rows);
            } catch (e) {}
        }
    }
    function open() { input.value = ''; search(); dlg.showModal(); setTimeout(() => input.focus(), 30); }
    document.addEventListener('click', e => { if (e.target.closest('[data-cmdk-open]')) { e.preventDefault(); open(); } });
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); dlg.open ? dlg.close() : open(); }
    });
    input.addEventListener('input', search);
    input.addEventListener('keydown', e => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault(); active = Math.max(0, Math.min(rows.length - 1, active + (e.key === 'ArrowDown' ? 1 : -1))); render(rows);
            list.querySelector('.--on')?.scrollIntoView({block: 'nearest'});
        } else if (e.key === 'Enter' && rows[active]) { e.preventDefault(); location.href = rows[active].u; }
        else if (e.key === 'Escape') { e.preventDefault(); dlg.close(); }
    });
    dlg.addEventListener('click', e => { if (e.target === dlg) dlg.close(); });
})();
</script>
