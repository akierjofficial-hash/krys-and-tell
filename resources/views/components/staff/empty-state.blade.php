@props(['icon' => 'fa-regular fa-folder-open', 'title', 'message' => null])
<div {{ $attributes->class(['staff-empty-state']) }}>
    <i class="{{ $icon }} staff-empty-state__icon" aria-hidden="true"></i>
    <strong>{{ $title }}</strong>
    @if($message)<p>{{ $message }}</p>@endif
    @isset($action)<div class="staff-empty-state__action">{{ $action }}</div>@endisset
</div>
