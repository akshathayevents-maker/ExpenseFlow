@props(['index', 'stepNumber', 'data' => []])
{{-- Used on create/edit when old() data exists (validation failure repopulation) --}}
<div class="ef-sop-card" data-sop-index="{{ $index }}">
    <div class="ef-sop-card-head">
        <span class="ef-sop-num-badge sop-num-badge">{{ $stepNumber }}</span>
        <span class="ef-sop-step-lbl">Step {{ $stepNumber }}</span>
        <div style="display:flex;gap:2px">
            <button type="button" class="ef-sop-btn" onclick="moveSop(this,-1)" title="Move up" aria-label="Move step up">
                <i class="bi bi-chevron-up"></i>
            </button>
            <button type="button" class="ef-sop-btn" onclick="moveSop(this,1)" title="Move down" aria-label="Move step down">
                <i class="bi bi-chevron-down"></i>
            </button>
            <button type="button" class="ef-sop-btn --del" onclick="removeSop(this)" title="Remove step" aria-label="Remove step">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
    <div class="ef-sop-card-body">
        <div>
            <label class="ef-label">Step Title <span style="color:var(--ef-danger)">*</span></label>
            <input type="text"
                   name="sops[{{ $index }}][title]"
                   class="ef-input @error("sops.$index.title") --error @enderror"
                   value="{{ $data['title'] ?? '' }}"
                   placeholder="e.g. Heat the oil, Temper mustard seeds" required>
            @error("sops.$index.title")<div class="ef-field-error">{{ $message }}</div>@enderror
        </div>
        <div>
            <label class="ef-label">Instructions <span style="color:var(--ef-danger)">*</span></label>
            <textarea name="sops[{{ $index }}][instruction]"
                      class="ef-textarea @error("sops.$index.instruction") --error @enderror"
                      placeholder="Detailed instructions for this step…"
                      rows="3" required>{{ $data['instruction'] ?? '' }}</textarea>
            @error("sops.$index.instruction")<div class="ef-field-error">{{ $message }}</div>@enderror
        </div>
        <div style="max-width:160px">
            <label class="ef-label">Duration <span style="color:var(--ef-muted);font-weight:400;font-size:.78rem">(optional)</span></label>
            <div style="display:flex;align-items:center;gap:8px">
                <input type="number"
                       name="sops[{{ $index }}][duration_minutes]"
                       class="ef-input"
                       value="{{ $data['duration_minutes'] ?? '' }}"
                       placeholder="0" min="0" max="1440">
                <span style="color:var(--ef-muted);font-size:.84rem;white-space:nowrap">min</span>
            </div>
        </div>
    </div>
</div>
