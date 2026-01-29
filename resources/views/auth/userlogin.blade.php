@extends('layouts.guest')

@section('content')
@php
    $redirect = request('redirect');
    $backUrl = $redirect ?: route('public.home');
@endphp

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
        --radius: 22px;
    }

    .auth-wrap{
        min-height:100vh;
        width:100%;
        position:relative;
        overflow:hidden;
        display:flex;
        align-items:center;
        justify-content:center;
        padding: 28px 16px;
        background:
            radial-gradient(1200px 650px at 10% 0%, rgba(176,124,88,.14), transparent 58%),
            radial-gradient(900px 520px at 90% 20%, rgba(216,193,176,.22), transparent 55%),
            var(--soft);
    }

    /* Soft dotted pattern (subtle) */
    .auth-wrap::before{
        content:"";
        position:absolute; inset:-80px;
        background:
            radial-gradient(circle, rgba(11,18,32,.045) 1px, transparent 1px);
        background-size: 22px 22px;
        opacity:.55;
        pointer-events:none;
    }

    .auth-shell{
        width:100%;
        max-width: 1080px;
        position:relative;
        z-index:2;
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .card{
        width:100%;
        max-width: 460px;
        background: var(--card);
        border: 1px solid rgba(255,255,255,.7);
        box-shadow: var(--shadow);
        border-radius: var(--radius);
        padding: 22px;
        box-sizing:border-box;
        backdrop-filter: blur(10px);
    }

    @media (min-width: 900px){
        .card{ max-width: 520px; padding: 26px; border-radius: 26px; }
    }

    .top-row{
        display:flex;
        align-items:center;
        justify-content:space-between;
        margin-bottom: 12px;
    }

    .back-btn{
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: rgba(255,255,255,.92);
        display:grid;
        place-items:center;
        color: rgba(23,23,23,.85);
        text-decoration:none;
        box-shadow: 0 12px 30px rgba(11,18,32,.08);
        transition: .15s ease;
    }
    .back-btn:hover{ transform: translateY(-1px); }

    .badge{
        width: 42px;
        height: 42px;
        border-radius: 999px;
        border: 1px solid var(--border);
        background: rgba(255,255,255,.92);
        display:grid;
        place-items:center;
        box-shadow: 0 12px 30px rgba(11,18,32,.08);
        color: var(--brand);
    }

    .orb{
        margin: 6px auto 14px auto;
        width: 78px;
        height: 78px;
        border-radius: 999px;
        background:
            radial-gradient(circle at 35% 35%, rgba(255,255,255,.95), rgba(255,255,255,.65) 55%, rgba(255,255,255,.35) 100%);
        border: 1px solid rgba(17,17,17,.08);
        box-shadow: 0 18px 60px rgba(11,18,32,.12);
        display:grid;
        place-items:center;
        overflow:hidden;
    }
    .orb img{
        width: 52px;
        height: 52px;
        object-fit: contain;
        filter: drop-shadow(0 12px 20px rgba(11,18,32,.18));
    }

    h1{
        margin: 0;
        text-align:center;
        color: var(--ink);
        font-size: 28px;
        line-height: 1.1;
        font-weight: 950;
        letter-spacing: -0.4px;
    }
    .subtitle{
        margin: 10px 0 18px 0;
        text-align:center;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.5;
        font-weight: 650;
    }

    .alert{
        border-radius: 16px;
        border: 1px solid rgba(239,68,68,.25);
        background: rgba(239,68,68,.08);
        color: #b91c1c;
        padding: 12px 12px;
        font-size: 13px;
        margin: 0 0 14px 0;
    }

    .field{ margin-bottom: 12px; }
    .label{
        display:block;
        font-size: 12px;
        font-weight: 900;
        color: rgba(11,18,32,.72);
        margin-bottom: 8px;
    }

    .input-wrap{ position:relative; }

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
        color: var(--ink);
        outline: none;
        font-size: 14px;
        transition: .15s ease;
        box-sizing:border-box;
        box-shadow: 0 10px 25px rgba(11,18,32,.06);
    }
    .input::placeholder{ color: rgba(11,18,32,.40); }
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
        border: none;
        background: transparent;
        color: rgba(11,18,32,.55);
        padding: 6px;
        cursor:pointer;
    }
    .toggle-pass:hover{ color: rgba(11,18,32,.80); }

    .row{
        display:flex;
        align-items:center;
        justify-content:flex-start;
        gap: 10px;
        margin: 10px 0 14px 0;
        flex-wrap: wrap;
    }

    .check{
        display:flex;
        align-items:center;
        gap: 8px;
        color: rgba(11,18,32,.70);
        font-size: 13px;
        font-weight: 750;
        user-select:none;
    }
    .check input{
        width: 16px;
        height: 16px;
        accent-color: var(--brand);
    }

    .btn{
        width: 100%;
        border: none;
        border-radius: 999px;
        padding: 14px 16px;
        font-weight: 950;
        font-size: 14px;
        background: linear-gradient(135deg, var(--brand), #d2a07a);
        color: #fff;
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

    .divider{
        display:flex;
        align-items:center;
        gap: 12px;
        margin: 16px 0;
        color: rgba(11,18,32,.45);
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .10em;
        text-transform: uppercase;
    }
    .divider::before,
    .divider::after{
        content:"";
        flex:1;
        height: 1px;
        background: rgba(11,18,32,.12);
    }

    .btn-google{
        width: 100%;
        border-radius: 999px;
        padding: 13px 16px;
        border: 1px solid rgba(17,17,17,.16);
        background: rgba(255,255,255,.92);
        color: var(--ink);
        text-decoration:none;
        font-weight: 950;
        display:flex;
        align-items:center;
        justify-content:center;
        gap: 10px;
        box-shadow: 0 14px 34px rgba(11,18,32,.08);
        transition: .15s ease;
    }
    .btn-google:hover{
        transform: translateY(-1px);
        box-shadow: 0 18px 44px rgba(11,18,32,.12);
    }

    .footer-links{
        margin-top: 14px;
        display:flex;
        justify-content:center;
        gap: 12px;
        font-size: 12px;
        color: rgba(11,18,32,.55);
        flex-wrap: wrap;
    }
    .footer-links a{
        color: rgba(11,18,32,.68);
        font-weight: 900;
        text-decoration:none;
    }
    .footer-links a:hover{ text-decoration: underline; }

    .fine{
        margin-top: 14px;
        text-align:center;
        font-size: 11px;
        color: rgba(11,18,32,.50);
        font-weight: 650;
    }

    @media (max-width: 420px){
        h1{ font-size: 26px; }
    }
</style>

<div class="auth-wrap">
    <div class="auth-shell">
        <div class="card">

            <div class="top-row">
                <a class="back-btn" href="{{ $backUrl }}" aria-label="Back">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 18l-6-6 6-6"/>
                    </svg>
                </a>

                <div class="badge" title="Krys & Tell">
                    {{-- Filled tooth icon (matches your reference style better) --}}
                    <svg width="18" height="18" viewBox="0 0 64 64" aria-hidden="true">
                        <path fill="currentColor" d="M32 6c-8.6 0-16 6.9-16 15.7 0 6 2.5 10.2 4.8 14.1 1.9 3.2 3.7 6.2 3.7 10.2 0 8 4.2 14 7.5 14 3.3 0 5.3-6.1 6-10.6.4-2.6.7-4.4 2-4.4s1.6 1.8 2 4.4c.7 4.5 2.7 10.6 6 10.6 3.3 0 7.5-6 7.5-14 0-4 1.8-7 3.7-10.2 2.3-3.9 4.8-8.1 4.8-14.1C48 12.9 40.6 6 32 6z"/>
                        <path fill="#fff" opacity=".9" d="M25.2 16.8c2.6-2 7.2-2.2 10.1-.6 1.1.6 1.6 1.4.8 2.2-1.9 1.8-8.6 1.9-11.9.3-1.4-.7-1.2-1.3 1-1.9z"/>
                        <path fill="#fff" opacity=".9" d="M44.6 21.1c1.4 1.6 1.2 5.6-.5 8-1.2 1.7-2.4 1.4-2.1-.6.5-2.7.5-4.7-.1-6.3-.5-1.3 1.6-2.2 2.7-1.1z"/>
                    </svg>
                </div>
            </div>

            <div class="orb" aria-hidden="true">
                <img src="{{ asset('images/weblogo.png') }}" alt="Krys &amp; Tell">
            </div>

            <h1>Welcome Back!</h1>
            <div class="subtitle">
                Sign in to access your bookings, profile, and updates.
            </div>

            @if ($errors->any())
                <div class="alert">
                    <div style="font-weight:950;">Login failed</div>
                    <div style="margin-top:4px;">{{ $errors->first() }}</div>
                </div>
            @endif

            {{-- ✅ Keep redirect query param when submitting --}}
            <form method="POST" action="{{ route('userlogin.submit', ['redirect' => request('redirect')]) }}">
                @csrf

                <div class="field">
                    <label class="label" for="email">Email address*</label>
                    <div class="input-wrap">
                        <span class="icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M3 8l9 6 9-6M4 6h16a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                            </svg>
                        </span>
                        <input id="email" class="input" type="email" name="email" value="{{ old('email') }}"
                               autocomplete="email" placeholder="example@gmail.com" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="password">Password*</label>
                    <div class="input-wrap">
                        <span class="icon-left">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 11V7a4 4 0 10-8 0v4m12 0V7a4 4 0 118 0v4M5 11h14a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2v-7a2 2 0 012-2z"/>
                            </svg>
                        </span>

                        <input id="password" class="input" type="password" name="password"
                               autocomplete="current-password" placeholder="••••••••" required>

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
                </div>

                <div class="row">
                    <label class="check">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        Remember me
                    </label>
                </div>

                <button type="submit" class="btn">
                    <svg width="18" height="18" viewBox="0 0 64 64" aria-hidden="true">
                        <path fill="rgba(255,255,255,.96)" d="M32 6c-8.6 0-16 6.9-16 15.7 0 6 2.5 10.2 4.8 14.1 1.9 3.2 3.7 6.2 3.7 10.2 0 8 4.2 14 7.5 14 3.3 0 5.3-6.1 6-10.6.4-2.6.7-4.4 2-4.4s1.6 1.8 2 4.4c.7 4.5 2.7 10.6 6 10.6 3.3 0 7.5-6 7.5-14 0-4 1.8-7 3.7-10.2 2.3-3.9 4.8-8.1 4.8-14.1C48 12.9 40.6 6 32 6z"/>
                        <path fill="#fff" opacity=".9" d="M25.2 16.8c2.6-2 7.2-2.2 10.1-.6 1.1.6 1.6 1.4.8 2.2-1.9 1.8-8.6 1.9-11.9.3-1.4-.7-1.2-1.3 1-1.9z"/>
                        <path fill="#fff" opacity=".9" d="M44.6 21.1c1.4 1.6 1.2 5.6-.5 8-1.2 1.7-2.4 1.4-2.1-.6.5-2.7.5-4.7-.1-6.3-.5-1.3 1.6-2.2 2.7-1.1z"/>
                    </svg>
                    Sign in
                </button>

                <div class="divider">Or continue with</div>

                {{-- ✅ Keep redirect param for Google too --}}
                <a class="btn-google" href="{{ route('google.redirect', ['redirect' => request('redirect')]) }}">
                    <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.01 1.53 7.39 2.81l5.06-5.06C33.36 4.3 29.08 2 24 2 14.73 2 6.98 7.3 3.08 15.02l6.1 4.74C11.2 13.5 17.08 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.5 24c0-1.64-.15-3.22-.43-4.74H24v9h12.7c-.55 2.97-2.2 5.48-4.7 7.17l7.2 5.6C43.76 36.97 46.5 30.98 46.5 24z"/>
                        <path fill="#FBBC05" d="M9.18 28.24A14.5 14.5 0 0 1 8.4 24c0-1.48.26-2.91.78-4.24l-6.1-4.74A23.9 23.9 0 0 0 2 24c0 3.86.92 7.52 2.54 10.98l6.64-6.74z"/>
                        <path fill="#34A853" d="M24 46c5.08 0 9.35-1.67 12.47-4.53l-7.2-5.6c-1.99 1.34-4.54 2.13-5.27 2.13-6.92 0-12.8-4-14.82-10.26l-6.64 6.74C6.98 40.7 14.73 46 24 46z"/>
                        <path fill="none" d="M2 2h44v44H2z"/>
                    </svg>
                    Continue with Google
                </a>

                <div class="footer-links">
                    <a href="{{ route('public.home') }}">Back to site</a>
                </div>

                <div class="fine">
                    © {{ date('Y') }} Krys &amp; Tell Dental Center
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
