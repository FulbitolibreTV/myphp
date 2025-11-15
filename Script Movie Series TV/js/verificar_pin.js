document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("formPin");
  const respuesta = document.getElementById("respuesta");

  form.addEventListener("submit", async function (e) {
    e.preventDefault();
    const pin = document.getElementById("pinInput").value.trim();

    if (!pin) {
      respuesta.innerHTML = "⚠️ Debes ingresar un PIN.";
      return;
    }

    try {
      const res = await fetch("/components/verificar_pin.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `pin=${encodeURIComponent(pin)}`
      });

      const data = await res.json();

      if (data.status === "ok") {
        respuesta.innerHTML = `✅ Acceso permitido para ${data.nombre}. Días restantes: ${data.dias_restantes}`;
        // Aquí podrías redirigir si quieres: window.location.href = "panel.php";
      } else {
        respuesta.innerHTML = `❌ ${data.mensaje}`;
      }
    } catch (err) {
      console.error("Error:", err);
      respuesta.innerHTML = "❌ Error de conexión.";
    }
  });
});
