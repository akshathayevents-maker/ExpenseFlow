<x-admin-layout title="Meal Plans">
@php
    $categories = \App\Models\MealPlan::categories();
    $catIcon    = ['premium' => 'bi-star', 'custom' => 'bi-sliders', 'standard' => 'bi-egg-fried'];
    $q          = $filters['q'];
    $cat        = $filters['category'];
    $tabUrl     = fn ($s) => route('hall.meal-plans.index', array_filter(['status' => $s, 'q' => $q ?: null, 'category' => $cat]));
    $tiles      = [
        'all'      => ['All plans', 'bi-collection', null],
        'active'   => ['Active', 'bi-check-circle', 'active'],
        'inactive' => ['Inactive', 'bi-pause-circle', 'inactive'],
    ];
    $filtered   = $q !== '' || $cat;
@endphp

@push('styles')
<style>
/* Meal plans — same language as Purchases / Purchase plans: header, status strip, toolbar, table (cards on phones), rail. */
.mp{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.mp a:focus-visible,.mp button:focus-visible,.mp-menu .dropdown-item:focus-visible{outline:2px solid var(--ef-emerald);outline-offset:2px}
.mp-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.mp-title{flex:1 1 260px;min-width:0}
.mp-title h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.mp-title p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.mp-acts{display:flex;gap:8px;flex-wrap:wrap}
.mp-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer;box-shadow:0 1px 2px rgba(19,17,16,.05)}
.mp-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.mp-btn .bi{color:var(--ef-gold)}
.mp-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.mp-btn.--main .bi{color:#f0c97a}
.mp-btn.--sm{min-height:36px;padding:4px 12px;font-size:.8rem}
.mp-btn.--icon{width:36px;min-height:36px;padding:0}
.mp-btn.--icon .bi{color:var(--ef-muted)}
.mp-btn.--icon[aria-expanded="true"]{background:var(--ef-bg-subtle);border-color:var(--ef-border-strong)}
@media (max-width:639.98px){.mp-acts{width:100%;display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.mp-title h1{font-size:1.25rem}.mp-title p .--long{display:none}}

.mp-q{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);overflow:hidden;box-shadow:0 1px 2px rgba(19,17,16,.04)}
.mp-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;border-right:1px solid var(--ef-border);min-width:0;background:var(--tint,transparent)}
.mp-qi:last-child{border-right:0}
.mp-qi:hover{color:var(--ef-ink);filter:brightness(.98)}
.mp-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mp-qi small .bi{color:var(--acc)}
.mp-qi strong{font-size:1.35rem;font-weight:800;font-variant-numeric:tabular-nums;line-height:1.15;color:var(--acc)}
.mp-qi.--zero strong{color:var(--ef-faint)}
.mp-qi.--zero{--tint:transparent}
.mp-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.mp-qi[data-k=all]{--acc:var(--ef-ink)}
.mp-qi[data-k=active]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
.mp-qi[data-k=inactive]{--acc:#5f6b5a;--tint:rgba(95,107,90,.05)}
.mp-qi[data-k=standard]{--acc:#2c5784;--tint:rgba(59,110,165,.06)}
.mp-qi[data-k=premium]{--acc:#9a6413;--tint:rgba(200,144,42,.08)}
.mp-qi[data-k=custom]{--acc:#6b5bb5;--tint:rgba(107,91,181,.05)}
.mp-qi[data-k=avg]{--acc:var(--ef-ink);--tint:var(--ef-bg-subtle);cursor:default}
@media (max-width:899.98px){.mp-q{display:flex;overflow-x:auto;scrollbar-width:none}.mp-q::-webkit-scrollbar{display:none}.mp-qi{flex:0 0 auto;min-width:104px;padding:8px 12px}.mp-qi strong{font-size:1.1rem}}

.mp-body{display:grid;gap:14px}
@media (min-width:1600px){.mp-body{grid-template-columns:minmax(0,1fr) 320px;align-items:start}}
.mp-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);box-shadow:0 1px 2px rgba(19,17,16,.04);min-width:0;border-top:3px solid var(--acc,var(--ef-gold))}
.mp-bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border)}
.mp-search{position:relative;flex:1 1 220px;min-width:0}
.mp-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.mp-search input{width:100%;min-height:40px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);font-size:16px;color:var(--ef-ink)}
.mp-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.mp-count b{color:var(--ef-ink)}

.mp-th,.mp-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;align-items:center;padding:12px 14px}
.mp-th{display:none}
.mp-row{border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,var(--ef-emerald))}
.mp-row:first-child{border-top:0}
.mp-row:hover{background:var(--ef-bg-subtle)}
.mp-row.--off{--tone:var(--ef-border-strong)}
.mp-row.--off .mp-t,.mp-row.--off .mp-price{color:var(--ef-muted)}
.mp-what{display:flex;align-items:center;gap:10px;min-width:0;text-decoration:none;color:var(--ef-ink)}
.mp-ico{flex:0 0 auto;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:var(--tint);color:var(--acc)}
.mp-ico[data-c=standard]{--acc:#2c5784;--tint:rgba(59,110,165,.1)}
.mp-ico[data-c=premium]{--acc:#9a6413;--tint:rgba(200,144,42,.14)}
.mp-ico[data-c=custom]{--acc:#6b5bb5;--tint:rgba(107,91,181,.1)}
.mp-what-b{min-width:0}
.mp-t{display:block;font-weight:750;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mp-what:hover .mp-t{text-decoration:underline}
.mp-s{display:block;font-size:.75rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mp-price{text-align:right;font-weight:800;font-variant-numeric:tabular-nums;font-size:.98rem;white-space:nowrap;line-height:1.15}
.mp-price small{display:block;font-size:.68rem;font-weight:600;color:var(--ef-muted)}
.mp-c-cat,.mp-c-dish,.mp-c-book{font-size:.84rem;white-space:nowrap}
.mp-c-cat,.mp-c-dish,.mp-c-book,.mp-c-st{display:none}
.mp-c-book small{display:block;font-size:.7rem;color:var(--ef-muted)}
.mp-c-book .--up{color:var(--ef-emerald-dk);font-weight:700}
.mp-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:8px;font-size:.68rem;font-weight:800;white-space:nowrap}
.mp-chip.--on{background:rgba(30,122,60,.1);color:var(--ef-emerald-dk)}
.mp-chip.--off{background:var(--ef-bg-subtle);color:var(--ef-muted)}
.mp-mmeta{grid-column:1/-1;padding-left:46px;display:flex;flex-wrap:wrap;gap:4px 12px;font-size:.76rem;color:var(--ef-muted);align-items:center}
.mp-mmeta .--up{color:var(--ef-emerald-dk);font-weight:700}
.mp-do{grid-column:1/-1;padding-left:46px;display:flex;gap:6px;align-items:center}
.mp-do .mp-btn.--sm{flex:1}
@media (max-width:399.98px){.mp-mmeta,.mp-do{padding-left:0}}
@media (min-width:1100px){
  .mp-th,.mp-row{grid-template-columns:minmax(220px,1fr) 110px 90px 120px 90px 110px 130px;gap:14px}
  .mp-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .mp-th .--r{text-align:right}
  .mp-c-cat,.mp-c-dish,.mp-c-book,.mp-c-st{display:block}
  .mp-mmeta{display:none}
  .mp-do{grid-column:auto;padding-left:0;justify-content:flex-end}
  .mp-do .mp-btn.--sm{flex:0 0 auto}
}
.mp-empty{padding:40px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.mp-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.mp-empty a{font-weight:700}
.mp-foot{padding:10px 14px;border-top:1px solid var(--ef-border)}
.mp-foot nav{overflow-x:auto;max-width:100%}

.mp-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:640px) and (max-width:1599.98px){.mp-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}}
.mp-card-h{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint)}
.mp-card-h h2{margin:0;font-size:.92rem;font-weight:800;color:var(--ef-ink);display:flex;align-items:center;gap:8px}
.mp-card-h h2 .bi{color:var(--acc)}
.mp-card-h a{margin-left:auto;font-size:.78rem;font-weight:700;color:var(--ef-gold);text-decoration:none}
.mp-card-b{padding:6px 14px 12px}
.mp-top{display:flex;align-items:center;gap:10px;padding:8px 0;border-top:1px solid var(--ef-border);text-decoration:none;color:var(--ef-ink)}
.mp-top:first-child{border-top:0}
.mp-top-b{flex:1;min-width:0}
.mp-top-b b{display:block;font-size:.86rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.mp-top-b i{display:block;height:4px;border-radius:4px;background:rgba(100,82,42,.1);margin-top:4px;overflow:hidden}
.mp-top-b i span{display:block;height:100%;background:var(--ef-emerald)}
.mp-top-n{font-size:.8rem;font-variant-numeric:tabular-nums;white-space:nowrap;color:var(--ef-muted)}
.mp-top-n b{color:var(--ef-ink)}
.mp-hint{font-size:.82rem;color:var(--ef-muted);margin:6px 0}
.mp-flow{list-style:none;margin:0;padding:0;font-size:.8rem;color:var(--ef-muted);display:grid;gap:7px}
.mp-flow li{display:flex;gap:8px;align-items:flex-start}
.mp-flow b{flex:0 0 auto;width:20px;height:20px;border-radius:50%;background:var(--ef-bg-subtle);color:var(--ef-ink);font-size:.7rem;display:flex;align-items:center;justify-content:center}
@media (max-width:639.98px){.mp-flowcard,.mp-count{display:none}}

.mp-menu{min-width:200px;padding:4px;border:1px solid var(--ef-border);border-radius:10px;background:var(--ef-surface);box-shadow:var(--ef-shadow-hover)}
.mp-menu .dropdown-item{display:flex;align-items:center;gap:8px;min-height:40px;padding:0 12px;font-size:.84rem;border-radius:8px;color:var(--ef-ink-2)}
.mp-menu .dropdown-item i{width:16px;text-align:center;color:var(--ef-muted)}
.mp-menu .dropdown-item:hover,.mp-menu .dropdown-item:focus{background:var(--ef-surface-2);color:var(--ef-ink)}
.mp-menu .dropdown-item.--danger,.mp-menu .dropdown-item.--danger i{color:var(--ef-danger)}
.mp-menu .dropdown-divider{border-color:var(--ef-border);margin:4px 0}
</style>
@endpush

<div class="mp">
    <header class="mp-head">
        <div class="mp-title">
            <h1>Meal plans</h1>
            <p>Catering packages and per-guest prices attached to hall bookings.<span class="--long"> Inactive plans stay on old bookings but can't be picked for new ones.</span></p>
        </div>
        <div class="mp-acts">
            <a href="{{ route('hall.bookings.index') }}" class="mp-btn"><i class="bi bi-calendar3"></i> Bookings</a>
            <a href="{{ route('hall.meal-plans.create') }}" class="mp-btn --main"><i class="bi bi-plus-lg"></i> New meal plan</a>
        </div>
    </header>

    <nav class="mp-q" aria-label="Plan status">
        @foreach($tiles as $k => [$label, $icon, $val])
            <a href="{{ $tabUrl($val) }}" data-k="{{ $k }}" class="mp-qi {{ $status === $val ? '--on' : '' }} {{ $stats[$k] ? '' : '--zero' }}" @if($status === $val) aria-current="page" @endif>
                <small><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $stats[$k] }}</strong>
            </a>
        @endforeach
        @foreach($categories as $ck => $cl)
            @php $n = (int) ($catCounts[$ck] ?? 0); $on = $cat === $ck; @endphp
            <a href="{{ route('hall.meal-plans.index', array_filter(['status' => $status, 'q' => $q ?: null, 'category' => $on ? null : $ck])) }}" data-k="{{ $ck }}" class="mp-qi {{ $on ? '--on' : '' }} {{ $n ? '' : '--zero' }}" @if($on) aria-current="true" @endif>
                <small><i class="bi {{ $catIcon[$ck] ?? 'bi-egg-fried' }}"></i>{{ $cl }}</small><strong>{{ $n }}</strong>
            </a>
        @endforeach
    </nav>

    <div class="mp-body">
        <section class="mp-panel" aria-label="Meal plans">
            <form method="GET" class="mp-bar" role="search">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                @if($cat)<input type="hidden" name="category" value="{{ $cat }}">@endif
                <label class="mp-search"><i class="bi bi-search"></i>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search plan name or dishes" aria-label="Search meal plans">
                </label>
                <button class="mp-btn" type="submit">Search</button>
                @if($filtered)<a class="mp-btn" href="{{ route('hall.meal-plans.index', array_filter(['status' => $status])) }}">Clear</a>@endif
                @if($plans->total())<span class="mp-count"><b>{{ $plans->total() }}</b> {{ Str::plural('plan', $plans->total()) }}</span>@endif
            </form>

            @if($plans->count())
                <div class="mp-th" aria-hidden="true">
                    <span>Plan</span><span>Category</span><span>Dishes</span><span>Bookings</span><span>Status</span><span class="--r">Price</span><span></span>
                </div>
            @endif
            <div>
            @forelse($plans as $plan)
                @php
                    $dishes = $plan->dishList();
                    $nd     = count($dishes);
                    $preview = implode(', ', array_slice($dishes, 0, 4)) . ($nd > 4 ? ' +' . ($nd - 4) . ' more' : '');
                    $catName = $categories[$plan->category] ?? ucfirst((string) $plan->category);
                    $mtLabel = $plan->mealTimesLabel();
                @endphp
                <article class="mp-row {{ $plan->is_active ? '' : '--off' }}" data-plan="{{ $plan->id }}">
                    <a class="mp-what" href="{{ route('hall.meal-plans.edit', $plan) }}">
                        <span class="mp-ico" data-c="{{ $plan->category }}"><i class="bi {{ $catIcon[$plan->category] ?? 'bi-egg-fried' }}"></i></span>
                        <span class="mp-what-b">
                            <span class="mp-t">{{ $plan->name }}</span>
                            <span class="mp-s">{{ $preview !== '' ? $preview : 'No dishes listed yet' }}</span>
                        </span>
                    </a>
                    <span class="mp-c-cat">{{ $catName }}@if($mtLabel)<small style="display:block;font-size:.7rem;color:var(--ef-muted)">{{ $mtLabel }}</small>@endif</span>
                    <span class="mp-c-dish">{{ $nd }} {{ Str::plural('dish', $nd) }}</span>
                    <span class="mp-c-book">{{ $plan->bookings_count }} total
                        @if($plan->upcoming_count)<small class="--up">{{ $plan->upcoming_count }} upcoming</small>@endif
                    </span>
                    <span class="mp-c-st"><span class="mp-chip {{ $plan->is_active ? '--on' : '--off' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span></span>
                    <span class="mp-price">₹{{ number_format($plan->price_per_person, 0) }}<small>per guest</small></span>
                    <div class="mp-mmeta">
                        <span class="mp-chip {{ $plan->is_active ? '--on' : '--off' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                        <span>{{ $catName }}@if($mtLabel) · {{ $mtLabel }}@endif</span>
                        <span>{{ $nd }} {{ Str::plural('dish', $nd) }}</span>
                        <span>{{ $plan->bookings_count }} {{ Str::plural('booking', $plan->bookings_count) }}</span>
                        @if($plan->upcoming_count)<span class="--up">{{ $plan->upcoming_count }} upcoming</span>@endif
                    </div>
                    <div class="mp-do">
                        <a href="{{ route('hall.meal-plans.edit', $plan) }}" class="mp-btn --sm" aria-label="Edit {{ $plan->name }}"><i class="bi bi-pencil"></i> Edit</a>
                        <div class="dropdown">
                            <button type="button" class="mp-btn --icon mp-more" data-bs-toggle="dropdown" data-bs-offset="0,4" aria-expanded="false" aria-haspopup="true" aria-label="More actions for {{ $plan->name }}"><i class="bi bi-three-dots"></i></button>
                            <ul class="mp-menu dropdown-menu dropdown-menu-end">
                                <li>
                                    <form method="POST" action="{{ route('hall.meal-plans.toggle-status', $plan) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="dropdown-item w-100 border-0 bg-transparent">
                                            <i class="bi bi-{{ $plan->is_active ? 'pause-circle' : 'play-circle' }}"></i>
                                            {{ $plan->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </li>
                                <li><a href="{{ route('hall.bookings.index', ['meal_plan_search' => $plan->name]) }}" class="dropdown-item"><i class="bi bi-calendar-check"></i> View bookings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item --danger w-100 border-0 bg-transparent"
                                            data-plan-name="{{ $plan->name }}"
                                            data-delete-url="{{ route('hall.meal-plans.destroy', $plan) }}"
                                            data-has-bookings="{{ $plan->bookings_count > 0 ? '1' : '0' }}"
                                            onclick="openMpDelete(this)"><i class="bi bi-trash"></i> Delete</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </article>
            @empty
                <div class="mp-empty">
                    @if($filtered || $status)
                        <i class="bi bi-search"></i>No meal plans match these filters. <a href="{{ route('hall.meal-plans.index') }}">Show all</a>
                    @else
                        <i class="bi bi-egg-fried"></i>No meal plans yet. <a href="{{ route('hall.meal-plans.create') }}">Create the first one</a>
                    @endif
                </div>
            @endforelse
            </div>
            @if($plans->hasPages())<div class="mp-foot">{{ $plans->links() }}</div>@endif
        </section>

        <aside class="mp-rail">
            <section class="mp-panel" style="--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)">
                <div class="mp-card-h"><h2><i class="bi bi-trophy"></i> Most booked</h2></div>
                <div class="mp-card-b">
                    @php $max = max(1, (int) ($topPlans->first()->bookings_count ?? 1)); @endphp
                    @forelse($topPlans as $tp)
                        <a class="mp-top" href="{{ route('hall.bookings.index', ['meal_plan_search' => $tp->name]) }}">
                            <span class="mp-top-b"><b>{{ $tp->name }}</b><i><span style="width:{{ round($tp->bookings_count / $max * 100) }}%"></span></i></span>
                            <span class="mp-top-n"><b>{{ $tp->bookings_count }}</b> {{ Str::plural('booking', $tp->bookings_count) }}</span>
                        </a>
                    @empty
                        <p class="mp-hint">No bookings use a meal plan yet.</p>
                    @endforelse
                    @if($stats['active'])<p class="mp-hint">Average active price <b style="color:var(--ef-ink)">₹{{ number_format($stats['avg_price']) }}</b> per guest.</p>@endif
                </div>
            </section>
            <section class="mp-panel mp-flowcard" style="--acc:#2c5784;--tint:rgba(59,110,165,.05)">
                <div class="mp-card-h"><h2><i class="bi bi-signpost-split"></i> How plans work</h2></div>
                <div class="mp-card-b">
                    <ol class="mp-flow">
                        <li><b>1</b><span>Create a plan with its dishes and per-guest price.</span></li>
                        <li><b>2</b><span>Pick it on a hall booking; the dishes become the draft menu.</span></li>
                        <li><b>3</b><span>The kitchen sees the final menu on the kitchen schedule.</span></li>
                        <li><b>4</b><span>Deactivate a plan to retire it without touching old bookings.</span></li>
                    </ol>
                </div>
            </section>
        </aside>
    </div>
</div>

{{-- Delete confirmation modal --}}
<div class="modal fade" id="mpDeleteModal" tabindex="-1" aria-labelledby="mpDeleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border:1px solid var(--ef-border);border-radius:var(--ef-radius);background:var(--ef-surface)">
            <div class="modal-header">
                <h2 class="modal-title fs-5 fw-bold mb-0" id="mpDeleteTitle">Delete Meal Plan</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3" style="color:var(--ef-ink-2);font-size:.9rem">
                    Remove <strong id="mpDeleteName" style="color:var(--ef-ink)"></strong> from the catering catalogue.
                    This action cannot be undone.
                </p>
                <div id="mpDeleteWarning" class="d-none" style="background:rgba(200,75,68,.07);border:1px solid rgba(200,75,68,.22);border-radius:var(--ef-radius-sm);color:var(--ef-danger);font-size:.82rem;padding:12px 14px;">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    This plan has existing bookings. Deleting it may affect booking records.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="mp-btn" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="mpDeleteForm">
                    @csrf @method('DELETE')
                    <button type="submit" class="mp-btn" style="background:var(--ef-danger);border-color:var(--ef-danger);color:#fff">
                        <i class="bi bi-trash"></i> Delete Plan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openMpDelete(btn) {
    document.getElementById('mpDeleteName').textContent = btn.dataset.planName;
    document.getElementById('mpDeleteForm').action      = btn.dataset.deleteUrl;
    document.getElementById('mpDeleteWarning').classList.toggle('d-none', btn.dataset.hasBookings !== '1');
    bootstrap.Modal.getOrCreateInstance(document.getElementById('mpDeleteModal')).show();
}

/* More menu positioning.
   Bootstrap's default (absolute, clippingParents boundary) can clip or misplace
   the menu; use fixed positioning against the viewport and reserve room for the
   topbar, the sticky mobile CTA and the PWA install banner so it flips/shifts
   instead of opening under them. Must be a window-capture hook: Bootstrap's own
   delegated dropdown handler is a document-capture listener and would win. */
(function () {
    function visibleBottom(el) {
        if (!el || getComputedStyle(el).display === 'none' || el.getAttribute('aria-hidden') === 'true') return 0;
        return el.offsetHeight;
    }
    function menuPadding() {
        var tb = document.getElementById('topbar');
        var bottom = Math.max(
            visibleBottom(document.getElementById('pwa-install-banner'))
        );
        return { top: (tb ? tb.getBoundingClientRect().bottom : 0) + 8, bottom: bottom + 8, left: 8, right: 8 };
    }
    window.addEventListener('click', function (e) {
        var btn = e.target.closest('.mp-more');
        if (!btn || !window.bootstrap || bootstrap.Dropdown.getInstance(btn)) return;
        new bootstrap.Dropdown(btn, {
            popperConfig: function (cfg) {
                var opts = { boundary: document.documentElement, rootBoundary: 'viewport', padding: menuPadding() };
                return Object.assign({}, cfg, {
                    strategy: 'fixed',
                    modifiers: cfg.modifiers.concat([
                        { name: 'preventOverflow', options: Object.assign({ altAxis: true }, opts) },
                        { name: 'flip', options: Object.assign({ fallbackPlacements: ['top-end', 'bottom-start', 'top-start'] }, opts) }
                    ])
                });
            }
        });
    }, true);
})();
</script>
@endpush
</x-admin-layout>
