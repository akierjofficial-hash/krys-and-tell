@props(['label', 'href' => null, 'variant' => 'secondary', 'type' => 'button'])
@if($href)
    <a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class(['btn', 'btn-'.$variant, 'staff-icon-button']) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class(['btn', 'btn-'.$variant, 'staff-icon-button']) }}>{{ $slot }}</button>
@endif
