function initCategoryNav(navWrap, nav, placeholder, startSectionId, sectionAttr) {
    const header = document.querySelector('.site-header');

    if (!nav || !navWrap) return;

    const pills    = Array.from(nav.querySelectorAll('.menu-category-pill'));
    const sections = Array.from(document.querySelectorAll('[' + sectionAttr + ']'));

    let navOffsetTop    = 0;
    let isFixed         = false;
    let ticking         = false;
    let observerBlocked = false;
    let blockTimer      = null;

    const headerH = () => header ? header.getBoundingClientRect().height : 0;
    const navH    = () => navWrap.offsetHeight;

    const measure = () => {
        const wasFixed = isFixed;
        if (wasFixed) unfix();
        navOffsetTop = navWrap.getBoundingClientRect().top + window.scrollY;
        if (wasFixed) fix();
    };

    const fix = () => {
        if (isFixed) return;
        isFixed = true;
        navWrap.style.top = headerH() + 'px';
        navWrap.classList.add('is-fixed');
        if (placeholder) {
            placeholder.style.height = navH() + 'px';
            placeholder.classList.add('is-visible');
        }
    };

    const unfix = () => {
        if (!isFixed) return;
        isFixed = false;
        navWrap.style.top = '';
        navWrap.classList.remove('is-fixed');
        if (placeholder) {
            placeholder.style.height = '';
            placeholder.classList.remove('is-visible');
        }
    };

    const syncFixed = () => {
        window.scrollY >= navOffsetTop - headerH() ? fix() : unfix();
    };

    const setActivePill = (targetId) => {
        pills.forEach(p => {
            const active = p.dataset.menuTarget === targetId;
            p.classList.toggle('is-active', active);
            active ? p.setAttribute('aria-current', 'true') : p.removeAttribute('aria-current');
        });
        // kein scrollIntoView nötig da Pills umgebrochen werden (kein horizontales Scrollen)
    };

    pills.forEach(pill => {
        pill.addEventListener('click', e => {
            e.preventDefault();

            const targetId = pill.dataset.menuTarget;
            if (!targetId) return;

            const target = document.getElementById(targetId);
            if (!target) return;

            observerBlocked = true;
            clearTimeout(blockTimer);
            blockTimer = setTimeout(() => { observerBlocked = false; }, 1200);

            setActivePill(targetId);
            fix();

            const offset   = headerH() + navH() + 16;
            const targetY  = target.getBoundingClientRect().top + window.scrollY;
            const scrollTo = Math.max(targetY - offset, 0);

            window.scrollTo({ top: scrollTo, behavior: 'smooth' });
        });
    });

    window.addEventListener('scroll', () => {
        if (ticking) return;
        ticking = true;
        requestAnimationFrame(() => { syncFixed(); ticking = false; });
    }, { passive: true });

    window.addEventListener('resize', () => {
        measure();
        if (isFixed) navWrap.style.top = headerH() + 'px';
        syncFixed();
    });

    const startSection = document.getElementById(startSectionId);
    if (startSection) {
        new IntersectionObserver(
            entries => {
                if (observerBlocked) return;
                if (entries[0].isIntersecting) setActivePill(startSectionId);
            },
            { rootMargin: '-10% 0px -75% 0px' }
        ).observe(startSection);
    }

    if (sections.length) {
        const observer = new IntersectionObserver(
            entries => {
                if (observerBlocked) return;
                const best = entries
                    .filter(e => e.isIntersecting)
                    .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
                if (best?.target?.id) setActivePill(best.target.id);
            },
            { rootMargin: '-15% 0px -65% 0px', threshold: [0.1, 0.25, 0.5] }
        );
        sections.forEach(s => observer.observe(s));
    }

    const init = () => { measure(); syncFixed(); };
    document.readyState === 'complete' ? init() : window.addEventListener('load', init, { once: true });
}

// ── Burger Menu ───────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('.burger-btn');
    const nav    = document.querySelector('.main-navigation');
    if (!burger || !nav) return;

    burger.addEventListener('click', () => {
        const open = burger.getAttribute('aria-expanded') === 'true';
        burger.setAttribute('aria-expanded', String(!open));
        burger.classList.toggle('is-open', !open);
        nav.classList.toggle('is-open', !open);
    });

    // Menü schliessen beim Klick auf einen Link
    nav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            burger.setAttribute('aria-expanded', 'false');
            burger.classList.remove('is-open');
            nav.classList.remove('is-open');
        });
    });

    // Menü schliessen beim Klick ausserhalb
    document.addEventListener('click', e => {
        if (!nav.contains(e.target) && !burger.contains(e.target)) {
            burger.setAttribute('aria-expanded', 'false');
            burger.classList.remove('is-open');
            nav.classList.remove('is-open');
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    // Speisekarte
    const spNavWrap     = document.querySelector('.menu-category-nav-wrap:not(.getraenke-nav-wrap)');
    const spNav         = spNavWrap ? spNavWrap.querySelector('.menu-category-nav') : null;
    const spPlaceholder = document.querySelector('.menu-category-nav-wrap-placeholder:not(.getraenke-placeholder)');
    if (spNavWrap && spNav) {
        initCategoryNav(spNavWrap, spNav, spPlaceholder, 'speisekarte-start', 'data-menu-section');
    }

    // Getränkekarte
    const gtNavWrap     = document.querySelector('.getraenke-nav-wrap');
    const gtNav         = gtNavWrap ? gtNavWrap.querySelector('.menu-category-nav') : null;
    const gtPlaceholder = document.querySelector('.getraenke-placeholder');
    if (gtNavWrap && gtNav) {
        initCategoryNav(gtNavWrap, gtNav, gtPlaceholder, 'getraenke-start', 'data-getraenke-section');
    }
});

// ── Homepage Speisekarte Tabs ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.menu-tab');
    const panels = document.querySelectorAll('.menu-panel');
    if (!tabs.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;

            tabs.forEach(t => {
                t.classList.remove('menu-tab--active');
                t.setAttribute('aria-selected', 'false');
            });
            panels.forEach(p => p.classList.remove('menu-panel--active'));

            tab.classList.add('menu-tab--active');
            tab.setAttribute('aria-selected', 'true');
            const panel = document.querySelector('.menu-panel[data-panel="' + target + '"]');
            if (panel) panel.classList.add('menu-panel--active');
        });
    });
});
