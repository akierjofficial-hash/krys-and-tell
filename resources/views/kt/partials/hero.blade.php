@php
$avgRating = (float) ($heroStats['average_rating'] ?? 0);
$avgRatingDisplay = number_format(max(0, $avgRating), 1);
$happySmiles = (string) ($heroStats['happy_smiles'] ?? '0+');

$patientCount = (int) ($heroStats['patient_count'] ?? 0);
$years = (int) ($heroStats['years'] ?? 1);
$satisfaction = (int) ($heroStats['satisfaction'] ?? 0);

$serviceBarItems = collect($services ?? [])->values()->map(function ($s) {
if (is_array($s)) {
return trim((string) ($s['name'] ?? ''));
}
return trim((string) ($s->name ?? ''));
})->filter()->take(6)->values();
@endphp

<section class="kt-hero" id="hero">
    <div class="kt-hero__photo">
        <img src="{{ asset('images/pic1.jpg') }}" alt="" class="kt-hero__photo-img" loading="eager">
        <div class="kt-hero__photo-overlay"></div>
        <div class="kt-hero__year-tag">Est. 2022</div>
        <div class="kt-hero__rating-badge">
            <div class="kt-hero__rating-num">{{ $avgRatingDisplay }}<span>*</span></div>
            <div class="kt-hero__rating-text">
                <strong>Patient Rating</strong>
                <em>{{ $happySmiles }} happy smiles</em>
            </div>
        </div>
    </div>

    <div class="kt-hero__content">
        <div class="kt-hero__deco-ring"></div>

        <div class="kt-hero__eyebrow">
            <span class="kt-hero__eyebrow-line"></span>
            <span class="kt-hero__eyebrow-text">Dental Care</span>
        </div>

        <h1 class="kt-hero__headline">
            <span>Welcome to</span>
            <em>Krys&amp;Tell.</em>
        </h1>

        <p class="kt-hero__sub">
            Clear explanations, gentle hands, and a clinic that feels warm and comfortable. Modern dentistry with a
            human touch.
        </p>

        <div class="kt-hero__ctas">
            <a href="#booking" class="kt-btn-primary"><span>Book an Appointment</span></a>
            <a href="#services" class="kt-btn-ghost">Explore Services ></a>
        </div>

        <div class="kt-hero__divider"></div>

        <div class="kt-hero__stats">
            <div class="kt-stat">
                <div class="kt-stat__num" data-target="{{ max(0, $patientCount) }}" data-suffix="">
                    {{ max(0, $patientCount) }}</div>
                <div class="kt-stat__label">Patients</div>
            </div>
            <div class="kt-stat">
                <div class="kt-stat__num" data-target="{{ max(1, $years) }}" data-suffix="+">
                    {{ max(1, $years) }}<em>+</em></div>
                <div class="kt-stat__label">Years</div>
            </div>
            <div class="kt-stat">
                <div class="kt-stat__num" data-target="{{ max(0, $satisfaction) }}" data-suffix="%">
                    {{ max(0, $satisfaction) }}<em>%</em></div>
                <div class="kt-stat__label">Satisfaction</div>
            </div>
        </div>
    </div>

    <div class="kt-hero__service-bar" aria-label="Core service categories">
        @if($serviceBarItems->isEmpty())
        <div class="kt-sbar__item"><span class="kt-dot"></span>No published services yet</div>
        @else
        @foreach($serviceBarItems as $serviceName)
        <div class="kt-sbar__item"><span class="kt-dot"></span>{{ $serviceName }}</div>
        @endforeach
        @endif
        <span class="kt-sbar__label">Our Services</span>
    </div>
</section>