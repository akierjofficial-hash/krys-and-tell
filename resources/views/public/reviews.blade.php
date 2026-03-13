@extends('layouts.kt-public')

@section('title', 'Patient Reviews - Krys & Tell Dental Center')

@section('content')
@php
    $testiList = collect($testimonials->items() ?? [])->values()->map(function ($t, $i) {
        $name = (string) ($t->name ?? 'Patient');
        return [
            'initial' => strtoupper(substr($name, 0, 1)),
            'name' => $name,
            'role' => $t->role ?? 'Patient',
            'text' => $t->text ?? '',
            'variant' => 'kt-testi-card__avatar--' . (($i % 3) + 1),
        ];
    })->filter(fn ($t) => trim((string) $t['text']) !== '')->values();

    $currentPage = (int) $testimonials->currentPage();
    $lastPage = (int) $testimonials->lastPage();
    $startPage = max(1, $currentPage - 1);
    $endPage = max(1, min($lastPage, $currentPage + 1));
@endphp

<section class="kt-testimonials kt-reviews-page">
    <div class="kt-page-shell">
        <a href="{{ route('public.home') }}#testimonials" class="kt-back-link kt-reviews-page__back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" aria-hidden="true">
                <path d="M19 12H5"/><path d="M12 5l-7 7 7 7"/>
            </svg>
            Back to Home
        </a>

        <div class="kt-testimonials__header kt-reveal">
            <div class="kt-label kt-label--light">Patient Stories</div>
            <h1 class="kt-section-title kt-section-title--light">
                All Public<br><em>Reviews</em>
            </h1>
            @if($testimonials->total() > 0)
                <p class="kt-reviews-page__meta">
                    Showing {{ $testimonials->firstItem() }}-{{ $testimonials->lastItem() }} of {{ $testimonials->total() }} reviews
                </p>
            @endif
        </div>

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

            @if($testimonials->hasPages())
                <nav class="kt-reviews-pagination" aria-label="Reviews Pagination">
                    @if($testimonials->onFirstPage())
                        <span class="kt-reviews-pagination__link kt-reviews-pagination__link--disabled">Previous</span>
                    @else
                        <a href="{{ $testimonials->previousPageUrl() }}" class="kt-reviews-pagination__link">Previous</a>
                    @endif

                    @for($page = $startPage; $page <= $endPage; $page++)
                        @if($page === $currentPage)
                            <span class="kt-reviews-pagination__link kt-reviews-pagination__link--active">{{ $page }}</span>
                        @else
                            <a href="{{ $testimonials->url($page) }}" class="kt-reviews-pagination__link">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($testimonials->hasMorePages())
                        <a href="{{ $testimonials->nextPageUrl() }}" class="kt-reviews-pagination__link">Next</a>
                    @else
                        <span class="kt-reviews-pagination__link kt-reviews-pagination__link--disabled">Next</span>
                    @endif
                </nav>
            @endif
        @endif
    </div>
</section>
@endsection
