@extends('layouts.kt-public')

@section('title', 'Services - Krys & Tell Dental Center')

@section('content')
<section class="kt-services-page">
    <div class="kt-page-shell">
        <div class="kt-services-page__head kt-reveal">
            <div class="kt-label">Our Services</div>
            <h1 class="kt-section-title">Comprehensive Treatments<br><em>For Every Smile</em></h1>
            <p class="kt-section-body">
                Browse our services and choose the treatment that fits your needs. Booking remains simple,
                with clear schedules and guided support from our team.
            </p>
        </div>

        <div class="kt-services-page__grid">
            @forelse($services as $service)
                @php
                    $bookUrl = url('/book/' . $service->id);
                    $loginThenBackToBook = route('userlogin', ['redirect' => $bookUrl]);
                    $duration = $service->duration_minutes;
                    $isWalkIn = ($duration === null || $duration === '' || (is_numeric($duration) && (int) $duration > 0 && (int) $duration <= 5));
                @endphp

                <article class="kt-services-page__card kt-reveal">
                    <div class="kt-services-page__meta">
                        <span class="kt-services-page__badge">{{ $isWalkIn ? 'Walk-in' : 'Scheduled' }}</span>
                        @if(!empty($service->duration_minutes))
                            <span class="kt-services-page__time">{{ $service->duration_minutes }} mins</span>
                        @endif
                    </div>

                    <h2 class="kt-services-page__name">{{ $service->name }}</h2>
                    <p class="kt-services-page__desc">
                        {{ \Illuminate\Support\Str::limit($service->description ?? 'Professional dental care tailored to your comfort and goals.', 150) }}
                    </p>

                    <div class="kt-services-page__actions">
                        <a href="{{ route('public.services.show', $service) }}" class="kt-btn-ghost">View Details ></a>
                        <a href="{{ auth()->check() ? $bookUrl : $loginThenBackToBook }}" class="kt-btn-primary"><span>Book Service</span></a>
                    </div>
                </article>
            @empty
                <article class="kt-services-page__empty">
                    <h2>No Services Yet</h2>
                    <p>Services will appear here once added in the system.</p>
                </article>
            @endforelse
        </div>
    </div>
</section>
@endsection
