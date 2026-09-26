@props(['title' => null, 'description' => null])
<section {{ $attributes->class(['staff-section-card']) }}>
    @if($title || isset($actions))
        <header class="staff-section-card__header">
            <div>
                @if($title)<h2>{{ $title }}</h2>@endif
                @if($description)<p>{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="staff-section-card__actions">{{ $actions }}</div>@endisset
        </header>
    @endif
    {{ $slot }}
</section>
