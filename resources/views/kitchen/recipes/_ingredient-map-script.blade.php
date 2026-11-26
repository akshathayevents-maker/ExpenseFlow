@include('inventory._item-picker')
@once
<style>
/* ── Recipe editor layout fixes (create + edit) ───────────────────────── */
/* ingredient footer: tidy 2-column grid instead of squeezed flex items */
.ef-ingredient-row .ef-ing-footer{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px 12px;align-items:start}
.ef-ingredient-row .ef-ing-footer>*{min-width:0;flex:none}
.ef-ingredient-row .ef-ing-footer .ef-optional-label{grid-column:1/-1;padding-top:0}
.ef-ingredient-row .ef-ing-footer .ef-qty-note-wrap.--show{grid-column:1/-1}
.ef-ing-ta,.ef-ing-map{min-width:0}
.ef-ing-ta .ef-label small,.ef-ing-map .ef-label small{display:block;font-weight:400;text-transform:none;letter-spacing:0;color:var(--ef-faint)}
.ef-ing-map{grid-column:auto}
@media (max-width:640px){
  .ef-ingredient-row{padding:12px}
  .ef-ingredient-row .ef-ing-grid{grid-template-columns:minmax(0,1fr) minmax(0,1fr) 36px;row-gap:8px}
  .ef-ingredient-row .ef-ing-grid>div:first-child{grid-column:1/-1}
  .ef-ingredient-row .ef-ing-grid>.ef-ing-remove{grid-row:1;grid-column:3;align-self:end}
  .ef-ingredient-row .ef-ing-grid>div:first-child{grid-column:1/3}
  .ef-ingredient-row .ef-ing-footer{grid-template-columns:minmax(0,1fr)}
}
/* inputs: never smaller than 16px on phones (prevents iOS zoom), comfortable touch height */
@media (max-width:640px){.ef-create-layout .ef-input,.ef-create-layout .ef-select,.ef-create-layout .ef-textarea{font-size:16px;min-height:46px}}
/* steps: in "Both" mode on wide screens pair English and Tamil side by side (title | title, instruction | instruction) */
@media (min-width:900px){
  [data-edit-lang=both] .ef-sop-card-body{display:grid;grid-template-columns:1fr 1fr;gap:10px 14px}
  [data-edit-lang=both] .ef-sop-card-body>.bl-en:nth-child(1){order:1}
  [data-edit-lang=both] .ef-sop-card-body>.bl-ta:nth-child(3){order:2}
  [data-edit-lang=both] .ef-sop-card-body>.bl-en:nth-child(2){order:3}
  [data-edit-lang=both] .ef-sop-card-body>.bl-ta:nth-child(4){order:4}
  [data-edit-lang=both] .ef-sop-card-body>.bl-note{order:5;grid-column:1/-1}
  [data-edit-lang=both] .ef-sop-card-body>div[style*="max-width:160px"]{order:6;grid-column:1/-1}
}
.ef-sop-card-body textarea.ef-textarea{min-height:96px}
/* bottom save bar: one compact row on phones (was two stacked full-width buttons) */
@media (max-width:767.98px){
  .ef-kitchen-save-bar{flex-direction:row!important;padding:10px 12px calc(10px + env(safe-area-inset-bottom))!important}
  .ef-kitchen-save-bar .ef-btn{width:auto!important;flex:0 0 auto;min-height:46px}
  .ef-kitchen-save-bar .ef-btn-dark{width:auto!important;flex:1;min-height:46px;justify-content:center}
}
.ef-create-layout{padding-bottom:84px}
.ef-ing-map{min-width:0}
.ef-ing-map.--needs .ef-input{border-color:var(--ef-amber);background:rgba(216,154,61,.07)}
.ef-ing-map-flag{margin-left:6px;padding:1px 8px;border-radius:999px;background:rgba(216,154,61,.18);color:#7D5218;font-size:.7rem;font-weight:750}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const attach = root => root.querySelectorAll('[data-ing-map]:not([data-picker])').forEach(input => {
        const wrap = input.closest('[data-ing-map-wrap]'), hid = wrap.querySelector('[data-ing-map-id]');
        window.IcItemPicker?.attach(input, {
            allowCreate: true,
            onPick(r) { hid.value = r.id; wrap.classList.remove('--needs'); wrap.querySelector('.ef-ing-map-flag')?.remove(); },
            onClear() { hid.value = ''; },
        });
    });
    attach(document);
    const builder = document.getElementById('ingredientBuilder');
    if (builder) new MutationObserver(() => attach(builder)).observe(builder, {childList: true, subtree: true});
});
</script>
@endonce
