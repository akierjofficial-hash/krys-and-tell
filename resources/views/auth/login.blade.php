@extends('layouts.guest')

@section('content')
<style>
    :root{
        /* Match public.blade.php (beige / brand theme) */
        --brand: #B07C58;
        --brand2:#D8C1B0;
        --ink:#0b1220;
        --text:#171717;
        --muted: rgba(23,23,23,.68);
        --soft:#FBF7F2;
        --card: rgba(255,255,255,.92);
        --border: rgba(17,17,17,.10);
        --shadow: 0 18px 55px rgba(11,18,32,.10);
        --radius: 24px;
    }

    .auth-wrap{
        min-height: 100vh;
        width: 100%;
        position: relative;
        overflow: hidden;

        background:
            radial-gradient(1200px 650px at 10% 0%, rgba(176,124,88,.14), transparent 58%),
            radial-gradient(900px 520px at 90% 20%, rgba(216,193,176,.22), transparent 55%),
            linear-gradient(180deg, #fff, var(--soft));
        display:flex;
        align-items:center;
        justify-content:center;
        padding: 32px 18px;
        box-sizing: border-box;
    }

    /* subtle dotted texture */
    .auth-wrap::before{
        content:"";
        position:absolute; inset:-80px;
        background: radial-gradient(circle, rgba(11,18,32,.045) 1px, transparent 1px);
        background-size: 22px 22px;
        opacity:.55;
        pointer-events:none;
    }

    /* soft blobs (beige) */
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
        background: radial-gradient(circle at 30% 30%, rgba(176,124,88,.22), transparent 62%);
    }
    .blob.two{
        width: 640px; height: 640px;
        right: -260px; top: -220px;
        background: radial-gradient(circle at 35% 35%, rgba(216,193,176,.55), transparent 60%);
        opacity: .75;
    }
    .blob.three{
        width: 520px; height: 520px;
        right: -220px; bottom: -260px;
        background: radial-gradient(circle at 40% 40%, rgba(176,124,88,.16), transparent 60%);
    }

    .auth-inner{
        width: 100%;
        max-width: 1180px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .login-card{
        width: 100%;
        max-width: 520px;
        background: var(--card);
        border: 1px solid rgba(255,255,255,.75);
        box-shadow: var(--shadow);
        border-radius: var(--radius);
        padding: 26px;
        box-sizing: border-box;
        backdrop-filter: blur(10px);
    }

    .brand-row{
        display:flex;
        align-items:center;
        gap: 12px;
        margin-bottom: 14px;
    }

    .logo-badge{
        width: 46px;
        height: 46px;
        border-radius: 16px;
        background: rgba(255,255,255,.95);
        border: 1px solid var(--border);
        display:grid;
        place-items:center;
        box-shadow: 0 12px 28px rgba(11,18,32,.08);
        flex: 0 0 auto;
        color: var(--brand);
    }

    .brand-title{
        margin: 0;
        font-size: 14px;
        font-weight: 950;
        color: rgba(23,23,23,.90);
        line-height: 1.1;
        letter-spacing: -.01em;
    }
    .brand-sub{
        margin: 4px 0 0 0;
        font-size: 12px;
        color: rgba(23,23,23,.62);
        font-weight: 700;
    }

    .welcome{
        margin: 10px 0 4px 0;
        font-size: 22px;
        font-weight: 950;
        letter-spacing: -.2px;
        color: var(--ink);
    }
    .welcome-sub{
        margin: 0 0 12px 0;
        font-size: 13px;
        color: var(--muted);
        font-weight: 650;
        line-height: 1.5;
    }

    .alert{
        border-radius: 16px;
        border: 1px solid rgba(239,68,68,.25);
        background: rgba(239,68,68,.08);
        color: #b91c1c;
        padding: 12px 12px;
        font-size: 13px;
        margin-bottom: 14px;
        font-weight: 650;
    }

    /* ✅ Caution (staff/admin only) */
    .caution{
        border-radius: 16px;
        border: 1px solid rgba(245,158,11,.25);
        background: rgba(245,158,11,.10);
        color: #92400e;
        padding: 12px 12px;
        font-size: 13px;
        margin-bottom: 14px;
        font-weight: 650;
    }
    .caution b{ font-weight: 950; }
    .caution a{
        color: #92400e;
        font-weight: 950;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .field{ margin-bottom: 12px; }
    .label{
        display:block;
        font-size: 12px;
        font-weight: 900;
        color: rgba(11,18,32,.72);
        margin-bottom: 8px;
    }

    .input-wrap{ position: relative; }

    .icon-left{
        position:absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(11,18,32,.48);
        pointer-events:none;
    }

    .input{
        width: 100%;
        padding: 13px 12px 13px 40px;
        border-radius: 14px;
        border: 1px solid rgba(17,17,17,.14);
        background: rgba(251,247,242,.85);
        outline: none;
        font-size: 14px;
        color: var(--ink);
        box-shadow: 0 10px 25px rgba(11,18,32,.06);
        transition: .15s ease;
        box-sizing: border-box;
    }
    .input:focus{
        border-color: rgba(176,124,88,.45);
        box-shadow: 0 0 0 4px rgba(176,124,88,.12);
        background: rgba(255,255,255,.95);
    }

    .toggle-pass{
        position:absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: rgba(11,18,32,.55);
        cursor: pointer;
        padding: 6px;
    }
    .toggle-pass:hover{ color: rgba(11,18,32,.80); }

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
        color: rgba(11,18,32,.70);
        user-select:none;
        font-weight: 750;
    }
    .check input{
        width: 16px;
        height: 16px;
        accent-color: var(--brand);
    }

    .link{
        font-size: 13px;
        font-weight: 950;
        color: rgba(11,18,32,.70);
        text-decoration:none;
    }
    .link:hover{ text-decoration: underline; }

    .btn{
        width: 100%;
        border: none;
        border-radius: 999px;
        padding: 14px 16px;
        font-weight: 950;
        font-size: 14px;
        color: #fff;
        background: linear-gradient(135deg, var(--brand), #d2a07a);
        box-shadow: 0 18px 45px rgba(176,124,88,.22);
        cursor:pointer;
        transition: .15s ease;
        display:flex;
        align-items:center;
        justify-content:center;
        gap: 10px;
    }
    .btn:hover{
        transform: translateY(-1px);
        box-shadow: 0 24px 55px rgba(176,124,88,.28);
    }

    .footer{
        margin-top: 14px;
        text-align:center;
        font-size: 11px;
        color: rgba(11,18,32,.50);
        font-weight: 650;
    }

    /* Background teeth svg (filled + shine style, subtle) */
    .bg-tooth{
        position:absolute;
        opacity: .08;
        pointer-events:none;
        z-index:1;
        filter: blur(.1px);
    }
    .bg-tooth path.tooth-fill{ fill: rgba(11,18,32,.16); }
    .bg-tooth path.tooth-shine{ fill: rgba(255,255,255,.72); opacity: .8; }

    .bg-tooth.t1{ width: 520px; right: 8%; top: 50%; transform: translateY(-50%) rotate(10deg); opacity: .07; }
    .bg-tooth.t2{ width: 220px; left: 4%; top: 22%; transform: rotate(-12deg); opacity: .06; }
    .bg-tooth.t3{ width: 280px; left: 18%; bottom: 8%; transform: rotate(12deg); opacity: .05; }
    .bg-tooth.t4{ width: 240px; right: 22%; top: 12%; transform: rotate(14deg); opacity: .05; }
    .bg-tooth.t5{ width: 180px; right: 10%; bottom: 10%; transform: rotate(-18deg); opacity: .045; }
    .bg-tooth.t6{ width: 200px; left: 38%; top: 10%; transform: rotate(6deg); opacity: .045; }

    @media (max-width: 900px){
        .bg-tooth.t3, .bg-tooth.t4, .bg-tooth.t6{ display:none; }
        .bg-tooth.t1{ width: 380px; right: -10%; }
    }
</style>

<div class="auth-wrap">

    <div class="blob one"></div>
    <div class="blob two"></div>
    <div class="blob three"></div>

    @php
        // Filled tooth SVG (same vibe as your reference)
        $toothSvg = '
            <path class="tooth-fill" d="M256 32c-70.7 0-128 57.3-128 128
                     0 48.4 18.6 80.7 35.9 110.8
                     14.6 25.5 28.4 49.5 28.4 81.2
                     0 61.9 31.7 128 63.7 128
                     s63.7-66.1 63.7-128
                     c0-31.7 13.8-55.7 28.4-81.2
                     C365.4 240.7 384 208.4 384 160
                     c0-70.7-57.3-128-128-128Z"/>

            <path class="tooth-shine" d="M210 150c18-14 52-15 73-5
                    8 4 12 11 6 17-14 13-62 14-86 2-11-6-9-9 7-14z"/>
            <path class="tooth-shine" d="M350 180c10 12 9 40-3 57
                    -9 12-17 10-15-4 4-19 4-33 0-45-3-9 11-16 18-8z"/>
        ';
    @endphp

    <svg class="bg-tooth t1" viewBox="0 0 512 512" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t2" viewBox="0 0 512 512" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t3" viewBox="0 0 512 512" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t4" viewBox="0 0 512 512" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t5" viewBox="0 0 512 512" aria-hidden="true">{!! $toothSvg !!}</svg>
    <svg class="bg-tooth t6" viewBox="0 0 512 512" aria-hidden="true">{!! $toothSvg !!}</svg>

    <div class="auth-inner">
        <div class="login-card">

            <div class="brand-row">
                <div class="logo-badge" aria-hidden="true">
                    {{-- Filled tooth icon --}}
                    <svg width="24" height="24" viewBox="0 0 64 64" aria-hidden="true">
                        <path fill="currentColor" d="M32 6c-8.6 0-16 6.9-16 15.7 0 6 2.5 10.2 4.8 14.1 1.9 3.2 3.7 6.2 3.7 10.2 0 8 4.2 14 7.5 14 3.3 0 5.3-6.1 6-10.6.4-2.6.7-4.4 2-4.4s1.6 1.8 2 4.4c.7 4.5 2.7 10.6 6 10.6 3.3 0 7.5-6 7.5-14 0-4 1.8-7 3.7-10.2 2.3-3.9 4.8-8.1 4.8-14.1C48 12.9 40.6 6 32 6z"/>
                        <path fill="#fff" opacity=".9" d="M25.2 16.8c2.6-2 7.2-2.2 10.1-.6 1.1.6 1.6 1.4.8 2.2-1.9 1.8-8.6 1.9-11.9.3-1.4-.7-1.2-1.3 1-1.9z"/>
                        <path fill="#fff" opacity=".9" d="M44.6 21.1c1.4 1.6 1.2 5.6-.5 8-1.2 1.7-2.4 1.4-2.1-.6.5-2.7.5-4.7-.1-6.3-.5-1.3 1.6-2.2 2.7-1.1z"/>
                    </svg>
                </div>
                <div>
                    <div class="brand-title">Krys &amp; Tell</div>
                    <div class="brand-sub">Clinic Management System</div>
                </div>
            </div>

            <div class="welcome">Staff/Admin Portal</div>
            <div class="welcome-sub">Sign in to continue to your portal.</div>

            <div class="caution">
                <b>Notice:</b> This portal is for <b>authorized Admin &amp; Staff only</b>.
                Patient/users should sign in using the <a href="{{ route('userlogin') }}">User Login</a>.
            </div>

            @if ($errors->any())
                <div class="alert">
                    <div style="font-weight:950;">Login failed</div>
                    <div style="margin-top:4px;">{{ $errors->first() }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf

                <div class="field">
                    <label class="label" for="email">Email</label>
                    <div class="input-wrap">
                        <span class="icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                            </svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}"
                               autocomplete="email" placeholder="staff@clinic.com"
                               class="input" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="password">Password</label>
                    <div class="input-wrap">
                        <span class="icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 11V7a4 4 0 10-8 0v4m12 0V7a4 4 0 118 0v4M5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/>
                            </svg>
                        </span>

                        <input id="password" type="password" name="password"
                               autocomplete="current-password" placeholder="••••••••"
                               class="input" required>

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

                <button class="btn" type="submit">
                    <svg width="18" height="18" viewBox="0 0 64 64" aria-hidden="true">
                        <path fill="rgba(255,255,255,.96)" d="M32 6c-8.6 0-16 6.9-16 15.7 0 6 2.5 10.2 4.8 14.1 1.9 3.2 3.7 6.2 3.7 10.2 0 8 4.2 14 7.5 14 3.3 0 5.3-6.1 6-10.6.4-2.6.7-4.4 2-4.4s1.6 1.8 2 4.4c.7 4.5 2.7 10.6 6 10.6 3.3 0 7.5-6 7.5-14 0-4 1.8-7 3.7-10.2 2.3-3.9 4.8-8.1 4.8-14.1C48 12.9 40.6 6 32 6z"/>
                        <path fill="#fff" opacity=".9" d="M25.2 16.8c2.6-2 7.2-2.2 10.1-.6 1.1.6 1.6 1.4.8 2.2-1.9 1.8-8.6 1.9-11.9.3-1.4-.7-1.2-1.3 1-1.9z"/>
                        <path fill="#fff" opacity=".9" d="M44.6 21.1c1.4 1.6 1.2 5.6-.5 8-1.2 1.7-2.4 1.4-2.1-.6.5-2.7.5-4.7-.1-6.3-.5-1.3 1.6-2.2 2.7-1.1z"/>
                    </svg>
                    Sign in
                </button>

                <div class="footer">
                    © {{ date('Y') }} Krys &amp; Tell Clinic Management System
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
