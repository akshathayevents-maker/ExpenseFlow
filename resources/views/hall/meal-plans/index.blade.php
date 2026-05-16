<x-admin-layout title="Meal Plans">

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h5 class="mb-0 fw-bold"><i class="bi bi-egg-fried me-2 text-primary"></i>Meal Plans</h5>
        <p class="text-muted mb-0" style="font-size:.8rem">{{ $plans->total() }} plan(s)</p>
    </div>
    <a href="{{ route('hall.meal-plans.create') }}" class="btn btn-primary rounded-3">
        <i class="bi bi-plus-circle me-1"></i>Add Plan
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Price/Person</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td class="fw-semibold small">{{ $plan->name }}</td>
                        <td>
                            <span class="badge bg-{{ $plan->category === 'premium' ? 'warning text-dark' : ($plan->category === 'custom' ? 'info' : 'secondary') }} rounded-pill">
                                {{ ucfirst($plan->category) }}
                            </span>
                        </td>
                        <td class="small text-muted">{{ Str::limit($plan->description, 60) ?: '—' }}</td>
                        <td class="fw-semibold small">₹{{ number_format($plan->price_per_person, 2) }}</td>
                        <td>
                            <form method="POST" action="{{ route('hall.meal-plans.toggle-status', $plan) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $plan->is_active ? 'btn-success' : 'btn-outline-secondary' }} rounded-pill px-3" style="font-size:.72rem">
                                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </form>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('hall.meal-plans.edit', $plan) }}" class="btn btn-sm btn-outline-secondary rounded-2">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form method="POST" action="{{ route('hall.meal-plans.destroy', $plan) }}"
                                      onsubmit="return confirm('Delete this meal plan?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-egg-fried d-block fs-2 mb-2 opacity-40"></i>
                            No meal plans yet. <a href="{{ route('hall.meal-plans.create') }}">Add one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($plans->hasPages())
        <div class="card-footer bg-white border-0 d-flex justify-content-end">
            {{ $plans->links() }}
        </div>
    @endif
</div>

</x-admin-layout>
