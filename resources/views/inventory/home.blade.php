<x-admin-layout title="Store & Inventory">
@include('inventory._styles')
<div class="ic-wrap">
    <div class="ic-head"><h1>Store &amp; Inventory</h1>
        @if(auth()->user()->isAdmin())
            <a class="ic-btn" href="{{ route('admin.inventory.items.index') }}"><i class="bi bi-journal-text"></i> Item catalogue</a>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isStorekeeper())
            <a class="ic-btn" href="{{ route('inventory.stock') }}"><i class="bi bi-boxes"></i> Stock</a>
        @endif
        @if(auth()->user()->isAdmin() || auth()->user()->isManager())
            <a class="ic-btn" href="{{ route('inventory.reports.index') }}"><i class="bi bi-shield-check"></i> Loss control</a>
        @endif
    </div>

    <div class="ic-card" style="padding-bottom:14px">
        <label class="ic-label" for="icFindItem">Find an item — English, தமிழ் or Tanglish</label>
        <div><input type="text" class="ic-input" id="icFindItem" placeholder="e.g. parupu, துவரம் பருப்பு, sakkarai" autocomplete="off"></div>
        <div class="ic-note" id="icFindResult" aria-live="polite" style="margin-top:8px"></div>
    </div>
    @include('inventory._item-picker')
    @push('scripts')
    <script>
    (function () {
        const out = document.getElementById('icFindResult');
        const admin = @json(auth()->user()->isAdmin());
        const base = @json(url('admin/inventory/items'));
        window.IcItemPicker.attach(document.getElementById('icFindItem'), {
            onPick(r) {
                out.textContent = '';
                const strong = document.createElement('strong');
                strong.textContent = r.name + (r.name_ta ? ' — ' + r.name_ta : '');
                out.append(strong, ' · ' + (r.category || '') + ' · stock unit ' + r.unit + ' ');
                if (admin) { const a = document.createElement('a'); a.href = base + '/' + r.id; a.textContent = 'Open item →'; out.append(a); }
            },
        });
    })();
    </script>
    @endpush

    @if($admin)
        <h2 class="ic-label" style="font-size:.85rem">Needs your decision</h2>
        <div class="ic-tiles">
            <a class="ic-tile" href="{{ route('inventory.purchases.index', ['status' => 'submitted']) }}"><i class="bi bi-cart-check"></i><b>{{ $admin['purchases'] }}</b><span>Purchase approvals</span></a>
            <a class="ic-tile" href="{{ route('inventory.material-requests.index', ['tab' => 'approve']) }}"><i class="bi bi-clipboard-check"></i><b>{{ $admin['materials'] }}</b><span>Material approvals</span></a>
            <a class="ic-tile" href="{{ route('inventory.stock', ['show' => 'low']) }}"><i class="bi bi-exclamation-triangle"></i><b>{{ $admin['alerts'] }}</b><span>Stock alerts</span></a>
            <a class="ic-tile" href="{{ route('inventory.material-requests.index', ['tab' => 'review']) }}"><i class="bi bi-graph-up-arrow"></i><b>{{ $admin['review'] }}</b><span>Unusual usage</span></a>
            <a class="ic-tile" href="{{ route('inventory.reports.show', 'reconciliation') }}"><i class="bi bi-hourglass-split"></i><b>{{ $admin['unreconciled'] }}</b><span>Usage not recorded</span></a>
        </div>
    @endif

    @if($store)
        <h2 class="ic-label" style="font-size:.85rem">Store tasks</h2>
        <div class="ic-tiles">
            <a class="ic-tile ic-tile--primary" href="{{ route('inventory.material-requests.index', ['tab' => 'handover']) }}"><i class="bi bi-box-arrow-up-right"></i><b>{{ $store['handover'] }}</b><span>Ready for handover</span></a>
            <a class="ic-tile" href="{{ route('inventory.material-requests.index', ['tab' => 'all']) }}"><i class="bi bi-calendar-check"></i><b>{{ $store['issuedToday'] }}</b><span>Issued today</span></a>
            <a class="ic-tile" href="{{ route('inventory.material-requests.index', ['tab' => 'out']) }}"><i class="bi bi-arrow-return-left"></i><b>{{ $store['out'] }}</b><span>Out · returns pending</span></a>
            <a class="ic-tile" href="{{ route('inventory.purchases.index', ['status' => 'approved']) }}"><i class="bi bi-truck"></i><b>{{ $store['receive'] }}</b><span>Goods to receive</span></a>
        </div>
        @if($store['queue']->isNotEmpty())
            <div class="ic-card"><h2>Next handovers</h2>
                @foreach($store['queue'] as $r)
                    <a class="ic-list-card" href="{{ route('inventory.material-requests.show', $r) }}">
                        <div class="ic-list-main"><div class="ic-list-title">{{ $r->preparation_name ?: $r->forLabel() }}</div>
                            <div class="ic-list-sub">{{ $r->requester?->name }} · {{ $r->required_date->format('D d M') }} {{ $r->required_time ? \Carbon\Carbon::parse($r->required_time)->format('h:i A') : '' }}</div></div>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @endforeach
            </div>
        @endif
    @endif

    <h2 class="ic-label" style="font-size:.85rem">Kitchen</h2>
    <div class="ic-tiles">
        <a class="ic-tile ic-tile--primary" href="{{ route('inventory.material-requests.create') }}"><i class="bi bi-plus-circle"></i><b>+</b><span>Request materials</span></a>
        <a class="ic-tile" href="{{ route('inventory.material-requests.index', ['tab' => 'mine']) }}"><i class="bi bi-list-check"></i><b>{{ $kitchen['open'] }}</b><span>My open requests</span></a>
        <a class="ic-tile" href="{{ route('inventory.material-requests.index', ['tab' => 'mine']) }}"><i class="bi bi-hourglass"></i><b>{{ $kitchen['waiting'] }}</b><span>Waiting approval</span></a>
        <a class="ic-tile" href="{{ route('inventory.material-requests.index', ['tab' => 'mine']) }}"><i class="bi bi-clipboard-data"></i><b>{{ $kitchen['usage'] }}</b><span>Record usage</span></a>
        <a class="ic-tile" href="{{ route('inventory.purchases.create') }}"><i class="bi bi-bag-plus"></i><b>+</b><span>Enter a purchase</span></a>
    </div>
    @if($kitchen['recent']->isNotEmpty())
        <div class="ic-card"><h2>My recent requests</h2>
            @foreach($kitchen['recent'] as $r)
                <a class="ic-list-card" href="{{ route('inventory.material-requests.show', $r) }}">
                    <div class="ic-list-main"><div class="ic-list-title">{{ $r->preparation_name ?: $r->forLabel() }}</div>
                        <div class="ic-list-sub">{{ $r->reference() }} · {{ $r->required_date->format('D d M') }}</div></div>
                    @include('inventory._status', ['label' => $r->statusLabel(), 'tone' => \App\Models\MaterialRequest::statusTone($r->status)])
                </a>
            @endforeach
        </div>
    @endif
</div>
</x-admin-layout>
