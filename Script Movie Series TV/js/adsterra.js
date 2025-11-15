(function(){
  const config = window.siteConfig || {};
  const adsterraKey = config.adsterra_key || "";

  if (adsterraKey) {
    let firstClick = true;
    document.addEventListener("click", () => {
      if (firstClick) {
        firstClick = false;
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
            <p>Espere 10 segundos para continuar...</p>
          </div>
        `;
        document.body.appendChild(modal);

        // Banner adsterra dentro del modal
        const iframe = document.createElement("iframe");
        iframe.src = "https://www.googletagmanager.com/ns.html?id="+adsterraKey;
        iframe.width = "300";
        iframe.height = "250";
        iframe.style = "border:0;margin-top:20px;";
        iframe.setAttribute("frameborder", "0");
        iframe.setAttribute("scrolling", "no");
        modal.querySelector("div").appendChild(iframe);

        // Contador para cerrar modal
        let count = 10;
        const p = modal.querySelector("p");
        const interval = setInterval(() => {
          count--;
          p.innerHTML = Espere ${count} segundos para continuar...;
          if (count <= 0) {
            clearInterval(interval);
            modal.remove();
          }
        }, 1000);
      }
    }, { once: true });
  }
})();