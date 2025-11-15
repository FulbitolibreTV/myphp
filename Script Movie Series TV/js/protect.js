// Bloquear click derecho con mensaje
document.addEventListener('contextmenu', e => {
  e.preventDefault();
  alert("🚫 No está permitido copiar ni inspeccionar este sitio.");
});

// Bloquear selección y arrastre
document.addEventListener('selectstart', e => e.preventDefault());
document.addEventListener('dragstart', e => e.preventDefault());

// Bloquear teclas F12, Ctrl+Shift+I/J, Ctrl+U, Ctrl+S
document.onkeydown = function(e) {
  if (e.keyCode == 123) return false; // F12
  if (e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) ||
                                   e.keyCode == 'J'.charCodeAt(0))) return false;
  if (e.ctrlKey && e.keyCode == 'U'.charCodeAt(0)) return false; // Ctrl+U
  if (e.ctrlKey && e.keyCode == 'S'.charCodeAt(0)) {
    e.preventDefault();
    return false;
  }
};

// Proteger imágenes y videos del clic derecho y arrastre
document.addEventListener("DOMContentLoaded", function(){
  document.querySelectorAll("img, video").forEach(el => {
    el.addEventListener("contextmenu", e => e.preventDefault());
    el.addEventListener("dragstart", e => e.preventDefault());
  });
});