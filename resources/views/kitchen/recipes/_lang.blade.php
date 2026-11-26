{{--
    Recipe content language switch: English / தமிழ் / Both. Only changes recipe text (not the app UI).
    Mark content with .l-en / .l-ta inside an element with [data-rlang-root]. Choice saved per device.
    Missing Tamil is shown as missing (.l-missing), never replaced by English.
--}}
@once
<style>
.rl-switch{display:inline-flex;border:1px solid var(--ef-border,#e5e0d6);border-radius:999px;padding:3px;background:var(--ef-surface,#fff);gap:2px}
.rl-switch button{min-height:38px;min-width:64px;padding:4px 14px;border:0;border-radius:999px;background:none;font-weight:750;font-size:.86rem;color:var(--ef-muted,#6b6459);cursor:pointer}
.rl-switch button[aria-pressed=true]{background:var(--ef-ink,#151513);color:#fff}
[data-rlang=en] .l-ta,[data-rlang=ta] .l-en{display:none!important}
[data-rlang=both] .l-ta{display:block;margin-top:4px}
[data-rlang=both] .l-ta.l-inline{display:inline;margin:0}
[data-rlang=both] .l-tag{display:inline-block}
.l-tag{display:none;font-size:.7rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase;color:var(--ef-faint,#8d867a);margin-right:6px}
.l-ta{font-family:"Noto Sans Tamil","Latha","Vijaya",system-ui,sans-serif;line-height:1.65}
.l-missing{color:#9a6413;font-style:italic;font-size:.86em}
</style>
<script>
(() => {
    const KEY = 'ef-recipe-lang';
    const get = () => { try { return localStorage.getItem(KEY) || 'both'; } catch (e) { return 'both'; } };
    function apply(lang) {
        document.querySelectorAll('[data-rlang-root]').forEach(r => r.dataset.rlang = lang);
        document.querySelectorAll('.rl-switch button').forEach(b => b.setAttribute('aria-pressed', b.dataset.lang === lang ? 'true' : 'false'));
        document.querySelectorAll('[data-rlang-link]').forEach(a => { const u = new URL(a.href); u.searchParams.set('lang', lang); a.href = u; });
    }
    document.addEventListener('click', e => {
        const b = e.target.closest('.rl-switch button'); if (!b) return;
        try { localStorage.setItem(KEY, b.dataset.lang); } catch (_) {}
        apply(b.dataset.lang);
    });
    document.addEventListener('DOMContentLoaded', () => apply(get()));
})();
</script>
@endonce
<div class="rl-switch" role="group" aria-label="Recipe language">
    <button type="button" data-lang="en" aria-pressed="false">English</button>
    <button type="button" data-lang="ta" aria-pressed="false" lang="ta">தமிழ்</button>
    <button type="button" data-lang="both" aria-pressed="true">Both</button>
</div>
