@extends('layouts.kt-public')

@section('title', 'About - Krys & Tell Dental Center')

@section('content')
<section class="kt-about-page">
    <div class="kt-page-shell">
        <div class="kt-about-page__hero">
            <div class="kt-about-page__intro kt-reveal-left">
                <div class="kt-label">About Krys &amp; Tell</div>
                <h1 class="kt-section-title">Gentle Dentistry,<br><em>Modern Care</em></h1>
                <p class="kt-section-body">
                    We keep every visit calm, clear, and comfortable. Our team focuses on careful treatment,
                    honest guidance, and long-term dental health for every patient.
                </p>

                <div class="kt-about-page__points">
                    <article class="kt-about-page__point kt-reveal">
                        <h3>Comfort First</h3>
                        <p>Relaxed clinic flow, compassionate care, and clear expectations from start to finish.</p>
                    </article>
                    <article class="kt-about-page__point kt-reveal">
                        <h3>Clinical Precision</h3>
                        <p>Modern tools and proven procedures to keep treatments effective and predictable.</p>
                    </article>
                    <article class="kt-about-page__point kt-reveal">
                        <h3>Trusted Team</h3>
                        <p>A patient-first team committed to honest recommendations and long-term results.</p>
                    </article>
                </div>

                <div class="kt-about-page__actions">
                    <a href="{{ route('public.services.index') }}" class="kt-btn-primary"><span>Explore Services</span></a>
                    <a href="{{ route('public.contact') }}" class="kt-btn-ghost">Contact Us ></a>
                </div>
            </div>

            <div class="kt-about-page__visual kt-reveal-right">
                <div class="kt-about-page__hero-photo">
                    <img src="{{ asset('images/pic6.jpg') }}" alt="" loading="lazy">
                </div>
                <div class="kt-about-page__hero-chip">
                    <strong>7+</strong>
                    <span>Years Serving Smiles</span>
                </div>
            </div>
        </div>

        <div class="kt-about-page__team">
            <div class="kt-about-page__team-head kt-reveal">
                <div class="kt-label">Our Team</div>
                <h2 class="kt-section-title">Skilled Hands,<br><em>Warm Approach</em></h2>
                <p class="kt-section-body">Friendly professionals dedicated to safe, consistent, and compassionate care.</p>
            </div>

            <div class="kt-about-page__team-grid">
                <div class="kt-about-page__team-card kt-reveal">
                    <img src="{{ asset('images/staffimg1.jpg') }}" alt="" loading="lazy">
                </div>
                <div class="kt-about-page__team-card kt-reveal">
                    <img src="{{ asset('images/staffimg2.jpg') }}" alt="" loading="lazy">
                </div>
                <div class="kt-about-page__team-card kt-reveal">
                    <img src="{{ asset('images/staffimg3.jpg') }}" alt="" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
