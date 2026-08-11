<div class="min-h-screen bg-gray-50 flex flex-col" x-data="{
        error: null,
        getPosition() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) { reject('GPS no disponible en este navegador.'); return; }
                navigator.geolocation.getCurrentPosition(
                    (pos) => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
                    (err) => reject('No se pudo obtener tu ubicación: ' + err.message),
                    { enableHighAccuracy: true, timeout: 15000 }
                );
            });
        },
        async capture(method) {
            this.error = null;
            try {
                const { lat, lng } = await this.getPosition();
                await $wire.call(method, lat, lng);
            } catch (e) {
                this.error = typeof e === 'string' ? e : 'Ocurrió un error al capturar la ubicación.';
            }
        },
    }"
>
    <header class="flex items-center justify-between px-5 py-4 bg-white border-b border-gray-100">
        <a href="{{ route('portal.dashboard') }}" wire:navigate class="text-sm text-gray-500">&larr; Volver</a>
        <p class="font-semibold text-gray-900 text-sm">
            {{ match($step) {
                'setup' => 'Nueva visita',
                'traveling_to' => 'Viajando al destino',
                'at_client' => 'En el destino',
                'traveling_back' => 'Volviendo a base',
                'pending_approval' => 'Firmas',
                'completed' => 'Visita completada',
                'cancelled' => 'Visita cancelada',
                default => '',
            } }}
        </p>
        <span class="w-12"></span>
    </header>

    <template x-if="error">
        <div class="mx-5 mt-4 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3" x-text="error"></div>
    </template>

    <main class="flex-1 px-5 py-6 max-w-lg w-full mx-auto">

        {{-- STEP 0 — setup form, before departing --}}
        @if ($step === 'setup')
            <div class="flex flex-col gap-5">
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('type', 'client_visit')"
                        @class(['flex-1 py-3 rounded-xl text-sm font-semibold border', 'bg-blue-600 text-white border-blue-600' => $type === 'client_visit', 'bg-white text-gray-600 border-gray-200' => $type !== 'client_visit'])>
                        Visita a cliente
                    </button>
                    <button type="button" wire:click="$set('type', 'machine_job')"
                        @class(['flex-1 py-3 rounded-xl text-sm font-semibold border', 'bg-blue-600 text-white border-blue-600' => $type === 'machine_job', 'bg-white text-gray-600 border-gray-200' => $type !== 'machine_job'])>
                        Trabajo con máquina
                    </button>
                </div>

                @if ($type === 'client_visit')
                    <div>
                        <label class="text-sm font-medium text-gray-700">Empresa</label>
                        <select wire:model="companyId" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
                            <option value="">Seleccionar...</option>
                            @foreach ($this->companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('companyId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div>
                        <label class="text-sm font-medium text-gray-700">Máquina</label>
                        <select wire:model="machineId" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
                            <option value="">Seleccionar...</option>
                            @foreach ($this->machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                            @endforeach
                        </select>
                        @error('machineId') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700">N° OV</label>
                        <input type="text" wire:model.live.debounce.500ms="ovNumber"
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">N° OT</label>
                        <input type="text" wire:model="otNumber"
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
                    </div>
                </div>

                @if ($ovHint)
                    <button type="button" wire:click="applyOvHint"
                        class="text-left text-xs bg-blue-50 border border-blue-200 text-blue-700 rounded-lg px-3 py-2">
                        {{ $ovHint }} — tocar para reusar
                    </button>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-700">Actividades</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($this->activities as $activity)
                            <label @class([
                                'text-xs px-3 py-1.5 rounded-full border cursor-pointer select-none',
                                'bg-blue-600 text-white border-blue-600' => in_array($activity->id, $activityIds),
                                'bg-white text-gray-600 border-gray-200' => ! in_array($activity->id, $activityIds),
                            ])>
                                <input type="checkbox" wire:model="activityIds" value="{{ $activity->id }}" class="hidden">
                                {{ $activity->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Observaciones</label>
                    <textarea wire:model="notes" rows="2"
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea>
                </div>

                <button type="button" @click="capture('startVisit')"
                    class="mt-2 w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold shadow-lg shadow-blue-600/30">
                    Confirmar salida
                </button>
            </div>
        @endif

        {{-- STEP 1 — traveling to destination --}}
        @if ($step === 'traveling_to')
            <div class="flex flex-col gap-6 items-center text-center pt-10"
                x-data="trackBuffer('to_client')" x-init="start()">
                <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-3xl">🚗</div>
                <p class="text-gray-600">Viajando hacia {{ $visit?->company?->name ?? $visit?->machine?->name }}</p>
                <button type="button" @click="capture('confirmArrival')"
                    class="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold shadow-lg shadow-blue-600/30">
                    Confirmar llegada
                </button>
            </div>
        @endif

        {{-- STEP 2 — at the destination --}}
        @if ($step === 'at_client')
            <div class="flex flex-col gap-6 items-center text-center pt-10">
                <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center text-3xl">📍</div>
                <p class="text-gray-600">En {{ $visit?->company?->name ?? $visit?->machine?->name }}</p>

                <div class="w-full text-left">
                    <label class="text-sm font-medium text-gray-700">Fotos (opcional)</label>
                    <input type="file" wire:model="newPhotos" multiple accept="image/*" capture="environment"
                        class="mt-1 w-full text-sm">
                    <div wire:loading wire:target="newPhotos" class="text-xs text-gray-400 mt-1">Subiendo...</div>
                    @if (!empty($newPhotos))
                        <button type="button" wire:click="savePhotos"
                            class="mt-2 text-sm text-blue-600 font-medium">Guardar {{ count($newPhotos) }} foto(s)</button>
                    @endif
                    @if ($visit?->photos->count())
                        <p class="text-xs text-gray-400 mt-2">{{ $visit->photos->count() }} foto(s) guardadas</p>
                    @endif
                </div>

                <button type="button" @click="capture('confirmDeparture')"
                    class="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold shadow-lg shadow-blue-600/30">
                    Confirmar salida del destino
                </button>
            </div>
        @endif

        {{-- STEP 3 — traveling back to base --}}
        @if ($step === 'traveling_back')
            <div class="flex flex-col gap-6 items-center text-center pt-10"
                x-data="trackBuffer('to_base')" x-init="start()">
                <div class="w-20 h-20 rounded-full bg-blue-100 flex items-center justify-center text-3xl">🚗</div>
                <p class="text-gray-600">Volviendo a la base</p>
                <button type="button" @click="capture('confirmReturn')"
                    class="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold shadow-lg shadow-blue-600/30">
                    Confirmar llegada a base
                </button>
            </div>
        @endif

        {{-- STEP 4 — signatures --}}
        @if ($step === 'pending_approval')
            <div class="flex flex-col gap-6">
                <p class="text-gray-600 text-sm text-center">Firmá vos y quien te recibió para cerrar la visita.</p>

                <div x-data="signaturePad('worker')" class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Firma del trabajador</label>
                    <canvas x-ref="canvas" class="w-full h-40 rounded-xl border border-gray-200 bg-white touch-none"></canvas>
                    <div class="flex gap-2">
                        <button type="button" @click="clear()" class="text-xs text-gray-500">Limpiar</button>
                        <button type="button" @click="save()" class="text-xs text-blue-600 font-medium ml-auto">Guardar firma</button>
                    </div>
                    <p x-show="saved" class="text-xs text-green-600">Firma guardada &check;</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700">Nombre de quien recibe</label>
                    <input type="text" wire:model="secondSignerName"
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
                    @error('secondSignerName') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-data="signaturePad('second')" class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700">Firma de quien recibe</label>
                    <canvas x-ref="canvas" class="w-full h-40 rounded-xl border border-gray-200 bg-white touch-none"></canvas>
                    <div class="flex gap-2">
                        <button type="button" @click="clear()" class="text-xs text-gray-500">Limpiar</button>
                        <button type="button" @click="save()" class="text-xs text-blue-600 font-medium ml-auto">Guardar firma</button>
                    </div>
                    <p x-show="saved" class="text-xs text-green-600">Firma guardada &check;</p>
                </div>

                <button type="button" wire:click="finish"
                    class="w-full py-4 rounded-2xl bg-slate-800 hover:bg-slate-700 active:scale-95 transition-all text-white font-bold shadow-lg">
                    Terminar — queda pendiente de aprobación
                </button>
            </div>
        @endif

        {{-- Finished states — read-only summary --}}
        @if (in_array($step, ['completed', 'cancelled']))
            <div class="flex flex-col gap-4 items-center text-center pt-10">
                <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl
                    {{ $step === 'completed' ? 'bg-green-100' : 'bg-red-100' }}">
                    {{ $step === 'completed' ? '✅' : '✕' }}
                </div>
                <p class="text-gray-600">
                    Esta visita está {{ $step === 'completed' ? 'completada y aprobada' : 'cancelada' }}.
                </p>
                <a href="{{ route('portal.dashboard') }}" wire:navigate
                    class="text-sm text-blue-600 font-medium">Volver al inicio</a>
            </div>
        @endif
    </main>
</div>

@script
<script>
    Alpine.data('trackBuffer', (leg) => ({
        buffer: [],
        watchId: null,
        start() {
            if (!navigator.geolocation) return;
            this.watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    this.buffer.push({
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        ts: new Date().toISOString(),
                    });
                },
                () => {},
                { enableHighAccuracy: true }
            );
            this.interval = setInterval(() => this.flush(leg), 25000);
            this.$watch('$wire.step', () => this.stop());
        },
        flush(leg) {
            if (this.buffer.length === 0) return;
            const points = this.buffer;
            this.buffer = [];
            $wire.call('recordTrackPoints', points, leg);
        },
        stop() {
            if (this.watchId !== null) navigator.geolocation.clearWatch(this.watchId);
            if (this.interval) clearInterval(this.interval);
            this.flush(leg);
        },
    }));

    Alpine.data('signaturePad', (who) => ({
        pad: null,
        saved: false,
        init() {
            this.pad = new SignaturePad(this.$refs.canvas);
        },
        clear() {
            this.pad.clear();
            this.saved = false;
        },
        async save() {
            if (this.pad.isEmpty()) return;
            await $wire.call('saveSignature', who, this.pad.toDataURL('image/png'));
            this.saved = true;
        },
    }));
</script>
@endscript
