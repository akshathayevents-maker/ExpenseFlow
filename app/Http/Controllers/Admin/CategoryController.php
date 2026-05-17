<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('search');

        $categories = ExpenseCategory::withCount('expenseRequests')
            ->when($search, fn ($q) =>
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
            )->orderBy('name')->paginate(15)->withQueryString();

        $stats = [
            'total'    => ExpenseCategory::count(),
            'active'   => ExpenseCategory::where('is_active', true)->count(),
            'inactive' => ExpenseCategory::where('is_active', false)->count(),
        ];

        return view('admin.categories.index', compact('categories', 'search', 'stats'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        ExpenseCategory::create($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(ExpenseCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, ExpenseCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(ExpenseCategory $category): RedirectResponse
    {
        if ($category->expenseRequests()->exists()) {
            return back()->with('error', 'Cannot delete: category has associated expense requests.');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted.');
    }

    public function toggleStatus(ExpenseCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);
        $label = $category->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Category {$label}.");
    }
}
