<footer class="kt-footer">
    <div class="kt-footer__inner">
        <div class="kt-footer__top">
            <div class="kt-footer__brand">
                <div class="kt-footer__brand-row">
                    <img src="{{ asset('images/krysandtelllogo.jpg') }}" alt="" class="kt-footer__logo-img" width="38" height="38" loading="lazy">
                    <span class="kt-footer__brand-name">KRYS &amp; TELL</span>
                </div>

                <p class="kt-footer__tagline">
                    Gentle. Modern. Trusted. Where healthy smiles meet premium dental care.
                </p>

                <div class="kt-footer__social">
                    <a href="https://www.instagram.com/krysandtelldental/" class="kt-social-btn" aria-label="Instagram">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="https://www.facebook.com/Ktelzaflats" class="kt-social-btn" aria-label="Facebook">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                    </a>
                    <a href="https://www.tiktok.com/@krysandtell2023" class="kt-social-btn" aria-label="Tiktok">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"/></svg>
                    </a>
                </div>

                <a href="#booking" class="kt-footer__book-cta">Book an Appointment</a>
            </div>

            <div class="kt-footer__col">
                <div class="kt-footer__col-title">Services</div>
                <ul class="kt-footer__links">
                    <li><a href="{{ route('public.services.index') }}">General Dentistry</a></li>
                    <li><a href="{{ route('public.services.index') }}">Cosmetic Dentistry</a></li>
                    <li><a href="{{ route('public.services.index') }}">Teeth Whitening</a></li>
                    <li><a href="{{ route('public.services.index') }}">Orthodontics</a></li>
                    <li><a href="{{ route('public.services.index') }}">Dental Implants</a></li>
                </ul>
            </div>

            <div class="kt-footer__col">
                <div class="kt-footer__col-title">Clinic</div>
                <ul class="kt-footer__links">
                    <li><a href="{{ route('public.about') }}">About Us</a></li>
                    <li><a href="{{ route('public.services.index') }}">Our Services</a></li>
                    <li><a href="{{ route('public.contact') }}">Contact</a></li>
                    @auth
                        <li>
                            @if(in_array(auth()->user()->role ?? '', ['admin', 'staff']))
                                <a href="{{ route('portal') }}">Portal</a>
                            @else
                                <a href="{{ route('profile.show') }}">My Account</a>
                            @endif
                        </li>
                    @endauth
                </ul>
            </div>

            <div class="kt-footer__col">
                <div class="kt-footer__col-title">Contact</div>
                <ul class="kt-footer__links">
                    <li><a href="tel:+639772443595">0977 244 3595</a></li>
                    <li><a href="mailto:krysandt@gmail.com">krysandt@gmail.com</a></li>
                    <li>CT Building, Jose Romero Road, Bagacay</li>
                    <li><a href="#booking" class="kt-footer__cta-link">> Book Appointment</a></li>
                </ul>
            </div>
        </div>

        <div class="kt-footer__bottom">
            <span class="kt-footer__copy">&copy; {{ date('Y') }} Krys &amp; Tell Dental Center. All rights reserved.</span>
            <div class="kt-footer__legal">
                <a href="{{ route('public.privacy') }}">Privacy Policy</a>
                <a href="{{ route('public.terms') }}">Terms</a>
            </div>
        </div>
    </div>
</footer>
