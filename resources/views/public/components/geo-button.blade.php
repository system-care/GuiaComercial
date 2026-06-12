@php
    $btnId     = $btnId     ?? 'btn-geo';
    $hasGeo    = $hasGeo    ?? false;
    $removeUrl = $removeUrl ?? null;
@endphp

@if ($hasGeo && $removeUrl)
    <a href="{{ $removeUrl }}"
       class="inline-flex items-center gap-2 rounded-full border border-violet-400 bg-violet-100 px-4 py-2 text-sm font-semibold text-violet-800 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
        </svg>
        <span>Localização ativa · Remover</span>
    </a>
@else
    <button type="button" id="{{ $btnId }}"
        class="inline-flex items-center gap-2 rounded-full border border-violet-200 bg-violet-50 px-4 py-2 text-sm font-semibold text-violet-700 transition hover:bg-violet-100 disabled:cursor-not-allowed disabled:opacity-60">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v3M12 19v3M2 12h3M19 12h3"/>
        </svg>
        <span>Usar minha localização</span>
    </button>
    <span id="{{ $btnId }}-msg" class="hidden text-sm text-slate-500"></span>
    <script>
    (function () {
        var btn = document.getElementById('{{ $btnId }}');
        var msg = document.getElementById('{{ $btnId }}-msg');
        if (!btn) return;
        btn.addEventListener('click', function () {
            if (!navigator.geolocation) {
                msg.textContent = 'Geolocalização não suportada neste navegador.';
                msg.classList.remove('hidden');
                return;
            }
            btn.disabled = true;
            btn.querySelector('span').textContent = 'Obtendo localização…';
            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    btn.querySelector('span').textContent = 'Identificando cidade…';
                    var lat = pos.coords.latitude.toFixed(6);
                    var lng = pos.coords.longitude.toFixed(6);
                    function doRedirect(city) {
                        var p = new URLSearchParams();
                        var q   = (document.querySelector('input[name="q"]')        || {}).value || '';
                        var loc = city || (document.querySelector('input[name="location"]') || {}).value || '';
                        var cat = (document.querySelector('input[name="category"]') || {}).value || '';
                        if (q.trim())   p.set('q', q.trim());
                        if (loc.trim()) p.set('location', loc.trim());
                        if (cat.trim()) p.set('category', cat.trim());
                        p.set('lat', lat);
                        p.set('lng', lng);
                        window.location.href = '/buscar?' + p.toString();
                    }
                    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng, {
                        headers: { 'Accept-Language': 'pt-BR,pt;q=0.9' }
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        var a = data.address || {};
                        var city = a.city || a.town || a.village || a.municipality || a.county || '';
                        doRedirect(city);
                    })
                    .catch(function () { doRedirect(''); });
                },
                function () {
                    btn.disabled = false;
                    btn.querySelector('span').textContent = 'Usar minha localização';
                    msg.textContent = 'Localização negada. Use o campo de cidade ou bairro.';
                    msg.classList.remove('hidden');
                },
                { timeout: 8000, maximumAge: 60000 }
            );
        });
    }());
    </script>
@endif
