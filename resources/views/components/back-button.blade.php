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
        if (str_starts_with($url, '//')) return false;
        if (str_starts_with($url, '\\\\')) return false;

        if (str_starts_with($url, '/')) return true;

        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;

        return strcasecmp($host, (string) request()->getHost()) === 0;
    };

    $backUrl = null;
    $backSource = 'fallback';

    // 1) explicit ?return=...
    if ($isSafe($returnUrl)) {
        $backUrl = $returnUrl;
        $backSource = 'explicit';
    }

    // 2) real previous page
    if (!$backUrl) {
        $prev = url()->previous();
        if ($isSafe($prev) && $prev !== url()->current()) {
            $backUrl = $prev;
            $backSource = 'previous';
        }
    }

    // 3) stored last list URL (index pages)
    if (!$backUrl) {
        $stored = session('kt.return_url');
        if ($isSafe($stored)) {
            $backUrl = $stored;
            $backSource = 'stored';
        }
    }

    // 4) fallback
    if (!$backUrl) {
        $backUrl = $fallback ?: url('/');
        $backSource = 'fallback';
    }
@endphp

<a
    href="{{ $backUrl }}"
    class="{{ $class }}"
    data-back-source="{{ $backSource }}"
    data-no-loader
    onclick="if(this.dataset.backSource==='previous' && history.length>1){event.preventDefault();history.back();return false;}"
>
    @if($icon_class)
        <i class="{{ $icon_class }}"></i>
    @endif
    {{ $label }}
</a>
