{{-- Kitchen inventory control: shared page styles (uses --ef-* tokens only). --}}
@once
@push('styles')
<style>
.ic-wrap{max-width:1100px;margin:0 auto;padding-bottom:90px}
.ic-head{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px}
.ic-head h1{font-size:1.35rem;font-weight:800;color:var(--ef-ink);margin:0;flex:1;min-width:200px}
.ic-back{color:var(--ef-gold);text-decoration:none;font-size:.9rem;font-weight:600}
.ic-card{background:var(--ef-surface);border:1px solid var(--ef-border);border-radius:var(--ef-radius);padding:16px;margin-bottom:14px;box-shadow:var(--ef-shadow)}
.ic-card h2{font-size:1rem;font-weight:750;margin:0 0 12px;color:var(--ef-ink)}
.ic-fields{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
.ic-label{display:block;font-size:.78rem;font-weight:700;color:var(--ef-muted);margin-bottom:4px;text-transform:uppercase;letter-spacing:.03em}
.ic-input,.ic-select{width:100%;min-height:46px;border:1.5px solid var(--ef-border);border-radius:10px;padding:8px 12px;font-size:1rem;background:var(--ef-surface);color:var(--ef-ink)}
.ic-input:focus,.ic-select:focus{outline:none;border-color:var(--ef-gold);box-shadow:0 0 0 3px var(--ef-gold-soft)}
.ic-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:48px;padding:10px 20px;border-radius:12px;font-size:1rem;font-weight:700;border:1.5px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);text-decoration:none;cursor:pointer}
.ic-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.ic-btn--gold{background:var(--ef-gold);border-color:var(--ef-gold);color:#fff}
.ic-btn--gold:hover{background:var(--ef-gold-hi);color:#fff}
.ic-btn--dark{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.ic-btn--dark:hover{color:#fff;opacity:.92}
.ic-btn--danger{color:var(--ef-danger);border-color:rgba(200,75,68,.35)}
.ic-btn--sm{min-height:38px;padding:6px 14px;font-size:.88rem}
.ic-btn[disabled]{opacity:.55;pointer-events:none}
.ic-chip{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:999px;font-size:.78rem;font-weight:700;white-space:nowrap}
.ic-chip--neutral{background:rgba(20,20,18,.06);color:var(--ef-muted)}
.ic-chip--warn{background:rgba(216,154,61,.15);color:#7D5218}
.ic-chip--info{background:rgba(59,110,165,.12);color:#2c5282}
.ic-chip--ok{background:rgba(15,123,95,.12);color:#0A5240}
.ic-chip--bad{background:rgba(200,75,68,.12);color:#9B2C2C}
.ic-note{font-size:.85rem;color:var(--ef-muted)}
.ic-err{background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.3);color:#9B2C2C;border-radius:10px;padding:10px 14px;margin-bottom:12px;font-size:.9rem}
.ic-err ul{margin:0;padding-left:18px}
.ic-sticky{position:sticky;bottom:0;z-index:20;background:var(--ef-surface);border-top:1px solid var(--ef-border);padding:12px 16px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;box-shadow:0 -6px 18px rgba(0,0,0,.06);border-radius:14px 14px 0 0}
.ic-total{font-size:1.25rem;font-weight:800;color:var(--ef-ink);margin-right:auto}
.ic-total small{display:block;font-size:.72rem;font-weight:700;color:var(--ef-faint);text-transform:uppercase}
/* grid */
.ic-grid{width:100%}
.ic-row{display:grid;gap:8px;align-items:start;padding:8px 0;border-bottom:1px solid var(--ef-border)}
.ic-grid[data-mode=purchase] .ic-row{grid-template-columns:32px minmax(180px,2.4fr) 100px 100px 110px 80px 110px 40px}
.ic-grid[data-mode=request] .ic-row{grid-template-columns:32px minmax(200px,3fr) 120px 110px 140px 40px}
.ic-row--head{font-size:.72rem;font-weight:800;text-transform:uppercase;color:var(--ef-faint);letter-spacing:.04em;border-bottom:2px solid var(--ef-border)}
.ic-row .ic-input,.ic-row .ic-select{min-height:44px;padding:6px 10px}
.ic-rownum{font-weight:700;color:var(--ef-faint);padding-top:12px;text-align:center}
.ic-amount{font-weight:750;padding-top:11px;text-align:right}
.ic-hint{font-size:.74rem;color:var(--ef-faint);margin-top:3px;line-height:1.3}
.ic-hint--warn{color:#7D5218}
.ic-hint--bad{color:#9B2C2C}
.ic-row.--dup{background:rgba(216,154,61,.08)}
.ic-row.--bad .ic-item-input{border-color:var(--ef-danger)}
.ic-del{border:none;background:none;color:var(--ef-faint);font-size:1.2rem;min-height:44px;cursor:pointer}
.ic-del:hover{color:var(--ef-danger)}
.ic-lbl-m{display:none}
.ic-banner{display:flex;gap:10px;align-items:center;flex-wrap:wrap;background:rgba(216,154,61,.12);border:1px solid rgba(216,154,61,.35);border-radius:12px;padding:10px 14px;margin-bottom:12px;font-size:.9rem}
.ic-table{width:100%;border-collapse:collapse}
.ic-table th{font-size:.72rem;text-transform:uppercase;color:var(--ef-faint);font-weight:800;text-align:left;padding:8px;border-bottom:2px solid var(--ef-border)}
.ic-table td{padding:10px 8px;border-bottom:1px solid var(--ef-border);vertical-align:top;font-size:.92rem}
.ic-table .r{text-align:right}
.ic-tabs{display:flex;gap:8px;overflow-x:auto;margin-bottom:12px;padding-bottom:2px}
.ic-tab{flex-shrink:0;padding:8px 14px;border-radius:999px;border:1px solid var(--ef-border);color:var(--ef-muted);text-decoration:none;font-weight:650;font-size:.88rem;background:var(--ef-surface)}
.ic-tab.--on{background:var(--ef-ink);color:#fff;border-color:var(--ef-ink)}
.ic-list-card{display:flex;gap:12px;align-items:center;padding:14px 16px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);margin-bottom:10px;text-decoration:none;color:var(--ef-ink)}
.ic-list-card:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.ic-list-main{flex:1;min-width:0}
.ic-list-title{font-weight:750;font-size:1rem}
.ic-list-sub{font-size:.84rem;color:var(--ef-muted)}
.ic-kv{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px}
.ic-kv div small{display:block;font-size:.72rem;font-weight:700;text-transform:uppercase;color:var(--ef-faint)}
.ic-kv div strong{font-size:.98rem}
.ic-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px;margin-bottom:16px}
.ic-tile{display:block;padding:18px;border-radius:16px;border:1px solid var(--ef-border);background:var(--ef-surface);text-decoration:none;color:var(--ef-ink);box-shadow:var(--ef-shadow)}
.ic-tile:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.ic-tile i{font-size:1.5rem;color:var(--ef-gold)}
.ic-tile b{display:block;font-size:1.6rem;font-weight:800;margin-top:4px}
.ic-tile span{font-size:.88rem;font-weight:650;color:var(--ef-muted)}
.ic-tile--primary{background:var(--ef-ink);color:#fff}
.ic-tile--primary i,.ic-tile--primary span{color:var(--ef-on-dark-gold, #e9c46a)}
.ic-tile--primary:hover{color:#fff}
@media (max-width:760px){
  .ic-row--head{display:none}
  .ic-grid[data-mode] .ic-row{grid-template-columns:1fr 1fr;border:1px solid var(--ef-border);border-radius:14px;padding:10px 12px 12px;margin-bottom:10px;position:relative}
  .ic-row > .ic-cell-item{grid-column:1 / -1}
  .ic-rownum{position:absolute;top:6px;right:44px;padding:0;font-size:.75rem}
  .ic-del{position:absolute;top:0;right:4px}
  .ic-lbl-m{display:block;font-size:.7rem;font-weight:800;color:var(--ef-faint);text-transform:uppercase;margin-bottom:2px}
  .ic-amount{text-align:left;padding-top:0}
  .ic-menu-row{grid-template-columns:1fr 40px !important}
  .ic-menu-row > :nth-child(2),.ic-menu-row > :nth-child(3){grid-column:1}
  .ic-table thead{display:none}
  .ic-table tr{display:block;border:1px solid var(--ef-border);border-radius:12px;margin-bottom:10px;padding:6px 4px}
  .ic-table td{display:flex;justify-content:space-between;gap:10px;border:none;padding:5px 10px}
  .ic-table td[data-l]::before{content:attr(data-l);font-size:.72rem;font-weight:800;text-transform:uppercase;color:var(--ef-faint)}
  .ic-table .r{text-align:right}
}
</style>
@endpush
@endonce
@once
@push('scripts')
<script>
// Double-submit guard for inventory forms (keeps the clicked button's value).
document.addEventListener('submit', function (e) {
    const form = e.target;
    if (!form.matches('[data-ic-form]') || e.defaultPrevented) return;
    if (form.dataset.sent === '1') { e.preventDefault(); return; }
    const btn = e.submitter;
    if (btn && btn.name) {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = btn.name; h.value = btn.value; form.appendChild(h);
    }
    form.dataset.sent = '1';
    setTimeout(() => form.querySelectorAll('button[type=submit]').forEach(b => { b.disabled = true; if (b === btn) b.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving…'; }), 0);
});
</script>
@endpush
@endonce
