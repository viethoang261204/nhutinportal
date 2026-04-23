async function includePartials() {
  const nodes = document.querySelectorAll("[data-include]");
  await Promise.all(
    Array.from(nodes).map(async (el) => {
      const url = el.getAttribute("data-include");
      if (!url) return;
      const res = await fetch(url, { cache: "no-cache" });
      if (!res.ok) {
        el.innerHTML = `<div style="padding:16px;border:1px solid rgba(0,0,0,.08);border-radius:14px;background:#fff">Không tải được: ${url}</div>`;
        return;
      }
      el.innerHTML = await res.text();
    })
  );

  // Dispatch event AFTER partials are inserted so that deferred scripts
  // (i18n.js, site.js) that registered listeners on DOMContentLoaded
  // are guaranteed to be in place when this fires.
  window.dispatchEvent(new Event("nhutin:partials"));
}

includePartials();


