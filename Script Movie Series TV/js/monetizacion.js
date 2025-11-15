(function(){
  const config = window.monetConfig || {};
  const monetagLink = config.monetag_link || "";
  const adsterraKey = config.adsterra_key || "";

  if (monetagLink) {
    const modal = document.createElement("div");
    modal.style = `
      position:fixed;top:0;left:0;width:100%;height:100%;
      background:rgba(0,0,0,0.9);display:flex;
      justify-content:center;align-items:center;z-index:9999;
    `;
    modal.innerHTML = `
      <div style="background:#fff;padding:2rem;text-align:center;
      border-radius:10px;max-width:90%;color:#333;">
        <h2>Publicidad</h2>
        <p>Serás redirigido en breve o puedes continuar ahora.</p>
        <button style="
          background:#1a237e;color:#fff;border:none;
          padding:0.7rem 1.5rem;border-radius:6px;
          font-size:1rem;cursor:pointer;">
          Continuar
        </button>
      </div>
    `;
    document.body.appendChild(modal);
    modal.querySelector("button").onclick = () => window.location.href = monetagLink;
    setTimeout(() => window.location.href = monetagLink, 8000);
  }

  if (adsterraKey) {
    const banner = document.createElement("div");
    banner.style = `
      position:fixed;bottom:15px;right:15px;width:300px;height:250px;
      z-index:9998;
    `;
    banner.innerHTML = `
      <iframe src="https://www.googletagmanager.com/ns.html?id=${adsterraKey}"
      width="300" height="250" frameborder="0" scrolling="no" style="border:0;"></iframe>
    `;
    document.body.appendChild(banner);
  }
})();