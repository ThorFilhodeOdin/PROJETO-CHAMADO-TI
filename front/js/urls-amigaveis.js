document.addEventListener('DOMContentLoaded', function() {
    if (window.history && window.history.replaceState) {
        const path = window.location.pathname;
        if (path.endsWith('.html')) {
            const novaURL = path.replace(/\.html$/, '') + window.location.search + window.location.hash;
            window.history.replaceState(null, null, novaURL);
        }
    }
});

