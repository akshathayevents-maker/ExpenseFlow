@push('styles')
<style>
/* Purchase plans (staff workflow) — same language as the other list pages (pl-*). */
.pl{display:flex;flex-direction:column;gap:14px;max-width:1400px}
.pl-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.pl-back{display:inline-flex;align-items:center;gap:6px;font-size:.8rem;font-weight:700;color:var(--ef-muted);text-decoration:none;margin-bottom:4px}
.pl-head h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink);display:flex;flex-wrap:wrap;gap:8px;align-items:center;overflow-wrap:anywhere}
.pl-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.pl-acts{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.pl-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer}
.pl-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.pl-btn .bi{color:var(--ef-gold)}
.pl-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.pl-btn.--main .bi{color:var(--ef-gold-hi)}
.pl-btn.--ok{background:var(--ef-emerald);border-color:var(--ef-emerald);color:#fff}
.pl-btn.--ok .bi{color:#fff}
.pl-btn.--bad{color:var(--ef-danger)}
.pl-btn.--bad .bi{color:var(--ef-danger)}
.pl-btn.--sm{min-height:36px;padding:4px 12px;font-size:.8rem}
@media (max-width:639.98px){.pl-head h1{font-size:1.25rem}.pl-acts{width:100%}.pl-acts .pl-btn{flex:1}}
.pl-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:8px;font-size:.72rem;font-weight:800;white-space:nowrap;background:var(--bg,var(--ef-bg-subtle));color:var(--fg,var(--ef-muted))}
.pl-chip[data-s=draft]{--bg:var(--ef-bg-subtle);--fg:var(--ef-muted)}
.pl-chip[data-s=submitted]{--bg:color-mix(in srgb,var(--ef-gold) 15%,transparent);--fg:color-mix(in srgb,var(--ef-gold) 70%,#000)}
.pl-chip[data-s=approved]{--bg:color-mix(in srgb,var(--ef-emerald) 12%,transparent);--fg:var(--ef-emerald-dk)}
.pl-chip[data-s=rejected]{--bg:color-mix(in srgb,var(--ef-danger) 10%,transparent);--fg:var(--ef-danger)}
.pl-chip[data-s=ordered]{--bg:color-mix(in srgb,var(--ef-info) 12%,transparent);--fg:color-mix(in srgb,var(--ef-info) 75%,#000)}
.pl-chip[data-s=completed]{--bg:color-mix(in srgb,var(--ef-bluegray) 12%,transparent);--fg:color-mix(in srgb,var(--ef-bluegray) 80%,#000)}
.pl-chip[data-s=cancelled]{--bg:var(--ef-bg-subtle);--fg:var(--ef-faint)}
.pl-chip[data-s=as_planned]{--bg:color-mix(in srgb,var(--ef-emerald) 12%,transparent);--fg:var(--ef-emerald-dk)}
.pl-chip[data-s=short],.pl-chip[data-s=not_bought]{--bg:color-mix(in srgb,var(--ef-amber) 12%,transparent);--fg:color-mix(in srgb,var(--ef-amber) 62%,#000)}
.pl-chip[data-s=over]{--bg:color-mix(in srgb,var(--ef-bluegray) 12%,transparent);--fg:color-mix(in srgb,var(--ef-bluegray) 80%,#000)}
.pl-chip[data-s=awaiting]{--bg:color-mix(in srgb,var(--ef-info) 12%,transparent);--fg:color-mix(in srgb,var(--ef-info) 75%,#000)}
.pl-chip[data-s=dropped]{--bg:var(--ef-bg-subtle);--fg:var(--ef-faint)}
.pl-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}
.pl-ph{display:flex;flex-wrap:wrap;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint,transparent)}
.pl-ph h2{margin:0;font-size:.95rem;font-weight:800;display:flex;align-items:center;gap:8px}
.pl-ph h2 .bi{color:var(--acc)}
.pl-ph .--r{margin-left:auto;font-size:.78rem;color:var(--ef-muted)}
.pl-pb{padding:12px 14px}
.pl-field{display:flex;flex-direction:column;gap:4px;font-size:.74rem;font-weight:700;color:var(--ef-muted);margin:0;min-width:0}
.pl-field input,.pl-field select,.pl-field textarea{min-height:42px;padding:6px 10px;border-radius:9px;border:1px solid var(--ef-border);font-size:16px;background:var(--ef-surface);color:var(--ef-ink);width:100%;min-width:0;font-weight:500}
.pl-field textarea{min-height:64px}
.pl-field input:focus,.pl-field select:focus,.pl-field textarea:focus{outline:none;border-color:var(--ef-gold);box-shadow:0 0 0 3px color-mix(in srgb,var(--ef-gold) 15%,transparent)}
.pl-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
@media (max-width:719.98px){.pl-grid{grid-template-columns:1fr}}
.pl-grid .--wide{grid-column:1/-1}
.pl-note{font-size:.82rem;color:var(--ef-muted);margin:0}
.pl-err{border:1px solid color-mix(in srgb,var(--ef-danger) 30%,transparent);background:color-mix(in srgb,var(--ef-danger) 6%,transparent);color:var(--ef-danger);border-radius:12px;padding:10px 14px;font-size:.86rem}
.pl-err ul{margin:4px 0 0;padding-left:18px}
.pl-steps{display:flex;gap:0;overflow-x:auto;scrollbar-width:none;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface)}
.pl-steps::-webkit-scrollbar{display:none}
.pl-step{flex:1 0 auto;min-width:110px;display:flex;align-items:center;gap:8px;padding:10px 12px;font-size:.78rem;font-weight:700;color:var(--ef-faint);border-right:1px solid var(--ef-border)}
.pl-step:last-child{border-right:0}
.pl-step b{width:22px;height:22px;flex:0 0 auto;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.7rem;background:var(--ef-bg-subtle);color:var(--ef-muted)}
.pl-step.--done{color:var(--ef-ink)}
.pl-step.--done b{background:var(--ef-emerald);color:#fff}
.pl-step.--now{color:var(--ef-ink);background:color-mix(in srgb,var(--ef-gold) 8%,transparent)}
.pl-step.--now b{background:var(--ef-gold);color:#fff}
.pl-step.--stop b{background:var(--ef-danger);color:#fff}
.pl-step small{display:block;font-weight:500;color:var(--ef-muted);font-size:.68rem}
</style>
@endpush
