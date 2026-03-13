@php
    $years = (int) ($heroStats['years'] ?? max(1, (int) now()->year - 2022 + 1));
@endphp

<section class="kt-about" id="about">
    <div class="kt-about__inner">
        <div class="kt-about__visual kt-reveal-left">
            <div class="kt-about__main-photo">
                <img src="{{ asset('images/pic6.jpg') }}" alt="" class="kt-about__main-img" loading="lazy">
            </div>

            <div class="kt-about__accent-box">
                <span class="kt-about__accent-num">{{ max(1, $years) }}<em>+</em></span>
                <span class="kt-about__accent-label">Years of care</span>
            </div>

            <div class="kt-about__team-row">
                <div class="kt-about__team-photo">
                    <img src="{{ asset('images/staffimg1.jpg') }}" alt="" class="kt-about__team-img" loading="lazy">
                </div>
                <div class="kt-about__team-photo">
                    <img src="{{ asset('images/staffimg2.jpg') }}" alt="" class="kt-about__team-img" loading="lazy">
                </div>
                <div class="kt-about__team-photo">
                    <img src="{{ asset('images/staffimg3.jpg') }}" alt="" class="kt-about__team-img" loading="lazy">
                </div>
            </div>
        </div>

        <div class="kt-about__content kt-reveal-right">
            <div class="kt-label">Why Choose Us</div>
            <h2 class="kt-section-title">
                Designed for Comfort,<br>Delivered with <em>Expertise</em>
            </h2>
            <p class="kt-section-body">
                At Krys &amp; Tell, exceptional care goes beyond clinical excellence. Every detail is crafted around your comfort, from arrival to aftercare.
            </p>

            <div class="kt-about__features">
                <div class="kt-about__feature kt-reveal">
                    <span class="kt-about__feature-num">01</span>
                    <div>
                        <h4>Experienced Specialists</h4>
                        <p>Board-certified dentists with decades of combined expertise.</p>
                    </div>
                </div>
                <div class="kt-about__feature kt-reveal">
                    <span class="kt-about__feature-num">02</span>
                    <div>
                        <h4>Advanced Technology</h4>
                        <p>Digital X-rays, 3D imaging, and the latest treatment systems.</p>
                    </div>
                </div>
                <div class="kt-about__feature kt-reveal">
                    <span class="kt-about__feature-num">03</span>
                    <div>
                        <h4>Patient-First Philosophy</h4>
                        <p>Your comfort and confidence drive every decision we make.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
