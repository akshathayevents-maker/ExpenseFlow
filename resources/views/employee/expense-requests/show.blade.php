<x-admin-layout title="Request #{{ $expenseRequest->id }}">
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('employee.expense-requests.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
            <div>
                <h4 class="mb-0 fw-bold">{{ $expenseRequest->title }}</h4>
                <p class="text-muted mb-0 small">Request #{{ $expenseRequest->id }} · Submitted {{ $expenseRequest->created_at->diffForHumans() }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <x-status-badge :status="$expenseRequest->status" />
            <x-priority-badge :priority="$expenseRequest->priority" />
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-info-circle me-1 text-primary"></i> Request Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Category</p>
                            <p class="fw-semibold mb-0">{{ $expenseRequest->category->name }}</p>
                        </div>
                        <div class="col-sm-6">
                            <p class="text-muted small mb-1">Amount</p>
                            <p class="fw-bold fs-5 mb-0 text-primary">₹{{ number_format($expenseRequest->amount, 2) }}</p>
                        </div>
                        @if($expenseRequest->vendor)
                            <div class="col-sm-6">
                                <p class="text-muted small mb-1">Vendor</p>
                                <p class="fw-semibold mb-0">{{ $expenseRequest->vendor->name }}</p>
                            </div>
                        @endif
                        @if($expenseRequest->notes)
                            <div class="col-12">
                                <p class="text-muted small mb-1">Notes</p>
                                <p class="mb-0">{{ $expenseRequest->notes }}</p>
                            </div>
                        @endif
                    </div>

                    @if($expenseRequest->isRejected() && $expenseRequest->rejection_reason)
                        <div class="alert alert-danger mt-3 mb-0">
                            <strong><i class="bi bi-x-circle me-1"></i> Rejected:</strong>
                            {{ $expenseRequest->rejection_reason }}
                        </div>
                    @endif

                    @if($expenseRequest->isApproved())
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-check-circle me-1"></i>
                            Approved by <strong>{{ $expenseRequest->approver?->name }}</strong>
                            on {{ $expenseRequest->approved_at?->format('d M Y, h:i A') }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- Bills --}}
            @if($expenseRequest->bills->isNotEmpty())
                <div class="card shadow-sm">
                    <div class="card-header bg-transparent fw-semibold">
                        <i class="bi bi-paperclip me-1 text-primary"></i> Bills
                        <span class="badge bg-secondary ms-1">{{ $expenseRequest->bills->count() }}</span>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($expenseRequest->bills as $bill)
                                <div class="col-6 col-sm-4">
                                    <div class="border rounded overflow-hidden position-relative" style="aspect-ratio:1">
                                        @if($bill->isImage())
                                            <img src="{{ $bill->url() }}" class="w-100 h-100" style="object-fit:cover;cursor:pointer"
                                                 data-bs-toggle="modal" data-bs-target="#billModal{{ $bill->id }}">
                                        @else
                                            <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-light">
                                                <i class="bi bi-file-earmark-pdf fs-2 text-danger"></i>
                                                <small class="mt-1 text-muted text-center px-1">{{ Str::limit($bill->original_name, 18) }}</small>
                                            </div>
                                        @endif
                                        <div class="position-absolute bottom-0 start-0 end-0 bg-dark bg-opacity-50 text-white px-2 py-1" style="font-size:.65rem">
                                            {{ $bill->humanSize() }}
                                        </div>
                                    </div>
                                    <a href="{{ $bill->url() }}" target="_blank" class="btn btn-sm btn-outline-secondary w-100 mt-1" style="font-size:.75rem">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                                @if($bill->isImage())
                                    <div class="modal fade" id="billModal{{ $bill->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title">{{ $bill->original_name }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-2 text-center">
                                                    <img src="{{ $bill->url() }}" class="img-fluid rounded">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-clock-history me-1 text-primary"></i> Timeline</div>
                <div class="card-body">
                    <div class="d-flex gap-2 mb-3">
                        <div class="rounded-circle bg-primary flex-shrink-0" style="width:10px;height:10px;margin-top:4px"></div>
                        <div>
                            <p class="mb-0 small fw-semibold">Submitted</p>
                            <p class="mb-0 text-muted" style="font-size:.75rem">{{ $expenseRequest->created_at->format('d M Y, h:i A') }}</p>
                        </div>
                    </div>

                    @if($expenseRequest->isPending())
                        <div class="d-flex gap-2">
                            <div class="rounded-circle bg-warning flex-shrink-0" style="width:10px;height:10px;margin-top:4px"></div>
                            <div>
                                <p class="mb-0 small fw-semibold">Awaiting Review</p>
                                <p class="mb-0 text-muted" style="font-size:.75rem">Your request is being reviewed</p>
                            </div>
                        </div>
                    @elseif($expenseRequest->approved_at)
                        <div class="d-flex gap-2">
                            <div class="rounded-circle flex-shrink-0 bg-{{ $expenseRequest->isRejected() ? 'danger' : 'success' }}"
                                 style="width:10px;height:10px;margin-top:4px"></div>
                            <div>
                                <p class="mb-0 small fw-semibold">{{ ucfirst($expenseRequest->status) }}</p>
                                <p class="mb-0 text-muted" style="font-size:.75rem">{{ $expenseRequest->approved_at->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
