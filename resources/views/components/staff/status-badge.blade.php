@props(['tone' => 'neutral', 'icon' => null])
<span {{ $attributes->class(['staff-status', 'staff-status--'.$tone]) }}>
    @if($icon)<i class="{{ $icon }}" aria-hidden="true"></i>@endif
    {{ $slot }}
</span>
