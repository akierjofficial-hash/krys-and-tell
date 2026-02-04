<!DOCTYPE html>
<html>
<head>
    <title>Krys&Tell Dental Center System</title>

    {{-- ✅ PWA (Installable App) --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#B07C58">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Krys&Tell">
    <link rel="apple-touch-icon" href="/images/pwa/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/pwa/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/pwa/icon-512.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
</head>
<body class="p-6">
    <div class="container mx-auto">
        @yield('content')
    </div>

{{-- ✅ PWA Service Worker --}}
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
        });
    }
</script>
</body>
</html>
