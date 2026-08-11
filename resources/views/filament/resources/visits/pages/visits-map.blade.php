<x-filament-panels::page>
    <a href="{{ \App\Filament\Resources\Visits\VisitResource::getUrl('index') }}"
        class="inline-block mb-4 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">&larr; Volver al listado</a>

    <div
        wire:ignore
        x-data="visitsMap(@js($this->mapData))"
        x-init="init()"
    >
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select x-model="statusFilter" @change="redraw()"
                class="rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 text-sm">
                <option value="all">Todas las visitas</option>
                <option value="traveling_to">Viajando al destino</option>
                <option value="at_client">En el destino</option>
                <option value="traveling_back">Volviendo a base</option>
                <option value="pending_approval">Pendiente de aprobación</option>
                <option value="completed">Completada</option>
                <option value="cancelled">Cancelada</option>
            </select>
            <span class="text-xs text-gray-400" x-text="visibleCount + ' visita(s) en el mapa'"></span>
        </div>

        <div id="visits-map" style="height: 600px; border-radius: 0.75rem;" class="border border-gray-200 dark:border-gray-700"></div>

        <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
            <span class="flex items-center gap-1"><span class="inline-block w-4 h-0.5 bg-blue-500"></span> Viaje al destino</span>
            <span class="flex items-center gap-1"><span class="inline-block w-4 h-0.5 bg-orange-500"></span> Vuelta a base</span>
            <span class="flex items-center gap-1">🏠 Base (salida/llegada)</span>
            <span class="flex items-center gap-1">📍 Llegada al destino</span>
            <span class="flex items-center gap-1">🏁 Salida del destino</span>
        </div>
    </div>

    @script
    <script>
        Alpine.data('visitsMap', (visits) => ({
            statusFilter: 'all',
            visibleCount: visits.length,
            allVisits: [],
            map: null,
            clusterGroup: null,
            lineLayer: null,

            init() {
                this.waitForLeaflet(() => this.build(visits));
            },

            waitForLeaflet(cb) {
                if (typeof L === 'undefined' || typeof L.markerClusterGroup === 'undefined') {
                    setTimeout(() => this.waitForLeaflet(cb), 150);
                    return;
                }
                cb();
            },

            icon(emoji) {
                var wrapOpen = String.fromCharCode(60) + 'div style="font-size:20px;line-height:1;transform:translate(-50%,-100%)"' + String.fromCharCode(62);
                var wrapClose = String.fromCharCode(60) + '/div' + String.fromCharCode(62);
                return L.divIcon({ html: wrapOpen + emoji + wrapClose, className: '', iconSize: [0, 0] });
            },

            build(visits) {
                this.map = L.map('visits-map');
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(this.map);

                this.allVisits = visits;
                this.redraw();
            },

            redraw() {
                if (this.clusterGroup) this.map.removeLayer(this.clusterGroup);
                if (this.lineLayer) this.map.removeLayer(this.lineLayer);

                this.clusterGroup = L.markerClusterGroup();
                this.lineLayer = L.layerGroup();

                const visits = this.statusFilter === 'all'
                    ? this.allVisits
                    : this.allVisits.filter(v => v.status === this.statusFilter);

                this.visibleCount = visits.length;
                const bounds = [];

                visits.forEach(v => {
                    const cp = v.checkpoints;

                    if (cp.departed_base) {
                        this.clusterGroup.addLayer(this.marker(cp.departed_base, '🏠', v, 'Salida de base'));
                        bounds.push([cp.departed_base.lat, cp.departed_base.lng]);
                    }
                    if (cp.arrived_client) {
                        this.clusterGroup.addLayer(this.marker(cp.arrived_client, '📍', v, 'Llegada a ' + (v.destination ?? 'destino')));
                        bounds.push([cp.arrived_client.lat, cp.arrived_client.lng]);
                    }
                    if (cp.departed_client) {
                        this.clusterGroup.addLayer(this.marker(cp.departed_client, '🏁', v, 'Salida de ' + (v.destination ?? 'destino')));
                    }
                    if (cp.arrived_base) {
                        this.clusterGroup.addLayer(this.marker(cp.arrived_base, '🏠', v, 'Llegada a base'));
                        bounds.push([cp.arrived_base.lat, cp.arrived_base.lng]);
                    }

                    if (v.legs.to_client.length > 1) {
                        L.polyline(v.legs.to_client, { color: '#3b82f6', weight: 3, opacity: 0.7 }).addTo(this.lineLayer);
                    }
                    if (v.legs.to_base.length > 1) {
                        L.polyline(v.legs.to_base, { color: '#f97316', weight: 3, opacity: 0.7 }).addTo(this.lineLayer);
                    }
                });

                this.clusterGroup.addTo(this.map);
                this.lineLayer.addTo(this.map);

                if (bounds.length > 0) {
                    this.map.fitBounds(bounds, { padding: [30, 30] });
                } else {
                    this.map.setView([-25.2637, -57.5759], 12);
                }
            },

            marker(point, emoji, visit, label) {
                var parts = [
                    '<strong>' + (visit.worker ?? '') + '</strong><br>' + label,
                    point.time ? '<br><span style="color:#888">' + point.time + '</span>' : '',
                    '<br><span style="font-size:11px;color:#2563eb">' + visit.statusLabel + '</span>',
                ];
                return L.marker([point.lat, point.lng], { icon: this.icon(emoji) }).bindPopup(parts.join(''));
            },
        }));
    </script>
    @endscript
</x-filament-panels::page>
