@php
    $testiList = collect($testimonials ?? [])->values()->map(function ($t, $i) {
        if (is_array($t)) {
            $name = (string) ($t['name'] ?? 'Patient');
            $rating = (int) ($t['rating'] ?? 5);
            return [
                'initial' => strtoupper(substr($name, 0, 1)),
                'name' => $name,
                'role' => $t['role'] ?? 'Patient',
                'text' => $t['text'] ?? '',
                'rating' => max(1, min(5, $rating)),
                'variant' => 'kt-testi-card__avatar--' . (($i % 3) + 1),
            ];
        }

        $name = (string) ($t->name ?? 'Patient');
        $rating = (int) ($t->rating ?? 5);
        return [
            'initial' => strtoupper(substr($name, 0, 1)),
            'name' => $name,
            'role' => $t->role ?? 'Patient',
            'text' => $t->text ?? '',
            'rating' => max(1, min(5, $rating)),
            'variant' => 'kt-testi-card__avatar--' . (($i % 3) + 1),
        ];
    })->filter(fn ($t) => trim((string) $t['text']) !== '')->values();

    $testimonialsTotal = (int) ($testimonialsTotal ?? $testiList->count());
    $hasMoreReviews = $testimonialsTotal > $testiList->count();
@endphp

<section class="kt-testimonials" id="testimonials">
    <div class="kt-testimonials__inner">
        <div class="kt-testimonials__header kt-reveal">
            <div class="kt-label kt-label--light">Patient Stories</div>
            <h2 class="kt-section-title kt-section-title--light">
                Words from Our<br><em>Happy Patients</em>
            </h2>
        </div>

        @if(session('review_success'))
            <div class="kt-form-success kt-review-success">{{ session('review_success') }}</div>
        @endif

        @if(session('review_error'))
            <div class="kt-form-error-banner kt-review-success">
                <strong>{{ session('review_error') }}</strong>
            </div>
        @endif

        @if($testiList->isEmpty())
            <article class="kt-testimonials-empty kt-reveal">
                <h3>No public reviews yet.</h3>
                <p>Be the first to share your experience with Krys and Tell Dental Center.</p>
            </article>
        @else
            <div class="kt-testimonials__grid">
                @foreach($testiList as $t)
                    <article class="kt-testi-card kt-reveal">
                        <p class="kt-testi-card__quote">"{{ $t['text'] }}"</p>
                        <div class="kt-testi-card__author">
                            <div class="kt-testi-card__avatar {{ $t['variant'] }}">{{ $t['initial'] }}</div>
                            <div>
                                <div class="kt-testi-card__name">{{ $t['name'] }}</div>
                                <div class="kt-testi-card__role">{{ $t['role'] }}</div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($hasMoreReviews)
                <div class="kt-testimonials__actions kt-reveal">
                    <a href="{{ route('public.reviews.index') }}" class="kt-btn-ghost">See all reviews ({{ $testimonialsTotal }}) ></a>
                </div>
            @endif
        @endif

        <div class="kt-review-panel kt-reveal">
            <h3>Share Your Review</h3>
            @auth
                @if($errors->review->any())
                    <div class="kt-form-error-banner">
                        <strong>Please fix the following:</strong>
                        <ul>
                            @foreach($errors->review->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('public.reviews.store') }}" class="kt-review-form">
                    @csrf
                    <div class="kt-form-row">
                        <div class="kt-form-group">
                            <p class="kt-form-label">Rating</p>
                            <div id="kt_review_rating" class="kt-review-rating" role="radiogroup" aria-label="Rating">
                                @for($i = 5; $i >= 1; $i--)
                                    <input
                                        id="kt_review_rating_{{ $i }}"
                                        type="radio"
                                        name="rating"
                                        value="{{ $i }}"
                                        class="kt-review-rating__input"
                                        {{ (int) old('rating', 5) === $i ? 'checked' : '' }}
                                        required>
                                    <label
                                        for="kt_review_rating_{{ $i }}"
                                        class="kt-review-rating__star"
                                        aria-label="{{ $i }} out of 5 stars">&#9733;</label>
                                @endfor
                            </div>
                            @error('rating', 'review')<span class="kt-form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="kt-form-group">
                            <label class="kt-form-label" for="kt_review_name">Reviewer</label>
                            <input id="kt_review_name" type="text" class="kt-form-input" value="{{ auth()->user()->name }}" readonly aria-readonly="true">
                        </div>
                    </div>

                    <div class="kt-form-group">
                        <label class="kt-form-label" for="kt_review_text">Your Review</label>
                        <textarea id="kt_review_text" name="text" class="kt-form-input @error('text', 'review') kt-input--error @enderror" rows="4" maxlength="600" placeholder="Tell other patients about your experience..." required>{{ old('text') }}</textarea>
                        @error('text', 'review')<span class="kt-form-error">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="kt-form-submit"><span>Submit Review ></span></button>
                </form>
            @else
                <p class="kt-review-panel__note">Please sign in to post a public review.</p>
                <a href="{{ route('userlogin', ['redirect' => route('public.home') . '#testimonials']) }}" class="kt-btn-primary"><span>Sign In to Review</span></a>
            @endauth
        </div>
    </div>
</section>
