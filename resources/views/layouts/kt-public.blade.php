<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Krys&Tell Dental Center')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400&family=Jost:wght@200;300;400;500;600&family=DM+Sans:wght@300;400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kt-public.css') }}?v={{ @filemtime(public_path('css/kt-public.css')) }}">
    @stack('styles')
</head>

<body class="kt-site">
    <div class="kt-loader" id="ktLoader" aria-hidden="true">
        <div class="kt-loader__inner">
            <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="" class="kt-loader__logo" width="64" height="64"
                loading="eager">
            <div class="kt-loader__bar">
                <div class="kt-loader__progress"></div>
            </div>
        </div>
    </div>

    @include('kt.partials.nav')

    <main class="kt-main">
        @yield('content')
    </main>

    @include('kt.partials.footer')

    <script src="{{ asset('js/kt-public.js') }}?v={{ @filemtime(public_path('js/kt-public.js')) }}"></script>
    @stack('scripts')
</body>

</html>