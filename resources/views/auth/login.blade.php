@extends('layouts.guest')

@section('content')
<style>
    :root{
        --bg1:#fbf7f1;
        --bg2:#f4e7d8;
        --bg3:#efe0d6;
        --card:#ffffffcc;
        --stroke: rgba(15,23,42,.12);
        --text:#0f172a;
        --muted: rgba(15,23,42,.55);
        --brand:#2563eb;
        --shadow: 0 18px 45px rgba(15,23,42,.10);
    }

    /* Full screen background - no white top bar */
    .auth-wrap{
        min-height: 100vh;
        width: 100%;
        position: relative;
        overflow: hidden;

        background:
            radial-gradient(1200px 700px at 55% 45%, rgba(255,255,255,.55), transparent 60%),
            radial-gradient(700px 500px at 15% 80%, rgba(255,255,255,.35), transparent 60%),
            linear-gradient(135deg, var(--bg1), var(--bg2), var(--bg3));
        display:flex;
        align-items:center;
        justify-content:flex-start;
        padding: 32px 18px;
        box-sizing: border-box;
    }

    /* Soft blobs */
    .blob{
        position:absolute;
        border-radius: 999px;
        filter: blur(2px);
        opacity: .55;
        z-index: 0;
        pointer-events:none;
        mix-blend-mode: multiply;
    }
    .blob.one{
        width: 520px; height: 520px;
        left: -160px; top: -140px;
        background: radial-gradient(circle at 30% 30%, rgba(180, 120, 80, .35), transparent 62%);
    }
    .blob.two{
        width: 640px; height: 640px;
        right: -260px; top: -220px;
        background: radial-gradient(circle at 35% 35%, rgba(240, 215, 190, .55), transparent 60%);
        opacity: .75;
    }
    .blob.three{
        width: 520px; height: 520px;
        right: -220px; bottom: -260px;
        background: radial-gradient(circle at 40% 40%, rgba(200, 160, 130, .35), transparent 60%);
    }

    /* Card area placement: slightly left, not corner */
    .auth-inner{
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
        display:flex;
        align-items:center;
        justify-content:flex-start;
    }

    .login-card{
        width: 100%;
        max-width: 420px;
        background: var(--card);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,.65);
        box-shadow: var(--shadow);
        border-radius: 22px;
        padding: 26px;
        box-sizing: border-box;

        /* this makes it slightly left but not corner */
        margin-left: clamp(10px, 6vw, 90px);
    }

    .brand-row{
        display:flex;
        align-items:center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .logo-badge{
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(255,255,255,.78);
        border: 1px solid rgba(15,23,42,.10);
        display:grid;
        place-items:center;
        box-shadow: 0 10px 20px rgba(15,23,42,.08);
        flex: 0 0 auto;
    }

    .brand-title{
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.1;
    }
    .brand-sub{
        margin: 4px 0 0 0;
        font-size: 12px;
        color: var(--muted);
    }

    .welcome{
        margin: 10px 0 4px 0;
        font-size: 22px;
        font-weight: 900;
        letter-spacing: -.2px;
        color: var(--text);
    }
    .welcome-sub{
        margin: 0 0 18px 0;
        font-size: 13px;
        color: var(--muted);
    }

    .alert{
        border-radius: 14px;
        border: 1px solid rgba(239,68,68,.25);
        background: rgba(239,68,68,.08);
        color: #b91c1c;
        padding: 12px 12px;
        font-size: 13px;
        margin-bottom: 14px;
    }

    .field{
        margin-bottom: 12px;
    }
    .label{
        display:block;
        font-size: 12px;
        font-weight: 800;
        color: rgba(15,23,42,.70);
        margin-bottom: 6px;
    }

    .input-wrap{
        position: relative;
    }
    .icon-left{
        position:absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(15,23,42,.45);
        pointer-events:none;
    }
    .input{
        width: 100%;
        padding: 12px 12px 12px 40px;
        border-radius: 14px;
        border: 1px solid rgba(15,23,42,.12);
        background: rgba(255,255,255,.88);
        outline: none;
        font-size: 14px;
        color: var(--text);
        box-shadow: 0 8px 18px rgba(15,23,42,.06);
        transition: .15s ease;
        box-sizing: border-box;
    }
    .input:focus{
        border-color: rgba(37,99,235,.45);
        box-shadow: 0 0 0 4px rgba(37,99,235,.12);
        background: #fff;
    }

    .toggle-pass{
        position:absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: rgba(15,23,42,.55);
        cursor: pointer;
        padding: 6px;
    }
    .toggle-pass:hover{ color: rgba(15,23,42,.78); }

    .row-between{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        margin: 10px 0 14px 0;
        flex-wrap: wrap;
    }
    .check{
        display:flex;
        align-items:center;
        gap: 8px;
        font-size: 13px;
        color: rgba(15,23,42,.65);
        user-select:none;
    }
    .check input{ width: 16px; height: 16px; }

    .link{
        font-size: 13px;
        font-weight: 800;
        color: var(--brand);
        text-decoration:none;
    }
    .link:hover{ text-decoration: underline; }

    .btn{
        width: 100%;
        border: none;
        border-radius: 14px;
        padding: 12px 14px;
        font-weight: 900;
        font-size: 14px;
        color: #fff;
        background: linear-gradient(135deg, #2563eb, #1e90ff);
        box-shadow: 0 14px 28px rgba(37,99,235,.22);
        cursor:pointer;
        transition: .15s ease;
    }
    .btn:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 34px rgba(37,99,235,.28);
    }

    .fineprint{
        margin-top: 12px;
        font-size: 11px;
        color: rgba(15,23,42,.45);
        text-align:center;
    }

    .footer{
        margin-top: 14px;
        text-align:center;
        font-size: 11px;
        color: rgba(15,23,42,.45);
    }

    /* Background teeth svg */
    .bg-tooth{
        position:absolute;
        opacity: .06;
        color: rgba(15,23,42,.75);
        pointer-events:none;
        z-index:1;
    }
    .bg-tooth.t1{
        width: 460px;
        right: 10%;
        top: 50%;
        transform: translateY(-50%) rotate(8deg);
        opacity: .055;
    }
    .bg-tooth.t2{
        width: 220px;
        left: 3%;
        top: 22%;
        transform: rotate(-12deg);
        opacity: .05;
    }
    .bg-tooth.t3{
        width: 280px;
        left: 22%;
        bottom: 6%;
        transform: rotate(10deg);
        opacity: .045;
    }
    .bg-tooth.t4{
        width: 240px;
        right: 22%;
        top: 14%;
        transform: rotate(14deg);
        opacity: .04;
    }
    .bg-tooth.t5{
        width: 180px;
        right: 8%;
        bottom: 10%;
        transform: rotate(-18deg);
        opacity: .04;
    }
    .bg-tooth.t6{
        width: 200px;
        left: 38%;
        top: 10%;
        transform: rotate(6deg);
        opacity: .04;
    }

    /* Divider */
    .divider{
        display:flex;
        align-items:center;
        gap: 12px;
        margin: 14px 0;
        color: rgba(15,23,42,.45);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .divider::before,
    .divider::after{
        content:"";
        flex:1;
        height: 1px;
        background: rgba(15,23,42,.12);
    }

    /* Google button */
    .btn-google{
        width: 100%;
        display:flex;
        align-items:center;
        justify-content:center;
        gap: 10px;
        border-radius: 14px;
        padding: 12px 14px;
        font-weight: 900;
        font-size: 14px;
        text-decoration: none;

        border: 1px solid rgba(15,23,42,.12);
        background: rgba(255,255,255,.92);
        color: var(--text);
        box-shadow: 0 12px 22px rgba(15,23,42,.08);
        transition: .15s ease;
    }
    .btn-google:hover{
        transform: translateY(-1px);
        box-shadow: 0 16px 28px rgba(15,23,42,.12);
    }
    .btn-google:active{ transform: translateY(0); }

    .g-icon{
        width: 18px;
        height: 18px;
        display:block;
    }

    /* Responsive: center card on mobile */
    @media (max-width: 900px){
        .auth-inner{ justify-content:center; }
        .login-card{ margin-left: 0; }
        .bg-tooth.t3,
        .bg-tooth.t4,
        .bg-tooth.t6{ display:none; }
        .bg-tooth.t1{ width: 360px; right: -8%; }
    }
</style>

<div class="auth-wrap">

    <div class="blob one"></div>
    <div class="blob two"></div>
    <div class="blob three"></div>

    {{-- Background teeth (subtle) --}}
    @php
        // reusable tooth SVG path
        $toothSvg = '
            <path d="M256 32c-70.7 0-128 57.3-128 128
                     0 48.4 18.6 80.7 35.9 110.8
                     14.6 25.5 28.4 49.5 28.4 81.2
                     0 61.9 31.7 128 63.7 128
                     s63.7-66.1 63.7-128
                     c0-31.7 13.8-55.7 28.4-81.2
                     C365.4 240.7 384 208.4 384 160
                     c0-70.7-57.3-128-128-128Z"
                  stroke="currentColor" stroke-width="20"
                  stroke-linecap="round" stroke-linejoin="round"/>';
    @endphp

    <svg class="bg-tooth t1" viewBox="0 0 512 512" fill="none" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t2" viewBox="0 0 512 512" fill="none" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t3" viewBox="0 0 512 512" fill="none" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t4" viewBox="0 0 512 512" fill="none" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t5" viewBox="0 0 512 512" fill="none" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t6" viewBox="0 0 512 512" fill="none" aria-hidden="true">{!! $toothSvg !!}</svg>

    <div class="auth-inner">
        <div class="login-card">

            <div class="brand-row">
                <div class="logo-badge" aria-hidden="true">
                    {{-- Small tooth icon (logo) --}}
                    <svg width="24" height="24" viewBox="0 0 512 512" fill="none">
                        <path d="M256 32c-70.7 0-128 57.3-128 128
                                 0 48.4 18.6 80.7 35.9 110.8
                                 14.6 25.5 28.4 49.5 28.4 81.2
                                 0 61.9 31.7 128 63.7 128
                                 s63.7-66.1 63.7-128
                                 c0-31.7 13.8-55.7 28.4-81.2
                                 C365.4 240.7 384 208.4 384 160
                                 c0-70.7-57.3-128-128-128Z"
                              stroke="rgba(15,23,42,.75)" stroke-width="26"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="brand-title">Krys &amp; Tell</div>
                    <div class="brand-sub">Clinic Management System</div>
                </div>
            </div>

            <div class="welcome">Welcome back</div>
            <div class="welcome-sub">Sign in to continue to your portal.</div>

            @if ($errors->any())
                <div class="alert">
                    <div style="font-weight:800;">Login failed</div>
                    <div style="margin-top:4px;">{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                {{-- Email --}}
                <div class="field">
                    <label class="label" for="email">Email</label>
                    <div class="input-wrap">
                        <span class="icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                            </svg>
                        </span>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            placeholder="you@example.com"
                            class="input"
                            required
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="field">
                    <label class="label" for="password">Password</label>
                    <div class="input-wrap">
                        <span class="icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 11V7a4 4 0 10-8 0v4m12 0V7a4 4 0 118 0v4M5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/>
                            </svg>
                        </span>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="input"
                            required
                        >

                        <button type="button" class="toggle-pass"
                                onclick="const p=document.getElementById('password'); p.type=p.type==='password'?'text':'password';"
                                aria-label="Toggle password visibility">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    </div>

                    <div class="row-between">
                        <label class="check">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            Remember me
                        </label>

                        <a class="link" href="{{ route('public.home') }}">Back to site</a>
                    </div>
                </div>

                <button class="btn" type="submit">Sign in</button>

                <div class="divider"><span>or</span></div>

                <a class="btn-google" href="{{ route('google.redirect') }}">
                    <svg class="g-icon" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.01 1.53 7.39 2.81l5.06-5.06C33.36 4.3 29.08 2 24 2 14.73 2 6.98 7.3 3.08 15.02l6.1 4.74C11.2 13.5 17.08 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.5 24c0-1.64-.15-3.22-.43-4.74H24v9h12.7c-.55 2.97-2.2 5.48-4.7 7.17l7.2 5.6C43.76 36.97 46.5 30.98 46.5 24z"/>
                        <path fill="#FBBC05" d="M9.18 28.24A14.5 14.5 0 0 1 8.4 24c0-1.48.26-2.91.78-4.24l-6.1-4.74A23.9 23.9 0 0 0 2 24c0 3.86.92 7.52 2.54 10.98l6.64-6.74z"/>
                        <path fill="#34A853" d="M24 46c5.08 0 9.35-1.67 12.47-4.53l-7.2-5.6c-1.99 1.34-4.54 2.13-5.27 2.13-6.92 0-12.8-4-14.82-10.26l-6.64 6.74C6.98 40.7 14.73 46 24 46z"/>
                        <path fill="none" d="M2 2h44v44H2z"/>
                    </svg>
                    Continue with Google
                </a>

                <div class="fineprint">
                    New here? Continue with Google to create your account instantly.
                </div>
            </form>

            <div class="footer">
                © {{ date('Y') }} Krys &amp; Tell Clinic Management System
            </div>
        </div>
    </div>
</div>
@endsection
