<x-admin-layout title="Wallet — {{ $wallet->user->name }}">

@push('styles')
<style>
/* ── Wallet Show — ef-wsh namespace ─────────────────────────────── */

.ef-wsh-bal-val {
    font-size: 2rem;
    font-variant-numeric: tabular-nums;
    font-weight: 800;
    letter-spacing: -.03em;
    line-height: 1;
    margin: 8px 0;
}
.ef-wsh-bal-val.--ok  { color: var(--ef-on-dark-gold); }
.ef-wsh-bal-val.--low { color: #f6c86b; }
.ef-wsh-bal-val.--neg { color: #f87171; }

.ef-wsh-main-grid {
    align-items: start;
    display: grid;
    gap: 16px;
    grid-template-columns: 264px 1fr;
    margin-bottom: 16px;
}
.ef-wsh-profile {
    background: var(--ef-surface);
    border: 1px solid var(--ef-border);
    border-radius: var(--ef-radius);
    box-shadow: var(--ef-shadow);
    padding: 20px;
}
.ef-wsh-avatar {
    align-items: center;
    background: var(--ef-hero-grad);
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(4,27,20,.18);
    color: var(--ef-on-dark-gold);
    display: flex;
    font-size: 1.35rem;
    font-weight: 800;
    height: 58px;
    justify-content: center;
    letter-spacing: -.01em;
    margin-bottom: 14px;
    width: 58px;
}
.ef-wsh-profile-name  { color: var(--ef-ink); font-size: 1rem; font-weight: 800; margin: 0 0 2px; }
.ef-wsh-profile-email { color: var(--ef-muted); font-size: .79rem; margin: 0 0 10px; word-break: break-all; }
.ef-wsh-profile-divider { border: none; border-top: 1px solid var(--ef-border); margin: 14px 0; }
.ef-wsh-detail-row { align-items: baseline; display: flex; gap: 8px; margin-bottom: 8px; }
.ef-wsh-detail-row:last-child { margin-bottom: 0; }
.ef-wsh-detail-lbl { color: var(--ef-faint); font-size: .68rem; font-weight: 700; letter-spacing: .06em; min-width: 56px; text-transform: uppercase; }
.ef-wsh-detail-val { color: var(--ef-ink-2); font-size: .84rem; font-weight: 600; }

.ef-wsh-txn-table { border-collapse: collapse; width: 100%; }
.ef-wsh-txn-table th {
    background: var(--ef-bg-subtle);
    border-bottom: 1px solid var(--ef-border);
    color: var(--ef-faint);
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .08em;
    padding: 10px 16px;
    text-align: left;
    text-transform: uppercase;
    white-space: nowrap;
}
.ef-wsh-txn-table th.r { text-align: right; }
.ef-wsh-txn-table td {
    border-bottom: 1px solid var(--ef-border);
    font-size: .86rem;
    padding: 13px 16px;
    vertical-align: middle;
}
.ef-wsh-txn-table td.r { text-align: right; }
.ef-wsh-txn-table tbody tr:last-child td { border-bottom: none; }
.ef-wsh-txn-table tbody tr:hover td { background: var(--ef-bg-subtle); transition: background .12s; }

.ef-wsh-type-chip {
    align-items: center;
    border-radius: 8px;
    display: inline-flex;
    font-size: .72rem;
    font-weight: 700;
    gap: 5px;
    letter-spacing: .03em;
    padding: 4px 9px;
    text-transform: capitalize;
    white-space: nowrap;
}
.ef-wsh-type-chip.--credit        { background: rgba(15,123,95,.1);  color: var(--ef-emerald); }
.ef-wsh-type-chip.--debit         { background: rgba(200,75,68,.08); color: var(--ef-danger); }
.ef-wsh-type-chip.--adjustment    { background: rgba(96,112,128,.1); color: var(--ef-bluegray); }
.ef-wsh-type-chip.--reimbursement { background: rgba(184,137,62,.1); color: var(--ef-gold); }

.ef-wsh-amt { font-size: .9rem; font-variant-numeric: tabular-nums; font-weight: 760; }
.ef-wsh-amt.--in  { color: var(--ef-emerald); }
.ef-wsh-amt.--out { color: var(--ef-danger); }

@media (max-width: 767.98px) {
    .ef-wsh-main-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@php
    $nameParts = explode(' ', trim($wallet->user->name));
    $initials  = strtoupper(substr($nameParts[0], 0, 1));
    if (count($nameParts) > 1) $initials .= strtoupper(substr(end($nameParts), 0, 1));
@endphp

<x-ds.hero
    eyebrow="WALLET OPERATIONS"
    :title="$wallet->user->name . chr(39) . 's Wallet'"
    :meta="[
        ['icon' => 'bi-person-badge',  'text' => ucfirst($wallet->user->role)],
        ['icon' => 'bi-clock-history', 'text' => $stats['last_txn_at'] ? \Carbon\Carbon::parse($stats['last_txn_at'])->diffForHumans() : 'No transactions'],
        ['icon' => 'bi-receipt',       'text' => $stats['txn_count'] . ' transactions total'],
    ]"
>
    <x-slot:actions>
        <button class="ef-ds-btn --primary" data-bs-toggle="modal" data-bs-target="#creditModal">
            <i class="bi bi-plus-circle"></i> <span>Credit</span>
        </button>
        <button class="ef-ds-btn" style="background:rgba(200,75,68,.12);border-color:rgba(200,75,68,.28);color:#f87171" data-bs-toggle="modal" data-bs-target="#debitModal">
            <i class="bi bi-dash-circle"></i> <span>Debit</span>
        </button>
        <button class="ef-ds-btn" data-bs-toggle="modal" data-bs-target="#adjustModal">
            <i class="bi bi-sliders"></i> <span>Adjust</span>
        </button>
        <a href="{{ route('admin.wallets.index') }}" class="ef-ds-btn">
            <i class="bi bi-arrow-left"></i> <span>All Wallets</span>
        </a>
    </x-slot:actions>

    <x-slot:side>
        <div>
            <div class="ef-ds-side-label">Current Balance</div>
            <div id="wallet-balance-display"
                 class="ef-wsh-bal-val {{ $wallet->isNegative() ? '--neg' : ($wallet->isLow() ? '--low' : '--ok') }}"
                 data-raw="{{ $wallet->balance }}">
                ₹{{ number_format($wallet->balance, 2) }}
            </div>
            <div id="wallet-balance-badge" style="margin-top:8px">
                @if($wallet->isNegative())
                    <span style="background:rgba(248,113,113,.18);border-radius:20px;color:#f87171;font-size:.67rem;font-weight:700;letter-spacing:.06em;padding:3px 10px;text-transform:uppercase">Negative</span>
                @elseif($wallet->isLow())
                    <span style="background:rgba(246,200,107,.15);border-radius:20px;color:#f6c86b;font-size:.67rem;font-weight:700;letter-spacing:.06em;padding:3px 10px;text-transform:uppercase">Low Balance</span>
                @else
                    <span style="background:rgba(15,123,95,.2);border-radius:20px;color:#4ade80;font-size:.67rem;font-weight:700;letter-spacing:.06em;padding:3px 10px;text-transform:uppercase">Healthy</span>
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('admin.employees.show', $wallet->user) }}"
               class="ef-ds-btn" style="justify-content:center;margin-top:12px;width:100%">
                <i class="bi bi-person-circle"></i> <span>View Employee</span>
            </a>
        </div>
    </x-slot:side>

    <x-slot:mobile_stat>
        <span class="ef-ds-hero-mstat-val">₹{{ number_format($wallet->balance, 2) }}</span>
        <span class="ef-ds-hero-mstat-note">{{ $wallet->isNegative() ? 'Negative balance' : ($wallet->isLow() ? 'Low balance' : 'Healthy') }}</span>
    </x-slot:mobile_stat>
</x-ds.hero>

{{-- KPI Strip --}}
<div class="ef-ds-kpi-wrap" style="margin-bottom:20px">
    <div class="ef-ds-kpi-grid" style="--kpi-cols:4">
        <x-ds.kpi-card
            icon="bi-arrow-down-circle-fill"
            label="Total Credited"
            :value="'₹' . number_format($stats['total_credited'], 0)"
            note="Credits &amp; reimbursements"
            accent="emerald"
            value-color="c-emerald"
        />
        <x-ds.kpi-card
            icon="bi-arrow-up-circle-fill"
            label="Total Debited"
            :value="'₹' . number_format($stats['total_debited'], 0)"
            note="All debit transactions"
            accent="danger"
            value-color="c-danger"
        />
        <x-ds.kpi-card
            icon="bi-list-ul"
            label="Transactions"
            :value="number_format($stats['txn_count'])"
            note="All time activity"
            accent="bluegray"
        />
        <x-ds.kpi-card
            icon="bi-clock-history"
            label="Last Activity"
            :value="$stats['last_txn_at'] ? \Carbon\Carbon::parse($stats['last_txn_at'])->format('d M Y') : '—'"
            :note="$stats['last_txn_at'] ? \Carbon\Carbon::parse($stats['last_txn_at'])->diffForHumans() : 'No activity yet'"
            accent="gold"
            value-color="c-gold"
        />
    </div>
</div>

{{-- Employee profile + Filter --}}
<div class="ef-wsh-main-grid">

    {{-- Employee profile card --}}
    <div class="ef-wsh-profile">
        <div class="ef-wsh-avatar">{{ $initials }}</div>
        <div class="ef-wsh-profile-name">{{ $wallet->user->name }}</div>
        <div class="ef-wsh-profile-email">{{ $wallet->user->email }}</div>
        <div>
            <span class="ef-chip" data-tone="emerald">{{ ucfirst($wallet->user->role) }}</span>
        </div>
        <hr class="ef-wsh-profile-divider">
        <div class="ef-wsh-detail-row">
            <span class="ef-wsh-detail-lbl">Phone</span>
            <span class="ef-wsh-detail-val">{{ $wallet->user->phone ?? '—' }}</span>
        </div>
        <div class="ef-wsh-detail-row">
            <span class="ef-wsh-detail-lbl">Joined</span>
            <span class="ef-wsh-detail-val">{{ $wallet->user->created_at->format('M Y') }}</span>
        </div>
        <div class="ef-wsh-detail-row">
            <span class="ef-wsh-detail-lbl">Wallet</span>
            <span class="ef-wsh-detail-val">Since {{ $wallet->created_at->format('M Y') }}</span>
        </div>
    </div>

    {{-- Filter card --}}
    <x-ds.card title="Filter Transactions">
        <form method="GET" style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:12px;align-items:end" data-no-ajax>
            <div>
                <label class="ef-label">Type</label>
                <select name="type" class="ef-select">
                    <option value="">All Types</option>
                    <option value="credit"        {{ request('type') === 'credit'        ? 'selected' : '' }}>Credit</option>
                    <option value="debit"         {{ request('type') === 'debit'         ? 'selected' : '' }}>Debit</option>
                    <option value="adjustment"    {{ request('type') === 'adjustment'    ? 'selected' : '' }}>Adjustment</option>
                    <option value="reimbursement" {{ request('type') === 'reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                </select>
            </div>
            <div>
                <label class="ef-label">From</label>
                <input type="date" name="from" class="ef-input" value="{{ request('from') }}">
            </div>
            <div>
                <label class="ef-label">To</label>
                <input type="date" name="to" class="ef-input" value="{{ request('to') }}">
            </div>
            <div style="display:flex;gap:8px">
                <button class="ef-btn ef-btn-dark" type="submit">Filter</button>
                <a href="{{ route('admin.wallets.show', $wallet->user) }}" class="ef-btn">Reset</a>
            </div>
        </form>
        @if(request('type') || request('from') || request('to'))
        <div style="align-items:center;border-top:1px solid var(--ef-border);display:flex;flex-wrap:wrap;gap:6px;margin-top:14px;padding-top:12px">
            <span style="color:var(--ef-faint);font-size:.7rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase">Active:</span>
            @if(request('type'))
                <span style="background:rgba(184,137,62,.1);border-radius:20px;color:var(--ef-gold);font-size:.72rem;font-weight:700;padding:2px 10px">{{ ucfirst(request('type')) }}</span>
            @endif
            @if(request('from'))
                <span style="background:rgba(96,112,128,.08);border-radius:20px;color:var(--ef-bluegray);font-size:.72rem;font-weight:700;padding:2px 10px">From: {{ request('from') }}</span>
            @endif
            @if(request('to'))
                <span style="background:rgba(96,112,128,.08);border-radius:20px;color:var(--ef-bluegray);font-size:.72rem;font-weight:700;padding:2px 10px">To: {{ request('to') }}</span>
            @endif
        </div>
        @endif
    </x-ds.card>

</div>

{{-- Transaction ledger --}}
<x-ds.card :no-pad="true">
    <x-slot:head_right>
        <span style="color:var(--ef-faint);font-size:.78rem">{{ $transactions->total() }} records</span>
    </x-slot:head_right>
    {{-- manual header since no-pad needs a title row --}}
    <div style="align-items:center;border-bottom:1px solid var(--ef-border);display:flex;justify-content:space-between;padding:14px 18px">
        <div style="color:var(--ef-ink);font-size:.9rem;font-weight:800">Transaction Ledger</div>
        <div style="color:var(--ef-faint);font-size:.78rem">{{ $transactions->total() }} records</div>
    </div>
    <div style="overflow-x:auto">
        <table class="ef-wsh-txn-table">
            <thead>
                <tr>
                    <th>Date &amp; Time</th>
                    <th>Type</th>
                    <th>Notes</th>
                    <th>Linked Request</th>
                    <th class="r">Before</th>
                    <th class="r">Amount</th>
                    <th class="r">After</th>
                    <th>Actioned By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $txn)
                @php
                    $typeIcon = match($txn->type) {
                        'credit'        => 'bi-plus-circle',
                        'debit'         => 'bi-dash-circle',
                        'adjustment'    => 'bi-sliders',
                        'reimbursement' => 'bi-arrow-counterclockwise',
                        default         => 'bi-circle',
                    };
                @endphp
                <tr>
                    <td style="white-space:nowrap">
                        <div style="color:var(--ef-ink-2);font-size:.86rem;font-weight:600">{{ $txn->created_at->format('d M Y') }}</div>
                        <div style="color:var(--ef-faint);font-size:.75rem">{{ $txn->created_at->format('h:i A') }}</div>
                    </td>
                    <td>
                        <span class="ef-wsh-type-chip --{{ $txn->type }}">
                            <i class="bi {{ $typeIcon }}"></i>{{ $txn->type }}
                        </span>
                    </td>
                    <td style="color:var(--ef-muted);font-size:.84rem;max-width:180px">{{ $txn->notes ?? '—' }}</td>
                    <td>
                        @if($txn->expenseRequest)
                            <a href="{{ route('admin.expense-requests.show', $txn->expenseRequest) }}"
                               style="color:var(--ef-emerald);font-size:.83rem;font-weight:600;text-decoration:none">
                                <i class="bi bi-link-45deg"></i> {{ Str::limit($txn->expenseRequest->title, 26) }}
                            </a>
                        @else
                            <span style="color:var(--ef-faint)">—</span>
                        @endif
                    </td>
                    <td class="r" style="color:var(--ef-faint);font-size:.84rem;font-variant-numeric:tabular-nums">₹{{ number_format($txn->balance_before, 2) }}</td>
                    <td class="r">
                        <span class="ef-wsh-amt {{ $txn->isCredit() ? '--in' : '--out' }}">
                            {{ $txn->isCredit() ? '+' : '−' }}₹{{ number_format($txn->amount, 2) }}
                        </span>
                    </td>
                    <td class="r" style="color:var(--ef-ink-2);font-variant-numeric:tabular-nums;font-weight:700">₹{{ number_format($txn->balance_after, 2) }}</td>
                    <td style="color:var(--ef-muted);font-size:.83rem;white-space:nowrap">{{ $txn->creator->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="padding:52px 20px;text-align:center">
                        <i class="bi bi-clock-history" style="color:var(--ef-faint);display:block;font-size:2rem;margin-bottom:10px"></i>
                        <div style="color:var(--ef-faint);font-size:.86rem;font-weight:600">No transactions found</div>
                        @if(request('type') || request('from') || request('to'))
                            <div style="color:var(--ef-faint);font-size:.78rem;margin-top:4px">Try adjusting your filters</div>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div style="border-top:1px solid var(--ef-border);padding:14px 18px">
        {{ $transactions->links() }}
    </div>
    @endif
</x-ds.card>

{{-- Credit Modal --}}
<div class="modal fade" id="creditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:16px">
            <form id="creditForm" method="POST" action="{{ route('admin.wallets.transact', $wallet->user) }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="credit">
                <div class="modal-header" style="border-bottom:1px solid var(--ef-border)">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700">
                        <i class="bi bi-plus-circle" style="color:var(--ef-emerald);margin-right:8px"></i>Credit Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-none wallet-error" style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;color:var(--ef-danger);font-size:.82rem;margin-bottom:12px;padding:8px 14px" role="alert"></div>
                    <div style="margin-bottom:16px">
                        <label class="ef-label">Amount (₹) <span style="color:var(--ef-danger)">*</span></label>
                        <input type="number" name="amount" class="ef-input" min="0.01" step="0.01" required placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label class="ef-label">Notes <span style="color:var(--ef-faint);font-weight:400">(optional)</span></label>
                        <textarea name="notes" class="ef-textarea" rows="2" placeholder="Reason for credit…" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border)">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark">
                        <i class="bi bi-plus-circle"></i> Credit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Debit Modal --}}
<div class="modal fade" id="debitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:16px">
            <form id="debitForm" method="POST" action="{{ route('admin.wallets.transact', $wallet->user) }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="debit">
                <div class="modal-header" style="border-bottom:1px solid var(--ef-border)">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700">
                        <i class="bi bi-dash-circle" style="color:var(--ef-danger);margin-right:8px"></i>Debit Wallet
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-none wallet-error" style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;color:var(--ef-danger);font-size:.82rem;margin-bottom:12px;padding:8px 14px" role="alert"></div>
                    <div style="background:rgba(246,200,107,.06);border:1px solid rgba(246,200,107,.18);border-radius:10px;font-size:.82rem;margin-bottom:14px;padding:9px 13px">
                        <i class="bi bi-exclamation-triangle" style="color:#c8900a;margin-right:6px"></i>
                        <span style="color:#7a5c00">Current balance: <strong id="debit-balance-hint">₹{{ number_format($wallet->balance, 2) }}</strong></span>
                    </div>
                    <div style="margin-bottom:16px">
                        <label class="ef-label">Amount (₹) <span style="color:var(--ef-danger)">*</span></label>
                        <input type="number" name="amount" class="ef-input" id="debitAmount"
                               min="0.01" step="0.01" max="{{ $wallet->balance }}" required placeholder="0.00">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label class="ef-label">Notes <span style="color:var(--ef-faint);font-weight:400">(optional)</span></label>
                        <textarea name="notes" class="ef-textarea" rows="2" placeholder="Reason for debit…" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border)">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark" style="background:var(--ef-danger);border-color:var(--ef-danger)">
                        <i class="bi bi-dash-circle"></i> Debit Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Adjust Modal --}}
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border:none;border-radius:16px">
            <form id="adjustForm" method="POST" action="{{ route('admin.wallets.transact', $wallet->user) }}" novalidate>
                @csrf
                <input type="hidden" name="type" value="adjustment">
                <div class="modal-header" style="border-bottom:1px solid var(--ef-border)">
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700">
                        <i class="bi bi-sliders" style="margin-right:8px"></i>Balance Adjustment
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-none wallet-error" style="background:rgba(200,75,68,.08);border:1px solid rgba(200,75,68,.2);border-radius:8px;color:var(--ef-danger);font-size:.82rem;margin-bottom:12px;padding:8px 14px" role="alert"></div>
                    <div style="margin-bottom:16px">
                        <label class="ef-label">New Balance (₹) <span style="color:var(--ef-danger)">*</span></label>
                        <input type="number" name="amount" class="ef-input" min="0" step="0.01"
                               value="{{ $wallet->balance }}" required>
                        <div style="color:var(--ef-faint);font-size:.75rem;margin-top:5px">Set exact balance. Difference will be recorded.</div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div>
                        <label class="ef-label">Notes <span style="color:var(--ef-faint);font-weight:400">(optional)</span></label>
                        <textarea name="notes" class="ef-textarea" rows="2" placeholder="Reason for adjustment…" maxlength="500"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--ef-border)">
                    <button type="button" class="ef-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="ef-btn ef-btn-dark">
                        <i class="bi bi-sliders"></i> Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:1100">
    <div id="walletToast" role="alert" aria-live="assertive" aria-atomic="true"
         style="background:var(--ef-hero-grad);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#fffdfa;min-width:240px">
        <div style="align-items:center;display:flex;gap:10px;padding:12px 14px">
            <div class="toast-body" style="flex:1;font-size:.875rem;font-weight:600"></div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const CSRF       = document.querySelector('meta[name="csrf-token"]').content;
    const LOW_TH     = 500;

    const balanceEl     = document.getElementById('wallet-balance-display');
    const badgeEl       = document.getElementById('wallet-balance-badge');
    const debitHint     = document.getElementById('debit-balance-hint');
    const debitAmountEl = document.getElementById('debitAmount');
    const toastEl       = document.getElementById('walletToast');
    const bsToast       = new bootstrap.Toast(toastEl, { delay: 4000 });

    function fmtBalance(n) {
        return '₹' + parseFloat(n).toLocaleString('en-IN', {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
    }

    function updateBalanceDisplay(newBalance) {
        balanceEl.textContent = fmtBalance(newBalance);
        balanceEl.className   = 'ef-wsh-bal-val';
        if      (newBalance < 0)      balanceEl.classList.add('--neg');
        else if (newBalance < LOW_TH) balanceEl.classList.add('--low');
        else                          balanceEl.classList.add('--ok');

        if (debitHint)     debitHint.textContent = fmtBalance(newBalance);
        if (debitAmountEl) debitAmountEl.max = newBalance > 0 ? newBalance : 0;

        if (badgeEl) {
            const chip = (bg, clr, lbl) =>
                `<span style="background:${bg};border-radius:20px;color:${clr};font-size:.67rem;font-weight:700;letter-spacing:.06em;padding:3px 10px;text-transform:uppercase">${lbl}</span>`;

            if      (newBalance < 0)      badgeEl.innerHTML = chip('rgba(248,113,113,.18)', '#f87171', 'Negative');
            else if (newBalance < LOW_TH) badgeEl.innerHTML = chip('rgba(246,200,107,.15)', '#f6c86b', 'Low Balance');
            else                          badgeEl.innerHTML = chip('rgba(15,123,95,.2)',    '#4ade80', 'Healthy');
        }
    }

    function showToast(message, type) {
        if (type === 'success') {
            toastEl.style.background  = 'var(--ef-hero-grad)';
            toastEl.style.borderColor = 'rgba(255,255,255,.1)';
        } else {
            toastEl.style.background  = 'rgba(200,75,68,.92)';
            toastEl.style.borderColor = 'rgba(200,75,68,.3)';
        }
        toastEl.querySelector('.toast-body').textContent = message;
        bsToast.show();
    }

    function clearModalErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        const err = form.querySelector('.wallet-error');
        if (err) { err.classList.add('d-none'); err.textContent = ''; }
        form.classList.remove('was-validated');
    }

    function showModalErrors(form, errors) {
        const errBox = form.querySelector('.wallet-error');
        if (errors && typeof errors === 'object') {
            Object.entries(errors).forEach(([field, messages]) => {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');
                    const fb = input.parentNode.querySelector('.invalid-feedback');
                    if (fb) fb.textContent = Array.isArray(messages) ? messages[0] : messages;
                }
            });
        }
        if (errBox) {
            errBox.textContent = 'Please fix the errors above.';
            errBox.classList.remove('d-none');
        }
    }

    function attachWalletForm(formEl, modalId) {
        const modalEl = document.getElementById(modalId);
        if (!formEl || !modalEl) return;

        const submitBtn = formEl.querySelector('[type="submit"]');

        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            clearModalErrors(formEl);

            if (!formEl.checkValidity()) {
                formEl.classList.add('was-validated');
                return;
            }

            if (submitBtn.disabled) return;

            const originalHtml  = submitBtn.innerHTML;
            submitBtn.disabled  = true;
            submitBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Processing…';

            fetch(formEl.action, {
                method : 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept'          : 'application/json',
                    'X-CSRF-TOKEN'    : CSRF,
                },
                body: new FormData(formEl),
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw data;
                return data;
            })
            .then(data => {
                bootstrap.Modal.getInstance(modalEl).hide();
                updateBalanceDisplay(data.balance);
                showToast(data.message, 'success');
                setTimeout(() => window.location.reload(), 1600);
            })
            .catch(err => {
                submitBtn.disabled  = false;
                submitBtn.innerHTML = originalHtml;

                if (err && err.errors) {
                    showModalErrors(formEl, err.errors);
                } else {
                    const errBox = formEl.querySelector('.wallet-error');
                    if (errBox) {
                        errBox.textContent = (err && err.message) || 'Transaction failed. Please try again.';
                        errBox.classList.remove('d-none');
                    }
                    showToast((err && err.message) || 'Transaction failed.', 'danger');
                }
            });
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            formEl.reset();
            clearModalErrors(formEl);
            submitBtn.disabled  = false;
            submitBtn.innerHTML = submitBtn.dataset.originalHtml || submitBtn.innerHTML;
        });

        submitBtn.dataset.originalHtml = submitBtn.innerHTML;
    }

    attachWalletForm(document.getElementById('creditForm'),  'creditModal');
    attachWalletForm(document.getElementById('debitForm'),   'debitModal');
    attachWalletForm(document.getElementById('adjustForm'),  'adjustModal');
})();
</script>
@endpush

</x-admin-layout>
