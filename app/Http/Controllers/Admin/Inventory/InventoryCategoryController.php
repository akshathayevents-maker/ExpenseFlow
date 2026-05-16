<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\InventoryCategoryRequest;
use App\Models\InventoryCategory;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InventoryCategoryController extends Controller
{
    public function __construct(private AuditLogService $audit) {}

    public function index(): View
    {
        $categories = InventoryCategory::withCount('items')->orderBy('name')->paginate(20);
        return view('admin.inventory.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.inventory.categories.create');
    }

    public function store(InventoryCategoryRequest $request): RedirectResponse
    {
        $cat = InventoryCategory::create($request->validated() + ['is_active' => $request->boolean('is_active', true)]);
        $this->audit->log('created', 'inventory_category', $cat->id, $cat->name);
        return redirect()->route('admin.inventory.categories.index')->with('success', 'Category created.');
    }

    public function edit(InventoryCategory $category): View
    {
        return view('admin.inventory.categories.edit', compact('category'));
    }

    public function update(InventoryCategoryRequest $request, InventoryCategory $category): RedirectResponse
    {
        $old = $category->only(['name', 'is_active']);
        $category->update($request->validated() + ['is_active' => $request->boolean('is_active')]);
        $this->audit->log('updated', 'inventory_category', $category->id, $category->name, $old, $category->fresh()->only(['name', 'is_active']));
        return redirect()->route('admin.inventory.categories.index')->with('success', 'Category updated.');
    }

    public function destroy(InventoryCategory $category): RedirectResponse
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Cannot delete category with inventory items.');
        }
        $this->audit->log('deleted', 'inventory_category', $category->id, $category->name);
        $category->delete();
        return redirect()->route('admin.inventory.categories.index')->with('success', 'Category deleted.');
    }

    public function toggleStatus(InventoryCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);
        return back()->with('success', 'Status updated.');
    }
}
