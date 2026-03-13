@extends('layouts.kt-public')

@section('title', ($service->name ?? 'Service') . ' - Krys & Tell Dental Center')

@section('content')
<section class="kt-service-page">
    <div class="kt-page-shell">
        @php
            $bookUrl = url('/book/' . $service->id);
            $loginThenBackToBook = route('userlogin', ['redirect' => $bookUrl]);
        @endphp

        <div class="kt-service-page__layout">
            <div class="kt-service-page__main kt-reveal-left">
                <div class="kt-label">Service Details</div>
                <h1 class="kt-section-title">{{ $service->name }}<br><em>Personalized Care Plan</em></h1>
                <p class="kt-section-body">
                    {{ $service->description ?? 'Professional treatment tailored to your comfort and long-term dental health.' }}
                </p>

                <div class="kt-service-page__stats">
                    <article class="kt-service-page__stat">
                        <h3>Estimated Duration</h3>
                        <p>{{ $service->duration_minutes ? $service->duration_minutes . ' minutes' : 'Depends on the case' }}</p>
                    </article>

                    <article class="kt-service-page__stat">
                        <h3>Starting Price</h3>
                        <p>
                            @if(isset($service->base_price))
                                PHP {{ number_format((float) $service->base_price, 2) }}
                            @else
                                Available upon consultation
                            @endif
                        </p>
                        @if(!empty($service->allow_custom_price))
                            <small>Final cost may vary based on treatment complexity.</small>
                        @endif
                    </article>
                </div>

                <div class="kt-service-page__actions">
                    <a href="{{ auth()->check() ? $bookUrl : $loginThenBackToBook }}" class="kt-btn-primary"><span>Book This Service</span></a>
                    <a href="{{ route('public.services.index') }}" class="kt-btn-ghost">Back to Services ></a>
                </div>
            </div>

            <aside class="kt-service-page__aside kt-reveal-right">
                <div class="kt-service-page__photo">
                    <img src="{{ asset('images/pic2.jpg') }}" alt="" loading="lazy">
                </div>

                <div class="kt-service-page__note">
                    <h3>Need Help Deciding?</h3>
                    <p>Our team can guide you to the right option based on your goals and clinical needs.</p>
                    <a href="{{ route('public.contact') }}" class="kt-text-link">Contact the Clinic ></a>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection
