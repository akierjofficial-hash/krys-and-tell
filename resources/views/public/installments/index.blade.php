@extends('layouts.public')
@section('title', 'My Installments')

@section('content')
@php
    use Carbon\Carbon;

    $user = auth()->user();
    $email = $user?->email ?? null;
@endphp

<section class="section section-soft">
    <div class="container">

        <div class="d-flex align-items-end justify-content-between flex-wrap gap-3 mb-3">
            <div>
                <h2 class="sec-title mb-1">My Installment Plans</h2>
                <p class="sec-sub mb-0">View your plan balance and payment history (read-only).</p>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('profile.show') }}" class="btn kt-btn kt-btn-outline">
                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Profile
                </a>
            </div>
        </div>

        @if($plans->isEmpty())
            <div class="card-soft p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="icon-badge" style="width:46px;height:46px;border-radius:18px;">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <div style="font-weight:950; font-size:1.05rem;">No installment plans found.</div>
                        <div class="text-muted-2" style="font-weight:650; line-height:1.6;">
                            If you already have an installment plan, make sure your clinic record email matches your account email
                            <strong>{{ $email ?: '—' }}</strong>.
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row g-3">
                @foreach($plans as $plan)
                    @php
                        $patient = $plan->patient ?? $plan->visit?->patient ?? null;
                        $patientName = trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: ($user?->name ?? 'N/A');

                        $serviceName = $plan->service?->name ?? '—';

                        $startDate = $plan->start_date ? Carbon::parse($plan->start_date) : null;
                        $months = (int)($plan->months ?? 0);
                        $isOpen = (bool)($plan->is_open_contract ?? false);

                        // Prefer stored balance; fallback to computed if missing
                        $remaining = $plan->balance;
                        if ($remaining === null) {
                            $totalCost = (float)($plan->total_cost ?? 0);
                            $paymentsTotal = (float)($plan->payments?->sum('amount') ?? 0);
                            $down = (float)($plan->downpayment ?? 0);
                            $remaining = max(0, $totalCost - ($paymentsTotal + $down));
                        }

                        $status = strtoupper(trim((string)($plan->status ?? '')));
                        $badgeClass = 'bg-warning-subtle text-warning-emphasis';
                        if (str_contains($status, 'FULL') || str_contains($status, 'PAID') || (float)$remaining <= 0) {
                            $badgeClass = 'bg-success-subtle text-success-emphasis';
                        }
                        if (str_contains($status, 'COMPLETE')) {
                            $badgeClass = 'bg-info-subtle text-info-emphasis';
                        }
                    @endphp

                    <div class="col-12 col-lg-6">
                        <a href="{{ route('public.installments.show', $plan) }}" class="text-decoration-none">
                            <div class="card-soft p-4" style="transition: transform .15s ease, box-shadow .15s ease;">
                                <div class="d-flex align-items-start justify-content-between gap-3">
                                    <div style="min-width:0;">
                                        <div style="font-weight:950; font-size:1.05rem; line-height:1.2;">
                                            {{ $serviceName !== '—' ? $serviceName : 'Installment Plan' }}
                                        </div>
                                        <div class="text-muted-2" style="font-weight:650;">
                                            {{ $patientName }}
                                            @if($startDate)
                                                • Start {{ $startDate->format('M d, Y') }}
                                            @endif
                                        </div>
                                    </div>

                                    <span class="badge {{ $badgeClass }}" style="font-weight:900; border-radius:999px;">
                                        {{ $status !== '' ? $status : ((float)$remaining <= 0 ? 'FULLY PAID' : 'PENDING') }}
                                    </span>
                                </div>

                                <hr style="border-color: rgba(17,17,17,.10);">

                                <div class="d-flex align-items-end justify-content-between flex-wrap gap-2">
                                    <div>
                                        <div class="text-muted-2" style="font-weight:800; font-size:.85rem;">Remaining Balance</div>
                                        <div style="font-weight:950; font-size:1.35rem; letter-spacing:-.02em;">
                                            ₱{{ number_format((float)$remaining, 2) }}
                                        </div>
                                    </div>
                                    <div class="text-muted-2" style="font-weight:800;">
                                        {{ $isOpen ? 'Open Contract' : ($months > 0 ? ($months.' month(s)') : '—') }}
                                        <span class="ms-2"><i class="fa-solid fa-chevron-right"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</section>
@endsection
