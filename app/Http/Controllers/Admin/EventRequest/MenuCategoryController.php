<?php

namespace App\Http\Controllers\Admin\EventRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest\MenuCategoryRequest;
use App\Models\EventRequest;
use App\Models\EventRequestMenuCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MenuCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('manageMenu', EventRequest::class);

        $categories = EventRequestMenuCategory::withCount('items')->ordered()->get();

        return view('event-request.admin.menu.categories', compact('categories'));
    }

    public function store(MenuCategoryRequest $request): RedirectResponse
    {
        EventRequestMenuCategory::create($request->validated());

        return back()->with('status', 'Category added.');
    }

    public function update(MenuCategoryRequest $request, EventRequestMenuCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return back()->with('status', 'Category updated.');
    }

    public function destroy(EventRequestMenuCategory $category): RedirectResponse
    {
        $this->authorize('manageMenu', EventRequest::class);

        $category->delete();

        return back()->with('status', 'Category removed.');
    }
}
