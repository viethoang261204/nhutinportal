// UI interactions + effects for NHUTIN site

function qs(sel, root = document) {
  return root.querySelector(sel);
}

function qsa(sel, root = document) {
  return Array.from(root.querySelectorAll(sel));
}

// Mobile menu
function initNav() {
  // Prevent double-binding
  if (document.documentElement.dataset.navInit === "1") return;

  const btn = qs("[data-nav-toggle]");
  const panel = qs("[data-nav-panel]");
  if (!btn || !panel) return;

  const close = () => {
    panel.classList.remove("is-open");
    btn.setAttribute("aria-expanded", "false");
  };

  btn.addEventListener("click", () => {
    const open = !panel.classList.contains("is-open");
    panel.classList.toggle("is-open");
    btn.setAttribute("aria-expanded", String(open));
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") close();
  });

  document.addEventListener("click", (e) => {
    if (!panel.classList.contains("is-open")) return;
    if (panel.contains(e.target) || btn.contains(e.target)) return;
    close();
  });

  document.documentElement.dataset.navInit = "1";
}

// Scroll reveal
function initReveal() {
  if (document.documentElement.dataset.revealInit === "1") return;
  const els = qsa("[data-reveal]");
  if (!els.length) return;

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-visible");
        io.unobserve(entry.target);
      });
    },
    { threshold: 0.12 }
  );

  els.forEach((el) => io.observe(el));
  document.documentElement.dataset.revealInit = "1";
}

// Counter animation
function initCounters() {
  if (document.documentElement.dataset.counterInit === "1") return;
  const counters = qsa("[data-count-to]");
  if (!counters.length) return;

  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const el = entry.target;
        io.unobserve(el);

        const to = Number(el.getAttribute("data-count-to") || "0");
        const suffix = el.getAttribute("data-count-suffix") || "";
        const duration = Number(el.getAttribute("data-count-duration") || "1100");

        const start = performance.now();
        const from = 0;

        const tick = (now) => {
          const t = Math.min(1, (now - start) / duration);
          const eased = 1 - Math.pow(1 - t, 3);
          const val = Math.round(from + (to - from) * eased);
          el.textContent = `${val}${suffix}`;
          if (t < 1) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
      });
    },
    { threshold: 0.35 }
  );

  counters.forEach((el) => io.observe(el));
  document.documentElement.dataset.counterInit = "1";
}

// Smooth scroll
function initSmoothAnchors() {
  if (document.documentElement.dataset.smoothInit === "1") return;
  document.addEventListener("click", (e) => {
    const a = e.target.closest("a[href^='#']");
    if (!a) return;
    const href = a.getAttribute("href");
    if (!href || href === "#") return;
    const el = qs(href);
    if (!el) return;
    e.preventDefault();
    el.scrollIntoView({ behavior: "smooth", block: "start" });
    history.pushState(null, "", href);
  });
  document.documentElement.dataset.smoothInit = "1";
}

// Floating actions
function initFab() {
  if (document.documentElement.dataset.fabInit === "1") return;
  const toTop = qs("[data-to-top]");
  if (toTop) {
    const onScroll = () => {
      const show = window.scrollY > 800;
      toTop.classList.toggle("is-show", show);
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
    toTop.addEventListener("click", (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }
  document.documentElement.dataset.fabInit = "1";
}

function initYear() {
  const y = qs("[data-year]");
  if (y) y.textContent = String(new Date().getFullYear());
}

// Contact mailto helper
function initContact() {
  if (document.documentElement.dataset.contactInit === "1") return;
  const btn = qs("[data-contact-send]");
  if (!btn) return;
  btn.addEventListener("click", () => {
    const name = qs("#name")?.value || "";
    const phone = qs("#phone")?.value || "";
    const email = qs("#email")?.value || "";
    const service = qs("#service")?.value || qs("#topic")?.value || "";
    const message = qs("#message")?.value || "";

    const subject = encodeURIComponent("[NHUTIN] Yêu cầu tư vấn / báo giá");
    const body = encodeURIComponent(
      `Họ và tên: ${name}\nSĐT: ${phone}\nEmail: ${email}\nNhu cầu: ${service}\n\nTin nhắn:\n${message}\n`
    );
    window.location.href = `mailto:nhutincorp@gmail.com?subject=${subject}&body=${body}`;
  });
  document.documentElement.dataset.contactInit = "1";
}

function boot() {
  initNav();
  initReveal();
  initCounters();
  initSmoothAnchors();
  initFab();
  initYear();
  initContact();
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", boot);
} else {
  boot();
}

// If navbar/footer are injected after DOM load, re-run boot (idempotent guards apply)
window.addEventListener("nhutin:partials", boot);


