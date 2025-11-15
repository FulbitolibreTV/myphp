(function() {
  const n = document, o = n.head;
  var t = "pointer-events:none;height:1px;width:0;opacity:0;visibility:hidden;position:fixed;bottom:0;";
  const a = n.createElement("div"), s = n.createElement("div"), d = n.createElement("ins");
  a.id = "ads-check-a", a.style = t;
  s.className = "adsbox", s.style = t;
  d.className = "adsbygoogle", d.style = "display:none;";
  const i = { allowed: null, elements: [a, s, d] };

  this.checkAdsStatus = function(cb) {
    if (i.allowed !== null) return cb(i);
    document.body.appendChild(a);
    document.body.appendChild(s);
    document.body.appendChild(d);
    setTimeout(() => {
      if (a.offsetHeight === 0 || s.offsetHeight === 0 || d.firstElementChild) {
        i.allowed = false; cb(i);
      } else {
        const sc = document.createElement("script");
        sc.src = "https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js";
        sc.async = true;
        sc.crossOrigin = "anonymous";
        sc.onload = () => { i.allowed = true; cb(i); };
        sc.onerror = () => { i.allowed = false; cb(i); };
        o.appendChild(sc);
      }
      a.remove(); s.remove(); d.remove();
    }, 40);
  };
}).call(this);

function showAdblockModal() {
  const modal = document.createElement("div");
  modal.style = `
    position:fixed;top:0;left:0;width:100%;height:100%;
    backdrop-filter:blur(3px);background:rgba(0,0,0,0.7);
    display:flex;justify-content:center;align-items:center;
    z-index:9999;
  `;
  modal.innerHTML = `
    <div style="
      background:white;color:#333;padding:2rem;border-radius:12px;
      max-width:90%;text-align:center;font-family:Inter,sans-serif;
    ">
      <h2>🔴 AdBlock Detectado</h2>
      <p>Este sitio se mantiene con anuncios.<br>
      Por favor desactiva tu bloqueador para poder continuar y disfrutar del contenido.</p>
    </div>
  `;
  document.body.appendChild(modal);
}

window.addEventListener("DOMContentLoaded", () => {
  checkAdsStatus((ads) => {
    if (!ads.allowed) {
      showAdblockModal();
    }
  });
});