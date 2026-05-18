<x-admin-layout title="Inventory Categories">

<x-ds.hero eyebrow="Inventory" title="Inventory Categories"
    :meta="[['icon' => 'bi-tags', 'text' => 'Organise inventory items by category']]">
    <x-slot:actions>
        <a href="{{ route('admin.inventory.categories.create') }}" class="ef-btn ef-btn-dark">
            <i class="bi bi-plus-lg"></i> New Category
        </a>
    </x-slot:actions>
</x-ds.hero>

<x-ds.card :no-pad="true">
    <div style="overflow-x:auto">
        <table class="ef-an-trend-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="r">Items</th>
                    <th style="text-align:center">Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                <tr>
                    <td class="fw">{{ $cat->name }}</td>
                    <td style="color:var(--ef-faint);font-size:.84rem">{{ $cat->description ?? '—' }}</td>
                    <td class="r">{{ $cat->items_count }}</td>
                    <td style="text-align:center">
                        @if($cat->is_active)
                            <span style="background:rgba(15,123,95,.1);border:1px solid rgba(15,123,95,.2);border-radius:5px;color:var(--ef-emerald);font-size:.72rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Active</span>
                        @else
                            <span style="background:rgba(100,116,139,.08);border:1px solid rgba(100,116,139,.15);border-radius:5px;color:#64748b;font-size:.72rem;font-weight:700;padding:2px 8px;text-transform:uppercase">Inactive</span>
                        @endif
                    </td>
                    <td style="text-align:right">
                        <div style="display:flex;gap:6px;justify-content:flex-end">
                            <a href="{{ route('admin.inventory.categories.edit', $cat) }}" class="ef-btn ef-btn-icon" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.inventory.categories.toggle-status', $cat) }}" style="display:inline">
                                @csrf @method('PATCH')
                                <button class="ef-btn ef-btn-icon" title="{{ $cat->is_active ? 'Deactivate' : 'Activate' }}">
                                    <i class="bi bi-{{ $cat->is_active ? 'pause' : 'play' }}"></i>
                                </button>
                            </form>
                            @if($cat->items_count === 0)
                            <form method="POST" action="{{ route('admin.inventory.categories.destroy', $cat) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="ef-btn ef-btn-icon" style="color:var(--ef-danger)" title="Delete"
                                        onclick="return confirm('Delete {{ $cat->name }}?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--ef-faint)">
                        <i class="bi bi-tags" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
                        No categories yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div style="padding:12px 18px;border-top:1px solid var(--ef-border)">{{ $categories->links() }}</div>
    @endif
</x-ds.card>

</x-admin-layout>
