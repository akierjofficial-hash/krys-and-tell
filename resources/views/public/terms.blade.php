@extends('layouts.kt-public')

@section('title', 'Terms of Service - Krys & Tell Dental Center')

@section('content')
<section class="kt-legal-page">
    <div class="kt-page-shell">
        <a href="{{ route('public.home') }}" class="kt-back-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
            </svg>
            Back to Home
        </a>

        <div class="kt-legal-page__head kt-reveal">
            <div class="kt-label">Legal</div>
            <h1 class="kt-section-title">Terms of <em>Service</em></h1>
            <p class="kt-section-body">
                Last updated: {{ now()->format('F j, Y') }}. By using this website and our online booking features,
                you agree to the terms below.
            </p>
        </div>

        <div class="kt-legal-page__grid">
            <article class="kt-legal-card kt-reveal">
                <h2>Use of Website</h2>
                <p>You agree to use this site only for lawful purposes related to clinic information and services.</p>
                <ul>
                    <li>Do not misuse forms, accounts, or booking tools</li>
                    <li>Do not submit false, misleading, or harmful content</li>
                    <li>Respect system availability and fair use limits</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Account Responsibilities</h2>
                <p>You are responsible for information submitted through your account.</p>
                <ul>
                    <li>Keep login access secure</li>
                    <li>Provide accurate profile and booking details</li>
                    <li>Notify us if you suspect unauthorized access</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Appointments and Scheduling</h2>
                <p>Appointment slots are subject to availability and clinic confirmation.</p>
                <ul>
                    <li>Requested schedules may be adjusted based on clinical availability</li>
                    <li>Late arrivals may require rescheduling</li>
                    <li>Cancellations or changes should be made as early as possible</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Medical Disclaimer</h2>
                <p>Website content is for general information and does not replace professional diagnosis or treatment.</p>
                <ul>
                    <li>For urgent concerns, contact the clinic directly</li>
                    <li>In emergencies, seek immediate emergency care</li>
                    <li>Treatment decisions are made during clinical consultation</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Intellectual Property</h2>
                <p>Content, design, branding, and media on this site are owned or licensed by Krys &amp; Tell.</p>
                <ul>
                    <li>No unauthorized copying or redistribution</li>
                    <li>No use of branding without prior written permission</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Changes to Terms</h2>
                <p>We may update these terms from time to time. Updates become effective when posted on this page.</p>
                <ul>
                    <li>Continued use means you accept updated terms</li>
                    <li>If you disagree, discontinue use of online services</li>
                </ul>
            </article>
        </div>
    </div>
</section>
@endsection
