@php
    $serviceList = collect($services ?? [])->values()->map(function ($s, $i) {
        if (is_array($s)) {
            return [
                'id' => $s['id'] ?? null,
                'number' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'name' => $s['name'] ?? 'Dental Service',
                'tag' => $s['tag'] ?? (!empty($s['duration_minutes']) ? ((int) $s['duration_minutes'] . ' mins') : 'Personalized treatment'),
            ];
        }

        return [
            'id' => $s->id ?? null,
            'number' => str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
            'name' => $s->name ?? 'Dental Service',
            'tag' => !empty($s->duration_minutes)
                ? ((int) $s->duration_minutes . ' mins')
                : 'Personalized treatment',
        ];
    });
@endphp

<section class="kt-services" id="services">
    <div class="kt-services__inner">
        <div class="kt-services__header kt-reveal">
            <div class="kt-label">Our Expertise</div>
            <h2 class="kt-section-title">
                Comprehensive Care<br>for Every <em>Smile</em>
            </h2>
            <p class="kt-section-body">
                From routine check-ups to complete smile transformations, every treatment delivered with precision and compassion.
            </p>
            <a href="#booking" class="kt-text-link">Book a Consultation ></a>
        </div>

        <div class="kt-services__list">
            @if($serviceList->isEmpty())
                <article class="kt-services-empty kt-reveal">
                    <h3>Services will appear here soon.</h3>
                    <p>Our team is currently updating available treatments.</p>
                    <a href="{{ route('public.contact') }}" class="kt-text-link">Contact the Clinic ></a>
                </article>
            @else
                @foreach($serviceList as $s)
                    @php
                        $href = !empty($s['id']) ? route('public.services.show', $s['id']) : route('public.services.index');
                    @endphp
                    <a href="{{ $href }}" class="kt-service-row kt-reveal" aria-label="{{ $s['name'] }}">
                        <span class="kt-service-row__num">{{ $s['number'] }}</span>
                        <div class="kt-service-row__body">
                            <div class="kt-service-row__name">{{ $s['name'] }}</div>
                            <div class="kt-service-row__tag">{{ $s['tag'] }}</div>
                        </div>
                        <span class="kt-service-row__arrow">></span>
                    </a>
                @endforeach
            @endif
        </div>
    </div>
</section>
