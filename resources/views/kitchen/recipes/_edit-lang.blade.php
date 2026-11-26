{{-- Editing mode for bilingual fields. Only hides inputs (nothing is cleared), so switching never loses text. --}}
@once
<style>
.bl-switch{display:inline-flex;border:1px solid var(--ef-border);border-radius:999px;padding:3px;gap:2px;background:var(--ef-surface,#fff);margin:0 0 10px}
.bl-switch button{min-height:38px;padding:4px 14px;border:0;border-radius:999px;background:none;font-weight:750;font-size:.86rem;color:var(--ef-muted);cursor:pointer}
.bl-switch button[aria-pressed=true]{background:var(--ef-ink);color:#fff}
[data-edit-lang=en] .bl-ta,[data-edit-lang=ta] .bl-en{display:none!important}
.bl-ta textarea,.bl-ta input{font-family:"Noto Sans Tamil","Latha",system-ui,sans-serif}
.bl-missing{margin-left:6px;padding:1px 8px;border-radius:999px;background:rgba(216,154,61,.18);color:#7D5218;font-size:.7rem;font-weight:750}
.bl-note{margin-top:4px;font-size:.84rem}
.bl-note summary{cursor:pointer;color:var(--ef-muted);font-weight:650;min-height:32px;display:flex;align-items:center}
@media (min-width:900px){[data-edit-lang=both] .ef-sop-card-body{display:grid;grid-template-columns:1fr 1fr;gap:10px 14px}
  [data-edit-lang=both] .ef-sop-card-body>div[style*="max-width:160px"],[data-edit-lang=both] .ef-sop-card-body>.bl-note{grid-column:1/-1}}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const roots = () => document.querySelectorAll('[data-edit-lang]');
    const set = l => { roots().forEach(r => r.dataset.editLang = l); document.querySelectorAll('.bl-switch button').forEach(b => b.setAttribute('aria-pressed', b.dataset.l === l ? 'true' : 'false')); try { localStorage.setItem('ef-recipe-edit-lang', l); } catch (e) {} };
    document.addEventListener('click', e => { const b = e.target.closest('.bl-switch button'); if (b) set(b.dataset.l); });
    let start = 'both'; try { start = localStorage.getItem('ef-recipe-edit-lang') || (innerWidth < 900 ? 'en' : 'both'); } catch (e) {}
    set(start);
    // a required English field hidden in Tamil mode would block submit invisibly: show both and focus it
    document.querySelectorAll('form').forEach(f => f.addEventListener('invalid', () => set('both'), true));
});
</script>
@endonce
<div class="bl-switch" role="group" aria-label="Editing language">
    <button type="button" data-l="en" aria-pressed="false">English</button>
    <button type="button" data-l="ta" aria-pressed="false" lang="ta">தமிழ்</button>
    <button type="button" data-l="both" aria-pressed="true">Both</button>
</div>
