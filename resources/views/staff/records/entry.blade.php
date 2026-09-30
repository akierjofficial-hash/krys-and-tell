@extends('layouts.staff')
@section('content')
<style>
    .record-entry{max-width:1200px;margin:auto;padding-bottom:100px;color:#263246}
    .record-entry h2,.record-entry h3,.record-entry h4{font-weight:700}
    .record-entry h3{font-size:1.2rem}.record-entry h4{font-size:1rem;margin:12px 0}
    .re-card{background:#fff;border:1px solid #dce4ef;border-radius:14px;padding:20px;margin:16px 0;overflow:visible}
    .re-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;align-items:start}
    .re-grid label{display:flex;flex-direction:column;gap:5px;font-size:.87rem;font-weight:600}
    .re-field-caption{display:inline;line-height:1.35}
    .re-grid input,.re-grid select,.re-grid textarea{width:100%;min-height:40px;border:1px solid #bac6d6;border-radius:7px;padding:8px;background:white;color:#263246}
    .re-service-field{min-width:0;font-size:.87rem;font-weight:600}
    .re-service-field>label{display:block;margin-bottom:5px}
    .re-service-picker{position:relative}
    .re-service-options{position:absolute;z-index:30;top:calc(100% + 5px);left:0;width:min(320px,calc(100vw - 48px));min-width:100%;max-height:260px;overflow:auto;padding:5px;background:#fff;border:1px solid #bac6d6;border-radius:9px;box-shadow:0 12px 30px #243a5326}
    .re-service-options button{display:block;width:100%;padding:9px 10px;border:0;border-radius:6px;background:transparent;color:#263246;text-align:left;font-size:.9rem}
    .re-service-options button:hover,.re-service-options button.is-active{background:#e8f1ff}
    .re-service-options button[aria-selected="true"]{font-weight:700}
    .re-service-hint{padding:9px 10px;color:#526278;font-size:.84rem}
    .re-grid textarea{min-height:40px}.re-row{border-left:3px solid #ccd9ed;background:#f6f8fc;padding:12px;margin:10px 0;border-radius:6px}
    .re-actions{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:12px 0}
    .re-actions .btn{white-space:normal}.re-muted{color:#526278;font-size:.88rem}
    .re-totals{display:flex;flex-wrap:wrap;gap:18px;padding:10px 0;font-weight:600}
    .re-billing-warning{flex-basis:100%;padding:10px 12px;border:1px solid #f4b4b4;border-radius:8px;background:#fff3f3;color:#9c2019;font-size:.88rem;line-height:1.4}
    .re-footer{position:sticky;bottom:0;z-index:5;background:#fff;border:1px solid #dce4ef;border-radius:10px;padding:12px;box-shadow:0 -3px 12px #243a5310}
    .record-entry [hidden]{display:none!important}.record-entry :focus-visible{outline:3px solid #4479cb;outline-offset:2px}
    .record-entry [aria-invalid=true]{border:2px solid #b42318}.re-error-link{color:#9c2019;text-align:left}
    #re-review{scroll-margin-top:20px}#re-status{font-size:.88rem} .re-summary{white-space:pre-line}
    html[data-theme="dark"] .record-entry{color:#e2e8f0}
    html[data-theme="dark"] .record-entry .re-card,html[data-theme="dark"] .record-entry .re-footer{background:#152032;border-color:#3b4d64}
    html[data-theme="dark"] .record-entry .re-row{background:#1b2a40;border-color:#55749e}
    html[data-theme="dark"] .record-entry .re-muted{color:#bac9db}
    html[data-theme="dark"] .record-entry .re-grid input,html[data-theme="dark"] .record-entry .re-grid select,html[data-theme="dark"] .record-entry .re-grid textarea{background:#101b2b;color:#e2e8f0;border-color:#65778e}
    html[data-theme="dark"] .re-service-options{background:#152032;border-color:#65778e}
    html[data-theme="dark"] .re-service-options button{color:#e2e8f0}
    html[data-theme="dark"] .re-service-options button:hover,html[data-theme="dark"] .re-service-options button.is-active{background:#263b58}
    html[data-theme="dark"] .re-service-hint{color:#bac9db}
    .re-patient-picker{position:relative}.re-patient-results{position:absolute;z-index:20;top:calc(100% + 6px);left:0;right:0;max-height:360px;overflow:auto;background:#fff;border:1px solid #bac6d6;border-radius:10px;box-shadow:0 12px 30px #243a5326;padding:6px}
    .re-patient-result{display:flex;width:100%;justify-content:space-between;gap:14px;text-align:left;border:0;background:transparent;border-radius:7px;padding:11px 12px;color:#263246}.re-patient-result:hover,.re-patient-result[aria-selected="true"]{background:#e8f1ff}.re-patient-result small{color:#526278}
    .re-drafts-wrap{position:relative}.re-drafts-toggle{position:relative;width:44px;height:40px;display:inline-flex;align-items:center;justify-content:center}.re-drafts-badge{position:absolute;top:-7px;right:-7px;min-width:20px;height:20px;padding:0 5px;border-radius:999px;background:#dc3545;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center}
    .re-draft-panel{margin-top:-7px}.re-draft-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 0;border-top:1px solid #dce4ef}.re-draft-item:first-of-type{border-top:0}.re-draft-meta{min-width:0}.re-draft-meta strong,.re-draft-meta span{display:block}.re-draft-meta span{font-size:.84rem;color:#526278}
    html[data-theme="dark"] .re-patient-results{background:#152032;border-color:#65778e}html[data-theme="dark"] .re-patient-result{color:#e2e8f0}html[data-theme="dark"] .re-patient-result:hover,html[data-theme="dark"] .re-patient-result[aria-selected="true"]{background:#263b58}html[data-theme="dark"] .re-patient-result small,html[data-theme="dark"] .re-draft-meta span{color:#bac9db}html[data-theme="dark"] .re-draft-item{border-color:#3b4d64}
    .kt-staff .record-entry .re-visit-card{padding:24px !important;margin:20px 0 !important;box-shadow:0 5px 20px #233b5510}
    .re-visit-heading,.re-row-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .re-visit-heading h3{margin:0}.re-visit-heading .re-actions{margin:0}
    .re-section{padding:22px 0;border-top:1px solid #e1e8f2}.re-section:first-of-type{margin-top:20px}
    .kt-staff .record-entry .re-section h4{font-size:14px !important;color:#243956 !important;text-transform:none;letter-spacing:0;margin:0 0 14px !important}
    .re-section h5{font-size:13px;font-weight:700;margin:0 0 6px}
    .re-section>.re-muted{margin:8px 0 14px}.re-section>.btn{margin-top:6px}
    .kt-staff .record-entry .re-row{padding:16px !important;margin:12px 0 !important;border:1px solid #dce5f0 !important;background:#f8faff !important}
    .re-row-heading{margin-bottom:12px}.re-row-heading strong{font-size:13px}
    .kt-staff .record-entry .re-procedure-main{grid-template-columns:minmax(230px,2fr) minmax(160px,1fr) !important}
    .re-procedure-extra{margin-top:13px}.re-procedure-extra summary,.re-paste-panel summary{width:fit-content;color:#1266bf;font-size:13px;font-weight:700;cursor:pointer;padding:5px 0}
    .re-procedure-extra-grid{margin-top:12px}.re-procedure-extra-grid textarea{min-height:44px}
    .re-recement-context{max-width:620px;margin-top:13px}
    .re-recement-context label{display:flex;flex-direction:column;gap:6px;font-size:13px;font-weight:700}
    .re-recement-context select{width:100%;min-height:44px;border:1px solid #bac6d6;border-radius:8px;padding:8px;background:#fff;color:#263246}
    .re-recement-context p{margin:7px 0 0}
    html[data-theme="dark"] .record-entry .re-recement-context select{background:#101b2b;color:#e2e8f0;border-color:#65778e}
    .kt-staff .record-entry .re-plan-overview{grid-template-columns:repeat(3,minmax(0,1fr)) !important}
    .kt-staff .record-entry .re-plan-grid{grid-template-columns:repeat(3,minmax(0,1fr)) !important;margin-top:18px}
    .re-unknown-total{align-self:end;margin:0;padding:11px 13px;background:#eaf4ff;border:1px solid #c9dffa;border-radius:9px;color:#164978;font-size:13px;font-weight:700}
    .re-ended-choice,.re-closure-review{display:flex;align-items:flex-start;gap:9px;margin:18px 0 0;font-size:13px;font-weight:650}
    .re-ended-choice input,.re-closure-review input{flex:none;width:18px;height:18px;margin:1px 0 0}
    .re-end-fields{margin-top:14px;padding:16px;border:1px solid #dce5f0;border-radius:10px;background:#f8faff}
    .re-end-fields .re-grid{max-width:320px}.re-end-fields .re-closure-review{margin-top:12px}
    .re-plan-hint{margin:14px 0 0}.re-subsection{margin-top:22px;padding-top:20px;border-top:1px solid #e1e8f2}
    .re-payment-actions{display:flex;align-items:center;gap:15px;flex-wrap:wrap;margin-top:12px}
    .re-paste-panel{flex:1 1 250px}.re-paste-content{padding:14px;margin-top:8px;border:1px solid #dce5f0;border-radius:10px;background:#f8faff}
    .re-paste-content label{display:block;font-size:13px;font-weight:700;margin:8px 0 5px}.re-paste-content textarea{max-width:580px;margin-bottom:10px}
    .kt-staff .record-entry .re-summary-section .re-totals{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:10px !important;padding:0 !important}
    .re-summary-item{display:flex;flex-direction:column;gap:4px;padding:12px 14px;background:#f6f9fd;border:1px solid #e1e8f2;border-radius:10px}
    .re-summary-item span{color:#526278;font-size:12px}.re-summary-item strong{font-size:14px;line-height:1.35}
    .re-field-error{display:block;color:#b42318;font-size:12px;font-weight:600;line-height:1.35}
    .record-entry label:has([aria-invalid="true"])>.re-field-caption{color:#b42318}
    .kt-staff .record-entry .re-plan-section{display:grid;gap:14px}
    .kt-staff .record-entry .re-plan-section>h4{margin-bottom:0 !important}
    .re-plan-block{min-width:0;padding:18px 20px;border:1px solid #dce5f0;border-radius:12px;background:#fbfcfe}
    .re-plan-block>h5{margin:0 0 13px;font-size:14px;font-weight:800;color:#243956}
    .re-plan-block>.re-muted{margin:0 0 13px;line-height:1.45}
    .kt-staff .record-entry :is(.re-plan-agreement,.re-plan-initial){grid-template-columns:repeat(3,minmax(0,1fr)) !important;gap:15px 16px}
    .re-plan-section .re-grid label{min-width:0;gap:7px}
    .re-plan-section .re-grid :is(input:not([type="checkbox"]),select){box-sizing:border-box;width:100%;height:44px;min-height:44px;margin:0}
    .re-plan-section .re-grid textarea{box-sizing:border-box;width:100%;min-height:44px;margin:0}
    .re-plan-section .re-unknown-total,.re-plan-notice{margin:14px 0 0;padding:11px 13px;border-radius:9px;font-size:13px;line-height:1.45}
    .re-plan-notice{background:#fff7e9;border:1px solid #edce91;color:#6d4a16}
    .re-plan-section .re-plan-hint{margin:12px 0 0}
    .re-plan-section .re-end-fields .re-grid{max-width:380px}
    .re-plan-section .re-subsection{margin:0;padding:0;border:0}
    .re-plan-section .re-subsection>h5{display:none}
    .re-plan-section .re-receipt-row{padding:12px 14px !important;margin:8px 0 !important}
    .kt-staff .record-entry .re-plan-section .re-payment-grid{grid-template-columns:repeat(4,minmax(0,1fr)) !important;gap:10px 12px}
    .re-receipt-extra{margin-top:10px}
    .re-receipt-extra summary{width:fit-content;color:#1266bf;font-size:13px;font-weight:700;cursor:pointer}
    .re-receipt-extra .re-grid{grid-template-columns:repeat(2,minmax(0,1fr));margin-top:10px}
    .re-plan-section .re-payment-actions{margin-top:14px}
    .re-plan-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}
    .re-plan-summary .re-summary-item{background:#f1f6fd}
    html[data-theme="dark"] .record-entry .re-plan-block,html[data-theme="dark"] .record-entry .re-plan-summary .re-summary-item{background:#1b2a40;border-color:#3b4d64}
    html[data-theme="dark"] .record-entry .re-plan-block>h5{color:#e2e8f0}
    html[data-theme="dark"] .record-entry .re-plan-notice{background:#3b321e;border-color:#80683b;color:#f9e8c4}
    .record-entry{scroll-padding-bottom:110px}.record-entry :is(input,select,textarea,button,summary){scroll-margin-bottom:110px}
    .kt-staff .record-entry{padding-bottom:120px !important}
    .kt-staff .record-entry .re-footer{bottom:env(safe-area-inset-bottom,0px);padding:10px 14px !important}
    .kt-staff .record-entry #re-grand-totals{font-size:13px;font-weight:650;color:#435873}
    html[data-theme="dark"] .record-entry .re-section,html[data-theme="dark"] .record-entry .re-subsection{border-color:#3b4d64}
    html[data-theme="dark"] .record-entry .re-section h4{color:#e2e8f0 !important}
    html[data-theme="dark"] .record-entry :is(.re-row,.re-end-fields,.re-paste-content,.re-summary-item){background:#1b2a40 !important;border-color:#3b4d64 !important}
    html[data-theme="dark"] .record-entry .re-unknown-total{background:#203957;border-color:#3b5c87;color:#d9eaff}
    .re-review{display:grid;gap:16px;margin-top:18px}
    .kt-staff .record-entry .re-review .re-card{margin:0 !important;padding:22px !important}
    .re-review-step{display:inline-block;color:#1266bf;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}
    .re-review-header h2{margin:4px 0 18px;font-size:24px}
    .re-review-identity{display:grid;grid-template-columns:minmax(180px,2fr) repeat(3,minmax(110px,1fr));gap:10px}
    .re-review-identity>div{display:flex;flex-direction:column;gap:3px;padding:12px 14px;border:1px solid #e1e8f2;border-radius:9px;background:#f8faff;min-width:0}
    .re-review-identity span,.re-review-figure span{color:#566781;font-size:12px}.re-review-identity strong{font-size:14px;overflow-wrap:anywhere}
    .re-review-unsaved{display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-top:16px;padding:12px 14px;border:1px solid #efc986;border-radius:9px;background:#fff8e9;color:#67470c;font-size:13px}
    .re-review-unsaved strong{font-size:15px}
    .re-review-visit-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-bottom:15px;border-bottom:1px solid #e1e8f2}
    .re-review-visit-head h3{margin:3px 0;font-size:18px}.re-review-visit-head p{margin:0;color:#566781;font-size:13px}
    .re-review-duplicate-tag{display:inline-block;padding:8px 10px;border-radius:8px;background:#fff1e8;color:#9a471b;font-size:12px;font-weight:700}
    .re-review-visit-body{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.2fr);gap:24px;padding:18px 0}
    .kt-staff .record-entry .re-review-visit h4{margin:0 0 10px !important;font-size:14px !important;color:#243956 !important;text-transform:none;letter-spacing:0}
    .re-review-procedures,.re-review-receipts{list-style:none;margin:0;padding:0}
    .re-review-procedures li{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #e8edf5;font-size:13px}
    .re-review-procedures li:last-child{border-bottom:0}.re-review-procedures small{display:block;color:#566781;margin-top:3px;line-height:1.45}.re-review-procedures li>span:last-child{white-space:nowrap;font-weight:700}
    .re-review-note{margin:12px 0 0;font-size:13px;line-height:1.5;white-space:pre-wrap}
    .re-review-arrangement{margin:0 0 12px;font-size:13px;font-weight:700}
    .re-review-payment-group{padding:13px 0;border-top:1px solid #e8edf5}
    .re-review-payment-group h5{display:flex;align-items:center;justify-content:space-between;margin:0 0 8px;font-size:12px;font-weight:800}
    .re-review-payment-group h5 span{min-width:23px;height:23px;display:inline-grid;place-items:center;border-radius:50%;background:#e9f3ff;color:#1266bf}
    .re-review-payment-group .re-muted{margin:0}
    .re-review-initial,.re-review-receipts li{display:grid;grid-template-columns:minmax(85px,1fr) minmax(95px,auto) minmax(75px,auto);align-items:center;gap:6px 12px;padding:8px 0;font-size:12px}
    .re-review-receipts li{grid-template-columns:24px minmax(85px,1fr) minmax(95px,auto) minmax(75px,auto);border-top:1px solid #edf1f6}
    .re-review-receipts li:first-child{border-top:0}.re-review-receipts strong,.re-review-initial strong{font-size:13px}.re-review-receipts small{grid-column:2/-1;color:#566781}
    .re-review-receipt-number{display:grid;place-items:center;width:21px;height:21px;border-radius:50%;background:#eef3f9;color:#526278;font-size:11px}
    .re-review-visit-summary{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;padding-top:16px;border-top:1px solid #e1e8f2}
    .re-review-figure{display:flex;flex-direction:column;gap:3px;padding:11px 12px;border:1px solid #e1e8f2;border-radius:9px;background:#f8faff}
    .re-review-figure strong{font-size:15px;line-height:1.4}
    .re-review-unknown{display:flex;flex-direction:column;gap:4px;padding:11px 12px;border:1px solid #c8dffa;border-radius:9px;background:#eef6ff;color:#184a80;font-size:13px}
    .re-review-unknown strong{font-size:14px}
    .re-review-warning{padding:18px 20px;border:1px solid #edc68c;border-radius:12px;background:#fff9ee}
    .re-review-warning h3{margin:0 0 7px;font-size:16px}.re-review-warning p,.re-review-warning li{font-size:13px;line-height:1.5}
    .re-review-warning ul{padding-left:20px;margin:10px 0 15px}.re-review-warning li+li{margin-top:7px}
    .re-review-ack{display:flex;align-items:flex-start;gap:9px;font-size:13px;font-weight:700}.re-review-ack input{flex:none;margin-top:2px}
    .re-review-clear{margin:0;padding:11px 14px;border:1px solid #cae8d8;border-radius:9px;background:#f2fbf5;color:#245b3e;font-size:13px}
    .re-review-actions{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;padding:16px 18px;border:1px solid #dce4ef;border-radius:12px;background:#fff}
    .re-review-actions p{max-width:620px;margin:0;color:#435873;font-size:13px;line-height:1.5}
    .re-review-actions>div{display:flex;gap:9px;flex-wrap:wrap}.re-review-actions .btn{min-width:130px}
    .re-review-actions .btn:disabled{opacity:.55;cursor:not-allowed}
    html[data-theme="dark"] .record-entry :is(.re-review-identity>div,.re-review-figure,.re-review-actions){background:#152032;border-color:#3b4d64}
    html[data-theme="dark"] .record-entry :is(.re-review-visit-head,.re-review-visit-summary,.re-review-payment-group,.re-review-procedures li,.re-review-receipts li){border-color:#3b4d64}
    html[data-theme="dark"] .record-entry .re-review-unknown{background:#203957;border-color:#3b5c87;color:#d9eaff}
    html[data-theme="dark"] .record-entry .re-review-unsaved,html[data-theme="dark"] .record-entry .re-review-warning{background:#3b321e;border-color:#80683b;color:#f9e8c4}
    @media(max-width:900px){.re-review-visit-body{grid-template-columns:1fr}.re-review-identity{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:650px){.kt-staff .record-entry .re-review .re-card{padding:16px !important}.re-review-identity{grid-template-columns:repeat(2,minmax(0,1fr))}.re-review-identity>div:first-child{grid-column:1/-1}.re-review-visit-body{gap:12px}.re-review-receipts li{grid-template-columns:22px minmax(0,1fr) auto;gap:4px 8px}.re-review-receipts li>span:nth-last-of-type(1){grid-column:2/-1}.re-review-receipts small{grid-column:2/-1}.re-review-initial{grid-template-columns:minmax(0,1fr) auto}.re-review-initial span{grid-column:1/-1}.re-review-actions{align-items:stretch}.re-review-actions>div{width:100%}.re-review-actions .btn{flex:1 1 45%}}
    @media(max-width:900px){.kt-staff .record-entry .re-plan-overview{grid-template-columns:repeat(2,minmax(0,1fr)) !important}.kt-staff .record-entry .re-plan-agreement{grid-template-columns:repeat(2,minmax(0,1fr)) !important}.kt-staff .record-entry .re-plan-section .re-payment-grid{grid-template-columns:repeat(2,minmax(0,1fr)) !important}}
    @media(max-width:650px){.kt-staff .record-entry .re-visit-card{padding:16px !important}.kt-staff .record-entry .re-procedure-main,.kt-staff .record-entry .re-plan-overview,.kt-staff .record-entry .re-plan-grid{grid-template-columns:1fr !important}.re-section{padding:18px 0}.re-visit-heading .re-actions{width:100%}.kt-staff .record-entry .re-footer{position:sticky !important;bottom:0;display:flex;flex-direction:column;align-items:stretch}.kt-staff .record-entry .re-footer .re-actions{display:flex;margin:0 !important}.kt-staff .record-entry #re-grand-totals{display:none}.kt-staff .record-entry .re-footer .re-actions .btn{flex:1 1 45%}}
    @media(max-width:650px){.re-card{padding:12px}.re-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.re-footer{position:static}}
    @media(max-width:650px){.re-plan-block{padding:16px}.kt-staff .record-entry :is(.re-plan-agreement,.re-plan-initial,.re-plan-section .re-payment-grid,.re-receipt-extra .re-grid){grid-template-columns:1fr !important}.re-plan-summary{grid-template-columns:1fr}}
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
                <input class="form-control" id="re-patient-search" type="search" placeholder="Start typing a patient name or record detail" autocomplete="off" role="combobox" aria-required="true" aria-autocomplete="list" aria-controls="re-patient-results" aria-expanded="false">
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
        <div id="re-editor" tabindex="-1">
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
        <div id="re-review" class="re-review" role="region" aria-labelledby="re-review-heading" hidden></div>
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
<script src="{{ asset('js/staff-record-entry.js') }}?v=13" defer></script>
@endsection
