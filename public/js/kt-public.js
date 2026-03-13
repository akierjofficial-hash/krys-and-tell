(function () {
    'use strict';

    window.addEventListener('load', function () {
        setTimeout(function () {
            var loader = document.getElementById('ktLoader');
            if (loader) {
                loader.classList.add('kt-loader--hidden');
            }
        }, 1500);
    });

    var nav = document.getElementById('ktNav');
    var navToggle = document.getElementById('ktNavToggle');
    var navDrawer = document.getElementById('ktNavDrawer');

    window.addEventListener('scroll', function () {
        if (!nav) {
            return;
        }
        nav.classList.toggle('kt-nav--scrolled', window.scrollY > 60);
    }, { passive: true });

    function closeDrawer() {
        if (!navDrawer || !navToggle) {
            return;
        }
        navDrawer.classList.remove('kt-nav__drawer--open');
        navDrawer.setAttribute('hidden', 'hidden');
        navToggle.classList.remove('kt-nav__toggle--open');
        navToggle.setAttribute('aria-label', 'Open menu');
        navToggle.setAttribute('aria-expanded', 'false');
        navDrawer.setAttribute('aria-hidden', 'true');
    }

    if (navToggle && navDrawer) {
        navToggle.addEventListener('click', function () {
            var open = navDrawer.classList.toggle('kt-nav__drawer--open');
            if (open) {
                navDrawer.removeAttribute('hidden');
            } else {
                navDrawer.setAttribute('hidden', 'hidden');
            }
            navToggle.classList.toggle('kt-nav__toggle--open', open);
            navToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            navDrawer.setAttribute('aria-hidden', open ? 'false' : 'true');
        });

        navDrawer.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeDrawer);
        });

        document.addEventListener('click', function (event) {
            if (!nav || !navDrawer || !navToggle) {
                return;
            }
            if (nav.contains(event.target) || navDrawer.contains(event.target)) {
                return;
            }
            closeDrawer();
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                closeDrawer();
            }
        });
    }

    var profileToggle = document.getElementById('ktProfileToggle');
    var profileMenu = document.getElementById('ktProfileMenu');

    function closeProfileMenu() {
        if (!profileMenu || !profileToggle) {
            return;
        }
        profileMenu.classList.remove('kt-nav__dropdown--open');
        profileToggle.setAttribute('aria-expanded', 'false');
    }

    if (profileToggle && profileMenu) {
        profileToggle.addEventListener('click', function (event) {
            event.preventDefault();
            var open = profileMenu.classList.toggle('kt-nav__dropdown--open');
            profileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (profileToggle.contains(event.target) || profileMenu.contains(event.target)) {
                return;
            }
            closeProfileMenu();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeProfileMenu();
                closeDrawer();
            }
        });
    }

    var revealSelectors = [
        '.kt-reveal',
        '.kt-reveal-left',
        '.kt-reveal-right',
        '.kt-service-row',
        '.kt-testi-card',
        '.kt-about__feature'
    ].join(', ');

    var revealElements = document.querySelectorAll(revealSelectors);

    if ('IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('kt-visible');
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -32px 0px' });

        revealElements.forEach(function (element) {
            revealObserver.observe(element);
        });
    } else {
        revealElements.forEach(function (element) {
            element.classList.add('kt-visible');
        });
    }

    document.querySelectorAll('.kt-service-row').forEach(function (element, index) {
        element.style.transitionDelay = (index * 0.07) + 's';
    });

    document.querySelectorAll('.kt-about__feature').forEach(function (element, index) {
        element.style.transitionDelay = (index * 0.1) + 's';
    });

    document.querySelectorAll('.kt-testi-card').forEach(function (element, index) {
        element.style.transitionDelay = (index * 0.12) + 's';
    });

    document.querySelectorAll('a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var id = this.getAttribute('href');
            if (id === '#') {
                return;
            }

            var target = document.querySelector(id);
            if (!target) {
                return;
            }

            event.preventDefault();
            var navHeight = nav ? nav.offsetHeight : 0;
            var top = target.getBoundingClientRect().top + window.pageYOffset - navHeight - 16;
            window.scrollTo({ top: top, behavior: 'smooth' });
        });
    });

    var statsSection = document.querySelector('.kt-hero__stats');
    if (statsSection && 'IntersectionObserver' in window) {
        var statObserver = new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting) {
                return;
            }

            statObserver.disconnect();

            document.querySelectorAll('.kt-stat__num').forEach(function (element) {
                var target = parseFloat(element.dataset.target || element.textContent) || 0;
                var suffix = element.dataset.suffix || '';
                var start = performance.now();
                var duration = 1800;

                function tick(now) {
                    var progress = Math.min((now - start) / duration, 1);
                    var ease = 1 - Math.pow(1 - progress, 3);
                    var value = Math.floor(ease * target);
                    element.innerHTML = value + '<em>' + suffix + '</em>';
                    if (progress < 1) {
                        requestAnimationFrame(tick);
                    }
                }

                requestAnimationFrame(tick);
            });
        }, { threshold: 0.5 });

        statObserver.observe(statsSection);
    }

    var bookingForm = document.getElementById('ktBookingForm');
    if (!bookingForm) {
        return;
    }

    var serviceSelect = document.getElementById('kt_service_id');
    var dateInput = document.getElementById('kt_date');
    var doctorSelect = document.getElementById('kt_doctor_id');
    var timeSelect = document.getElementById('kt_time');
    var timeHelp = document.getElementById('kt_time_help');
    var walkInInput = document.getElementById('kt_request_walkin');
    var bookingBase = bookingForm.getAttribute('data-book-base') || '/book';

    var oldTime = (dateInput && dateInput.dataset.oldTime) ? dateInput.dataset.oldTime : '';
    var oldDoctorId = (dateInput && dateInput.dataset.oldDoctorId) ? dateInput.dataset.oldDoctorId : '';
    var appliedOldTime = false;
    var appliedOldDoctor = false;

    function selectedServiceOption() {
        if (!serviceSelect) {
            return null;
        }
        return serviceSelect.options[serviceSelect.selectedIndex] || null;
    }

    function selectedServiceId() {
        return serviceSelect ? serviceSelect.value : '';
    }

    function selectedServiceIsWalkIn() {
        var option = selectedServiceOption();
        if (!option) {
            return false;
        }
        return option.getAttribute('data-walkin') === '1';
    }

    function setFormAction() {
        var serviceId = selectedServiceId();
        if (!serviceId) {
            bookingForm.setAttribute('action', window.location.href);
            return;
        }
        bookingForm.setAttribute('action', bookingBase + '/' + serviceId);
    }

    function setTimeMessage(message) {
        if (timeHelp) {
            timeHelp.textContent = message;
        }
    }

    function resetDoctors(message) {
        if (!doctorSelect) {
            return;
        }
        doctorSelect.innerHTML = '<option value="">' + (message || 'Select dentist') + '</option>';
        doctorSelect.required = false;
    }

    function resetTime(message) {
        if (!timeSelect) {
            return;
        }
        timeSelect.innerHTML = '<option value="">' + (message || 'Select date, service, and dentist first') + '</option>';
        timeSelect.disabled = false;
        timeSelect.required = !selectedServiceIsWalkIn();
    }

    function setWalkInMode(enabled, message) {
        var serviceWalkIn = selectedServiceIsWalkIn();
        if (walkInInput) {
            walkInInput.value = (enabled && !serviceWalkIn) ? '1' : '0';
        }
        if (!timeSelect) {
            return;
        }

        if (enabled) {
            timeSelect.innerHTML = '<option value="">Walk-in service: no slot required</option>';
            timeSelect.value = '';
            timeSelect.disabled = true;
            timeSelect.required = false;
            setTimeMessage(message || 'This service is processed as walk-in. Submit your request and staff will confirm details.');
            return;
        }

        timeSelect.disabled = false;
        timeSelect.required = true;
    }

    function populateDoctors(doctors) {
        resetDoctors('Select dentist');

        if (!doctorSelect) {
            return;
        }

        if (!Array.isArray(doctors) || doctors.length === 0) {
            doctorSelect.required = false;
            return;
        }

        var availableCount = 0;

        doctors.forEach(function (doctor) {
            if (!doctor || !doctor.id) {
                return;
            }

            var option = document.createElement('option');
            option.value = String(doctor.id);
            option.textContent = doctor.name || ('Doctor #' + doctor.id);

            if (doctor.available === false) {
                option.disabled = true;
                option.textContent += ' (Unavailable)';
            } else {
                availableCount += 1;
            }

            doctorSelect.appendChild(option);
        });

        if (!appliedOldDoctor && oldDoctorId) {
            var candidate = Array.from(doctorSelect.options).find(function (option) {
                return option.value === String(oldDoctorId) && !option.disabled;
            });
            if (candidate) {
                doctorSelect.value = candidate.value;
            }
            appliedOldDoctor = true;
        }

        doctorSelect.required = availableCount > 0;
    }

    function populateSlots(slots) {
        if (!timeSelect) {
            return;
        }

        if (!Array.isArray(slots) || slots.length === 0) {
            resetTime('No available slots');
            timeSelect.disabled = true;
            timeSelect.required = false;
            return;
        }

        timeSelect.innerHTML = '<option value="">Select time</option>';
        slots.forEach(function (slot) {
            var option = document.createElement('option');
            option.value = slot;
            option.textContent = slot;
            timeSelect.appendChild(option);
        });

        if (!appliedOldTime && oldTime) {
            var old = Array.from(timeSelect.options).find(function (option) {
                return option.value === oldTime;
            });
            if (old) {
                timeSelect.value = oldTime;
            }
            appliedOldTime = true;
        }

        timeSelect.disabled = false;
        timeSelect.required = true;
        setTimeMessage(slots.length + ' slot(s) available.');
    }

    function fetchJson(url) {
        return fetch(url, {
            headers: {
                Accept: 'application/json'
            }
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed');
            }
            return response.json();
        });
    }

    function loadDoctors() {
        var serviceId = selectedServiceId();
        var dateValue = dateInput ? dateInput.value : '';

        if (!serviceId || !dateValue) {
            resetDoctors('Select date first');
            resetTime('Select date, service, and dentist first');
            setTimeMessage('Slots are loaded based on your date and dentist.');
            return Promise.resolve();
        }

        setWalkInMode(false);
        resetDoctors('Loading dentists...');

        return fetchJson(bookingBase + '/' + serviceId + '/doctors?date=' + encodeURIComponent(dateValue))
            .then(function (payload) {
                populateDoctors(payload && payload.doctors ? payload.doctors : []);
            })
            .catch(function () {
                resetDoctors('Unable to load dentists');
            });
    }

    function loadSlots() {
        var serviceId = selectedServiceId();
        var dateValue = dateInput ? dateInput.value : '';

        if (!serviceId || !dateValue) {
            resetTime('Select date, service, and dentist first');
            setTimeMessage('Slots are loaded based on your date and dentist.');
            return;
        }

        if (doctorSelect && doctorSelect.required && !doctorSelect.value) {
            resetTime('Select dentist first');
            timeSelect.disabled = true;
            setTimeMessage('Please choose an available dentist.');
            return;
        }

        if (selectedServiceIsWalkIn()) {
            setWalkInMode(true);
            return;
        }

        setWalkInMode(false);
        resetTime('Loading available times...');
        timeSelect.disabled = true;

        var url = bookingBase + '/' + serviceId + '/slots?date=' + encodeURIComponent(dateValue);
        if (doctorSelect && doctorSelect.value) {
            url += '&doctor_id=' + encodeURIComponent(doctorSelect.value);
        }

        fetchJson(url)
            .then(function (payload) {
                if (payload && payload.meta && payload.meta.walk_in) {
                    setWalkInMode(true, 'Walk-in schedule detected for this service.');
                    return;
                }

                if (payload && payload.meta && payload.meta.doctor_unavailable && payload.meta.doctor_unavailable_reason) {
                    setTimeMessage('Selected dentist is unavailable: ' + payload.meta.doctor_unavailable_reason);
                }

                populateSlots(payload && payload.slots ? payload.slots : []);
            })
            .catch(function () {
                resetTime('Unable to load slots');
                setTimeMessage('Please choose another date or try again.');
            });
    }

    function refreshBookingData() {
        setFormAction();
        loadDoctors().then(loadSlots);
    }

    if (serviceSelect) {
        serviceSelect.addEventListener('change', function () {
            appliedOldDoctor = true;
            appliedOldTime = true;
            refreshBookingData();
        });
    }

    if (dateInput) {
        dateInput.addEventListener('change', function () {
            appliedOldDoctor = true;
            appliedOldTime = true;
            refreshBookingData();
        });
    }

    if (doctorSelect) {
        doctorSelect.addEventListener('change', function () {
            appliedOldTime = true;
            loadSlots();
        });
    }

    bookingForm.addEventListener('submit', function (event) {
        var serviceId = selectedServiceId();
        if (!serviceId) {
            event.preventDefault();
            setTimeMessage('Please select a service first.');
            return;
        }

        if (!selectedServiceIsWalkIn() && timeSelect && (timeSelect.disabled || !timeSelect.value)) {
            event.preventDefault();
            setTimeMessage('Please select an available time slot before submitting.');
        }
    });

    setFormAction();
    refreshBookingData();
})();
