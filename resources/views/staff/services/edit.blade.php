@extends('layouts.staff')

@section('content')

@php
    // Walk-in rule (recommended):
    // - If duration_minutes is empty => walk-in (no time slot)
    // - If duration_minutes is 1–5 => treat as walk-in too (prevents "too many slots" problem)
    $durValRaw = old('duration_minutes', $service->duration_minutes);
    $durValStr = is_null($durValRaw) ? '' : (string)$durValRaw;
    $durNum = is_numeric($durValStr) ? (int)$durValStr : null;

    $walkInByDuration = ($durValStr === '' || $durValNum = ($durNum !== null && $durNum > 0 && $durNum <= 5));
    $walkInOld = old('is_walk_in', $walkInByDuration ? 1 : 0);
    $isWalkInChecked = (int)$walkInOld === 1;
@endphp

<style>
    .page-title {
        font-size: 26px;
        font-weight: 600;
        color: #1e40af;
        margin-bottom: 10px;
    }

    .page-subtitle {
        color: #6b7280;
        margin-bottom: 25px;
        font-size: 15px;
    }

    .form-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        max-width: 700px;
        margin: auto;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 4px;
        color: #374151;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        border: 1px solid #d1d5db;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }

    .help-text{
        font-size: 12px;
        color: #6b7280;
        margin-top: 6px;
        line-height: 1.25rem;
    }

    .submit-btn {
        background: #2563eb;
        padding: 10px 18px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        transition: .2s;
        border: none;
    }

    .submit-btn:hover {
        background: #1d4ed8;
    }

    .cancel-link {
        margin-left: 10px;
        color: #6b7280;
        font-weight: 500;
        transition: .2s;
        text-decoration: none;
    }

    .cancel-link:hover {
        color: #1d4ed8;
    }

    .error-text{
        color:#dc2626;
        font-size:12px;
        margin-top:6px;
    }

    .inline-row{
        display:flex;
        align-items:flex-start;
        gap:10px;
    }
    .inline-row input[type="checkbox"]{
        margin-top:4px;
        transform: scale(1.05);
    }

    .soft-card{
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        padding: 12px 14px;
        border-radius: 10px;
        margin-top: 10px;
    }
</style>

<h2 class="page-title">Edit Service</h2>
<p class="page-subtitle">Update the details of this service below.</p>

<div class="form-card">
    <form action="{{ route('staff.services.update', $service) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="form-label">Service Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $service->name) }}" required>
                @error('name') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="form-label">Base Price</label>
                <input type="number" step="0.01" name="base_price" class="form-input" value="{{ old('base_price', $service->base_price) }}" required>
                @error('base_price') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="form-label">Allow Custom Price?</label>
                <select name="allow_custom_price" class="form-select" required>
                    <option value="0" {{ old('allow_custom_price', $service->allow_custom_price) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('allow_custom_price', $service->allow_custom_price) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
                @error('allow_custom_price') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            {{-- ✅ Walk-in toggle (NO time slot) --}}
            <div>
                <label class="form-label">Scheduling Mode</label>

                {{-- Always send 0 if unchecked --}}
                <input type="hidden" name="is_walk_in" value="0">

                <div class="inline-row">
                    <input type="checkbox" id="is_walk_in" name="is_walk_in" value="1" {{ $isWalkInChecked ? 'checked' : '' }}>
                    <div>
                        <div style="font-weight:700;color:#111827;">Walk-in (no time slot)</div>
                        <div class="help-text">
                            Use this for small services like checkup.
                            Patient will pick a date only and can come anytime during clinic hours.
                        </div>
                    </div>
                </div>

                <div class="soft-card">
                    <div style="font-weight:700;color:#111827;">Tip</div>
                    <div class="help-text" style="margin-top:4px;">
                        If Walk-in is ON, we will clear <b>Duration</b>. If Walk-in is OFF, set a realistic duration (e.g. 30–60 minutes).
                    </div>
                </div>
            </div>

            {{-- ✅ Duration (minutes) --}}
            <div id="durationWrap">
                <label class="form-label">Duration (minutes)</label>
                <input
                    id="duration_minutes"
                    type="number"
                    name="duration_minutes"
                    class="form-input"
                    min="1"
                    max="60"
                    step="1"
                    value="{{ old('duration_minutes', $service->duration_minutes) }}"
                    placeholder="e.g. 60 for treatment (leave empty for Walk-in)"
                >
                <div class="help-text">
                    Only used for scheduled services (time slots + overlap prevention).
                    Leave empty if this service is Walk-in.
                </div>
                @error('duration_minutes') <div class="error-text">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea">{{ old('description', $service->description) }}</textarea>
                @error('description') <div class="error-text">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-5">
            <button type="submit" class="submit-btn">Update Service</button>
            <a href="{{ route('staff.services.index') }}" class="cancel-link">Cancel</a>
        </div>
    </form>
</div>

<script>
(function(){
    const cb = document.getElementById('is_walk_in');
    const durationEl = document.getElementById('duration_minutes');
    const wrap = document.getElementById('durationWrap');

    if(!cb || !durationEl || !wrap) return;

    function apply(){
        const on = cb.checked;

        // If Walk-in ON: clear duration and make it read-only (still submits empty => null)
        if(on){
            durationEl.value = '';
            durationEl.readOnly = true;
            wrap.style.opacity = '0.65';
        } else {
            durationEl.readOnly = false;
            wrap.style.opacity = '1';
        }
    }

    cb.addEventListener('change', apply);
    apply();
})();
</script>

@endsection
