@extends('layouts.staff')
@section('content')
<style>
    .record-entry{max-width:1200px;margin:auto;padding-bottom:100px;color:#263246}
    .record-entry h2,.record-entry h3,.record-entry h4{font-weight:700}
    .record-entry h3{font-size:1.2rem}.record-entry h4{font-size:1rem;margin:12px 0}
    .re-card{background:#fff;border:1px solid #dce4ef;border-radius:14px;padding:20px;margin:16px 0;overflow:visible}
    .re-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;align-items:start}
    .re-grid label{display:flex;flex-direction:column;gap:5px;font-size:.87rem;font-weight:600}
    .re-grid input,.re-grid select,.re-grid textarea{width:100%;min-height:40px;border:1px solid #bac6d6;border-radius:7px;padding:8px;background:white;color:#263246}
    .re-grid textarea{min-height:40px}.re-row{border-left:3px solid #ccd9ed;background:#f6f8fc;padding:12px;margin:10px 0;border-radius:6px}
    .re-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:12px 0}
    .re-actions .btn{white-space:normal}.re-muted{color:#526278;font-size:.88rem}
    .re-totals{display:flex;flex-wrap:wrap;gap:18px;padding:10px 0;font-weight:600}
    .re-footer{position:sticky;bottom:0;z-index:5;background:#fff;border:1px solid #dce4ef;border-radius:10px;padding:12px;box-shadow:0 -3px 12px #243a5310}
    .record-entry [hidden]{display:none!important}.record-entry :focus-visible{outline:3px solid #4479cb;outline-offset:2px}
    .record-entry [aria-invalid=true]{border:2px solid #b42318}.re-error-link{color:#9c2019;text-align:left}
    #re-review{scroll-margin-top:20px}#re-status{font-size:.88rem} .re-summary{white-space:pre-line}
    html[data-theme="dark"] .record-entry{color:#e2e8f0}
    html[data-theme="dark"] .record-entry .re-card,html[data-theme="dark"] .record-entry .re-footer{background:#152032;border-color:#3b4d64}
    html[data-theme="dark"] .record-entry .re-row{background:#1b2a40;border-color:#55749e}
    html[data-theme="dark"] .record-entry .re-muted{color:#bac9db}
    html[data-theme="dark"] .record-entry .re-grid input,html[data-theme="dark"] .record-entry .re-grid select,html[data-theme="dark"] .record-entry .re-grid textarea{background:#101b2b;color:#e2e8f0;border-color:#65778e}
    .re-patient-picker{position:relative}.re-patient-results{position:absolute;z-index:20;top:calc(100% + 6px);left:0;right:0;max-height:360px;overflow:auto;background:#fff;border:1px solid #bac6d6;border-radius:10px;box-shadow:0 12px 30px #243a5326;padding:6px}
    .re-patient-result{display:flex;width:100%;justify-content:space-between;gap:14px;text-align:left;border:0;background:transparent;border-radius:7px;padding:11px 12px;color:#263246}.re-patient-result:hover,.re-patient-result[aria-selected="true"]{background:#e8f1ff}.re-patient-result small{color:#526278}
    .re-drafts-wrap{position:relative}.re-drafts-toggle{position:relative;width:44px;height:40px;display:inline-flex;align-items:center;justify-content:center}.re-drafts-badge{position:absolute;top:-7px;right:-7px;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:#dc3545;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center}
    .re-draft-panel{margin-top:-7px}.re-draft-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-top:1px solid #dce4ef}.re-draft-item:first-of-type{border-top:0}.re-draft-meta{min-width:0}.re-draft-meta strong,.re-draft-meta span{display:block}.re-draft-meta span{font-size:.84rem;color:#526278}
    html[data-theme="dark"] .re-patient-results{background:#152032;border-color:#65778e}html[data-theme="dark"] .re-patient-result{color:#e2e8f0}html[data-theme="dark"] .re-patient-result:hover,html[data-theme="dark"] .re-patient-result[aria-selected="true"]{background:#263b58}html[data-theme="dark"] .re-patient-result small,html[data-theme="dark"] .re-draft-meta span{color:#bac9db}html[data-theme="dark"] .re-draft-item{border-color:#3b4d64}
    @media(max-width:650px){.re-card{padding:12px}.re-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.re-footer{position:static}}
</style>
<div class="record-entry" id="record-entry">
    <x-staff.page-header
        :title="$mode === 'past' ? 'Past Records Entry' : 'Add Visit'"
        :subtitle="$mode === 'past' ? 'Enter multiple historical visits and payments for one patient.' : 'Record treatment and an optional payment or installment plan for this patient.'">
        <x-slot:actions><a class="btn btn-outline-secondary" href="{{ $patient ? route('staff.patients.show', $patient) : route('staff.patients.index') }}">Back to {{ $patient ? 'patient' : 'patients' }}</a></x-slot:actions>
    </x-staff.page-header>
    @if(!$patient)
        <div class="re-card">
            <label for="re-patient-search" class="form-label">Find patient by name, ID, birthdate, or contact number</label>
            <div class="re-patient-picker">
                <input class="form-control" id="re-patient-search" type="search" placeholder="Start typing a patient name or record detail" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="re-patient-results" aria-expanded="false">
                <div class="re-patient-results" id="re-patient-results" role="listbox" hidden></div>
            </div>
            <p class="re-muted mt-2 mb-0">Select a result to open the entry form immediately. Use ↑, ↓, and Enter from the keyboard.</p>
        </div>
    @else
        <div class="re-card re-patient-bar d-flex justify-content-between align-items-center gap-3 flex-wrap">
            <div class="re-patient-identity">
                <span class="re-label">Patient</span>
                <strong><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> {{ $patient->last_name }}, {{ $patient->first_name }}</strong>
            </div>
            <div class="re-patient-id"><span class="re-label">Patient ID</span><strong>P{{ str_pad((string) $patient->id, 5, '0', STR_PAD_LEFT) }}</strong></div>
            <div class="re-patient-state">
                <span class="re-state re-state-info"><i class="fa-regular fa-rectangle-list"></i> <span id="re-batch-count">1 record in batch</span></span>
                <span class="re-state re-state-draft"><i class="fa-regular fa-circle-check"></i> <span id="re-status" role="status">Unsaved draft</span></span>
            </div>
            <div class="re-actions mt-0 mb-0">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('staff.records.index', ['mode' => $mode]) }}"><i class="fa-solid fa-arrow-right-arrow-left"></i> Change patient</a>
                <div class="re-drafts-wrap">
                    <button type="button" class="btn btn-outline-primary re-drafts-toggle" id="re-drafts-toggle" aria-label="Open saved drafts" aria-controls="re-drafts" aria-expanded="false" title="Saved drafts" hidden>
                        <i class="fa fa-file-pen" aria-hidden="true"></i><span class="re-drafts-badge" id="re-drafts-count">0</span>
                    </button>
                </div>
            </div>
        </div>
        <div id="re-errors" class="alert alert-danger" role="alert" hidden></div>
        <div id="re-drafts" class="re-card re-draft-panel" hidden></div>
        <div id="re-editor">
            <div class="re-entry-toolbar">
                <div class="re-grid"><label><span><i class="fa-solid fa-user-doctor"></i> Default dentist</span><select id="re-default-doctor"><option value="">Choose dentist</option>@foreach($config['doctors'] as $doctor)<option value="{{ $doctor->id }}">{{ $doctor->name }}{{ !$doctor->is_active ? ' (inactive)' : '' }}</option>@endforeach</select></label></div>
                <div class="re-actions mt-0 mb-0">
                    <button type="button" class="btn btn-outline-primary" id="re-save-draft"><i class="fa-regular fa-floppy-disk"></i> Save Draft</button>
                    <button type="button" class="btn btn-outline-danger" id="re-discard"><i class="fa-regular fa-trash-can"></i> Discard Draft</button>
                </div>
            </div>
            <p class="re-muted mt-2">Tab moves between fields. Enter moves to the next field; Enter in notes starts a new line. Empty receipt amounts are ignored.</p>
            <div id="re-visits"></div>
        </div>
        <div id="re-review" class="re-card" hidden aria-live="polite"></div>
        <div class="re-footer" id="re-footer">
            <div id="re-grand-totals" class="re-totals" aria-live="polite"></div>
            <div class="re-actions">
                @if($mode === 'past')<button class="btn btn-outline-primary" type="button" id="re-add-visit"><i class="fa-solid fa-plus"></i> Add another visit</button>@endif
                <button type="button" class="btn btn-primary" id="re-review-button"><i class="fa-solid fa-check"></i> Review Records</button>
                @if($mode === 'visit')
                    <button type="button" class="btn btn-outline-primary" data-save-intent="visit">Save Visit</button>
                    <button type="button" class="btn btn-outline-primary" data-save-intent="payment">Save Visit &amp; Record Payment</button>
                    <button type="button" class="btn btn-outline-primary" data-save-intent="plan">Save Visit &amp; Create Installment Plan</button>
                @endif
            </div>
        </div>
    @endif
</div>
<script>window.recordEntryConfig = {{ Illuminate\Support\Js::from($config) }};</script>
<script src="{{ asset('js/staff-record-entry.js') }}?v=3" defer></script>
@endsection
