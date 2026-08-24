<x-admin-layout title="Attendance Regularization">

<x-ds.hero eyebrow="Approvals" title="Attendance Regularization"
    :meta="[['icon' => 'bi-calendar-check', 'text' => 'Review employee attendance correction requests']]">
</x-ds.hero>

@php
$statusChips = [
    'pending'   => ['bg' => 'rgba(216,154,61,.13)', 'color' => '#7D5218'],
    'approved'  => ['bg' => 'rgba(15,123,95,.11)',  'color' => '#0A5240'],
    'rejected'  => ['bg' => 'rgba(200,75,68,.11)',  'color' => '#9B2C2C'],
    'cancelled' => ['bg' => 'rgba(100,116,139,.11)','color' => '#334155'],
];
@endphp

@push('styles')
<style>
    .ar-list { display: flex; flex-direction: column; gap: 10px; }
    .ar-card { background: #fff; border: 1px solid var(--ef-border, #e5e7eb); border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; }
    .ar-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap; }
    .ar-name { font-weight: 700; word-break: break-word; }
    .ar-meta { color: var(--ef-faint, #6b7280); font-size: .82rem; margin-top: 2px; }
    .ar-reason { font-size: .84rem; color: var(--ef-ink, #1c1612); margin-top: 4px; }
    .ar-actions { display: flex; gap: 8px; flex-wrap: wrap; }
</style>
@endpush

<x-ds.card title="Requests">
    <div class="ar-list">
        @forelse($records as $record)
        @php $chip = $statusChips[$record->request_status] ?? $statusChips['pending']; @endphp
        <div class="ar-card">
            <div class="ar-top">
                <div>
                    <div class="ar-name">{{ $record->user->name }}</div>
                    <div class="ar-meta">
                        {{ $record->attendance_date->format('d M Y') }} ·
                        Requested: <span style="text-transform:capitalize">{{ str_replace('_', ' ', $record->requested_status) }}</span> ·
                        Submitted {{ $record->created_at->format('d M Y') }}
                    </div>
                    <div class="ar-reason">{{ $record->reason }}</div>
                    @if($record->review_note)
                        <div class="ar-meta">Note: {{ $record->review_note }}</div>
                    @endif
                </div>
                <span style="display:inline-flex;align-items:center;border-radius:6px;font-size:.72rem;font-weight:700;padding:3px 10px;background:{{ $chip['bg'] }};color:{{ $chip['color'] }};text-transform:capitalize">
                    {{ $record->request_status }}
                </span>
            </div>

            @can('approve', $record)
            <div class="ar-actions">
                <button type="button" class="ef-btn ef-btn-dark" data-bs-toggle="modal" data-bs-target="#approveArModal{{ $record->id }}">
                    <i class="bi bi-check-lg"></i> Approve
                </button>
                <button type="button" class="ef-btn" style="color:var(--ef-danger)" data-bs-toggle="modal" data-bs-target="#rejectArModal{{ $record->id }}">
                    <i class="bi bi-x-lg"></i> Reject
                </button>
            </div>

            <div class="modal fade" id="approveArModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-check-circle-fill me-2" style="color:var(--ef-emerald,#0F7B5F)"></i>Approve Regularization</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('manager.attendance-regularizations.approve', $record) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <p>Approve {{ $record->user->name }}'s request to mark {{ $record->attendance_date->format('d M Y') }} as "{{ str_replace('_', ' ', $record->requested_status) }}"?</p>
                                <label class="ef-label" for="approve_note_{{ $record->id }}">Note (optional)</label>
                                <textarea class="ef-textarea" id="approve_note_{{ $record->id }}" name="review_note" rows="2"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="ef-btn ef-btn-dark">Approve</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="rejectArModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-x-circle-fill me-2" style="color:var(--ef-danger)"></i>Reject Regularization</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('manager.attendance-regularizations.reject', $record) }}">
                            @csrf
                            @method('PATCH')
                            <div class="modal-body">
                                <p>Rejecting {{ $record->user->name }}'s request for {{ $record->attendance_date->format('d M Y') }}.</p>
                                <label class="ef-label" for="reject_note_{{ $record->id }}">Reason <span style="color:var(--ef-danger)">*</span></label>
                                <textarea class="ef-textarea" id="reject_note_{{ $record->id }}" name="review_note" rows="3" minlength="5" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endcan
        </div>
        @empty
        <div style="text-align:center;padding:40px 16px;color:var(--ef-faint,#6b7280)">
            <i class="bi bi-calendar-check" style="font-size:1.5rem;display:block;margin-bottom:8px"></i>
            No attendance regularization requests found.
        </div>
        @endforelse
    </div>
</x-ds.card>

</x-admin-layout>
