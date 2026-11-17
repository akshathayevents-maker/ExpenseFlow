<x-admin-layout :title="$mr->reference()">
@include('inventory._styles')
@php
    $u = auth()->user();
    $q = fn ($n) => rtrim(rtrim(number_format((float) $n, 3, '.', ''), '0'), '.');
@endphp
<div class="ic-wrap" style="max-width:980px">
    <div class="ic-head">
        <a class="ic-back" href="{{ route('inventory.material-requests.index') }}"><i class="bi bi-arrow-left"></i> Requests</a>
        <h1>{{ $mr->reference() }} · {{ $mr->preparation_name ?: $mr->forLabel() }}</h1>
        @include('inventory._status', ['label' => $mr->statusLabel(), 'tone' => \App\Models\MaterialRequest::statusTone($mr->status)])
        @if($mr->reconciliation_status === 'pending_review')
            <span class="ic-chip ic-chip--warn"><i class="bi bi-eye"></i> Usage needs review</span>
        @endif
    </div>

    @if($errors->any())
        <div class="ic-err"><ul>@foreach(collect($errors->all())->unique() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="ic-card">
        <div class="ic-kv">
            <div><small>For</small><strong>
                @if($mr->source === 'meal' && $mr->mealEntry)
                    <a href="{{ route('meal-register.entries.show', $mr->mealEntry) }}">{{ $mr->forLabel() }}</a>
                @elseif($mr->source === 'hall' && $mr->hallBooking && ($u->isAdmin() || $u->isManager()))
                    <a href="{{ route('hall.bookings.show', $mr->hallBooking) }}">{{ $mr->forLabel() }}</a>
                @else {{ $mr->forLabel() }} @endif
            </strong></div>
            <div><small>Needed</small><strong>{{ $mr->required_date->format('D d M Y') }}{{ $mr->required_time ? ', ' . \Carbon\Carbon::parse($mr->required_time)->format('h:i A') : '' }}</strong></div>
            <div><small>People</small><strong>{{ $mr->guest_count ?: '—' }}</strong></div>
            <div><small>Requested by</small><strong>{{ $mr->requester?->name }}</strong><span class="ic-note"> {{ $mr->submitted_at?->format('d M, h:i A') }}</span></div>
            <div><small>Approved by</small><strong>{{ $mr->approver?->name ?? '—' }}</strong><span class="ic-note"> {{ $mr->approved_at?->format('d M, h:i A') }}</span></div>
            <div><small>Handed over</small><strong>{{ $mr->handedOverBy?->name ?? '—' }}</strong>
                @if($mr->handed_over_at)<span class="ic-note"> → {{ $mr->collector?->name ?? $mr->collected_by_name }} · {{ $mr->handed_over_at->format('d M, h:i A') }}</span>@endif</div>
        </div>
        @if($mr->approval_remarks)<p class="ic-note" style="margin:10px 0 0"><strong>Approver:</strong> {{ $mr->approval_remarks }}</p>@endif
        @if($mr->cancel_reason)<div class="ic-err" style="margin:10px 0 0">Cancelled by {{ $mr->canceller?->name }}: {{ $mr->cancel_reason }}</div>@endif
        @if($mr->notes)<p class="ic-note" style="margin:10px 0 0;white-space:pre-line">{{ $mr->notes }}</p>@endif
        @if($mr->revisionOf)<p class="ic-note" style="margin:10px 0 0">Revision of <a href="{{ route('inventory.material-requests.show', $mr->revisionOf) }}">{{ $mr->revisionOf->reference() }}</a></p>@endif
        @if($mr->revisions->isNotEmpty())
            <p class="ic-note" style="margin:10px 0 0">Revisions: @foreach($mr->revisions as $rv)<a href="{{ route('inventory.material-requests.show', $rv) }}">{{ $rv->reference() }}</a> ({{ $rv->statusLabel() }}){{ $loop->last ? '' : ', ' }}@endforeach</p>
        @endif
        @if($mr->menu_snapshot && ! empty($mr->menu_snapshot['items']) || ! empty($mr->menu_snapshot['plans'] ?? []))
            <details style="margin-top:10px"><summary class="ic-note" style="cursor:pointer;font-weight:700">Final menu at time of request</summary>
                <div class="ic-note" style="margin-top:6px">
                    @foreach($mr->menu_snapshot['plans'] ?? [] as $p) Plan: <strong>{{ $p['plan'] }}</strong> ({{ $p['guests'] }})@if(! $loop->last), @endif @endforeach
                    <ul style="margin:6px 0 0;padding-left:18px">
                        @foreach($mr->menu_snapshot['items'] ?? [] as $mi)
                            <li style="{{ ! empty($mi['removed']) ? 'text-decoration:line-through' : '' }}">{{ $mi['name'] }} @if(($mi['change'] ?? '') && $mi['change'] !== 'selected' && $mi['change'] !== 'plan')<span class="ic-chip ic-chip--info">{{ $mi['change'] }}</span>@endif @if(! empty($mi['note']))<span>· {{ $mi['note'] }}</span>@endif</li>
                        @endforeach
                    </ul>
                </div>
            </details>
        @endif
    </div>

    {{-- ── Admin approval ───────────────────────────────────────── --}}
    @can('approve', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.approve', $mr) }}" class="ic-card">
            @csrf
            <h2>Approve materials</h2>
            <p class="ic-note">Change a quantity to approve partly. Enter 0 to reject a line. Approving deducts stock immediately.</p>
            <table class="ic-table">
                <thead><tr><th>Material</th><th class="r">Requested</th><th class="r">In store</th><th class="r">Already issued for this</th><th>Approve</th><th>Remark</th></tr></thead>
                <tbody>
                @foreach($mr->items as $i)
                    @php $short = $i->item->current_stock < $i->requested_base_quantity; @endphp
                    <tr>
                        <td data-l="Material"><strong>{{ $i->item->name }}</strong></td>
                        <td data-l="Requested" class="r">{{ $q($i->requested_quantity) }} {{ $i->requested_unit }}</td>
                        <td data-l="In store" class="r" style="{{ $short ? 'color:var(--ef-danger);font-weight:700' : '' }}">{{ $q($i->item->current_stock) }} {{ $i->item->unit }}</td>
                        <td data-l="Issued before" class="r">{{ isset($previous[$i->inventory_item_id]) ? $q($previous[$i->inventory_item_id]) . ' ' . $i->item->unit : '—' }}</td>
                        <td data-l="Approve"><div style="display:flex;gap:6px;align-items:center">
                            <input class="ic-input" style="max-width:120px" type="number" min="0" step="0.001" name="lines[{{ $i->id }}][quantity]" value="{{ old("lines.{$i->id}.quantity", $q($i->requested_quantity)) }}" inputmode="decimal">
                            <span class="ic-note">{{ $i->requested_unit }}</span></div></td>
                        <td data-l="Remark"><input class="ic-input" name="lines[{{ $i->id }}][remark]" maxlength="300" value="{{ old("lines.{$i->id}.remark") }}"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:12px">
                <input class="ic-input" name="remarks" placeholder="Remarks (optional)" maxlength="500" style="flex:1;min-width:220px">
                <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-check2-circle"></i> Approve</button>
            </div>
        </form>
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.reject', $mr) }}" class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap">
            @csrf
            <input class="ic-input" name="reason" placeholder="Reason for rejecting the whole request" required minlength="3" maxlength="500" style="flex:1;min-width:220px">
            <button class="ic-btn ic-btn--danger" type="submit">Reject request</button>
        </form>
    @else
        {{-- ── Lines (all other states) ───────────────────────────── --}}
        <div class="ic-card">
            <h2>Materials</h2>
            <table class="ic-table">
                <thead><tr><th>Material</th><th class="r">Requested</th><th class="r">Approved</th><th class="r">Handed over</th><th class="r">Returned</th><th class="r">Used</th><th class="r">Wasted</th><th class="r">Unexplained</th></tr></thead>
                <tbody>
                @foreach($mr->items as $i)
                    @php $unit = $i->item->unit; @endphp
                    <tr style="{{ $i->line_status === 'rejected' ? 'opacity:.55' : '' }}">
                        <td data-l="Material"><strong>{{ $i->item->name }}</strong>
                            @if($i->line_status === 'rejected')<span class="ic-chip ic-chip--bad">rejected</span>@elseif($i->line_status === 'reduced')<span class="ic-chip ic-chip--warn">reduced</span>@endif
                            @if($i->admin_remark)<div class="ic-note">{{ $i->admin_remark }}</div>@endif
                            @if($i->wastage_reason)<div class="ic-note">Waste: {{ $i->wastage_reason }}</div>@endif
                            @if($i->variance_reason)<div class="ic-note">Difference: {{ $i->variance_reason }}</div>@endif
                        </td>
                        <td data-l="Requested" class="r">{{ $q($i->requested_quantity) }} {{ $i->requested_unit }}</td>
                        <td data-l="Approved" class="r">{{ $i->approved_base_quantity !== null ? $q($i->approved_base_quantity) . ' ' . $unit : '—' }}</td>
                        <td data-l="Handed over" class="r">{{ $i->issued_base_quantity !== null ? $q($i->issued_base_quantity) . ' ' . $unit : '—' }}</td>
                        <td data-l="Returned" class="r">{{ $i->returned_base_quantity > 0 ? $q($i->returned_base_quantity) . ' ' . $unit : '—' }}</td>
                        <td data-l="Used" class="r">{{ $i->consumed_base_quantity !== null ? $q($i->consumed_base_quantity) : '—' }}</td>
                        <td data-l="Wasted" class="r" style="{{ $i->wasted_base_quantity > 0 ? 'color:#9B2C2C;font-weight:700' : '' }}">{{ $i->wasted_base_quantity !== null ? $q($i->wasted_base_quantity) : '—' }}</td>
                        <td data-l="Unexplained" class="r" style="{{ $i->unexplained_base_quantity > 0 ? 'color:#9B2C2C;font-weight:700' : '' }}">{{ $i->unexplained_base_quantity !== null ? $q($i->unexplained_base_quantity) : '—' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @if(in_array($mr->status, ['approved', 'issued', 'reconciled']) && ($u->isAdmin() || $u->isManager()))
                <p class="ic-note" style="margin:10px 0 0">Material cost (net of returns): <strong>₹{{ number_format($mr->issuedCost(), 2) }}</strong>
                    @if($mr->guest_count) · ₹{{ number_format($mr->issuedCost() / max(1, $mr->guest_count), 2) }} per person @endif</p>
            @endif
        </div>
    @endcan

    {{-- ── Storekeeper handover ──────────────────────────────────── --}}
    @can('handover', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.handover', $mr) }}" class="ic-card">
            @csrf
            <h2>Hand over to kitchen</h2>
            <p class="ic-note">Stock was already deducted at approval. If you give less than approved, change the number — the difference goes back into stock automatically.</p>
            @foreach($mr->items->where('line_status', '!=', 'rejected') as $i)
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:8px 0;border-bottom:1px solid var(--ef-border)">
                    <strong style="flex:1;min-width:140px">{{ $i->item->name }}</strong>
                    <input class="ic-input" style="max-width:130px" type="number" min="0" max="{{ $i->approved_base_quantity }}" step="0.001" name="issued[{{ $i->id }}]" value="{{ old("issued.{$i->id}", $q($i->approved_base_quantity)) }}" inputmode="decimal">
                    <span class="ic-note">{{ $i->item->unit }} (approved {{ $q($i->approved_base_quantity) }})</span>
                </div>
            @endforeach
            <div class="ic-fields" style="margin-top:12px">
                <div>
                    <label class="ic-label" for="collected_by">Collected by</label>
                    <select class="ic-select" id="collected_by" name="collected_by">
                        <option value="">— not in list —</option>
                        @foreach($staff as $s)<option value="{{ $s->id }}" @selected($s->id === $mr->requested_by)>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="ic-label" for="collected_by_name">Or name</label>
                    <input class="ic-input" id="collected_by_name" name="collected_by_name" maxlength="120">
                </div>
                <div>
                    <label class="ic-label" for="ho_notes">Notes</label>
                    <input class="ic-input" id="ho_notes" name="notes" maxlength="500">
                </div>
            </div>
            <button class="ic-btn ic-btn--gold" type="submit" style="margin-top:12px;width:100%"><i class="bi bi-box-arrow-up-right"></i> Confirm handover</button>
        </form>
    @endcan

    {{-- ── Returns ───────────────────────────────────────────────── --}}
    @can('recordReturn', $mr)
        <details class="ic-card" @if($errors->has('returns') || $errors->has('returns.*')) open @endif>
            <summary style="cursor:pointer;font-weight:750">Record materials returned to store</summary>
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.returns', $mr) }}" style="margin-top:10px">
                @csrf
                @foreach($mr->items->where('line_status', '!=', 'rejected') as $i)
                    @continue($i->netIssued() <= 0)
                    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;padding:6px 0">
                        <strong style="flex:1;min-width:140px">{{ $i->item->name }}</strong>
                        <input class="ic-input" style="max-width:130px" type="number" min="0" max="{{ $i->netIssued() }}" step="0.001" name="returns[{{ $i->id }}]" inputmode="decimal">
                        <span class="ic-note">{{ $i->item->unit }} (out: {{ $q($i->netIssued()) }})</span>
                    </div>
                @endforeach
                <input class="ic-input" name="note" placeholder="Note" maxlength="300" style="margin-top:8px">
                <button class="ic-btn ic-btn--dark" type="submit" style="margin-top:10px"><i class="bi bi-arrow-return-left"></i> Save return</button>
            </form>
        </details>
    @endcan

    {{-- ── Usage reconciliation ─────────────────────────────────── --}}
    @can('reconcile', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.reconcile', $mr) }}" class="ic-card">
            @csrf
            <h2>Record actual usage</h2>
            <p class="ic-note">For each material: how much was really used, and how much was wasted. Anything not explained must have a remark. Record returns first.</p>
            @foreach($mr->items->where('line_status', '!=', 'rejected') as $i)
                @php $net = $i->netIssued(); @endphp
                <div style="padding:10px 0;border-bottom:1px solid var(--ef-border)" data-recon data-net="{{ $net }}">
                    <div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap">
                        <strong>{{ $i->item->name }}</strong>
                        <span class="ic-note">Handed over {{ $q($i->issued_base_quantity) }} − returned {{ $q($i->returned_base_quantity) }} = <strong>{{ $q($net) }} {{ $i->item->unit }}</strong></span>
                    </div>
                    <div class="ic-fields" style="margin-top:8px">
                        <div><label class="ic-label">Used ({{ $i->item->unit }})</label>
                            <input class="ic-input" data-c type="number" min="0" step="0.001" name="lines[{{ $i->id }}][consumed]" value="{{ old("lines.{$i->id}.consumed", $q($net)) }}" inputmode="decimal"></div>
                        <div><label class="ic-label">Wasted ({{ $i->item->unit }})</label>
                            <input class="ic-input" data-w type="number" min="0" step="0.001" name="lines[{{ $i->id }}][wasted]" value="{{ old("lines.{$i->id}.wasted", 0) }}" inputmode="decimal"></div>
                        <div><label class="ic-label">Why wasted</label>
                            <input class="ic-input" name="lines[{{ $i->id }}][wastage_reason]" maxlength="300" value="{{ old("lines.{$i->id}.wastage_reason") }}"></div>
                        <div><label class="ic-label">Unexplained: <span data-u>0</span></label>
                            <input class="ic-input" name="lines[{{ $i->id }}][variance_reason]" maxlength="300" placeholder="Remark if not zero" value="{{ old("lines.{$i->id}.variance_reason") }}"></div>
                    </div>
                </div>
            @endforeach
            <button class="ic-btn ic-btn--gold" type="submit" style="margin-top:12px;width:100%"><i class="bi bi-clipboard-check"></i> Save usage</button>
        </form>
        @push('scripts')
        <script>
        document.querySelectorAll('[data-recon]').forEach(b => {
            const net = parseFloat(b.dataset.net) || 0, c = b.querySelector('[data-c]'), w = b.querySelector('[data-w]'), u = b.querySelector('[data-u]');
            const f = () => { const v = Math.round((net - (parseFloat(c.value) || 0) - (parseFloat(w.value) || 0)) * 1000) / 1000; u.textContent = v; u.style.color = Math.abs(v) > 0.0005 ? 'var(--ef-danger)' : ''; };
            c.addEventListener('input', f); w.addEventListener('input', f); f();
        });
        </script>
        @endpush
    @endcan

    @can('review', $mr)
        <form method="POST" data-ic-form action="{{ route('inventory.material-requests.review', $mr) }}" class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap">
            @csrf
            <strong style="width:100%">Review high wastage / unexplained difference</strong>
            <input class="ic-input" name="remarks" required minlength="3" maxlength="500" placeholder="Your finding (e.g. checked with cook, burnt batch)" style="flex:1;min-width:220px">
            <button class="ic-btn ic-btn--gold" type="submit">Mark reviewed</button>
        </form>
    @endcan
    @if($mr->review_remarks)
        <div class="ic-card ic-note">Reviewed by {{ $mr->reviewer?->name }} {{ $mr->reviewed_at?->format('d M') }}: {{ $mr->review_remarks }}</div>
    @endif

    {{-- ── Other actions ─────────────────────────────────────────── --}}
    <div class="ic-card" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-start">
        @can('update', $mr)
            <a class="ic-btn" href="{{ route('inventory.material-requests.edit', $mr) }}"><i class="bi bi-pencil"></i> Edit</a>
        @endcan
        @can('submit', $mr)
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.submit', $mr) }}">@csrf
                <button class="ic-btn ic-btn--gold" type="submit"><i class="bi bi-send"></i> Send for approval</button></form>
        @endcan
        @can('revise', $mr)
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.revise', $mr) }}">@csrf
                <button class="ic-btn" type="submit" title="Menu or numbers changed? Create a new revision; this one stays as it is."><i class="bi bi-files"></i> Create revision</button></form>
        @endcan
        @can('cancel', $mr)
            <form method="POST" data-ic-form action="{{ route('inventory.material-requests.cancel', $mr) }}" style="display:flex;gap:8px;flex-wrap:wrap;flex:1;min-width:260px">@csrf
                <input class="ic-input" name="reason" placeholder="Reason for cancelling" required minlength="3" maxlength="500" style="flex:1">
                <button class="ic-btn ic-btn--danger" type="submit">Cancel{{ $mr->status === 'approved' ? ' & restore stock' : '' }}</button></form>
        @endcan
    </div>
</div>
</x-admin-layout>
