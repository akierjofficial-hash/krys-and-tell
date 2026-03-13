@extends('layouts.kt-public')
@section('title', 'My Installments - Krys & Tell Dental Center')

@section('content')
@php
    use Carbon\Carbon;

    $user = auth()->user();
    $email = $user?->email ?? null;
@endphp

<section class="kt-installments-page">
    <div class="kt-page-shell">
        <a href="{{ route('profile.show') }}" class="kt-back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round">
                <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
            </svg>
            Back to Profile
        </a>

        <div class="kt-installments-page__head kt-reveal">
            <div class="kt-label">Payment Plans</div>
            <h1 class="kt-section-title">My Installment<br><em>Plans</em></h1>
            <p class="kt-section-body">
                Review your active plans, current balance, and payment status in one place.
            </p>
        </div>

        @if($plans->isEmpty())
            <article class="kt-installments-empty kt-reveal">
                <h3>No installment plans found.</h3>
                <p>
                    If you already have a plan, make sure your clinic record email matches your account:
                    <strong>{{ $email ?: '-' }}</strong>.
                </p>
            </article>
        @else
            <div class="kt-installments-grid">
                @foreach($plans as $plan)
                    @php
                        $patient = $plan->patient ?? $plan->visit?->patient ?? null;
                        $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
                        if ($patientName === '') {
                            $patientName = $user?->name ?? 'N/A';
                        }

                        $serviceName = $plan->service?->name ?? '-';
                        $startDate = $plan->start_date ? Carbon::parse($plan->start_date) : null;
                        $months = (int) ($plan->months ?? 0);
                        $isOpen = (bool) ($plan->is_open_contract ?? false);

                        $remaining = $plan->balance;
                        if ($remaining === null) {
                            $totalCost = (float) ($plan->total_cost ?? 0);
                            $paymentsTotal = (float) ($plan->payments?->sum('amount') ?? 0);
                            $downpayment = (float) ($plan->downpayment ?? 0);
                            $remaining = max(0, $totalCost - ($paymentsTotal + $downpayment));
                        }

                        $status = strtoupper(trim((string) ($plan->status ?? '')));
                        $statusLabel = $status !== '' ? $status : ((float) $remaining <= 0 ? 'FULLY PAID' : 'PENDING');
                        $statusClass = 'kt-installments-badge--pending';
                        if (str_contains($statusLabel, 'FULL') || str_contains($statusLabel, 'PAID') || (float) $remaining <= 0) {
                            $statusClass = 'kt-installments-badge--paid';
                        }
                        if (str_contains($statusLabel, 'COMPLETE')) {
                            $statusClass = 'kt-installments-badge--info';
                        }
                    @endphp

                    <a href="{{ route('public.installments.show', $plan) }}" class="kt-installments-card kt-reveal">
                        <div class="kt-installments-card__top">
                            <div>
                                <h3>{{ $serviceName !== '-' ? $serviceName : 'Installment Plan' }}</h3>
                                <p>
                                    {{ $patientName }}
                                    @if($startDate)
                                        <span>Start {{ $startDate->format('M d, Y') }}</span>
                                    @endif
                                </p>
                            </div>
                            <span class="kt-installments-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                        </div>

                        <div class="kt-installments-card__bottom">
                            <div>
                                <small>Remaining Balance</small>
                                <strong>PHP {{ number_format((float) $remaining, 2) }}</strong>
                            </div>
                            <div class="kt-installments-card__term">
                                {{ $isOpen ? 'Open Contract' : ($months > 0 ? ($months . ' month(s)') : '-') }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
