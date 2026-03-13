@php
    $role = auth()->user()->role ?? 'user';
@endphp

<nav class="kt-nav" id="ktNav">
    <a href="{{ route('public.home') }}" class="kt-nav__brand" aria-label="Krys and Tell Dental Center">
        <div class="kt-nav__logo-wrap">
            <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="" class="kt-nav__logo-img" width="36" height="36" loading="eager">
        </div>
        <div class="kt-nav__brand-text">
            <span class="kt-nav__brand-name">KRYS &amp; TELL</span>
            <span class="kt-nav__brand-sub">Dental Center</span>
        </div>
    </a>

    <ul class="kt-nav__links" id="ktNavLinks">
        <li>
            <a href="{{ route('public.home') }}" class="kt-nav__link {{ request()->routeIs('public.home') ? 'kt-nav__link--active' : '' }}">Home</a>
        </li>
        <li>
            <a href="{{ route('public.about') }}" class="kt-nav__link {{ request()->routeIs('public.about') ? 'kt-nav__link--active' : '' }}">About</a>
        </li>
        <li>
            <a href="{{ route('public.services.index') }}" class="kt-nav__link {{ request()->routeIs('public.services.*') ? 'kt-nav__link--active' : '' }}">Services</a>
        </li>
        <li>
            <a href="{{ route('public.contact') }}" class="kt-nav__link {{ request()->routeIs('public.contact') ? 'kt-nav__link--active' : '' }}">Contact</a>
        </li>
    </ul>

    <div class="kt-nav__right">
        @guest
            <a href="{{ route('userlogin') }}" class="kt-nav__signin" aria-label="Sign in">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                    <polyline points="10 17 15 12 10 7"/>
                    <line x1="15" y1="12" x2="3" y2="12"/>
                </svg>
                Sign in
            </a>
        @endguest

        @auth
            <div class="kt-nav__profile" id="ktNavProfile">
                <button class="kt-nav__avatar-btn" type="button" id="ktProfileToggle" aria-expanded="false" aria-controls="ktProfileMenu" aria-label="Open profile menu">
                    <span class="kt-nav__avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</span>
                </button>

                <div class="kt-nav__dropdown" id="ktProfileMenu" role="menu" aria-labelledby="ktProfileToggle">
                    <div class="kt-nav__dropdown-head">
                        <div class="kt-nav__dropdown-name">{{ auth()->user()->name }}</div>
                        <div class="kt-nav__dropdown-email">{{ auth()->user()->email }}</div>
                    </div>

                    <div class="kt-nav__dropdown-sep"></div>

                    <a class="kt-nav__dropdown-link" href="{{ route('profile.show') }}">My Profile</a>

                    @if(\Illuminate\Support\Facades\Route::has('public.installments.index'))
                        <a class="kt-nav__dropdown-link" href="{{ route('public.installments.index') }}">My Installment Plans</a>
                    @endif

                    @if(in_array($role, ['admin', 'staff']))
                        <a class="kt-nav__dropdown-link" href="{{ route('portal') }}">Portal</a>
                    @endif

                    <div class="kt-nav__dropdown-sep"></div>

                    <form method="POST" action="{{ route('userlogout') }}" class="kt-nav__dropdown-form">
                        @csrf
                        <button type="submit" class="kt-nav__dropdown-link kt-nav__dropdown-link--button">Logout</button>
                    </form>
                </div>
            </div>
        @endauth

        <button class="kt-nav__toggle" id="ktNavToggle" aria-label="Open menu" aria-controls="ktNavDrawer" aria-expanded="false" type="button">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<div class="kt-nav__drawer" id="ktNavDrawer" aria-hidden="true" hidden>
    <a href="{{ route('public.home') }}" class="kt-drawer__link {{ request()->routeIs('public.home') ? 'kt-drawer__link--active' : '' }}">Home <span>></span></a>
    <a href="{{ route('public.about') }}" class="kt-drawer__link {{ request()->routeIs('public.about') ? 'kt-drawer__link--active' : '' }}">About <span>></span></a>
    <a href="{{ route('public.services.index') }}" class="kt-drawer__link {{ request()->routeIs('public.services.*') ? 'kt-drawer__link--active' : '' }}">Services <span>></span></a>
    <a href="{{ route('public.contact') }}" class="kt-drawer__link {{ request()->routeIs('public.contact') ? 'kt-drawer__link--active' : '' }}">Contact <span>></span></a>

    @guest
        <a href="{{ route('userlogin') }}" class="kt-drawer__signin">Sign in ></a>
    @endguest

    @auth
        @if(in_array($role, ['admin', 'staff']))
            <a href="{{ route('portal') }}" class="kt-drawer__signin">Portal ></a>
        @else
            <a href="{{ route('profile.show') }}" class="kt-drawer__signin">My Account ></a>
        @endif
    @endauth

    <a href="#booking" class="kt-drawer__book">Book an Appointment</a>
</div>
