<script>
(function () {
    var input    = document.getElementById('{{ $inputId }}');
    var dropdown = document.getElementById('{{ $dropdownId }}');
    var geoBtn   = document.getElementById('{{ $btnId }}');
    var geoLabel = document.getElementById('{{ $labelId }}');
    if (!input || !dropdown || !geoBtn) return;

    input.addEventListener('focus', function () { dropdown.classList.remove('hidden'); });
    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && e.target !== input) dropdown.classList.add('hidden');
    });

    geoBtn.addEventListener('click', function () {
        dropdown.classList.add('hidden');
        if (!navigator.geolocation) { input.placeholder = 'Geolocalização não suportada'; return; }
        geoLabel.textContent = 'Obtendo localização…';
        geoBtn.disabled = true;
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                var lat = pos.coords.latitude.toFixed(6);
                var lng = pos.coords.longitude.toFixed(6);
                fetch('https://nominatim.openstreetmap.org/reverse?format=json&zoom=10&lat=' + lat + '&lon=' + lng, {
                    headers: { 'Accept-Language': 'pt-BR,pt;q=0.9' }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var a = data.address || {};
                    var city = a.city || a.town || a.village || a.municipality || a.county || '';
                    if (city) input.value = city;
                    var p = new URLSearchParams(window.location.search);
                    var q = (document.querySelector('input[name="q"]') || {}).value || '';
                    var cat = (document.querySelector('input[name="category"]') || {}).value || '';
                    if (q.trim()) p.set('q', q.trim()); else p.delete('q');
                    if (city.trim()) p.set('location', city.trim()); else p.delete('location');
                    if (cat.trim()) p.set('category', cat.trim());
                    p.set('lat', lat); p.set('lng', lng);
                    window.location.href = '/buscar?' + p.toString();
                })
                .catch(function () {
                    var p = new URLSearchParams();
                    p.set('lat', lat); p.set('lng', lng);
                    window.location.href = '/buscar?' + p.toString();
                });
            },
            function () {
                geoLabel.textContent = 'Minha localização';
                geoBtn.disabled = false;
                input.placeholder = 'Localização negada pelo navegador';
            },
            { timeout: 8000, maximumAge: 60000 }
        );
    });
}());
</script>
