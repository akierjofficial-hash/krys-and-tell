<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Krys & Tell</title>
    <style>
        :root{color-scheme:light dark;font-family:system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
        body{min-height:100vh;display:grid;place-items:center;margin:0;padding:24px;box-sizing:border-box;background:#eef3fa;color:#172a49}
        main{width:min(100%,540px);padding:30px;border:1px solid #dce4ef;border-radius:16px;background:#fff;box-shadow:0 12px 35px #1b335412}
        .code{font-size:13px;font-weight:800;letter-spacing:.12em;color:#386ca7}h1{margin:10px 0;font-size:28px;line-height:1.2}p{line-height:1.55;color:#4a607e}
        a{display:inline-block;margin-top:8px;padding:10px 15px;border-radius:9px;background:#086fda;color:#fff;text-decoration:none;font-weight:700}a:focus-visible{outline:3px solid #7ab7ff;outline-offset:3px}
        @media(prefers-color-scheme:dark){body{background:#0b1220;color:#e2e8f0}main{background:#152032;border-color:#3b4d64}.code{color:#8fc4ff}p{color:#bac9db}}
    </style>
</head>
<body><main role="main"><div class="code">@yield('code')</div><h1>@yield('title')</h1><p>@yield('message')</p><p>@yield('next')</p><a href="{{ url('/') }}">Go to home</a></main></body>
</html>
