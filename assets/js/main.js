document.addEventListener('DOMContentLoaded', function () {
    console.log('QueueLess home page loaded');
    document.body.classList.add('js-enhanced');

    var navToggle = document.querySelector('[data-nav-toggle]');
    var navMenu = document.querySelector('[data-nav-menu]');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function () {
            var isOpen = navMenu.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', String(isOpen));
            document.body.classList.toggle('nav-open', isOpen);
        });

        navMenu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navMenu.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
                document.body.classList.remove('nav-open');
            });
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        var targetSelector = button.getAttribute('data-password-toggle');
        var input = targetSelector ? document.querySelector(targetSelector) : null;

        if (!input) {
            return;
        }

        button.addEventListener('click', function () {
            var visible = input.getAttribute('type') === 'text';

            input.setAttribute('type', visible ? 'password' : 'text');
            button.textContent = visible ? 'Afficher' : 'Masquer';
            button.setAttribute('aria-pressed', String(!visible));
        });
    });

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.setAttribute('novalidate', 'novalidate');

        form.addEventListener('submit', function (event) {
            if (form.checkValidity()) {
                return;
            }

            event.preventDefault();

            var firstInvalidField = form.querySelector(':invalid');

            if (firstInvalidField) {
                firstInvalidField.focus();
            }
        });
    });

    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (event) {
            var message = element.getAttribute('data-confirm') || 'Confirmer cette action ?';

            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('[data-filter-input]').forEach(function (input) {
        var targetSelector = input.getAttribute('data-filter-input');
        var target = targetSelector ? document.querySelector(targetSelector) : null;

        if (!target) {
            return;
        }

        var items = Array.prototype.slice.call(target.querySelectorAll('[data-filter-item]'));

        input.addEventListener('input', function () {
            var search = input.value.trim().toLowerCase();

            items.forEach(function (item) {
                var searchText = (item.getAttribute('data-search-text') || item.textContent || '').toLowerCase();
                item.hidden = search !== '' && searchText.indexOf(search) === -1;
            });
        });
    });

    var revealElements = Array.prototype.slice.call(document.querySelectorAll('[data-reveal]'));

    revealElements.forEach(function (element) {
        var delay = Number(element.getAttribute('data-delay') || 0);
        element.style.setProperty('--reveal-delay', String(delay) + 'ms');
    });

    if ('IntersectionObserver' in window && revealElements.length > 0) {
        var revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.18
        });

        revealElements.forEach(function (element) {
            revealObserver.observe(element);
        });
    } else {
        revealElements.forEach(function (element) {
            element.classList.add('is-visible');
        });
    }

    var countElements = Array.prototype.slice.call(document.querySelectorAll('[data-countup]'));

    if ('IntersectionObserver' in window && countElements.length > 0) {
        countElements.forEach(function (element) {
            element.textContent = '0';
        });

        var countObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                var element = entry.target;
                var targetValue = Number(element.getAttribute('data-countup') || 0);
                var duration = 1100;
                var startTime = null;

                if (element.getAttribute('data-counted') === 'true') {
                    observer.unobserve(element);
                    return;
                }

                element.setAttribute('data-counted', 'true');

                var animate = function (timestamp) {
                    if (!startTime) {
                        startTime = timestamp;
                    }

                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    var currentValue = Math.floor(progress * targetValue);

                    element.textContent = currentValue.toLocaleString();

                    if (progress < 1) {
                        window.requestAnimationFrame(animate);
                        return;
                    }

                    element.textContent = targetValue.toLocaleString();
                };

                window.requestAnimationFrame(animate);
                observer.unobserve(element);
            });
        }, {
            threshold: 0.4
        });

        countElements.forEach(function (element) {
            countObserver.observe(element);
        });
    } else {
        countElements.forEach(function (element) {
            var targetValue = Number(element.getAttribute('data-countup') || 0);
            element.textContent = targetValue.toLocaleString();
        });
    }
});
