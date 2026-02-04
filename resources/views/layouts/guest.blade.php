<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <title>@yield('title', 'Krys&Tell')</title>

    {{-- ✅ PWA (Installable App) --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#B07C58">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Krys&Tell">
    <link rel="apple-touch-icon" href="/images/pwa/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/images/pwa/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/images/pwa/icon-512.png">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        html, body { height: 100%; }
        body{
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        *{ box-sizing: border-box; }
    </style>
</head>
<body>
    @yield('content')

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
