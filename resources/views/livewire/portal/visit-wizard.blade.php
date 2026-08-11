<div class="min-h-screen bg-gray-50 dark:bg-slate-900 flex flex-col" x-data="{
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
    <header class="flex items-center justify-between px-5 py-4 bg-white dark:bg-slate-800 border-b border-gray-100 dark:border-slate-700">
        <a href="{{ route('portal.dashboard') }}" wire:navigate class="text-sm text-gray-500 dark:text-slate-400">&larr; Volver</a>
        <p class="font-semibold text-gray-900 dark:text-white text-sm">
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
        <div class="mx-5 mt-4 rounded-lg bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm px-4 py-3" x-text="error"></div>
    </template>

    <main class="flex-1 px-5 py-6 max-w-lg w-full mx-auto">

        {{-- Step tracker + job summary — visible on every step once the visit exists --}}
        @if ($step !== 'setup' && $visit)
            @php
                $destino = $visit->destinationName() ?? 'destino';
                $nodes = [
                    ['icon' => '🚗', 'label' => 'Salida', 'done' => (bool) $visit->departed_base_at, 'time' => $visit->departed_base_at],
                    ['icon' => '📍', 'label' => $destino, 'done' => (bool) $visit->arrived_client_at, 'time' => $visit->arrived_client_at],
                    ['icon' => '🏁', 'label' => 'Salida', 'done' => (bool) $visit->departed_client_at, 'time' => $visit->departed_client_at],
                    ['icon' => '🏠', 'label' => 'Llegada', 'done' => (bool) $visit->arrived_base_at, 'time' => $visit->arrived_base_at],
                ];
                $activeIndex = match ($step) {
                    'traveling_to' => 0,
                    'at_client' => 1,
                    'traveling_back' => 2,
                    default => 3,
                };
            @endphp
            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 p-4 mb-4">
                <p class="text-xs text-gray-400 dark:text-slate-500 mb-3">{{ $destino }} @if($visit->ov_number) · OV {{ $visit->ov_number }} @endif</p>
                <div class="flex items-center">
                    @foreach ($nodes as $i => $node)
                        <div class="flex flex-col items-center flex-1">
                            <div @class([
                                'w-10 h-10 rounded-full flex items-center justify-center text-lg border-2',
                                'bg-blue-600 border-blue-600 text-white' => $i === $activeIndex,
                                'bg-green-500 border-green-500 text-white' => $node['done'] && $i !== $activeIndex,
                                'bg-gray-100 dark:bg-slate-700 border-gray-200 dark:border-slate-600 text-gray-400 dark:text-slate-500' => ! $node['done'] && $i !== $activeIndex,
                            ])>{{ $node['icon'] }}</div>
                            <p @class(['text-[10px] mt-1 text-center leading-tight', 'text-blue-600 dark:text-blue-400 font-semibold' => $i === $activeIndex, 'text-gray-400 dark:text-slate-500' => $i !== $activeIndex])>
                                {{ \Illuminate\Support\Str::limit($node['label'], 12) }}
                            </p>
                        </div>
                        @if ($i < count($nodes) - 1)
                            <div @class(['flex-1 h-0.5 -mt-5', 'bg-green-500' => $node['done'], 'bg-gray-200 dark:bg-slate-700' => ! $node['done']])></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 p-4 mb-4 text-sm">
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-gray-600 dark:text-slate-400">
                    @if ($visit->ov_number)<span>N° OV: <strong class="text-gray-900 dark:text-white">{{ $visit->ov_number }}</strong></span>@endif
                    @if ($visit->ot_number)<span>N° OT: <strong class="text-gray-900 dark:text-white">{{ $visit->ot_number }}</strong></span>@endif
                </div>
                @if ($visit->activities->isNotEmpty())
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @foreach ($visit->activities as $activity)
                            <span class="text-[11px] bg-blue-50 dark:bg-blue-950 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-full">{{ $activity->name }}</span>
                        @endforeach
                    </div>
                @endif
                @if ($visit->notes)
                    <p class="text-gray-500 dark:text-slate-400 text-xs mt-2">{{ $visit->notes }}</p>
                @endif

                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-slate-700 flex flex-col gap-1">
                    @foreach ($nodes as $node)
                        @if ($node['time'])
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-slate-400">
                                <span>{{ $node['icon'] }} {{ $node['label'] === $destino ? 'Llegada' : $node['label'] }}</span>
                                <span class="font-medium text-gray-700 dark:text-slate-300">{{ $node['time']->format('H:i:s') }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- STEP 0 — setup form, before departing --}}
        @if ($step === 'setup')
            <div class="flex flex-col gap-5">
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('type', 'client_visit')"
                        @class(['flex-1 py-3 rounded-xl text-sm font-semibold border', 'bg-blue-600 text-white border-blue-600' => $type === 'client_visit', 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 border-gray-200 dark:border-slate-600' => $type !== 'client_visit'])>
                        Visita a cliente
                    </button>
                    <button type="button" wire:click="$set('type', 'machine_job')"
                        @class(['flex-1 py-3 rounded-xl text-sm font-semibold border', 'bg-blue-600 text-white border-blue-600' => $type === 'machine_job', 'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 border-gray-200 dark:border-slate-600' => $type !== 'machine_job'])>
                        Trabajo con máquina
                    </button>
                </div>

                @if ($type === 'client_visit')
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Empresa</label>
                        <select wire:model="companyId" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm">
                            <option value="">Seleccionar...</option>
                            @foreach ($this->companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('companyId') <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                @else
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Máquina</label>
                        <select wire:model="machineId" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm">
                            <option value="">Seleccionar...</option>
                            @foreach ($this->machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                            @endforeach
                        </select>
                        @error('machineId') <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-slate-300">N° OV</label>
                        <input type="text" wire:model.live.debounce.500ms="ovNumber"
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700 dark:text-slate-300">N° OT</label>
                        <input type="text" wire:model="otNumber"
                            class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm">
                    </div>
                </div>

                @if ($ovHint)
                    <button type="button" wire:click="applyOvHint"
                        class="text-left text-xs bg-blue-50 dark:bg-blue-950 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 rounded-lg px-3 py-2">
                        {{ $ovHint }} — tocar para reusar
                    </button>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Actividades</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($this->activities as $activity)
                            <label @class([
                                'text-xs px-3 py-1.5 rounded-full border cursor-pointer select-none',
                                'bg-blue-600 text-white border-blue-600' => in_array($activity->id, $activityIds),
                                'bg-white dark:bg-slate-800 text-gray-600 dark:text-slate-300 border-gray-200 dark:border-slate-600' => ! in_array($activity->id, $activityIds),
                            ])>
                                <input type="checkbox" wire:model="activityIds" value="{{ $activity->id }}" class="hidden">
                                {{ $activity->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Observaciones</label>
                    <textarea wire:model="notes" rows="2"
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm"></textarea>
                </div>

                <button type="button" @click="capture('startVisit')"
                    class="mt-2 w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold shadow-lg shadow-blue-600/30">
                    Confirmar salida
                </button>
            </div>
        @endif

        {{-- STEP 1 — traveling to destination --}}
        @if ($step === 'traveling_to')
            <div class="flex flex-col gap-6 items-center text-center pt-4"
                x-data="trackBuffer('to_client')" x-init="start()">
                <div class="w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-3xl">🚗</div>
                <p class="text-gray-600 dark:text-slate-300">Viajando hacia {{ $visit?->destinationName() }}</p>

                <div class="flex items-center gap-2 text-xs" :class="active ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-slate-500'">
                    <span class="relative flex h-2 w-2">
                        <span x-show="active" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" :class="active ? 'bg-green-500' : 'bg-gray-300 dark:bg-slate-600'"></span>
                    </span>
                    <span x-text="active ? 'GPS activo · ' + totalSent + ' puntos guardados' : 'Esperando señal GPS...'"></span>
                </div>

                <button type="button" @click="capture('confirmArrival')"
                    class="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold shadow-lg shadow-blue-600/30">
                    Confirmar llegada
                </button>
            </div>
        @endif

        {{-- STEP 2 — at the destination --}}
        @if ($step === 'at_client')
            <div class="flex flex-col gap-6 items-center text-center pt-4">
                <div class="w-20 h-20 rounded-full bg-green-100 dark:bg-green-950 flex items-center justify-center text-3xl">📍</div>
                <p class="text-gray-600 dark:text-slate-300">En {{ $visit?->destinationName() }}</p>

                <div class="w-full text-left">
                    <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Fotos (opcional)</label>
                    <input type="file" wire:model="newPhotos" multiple accept="image/*" capture="environment"
                        class="mt-1 w-full text-sm text-gray-700 dark:text-slate-300">
                    <div wire:loading wire:target="newPhotos" class="text-xs text-gray-400 dark:text-slate-500 mt-1">Subiendo...</div>
                    @if (!empty($newPhotos))
                        <button type="button" wire:click="savePhotos"
                            class="mt-2 text-sm text-blue-600 dark:text-blue-400 font-medium">Guardar {{ count($newPhotos) }} foto(s)</button>
                    @endif
                    @if ($visit?->photos->count())
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">{{ $visit->photos->count() }} foto(s) guardadas</p>
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
            <div class="flex flex-col gap-6 items-center text-center pt-4"
                x-data="trackBuffer('to_base')" x-init="start()">
                <div class="w-20 h-20 rounded-full bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-3xl">🚗</div>
                <p class="text-gray-600 dark:text-slate-300">Volviendo a la base</p>

                <div class="flex items-center gap-2 text-xs" :class="active ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-slate-500'">
                    <span class="relative flex h-2 w-2">
                        <span x-show="active" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2" :class="active ? 'bg-green-500' : 'bg-gray-300 dark:bg-slate-600'"></span>
                    </span>
                    <span x-text="active ? 'GPS activo · ' + totalSent + ' puntos guardados' : 'Esperando señal GPS...'"></span>
                </div>

                <button type="button" @click="capture('confirmReturn')"
                    class="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 active:scale-95 transition-all text-white font-bold shadow-lg shadow-blue-600/30">
                    Confirmar llegada a base
                </button>
            </div>
        @endif

        {{-- STEP 4 — signatures --}}
        @if ($step === 'pending_approval')
            <div class="flex flex-col gap-6">
                <p class="text-gray-600 dark:text-slate-300 text-sm text-center">Firmá vos y quien te recibió para cerrar la visita.</p>

                {{-- Signature canvases stay white in both themes: signature_pad draws in black,
                     and the saved PNG needs a light background to stay legible in the admin review. --}}
                <div x-data="signaturePad('worker')" class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Firma del trabajador</label>
                    <canvas x-ref="canvas" class="w-full h-40 rounded-xl border border-gray-200 dark:border-slate-600 bg-white touch-none"></canvas>
                    <div class="flex gap-2">
                        <button type="button" @click="clear()" class="text-xs text-gray-500 dark:text-slate-400">Limpiar</button>
                        <button type="button" @click="save()" class="text-xs text-blue-600 dark:text-blue-400 font-medium ml-auto">Guardar firma</button>
                    </div>
                    <p x-show="saved" class="text-xs text-green-600 dark:text-green-400">Firma guardada &check;</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Nombre de quien recibe</label>
                    <input type="text" wire:model="secondSignerName"
                        class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white text-sm">
                    @error('secondSignerName') <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-data="signaturePad('second')" class="flex flex-col gap-2">
                    <label class="text-sm font-medium text-gray-700 dark:text-slate-300">Firma de quien recibe</label>
                    <canvas x-ref="canvas" class="w-full h-40 rounded-xl border border-gray-200 dark:border-slate-600 bg-white touch-none"></canvas>
                    <div class="flex gap-2">
                        <button type="button" @click="clear()" class="text-xs text-gray-500 dark:text-slate-400">Limpiar</button>
                        <button type="button" @click="save()" class="text-xs text-blue-600 dark:text-blue-400 font-medium ml-auto">Guardar firma</button>
                    </div>
                    <p x-show="saved" class="text-xs text-green-600 dark:text-green-400">Firma guardada &check;</p>
                </div>

                <button type="button" wire:click="finish"
                    class="w-full py-4 rounded-2xl bg-slate-800 hover:bg-slate-700 active:scale-95 transition-all text-white font-bold shadow-lg">
                    Terminar — queda pendiente de aprobación
                </button>
            </div>
        @endif

        {{-- Finished states — read-only summary --}}
        @if (in_array($step, ['completed', 'cancelled']))
            <div class="flex flex-col gap-4 items-center text-center pt-4">
                <div class="w-20 h-20 rounded-full flex items-center justify-center text-3xl
                    {{ $step === 'completed' ? 'bg-green-100 dark:bg-green-950' : 'bg-red-100 dark:bg-red-950' }}">
                    {{ $step === 'completed' ? '✅' : '✕' }}
                </div>
                <p class="text-gray-600 dark:text-slate-300">
                    Esta visita está {{ $step === 'completed' ? 'completada y aprobada' : 'cancelada' }}.
                </p>
                <a href="{{ route('portal.dashboard') }}" wire:navigate
                    class="text-sm text-blue-600 dark:text-blue-400 font-medium">Volver al inicio</a>
            </div>
        @endif
    </main>
</div>

@script
<script>
    Alpine.data('trackBuffer', (leg) => ({
        buffer: [],
        active: false,
        totalSent: 0,
        watchId: null,
        start() {
            if (!navigator.geolocation) return;
            this.watchId = navigator.geolocation.watchPosition(
                (pos) => {
                    this.active = true;
                    this.buffer.push({
                        lat: pos.coords.latitude,
                        lng: pos.coords.longitude,
                        ts: new Date().toISOString(),
                    });
                },
                () => { this.active = false; },
                { enableHighAccuracy: true }
            );
            this.interval = setInterval(() => this.flush(leg), 25000);
            this.$watch('$wire.step', () => this.stop());
        },
        flush(leg) {
            if (this.buffer.length === 0) return;
            const points = this.buffer;
            this.totalSent += points.length;
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
