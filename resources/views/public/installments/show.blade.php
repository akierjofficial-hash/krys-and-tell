@extends('layouts.kt-public')
@section('title', 'Installment Plan - Krys & Tell Dental Center')

@section('content')
@php
    use Carbon\Carbon;

    $patient = $plan->patient ?? $plan->visit?->patient ?? null;
    $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
    if ($patientName === '') {
        $patientName = auth()->user()->name ?? 'N/A';
    }

    $serviceName = $plan->service?->name ?? '-';
    $startDate = $plan->start_date ? Carbon::parse($plan->start_date) : null;
    $months = (int) ($plan->months ?? 0);
    $isOpen = (bool) ($plan->is_open_contract ?? false);

    $totalCost = (float) ($plan->total_cost ?? 0);
    $downpayment = (float) ($plan->downpayment ?? 0);
    $payments = $plan->payments ?? collect();

    $baseVisit = $plan->visit ?? null;
    $baseDentist = trim((string) ($baseVisit?->dentist_name ?? ''));
    if ($baseDentist === '') {
        $baseDentist = trim((string) ($baseVisit?->doctor?->name ?? ''));
    }
    if ($baseDentist === '') {
        $baseDentist = '-';
    }

    $planStartStr = $startDate ? $startDate->toDateString() : null;

    $dpPayment = $payments->first(function ($p) use ($downpayment, $planStartStr) {
        $monthNumber = (int) ($p->month_number ?? -1);
        $notes = strtolower((string) ($p->notes ?? ''));

        if ($monthNumber === 0) {
            return true;
        }

        if ($monthNumber === 1) {
            if (str_contains($notes, 'downpayment')) {
                return true;
            }

            $amount = (float) ($p->amount ?? 0);
            $paymentDate = $p->payment_date ? Carbon::parse($p->payment_date)->toDateString() : null;
            if ($downpayment > 0 && abs($amount - $downpayment) < 0.01 && $planStartStr && $paymentDate === $planStartStr) {
                return true;
            }
        }

        return false;
    });

    $hasMonth0 = $payments->contains(fn ($p) => (int) ($p->month_number ?? -1) === 0);
    $dpIsLegacyMonth1 = (!$hasMonth0 && $dpPayment && (int) ($dpPayment->month_number ?? -1) === 1);
    $shift = $dpIsLegacyMonth1 ? 1 : 0;

    $paymentsTotal = (float) $payments->sum('amount');
    $hasDpRecord = (bool) $dpPayment;
    $paidAmount = $paymentsTotal + ($hasDpRecord ? 0 : $downpayment);
    $remaining = max(0, $totalCost - $paidAmount);

    $status = strtoupper(trim((string) ($plan->status ?? 'PARTIALLY PAID')));
    $isCompleted = ($status === 'COMPLETED');
    $isPaid = $remaining <= 0;

    $refNo = 'INST-' . str_pad((string) ($plan->id ?? 0), 6, '0', STR_PAD_LEFT);

    $showDpRow = ($downpayment > 0) || (bool) $dpPayment;
    $dpAmount = $dpPayment?->amount ?? ($downpayment > 0 ? $downpayment : null);
    $dpDate = $dpPayment?->payment_date ? Carbon::parse($dpPayment->payment_date) : ($startDate ? $startDate->copy() : null);
    $dpMethod = $dpPayment?->method ?? '-';
    $dpNotes = trim((string) ($dpPayment?->notes ?? 'Downpayment'));
    if ($dpNotes === '') {
        $dpNotes = 'Downpayment';
    }

    $dpDentist = '-';
    if ($dpPayment) {
        $dpDentist = trim((string) ($dpPayment?->visit?->dentist_name ?? ''));
        if ($dpDentist === '') {
            $dpDentist = trim((string) ($dpPayment?->visit?->doctor?->name ?? ''));
        }
        if ($dpDentist === '') {
            $dpDentist = $baseDentist;
        }
    } else {
        $dpDentist = $baseDentist;
    }

    $paymentsByMonth = $payments
        ->filter(fn ($p) => (int) ($p->month_number ?? -1) >= 1)
        ->keyBy('month_number');

    $openPayments = $payments
        ->filter(fn ($p) => (int) ($p->month_number ?? -1) >= (1 + $shift))
        ->sortBy(fn ($p) => (int) ($p->month_number ?? 0))
        ->values();

    $colLabel = $isOpen ? 'Payment' : 'Month';
    $statusLabel = $isCompleted ? 'COMPLETED' : ($isPaid ? 'FULLY PAID' : ($status !== '' ? $status : 'PENDING'));
    $statusClass = $isCompleted ? 'kt-installments-badge--info' : ($isPaid ? 'kt-installments-badge--paid' : 'kt-installments-badge--pending');
@endphp

<section class="kt-installments-page">
    <div class="kt-page-shell">
        <a href="{{ route('public.installments.index') }}" class="kt-back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
            </svg>
            Back to Installments
        </a>

        <div class="kt-installments-page__head kt-reveal">
            <div class="kt-label">Installment Plan</div>
            <h1 class="kt-section-title">Plan Details<br><em>{{ $refNo }}</em></h1>
            <p class="kt-section-body">
                Review your schedule and payment history. Contact the clinic if any entry appears incorrect.
            </p>
        </div>

        <article class="kt-installment-show kt-reveal">
            <div class="kt-installment-show__top">
                <div class="kt-installment-show__ref">
                    <strong>{{ $refNo }}</strong>
                    <span class="kt-installments-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    @if($isOpen)
                        <span class="kt-installments-badge kt-installments-badge--info">OPEN CONTRACT</span>
                    @endif
                </div>
                <div class="kt-installment-show__start">
                    Start: <strong>{{ $startDate ? $startDate->format('M d, Y') : '-' }}</strong>
                </div>
            </div>

            <div class="kt-installment-show__body">
                <div class="kt-installment-show__summary-grid">
                    <div class="kt-installment-panel">
                        <h3>Details</h3>
                        <dl>
                            <dt>Patient</dt>
                            <dd>{{ $patientName }}</dd>

                            <dt>Contact</dt>
                            <dd>{{ $patient?->contact_number ?: '-' }}</dd>

                            <dt>Treatment</dt>
                            <dd>{{ $serviceName }}</dd>

                            <dt>Term</dt>
                            <dd>{{ $isOpen ? 'Open Contract (Unlimited)' : ($months . ' month(s)') }}</dd>

                            <dt>Primary Dentist</dt>
                            <dd>{{ $baseDentist }}</dd>
                        </dl>
                    </div>

                    <div class="kt-installment-panel">
                        <h3>Summary</h3>
                        <div class="kt-installment-show__remaining">PHP {{ number_format($remaining, 2) }}</div>
                        <p>Remaining Balance</p>
                        <ul>
                            <li><span>Total</span><strong>PHP {{ number_format($totalCost, 2) }}</strong></li>
                            <li><span>Downpayment</span><strong>PHP {{ number_format($downpayment, 2) }}</strong></li>
                            <li><span>Paid</span><strong>PHP {{ number_format($paidAmount, 2) }}</strong></li>
                        </ul>
                    </div>
                </div>

                <div class="kt-installment-table-wrap">
                    <table class="kt-installment-table">
                        <thead>
                            <tr>
                                <th>{{ $colLabel }}</th>
                                <th>Date</th>
                                <th>Notes</th>
                                <th>Dentist</th>
                                <th>Method</th>
                                <th class="is-right">Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($showDpRow)
                                <tr>
                                    <td data-label="{{ $colLabel }}">DP</td>
                                    <td data-label="Date">{{ $dpDate ? $dpDate->format('M d, Y') : '-' }}</td>
                                    <td data-label="Notes">{{ $dpNotes }}</td>
                                    <td data-label="Dentist">{{ $dpDentist }}</td>
                                    <td data-label="Method">{{ $dpMethod ?: '-' }}</td>
                                    <td data-label="Amount" class="is-right">{{ $dpAmount !== null ? ('PHP ' . number_format((float) $dpAmount, 2)) : '-' }}</td>
                                    <td data-label="Status">
                                        <span class="kt-installments-badge {{ ($dpAmount !== null && (float) $dpAmount > 0) ? 'kt-installments-badge--paid' : 'kt-installments-badge--pending' }}">
                                            {{ ($dpAmount !== null && (float) $dpAmount > 0) ? 'PAID' : 'PENDING' }}
                                        </span>
                                    </td>
                                </tr>
                            @endif

                            @if($isOpen)
                                @if($openPayments->isEmpty())
                                    <tr class="kt-installment-table__empty">
                                        <td colspan="7">No payments yet (besides downpayment).</td>
                                    </tr>
                                @else
                                    @foreach($openPayments as $p)
                                        @php
                                            $uiNo = (int) ($p->month_number ?? 0) - $shift;
                                            $paymentDate = $p->payment_date ? Carbon::parse($p->payment_date) : null;

                                            $notes = trim((string) ($p->notes ?? ''));
                                            if ($notes === '' && $p->visit_id) {
                                                $notes = 'Visit #' . $p->visit_id;
                                            }

                                            $dentist = trim((string) ($p->visit?->dentist_name ?? ''));
                                            if ($dentist === '') {
                                                $dentist = trim((string) ($p->visit?->doctor?->name ?? ''));
                                            }
                                            if ($dentist === '') {
                                                $dentist = '-';
                                            }
                                        @endphp

                                        <tr>
                                            <td data-label="Payment">Payment #{{ $uiNo }}</td>
                                            <td data-label="Date">{{ $paymentDate ? $paymentDate->format('M d, Y') : '-' }}</td>
                                            <td data-label="Notes">{{ $notes !== '' ? $notes : '-' }}</td>
                                            <td data-label="Dentist">{{ $dentist }}</td>
                                            <td data-label="Method">{{ $p->method ?? '-' }}</td>
                                            <td data-label="Amount" class="is-right">{{ $p->amount !== null ? ('PHP ' . number_format((float) $p->amount, 2)) : '-' }}</td>
                                            <td data-label="Status"><span class="kt-installments-badge kt-installments-badge--paid">PAID</span></td>
                                        </tr>
                                    @endforeach
                                @endif
                            @else
                                @php
                                    $uiMonths = max(0, $months);
                                    $hasDpForDue = $showDpRow;
                                @endphp

                                @if($uiMonths > 0)
                                    @for($i = 1; $i <= $uiMonths; $i++)
                                        @php
                                            $dbMonth = $i + $shift;
                                            $pay = $paymentsByMonth->get($dbMonth);

                                            $dueDate = $startDate
                                                ? $startDate->copy()->addMonths(($i - 1) + ($hasDpForDue ? 1 : 0))
                                                : null;

                                            $paidDate = $pay?->payment_date ? Carbon::parse($pay->payment_date) : null;
                                            $showDate = $paidDate ?? $dueDate;

                                            $amount = $pay?->amount ?? null;

                                            $notes = trim((string) ($pay?->notes ?? ''));
                                            if ($notes === '' && $pay?->visit_id) {
                                                $notes = 'Visit #' . $pay->visit_id;
                                            }

                                            $rowPaid = (bool) $pay;

                                            $dentist = '-';
                                            if ($pay) {
                                                $dentist = trim((string) ($pay->visit?->dentist_name ?? ''));
                                                if ($dentist === '') {
                                                    $dentist = trim((string) ($pay->visit?->doctor?->name ?? ''));
                                                }
                                                if ($dentist === '') {
                                                    $dentist = '-';
                                                }
                                            }
                                        @endphp

                                        <tr>
                                            <td data-label="Month">{{ $i }}</td>
                                            <td data-label="Date">{{ $showDate ? $showDate->format('M d, Y') : '-' }}</td>
                                            <td data-label="Notes">{{ $notes !== '' ? $notes : '-' }}</td>
                                            <td data-label="Dentist">{{ $dentist }}</td>
                                            <td data-label="Method">{{ $pay?->method ?? '-' }}</td>
                                            <td data-label="Amount" class="is-right">{{ $amount !== null ? ('PHP ' . number_format((float) $amount, 2)) : '-' }}</td>
                                            <td data-label="Status">
                                                <span class="kt-installments-badge {{ $rowPaid ? 'kt-installments-badge--paid' : 'kt-installments-badge--pending' }}">
                                                    {{ $rowPaid ? 'PAID' : 'PENDING' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endfor
                                @else
                                    <tr class="kt-installment-table__empty">
                                        <td colspan="7">No monthly installments configured.</td>
                                    </tr>
                                @endif
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="kt-installment-note">
                    This page is view-only. If you need corrections or an official receipt, please contact the clinic.
                </div>
            </div>
        </article>
    </div>
</section>
@endsection
