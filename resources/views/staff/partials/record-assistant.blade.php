@php
    $assistantPatient = match (true) {
        request()->routeIs('staff.patients.show') => request()->route('patient'),
        request()->routeIs('staff.visits.show') => request()->route('visit')?->patient,
        request()->routeIs('staff.payments.show') => request()->route('payment')?->visit?->patient,
        request()->routeIs('staff.installments.show') => request()->route('plan')?->patient,
        default => null,
    };
    if ($assistantPatient?->trashed()) $assistantPatient = null;
@endphp
<div id="staffRecordAssistant" class="record-assistant" data-search-url="{{ route('staff.assistant.patients') }}"
     data-ask-url="{{ route('staff.assistant.ask') }}"
     data-profile-context="{{ $assistantPatient instanceof \App\Models\Patient ? '1' : '0' }}"
     data-patient-id="{{ $assistantPatient instanceof \App\Models\Patient ? $assistantPatient->id : '' }}"
     data-patient-name="{{ $assistantPatient instanceof \App\Models\Patient ? trim($assistantPatient->first_name.' '.$assistantPatient->last_name) : '' }}">
    <button type="button" class="record-assistant__launcher" aria-label="Open staff records assistant"
            aria-expanded="false" aria-controls="staffRecordAssistantPanel">
        <svg aria-hidden="true" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5a7.5 7.5 0 0 1-7.5 7.5H5l1.8-3.6A7.5 7.5 0 1 1 20 11.5Z"/><path d="M9 11.5h7m-7 3h4"/></svg>
    </button>
    <section id="staffRecordAssistantPanel" class="record-assistant__panel" role="region" aria-label="Staff Assistant" hidden>
        <header class="record-assistant__header">
            <div class="record-assistant__heading"><strong>Staff Assistant</strong><span class="record-assistant__badge">Read-only</span></div>
            <button type="button" class="record-assistant__close" aria-label="Close assistant"><span aria-hidden="true">×</span></button>
        </header>
        <div class="record-assistant__patient">
            <div class="record-assistant__context"><span class="record-assistant__context-icon" aria-hidden="true">●</span><div><small>Looking at</small><div class="record-assistant__selected" aria-live="polite"></div></div></div>
            <div class="record-assistant__patient-actions">
                <button type="button" class="record-assistant__switch" aria-expanded="false" aria-controls="recordAssistantSearchPanel">Change patient</button>
                <button type="button" class="record-assistant__clear">All records</button>
            </div>
            <div id="recordAssistantSearchPanel" class="record-assistant__search-wrap" hidden>
                <label for="recordAssistantSearch">Find a patient by name</label>
                <input id="recordAssistantSearch" type="search" autocomplete="off" placeholder="Type at least 2 letters"
                       value="{{ $assistantPatient instanceof \App\Models\Patient ? trim($assistantPatient->first_name.' '.$assistantPatient->last_name) : '' }}">
                <div class="record-assistant__candidates" aria-label="Matching patients"></div>
                <p class="record-assistant__search-status" role="status"></p>
            </div>
        </div>
        <div class="record-assistant__body">
            <div class="record-assistant__empty">
                <div class="record-assistant__empty-icon" aria-hidden="true">✦</div>
                <h2>How can I help?</h2>
                <p class="record-assistant__capabilities">Ask about patient balances, visits, receipts, or installment dues. For the clinic, ask about outstanding balances, due installments, receipts today or this week, or visits in a date range. Verify answers using their source links.</p>
                <div class="record-assistant__suggestions" aria-label="Patient example questions">
                    <span>For this patient</span>
                    <button type="button" data-patient-question="What is the remaining balance?">Remaining balance</button>
                    <button type="button" data-patient-question="When was the last visit and what treatment was recorded?">Last visit</button>
                    <button type="button" data-patient-question="Show payment history">Payment history</button>
                    <button type="button" data-patient-question="Which installment payments are still due?">Installment dues</button>
                </div>
                <div class="record-assistant__clinic-suggestions" aria-label="Clinic example questions">
                    <span>Across the clinic</span>
                    <button type="button" data-clinic-question="Which patients have outstanding balances?">Outstanding balances</button>
                    <button type="button" data-clinic-question="Which installment payments are due or overdue?">Due installments</button>
                    <button type="button" data-clinic-question="What payments were received today?">Received today</button>
                    <button type="button" data-clinic-question="What payments were received this week?">Received this week</button>
                    <button type="button" data-clinic-question="How many visits occurred in the selected date range?">Count visits</button>
                </div>
            </div>
            <div class="record-assistant__messages" role="log" aria-live="polite"></div>
        </div>
        <div class="record-assistant__dates" hidden>
            <span>Visit date range</span>
            <label>From <input type="date" name="date_from" form="recordAssistantForm" aria-label="Visit count start date"></label>
            <label>To <input type="date" name="date_to" form="recordAssistantForm" aria-label="Visit count end date"></label>
        </div>
        <p class="record-assistant__status" role="status" aria-live="polite"></p>
        <form id="recordAssistantForm" class="record-assistant__form" data-no-loader>
            <input type="text" name="question" maxlength="500" required placeholder="Ask about records" aria-label="Question">
            <button type="submit" aria-label="Ask question"><span aria-hidden="true">↑</span></button>
        </form>
    </section>
</div>
<link rel="stylesheet" href="{{ asset('css/staff-record-assistant.css') }}?v=7">
<script src="{{ asset('js/staff-record-assistant.js') }}?v=7" defer></script>
