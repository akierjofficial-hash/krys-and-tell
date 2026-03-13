@extends('layouts.kt-public')

@section('title', 'Privacy Policy - Krys & Tell Dental Center')

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
            <h1 class="kt-section-title">Privacy <em>Policy</em></h1>
            <p class="kt-section-body">
                Last updated: {{ now()->format('F j, Y') }}. This page explains how Krys &amp; Tell Dental Center
                collects, uses, and protects your information when you use our website and services.
            </p>
        </div>

        <div class="kt-legal-page__grid">
            <article class="kt-legal-card kt-reveal">
                <h2>Information We Collect</h2>
                <p>We may collect identity, contact, booking, and communication details that you provide in forms.</p>
                <ul>
                    <li>Name, email address, and phone number</li>
                    <li>Appointment and service preferences</li>
                    <li>Messages you send through our contact form</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>How We Use Information</h2>
                <p>We use your data only for legitimate clinic operations and patient support.</p>
                <ul>
                    <li>To manage appointments and service requests</li>
                    <li>To communicate updates, confirmations, and reminders</li>
                    <li>To improve clinic operations and website experience</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Sharing and Disclosure</h2>
                <p>We do not sell personal data. We share information only when required for operations or law.</p>
                <ul>
                    <li>Authorized clinic staff for patient care and administration</li>
                    <li>Service providers that support secure system operations</li>
                    <li>Government or legal authorities when legally required</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Data Retention and Security</h2>
                <p>We keep records only as needed and use reasonable safeguards to protect stored data.</p>
                <ul>
                    <li>Access is restricted to authorized personnel</li>
                    <li>Systems are monitored and updated for security</li>
                    <li>Records are retained based on operational and legal requirements</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Your Rights</h2>
                <p>You may request access, correction, or updates to your information, subject to applicable law.</p>
                <ul>
                    <li>Request a copy of your stored personal information</li>
                    <li>Request correction of inaccurate or outdated details</li>
                    <li>Ask questions about how your data is processed</li>
                </ul>
            </article>

            <article class="kt-legal-card kt-reveal">
                <h2>Contact</h2>
                <p>For privacy questions, contact us through our official channels.</p>
                <ul>
                    <li>Email: <a href="mailto:krysandt@gmail.com">krysandt@gmail.com</a></li>
                    <li>Phone: <a href="tel:+639772443595">0977 244 3595</a></li>
                    <li>Address: CT Building, Jose Romero Road, Bagacay, Dumaguete City</li>
                </ul>
            </article>
        </div>
    </div>
</section>
@endsection
