{{-- Tamil + note fields for one step. $idx row index or '__IDX__', $data step values. Same index as English → stays paired when reordered. --}}
<div class="bl-ta">
    <label class="ef-label" lang="ta">படி தலைப்பு (Tamil title)</label>
    <input type="text" name="sops[{{ $idx }}][title_ta]" class="ef-input" lang="ta" value="{{ $data['title_ta'] ?? '' }}" maxlength="200" placeholder="எ.கா. எண்ணெய் சூடாக்குதல்">
</div>
<div class="bl-ta">
    <label class="ef-label" lang="ta">தமிழ் விளக்கம் (Tamil instructions)
        @if(empty($data['instruction_ta']) && ! empty($data['instruction']))<span class="bl-missing">missing</span>@endif</label>
    <textarea name="sops[{{ $idx }}][instruction_ta]" class="ef-textarea" lang="ta" rows="3" placeholder="இந்த படிக்கான செய்முறை தமிழில்…">{{ $data['instruction_ta'] ?? '' }}</textarea>
</div>
<details class="bl-note" @if(! empty($data['note']) || ! empty($data['note_ta'])) open @endif>
    <summary>Step note / food-safety tip (optional)</summary>
    <input type="text" name="sops[{{ $idx }}][note]" class="ef-input bl-en" maxlength="500" value="{{ $data['note'] ?? '' }}" placeholder="e.g. Keep chicken below 5 °C until cooking">
    <input type="text" name="sops[{{ $idx }}][note_ta]" class="ef-input bl-ta" lang="ta" maxlength="500" value="{{ $data['note_ta'] ?? '' }}" placeholder="தமிழ் குறிப்பு" style="margin-top:6px">
</details>
