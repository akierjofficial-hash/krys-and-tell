@props([
    'fallback' => null,                 // required fallback URL/route string
    'class' => 'btn-ghostx',
    'label' => 'Back',
    // Use any FontAwesome/Bootstrap icon classes you want (e.g. "fa-solid fa-arrow-left me-1")
    'icon_class' => 'fa fa-arrow-left',
])

@php
    $returnUrl = request('return') ?? old('return');

    $isSafe = function (?string $url) {
        if (!$url) return false;
        $host = parse_url($url, PHP_URL_HOST);
        return !$host || $host === request()->getHost();
    };

    $backUrl = null;

    // 1) explicit ?return=...
    if ($isSafe($returnUrl)) {
        $backUrl = $returnUrl;
    }

    // 2) stored last list URL (index pages)
    if (!$backUrl) {
        $stored = session('kt.return_url');
        if ($isSafe($stored)) {
            $backUrl = $stored;
        }
    }

    // 3) real previous page
    if (!$backUrl) {
        $prev = url()->previous();
        if ($isSafe($prev) && $prev !== url()->current()) {
            $backUrl = $prev;
        }
    }

    // 4) fallback
    if (!$backUrl) {
        $backUrl = $fallback ?: url('/');
    }
@endphp

<a
    href="{{ $backUrl }}"
    class="{{ $class }}"
    data-no-loader
    onclick="try{const r=document.referrer;if(r){const u=new URL(r);if(u.origin===location.origin && history.length>1){event.preventDefault();history.back();return false;}}}catch(e){}"
>
    @if($icon_class)
        <i class="{{ $icon_class }}"></i>
    @endif
    {{ $label }}
</a>
