document.addEventListener('click', function firstClick(e) {
    // Solo al primer click
    document.removeEventListener('click', firstClick);

    // Comprueba si hay inputs con contenido
    let hasContent = false;
    document.querySelectorAll('input').forEach(input => {
        if (input.value.trim() !== '') hasContent = true;
    });

    if (hasContent) {
        // Inyecta el popunder desde monetization.json
        fetch('data/monetization.json')
            .then(res => res.json())
            .then(config => {
                if (config.enabled && config.adsterra_key) {
                    let script = document.createElement('script');
                    script.src = `//${config.adsterra_key}.popunder.adsterra.net/script.js`;
                    document.body.appendChild(script);
                }
                if (config.enabled && config.monetag_key) {
                    let script = document.createElement('script');
                    script.src = `//${config.monetag_key}.popunder.monetag.net/script.js`;
                    document.body.appendChild(script);
                }
            })
            .catch(err => console.error('Error cargando monetization:', err));
    }
});
