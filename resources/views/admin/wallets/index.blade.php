<x-admin-layout title="Wallets">
@php
    $healthState = function (\App\Models\Wallet $w): string {
        if ($w->isNegative()) return 'negative';
        if ($w->balance < 200) return 'critical';
        if ($w->isLow()) return 'low';
        return 'healthy';
    };
    $healthLabel = ['negative' => 'Negative', 'critical' => 'Very low', 'low' => 'Low', 'healthy' => 'Healthy'];
    $maxBar = 5000; // ₹5,000 fills the bar
    $tiles = [
        '' => ['All wallets', 'bi-wallet2', $stats['total_wallets'], 'all'],
        'critical' => ['Negative', 'bi-x-octagon', $stats['negative_count'], 'neg'],
        'low' => ['Low (< ₹500)', 'bi-exclamation-triangle', $stats['low_balance_count'], 'low'],
        'healthy' => ['Healthy', 'bi-check2-circle', $stats['healthy_count'], 'ok'],
    ];
    $url = fn ($h) => route('admin.wallets.index', array_filter(['health' => $h, 'search' => $search ?: null]));
    $rupee = fn ($v) => '₹'.number_format((float) $v, 0);
@endphp

@push('styles')
<style>
/* Wallets — same language as the other list pages (wl-*). */
.wl{display:flex;flex-direction:column;gap:14px;max-width:1600px}
.wl-head{display:flex;flex-wrap:wrap;align-items:flex-end;gap:10px 16px;padding-bottom:12px;border-bottom:1px solid var(--ef-border)}
.wl-head h1{margin:0;font-size:1.45rem;font-weight:800;letter-spacing:-.02em;color:var(--ef-ink)}
.wl-head p{margin:2px 0 0;font-size:.84rem;color:var(--ef-muted)}
.wl-head p b{color:var(--ef-ink)}
.wl-head p .--bad{color:var(--ef-danger)}
.wl-acts{margin-left:auto;display:flex;gap:8px;flex-wrap:wrap}
.wl-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:42px;padding:8px 14px;border-radius:10px;border:1px solid var(--ef-border);background:var(--ef-surface);color:var(--ef-ink);font-weight:700;font-size:.86rem;text-decoration:none;white-space:nowrap;cursor:pointer}
.wl-btn:hover{border-color:var(--ef-gold);color:var(--ef-ink)}
.wl-btn .bi{color:var(--ef-gold)}
.wl-btn.--main{background:var(--ef-ink);border-color:var(--ef-ink);color:#fff}
.wl-btn.--main .bi{color:#f0c97a}
.wl-btn.--sm{min-height:36px;padding:4px 12px;font-size:.8rem}
@media (max-width:639.98px){.wl-head h1{font-size:1.25rem}.wl-acts{width:100%;display:grid;grid-template-columns:1fr 1fr}}

.wl-q{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1px;border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-border);overflow:hidden}
.wl-qi{position:relative;display:flex;flex-direction:column;gap:2px;padding:11px 14px;color:var(--ef-ink);text-decoration:none;min-width:0;background:linear-gradient(var(--tint,transparent),var(--tint,transparent)),var(--ef-surface)}
.wl-qi:hover{color:var(--ef-ink);filter:brightness(.98)}
.wl-qi small{display:flex;align-items:center;gap:6px;font-size:.72rem;font-weight:700;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-qi small .bi{color:var(--acc)}
.wl-qi strong{font-size:1.35rem;font-weight:800;line-height:1.15;color:var(--acc);font-variant-numeric:tabular-nums}
.wl-qi.--zero strong{color:var(--ef-faint)}
.wl-qi.--zero{--tint:transparent}
.wl-qi.--on::after{content:"";position:absolute;left:0;right:0;bottom:0;height:3px;background:var(--acc)}
.wl-qi[data-k=all]{--acc:var(--ef-ink)}
.wl-qi[data-k=neg]{--acc:var(--ef-danger);--tint:rgba(192,57,43,.05)}
.wl-qi[data-k=low]{--acc:#9a6413;--tint:rgba(200,144,42,.08)}
.wl-qi[data-k=ok]{--acc:var(--ef-emerald);--tint:rgba(30,122,60,.05)}
@media (max-width:639.98px){.wl-q{grid-template-columns:repeat(2,minmax(0,1fr))}.wl-qi{padding:8px 10px}.wl-qi strong{font-size:1.15rem}}

.wl-body{display:grid;gap:14px}
@media (min-width:1600px){.wl-body{grid-template-columns:minmax(0,1fr) 320px;align-items:start}}
.wl-panel{border:1px solid var(--ef-border);border-radius:14px;background:var(--ef-surface);min-width:0;overflow:hidden;border-top:3px solid var(--acc,var(--ef-gold))}
.wl-bar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;padding:10px 12px;border-bottom:1px solid var(--ef-border)}
.wl-search{position:relative;flex:1 1 220px;min-width:0;margin:0}
.wl-search .bi{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--ef-faint)}
.wl-search input{width:100%;min-height:42px;padding:6px 12px 6px 36px;border-radius:10px;border:1px solid var(--ef-border);font-size:16px;background:var(--ef-surface);color:var(--ef-ink)}
.wl-count{margin-left:auto;font-size:.78rem;color:var(--ef-muted);white-space:nowrap}
.wl-count b{color:var(--ef-ink)}

.wl-th,.wl-row{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:6px 12px;align-items:center;padding:11px 14px}
.wl-th{display:none}
.wl-row{border-top:1px solid var(--ef-border);border-left:3px solid var(--tone,transparent)}
.wl-row:first-of-type{border-top:0}
.wl-row:hover{background:var(--ef-bg-subtle)}
.wl-row[data-state=negative]{--tone:var(--ef-danger)}
.wl-row[data-state=critical]{--tone:#d66e28}
.wl-row[data-state=low]{--tone:#d89a3d}
.wl-who{display:flex;align-items:center;gap:10px;min-width:0;text-decoration:none;color:var(--ef-ink)}
.wl-av{flex:0 0 auto;width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;background:rgba(200,144,42,.12);color:#9a6413;font-weight:800;font-size:.78rem}
.wl-t{display:block;font-weight:750;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-who:hover .wl-t{text-decoration:underline}
.wl-s{display:block;font-size:.74rem;color:var(--ef-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.wl-role{display:inline-block;padding:0 6px;border-radius:6px;font-size:.64rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;background:var(--ef-bg-subtle);color:var(--ef-muted);margin-right:4px}
.wl-role.--manager{background:rgba(200,144,42,.14);color:#8a5a10}
.wl-bal{text-align:right;white-space:nowrap}
.wl-bal b{display:block;font-size:1.05rem;font-weight:800;font-variant-numeric:tabular-nums}
.wl-row[data-state=negative] .wl-bal b{color:var(--ef-danger)}
.wl-row[data-state=critical] .wl-bal b,.wl-row[data-state=low] .wl-bal b{color:#9a6413}
.wl-meter{display:block;height:5px;border-radius:4px;background:var(--ef-bg-subtle);overflow:hidden;margin-top:4px;min-width:80px}
.wl-meter i{display:block;height:100%;background:var(--ef-emerald)}
.wl-row[data-state=low] .wl-meter i,.wl-row[data-state=critical] .wl-meter i{background:#d89a3d}
.wl-chip{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:8px;font-size:.68rem;font-weight:800;white-space:nowrap}
.wl-chip.--negative{background:rgba(192,57,43,.1);color:var(--ef-danger)}
.wl-chip.--critical{background:rgba(214,110,40,.12);color:#a3480f}
.wl-chip.--low{background:rgba(200,144,42,.15);color:#8a5a10}
.wl-chip.--healthy{background:rgba(30,122,60,.1);color:var(--ef-emerald-dk)}
.wl-c{font-size:.82rem;white-space:nowrap}
.wl-c small{display:block;font-size:.7rem;color:var(--ef-muted)}
.wl-c-st,.wl-c-30,.wl-c-last{display:none}
.wl-m{grid-column:1/-1;display:flex;flex-wrap:wrap;gap:4px 12px;align-items:center;font-size:.76rem;color:var(--ef-muted);padding-left:46px}
.wl-do{grid-column:1/-1;display:flex;gap:6px;padding-left:46px}
.wl-do .wl-btn{flex:1}
@media (max-width:399.98px){.wl-m,.wl-do{padding-left:0}}
@media (min-width:1200px){
  .wl-th,.wl-row{grid-template-columns:minmax(200px,1fr) 130px 100px 140px 120px 210px;gap:12px}
  .wl-th{display:grid;padding:8px 14px;background:var(--ef-bg-subtle);border-bottom:1px solid var(--ef-border);font-size:.66rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--ef-muted)}
  .wl-th .--r{text-align:right}
  .wl-c-st,.wl-c-30,.wl-c-last{display:block}
  .wl-m{display:none}
  .wl-do{grid-column:auto;padding-left:0;justify-content:flex-end}
  .wl-do .wl-btn{flex:0 0 auto}
}
.wl-empty{padding:40px 16px;text-align:center;color:var(--ef-muted);font-size:.9rem}
.wl-empty .bi{display:block;font-size:2rem;color:var(--ef-faint);margin-bottom:6px}
.wl-foot{padding:10px 14px;border-top:1px solid var(--ef-border);overflow-x:auto}
.wl-rail{display:flex;flex-direction:column;gap:14px;min-width:0}
@media (min-width:700px) and (max-width:1599.98px){.wl-rail{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start}}
.wl-ph{display:flex;align-items:center;gap:8px;padding:11px 14px;border-bottom:1px solid var(--ef-border);background:var(--tint)}
.wl-ph h2{margin:0;font-size:.92rem;font-weight:800;display:flex;align-items:center;gap:8px}
.wl-ph h2 .bi{color:var(--acc)}
.wl-pb{padding:6px 14px 12px}
.wl-kv{display:flex;justify-content:space-between;gap:10px;padding:8px 0;border-top:1px solid var(--ef-border);font-size:.82rem;color:var(--ef-muted);text-decoration:none}
.wl-kv:first-child{border-top:0}
.wl-kv b{color:var(--ef-ink);font-variant-numeric:tabular-nums}
.wl-kv b.--bad{color:var(--ef-danger)}
.wl-kv b.--warn{color:#9a6413}
a.wl-kv:hover b{text-decoration:underline}
.wl-note{font-size:.8rem;color:var(--ef-muted);margin:0}
</style>
@endpush

<div class="wl">
    <header class="wl-head">
        <div style="flex:1 1 280px;min-width:0">
            <h1>Wallets</h1>
            <p>Employee advance balances · <b>{{ $rupee($stats['total_balance']) }}</b> held across {{ $stats['total_wallets'] }} {{ Str::plural('wallet', $stats['total_wallets']) }}@if($stats['negative_count']) · <b class="--bad">{{ $stats['negative_count'] }} negative</b>@endif</p>
        </div>
        <div class="wl-acts">
            <a href="{{ route('admin.expense-requests.index', ['status' => 'reimbursement_pending']) }}" class="wl-btn"><i class="bi bi-arrow-repeat"></i> Reimbursements</a>
            <a href="{{ route('admin.expense-requests.index') }}" class="wl-btn --main"><i class="bi bi-receipt"></i> Expenses</a>
        </div>
    </header>

    @if(session('success'))<div class="alert alert-success mb-0">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger mb-0">{{ session('error') }}</div>@endif

    <nav class="wl-q" aria-label="Wallet health">
        @foreach($tiles as $k => [$label, $icon, $n, $key])
            <a href="{{ $url($k ?: null) }}" data-k="{{ $key }}" class="wl-qi {{ ($health ?: '') === $k ? '--on' : '' }} {{ $n ? '' : '--zero' }}" @if(($health ?: '') === $k) aria-current="page" @endif>
                <small><i class="bi {{ $icon }}"></i>{{ $label }}</small><strong>{{ $n }}</strong>
            </a>
        @endforeach
    </nav>

    <div class="wl-body">
        <section class="wl-panel" aria-label="Wallets">
            <form method="GET" action="{{ route('admin.wallets.index') }}" class="wl-bar" role="search" id="wltFilterForm">
                @if($health)<input type="hidden" name="health" value="{{ $health }}">@endif
                <label class="wl-search"><i class="bi bi-search"></i>
                    <input type="search" name="search" value="{{ $search }}" placeholder="Search name or email" aria-label="Search wallets">
                </label>
                <button class="wl-btn" type="submit">Search</button>
                @if($search)<a class="wl-btn" href="{{ $url($health ?: null) }}">Clear</a>@endif
                <span class="wl-count"><b>{{ $wallets->total() }}</b> shown</span>
            </form>

            @if($wallets->count())
                <div class="wl-th" aria-hidden="true"><span>Employee</span><span class="--r">Balance</span><span>Status</span><span>Last 30 days</span><span>Last activity</span><span></span></div>
            @endif
            @forelse($wallets as $wallet)
                @php
                    $user = $wallet->user;
                    $state = $healthState($wallet);
                    $parts = preg_split('/\s+/', trim($user->name));
                    $ini = mb_strtoupper(mb_substr($parts[0] ?? '', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
                    $pct = $wallet->isNegative() ? 0 : min(100, round(max(0, (float) $wallet->balance) / $maxBar * 100));
                    $last = $wallet->last_txn_at ? \Illuminate\Support\Carbon::parse($wallet->last_txn_at) : null;
                @endphp
                <article class="wl-row" data-state="{{ $state }}" data-wallet="{{ $wallet->id }}">
                    <a class="wl-who" href="{{ route('admin.wallets.show', $user) }}">
                        <span class="wl-av" aria-hidden="true">{{ $ini }}</span>
                        <span style="min-width:0"><span class="wl-t">{{ $user->name }}</span><span class="wl-s"><span class="wl-role --{{ $user->role }}">{{ ucfirst($user->role) }}</span>{{ $user->email }}</span></span>
                    </a>
                    <span class="wl-bal"><b>{{ $rupee($wallet->balance) }}</b><span class="wl-meter" title="₹5,000 fills the bar"><i style="width:{{ $pct }}%"></i></span></span>
                    <span class="wl-c-st"><span class="wl-chip --{{ $state }}">{{ $healthLabel[$state] }}</span></span>
                    <span class="wl-c wl-c-30">@if((float) $wallet->spent_30 || (float) $wallet->added_30)−{{ $rupee($wallet->spent_30) }} spent<small>+{{ $rupee($wallet->added_30) }} added</small>@else<span style="color:var(--ef-faint)">No movement</span>@endif</span>
                    <span class="wl-c wl-c-last">{{ $last ? $last->diffForHumans() : 'No activity' }}<small>{{ $last?->format('d M Y') }}</small></span>
                    <div class="wl-m">
                        <span class="wl-chip --{{ $state }}">{{ $healthLabel[$state] }}</span>
                        @if((float) $wallet->spent_30 || (float) $wallet->added_30)<span>30 days: −{{ $rupee($wallet->spent_30) }} / +{{ $rupee($wallet->added_30) }}</span>@endif
                        <span>{{ $last ? $last->diffForHumans() : 'No activity' }}</span>
                    </div>
                    <div class="wl-do">
                        <a href="{{ route('admin.wallets.show', $user) }}" class="wl-btn --sm"><i class="bi bi-clock-history"></i> History</a>
                        <button type="button" class="wl-btn --sm --main" data-bs-toggle="modal" data-bs-target="#fundModal"
                                data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-balance="{{ number_format((float) $wallet->balance, 2) }}"
                                onclick="wltSetModal(this)"><i class="bi bi-plus-lg"></i> Add funds</button>
                    </div>
                </article>
            @empty
                <div class="wl-empty"><i class="bi bi-wallet2"></i>
                    @if($search || $health) No wallets match. <a href="{{ route('admin.wallets.index') }}">Show all</a>
                    @else Employee advance balances appear here once employees exist. @endif
                </div>
            @endforelse
            @if($wallets->hasPages())<div class="wl-foot">{{ $wallets->links() }}</div>@endif
        </section>

        <aside class="wl-rail">
            <section class="wl-panel" style="--acc:#9a6413;--tint:rgba(200,144,42,.06)">
                <div class="wl-ph"><h2><i class="bi bi-currency-rupee"></i> Money</h2></div>
                <div class="wl-pb">
                    <div class="wl-kv">Total held<b>{{ $rupee($stats['total_balance']) }}</b></div>
                    <div class="wl-kv">Average per wallet<b>{{ $rupee($stats['avg_balance']) }}</b></div>
                    <div class="wl-kv">Spent · last 30 days<b>{{ $rupee($stats['spent_30']) }}</b></div>
                    <div class="wl-kv">Added · last 30 days<b>{{ $rupee($stats['added_30']) }}</b></div>
                    <a class="wl-kv" href="{{ route('admin.expense-requests.index', ['status' => 'reimbursement_pending']) }}">Reimbursement pending<b class="{{ $stats['pending_reimb_count'] ? '--warn' : '' }}">{{ $stats['pending_reimb_count'] }} · {{ $rupee($stats['pending_reimb_amount']) }}</b></a>
                </div>
            </section>
            <section class="wl-panel" style="--acc:#2c5784;--tint:rgba(59,110,165,.05)">
                <div class="wl-ph"><h2><i class="bi bi-info-circle"></i> Balance levels</h2></div>
                <div class="wl-pb">
                    <p class="wl-note"><span class="wl-chip --negative">Negative</span> below ₹0 · <span class="wl-chip --critical">Very low</span> under ₹200 · <span class="wl-chip --low">Low</span> under ₹500 · <span class="wl-chip --healthy">Healthy</span> ₹500+. The bar is full at ₹5,000.</p>
                </div>
            </section>
        </aside>
    </div>
</div>

{{-- ── Add Funds modal ────────────────────────────────────────── --}}
<div class="modal fade" id="fundModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content" style="border:1px solid var(--ef-border);border-radius:var(--ef-radius);box-shadow:0 20px 60px rgba(0,0,0,.18)">
            <div class="modal-header" style="border-bottom:1px solid var(--ef-border);padding:1.2rem 1.5rem .9rem">
                <div>
                    <h5 class="modal-title mb-0" style="font-weight:700;font-size:.95rem;color:var(--ef-ink)">
                        Wallet Transaction
                    </h5>
                    <div class="modal-subtitle" id="fundModalSubtitle"
                         style="font-size:.78rem;color:var(--ef-muted);margin-top:.15rem"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="opacity:.5"></button>
            </div>
            <form method="POST" id="fundModalForm">
                @csrf
                <div class="modal-body" style="padding:1.4rem 1.5rem">
                    @if ($errors->any())
                    <div style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;padding:10px 14px;margin-bottom:14px;color:var(--ef-danger);font-size:.82rem">
                        <strong>Please fix:</strong>
                        <ul style="margin:4px 0 0 16px;padding:0">
                            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                    @endif
                    <div style="display:flex;flex-direction:column;gap:1rem">
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--ef-ink);display:block;margin-bottom:.4rem">
                                Transaction Type
                            </label>
                            <select name="type" class="ef-select" style="border:1px solid var(--ef-border-strong);border-radius:9px;font-size:.875rem;background:var(--ef-faint);padding:.55rem .85rem;outline:none">
                                <option value="credit">Credit — Add Funds</option>
                                <option value="debit">Debit — Deduct Funds</option>
                                <option value="adjustment">Adjustment — Set Balance</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--ef-ink);display:block;margin-bottom:.4rem">
                                Amount (₹) <span style="color:var(--ef-danger)">*</span>
                            </label>
                            <input type="number" name="amount" step="0.01" min="0.01"
                                   class="ef-input"
                                   style="border:1px solid var(--ef-border-strong);border-radius:9px;font-size:.9rem;font-weight:700;background:var(--ef-faint);padding:.6rem .85rem;outline:none"
                                   placeholder="0.00" required>
                        </div>
                        <div>
                            <label style="font-size:.78rem;font-weight:600;color:var(--ef-ink);display:block;margin-bottom:.4rem">
                                Notes
                            </label>
                            <textarea name="notes" rows="2"
                                      class="ef-textarea"
                                      style="border:1px solid var(--ef-border-strong);border-radius:9px;font-size:.85rem;background:var(--ef-faint);padding:.55rem .85rem;outline:none;resize:none"
                                      placeholder="Optional — reason for transaction"></textarea>
                        </div>
                        <div style="background:var(--ef-faint);border:1px solid var(--ef-border);border-radius:9px;padding:.65rem .9rem;font-size:.78rem;color:var(--ef-muted)">
                            <i class="bi bi-wallet2 me-1"></i> Current balance:
                            <strong id="fundModalBalance" style="color:var(--ef-ink)">—</strong>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border);padding:.9rem 1.5rem;gap:.5rem">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="ef-btn ef-btn-dark wl-btn --main" style="min-height:42px">
                        Record Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function wltSetModal(btn) {
    const userId  = btn.dataset.userId;
    const name    = btn.dataset.userName;
    const balance = btn.dataset.balance;
    document.getElementById('fundModalSubtitle').textContent = name;
    document.getElementById('fundModalBalance').textContent  = '₹' + balance;
    document.getElementById('fundModalForm').action =
        '{{ url('admin/wallets') }}/' + userId + '/transact';
}
@if ($errors->any())
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('fundModal')).show();
});
@endif
</script>
@endpush
</x-admin-layout>
