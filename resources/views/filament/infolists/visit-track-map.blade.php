@php
    $visit = $getRecord();
    $points = $visit->trackPoints->map(fn ($p) => ['lat' => (float) $p->lat, 'lng' => (float) $p->lng])->values();
    $mapId = 'visit-map-'.$visit->id;
@endphp

<div wire:ignore>
    @if ($points->isEmpty())
        <p class="text-sm text-gray-400">Sin recorrido GPS registrado.</p>
    @else
        <div
            id="{{ $mapId }}"
            style="height: 260px; border-radius: 0.75rem;"
            x-data="{ points: @js($points) }"
            x-init="
                (function initMap() {
                    if (typeof L === 'undefined') { setTimeout(initMap, 150); return; }
                    if ($el._leaflet_id) return;

                    const map = L.map($el);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                    }).addTo(map);

                    const latLngs = points.map(p => [p.lat, p.lng]);
                    L.polyline(latLngs, { color: '#2563eb', weight: 4 }).addTo(map);
                    L.marker(latLngs[0]).addTo(map).bindPopup('Inicio');
                    L.marker(latLngs[latLngs.length - 1]).addTo(map).bindPopup('Fin');
                    map.fitBounds(latLngs, { padding: [20, 20] });
                })();
            "
        ></div>
    @endif
</div>
