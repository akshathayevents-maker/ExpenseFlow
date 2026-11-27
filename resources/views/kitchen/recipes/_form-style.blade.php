{{-- Recipe create / edit — same language as the other redesigned pages (rc-*). Field names and JS unchanged. --}}
@push('styles')
<style>
.rc-page{max-width:1400px}
.rc-page .ef-create-hero{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding:0 0 12px;margin:0 0 14px;border-bottom:1px solid var(--ef-border);background:none;box-shadow:none;border-radius:0}
.rc-page .ef-create-hero > div:first-child{flex:1 1 280px;min-width:0}
.rc-page .ef-back{display:inline-flex;align-items:center;gap:6px;margin:0 0 6px!important;font-size:.8rem;font-weight:700;color:var(--ef-muted);text-decoration:none}
.rc-page .ef-back:hover{color:var(--ef-ink)}
.rc-page .ef-eyebrow{display:none}
.rc-page .ef-create-title{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink);overflow-wrap:anywhere}
.rc-page .ef-create-hero .ef-shell-note{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.rc-for{display:inline-flex;align-items:center;gap:6px;margin-top:6px;padding:3px 10px;border-radius:8px;background:rgba(30,122,60,.08);color:var(--ef-emerald-dk);font-size:.78rem;font-weight:700}
.rc-steps{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.rc-steps a{display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:0 10px;border-radius:999px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:.76rem;font-weight:700;color:var(--ef-ink-2);text-decoration:none}
.rc-steps a b{display:inline-flex;width:18px;height:18px;border-radius:50%;align-items:center;justify-content:center;font-size:.66rem;color:#fff;background:var(--acc)}
.rc-steps a:hover{border-color:var(--acc)}
@media (max-width:639.98px){.rc-page .ef-create-title{font-size:1.25rem}}

/* section cards: coloured top edge + tinted header, like the list-page panels */
.rc-page .rc-sec{border-top:3px solid var(--acc)!important;border-radius:14px!important;scroll-margin-top:12px}
.rc-page .rc-sec .ef-section-intro{margin:-4px 0 14px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.rc-page .rc-sec .ef-section-number{background:var(--acc)!important;color:#fff!important;border-color:var(--acc)!important}
.rc-page .rc-sec .ef-section-heading{font-size:1.05rem;font-weight:800}
.rc-page .rc-sec .ef-section-copy{font-size:.82rem}
.rc-sec.--info,.rc-steps .--info{--acc:#9a6413}
.rc-sec.--ing,.rc-steps .--ing{--acc:var(--ef-emerald)}
.rc-sec.--sop,.rc-steps .--sop{--acc:#2c5784}

/* ingredients: compact cards with a green edge */
.rc-page .ef-ingredient-row{border-radius:12px;border-left:3px solid rgba(30,122,60,.45);padding:10px 12px;margin-bottom:8px}
.rc-page .ef-ingredient-row:focus-within{border-color:var(--ef-emerald);box-shadow:0 0 0 3px rgba(30,122,60,.1)}
.rc-page .ef-ing-footer{margin-top:8px;padding-top:8px;border-top:1px dashed var(--ef-border)}
/* steps: blue edge */
.rc-page .ef-sop-card{border-radius:12px;border-left:3px solid rgba(44,87,132,.5)}
.rc-page .ef-sop-card:focus-within{border-color:#2c5784;box-shadow:0 0 0 3px rgba(59,110,165,.1)}
.rc-page .ef-add-row-btn{min-height:44px;border-radius:10px;border-style:dashed;font-weight:700;width:100%;justify-content:center}
.rc-page .rc-sec.--ing .ef-add-row-btn{color:var(--ef-emerald-dk);border-color:rgba(30,122,60,.45)}
.rc-page .rc-sec.--sop .ef-add-row-btn{color:#2c5784;border-color:rgba(44,87,132,.45)}

/* inputs: 16px on phones (no iOS zoom) */
@media (max-width:767.98px){.rc-page .ef-input,.rc-page .ef-select,.rc-page .ef-textarea{font-size:16px}}

/* live preview panel */
.rc-page .ef-recipe-preview-panel{border-radius:14px;border-top:3px solid var(--ef-ink);overflow:hidden}
.rc-page .ef-preview-panel-head{font-weight:800;font-size:.9rem;background:var(--ef-bg-subtle)}
@media (max-width:1199.98px){.rc-page .ef-create-layout > aside{display:none}}

/* save bar */
.rc-page .ef-kitchen-save-bar{gap:10px;padding:10px 16px calc(10px + env(safe-area-inset-bottom,0px));border-top:1px solid var(--ef-border);box-shadow:0 -6px 20px rgba(19,17,16,.06)}
.rc-page .ef-kitchen-save-bar .ef-btn{min-height:44px;border-radius:10px;font-weight:700}
.rc-page .ef-kitchen-save-bar .ef-btn-dark{min-height:44px;border-radius:10px!important;font-weight:800!important}
.rc-save-hint{margin-right:auto;font-size:.78rem;color:var(--ef-muted)}
@media (min-width:992px){.rc-page .ef-kitchen-save-bar{padding-left:calc(var(--sb-width,280px) + 24px)}}
@media (max-width:639.98px){.rc-save-hint{display:none}.rc-page .ef-kitchen-save-bar{display:grid;grid-template-columns:1fr 2fr}}
</style>
@endpush
