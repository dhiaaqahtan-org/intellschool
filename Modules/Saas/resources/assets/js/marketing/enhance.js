/**
 * Modules/Saas — marketing enhancement layer (framework-free).
 *
 * Everything here is progressive enhancement: the Blade-rendered page is fully
 * readable and operable with this file absent or broken. Only transform/opacity
 * are animated, and every effect is disabled under prefers-reduced-motion.
 */

const REDUCED = window.matchMedia('(prefers-reduced-motion: reduce)');
const COARSE = window.matchMedia('(pointer: coarse)');

/* -------------------------------------------------------------------------
 * Scroll reveal — one shared IntersectionObserver for the whole page.
 * Adds .is-inview, which drives .reveal, .chart .bar, .heat i, .iso-layer.
 * ---------------------------------------------------------------------- */
function initReveal() {
  const targets = document.querySelectorAll('.reveal, [data-inview]');
  if (!targets.length) return;

  if (!('IntersectionObserver' in window)) {
    targets.forEach((el) => el.classList.add('is-inview'));
    return;
  }

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-inview');
        io.unobserve(entry.target);
      });
    },
    { rootMargin: '0px 0px -12% 0px', threshold: 0.12 }
  );

  targets.forEach((el) => {
    // Stagger siblings without hand-authoring a delay on every element.
    if (!el.style.getPropertyValue('--i')) {
      const sibs = el.parentElement ? [...el.parentElement.children] : [];
      el.style.setProperty('--i', String(Math.min(sibs.indexOf(el), 8) || 0));
    }
    io.observe(el);
  });

  // Safety net: if the observer never fires (embedded/offscreen renderers,
  // print-to-PDF, exotic webviews), content must not stay invisible.
  window.setTimeout(() => {
    if (document.querySelector('.is-inview')) return;
    targets.forEach((el) => el.classList.add('is-inview'));
  }, 2500);
}

/* -------------------------------------------------------------------------
 * Index children of staggered collections (bars, heat cells, iso layers).
 * ---------------------------------------------------------------------- */
function initStaggerIndexes() {
  document.querySelectorAll('.chart, .heat, .iso-vis__stack').forEach((group) => {
    [...group.children].forEach((child, i) => child.style.setProperty('--i', String(i)));
  });
}

/* -------------------------------------------------------------------------
 * Pointer parallax for the 3D product stage.
 * Writes --rx/--ry on .stage__scene; CSS owns the actual transform so the
 * effect vanishes cleanly at mobile breakpoints and under reduced motion.
 * ---------------------------------------------------------------------- */
function initStageParallax() {
  const stages = document.querySelectorAll('.stage');
  if (!stages.length || REDUCED.matches || COARSE.matches) return;

  stages.forEach((stage) => {
    const scene = stage.querySelector('.stage__scene');
    if (!scene) return;

    let raf = 0;
    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;

    const MAX = 5; // degrees — clamped so panels never leave their hit box

    const tick = () => {
      currentX += (targetX - currentX) * 0.08;
      currentY += (targetY - currentY) * 0.08;
      scene.style.setProperty('--rx', `${currentX.toFixed(3)}deg`);
      scene.style.setProperty('--ry', `${currentY.toFixed(3)}deg`);

      if (Math.abs(targetX - currentX) > 0.01 || Math.abs(targetY - currentY) > 0.01) {
        raf = requestAnimationFrame(tick);
      } else {
        raf = 0;
      }
    };

    const onMove = (e) => {
      const r = stage.getBoundingClientRect();
      targetX = -((e.clientY - r.top) / r.height - 0.5) * 2 * MAX;
      targetY = ((e.clientX - r.left) / r.width - 0.5) * 2 * MAX;
      if (!raf) raf = requestAnimationFrame(tick);
    };

    const onLeave = () => {
      targetX = 0;
      targetY = 0;
      if (!raf) raf = requestAnimationFrame(tick);
    };

    stage.addEventListener('pointermove', onMove);
    stage.addEventListener('pointerleave', onLeave);
    // Scene transition is only wanted on release, not during tracking.
    stage.addEventListener('pointerenter', () => { scene.style.transitionDuration = '0ms'; });
    stage.addEventListener('pointerleave', () => { scene.style.transitionDuration = ''; });
  });
}

/* -------------------------------------------------------------------------
 * Hero backdrop — a cheap 2D particle/constellation field.
 * Deliberately not WebGL: this must not compete with LCP.
 * ---------------------------------------------------------------------- */
function initHeroCanvas() {
  const canvas = document.querySelector('.hero__canvas');
  if (!canvas || REDUCED.matches) return;

  const ctx = canvas.getContext('2d', { alpha: true });
  if (!ctx) return;

  let w = 0;
  let h = 0;
  let dpr = 1;
  let nodes = [];
  let raf = 0;
  let running = true;

  const resize = () => {
    const r = canvas.getBoundingClientRect();
    if (r.width < 1 || r.height < 1) return false; // not laid out yet

    dpr = Math.min(window.devicePixelRatio || 1, 2);
    w = r.width;
    h = r.height;
    canvas.width = Math.round(w * dpr);
    canvas.height = Math.round(h * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const count = Math.min(64, Math.round((w * h) / 22000));
    nodes = Array.from({ length: count }, () => ({
      x: Math.random() * w,
      y: Math.random() * h,
      vx: (Math.random() - 0.5) * 0.16,
      vy: (Math.random() - 0.5) * 0.16,
      r: Math.random() * 1.5 + 0.6,
    }));

    return true;
  };

  const draw = () => {
    ctx.clearRect(0, 0, w, h);

    for (let i = 0; i < nodes.length; i++) {
      const a = nodes[i];
      a.x += a.vx;
      a.y += a.vy;
      if (a.x < 0 || a.x > w) a.vx *= -1;
      if (a.y < 0 || a.y > h) a.vy *= -1;

      for (let j = i + 1; j < nodes.length; j++) {
        const b = nodes[j];
        const dx = a.x - b.x;
        const dy = a.y - b.y;
        const d2 = dx * dx + dy * dy;
        if (d2 < 20000) {
          ctx.globalAlpha = (1 - d2 / 20000) * 0.16;
          ctx.strokeStyle = '#93c5fd';
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(a.x, a.y);
          ctx.lineTo(b.x, b.y);
          ctx.stroke();
        }
      }

      ctx.globalAlpha = 0.5;
      ctx.fillStyle = '#bfdbfe';
      ctx.beginPath();
      ctx.arc(a.x, a.y, a.r, 0, Math.PI * 2);
      ctx.fill();
    }

    ctx.globalAlpha = 1;
    if (running) raf = requestAnimationFrame(draw);
  };

  // The hero may not have a box on the first frame (fonts, late layout).
  /*
   * Size the canvas and start rendering.
   *
   * This is called from several places on purpose. The hero has no height
   * until the stylesheet applies, and with a module script the DOM is often
   * ready before the CSS is — so the first attempt legitimately measures 0x0.
   * Every path that could be the one where a real size first exists has to be
   * able to start the loop, otherwise the canvas sizes correctly but nothing
   * ever paints into it.
   */
  const sizeAndStart = () => {
    if (!resize()) return false;
    if (!raf && running) raf = requestAnimationFrame(draw);

    return true;
  };

  sizeAndStart();

  if ('ResizeObserver' in window) {
    // Fires once on observe, and again when the stylesheet gives the hero its
    // real height — which is usually the attempt that actually succeeds.
    new ResizeObserver(() => sizeAndStart()).observe(canvas);
  } else {
    let resizeTimer = 0;
    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = window.setTimeout(sizeAndStart, 180);
    });
  }

  // Backstops. A module script can execute before its stylesheet has applied,
  // in which case the hero has no height and the first measurement is 0x0.
  // These are cheap and idempotent — sizeAndStart() is a no-op once the canvas
  // already matches its box — so it is better to have several than to depend
  // on any single one winning the race.
  if (document.readyState !== 'complete') {
    window.addEventListener('load', sizeAndStart, { once: true });
  }

  if (document.fonts?.ready) {
    document.fonts.ready.then(sizeAndStart).catch(() => {});
  }

  requestAnimationFrame(() => requestAnimationFrame(sizeAndStart));

  // Stop burning frames when the hero scrolls away or the tab is hidden.
  if ('IntersectionObserver' in window) {
    new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        running = entry.isIntersecting && !document.hidden;
        if (running && !raf) raf = requestAnimationFrame(draw);
        if (!running && raf) { cancelAnimationFrame(raf); raf = 0; }
      });
    }).observe(canvas);
  }

  document.addEventListener('visibilitychange', () => {
    running = !document.hidden;
    if (running && !raf) raf = requestAnimationFrame(draw);
  });
}

/* -------------------------------------------------------------------------
 * Count-up for the hero fact strip. Reads the final value from the DOM so the
 * server-rendered number is always the source of truth.
 * ---------------------------------------------------------------------- */
function initCounters() {
  const els = document.querySelectorAll('[data-count]');
  if (!els.length) return;
  if (REDUCED.matches || !('IntersectionObserver' in window)) return;

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      const el = entry.target;
      io.unobserve(el);

      const final = el.textContent.trim();
      const target = parseFloat(final.replace(/[^\d.]/g, ''));
      if (!Number.isFinite(target)) return;

      const suffix = final.replace(/[\d.,]/g, '');
      const start = performance.now();
      const dur = 1100;

      const step = (now) => {
        const t = Math.min((now - start) / dur, 1);
        const eased = 1 - Math.pow(1 - t, 3);
        const value = Math.round(target * eased);
        el.textContent = value.toLocaleString(document.documentElement.lang || 'en') + suffix;
        if (t < 1) requestAnimationFrame(step);
        else el.textContent = final; // restore exact server-rendered string
      };

      el.textContent = '0' + suffix;
      requestAnimationFrame(step);
    });
  }, { threshold: 0.5 });

  els.forEach((el) => io.observe(el));
}

/* -------------------------------------------------------------------------
 * Donut charts — animate the --p custom property once in view.
 * ---------------------------------------------------------------------- */
function initDonuts() {
  const els = document.querySelectorAll('.donut[data-p]');
  if (!els.length) return;

  const apply = (el) => el.style.setProperty('--p', el.dataset.p);

  if (REDUCED.matches || !('IntersectionObserver' in window)) {
    els.forEach(apply);
    return;
  }

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      apply(entry.target);
      io.unobserve(entry.target);
    });
  }, { threshold: 0.4 });

  els.forEach((el) => io.observe(el));
}

/* -------------------------------------------------------------------------
 * Sticky header state + mobile navigation.
 * ---------------------------------------------------------------------- */
function initHeader() {
  const header = document.querySelector('.site-header');
  if (header) {
    const onScroll = () => header.classList.toggle('is-stuck', window.scrollY > 12);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  const toggle = document.querySelector('[data-nav-toggle]');
  const panel = document.querySelector('[data-nav-panel]');
  if (!toggle || !panel) return;

  const setOpen = (open) => {
    toggle.setAttribute('aria-expanded', String(open));
    panel.hidden = !open;
  };

  setOpen(false);
  toggle.addEventListener('click', () => setOpen(panel.hidden));
  panel.addEventListener('click', (e) => {
    if (e.target.closest('a')) setOpen(false);
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !panel.hidden) {
      setOpen(false);
      toggle.focus();
    }
  });
}

/* -------------------------------------------------------------------------
 * Tabs — used by the role explorer when Vue is unavailable. Full roving
 * arrow-key support per the WAI-ARIA tabs pattern, RTL aware.
 * ---------------------------------------------------------------------- */
function initTabs() {
  document.querySelectorAll('[role="tablist"]').forEach((list) => {
    const tabs = [...list.querySelectorAll('[role="tab"]')];
    if (!tabs.length) return;

    const rtl = document.documentElement.dir === 'rtl';

    const select = (tab, focus = true) => {
      tabs.forEach((t) => {
        const on = t === tab;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        const panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.hidden = !on;
      });
      if (focus) tab.focus();
    };

    tabs.forEach((tab) => {
      tab.addEventListener('click', () => select(tab, false));
      tab.addEventListener('keydown', (e) => {
        const i = tabs.indexOf(tab);
        const fwd = rtl ? 'ArrowLeft' : 'ArrowRight';
        const back = rtl ? 'ArrowRight' : 'ArrowLeft';
        let next = null;

        if (e.key === fwd || e.key === 'ArrowDown') next = tabs[(i + 1) % tabs.length];
        else if (e.key === back || e.key === 'ArrowUp') next = tabs[(i - 1 + tabs.length) % tabs.length];
        else if (e.key === 'Home') next = tabs[0];
        else if (e.key === 'End') next = tabs[tabs.length - 1];

        if (next) {
          e.preventDefault();
          select(next);
        }
      });
    });

    select(tabs.find((t) => t.getAttribute('aria-selected') === 'true') || tabs[0], false);
  });
}

/* -------------------------------------------------------------------------
 * Progressive form safeguards for server-rendered forms.
 * ---------------------------------------------------------------------- */
function initProtectedForms() {
  const reset = (form) => {
    const button = form.querySelector('[data-submit-button]');
    if (!button) return;
    button.disabled = false;
    button.removeAttribute('aria-busy');
    if (button.dataset.defaultLabel) button.textContent = button.dataset.defaultLabel;
  };

  document.querySelectorAll('[data-submit-guard]').forEach((form) => {
    const button = form.querySelector('[data-submit-button]');
    if (!button) return;
    button.dataset.defaultLabel = button.textContent.trim();

    form.addEventListener('submit', () => {
      if (button.disabled) return;
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      button.textContent = button.dataset.pendingLabel || button.dataset.defaultLabel;
    });
  });

  const summary = document.querySelector('[data-error-summary]');
  if (summary) summary.focus({ preventScroll: true });

  window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-submit-guard]').forEach(reset);
  });
}

/* ----------------------------------------------------------------------
 * No `export` on purpose: this file must load both as a Vite side-effect
 * import and as a plain <script> in preview/index.html opened over file://
 * (where ES modules are blocked by CORS).
 * ---------------------------------------------------------------------- */
function boot() {
  document.documentElement.classList.remove('no-js');
  initStaggerIndexes();
  initReveal();
  initStageParallax();
  initHeroCanvas();
  initCounters();
  initDonuts();
  initHeader();
  initTabs();
  initProtectedForms();
}

window.SaasMarketing = { boot };

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
  boot();
}
