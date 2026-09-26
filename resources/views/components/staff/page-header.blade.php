@props(['title', 'subtitle' => null, 'eyebrow' => null])
<header {{ $attributes->class(['staff-page-header']) }}>
    <div class="staff-page-header__copy">
        @if($eyebrow)<div class="staff-page-header__eyebrow">{{ $eyebrow }}</div>@endif
        <h1>{{ $title }}</h1>
        @if($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @isset($actions)<div class="staff-page-header__actions">{{ $actions }}</div>@endisset
</header>
