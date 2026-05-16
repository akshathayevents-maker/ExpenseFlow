<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryStockAlert;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockAlertController extends Controller
{
    public function index(Request $request): View
    {
        $alerts = InventoryStockAlert::with(['item.category', 'resolver'])
            ->when($request->get('resolved') === '1', fn ($q) => $q->where('is_resolved', true))
            ->when($request->get('resolved') !== '1', fn ($q) => $q->where('is_resolved', false))
            ->when($request->get('type'), fn ($q, $v) => $q->where('alert_type', $v))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $unresolvedCount = InventoryStockAlert::where('is_resolved', false)->count();

        return view('admin.inventory.alerts.index', compact('alerts', 'unresolvedCount'));
    }

    public function resolve(InventoryStockAlert $alert): RedirectResponse
    {
        $alert->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Alert resolved.');
    }

    public function resolveAll(): RedirectResponse
    {
        InventoryStockAlert::where('is_resolved', false)->update([
            'is_resolved' => true,
            'resolved_at' => now(),
            'resolved_by' => auth()->id(),
        ]);

        return back()->with('success', 'All alerts resolved.');
    }
}
