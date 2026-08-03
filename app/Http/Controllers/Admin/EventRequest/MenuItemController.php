<?php

namespace App\Http\Controllers\Admin\EventRequest;

use App\Http\Controllers\Controller;
use App\Http\Requests\EventRequest\MenuItemRequest;
use App\Models\EventRequest;
use App\Models\EventRequestMenuCategory;
use App\Models\EventRequestMenuItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageMenu', EventRequest::class);

        $categories = EventRequestMenuCategory::active()->ordered()->get();

        $perPage = (int) $request->input('per_page', 25);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;

        $items = EventRequestMenuItem::with('category')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'ilike', '%'.$request->input('search').'%'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')))
            ->when($request->input('is_veg') !== null && $request->input('is_veg') !== '', fn ($q) => $q->where('is_veg', $request->input('is_veg')))
            ->when($request->input('is_active') !== null && $request->input('is_active') !== '', fn ($q) => $q->where('is_active', $request->input('is_active')))
            ->orderBy('category_id')
            ->orderBy('display_order')
            ->paginate($perPage)
            ->withQueryString();

        return view('event-request.admin.menu.items', compact('categories', 'items'));
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        EventRequestMenuItem::create($request->validated());

        return back()->with('status', 'Menu item added.');
    }

    public function update(MenuItemRequest $request, EventRequestMenuItem $item): RedirectResponse
    {
        $item->update($request->validated());

        return back()->with('status', 'Menu item updated.');
    }

    public function destroy(EventRequestMenuItem $item): RedirectResponse
    {
        $this->authorize('manageMenu', EventRequest::class);

        $item->delete();

        return back()->with('status', 'Menu item removed.');
    }
}
