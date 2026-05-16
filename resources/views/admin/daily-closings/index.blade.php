<x-admin-layout title="Daily Closings">
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h4 class="mb-0 fw-bold">Daily Closings</h4>
        <p class="text-muted mb-0 small">End-of-day operational summaries</p>
    </div>
    @if(!$todayClosed)
    <a href="{{ route('admin.daily-closings.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Close Today
    </a>
    @else
    <span class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3">
        <i class="bi bi-check-circle me-1"></i> Today Closed
    </span>
    @endif
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Expenses</th>
                        <th class="text-center">Count</th>
                        <th class="text-end">Payments</th>
                        <th class="text-center">Status</th>
                        <th>Created By</th>
                        <th>Verified By</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($closings as $closing)
                    @php
                        $colors = \App\Models\DailyClosing::statusColors();
                        $color  = $colors[$closing->status] ?? 'secondary';
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $closing->date->format('d M Y') }}
                            @if($closing->date->isToday())
                                <span class="badge bg-info-subtle text-info ms-1" style="font-size:.62rem">Today</span>
                            @endif
                        </td>
                        <td class="text-end fw-semibold">₹{{ number_format($closing->expense_total, 2) }}</td>
                        <td class="text-center text-muted small">{{ $closing->expense_count }}</td>
                        <td class="text-end text-muted small">₹{{ number_format($closing->payment_total, 2) }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} border border-{{ $color }}-subtle"
                                  style="font-size:.7rem;text-transform:uppercase">
                                {{ $closing->status }}
                            </span>
                        </td>
                        <td class="text-muted small">{{ $closing->creator->name }}</td>
                        <td class="text-muted small">{{ $closing->verifier?->name ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.daily-closings.show', $closing) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-check fs-2 d-block mb-2"></i>No daily closings recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($closings->hasPages())
    <div class="card-footer bg-transparent border-top">{{ $closings->links() }}</div>
    @endif
</div>
</x-admin-layout>
