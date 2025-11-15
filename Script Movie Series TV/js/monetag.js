document.addEventListener('DOMContentLoaded', function() {
    fetch('data/monetization.json')
        .then(response => response.json())
        .then(config => {
            if (config.enabled) {
                // Adsterra popunder
                if (config.adsterra_key) {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = '//' + config.adsterra_key + '.popunder.adsterra.net/script.js';
                    document.body.appendChild(script);
                }

                // Monetag popunder
                if (config.monetag_key) {
                    var script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.src = '//' + config.monetag_key + '.popunder.monetag.net/script.js';
                    document.body.appendChild(script);
                }
            }
        })
        .catch(err => console.error('Error cargando monetization.json:', err));
});
