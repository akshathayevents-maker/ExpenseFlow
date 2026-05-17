<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVendorRequest;
use App\Http\Requests\Admin\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $vendors = Vendor::withCount('expenseRequests')
            ->when($search, fn ($q) =>
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
            )->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total'    => Vendor::count(),
            'active'   => Vendor::where('is_active', true)->count(),
            'inactive' => Vendor::where('is_active', false)->count(),
        ];

        return view('admin.vendors.index', compact('vendors', 'search', 'stats'));
    }

    public function create(): View
    {
        return view('admin.vendors.create');
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        Vendor::create($request->validated());

        return redirect()->route('admin.vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function edit(Vendor $vendor): View
    {
        return view('admin.vendors.edit', compact('vendor'));
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $vendor->update($request->validated());

        return redirect()->route('admin.vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor): RedirectResponse
    {
        if ($vendor->expenseRequests()->exists()) {
            return back()->with('error', 'Cannot delete: vendor has associated expense requests.');
        }

        $vendor->delete();

        return redirect()->route('admin.vendors.index')
            ->with('success', 'Vendor deleted.');
    }

    public function toggleStatus(Vendor $vendor): RedirectResponse
    {
        $vendor->update(['is_active' => ! $vendor->is_active]);
        $label = $vendor->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Vendor {$label}.");
    }
}
