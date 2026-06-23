<x-admin-layout title="Edit Client">
@push('styles')
<style>
.cr-wrap{max-width:600px;margin:0 auto;padding-bottom:60px}
.cr-page-hdr{display:flex;align-items:center;gap:12px;margin-bottom:22px}
.cr-back{color:#a0723a;text-decoration:none;font-size:.84rem;display:inline-flex;align-items:center;gap:5px}
.cr-back:hover{text-decoration:underline;color:#b8832a}
.cr-page-title{font-size:1.3rem;font-weight:800;color:#1c1712;flex:1;margin:0}
.cr-card{background:#fff;border:1.5px solid #e8e2d8;border-radius:16px;overflow:hidden;box-shadow:0 1px 6px rgba(0,0,0,.04);margin-bottom:16px}
.cr-card-hdr{padding:14px 20px;background:#faf8f5;border-bottom:1px solid #e8e2d8;font-size:.9rem;font-weight:700;color:#1c1712;display:flex;align-items:center;gap:8px}
.cr-card-body{padding:20px}
.cr-field{margin-bottom:16px}
.cr-label{display:block;font-size:.72rem;font-weight:700;color:#7a6e62;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
.cr-label span{font-weight:400;text-transform:none;color:#b0a89a}
.cr-input{width:100%;padding:11px 14px;border:1.5px solid #e8e2d8;border-radius:10px;font-size:.9rem;color:#1c1712;background:#fff;outline:none;transition:border-color .15s,box-shadow .15s}
.cr-input:focus{border-color:#a0723a;box-shadow:0 0 0 3px rgba(160,114,58,.12)}
.cr-input.is-invalid{border-color:#dc2626}
.cr-err{font-size:.76rem;color:#dc2626;margin-top:4px}
.cr-textarea{resize:vertical;min-height:80px}
.cr-grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:480px){.cr-grid2{grid-template-columns:1fr}}
.cr-btn-row{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.cr-btn{display:inline-flex;align-items:center;gap:7px;padding:11px 22px;border-radius:10px;font-size:.9rem;font-weight:700;border:1.5px solid transparent;cursor:pointer;text-decoration:none;transition:all .14s}
.cr-btn--primary{background:#a0723a;color:#fff}
.cr-btn--primary:hover{background:#b8832a;color:#fff}
.cr-btn--cancel{background:#f7f5f2;color:#7a6e62;border-color:#e8e2d8}
.cr-btn--cancel:hover{background:#e8e2d8}
</style>
@endpush

<div class="cr-wrap">
    <div class="cr-page-hdr">
        <a href="{{ route('meal-register.clients.show', $client) }}" class="cr-back"><i class="bi bi-arrow-left"></i> {{ $client->name }}</a>
        <h1 class="cr-page-title">Edit Client</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-radius:10px">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <form method="POST" action="{{ route('meal-register.clients.update', $client) }}">
        @csrf @method('PUT')
        <div class="cr-card">
            <div class="cr-card-hdr"><i class="bi bi-building"></i> Company Details</div>
            <div class="cr-card-body">
                <div class="cr-field">
                    <label class="cr-label" for="name">Company Name *</label>
                    <input id="name" name="name" type="text" class="cr-input @error('name') is-invalid @enderror"
                           value="{{ old('name', $client->name) }}" required>
                    @error('name')<div class="cr-err">{{ $message }}</div>@enderror
                </div>
                <div class="cr-grid2">
                    <div class="cr-field">
                        <label class="cr-label" for="contact_person">Contact Person <span>(optional)</span></label>
                        <input id="contact_person" name="contact_person" type="text"
                               class="cr-input @error('contact_person') is-invalid @enderror"
                               value="{{ old('contact_person', $client->contact_person) }}">
                        @error('contact_person')<div class="cr-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="cr-field">
                        <label class="cr-label" for="mobile">Mobile <span>(optional)</span></label>
                        <input id="mobile" name="mobile" type="tel" inputmode="numeric"
                               class="cr-input @error('mobile') is-invalid @enderror"
                               value="{{ old('mobile', $client->mobile) }}">
                        @error('mobile')<div class="cr-err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="cr-grid2">
                    <div class="cr-field">
                        <label class="cr-label" for="email">Email <span>(optional)</span></label>
                        <input id="email" name="email" type="email"
                               class="cr-input @error('email') is-invalid @enderror"
                               value="{{ old('email', $client->email) }}">
                        @error('email')<div class="cr-err">{{ $message }}</div>@enderror
                    </div>
                    <div class="cr-field">
                        <label class="cr-label" for="gst_number">GST Number <span>(optional)</span></label>
                        <input id="gst_number" name="gst_number" type="text"
                               class="cr-input @error('gst_number') is-invalid @enderror"
                               value="{{ old('gst_number', $client->gst_number) }}">
                        @error('gst_number')<div class="cr-err">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="cr-field">
                    <label class="cr-label" for="address">Address <span>(optional)</span></label>
                    <textarea id="address" name="address" class="cr-input cr-textarea @error('address') is-invalid @enderror">{{ old('address', $client->address) }}</textarea>
                    @error('address')<div class="cr-err">{{ $message }}</div>@enderror
                </div>
                <div class="cr-field">
                    <label class="cr-label" for="remarks">Remarks <span>(optional)</span></label>
                    <textarea id="remarks" name="remarks" class="cr-input cr-textarea @error('remarks') is-invalid @enderror" style="min-height:60px">{{ old('remarks', $client->remarks) }}</textarea>
                    @error('remarks')<div class="cr-err">{{ $message }}</div>@enderror
                </div>
                <div class="cr-btn-row">
                    <button type="submit" class="cr-btn cr-btn--primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                    <a href="{{ route('meal-register.clients.show', $client) }}" class="cr-btn cr-btn--cancel">Cancel</a>
                </div>
            </div>
        </div>
    </form>
</div>
</x-admin-layout>
